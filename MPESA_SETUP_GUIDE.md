# M-Pesa Integration Setup Guide for Ali-Safi

## Overview
This guide explains how to set up M-Pesa STK Push integration to send M-Pesa payment prompts directly to customer phones.

## Prerequisites
1. **Safaricom Business Account** - Required to access Daraja API
2. **M-Pesa Business Shortcode** - A 5-6 digit code (e.g., 174379)
3. **M-Pesa API Credentials** - Consumer Key and Consumer Secret
4. **Pass Key** - Used to generate STK Push passwords

## Step-by-Step Setup

### 1. Get M-Pesa Daraja API Credentials

**For Sandbox (Testing):**
1. Visit: https://developer.safaricom.co.ke/
2. Create a developer account if you don't have one
3. Create a new application
4. Under "Keys", you'll find:
   - Consumer Key
   - Consumer Secret

**For Production (Live):**
1. Contact Safaricom Business at: 0722999999 or visit your nearest Safaricom office
2. Request enterprise support for Daraja API
3. Provide your business details and expected transaction volume
4. They will provide you with:
   - Consumer Key
   - Consumer Secret
   - Business Shortcode
   - Pass Key

### 2. Update .env File

Add your M-Pesa credentials to `.env`:

```env
# ===== M-Pesa Daraja API Configuration =====
MPESA_CONSUMER_KEY=Your_Consumer_Key_Here
MPESA_CONSUMER_SECRET=Your_Consumer_Secret_Here
MPESA_BUSINESS_CODE=174379
MPESA_SHORTCODE=174379
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_COMMAND_ID=CustomerPayBillOnline
MPESA_ACCOUNT_REFERENCE=ALISAFI
MPESA_TRANSACTION_DESC=Ali-Safi Order Payment
```

**Where to get each value:**

| Variable | Source | Example |
|----------|--------|---------|
| MPESA_CONSUMER_KEY | Daraja Dashboard | `kT0Xy1z2A3b4C5d` |
| MPESA_CONSUMER_SECRET | Daraja Dashboard | `mK9L0M1N2O3P4q5` |
| MPESA_BUSINESS_CODE | M-Pesa Business Account | `174379` |
| MPESA_SHORTCODE | M-Pesa Business Account | `174379` |
| MPESA_PASSKEY | M-Pesa Business Account | Your account's pass key |

### 3. Testing with Sandbox

**Use these test credentials for sandbox testing:**
```env
MPESA_CONSUMER_KEY=I8K8TJOy5ZBxxxx
MPESA_CONSUMER_SECRET=Mj2JMRxxQxxxxxx
MPESA_BUSINESS_CODE=174379
MPESA_SHORTCODE=174379
```

**Test Phone Numbers:**
- `254712345678` (format: must start with 254)
- `0712345678` (will be auto-converted to 254712345678)

### 4. Environment Configuration

The system automatically detects environment based on `APP_ENV`:
- **APP_ENV=local** → Uses Sandbox: `https://sandbox.safaricom.co.ke`
- **APP_ENV=production** → Uses Production: `https://api.safaricom.co.ke`

## How It Works

### Customer Flow

1. **Customer selects M-Pesa** during checkout
2. **Enters M-Pesa number** (format: 254XXXXXXXXX)
3. **Places order**
4. **STK Push prompt sent** to their phone immediately
5. **Customer enters M-Pesa PIN** to confirm payment
6. **Payment confirmed** → Order status updates to "confirmed"

### Technical Flow

```
Order Placed
    ↓
validatePhoneNumber (must start with 254)
    ↓
initiateStkPush(phone, amount, orderNumber)
    ↓
Get OAuth Token from M-Pesa
    ↓
Send STK Push Request
    ↓
STK Prompt appears on customer's phone
    ↓
Customer enters M-Pesa PIN
    ↓
M-Pesa approves/denies payment
    ↓
Webhook callback received at /mpesa/callback
    ↓
Order payment status updated in database
    ↓
Order confirmation sent to vendor
```

## M-Pesa Prompt Details

When customer places order with M-Pesa payment:

1. **Phone number** must be in format: `254XXXXXXXXX`
   - Examples: `254712345678`, `254722123456`
   - System auto-converts `0712345678` to `254712345678`

2. **STK Prompt** is sent to customer's phone with:
   - Business name
   - Amount to pay
   - Account reference

3. **Customer action**: Enter M-Pesa PIN to confirm

4. **Timeout**: Standard M-Pesa timeout is ~2-3 minutes

## Security Considerations

🔒 **Important Security Notes:**

1. **Never commit credentials** to version control
   - Use `.env` file (which is .gitignored)
   - Use environment variables in production

2. **HTTPS Only** in production
   - Callback URL must be HTTPS
   - M-Pesa will reject HTTP URLs in production

3. **Validate callbacks**
   - Check that callback IP is from Safaricom
   - Verify callback signature if available

4. **Rate limiting**
   - Implement rate limiting on callback endpoint
   - Prevent duplicate processing

## Troubleshooting

### Issue: "Invalid phone number"
**Solution**: Ensure phone starts with `254` and has 12 digits total
- ❌ Wrong: `712345678` (missing country code)
- ✅ Right: `254712345678`

### Issue: "Authentication failed" with M-Pesa API
**Solution**: 
- Check Consumer Key and Secret are correct
- Verify APP_ENV matches your intended environment
- Check Daraja API status page

### Issue: STK Prompt not appearing
**Solution**:
- Verify phone number is active and ready for M-Pesa
- Check network connectivity
- Verify business shortcode is correct
- Test in sandbox first before going live

### Issue: Callback not received
**Solution**:
- Verify callback URL is HTTPS in production
- Check firewall isn't blocking Safaricom IPs
- Verify webhook route exists: `POST /mpesa/callback`
- Check application logs

## API Endpoints

### Send STK Push
```php
$mpesaService->initiateStkPush(
    $phoneNumber,      // Format: 254XXXXXXXXX
    $amount,            // Amount in KES
    $orderId            // Order ID for reference
);
```

**Response:**
```json
{
    "success": true,
    "message": "M-Pesa prompt sent successfully",
    "data": {
        "CheckoutRequestID": "..."
    }
}
```

### Check Payment Status
```php
$mpesaService->queryTransaction($checkoutRequestId);
```

## Production Deployment Checklist

- [ ] Update `APP_ENV=production`
- [ ] Add valid Consumer Key from production
- [ ] Add valid Consumer Secret from production
- [ ] Verify MPESA_SHORTCODE matches your business account
- [ ] Update callback URL to your production domain
- [ ] Set up HTTPS certificate
- [ ] Configure firewall to allow Safaricom API IPs
- [ ] Test with small transaction first
- [ ] Set up SMS notifications for customers
- [ ] Configure error alerting/monitoring

## Support & Resources

- **Daraja API Docs**: https://developer.safaricom.co.ke/docs
- **M-Pesa Support**: 0722999999
- **Safaricom Business**: https://business.safaricom.co.ke

## Logs

All M-Pesa transactions are logged in:
```
storage/logs/laravel.log
```

Check logs for:
- STK Push initiation
- Callback receipts
- Payment confirmations
- Errors and failures
