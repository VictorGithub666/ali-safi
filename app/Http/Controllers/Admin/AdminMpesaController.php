<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminMpesaController extends Controller
{
    /**
     * Display M-Pesa transactions list
     */
    public function index(Request $request)
    {
        $query = MpesaTransaction::with('order', 'order.customer', 'order.vendor');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Filter by phone number
        if ($request->filled('phone')) {
            $query->where('phone_number', 'like', '%' . $request->get('phone') . '%');
        }

        // Filter by amount
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->get('amount_from'));
        }
        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->get('amount_to'));
        }

        $transactions = $query->latest()->paginate(20);

        // Calculate statistics
        $totalCompleted = MpesaTransaction::completed()->sum('amount');
        $totalPending = MpesaTransaction::pending()->count();
        $totalFailed = MpesaTransaction::failed()->count();
        $totalTransactions = MpesaTransaction::count();

        return view('admin.mpesa.index', compact(
            'transactions',
            'totalCompleted',
            'totalPending',
            'totalFailed',
            'totalTransactions'
        ));
    }

    /**
     * Display M-Pesa transaction details
     */
    public function show(MpesaTransaction $transaction)
    {
        $transaction->load('order', 'order.customer', 'order.vendor', 'order.items');

        return view('admin.mpesa.show', compact('transaction'));
    }

    /**
     * Get M-Pesa statistics for dashboard
     */
    public function dashboard(Request $request)
    {
        // Get date range
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $query = MpesaTransaction::whereBetween('created_at', [$dateFrom, $dateTo]);

        // Statistics
        $totalRevenue = (clone $query)->completed()->sum('amount');
        $totalTransactions = (clone $query)->count();
        $completedTransactions = (clone $query)->completed()->count();
        $pendingTransactions = (clone $query)->pending()->count();
        $failedTransactions = (clone $query)->failed()->count();
        $cancellledTransactions = (clone $query)->where('status', 'cancelled')->count();

        // Get completion rate
        $completionRate = $totalTransactions > 0 ? ($completedTransactions / $totalTransactions) * 100 : 0;

        // Daily revenue chart data
        $dailyData = MpesaTransaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(completed_at) as date, SUM(amount) as revenue, COUNT(*) as count')
            ->groupByRaw('DATE(completed_at)')
            ->orderBy('date')
            ->get();

        // Status breakdown
        $statusBreakdown = MpesaTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get();

        // Top phones by transaction count
        $topPhones = MpesaTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('phone_number, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('phone_number')
            ->orderByRaw('count DESC')
            ->limit(10)
            ->get();

        return view('admin.mpesa.dashboard', compact(
            'totalRevenue',
            'totalTransactions',
            'completedTransactions',
            'pendingTransactions',
            'failedTransactions',
            'cancellledTransactions',
            'completionRate',
            'dailyData',
            'statusBreakdown',
            'topPhones',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Get M-Pesa notifications
     */
    public function notifications(Request $request)
    {
        $query = MpesaTransaction::where('status', '!=', 'pending')->with('order', 'order.customer');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('status', $request->get('type'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->get('date_to'));
        }

        $notifications = $query->latest('completed_at')->paginate(30);

        // Summary
        $todayCompleted = MpesaTransaction::completed()
            ->whereDate('completed_at', now()->toDateString())
            ->count();

        $todayFailed = MpesaTransaction::failed()
            ->whereDate('completed_at', now()->toDateString())
            ->count();

        return view('admin.mpesa.notifications', compact(
            'notifications',
            'todayCompleted',
            'todayFailed'
        ));
    }

    /**
     * Export M-Pesa transactions to CSV
     */
    public function export(Request $request)
    {
        $query = MpesaTransaction::with('order', 'order.customer');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $transactions = $query->get();

        // Generate CSV
        $filename = 'mpesa-transactions-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('ID', 'Order Number', 'Customer', 'Phone', 'Amount', 'Status', 'Receipt Number', 'Initiated At', 'Completed At');

        $callback = function() use($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($transactions as $transaction) {
                fputcsv($file, array(
                    $transaction->id,
                    $transaction->order->order_number ?? 'N/A',
                    $transaction->order->customer->name ?? 'N/A',
                    $transaction->phone_number,
                    $transaction->amount,
                    $transaction->status,
                    $transaction->mpesa_receipt_number ?? 'N/A',
                    $transaction->initiated_at->format('Y-m-d H:i:s'),
                    $transaction->completed_at ? $transaction->completed_at->format('Y-m-d H:i:s') : 'N/A'
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Resend callback for a transaction
     */
    public function resendCallback(MpesaTransaction $transaction)
    {
        // This would be used if we need to manually re-trigger processing
        // For now, just a placeholder for future implementation
        
        if ($transaction->status !== 'completed') {
            return redirect()->back()->with('error', 'Only completed transactions can be reprocessed');
        }

        // TODO: Implement webhook retry logic
        return redirect()->back()->with('success', 'Callback resend initiated');
    }

    /**
     * Manual payment confirmation (admin override)
     */
    public function confirmPayment(MpesaTransaction $transaction)
    {
        if ($transaction->status === 'completed') {
            return redirect()->back()->with('error', 'This payment is already confirmed');
        }

        $transaction->markAsCompleted(
            $transaction->mpesa_receipt_number ?? 'MANUAL_' . $transaction->id,
            '0',
            'Manually confirmed by admin'
        );

        $transaction->order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Payment confirmed successfully');
    }
}
