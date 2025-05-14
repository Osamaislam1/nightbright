<?php

namespace App\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class DonationController extends Controller
{
    /**
     * Create a Stripe Checkout session and redirect to payment page
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function createCheckoutSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:1',
            'donation_type' => 'required|string|in:one-time,monthly',
            'organization' => 'required|string|max:255',
            'message' => 'nullable|string',
            'contact_permission' => 'required|boolean',
            'stay_anonymous' => 'required|boolean',
            'tip_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $amount = (float) $request->amount;
            $tipPercentage = (int) ($request->tip_percentage ?? 0);
            
            // Calculate processing fee and tip amount
            $processingFee = ($amount * 0.029) + 0.30;
            $tipAmount = ($amount * $tipPercentage / 100);
            $totalAmount = $amount + $processingFee + $tipAmount;
            
            // Convert amount to cents for Stripe
            $amountInCents = (int) ($totalAmount * 100);
            
            // Set your Stripe API key
            Stripe::setApiKey(config('cashier.secret'));
            
            // Prepare line items for Stripe checkout
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Donation to ' . $request->organization,
                        ],
                        'unit_amount' => $amountInCents,
                        // Add recurring parameter for subscription
                        'recurring' => $request->donation_type === 'monthly' ? [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ] : null,
                    ],
                    'quantity' => 1,
                ],
            ];
            
            // Create a checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => $request->donation_type === 'monthly' ? 'subscription' : 'payment',
                'success_url' => route('donation.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('donation.cancel'),
                'metadata' => [
                    'name' => $request->stay_anonymous ? 'Anonymous' : $request->name,
                    'email' => $request->email,
                    'donation_type' => $request->donation_type,
                    'organization' => $request->organization,
                    'message' => $request->message,
                    'amount' => $amount,
                    'processing_fee' => $processingFee,
                    'tip_amount' => $tipAmount,
                    'tip_percentage' => $tipPercentage,
                    'contact_permission' => $request->contact_permission ? 'Yes' : 'No',
                    'stay_anonymous' => $request->stay_anonymous ? 'Yes' : 'No',
                ],
            ]);
            
            // Redirect to Stripe Checkout
            return redirect($session->url);
            
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('Donation Processing Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing your donation.');
        }
    }
    
    /**
     * Handle successful payment
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        try {
            if ($request->has('session_id')) {
                Stripe::setApiKey(config('cashier.secret'));
                $session = Session::retrieve($request->session_id);
                
                
                
                return view('frontend.donation-success', ['session' => $session]);
            }
            
            return redirect('/')->with('success', 'Thank you for your donation!');
            
        } catch (Exception $e) {
            Log::error('Error handling successful payment: ' . $e->getMessage());
            return redirect('/')->with('error', 'There was an issue processing your donation.');
        }
    }
    
    /**
     * Handle cancelled payment
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        return redirect('/')->with('info', 'Your donation has been cancelled.');
    }
    
    /**
     * Webhook handler for Stripe events
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('cashier.webhook.secret');
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
            
            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->updateDonationStatus($session->id, 'completed');
                    break;
                    
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    // If you have access to the session ID in the payment intent metadata, use it here
                    if (isset($paymentIntent->metadata->checkout_session_id)) {
                        $this->updateDonationStatus($paymentIntent->metadata->checkout_session_id, 'failed');
                    }
                    break;
            }
            
            return response('Webhook received', 200);
            
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook Signature Error: ' . $e->getMessage());
            return response('Invalid signature', 400);
        } catch (Exception $e) {
            Log::error('Webhook Processing Error: ' . $e->getMessage());
            return response('Webhook error', 500);
        }
    }
    
    /**
     * Update donation status based on session ID
     *
     * @param string $sessionId
     * @param string $status
     * @return void
     */
    
}
