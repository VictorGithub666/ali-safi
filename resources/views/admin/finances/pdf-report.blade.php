<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Finance Report - Ali Safi</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #05bb14;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #05bb14;
            margin: 0;
        }
        .filters {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #05bb14;
            color: white;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .text-right {
            text-align: right;
        }
        .text-success {
            color: #05bb14;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ali Safi Finance Report</h1>
        <p>Generated on: {{ $generated_at->format('F d, Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        <ul>
            @if($filters['vendor_id'])
                <li>Vendor: {{ App\Models\Vendor::find($filters['vendor_id'])?->business_name ?? 'ID: ' . $filters['vendor_id'] }}</li>
            @endif
            @if($filters['status'])
                <li>Status: {{ ucfirst($filters['status']) }}</li>
            @endif
            @if($filters['date_from'])
                <li>From: {{ $filters['date_from'] }}</li>
            @endif
            @if($filters['date_to'])
                <li>To: {{ $filters['date_to'] }}</li>
            @endif
            @if($filters['search'])
                <li>Search: {{ $filters['search'] }}</li>
            @endif
        </ul>
    </div>
    @endif

    <div class="summary">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none;"><strong>Total Orders Value:</strong></td>
                <td style="border: none;" class="text-right">KES {{ number_format($total_orders_value, 2) }}</td>
                <td style="border: none; width: 50px;"></td>
                <td style="border: none;"><strong>Total Profit:</strong></td>
                <td style="border: none;" class="text-right text-success">KES {{ number_format($total_profit, 2) }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Total Transactions:</strong></td>
                <td style="border: none;" class="text-right">{{ $transactions->count() }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Order #</th>
                <th>Vendor</th>
                <th>Order Total</th>
                <th>Commission</th>
                <th>Delivery Fee</th>
                <th>Rider Fee</th>
                <th>Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trans)
            <tr>
                <td>{{ $trans->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $trans->order->order_number ?? 'N/A' }}</td>
                <td>{{ $trans->vendor->business_name ?? 'N/A' }}</td>
                <td class="text-right">KES {{ number_format($trans->order_subtotal, 2) }}</td>
                <td class="text-right">KES {{ number_format($trans->platform_commission, 2) }}</td>
                <td class="text-right">KES {{ number_format($trans->delivery_fee, 2) }}</td>
                <td class="text-right">KES {{ number_format($trans->rider_fee, 2) }}</td>
                <td class="text-right">KES {{ number_format($trans->admin_profit, 2) }}</td>
                <td>{{ ucfirst($trans->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report. For any queries, please contact support.</p>
    </div>
</body>
</html>