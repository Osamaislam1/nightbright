// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Sidebar image switch functionality
    const sidebarThumbs = document.querySelectorAll('.sidebar-thumb');
    const mainImage = document.getElementById('mainImage');

    sidebarThumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const newSrc = this.getAttribute('data-main');
            mainImage.src = newSrc;
            mainImage.alt = this.alt;

            sidebarThumbs.forEach(img => img.classList.remove('active-sidebar'));
            this.classList.add('active-sidebar');
        });
    });
    if (sidebarThumbs.length > 0) {
        sidebarThumbs[0].classList.add('active-sidebar');
    }

    // --- Donation Sidebar Widget Logic ---
    const donateSidebar = document.getElementById('donateSidebar');
    const donateSidebarOverlay = document.getElementById('donateSidebarOverlay');
    const donateCloseBtn = document.getElementById('donateCloseBtn');
    const donateBtn = document.querySelector('.donate-btn');
    const donateStep1 = document.getElementById('donateStep1');
    const donateStep2 = document.getElementById('donateStep2');
    const donateStep3 = document.getElementById('donateStep3');
    const continueToStep2 = document.getElementById('continueToStep2');
    const backToStep1 = document.getElementById('backToStep1');
    const addMessageBtn = document.getElementById('addMessageBtn');
    const messageInput = document.getElementById('messageInput');
    const amountButtons = document.querySelectorAll('.amount-btn');
    const donationTypeButtons = document.querySelectorAll('.donate-tabs .donate-tab');
    const paymentMethodSelect = document.getElementById('paymentMethod');
    const tipPercentageSelect = document.getElementById('tipPercentage');
    const finishDonation = document.getElementById('finishDonation');
    const stripeForm = document.getElementById('stripeForm');

    // Form fields
    const donorName = document.getElementById('donorName');
    const donorEmail = document.getElementById('donorEmail');
    const donationOrg = document.getElementById('donationOrg');
    const otherAmountInput = document.getElementById('otherAmount');
    const stayAnonymous = document.getElementById('stayAnonymous');
    const contactPermission = document.getElementById('contactPermission');

    // Hidden form fields
    const formName = document.getElementById('formName');
    const formEmail = document.getElementById('formEmail');
    const formAmount = document.getElementById('formAmount');
    const formOrganization = document.getElementById('formOrganization');
    const formDonationType = document.getElementById('formDonationType');
    const formTipPercentage = document.getElementById('formTipPercentage');
    const formMessage = document.getElementById('formMessage');
    const formStayAnonymous = document.getElementById('formStayAnonymous');
    const formContactPermission = document.getElementById('formContactPermission');
    const formPaymentMethod = document.getElementById('formPaymentMethod');

    // Display elements
    const displayDonationAmount = document.getElementById('displayDonationAmount');
    const processingFees = document.getElementById('processingFees');
    const totalAmount = document.getElementById('totalAmount');

    // Variables
    let selectedAmount = 25;
    let selectedDonationType = 'one-time';
    let selectedTipPercentage = 12;

    // Toggle donation sidebar
    if (donateBtn) {
        donateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            donateSidebar.classList.add('active');
            donateSidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close donation sidebar
    [donateCloseBtn, donateSidebarOverlay].forEach(el => {
        if (el) {
            el.addEventListener('click', () => {
                donateSidebar.classList.remove('active');
                donateSidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
                showStep(1);
            });
        }
    });

    // Navigation between steps
    function showStep(stepNum) {
        if (stepNum === 1) {
            donateStep1.classList.add('active');
            donateStep2.classList.remove('active');
            donateStep3.classList.remove('active');
        } else if (stepNum === 2) {
            donateStep1.classList.remove('active');
            donateStep2.classList.add('active');
            donateStep3.classList.remove('active');
            updateTotals();
        } else {
            donateStep1.classList.remove('active');
            donateStep2.classList.remove('active');
            donateStep3.classList.add('active');
        }
    }

    if (continueToStep2) {
        continueToStep2.addEventListener('click', function() {
            if (!validateStep1()) return;
            displayDonationAmount.textContent = '$' + selectedAmount.toFixed(2);
            updateTotals();
            showStep(2);
        });
    }

    if (backToStep1) {
        backToStep1.addEventListener('click', () => showStep(1));
    }

    // Toggle message input
    if (addMessageBtn) {
        addMessageBtn.addEventListener('click', function() {
            if (messageInput.style.display === 'none') {
                messageInput.style.display = 'block';
                addMessageBtn.textContent = '- Remove message';
            } else {
                messageInput.style.display = 'none';
                messageInput.value = '';
                addMessageBtn.textContent = '+ Add a message';
            }
        });
    }

    // Handle donation amount buttons
    if (amountButtons.length) {
        amountButtons.forEach(button => {
            if (button.tagName === 'BUTTON') {
                button.addEventListener('click', function() {
                    if (this === otherAmountInput) return;
                    amountButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    selectedAmount = parseInt(this.getAttribute('data-amount'), 10);
                    otherAmountInput.value = '';
                });
            }
        });

        // "Other" amount input
        if (otherAmountInput) {
            otherAmountInput.addEventListener('focus', function() {
                amountButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
            otherAmountInput.addEventListener('input', function() {
                if (this.value && !isNaN(this.value)) {
                    selectedAmount = parseFloat(this.value);
                }
            });
        }
    }

    // Handle donation type tabs
    if (donationTypeButtons.length) {
        donationTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                donationTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                selectedDonationType = this.getAttribute('data-type');
            });
        });
    }

    // Tip percentage change
    if (tipPercentageSelect) {
        tipPercentageSelect.addEventListener('change', function() {
            selectedTipPercentage = parseInt(this.value, 10);
            updateTotals();
        });
    }

    // Payment method change
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            formPaymentMethod.value = this.value;
            updateTotals();
        });
    }

    // Calculate tip amount
    function calculateTip(amount, percentage) {
        return parseFloat((amount * percentage / 100).toFixed(2));
    }

    // Cache for storing fee rates
    const feeRateCache = {
        lastUpdated: 0,
        rates: {}
    };

    // Calculate processing fee based on payment method
    async function calculateProcessingFee(amount) {
        const method = paymentMethodSelect.value;
        let cardBrand = 'visa';
        let paymentType = 'card';

        switch (method) {
            case 'amex':
                cardBrand = 'amex';
                break;
            case 'usbank':
                paymentType = 'us_bank_account';
                break;
            case 'cashapp':
                paymentType = 'cashapp';
                break;
        }

        const cacheKey = `${paymentType}_${cardBrand}`;
        const now = Date.now();

        if (!feeRateCache.rates[cacheKey] || now - feeRateCache.lastUpdated > 3600000) {
            try {
                const res = await fetch(`/api/stripe/processing-fees?payment_method=${paymentType}&card_brand=${cardBrand}`);
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Failed to get fee rates');
                feeRateCache.rates[cacheKey] = data.fee_rate;
                feeRateCache.lastUpdated = now;
            } catch (err) {
                console.error('Error fetching fees, using fallback rates:', err);
                // Fallback hardcoded
                const fallbackRates = {
                    amex: {
                        percentage: 3.5,
                        fixed: 0.30
                    },
                    visa: {
                        percentage: 2.9,
                        fixed: 0.30
                    },
                    us_bank_account: {
                        percentage: 0.8,
                        fixed: 0.30
                    },
                    cashapp: {
                        percentage: 2.5,
                        fixed: 0.30
                    }
                };
                feeRateCache.rates[cacheKey] = fallbackRates[cardBrand] || fallbackRates['visa'];
                feeRateCache.lastUpdated = now;
            }
        }

        const rate = feeRateCache.rates[cacheKey];
        return parseFloat(((amount * rate.percentage / 100) + rate.fixed).toFixed(2));
    }

    // Update total calculations
    async function updateTotals() {
        const amount = selectedAmount || 0;
        const fee = await calculateProcessingFee(amount);
        const tip = calculateTip(amount, selectedTipPercentage);
        const total = amount + fee + tip;

        displayDonationAmount.textContent = `$${amount.toFixed(2)}`;
        processingFees.textContent = `$${fee.toFixed(2)}`;
        totalAmount.textContent = `$${total.toFixed(2)}`;

        formTipPercentage.value = selectedTipPercentage;
    }

    // Validate step 1 inputs
    function validateStep1() {
        let isValid = true;
        let msg = '';

        if (!stayAnonymous.checked && (!donorName.value.trim())) {
            isValid = false;
            msg += 'Please enter your name or check "Stay anonymous".\n';
        }
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(donorEmail.value)) {
            isValid = false;
            msg += 'Please enter a valid email address.\n';
        }
        if (!selectedAmount || selectedAmount <= 0) {
            isValid = false;
            msg += 'Please select or enter a valid donation amount.\n';
        }
        if (!isValid) alert(msg);
        return isValid;
    }

    // Show loading step
    function showLoadingStep() {
        showStep(3);
    }

    // Handle form submission
    if (stripeForm) {
        stripeForm.addEventListener('submit', async function(e) {
            formName.value = stayAnonymous.checked ? 'Anonymous' : donorName.value;
            formEmail.value = donorEmail.value;
            formAmount.value = selectedAmount;
            formOrganization.value = donationOrg.value || 'Night Bright';
            formDonationType.value = selectedDonationType;
            formMessage.value = messageInput.value || '';
            formStayAnonymous.value = stayAnonymous.checked ? '1' : '0';
            formContactPermission.value = contactPermission.checked ? '1' : '0';
            formPaymentMethod.value = paymentMethodSelect.value;

            const fee = await calculateProcessingFee(selectedAmount);
            const tip = calculateTip(selectedAmount, selectedTipPercentage);

            // Ensure hidden inputs exist
            if (!document.getElementById('formProcessingFee')) {
                const feeInput = document.createElement('input');
                feeInput.type = 'hidden';
                feeInput.name = 'processing_fee';
                feeInput.id = 'formProcessingFee';
                stripeForm.appendChild(feeInput);
            }
            if (!document.getElementById('formTipAmount')) {
                const tipInput = document.createElement('input');
                tipInput.type = 'hidden';
                tipInput.name = 'tip_amount';
                tipInput.id = 'formTipAmount';
                stripeForm.appendChild(tipInput);
            }
            document.getElementById('formProcessingFee').value = fee.toFixed(2);
            document.getElementById('formTipAmount').value = tip.toFixed(2);

            // Final validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(donorEmail.value) || !selectedAmount || selectedAmount <= 0) {
                e.preventDefault();
                alert('Please go back and correct your email or donation amount.');
                return false;
            }

            showLoadingStep();
            return true;
        });
    }
});