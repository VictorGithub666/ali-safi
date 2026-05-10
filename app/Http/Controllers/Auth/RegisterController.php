<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Rider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the customer registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register', ['userType' => 'customer']);
    }

    /**
     * Show the vendor registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showVendorRegistrationForm()
    {
        return view('auth.register-vendor', ['userType' => 'vendor']);
    }

    /**
     * Show the rider registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRiderRegistrationForm()
    {
        return view('auth.register-rider', ['userType' => 'rider']);
    }

    /**
     * Handle a vendor registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function registerVendor(Request $request)
    {
        $request->validate($this->vendorValidationRules());

        $user = $this->createVendor($request->all());

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }

    /**
     * Handle a rider registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function registerRider(Request $request)
    {
        $request->validate($this->riderValidationRules());

        $user = $this->createRider($request->all());

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming customer registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Get a validator for vendor registration.
     *
     * @return array
     */
    protected function vendorValidationRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:20'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get a validator for rider registration.
     *
     * @return array
     */
    protected function riderValidationRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['required', 'string', 'in:motorcycle,car,van,truck'],
            'vehicle_number' => ['required', 'string', 'max:50'],
            'license_number' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Create a new user instance after a valid customer registration.
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'customer',
        ]);
    }

    /**
     * Create a new vendor user instance after valid registration.
     * This creates BOTH the User and the Vendor record.
     *
     * @return User
     */
    protected function createVendor(array $data)
    {
        // Create the user first
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'user_type' => 'vendor',
            'is_active' => true,
            'is_verified' => false, // Admin needs to verify
        ]);

        // Create the vendor record
        $user->vendor()->create([
            'business_name' => $data['business_name'],
            'business_phone' => $data['business_phone'] ?? $data['phone'],
            'business_address' => $data['business_address'] ?? '',
            'latitude' => null,
            'longitude' => null,
            'operating_hours' => json_encode(['mon_fri' => '09:00-18:00', 'sat' => '09:00-15:00', 'sun' => 'closed']),
            'is_open' => true,
            'rating' => 0,
            'total_orders' => 0,
            'wallet_balance' => 0,
            'is_verified' => false,
        ]);

        return $user;
    }
    

    /**
     * Create a new rider user instance after valid registration.
     * This creates BOTH the User and the Rider record.
     *
     * @return User
     */
    protected function createRider(array $data)
    {
        // Create the user first
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'user_type' => 'rider',
            'is_active' => true,
            'is_verified' => false, // Admin needs to verify
        ]);

        // Create the rider record
        $user->rider()->create([
            'vehicle_type' => $data['vehicle_type'],
            'vehicle_number' => $data['vehicle_number'],
            'license_number' => $data['license_number'],
            'is_available' => false,
            'current_latitude' => null,
            'current_longitude' => null,
            'rating' => 0,
            'total_deliveries' => 0,
            'wallet_balance' => 0,
            'is_verified' => false,
            'last_location_update' => null,
        ]);

        return $user;
    }
}