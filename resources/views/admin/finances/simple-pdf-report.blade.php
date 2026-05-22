{{-- resources/views/admin/finances/simple-pdf-report.blade.php --}}
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
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .report-info {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .report-info table {
            width: 100%;
            border: none;
        }
        .report-info td {
            padding: 5px;
            border: none;
        }
        .summary-cards {
            margin-bottom: 30px;
        }
        .summary-cards table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-cards td {
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 5px;
            width: 25%;
        }
        .summary-cards .card-value {
            font-size: 18px;
            font-weight: bold;
            color: #05bb14;
        }
        .summary-cards .card-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #05bb14;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-success {
            color: #05bb14;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-settled {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ali Safi Finance Report</h1>
        <p>Generated on: {{ $generated_at->format('F d, Y H:i:s') }}</p>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td><strong>Report Period:</strong></td>
                <td>{{ $date_from }} to {{ $date_to }}</td>
                <td><strong>Status Filter:</strong></td>
                <td>{{ ucfirst($status_filter) }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-cards">
        <table>
            <tr>
                <td>
                    <div class="card-value">KES {{ number_format($total_orders_value, 2) }}</div>
                    <div class="card-label">Total Orders Value</div>
                </td>
                <td>
                    <div class="card-value">KES {{ number_format($total_delivery_fees, 2) }}</div>
                    <div class="card-label">Total Delivery Fees</div>
                </td>
                <td>
                    <div class="card-value" style="color: #05bb14;">KES {{ number_format($total_profit, 2) }}</div>
                    <div class="card-label">Total Profit</div>
                </td>
                <td>
                    <div class="card-value">{{ $transaction_count }}</div>
                    <div class="card-label">Transactions</div>
                </td>
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
                <th>Delivery Fee</th>
                <th>Admin Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $trans)
            <tr>
                <td>{{ $trans->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $trans->order->order_number ?? 'N/A' }}</td>
                <td>{{ $trans->vendor->business_name ?? 'N/A' }}</td>
                <td class="text-right">KES {{ number_format($trans->order_subtotal, 2) }}</td>
                <td class="text-right">KES {{ number_format($trans->delivery_fee, 2) }}</td>
                <td class="text-right">KES {{ number_format($trans->admin_profit, 2) }}</td>
                <td>
                    @if($trans->status === 'settled')
                        <span class="status-badge status-settled">Settled</span>
                    @elseif($trans->status === 'pending')
                        <span class="status-badge status-pending">Pending</span>
                    @else
                        <span class="status-badge status-cancelled">Cancelled</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">No transactions found for the selected period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report. For any queries, please contact support.</p>
        <p>&copy; {{ date('Y') }} Ali Safi. All rights reserved.</p>
    </div>
</body>
</html>