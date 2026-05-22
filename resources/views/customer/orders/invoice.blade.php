<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background: white;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 40px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .company-info h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .company-info p {
            color: #666;
            font-size: 13px;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .invoice-details h2 {
            color: #007bff;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            margin: 10px 0;
            color: #666;
            font-size: 13px;
        }
        
        .invoice-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .date-section h3 {
            color: #007bff;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .date-section p {
            font-size: 14px;
            color: #333;
        }
        
        .bill-ship {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .bill-ship-section h3 {
            color: #007bff;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .bill-ship-section p {
            font-size: 13px;
            color: #333;
            line-height: 1.8;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        
        .items-table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
        }
        
        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #007bff;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #007bff;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .summary-table {
            width: 400px;
        }
        
        .summary-table tr {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .summary-table .total-row {
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 12px 0;
        }
        
        .total-amount {
            color: #007bff;
            font-size: 18px;
            font-weight: 700;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>Ali Safi</h1>
                <p>Online Delivery Platform</p>
                <p>Platform Fee Management</p>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <div class="invoice-number">Order #{{ $order->order_number }}</div>
                <div class="invoice-number">Created: {{ $order->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Dates -->
        <div class="invoice-dates">
            <div class="date-section">
                <h3>Invoice Date</h3>
                <p>{{ $order->created_at->format('F d, Y') }}</p>
            </div>
            <div class="date-section">
                <h3>Due Date</h3>
                <p>{{ $order->created_at->addDays(7)->format('F d, Y') }}</p>
            </div>
        </div>

        <!-- Bill To / Ship To -->
        <div class="bill-ship">
            <div class="bill-ship-section">
                <h3>Bill To</h3>
                <p>
                    {{ $order->customer->name }}<br>
                    {{ $order->delivery_address }}<br>
                    {{ $order->ward }}, {{ $order->sub_county }}<br>
                    {{ $order->county }}<br>
                    {{ $order->customer->phone }}
                </p>
            </div>
            <div class="bill-ship-section">
                <h3>Ship To</h3>
                <p>
                    {{ $order->customer->name }}<br>
                    {{ $order->delivery_address }}<br>
                    {{ $order->ward }}, {{ $order->sub_county }}<br>
                    {{ $order->county }}<br>
                    {{ $order->customer->phone }}
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width: 100px;">Quantity</th>
                    <th style="width: 120px;" class="text-right">Unit Price</th>
                    <th style="width: 120px;" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">KES {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">KES {{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">KES {{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Delivery Fee</td>
                    <td class="text-right">KES {{ number_format($order->delivery_fee, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Amount</td>
                    <td class="text-right"><span class="total-amount">KES {{ number_format($order->total, 2) }}</span></td>
                </tr>
            </div>
        </div>

        <!-- Payment Status -->
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
            <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
            <p><strong>Payment Status:</strong> <span style="color: {{ $order->payment_status === 'paid' ? '#28a745' : '#ffc107' }}; font-weight: 600;">{{ ucfirst($order->payment_status) }}</span></p>
            <p><strong>Order Status:</strong> <span style="color: #007bff; font-weight: 600;">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your order! If you have any questions, please contact customer support.</p>
            <p>© {{ now()->year }} Ali Safi. All rights reserved.</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
