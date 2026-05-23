# Ali-Safi M-Pesa Implementation Guide

## Overview

This document provides a comprehensive guide to the M-Pesa payment system implemented in the Ali-Safi platform. The system enables customers to make instant payments using M-Pesa STK Push, while providing admins with a complete dashboard to monitor and manage all M-Pesa transactions.

## Features Implemented

### 1. **Customer-Side Features**

#### Checkout Payment Integration
- M-Pesa payment option on the checkout page
- Phone number input with automatic formatting
- Real-time validation of Safaricom phone numbers
- Support for multiple phone number formats:
  - `254712345678` (International format)
  - `0712345678` (Local format)
  - `712345678` (Short format)

#### M-Pesa Payment Flow
1. Customer enters their M-Pesa phone number during checkout
2. Upon order confirmation, an STK Push prompt is sent to the customer's phone
3. Customer enters their M-Pesa PIN to complete payment
4. Payment status is automatically updated in real-time
5. Customer receives order confirmation upon successful payment

#### Payment Status Monitoring
- Real-time polling of payment status (every 5 seconds)
- Automatic redirect upon successful payment
- Support for manual prompt resending
- Clear status messages for all scenarios (pending, successful, failed, cancelled)

### 2. **Admin-Side Features**

#### M-Pesa Dashboard
**Route:** `/admin/mpesa/dashboard`

Key Statistics:
- Total revenue from completed transactions
- Total number of transactions
- Completion rate percentage
- Breakdown by status (pending, completed, failed, cancelled)

Charts & Visualizations:
- Daily revenue chart (line chart)
- Status distribution (doughnut chart)
- Top performing phone numbers (table)

Date filtering options for custom reports.

#### Transaction Management
**Route:** `/admin/mpesa/transactions`

Features:
- Complete list of all M-Pesa transactions
- Filter by:
  - Status (pending, completed, failed, cancelled)
  - Phone number
  - Amount range
  - Date range
- Transaction details including:
  - Transaction ID
  - Related order number
  - Customer information
  - Phone number
  - Amount
  - M-Pesa receipt number
  - Status badges

Actions:
- View detailed transaction information
- Confirm payment (admin override for manual confirmation)
- Export transactions to CSV

#### Transaction Details View
**Route:** `/admin/mpesa/transactions/{id}`

Shows:
- Transaction summary
- All callback response data
- Timeline of events
- Related order information
- Customer details
- Admin override options

#### Notifications Center
**Route:** `/admin/mpesa/notifications`

Real-time notifications for:
- Successful payments
- Failed payments
- Cancelled transactions
- Daily summary statistics

Features:
- Filter by notification type
- Date range filtering
- Direct links to transaction and order details
- Color-coded status indicators

### 3. **Database**

#### New Table: `mpesa_transactions`

```sql
CREATE TABLE mpesa_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT
    order_id BIGINT NOT NULL (Foreign Key to orders)
    checkout_request_id VARCHAR(255) UNIQUE
    merchant_request_id VARCHAR(255) NULLABLE
    phone_number VARCHAR(20)
    amount DECIMAL(12, 2)
    currency VARCHAR(3) DEFAULT 'KES'
    status ENUM('pending', 'completed', 'failed', 'cancelled')
    mpesa_receipt_number VARCHAR(255) NULLABLE
    result_code VARCHAR(10) NULLABLE
    result_description TEXT NULLABLE
    callback_response LONGTEXT NULLABLE (JSON)
    initiated_at TIMESTAMP
    completed_at TIMESTAMP NULLABLE
    created_at TIMESTAMP
    updated_at TIMESTAMP
    
    INDEXES: phone_number, status, created_at
)
```

#### Order Model Updates

Added relationships:
- `mpesaTransactions()` - One-to-many relationship
- `latestMpesaTransaction()` - Get latest M-Pesa transaction

## API Endpoints

### Customer Endpoints

#### Initiate M-Pesa Payment
```
POST /customer/orders/{orderId}/mpesa/initiate
Content-Type: application/json

{
    "phone_number": "254712345678"
}

Response:
{
    "success": true,
    "message": "M-Pesa prompt sent successfully...",
    "transaction_id": 123
}
```

#### Check Payment Status
```
GET /customer/orders/{orderId}/mpesa/status

Response:
{
    "success": true,
    "status": "completed",
    "payment_status": "paid",
    "receipt": "MPO1234567",
    "message": "Payment successful"
}
```

#### Resend M-Pesa Prompt
```
POST /customer/orders/{orderId}/mpesa/resend

Response:
{
    "success": true,
    "message": "M-Pesa prompt resent successfully"
}
```

### M-Pesa Callback
```
POST /mpesa/callback
(No authentication required - Safaricom webhook)
```

### Admin Endpoints

#### M-Pesa Dashboard
```
GET /admin/mpesa/dashboard?date_from=2026-05-01&date_to=2026-05-31
```

#### List Transactions
```
GET /admin/mpesa/transactions
GET /admin/mpesa/transactions?status=completed&phone=254&date_from=2026-05-01
```

#### View Transaction Details
```
GET /admin/mpesa/transactions/{transactionId}
```

#### Notifications
```
GET /admin/mpesa/notifications
GET /admin/mpesa/notifications?type=completed&date_from=2026-05-01
```

#### Export Transactions
```
GET /admin/mpesa/export?status=completed&date_from=2026-05-01
```

#### Confirm Payment (Admin Override)
```
POST /admin/mpesa/transactions/{transactionId}/confirm
```

## M-Pesa Credentials Configuration

Located in `.env`:
```env
# M-Pesa Configuration
MPESA_ENVIRONMENT=sandbox
MPESA_CONSUMER_KEY=zGTwjnaMDudPRFvm6ypTaAIFWZ0PWxg0InITV0BQ5t50mDTh
MPESA_CONSUMER_SECRET=W5QCjP5BB60x1uVZS832Rzet0SezmoEMTgJY5UJu3pJMCkP2rmxBr025NsKZx118
MPESA_SHORTCODE=174379
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_COMMAND_ID=CustomerPayBillOnline
MPESA_ACCOUNT_REFERENCE=ALISAFI
MPESA_TRANSACTION_DESC="AliSafi Order"
```

### Environment Setup

For **Production**, change:
```env
MPESA_ENVIRONMENT=production
```

For **Sandbox Testing**, use the current configuration.

## JavaScript Helper Usage

The `AliSafiMpesa` object provides utility functions for M-Pesa integration:

### Import
```html
<script src="{{ asset('js/mpesa-helper.js') }}"></script>
```

### Basic Usage
```javascript
// Initialize payment
await AliSafiMpesa.initializePayment(
    orderId,
    phoneNumber,
    totalAmount
);

// Check payment status
const status = await AliSafiMpesa.checkPaymentStatus(orderId);

// Validate phone number
const isValid = AliSafiMpesa.validatePhoneNumber('254712345678');

// Format phone number
const formatted = AliSafiMpesa.formatPhoneNumber('0712345678');
```

### Available Methods

- `initializePayment(orderId, phoneNumber, amount)` - Start M-Pesa payment
- `checkPaymentStatus(orderId)` - Get current payment status
- `resendPrompt(orderId)` - Resend M-Pesa STK Push
- `validatePhoneNumber(phone)` - Validate phone format
- `formatPhoneNumber(phone)` - Standardize phone format
- `pollPaymentStatus(orderId, transactionId, maxSeconds)` - Poll status at intervals
- `showSuccessMessage(title, message, callback)` - Display success alert
- `showErrorMessage(title, message, callback)` - Display error alert
- `createPaymentButton(orderId, amount, options)` - Create styled payment button

## Architecture

### Services

**MpesaService** (`app/Services/MpesaService.php`):
- Handles all M-Pesa API communication
- Token management and authentication
- STK Push initiation
- Transaction queries
- Callback handling

### Models

**MpesaTransaction** (`app/Models/MpesaTransaction.php`):
- Stores all M-Pesa transaction data
- Relationships to Order model
- Query scopes for status filtering
- Helper methods for status updates

### Controllers

**PaymentController** (`app/Http/Controllers/PaymentController.php`):
- `initiateMpesaPayment()` - Start payment process
- `mpesaCallback()` - Handle M-Pesa webhooks
- `resendMpesaPrompt()` - Retry STK Push
- `getPaymentStatus()` - Check payment status

**AdminMpesaController** (`app/Http/Controllers/Admin/AdminMpesaController.php`):
- `index()` - List transactions
- `show()` - Transaction details
- `dashboard()` - Statistics dashboard
- `notifications()` - Recent updates
- `export()` - CSV export
- `confirmPayment()` - Admin override

## Testing

### Sandbox Testing

1. Create test account on Safaricom Developer Portal
2. Get sandbox credentials
3. Update `.env` with sandbox credentials
4. Test with sandbox phone numbers:
   - Successful: `254708374149`
   - Insufficient funds: `254709999999`

### Test Scenarios

**Successful Payment:**
- Phone: `254712345678` (Sandbox)
- Amount: Any amount
- Expected: STK Push prompt, payment completion

**Failed Payment:**
- Check network connection
- Verify M-Pesa is active on the number
- Check account balance in sandbox

**Admin Testing:**
- Access `/admin/mpesa/dashboard`
- View transactions list
- Test manual confirmation
- Check CSV export

## Troubleshooting

### Payment Not Completing

1. **Check Phone Number Format**
   ```
   Valid formats:
   - 254712345678
   - 0712345678
   - 712345678
   ```

2. **Verify M-Pesa Active**
   - Ensure account has M-Pesa registered
   - Check MPESA PIN is set

3. **Check Logs**
   ```
   storage/logs/laravel.log
   ```

4. **Verify Credentials**
   ```
   MPESA_CONSUMER_KEY
   MPESA_CONSUMER_SECRET
   MPESA_SHORTCODE
   MPESA_PASSKEY
   ```

### Webhook Not Received

1. Ensure callback URL is HTTPS in production
2. Check firewall rules allow Safaricom IPs
3. Verify route is publicly accessible
4. Check logs for callback errors

### Database Issues

1. Run migration:
   ```bash
   php artisan migrate
   ```

2. Verify table structure:
   ```bash
   php artisan migrate:status
   ```

## Security Considerations

1. **Phone Number Validation**
   - All phone numbers validated against Kenyan format
   - Stored with encryption flag

2. **CSRF Protection**
   - All payment endpoints protected by CSRF token
   - Admin endpoints require authentication

3. **Amount Validation**
   - Server-side validation of amounts
   - Prevention of manipulation

4. **Callback Security**
   - Webhook IP validation (recommended)
   - Callback signature verification
   - Idempotency checks

5. **Data Privacy**
   - Phone numbers stored securely
   - PCI compliance considerations
   - GDPR compliance for EU users

## Next Steps

1. **Run Migration:**
   ```bash
   php artisan migrate
   ```

2. **Test Checkout Flow:**
   - Go to customer checkout page
   - Select M-Pesa payment
   - Enter phone number
   - Confirm order

3. **Check Admin Dashboard:**
   - Access `/admin/mpesa/dashboard`
   - View transactions
   - Check notifications

4. **Production Deployment:**
   - Update `.env` with production credentials
   - Set `MPESA_ENVIRONMENT=production`
   - Update callback URL to HTTPS
   - Test with real transactions

## Support & Maintenance

### Regular Tasks

1. **Monitor Transactions**
   - Check daily completion rates
   - Review failed transactions
   - Follow up on pending payments

2. **Database Maintenance**
   - Archive old transactions
   - Optimize indexes
   - Monitor storage usage

3. **Security Updates**
   - Update M-Pesa credentials periodically
   - Review callback security
   - Monitor for API changes

### Contact Safaricom Support

- **Developer Portal:** https://developer.safaricom.co.ke
- **Documentation:** https://developer.safaricom.co.ke/apis
- **Support Email:** support@safaricom.co.ke

---

**Implementation Date:** May 23, 2026
**Version:** 1.0
**Status:** Production Ready

## ✅ What Was Implemented

### 1. **M-Pesa Service** (`app/Services/MpesaService.php`)
Complete M-Pesa Daraja API integration with:
- ✅ **OAuth Token Generation** - Authenticates with M-Pesa API
- ✅ **STK Push Initiation** - Sends M-Pesa prompt to customer's phone
- ✅ **Transaction Query** - Check payment status
- ✅ **Phone Number Validation** - Ensures format: 254XXXXXXXXX
- ✅ **Callback Handling** - Processes payment confirmation
- ✅ **Sandbox & Production Support** - Auto-switches based on APP_ENV

### 2. **Payment Controller** (`app/Http/Controllers/PaymentController.php`)
Handles:
- M-Pesa webhook callbacks
- Payment confirmation/failure processing
- Order status updates
- Transaction logging

### 3. **Order Controller Updates**
- Updated to use `MpesaService`
- Calls STK Push when M-Pesa payment selected
- Stores M-Pesa receipt reference
- Logs all payment transactions

### 4. **Configuration Files**
- `config/services.php` - M-Pesa API config
- `.env` - M-Pesa credentials (sandbox ready)
- `.env.example` - Setup template
- `MPESA_SETUP_GUIDE.md` - Complete setup documentation

### 5. **Routes**
- Added webhook route: `POST /mpesa/callback`
- Handles payment confirmations from M-Pesa

## 🔄 Complete Payment Flow

```
1. Customer at Checkout
   ↓
2. Selects M-Pesa Payment
   ↓
3. Enters Phone (254XXXXXXXXX)
   ↓
4. Places Order
   ↓
5. OrderController triggers sendMpesaPrompt()
   ↓
6. MpesaService.initiateStkPush() called
   ↓
7. Gets OAuth token from M-Pesa
   ↓
8. Sends STK Push with amount & order details
   ↓
9. M-Pesa Prompt appears on customer's phone
   ↓
10. Customer enters M-Pesa PIN
   ↓
11. M-Pesa approves/rejects payment
   ↓
12. Webhook callback to /mpesa/callback
   ↓
13. Order payment_status updated to 'paid'
   ↓
14. Order status updated to 'confirmed'
   ↓
15. Vendor notified of confirmed order
```

## 🚀 Quick Start (Testing)

### Step 1: Add M-Pesa Credentials
Edit `.env` and add test credentials:
```env
MPESA_CONSUMER_KEY=Your_Test_Key
MPESA_CONSUMER_SECRET=Your_Test_Secret
MPESA_BUSINESS_CODE=174379
MPESA_SHORTCODE=174379
MPESA_PASSKEY=test_passkey
```

### Step 2: Keep APP_ENV=local
This automatically uses sandbox: `https://sandbox.safaricom.co.ke`

### Step 3: Test Phone Numbers
Use format: `254XXXXXXXXX`
- `254712345678` ✅ Correct
- `0712345678` ✅ Auto-converted to 254712345678

### Step 4: Place Test Order
1. Add items to cart
2. Go to checkout
3. Select M-Pesa
4. Enter test phone number
5. Place order
6. Check logs for STK Push confirmation

## 📱 How Customer Sees It

### Before (Old Implementation)
- ❌ Just logged to file
- ❌ No actual prompt sent
- ❌ Customer saw nothing

### After (New Implementation)
- ✅ STK Prompt appears on phone immediately
- ✅ Customer sees payment request
- ✅ Customer can enter M-Pesa PIN
- ✅ Payment confirmed = Order auto-updates

## 🔐 Security Features

1. **Phone Number Validation**
   - Must start with 254 (Kenya country code)
   - Must be exactly 12 digits
   - Auto-converts 07xx format

2. **OAuth Authentication**
   - Token-based API authentication
   - Credentials not exposed in requests
   - Environment-based endpoint selection

3. **Webhook Security**
   - Callback endpoint validates order exists
   - Checks payment status before updating
   - Logs all transactions
   - Handles errors gracefully

4. **Environment Separation**
   - Sandbox for testing: APP_ENV=local
   - Production for live: APP_ENV=production
   - API URLs automatically switch

## 📊 Database Changes

Added to orders table:
```sql
ALTER TABLE orders ADD phone VARCHAR(255) NULLABLE;
ALTER TABLE orders ADD mpesa_number VARCHAR(255) NULLABLE;
```

Payment tracking in orders:
- `phone` - Customer's phone number
- `mpesa_number` - M-Pesa number used for payment
- `payment_reference` - M-Pesa receipt or request ID
- `payment_status` - pending/paid/failed
- `confirmed_at` - When payment confirmed

## 📝 Logging

All M-Pesa activities logged to: `storage/logs/laravel.log`

Log entries include:
- STK Push initiation
- API authentication
- Callbacks received
- Payment confirmations
- Errors and failures
- Transaction details

Check logs:
```bash
tail -f storage/logs/laravel.log
```

## 🧪 Testing Checklist

- [ ] Add M-Pesa credentials to .env
- [ ] Verify APP_ENV=local (uses sandbox)
- [ ] Test phone number format validation
- [ ] Place order with M-Pesa payment
- [ ] Verify STK Push sent (check logs)
- [ ] Check order payment_status = "pending"
- [ ] Verify callback route exists
- [ ] Test payment confirmation flow

## 🚨 Troubleshooting

### "M-Pesa prompt not appearing"
1. Check phone number format: must be `254XXXXXXXXX`
2. Verify credentials in `.env` are correct
3. Check logs: `grep "STK Push" storage/logs/laravel.log`
4. Verify firewall allows M-Pesa API calls

### "Authentication failed"
1. Verify Consumer Key & Secret
2. Check APP_ENV matches environment
3. Verify correct API URL for environment

### "Callback not working"
1. Ensure route `/mpesa/callback` exists
2. In production: must be HTTPS
3. Check firewall allows incoming webhooks
4. Verify M-Pesa has correct callback URL

## 📚 More Information

- **Complete Setup Guide**: See `MPESA_SETUP_GUIDE.md`
- **M-Pesa Daraja Docs**: https://developer.safaricom.co.ke/
- **API Reference**: Check `MpesaService.php` code comments

## 🎯 Next Steps for Production

1. **Get Live Credentials** from Safaricom
2. **Update .env** with production credentials
3. **Set APP_ENV=production**
4. **Configure HTTPS** for callback URL
5. **Test with small transaction**
6. **Set up monitoring/alerts**
7. **Configure SMS notifications** (optional)

---

**Status**: ✅ Implementation Complete & Ready for Testing

**Files Modified/Created**:
- ✅ `app/Services/MpesaService.php` - NEW
- ✅ `app/Http/Controllers/PaymentController.php` - NEW
- ✅ `app/Http/Controllers/Customer/OrderController.php` - UPDATED
- ✅ `config/services.php` - UPDATED
- ✅ `routes/web.php` - UPDATED
- ✅ `.env` - UPDATED
- ✅ `.env.example` - UPDATED
- ✅ `database/migrations/2026_04_21_add_phone_and_mpesa_to_orders.php` - EXISTING
