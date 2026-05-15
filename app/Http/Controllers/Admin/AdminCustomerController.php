<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('user_type', 'customer');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true)->whereNotNull('email_verified_at');
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $customers = $query->withCount('orders')->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['user_type'] = 'customer';
        $validated['email_verified_at'] = now();

        User::create($validated);
        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully');
    }

    public function show(User $customer)
    {
        $customer->load('orders');
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'required|string|unique:users,phone,' . $customer->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $customer->update($validated);
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully');
    }

    public function suspend(User $customer)
    {
        $customer->update(['is_active' => false]);
        return back()->with('success', 'Customer suspended successfully');
    }

    public function activate(User $customer)
    {
        $customer->update(['is_active' => true]);
        return back()->with('success', 'Customer activated successfully');
    }

    public function destroy(User $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully');
    }
}
