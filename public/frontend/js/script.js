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
            // Change main image src
            const newSrc = this.getAttribute('data-main');
            mainImage.setAttribute('src', newSrc);
            mainImage.setAttribute('alt', this.getAttribute('alt'));

            // Highlight selected sidebar
            sidebarThumbs.forEach(img => img.classList.remove('active-sidebar'));
            this.classList.add('active-sidebar');
        });
    });
    if(sidebarThumbs.length > 0) sidebarThumbs[0].classList.add('active-sidebar');

    // --- Donation Sidebar Widget Logic ---
    // Elements
    const donateSidebar = document.getElementById('donateSidebar');
    const donateSidebarOverlay = document.getElementById('donateSidebarOverlay');
    const donateCloseBtn = document.getElementById('donateCloseBtn');
    const donateBtn = document.querySelector('.donate-btn');
    const donateStep1 = document.getElementById('donateStep1');
    const donateStep2 = document.getElementById('donateStep2');
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
    if (donateCloseBtn) {
        donateCloseBtn.addEventListener('click', function() {
            donateSidebar.classList.remove('active');
            donateSidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
            // Reset to step 1 when closing
            showStep(1);
        });
    }

    if (donateSidebarOverlay) {
        donateSidebarOverlay.addEventListener('click', function() {
            donateSidebar.classList.remove('active');
            donateSidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
            // Reset to step 1 when closing
            showStep(1);
        });
    }
    
    // Navigation between steps
    function showStep(stepNum) {
        if (stepNum === 1) {
            donateStep1.classList.add('active');
            donateStep2.classList.remove('active');
        } else {
            donateStep1.classList.remove('active');
            donateStep2.classList.add('active');
            updateTotals();
        }
    }
    
    if (continueToStep2) {
        continueToStep2.addEventListener('click', function() {
            // Validate step 1
            if (!validateStep1()) {
                return;
            }
            
            // Transfer data to step 2
            displayDonationAmount.textContent = '$' + selectedAmount;
            updateTotals();
            
            // Show step 2
            showStep(2);
        });
    }
    
    if (backToStep1) {
        backToStep1.addEventListener('click', function() {
            showStep(1);
        });
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
                    // Skip for the "Other" amount input field
                    if (this === otherAmountInput) return;
                    
                    // Remove active class from all buttons
                    amountButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Set the donation amount
                    selectedAmount = parseInt(this.getAttribute('data-amount'));
                    
                    // Clear the "Other" amount input
                    if (otherAmountInput) {
                        otherAmountInput.value = '';
                    }
                });
            }
        });
        
        // Handle "Other" amount input
        if (otherAmountInput) {
            otherAmountInput.addEventListener('focus', function() {
                amountButtons.forEach(btn => {
                    if (btn.tagName === 'BUTTON') btn.classList.remove('active');
                });
                this.classList.add('active');
            });
            
            otherAmountInput.addEventListener('input', function() {
                if (this.value && !isNaN(this.value)) {
                    // Set the donation amount to the custom value
                    selectedAmount = parseFloat(this.value);
                }
            });
        }
    }
    
    // Handle donation type tabs
    if (donationTypeButtons.length) {
        donationTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                donationTypeButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Set the donation type
                selectedDonationType = this.getAttribute('data-type');
            });
        });
    }
    
    // Handle tip percentage change
    if (tipPercentageSelect) {
        tipPercentageSelect.addEventListener('change', function() {
            selectedTipPercentage = parseInt(this.value);
            updateTotals();
        });
    }
    
    // Handle payment method change
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            formPaymentMethod.value = this.value;
        });
    }
    
    // Update total calculations
    function updateTotals() {
        // Calculate processing fee (2.9% + $0.30)
        const fee = calculateProcessingFee(selectedAmount);
        processingFees.textContent = '$' + fee.toFixed(2);
        
        // Calculate tip amount
        const tipAmount = calculateTip(selectedAmount, selectedTipPercentage);
        
        // Calculate total amount
        const total = parseFloat(selectedAmount) + fee + tipAmount;
        totalAmount.textContent = '$' + total.toFixed(2);
        
        // Update hidden form fields
        formTipPercentage.value = selectedTipPercentage;
    }
    
    // Calculate processing fee
    function calculateProcessingFee(amount) {
        return parseFloat(((amount * 0.029) + 0.30).toFixed(2));
    }
    
    // Calculate tip amount
    function calculateTip(amount, percentage) {
        return parseFloat((amount * percentage / 100).toFixed(2));
    }
    
    // Validate step 1
    function validateStep1() {
        let isValid = true;
        let errorMessage = '';
        
        // Check if name is provided (if not anonymous)
        if (!stayAnonymous.checked && (!donorName.value || donorName.value.trim() === '')) {
            isValid = false;
            errorMessage += 'Please enter your name or check "Stay anonymous".\n';
        }
        
        // Check if email is provided and valid
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!donorEmail.value || !emailPattern.test(donorEmail.value)) {
            isValid = false;
            errorMessage += 'Please enter a valid email address.\n';
        }
        
        // Check if a donation amount is selected
        if (!selectedAmount || selectedAmount <= 0) {
            isValid = false;
            errorMessage += 'Please select or enter a valid donation amount.\n';
        }
        
        // Show error message if validation fails
        if (!isValid) {
            alert(errorMessage);
        }
        
        return isValid;
    }
    
    // Handle form submission
    if (stripeForm) {
        stripeForm.addEventListener('submit', function(e) {
            // Set hidden form values from step 1 and step 2
            formName.value = stayAnonymous.checked ? 'Anonymous' : donorName.value;
            formEmail.value = donorEmail.value;
            formAmount.value = selectedAmount;
            formOrganization.value = donationOrg.value || 'Night Bright';
            formDonationType.value = selectedDonationType;
            formMessage.value = messageInput.value || '';
            formStayAnonymous.value = stayAnonymous.checked ? '1' : '0';
            formContactPermission.value = contactPermission.checked ? '1' : '0';
            
            // Final validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!donorEmail.value || !emailPattern.test(donorEmail.value)) {
                e.preventDefault();
                alert('Please go back and enter a valid email address.');
                return false;
            }
            
            if (!selectedAmount || selectedAmount <= 0) {
                e.preventDefault();
                alert('Please go back and select a valid donation amount.');
                return false;
            }
            
            // Form is valid, submit to Stripe
            return true;
        });
    }
});
