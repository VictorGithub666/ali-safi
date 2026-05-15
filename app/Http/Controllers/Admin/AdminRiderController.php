<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Rider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminRiderController extends Controller
{
    public function index(Request $request)
    {
        $query = Rider::with('user');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%$search%")
                       ->orWhere('email', 'like', "%$search%")
                       ->orWhere('phone', 'like', "%$search%");
                })
                ->orWhere('vehicle_number', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($status === 'unverified') {
                $query->where('is_verified', false);
            } elseif ($status === 'active') {
                $query->whereHas('user', function ($q) {
                    $q->where('is_active', true);
                });
            } elseif ($status === 'available') {
                $query->where('is_available', true);
            }
        }

         $riders = $query->withCount(['orders' => function($q) {
                $q->where('status', 'delivered'); // Count only completed deliveries
            }])
            ->withSum(['orders as total_delivery_fee' => function($q) {
                $q->where('status', 'delivered'); // Sum delivery fees only for completed deliveries
            }], 'delivery_fee')
            ->paginate(15);

        return view('admin.riders.index', compact('riders'));
    }

    public function create()
    {
        return view('admin.riders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'vehicle_type' => 'required|string',
            'vehicle_number' => 'required|string|unique:riders',
            'license_number' => 'required|string|unique:riders',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'user_type' => 'rider',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Rider::create([
            'user_id' => $user->id,
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'license_number' => $validated['license_number'],
            'is_verified' => false,
            'is_available' => true,
        ]);

        return redirect()->route('admin.riders.index')->with('success', 'Rider created successfully');
    }

    public function show(Rider $rider)
    {
        // Load with proper counts and sums
        $rider->load('user');
        $rider->loadCount(['orders' => function($q) {
            $q->where('status', 'delivered');
        }]);
        $rider->loadSum(['orders' => function($q) {
            $q->where('status', 'delivered');
        }], 'delivery_fee');
        
        return view('admin.riders.show', compact('rider'));
    }

    public function edit(Rider $rider)
    {
        $rider->load('user');
        return view('admin.riders.edit', compact('rider'));
    }

    public function update(Request $request, Rider $rider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $rider->user->id,
            'phone' => 'required|string|unique:users,phone,' . $rider->user->id,
            'vehicle_type' => 'required|string',
            'vehicle_number' => 'required|string|unique:riders,vehicle_number,' . $rider->id,
            'license_number' => 'required|string|unique:riders,license_number,' . $rider->id,
        ]);

        $rider->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $rider->update([
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'license_number' => $validated['license_number'],
        ]);

        return redirect()->route('admin.riders.show', $rider)->with('success', 'Rider updated successfully');
    }

    public function verify(Rider $rider)
    {
        $rider->update(['is_verified' => true]);
        return back()->with('success', 'Rider verified successfully');
    }

    public function suspend(Rider $rider)
    {
        $rider->user->update(['is_active' => false]);
        return back()->with('success', 'Rider suspended successfully');
    }

    public function activate(Rider $rider)
    {
        $rider->user->update(['is_active' => true]);
        return back()->with('success', 'Rider activated successfully');
    }

    public function destroy(Rider $rider)
    {
        $rider->user->delete();
        return redirect()->route('admin.riders.index')->with('success', 'Rider deleted successfully');
    }
}
