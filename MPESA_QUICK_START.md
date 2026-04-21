# 🚀 Quick Start: Get M-Pesa Prompts Working Now!

## ⚡ In 3 Minutes

### 1️⃣ Get Test Credentials (From Daraja)
```
Visit: https://developer.safaricom.co.ke/
- Create account
- Create app
- Copy these 2 values:
  ├─ Consumer Key
  └─ Consumer Secret
```

### 2️⃣ Update Your .env File
Add to `.env`:
```env
# Copy from Daraja dashboard
MPESA_CONSUMER_KEY=paste_your_consumer_key_here
MPESA_CONSUMER_SECRET=paste_your_consumer_secret_here

# Use these defaults for testing
MPESA_BUSINESS_CODE=174379
MPESA_SHORTCODE=174379
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919

# Leave these as is
MPESA_COMMAND_ID=CustomerPayBillOnline
MPESA_ACCOUNT_REFERENCE=ALISAFI
MPESA_TRANSACTION_DESC=Ali-Safi Order Payment
```

### 3️⃣ Make Sure APP_ENV=local
```env
APP_ENV=local  # ← This tells system to use SANDBOX
APP_DEBUG=true
```

### 4️⃣ Test It! 🧪
```bash
# Go to your app
# Add items to cart
# Go to checkout
# Select M-Pesa
# Enter phone: 254712345678
# Place order
```

**🎉 You should see STK Prompt on phone!**

---

## 🎯 What Happens Now (Step by Step)

### Old Implementation ❌
```
Place Order → Log to file → Nothing on phone 😞
```

### New Implementation ✅
```
Place Order
    ↓
System calls: mpesaService->initiateStkPush()
    ↓
Authenticates with M-Pesa API
    ↓
Sends STK Push to phone
    ↓
Customer's phone vibrates 📱
    ↓
M-Pesa prompt appears
    ↓
Customer enters M-Pesa PIN
    ↓
Payment confirmed
    ↓
Order automatically updates 🎉
```

---

## 📋 Checklist

- [ ] Have M-Pesa credentials (Daraja API)
- [ ] Updated `.env` with credentials
- [ ] APP_ENV set to "local"
- [ ] Phone number format: 254XXXXXXXXX
- [ ] Test order placed
- [ ] M-Pesa STK Prompt received on phone ✅

---

## 🆘 If It Doesn't Work

### Check #1: Is phone format correct?
```
❌ 712345678       (missing country code)
❌ +254712345678   (has + symbol)
✅ 254712345678    (correct)
✅ 0712345678      (auto-converted to 254712345678)
```

### Check #2: Are credentials filled?
```bash
grep "MPESA_CONSUMER_KEY" .env
# Should show: MPESA_CONSUMER_KEY=xxxxxxxxxxxx
# NOT blank!
```

### Check #3: Check the logs
```bash
tail -f storage/logs/laravel.log
# Search for "STK Push" or "M-Pesa"
# Should see: "STK Push sent successfully"
```

### Check #4: Verify credentials are correct
```
- Go to https://developer.safaricom.co.ke/
- Log in
- Check your app
- Copy Consumer Key again
- Copy Consumer Secret again
- Paste into .env exactly as shown
```

---

## 💡 Testing Tips

**Use this test phone number:**
```
254712345678
```

**See what happens in logs:**
```bash
# Terminal window 1 - Watch logs
tail -f storage/logs/laravel.log

# Terminal window 2 - Your browser
# Place order...
# Watch logs for:
# - "M-Pesa STK Push initiated"
# - "M-Pesa prompt sent successfully"
# - "CheckoutRequestID"
```

---

## 📚 Full Documentation

If you get stuck:
- **Setup Guide**: Read `MPESA_SETUP_GUIDE.md`
- **Technical**: Read `MPESA_IMPLEMENTATION.md`
- **Daraja Docs**: https://developer.safaricom.co.ke/docs

---

## ✨ Summary

**What You Get:**
- ✅ M-Pesa prompt on customer's phone
- ✅ Automatic order confirmation
- ✅ Payment logged in database
- ✅ Sandbox testing ready
- ✅ Production ready (just change credentials)

**Total Setup Time:** ~5 minutes

**Status:** Ready to GO! 🚀
