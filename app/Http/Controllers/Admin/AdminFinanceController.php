<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminCommission;
use App\Models\Vendor;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = AdminCommission::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Use only settled commissions for accurate totals
        $settledQuery = clone $query;
        $settledQuery->where('status', 'settled');
        
        $totalOrders = $settledQuery->sum('order_subtotal');
        $totalProfit = $settledQuery->sum('admin_profit');
        $profitMargin = $totalOrders > 0 ? ($totalProfit / $totalOrders) * 100 : 0;
        $orderCount = $settledQuery->count();

        $platformCommission = $settledQuery->sum('platform_commission');
        $deliveryFees = $settledQuery->sum('delivery_fee');
        $riderFees = $settledQuery->sum('rider_fee');

        // Get all transactions for the table (including pending for visibility)
        $transactions = $query->with('vendor', 'order')->latest()->paginate(20);

        // Get summary by status
        $pendingCount = AdminCommission::where('status', 'pending')->count();
        $settledCount = AdminCommission::where('status', 'settled')->count();
        $cancelledCount = AdminCommission::where('status', 'cancelled')->count();

        return view('admin.finances.dashboard', compact(
            'totalOrders', 'totalProfit', 'profitMargin', 'orderCount',
            'platformCommission', 'deliveryFees', 'riderFees', 'transactions',
            'pendingCount', 'settledCount', 'cancelledCount'
        ));
    }

    public function margins(Request $request)
    {
        $query = AdminCommission::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Only settled commissions
        $settledQuery = clone $query;
        $settledQuery->where('status', 'settled');

        $totalOrders = $settledQuery->sum('order_subtotal');
        $totalProfit = $settledQuery->sum('admin_profit');
        $avgMargin = $totalOrders > 0 ? ($totalProfit / $totalOrders) * 100 : 0;
        $transactionCount = $settledQuery->count();

        $vendors = Vendor::with('user')->get();
        $marginData = [];

        foreach ($vendors as $vendor) {
            $vendorCommissions = AdminCommission::where('vendor_id', $vendor->id)
                ->where('status', 'settled')
                ->get();
            $vendorOrderTotal = $vendorCommissions->sum('order_subtotal');
            $vendorProfit = $vendorCommissions->sum('platform_commission');
            $vendorMargin = $vendorOrderTotal > 0 ? ($vendorProfit / $vendorOrderTotal) * 100 : 0;

            $marginData[$vendor->id] = [
                'orders_value' => $vendorOrderTotal,
                'profit' => $vendorProfit,
                'margin' => $vendorMargin,
                'count' => $vendorCommissions->count()
            ];
        }

        return view('admin.finances.margins', compact(
            'vendors', 'marginData', 'totalOrders', 'totalProfit', 'avgMargin', 'transactionCount'
        ));
    }

    public function reports(Request $request)
    {
        $query = AdminCommission::with('vendor', 'order');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->get('vendor_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            })->orWhereHas('vendor', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(15);
        $vendors = Vendor::pluck('business_name', 'id');
        
        // Store query parameters for download
        $filters = [
            'vendor_id' => $request->get('vendor_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ];

        return view('admin.finances.reports', compact('transactions', 'vendors', 'filters'));
    }

    public function downloadReport(Request $request)
    {
        $query = AdminCommission::with('vendor', 'order');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->get('vendor_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            })->orWhereHas('vendor', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->get();

        $format = $request->get('format', 'csv');
        
        if ($format === 'pdf') {
            return $this->downloadPDFReport($transactions, $request);
        }

        // CSV Download
        $filename = 'admin_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp/maxmemory:5000000', 'r+');

        fputcsv($handle, ['Date', 'Order ID', 'Order #', 'Vendor', 'Order Total', 'Platform Commission', 'Delivery Fee', 'Rider Fee', 'Admin Profit', 'Status']);

        foreach ($transactions as $trans) {
            fputcsv($handle, [
                $trans->created_at->format('Y-m-d H:i:s'),
                $trans->order->id ?? 'N/A',
                $trans->order->order_number ?? 'N/A',
                $trans->vendor->business_name ?? 'N/A',
                number_format($trans->order_subtotal, 2),
                number_format($trans->platform_commission, 2),
                number_format($trans->delivery_fee, 2),
                number_format($trans->rider_fee, 2),
                number_format($trans->admin_profit, 2),
                ucfirst($trans->status)
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    /**
     * Download PDF report
     */
    protected function downloadPDFReport($transactions, $request)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finances.pdf-report', [
            'transactions' => $transactions,
            'filters' => [
                'vendor_id' => $request->get('vendor_id'),
                'status' => $request->get('status'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'search' => $request->get('search'),
            ],
            'generated_at' => now(),
            'total_profit' => $transactions->sum('admin_profit'),
            'total_orders_value' => $transactions->sum('order_subtotal'),
        ]);

        $filename = 'admin_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function vendorSettlement(Request $request)
    {
        $vendors = Vendor::with('user', 'orders')->withCount('orders')->paginate(15);
        $settlementData = [];

        foreach ($vendors as $vendor) {
            $commissions = AdminCommission::where('vendor_id', $vendor->id)
                ->where('status', 'settled')
                ->get();
            $totalOrders = $commissions->sum('order_subtotal');
            $commission = $commissions->sum('platform_commission');
            $payout = $totalOrders - $commission;

            $pendingOrders = $vendor->orders()
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready_for_pickup'])
                ->count();

            $settlementData[$vendor->id] = [
                'total_orders' => $totalOrders,
                'commission' => $commission,
                'payout' => $payout,
                'pending_orders' => $pendingOrders
            ];
        }

        return view('admin.finances.vendor-settlement', compact('vendors', 'settlementData'));
    }

    /**
     * Sync all past orders to create AdminCommission records
     */
    public function syncOrders()
    {
        $orders = Order::whereDoesntHave('adminCommission')->get();
        $synced = 0;
        $errors = [];
        
        foreach ($orders as $order) {
            try {
                $commissionPercentage = \App\Models\Setting::get('platform_fee_percentage', 5);
                
                $platformCommission = $order->subtotal * ($commissionPercentage / 100);
                $riderFee = $order->rider_id ? $order->delivery_fee : 0;
                $adminProfit = $platformCommission + $order->delivery_fee + $riderFee;
                $vendorAmount = $order->subtotal - $platformCommission;
                
                $status = 'pending';
                if ($order->status === 'delivered') {
                    $status = 'settled';
                } elseif ($order->status === 'cancelled') {
                    $status = 'cancelled';
                }

                AdminCommission::create([
                    'vendor_id' => $order->vendor_id,
                    'order_id' => $order->id,
                    'order_subtotal' => $order->subtotal,
                    'vendor_amount' => $vendorAmount,
                    'platform_commission' => $platformCommission,
                    'commission_percentage' => $commissionPercentage,
                    'delivery_fee' => $order->delivery_fee,
                    'rider_fee' => $riderFee,
                    'admin_profit' => $adminProfit,
                    'status' => $status,
                    'created_at' => $order->created_at, // ADD THIS - preserve original date
                    'updated_at' => $order->updated_at, // ADD THIS - preserve original date
                ]);
                
                $synced++;
                
            } catch (\Exception $e) {
                $errors[] = "Order #{$order->id}: " . $e->getMessage();
            }
        }
        
        $message = "Synced {$synced} orders to finance records.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }
        
        return redirect()->route('admin.finances.dashboard')
            ->with('success', $message);
    }
}