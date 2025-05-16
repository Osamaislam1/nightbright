<?php

namespace App\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;

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
            'tip_percentage' => 'nullable|numeric|min:0|max:100',
            'processing_fee' => 'nullable|numeric|min:0',
            'tip_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $amount = (float) $request->amount;
            $tipPercentage = (int) ($request->tip_percentage ?? 0);
            
            // Use processing fee and tip amount from form if provided, otherwise calculate
            $processingFee = $request->has('processing_fee') ? (float) $request->processing_fee : (($amount * 0.029) + 0.30);
            $tipAmount = $request->has('tip_amount') ? (float) $request->tip_amount : ($amount * $tipPercentage / 100);
            $totalAmount = $amount + $processingFee + $tipAmount;
            
            // Convert amount to cents for Stripe
            $amountInCents = (int) ($totalAmount * 100);
            
            // Set your Stripe API key
            Stripe::setApiKey(config('cashier.secret'));
            
            // Convert individual amounts to cents for Stripe
            $donationAmountInCents = (int) ($amount * 100);
            $processingFeeInCents = (int) ($processingFee * 100);
            $tipAmountInCents = (int) ($tipAmount * 100);
            
            // Prepare line items for Stripe checkout
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Donation to ' . $request->organization,
                        ],
                        'unit_amount' => $donationAmountInCents,
                        // Add recurring parameter for subscription
                        'recurring' => $request->donation_type === 'monthly' ? [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ] : null,
                    ],
                    'quantity' => 1,
                ],
            ];
            
            // Add processing fee as a separate line item
            if ($processingFee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Processing Fee',
                        ],
                        'unit_amount' => $processingFeeInCents,
                        'recurring' => $request->donation_type === 'monthly' ? [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ] : null,
                    ],
                    'quantity' => 1,
                ];
            }
            
            // Add tip as a separate line item if it exists
            if ($tipAmount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Tip to support Night Bright',
                        ],
                        'unit_amount' => $tipAmountInCents,
                        'recurring' => $request->donation_type === 'monthly' ? [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ] : null,
                    ],
                    'quantity' => 1,
                ];
            }
            
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
     * Get real-time processing fee rates from Stripe
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProcessingFees(Request $request)
    {
        try {
            // Set your Stripe API key
            Stripe::setApiKey(config('cashier.secret'));
            
            $paymentMethod = $request->input('payment_method', 'card');
            $cardBrand = $request->input('card_brand', 'visa');
            
            // Default fee rates based on payment method and card brand
            $feeRates = [
                'card' => [
                    'visa' => ['percentage' => 2.9, 'fixed' => 0.30],
                    'mastercard' => ['percentage' => 2.9, 'fixed' => 0.30],
                    'amex' => ['percentage' => 3.5, 'fixed' => 0.30],
                    'discover' => ['percentage' => 2.9, 'fixed' => 0.30],
                    'default' => ['percentage' => 2.9, 'fixed' => 0.30],
                ],
                'us_bank_account' => ['percentage' => 0.8, 'fixed' => 0.30],
                'cashapp' => ['percentage' => 2.5, 'fixed' => 0.30],
                'default' => ['percentage' => 2.9, 'fixed' => 0.30],
            ];
            
            // Get the appropriate fee rate
            if ($paymentMethod === 'card') {
                $feeRate = $feeRates['card'][$cardBrand] ?? $feeRates['card']['default'];
            } else {
                $feeRate = $feeRates[$paymentMethod] ?? $feeRates['default'];
            }
            
            // Calculate example fees for common amounts
            $exampleAmounts = [10, 25, 50, 100, 250, 500, 1000];
            $exampleFees = [];
            
            foreach ($exampleAmounts as $amount) {
                $fee = ($amount * $feeRate['percentage'] / 100) + $feeRate['fixed'];
                $exampleFees[$amount] = round($fee, 2);
            }
            
            return response()->json([
                'success' => true,
                'fee_rate' => $feeRate,
                'example_fees' => $exampleFees
            ]);
            
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing fee calculation failed: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            Log::error('Processing Fee Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating processing fees.'
            ], 500);
        }
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
