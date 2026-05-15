<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminVendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::with('user');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%$search%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%$search%")
                         ->orWhere('phone', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($status === 'active') {
                $query->whereHas('user', function ($q) {
                    $q->where('is_active', true);
                });
            }
        }

         $vendors = $query->withCount('orders')
                         ->withSum(['orders as total_revenue' => function($q) {
                             $q->where('status', 'delivered');
                         }], 'subtotal')
                         ->paginate(15);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|max:255',
            'business_phone' => 'required|string',
            'business_address' => 'required|string',
            'city' => 'required|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'user_type' => 'vendor',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Vendor::create([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'business_phone' => $validated['business_phone'],
            'business_address' => $validated['business_address'],
            'city' => $validated['city'],
            'is_verified' => false,
        ]);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load('user', 'orders');
        return view('admin.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('user');
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $vendor->user->id,
            'phone' => 'required|string|unique:users,phone,' . $vendor->user->id,
            'business_name' => 'required|string|max:255',
            'business_phone' => 'required|string',
            'business_address' => 'required|string',
            'city' => 'required|string',
        ]);

        $vendor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $vendor->update([
            'business_name' => $validated['business_name'],
            'business_phone' => $validated['business_phone'],
            'business_address' => $validated['business_address'],
            'city' => $validated['city'],
        ]);

        return redirect()->route('admin.vendors.show', $vendor)->with('success', 'Vendor updated successfully');
    }

    public function verify(Vendor $vendor)
    {
        $vendor->update(['is_verified' => true]);
        $vendor->user->update(['is_active' => true]);
        return back()->with('success', 'Vendor verified successfully');
    }

    public function suspend(Vendor $vendor)
    {
        $vendor->user->update(['is_active' => false]);
        return back()->with('success', 'Vendor suspended successfully');
    }

    public function activate(Vendor $vendor)
    {
        $vendor->user->update(['is_active' => true]);
        return back()->with('success', 'Vendor activated successfully');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->user->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully');
    }
}
