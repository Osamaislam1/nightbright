@extends('frontend.layouts.master')

@section('content')
<main>

        <!-- Back Button -->
        <div class="container mt-4">
            <a href="#" class="back-link">
                <Back</a></div>

                <!-- Main Content -->
                <div class="container mt-4">
                    <div
                        class="main-content p-5 bg-white rounded shadow">
                        <!-- Organization Header -->
                        <div class="row align-items-center mb-5">
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="org-logo me-4">
                                    <img src="{{asset('frontend/images/logo.avif')}}" alt="Night Bright Logo" class="img-fluid">
                                </div>
                                <div>
                                    <h1 class="mb-1">Night Bright</h1>
                                    <div class="location-tags">
                                        <span class="location-tag">North America</span>
                                        <span class="location-tag d-block">United States</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <a href="#" class="donate-btn">
                                    Donate
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Organization Description -->
                        <div class="row mb-5">
                            <div class="col-12">
                                <p class="org-description">
                                    Night Bright is a non-profit 501(c)3. We strive to make donating to your favorite causes an enjoyable experience that leads to a deeper connection with the thousands of beautiful people spreading the love of God throughout the globe. Please join us in making it easier to find, fund, and resource missions worldwide.
                                </p>
                            </div>
                        </div>

                        <!-- Navigation Tabs -->
                        <div class="row mb-5">
                            <div class="col-12 d-flex justify-content-center">
                                <div class="tab-buttons">
                                    <a href="#" class="tab-btn active">Photos</a>
                                    <a href="#" class="tab-btn">Video</a>
                                    <a href="#" class="tab-btn">Causes</a>
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="sidebar">
                                    <div class="sidebar-item mb-3">
                                        <img src="{{asset('frontend/images/find.JPG')}}" alt="Find" class="img-fluid mb-2 sidebar-thumb" data-main="{{asset('frontend/images/find.JPG')}}">
                                    </div>
                                    <div class="sidebar-item mb-3">
                                        <img src="{{asset('frontend/images/fund.JPG')}}" alt="Fund" class="img-fluid mb-2 sidebar-thumb" data-main="{{asset('frontend/images/fund.JPG')}}">
                                    </div>
                                    <div class="sidebar-item">
                                        <img src="{{asset('frontend/images/resource.JPG')}}" alt="Resource" class="img-fluid mb-2 sidebar-thumb" data-main="{{asset('frontend/images/resource.JPG')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="main-image">
                                    <img id="mainImage" src="{{asset('frontend/images/find.JPG')}}" alt="Main Display" class="img-fluid rounded">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Donation Sidebar Widget -->
                <div id="donateSidebarOverlay" class="donate-overlay"></div>
                <aside id="donateSidebar" class="donate-sidebar scroll-thumb">
                    <button class="donate-close" id="donateCloseBtn" aria-label="Close">&times;</button>

                    <!-- Step 1: Initial Donation Form -->
                    <div id="donateStep1" class="donate-step active">
                        <div class="donate-title">DONATE</div>
                        <form class="donate-form" id="donateForm1">
                            <div class="mb-3 mt-2">
                                <label class="form-label fw-semibold">Missionary Donation</label>
                                <hr class="my-2">
                                

                                <div class="donate-tabs mb-3">
                                    <button type="button" class="donate-tab active" data-type="one-time">One-Time</button>
                                    <button type="button" class="donate-tab" data-type="monthly">Monthly</button>
                                </div>
                                <div class="row mb-3">
                                    <div class="col">
                                        <input type="text" class="form-control" placeholder="Donor's Name" aria-label="Donor Name" id="donorName">
                                    </div>
                                    <div class="col">
                                        <input type="email" class="form-control" placeholder="Donor's Email" aria-label="Donor Email" id="donorEmail">
                                    </div>
                                </div>

                                <select class="form-select mb-3" id="donationOrg" style="border: 1px solid #c6a15a !important;">
                                    <option>Night Bright</option>
                                </select>
                                <div class="donate-amounts mb-3">
                                    <button type="button" class="amount-btn" data-amount="10">10$</button>
                                    <button type="button" class="amount-btn active" data-amount="25">25$</button>
                                    <button type="button" class="amount-btn" data-amount="50">50$</button>
                                    <button type="button" class="amount-btn" data-amount="100">100$</button>
                                    <button type="button" class="amount-btn" data-amount="250">250$</button>
                                    <button type="button" class="amount-btn" data-amount="500">500$</button>
                                    <button type="button" class="amount-btn" data-amount="1000">1000$</button>
                                    <input type="number" class="form-control amount-btn" id="otherAmount" placeholder="Other" min="1" step="1">
                                </div>
                                <button type="button" class="btn-add-message mb-2 w-100" id="addMessageBtn">+ Add a message</button>
                                <input type="text" class="form-control mb-3 custom-input" id="messageInput" style="display: none;">
                                <hr class="my-2">

                                <!-- Stay Anonymous Option -->
                                 <div class="row mt-4">
                                    <div class="col-md-6">
                                <div class="form-check form-check-inline mb-3">
                                    <input class="form-check-input" type="checkbox" id="stayAnonymous" value="1">
                                    <label class="form-check-label" for="stayAnonymous">Stay anonymous</label>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <button type="button" class="btn btn-donate ms-2" id="continueToStep2">Continue</button>
                                </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Step 2: Payment Details -->
                    <div id="donateStep2" class="donate-step">
                    <div class="donate-title">DONATE</div>
                    
                    <div class="payment-summary mt-4">

                        <div class="back-to-step1">
                            <button type="button" class="btn-back" id="backToStep1">
                                <i class="fas fa-arrow-left"></i>
                                Final Details
                            </button>
                        </div>
                        <hr class="my-2">


                            <div class="d-flex justify-content-between mb-2 mt-5">
                                <span class="fw-bold">Donation</span>
                                <span id="displayDonationAmount" class="fw-bold">$25</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Credit card Processing fees</span>
                                <span id="processingFees" class="fw-bold">$0.00</span>
                            </div>
                            <div class="payment-method mb-3">
                                <select class="form-select" id="paymentMethod" style="background-color: #c6a15a;">
                                    <!--<option>Select Payment Method</option>-->
                                    <option value="card" selected="">Stripe</option>
                                </select>
                            </div>
                            <hr class="my-2">

                            <div class="fee-info small text-muted mb-4 mt-5">
                                You pay the CC fee so 100% of your donation goes to your chosen missionary or cause.
                            </div>

                            <div class="tip-section p-3 mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Add a tip to support Night Bright</span>
                                    <div class="tip-selector">
                                        <select class="form-select form-select-sm" id="tipPercentage">
                                            <option value="12">12%</option>
                                            <option value="10">10%</option>
                                            <option value="15">15%</option>
                                            <option value="0">0%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tip-info small">
                                    Why Tip? Night Bright does not charge any platform fees and relies on your generosity to support this free service.
                                </div>
                            </div>

                            <div class="contact-permission mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="contactPermission">
                                    <label class="form-check-label small" for="contactPermission">
                                        Allow Night Bright Inc. to contact me
                                    </label>
                                </div>
                            </div>
                            
                            <form id="stripeForm" action="{{ route('donation.checkout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="name" id="formName">
                                <input type="hidden" name="email" id="formEmail">
                                <input type="hidden" name="amount" id="formAmount">
                                <input type="hidden" name="organization" id="formOrganization" value="Night Bright">
                                <input type="hidden" name="donation_type" id="formDonationType" value="one-time">
                                <input type="hidden" name="tip_percentage" id="formTipPercentage" value="12">
                                <input type="hidden" name="message" id="formMessage">
                                <input type="hidden" name="stay_anonymous" id="formStayAnonymous" value="0">
                                <input type="hidden" name="contact_permission" id="formContactPermission" value="0">
                                <input type="hidden" name="payment_method" id="formPaymentMethod" value="card">
                                <hr class="my-2">

                                <button type="submit" class="btn btn-donate-2" id="finishDonation">Finish (<span id="totalAmount">$28.00</span>)</button>
                            </form>
                        </div>
                    </div>
                </aside>

</main>

@endsection