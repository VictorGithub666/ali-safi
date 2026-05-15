<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminCommission;
use App\Models\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        $totalOrders = $query->sum('order_subtotal');
        $totalProfit = $query->sum('admin_profit');
        $profitMargin = $totalOrders > 0 ? ($totalProfit / $totalOrders) * 100 : 0;
        $orderCount = $query->count();

        $platformCommission = $query->sum('platform_commission');
        $deliveryFees = $query->sum('delivery_fee');
        $riderFees = $query->sum('rider_fee');

        $transactions = $query->with('vendor', 'order')->latest()->paginate(20);

        return view('admin.finances.dashboard', compact(
            'totalOrders', 'totalProfit', 'profitMargin', 'orderCount',
            'platformCommission', 'deliveryFees', 'riderFees', 'transactions'
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

        $totalOrders = $query->sum('order_subtotal');
        $totalProfit = $query->sum('admin_profit');
        $avgMargin = $totalOrders > 0 ? ($totalProfit / $totalOrders) * 100 : 0;
        $transactionCount = $query->count();

        $vendors = Vendor::with('user')->get();
        $marginData = [];

        foreach ($vendors as $vendor) {
            $vendorCommissions = AdminCommission::where('vendor_id', $vendor->id)->get();
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
        $query = AdminCommission::query();

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

        $transactions = $query->with('vendor', 'order')->latest()->paginate(15);
        $vendors = Vendor::pluck('business_name', 'id');

        return view('admin.finances.reports', compact('transactions', 'vendors'));
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

        $transactions = $query->latest()->get();

        $filename = 'admin_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp/maxmemory:5000000', 'r+');

        fputcsv($handle, ['Date', 'Order ID', 'Vendor', 'Order Total', 'Platform Commission', 'Delivery Fee', 'Rider Fee', 'Admin Profit', 'Status']);

        foreach ($transactions as $trans) {
            fputcsv($handle, [
                $trans->created_at->format('Y-m-d H:i:s'),
                $trans->order->id ?? 'N/A',
                $trans->vendor->business_name ?? 'N/A',
                $trans->order_subtotal,
                $trans->platform_commission,
                $trans->delivery_fee,
                $trans->rider_fee,
                $trans->admin_profit,
                $trans->status
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

    public function vendorSettlement(Request $request)
    {
        $vendors = Vendor::with('user', 'orders')->withCount('orders')->paginate(15);
        $settlementData = [];

        foreach ($vendors as $vendor) {
            $commissions = AdminCommission::where('vendor_id', $vendor->id)->get();
            $totalOrders = $commissions->sum('order_subtotal');
            $commission = $commissions->sum('platform_commission');
            $payout = $vendor->orders->count() > 0 ? $totalOrders - $commission : 0;

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
}
