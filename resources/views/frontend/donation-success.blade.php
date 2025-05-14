@extends('frontend.layouts.master')

@section('content')
<main>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h1 class="mb-4">Thank You for Your Donation!</h1>
                        <p class="lead mb-4">Your generosity helps us continue our mission. A confirmation email has been sent to your email address.</p>
                        <div class="d-flex justify-content-center mb-4">
                            <div class="donation-details text-start">
                                <div class="mb-2">
                                    <strong>Organization:</strong> {{ $session->metadata->organization ?? 'Night Bright' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Amount:</strong> ${{ number_format($session->metadata->amount ?? 0, 2) }}
                                </div>
                                @if(isset($session->metadata->tip_amount) && $session->metadata->tip_amount > 0)
                                <div class="mb-2">
                                    <strong>Tip:</strong> ${{ number_format($session->metadata->tip_amount, 2) }} ({{ $session->metadata->tip_percentage }}%)
                                </div>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('frontend.index') }}" class="btn btn-primary">Return to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
