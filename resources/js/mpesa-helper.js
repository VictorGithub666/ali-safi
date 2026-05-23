/**
 * Ali-Safi M-Pesa Payment Helper
 * Provides utility functions for M-Pesa STK Push payments
 */

const AliSafiMpesa = {
    /**
     * Initialize M-Pesa payment for an order
     */
    initializePayment: async function(orderId, phoneNumber, totalAmount, callbackUrl = null) {
        try {
            // Validate phone number
            if (!this.validatePhoneNumber(phoneNumber)) {
                throw new Error('Invalid phone number format. Use 254XXXXXXXXX or 0XXXXXXXXX');
            }

            // Show loading state
            this.showLoadingState('Processing M-Pesa payment...');

            const response = await fetch(`/customer/orders/${orderId}/mpesa/initiate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value || this.getCsrfToken()
                },
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    amount: totalAmount
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to initiate M-Pesa payment');
            }

            // Show success message
            this.showSuccessMessage(
                'M-Pesa prompt sent successfully!',
                'Check your phone for the M-Pesa STK Push prompt and enter your PIN.'
            );

            // Poll for payment status
            if (data.transaction_id) {
                this.pollPaymentStatus(orderId, data.transaction_id, 120); // Poll for 2 minutes
            }

            return data;

        } catch (error) {
            console.error('M-Pesa Error:', error);
            this.showErrorMessage('M-Pesa Payment Error', error.message);
            throw error;
        }
    },

    /**
     * Validate phone number format
     */
    validatePhoneNumber: function(phone) {
        // Remove any spaces or special characters
        const cleaned = phone.replace(/\D/g, '');

        // Check if it's a valid Kenyan Safaricom number
        if (cleaned.length === 9 && cleaned.startsWith('7')) {
            // Format: 7XXXXXXXX
            return true;
        } else if (cleaned.length === 10 && cleaned.startsWith('07')) {
            // Format: 07XXXXXXXX
            return true;
        } else if (cleaned.length === 10 && cleaned.startsWith('7')) {
            // Format: 7XXXXXXXXX
            return true;
        } else if (cleaned.length === 12 && cleaned.startsWith('254')) {
            // Format: 254XXXXXXXXX
            return true;
        }

        return false;
    },

    /**
     * Format phone number to standard format (254XXXXXXXXX)
     */
    formatPhoneNumber: function(phone) {
        let cleaned = phone.replace(/\D/g, '');

        // Convert to 254 format
        if (cleaned.length === 9 && cleaned.startsWith('7')) {
            cleaned = '254' + cleaned;
        } else if (cleaned.length === 10 && cleaned.startsWith('0')) {
            cleaned = '254' + cleaned.substring(1);
        } else if (cleaned.length === 10 && cleaned.startsWith('7')) {
            cleaned = '254' + cleaned;
        }

        return cleaned;
    },

    /**
     * Check payment status
     */
    checkPaymentStatus: async function(orderId) {
        try {
            const response = await fetch(`/customer/orders/${orderId}/mpesa/status`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to check payment status');
            }

            return data;

        } catch (error) {
            console.error('Status Check Error:', error);
            throw error;
        }
    },

    /**
     * Poll for payment status at intervals
     */
    pollPaymentStatus: function(orderId, transactionId, maxSeconds = 120) {
        let elapsedSeconds = 0;
        const pollInterval = 5000; // Poll every 5 seconds

        const poll = setInterval(async () => {
            try {
                const status = await this.checkPaymentStatus(orderId);

                // If payment is completed
                if (status.payment_status === 'paid' || status.status === 'completed') {
                    clearInterval(poll);
                    this.showSuccessMessage(
                        'Payment Successful!',
                        'Your order has been confirmed and will be processed shortly.',
                        () => {
                            // Redirect to order confirmation page
                            window.location.href = `/customer/orders/${orderId}`;
                        }
                    );
                    return;
                }

                elapsedSeconds += pollInterval / 1000;

                // Stop polling after max seconds
                if (elapsedSeconds >= maxSeconds) {
                    clearInterval(poll);
                    this.showWarningMessage(
                        'Payment Pending',
                        'Your payment is still pending. We will notify you once it\'s confirmed.',
                        () => {
                            window.location.href = `/customer/orders/${orderId}`;
                        }
                    );
                }

            } catch (error) {
                // Continue polling even if there's an error
                console.error('Error checking payment status:', error);
            }
        }, pollInterval);
    },

    /**
     * Resend M-Pesa prompt
     */
    resendPrompt: async function(orderId) {
        try {
            const response = await fetch(`/customer/orders/${orderId}/mpesa/resend`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to resend M-Pesa prompt');
            }

            this.showSuccessMessage(
                'Prompt Resent',
                'Check your phone for a new M-Pesa STK Push prompt.'
            );

            return data;

        } catch (error) {
            console.error('Resend Error:', error);
            this.showErrorMessage('Resend Failed', error.message);
            throw error;
        }
    },

    /**
     * Get CSRF token from meta tag or cookie
     */
    getCsrfToken: function() {
        // Try meta tag first
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }

        // Try from input field
        const inputToken = document.querySelector('input[name="_token"]');
        if (inputToken) {
            return inputToken.value;
        }

        // Try from cookie
        const cookieToken = this.getCookie('XSRF-TOKEN');
        if (cookieToken) {
            return decodeURIComponent(cookieToken);
        }

        throw new Error('CSRF token not found');
    },

    /**
     * Get cookie value
     */
    getCookie: function(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return null;
    },

    /**
     * Show loading message
     */
    showLoadingState: function(message = 'Processing...') {
        const alertDiv = document.createElement('div');
        alertDiv.id = 'mpesaAlert';
        alertDiv.className = 'alert alert-info alert-dismissible fade show';
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm text-info me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <strong>${message}</strong>
                    <p class="mb-0">Do not refresh the page. Check your phone for the M-Pesa prompt.</p>
                </div>
            </div>
        `;

        this.insertAlert(alertDiv);
    },

    /**
     * Show success message
     */
    showSuccessMessage: function(title = 'Success', message = '', callback = null) {
        this.showAlert('success', title, message, callback);
    },

    /**
     * Show error message
     */
    showErrorMessage: function(title = 'Error', message = '', callback = null) {
        this.showAlert('danger', title, message, callback);
    },

    /**
     * Show warning message
     */
    showWarningMessage: function(title = 'Warning', message = '', callback = null) {
        this.showAlert('warning', title, message, callback);
    },

    /**
     * Generic alert display
     */
    showAlert: function(type, title, message, callback = null) {
        const alertDiv = document.createElement('div');
        alertDiv.id = 'mpesaAlert';
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            <strong>${title}</strong>
            ${message ? `<p class="mb-0">${message}</p>` : ''}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        this.insertAlert(alertDiv);

        if (callback) {
            alertDiv.addEventListener('closed.bs.alert', callback);
        }
    },

    /**
     * Insert alert at the top of the page
     */
    insertAlert: function(alertDiv) {
        // Remove existing alert
        const existing = document.getElementById('mpesaAlert');
        if (existing) {
            existing.remove();
        }

        // Find the main container and insert alert
        const container = document.querySelector('.container') || document.body;
        container.insertAdjacentElement('afterbegin', alertDiv);

        // Scroll to alert
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    /**
     * Format amount as currency
     */
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-KE', {
            style: 'currency',
            currency: 'KES'
        }).format(amount);
    },

    /**
     * Create payment button
     */
    createPaymentButton: function(orderId, totalAmount, options = {}) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = options.className || 'btn btn-success btn-lg';
        button.innerHTML = `<i class="bi bi-phone me-2"></i> Pay with M-Pesa`;

        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const phoneInput = document.querySelector(options.phoneSelector || '#mpesaPhone');
            if (!phoneInput) {
                this.showErrorMessage('Error', 'Phone number field not found');
                return;
            }

            const phone = phoneInput.value.trim();

            if (!phone) {
                this.showErrorMessage('Error', 'Please enter your phone number');
                return;
            }

            try {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';

                await this.initializePayment(orderId, phone, totalAmount);

                button.disabled = false;
                button.innerHTML = `<i class="bi bi-phone me-2"></i> Pay with M-Pesa`;
            } catch (error) {
                button.disabled = false;
                button.innerHTML = `<i class="bi bi-phone me-2"></i> Pay with M-Pesa`;
            }
        });

        return button;
    }
};

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AliSafiMpesa;
}
