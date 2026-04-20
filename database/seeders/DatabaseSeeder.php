<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Rider;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create settings
        Setting::create([
            'key' => 'platform_commission',
            'value' => '5',
            'type' => 'integer'
        ]);
        
        Setting::create([
            'key' => 'min_delivery_fee',
            'value' => '50',
            'type' => 'integer'
        ]);
        
        Setting::create([
            'key' => 'max_delivery_distance',
            'value' => '15',
            'type' => 'integer'
        ]);

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone' => '+254700000099',
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'is_verified' => true,
            'latitude' => -1.2921,
            'longitude' => 36.8219,
        ]);

        // Create customer users
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Customer $i",
                'email' => "customer$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '+254710000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'user_type' => 'customer',
                'email_verified_at' => now(),
                'is_verified' => true,
                'latitude' => -1.2921 + (rand(-100, 100) / 10000),
                'longitude' => 36.8219 + (rand(-100, 100) / 10000),
                'address' => "Address $i, Nairobi",
                'city' => 'Nairobi',
            ]);
        }

        // Create vendor users
        for ($i = 1; $i <= 3; $i++) {
            $vendorUser = User::create([
                'name' => "Vendor $i",
                'email' => "vendor$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '+254720000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'user_type' => 'vendor',
                'email_verified_at' => now(),
                'is_verified' => true,
                'latitude' => -1.2921 + (rand(-50, 50) / 10000),
                'longitude' => 36.8219 + (rand(-50, 50) / 10000),
            ]);

            Vendor::create([
                'user_id' => $vendorUser->id,
                'business_name' => "Gas Shop $i",
                'business_phone' => '+254720000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'business_address' => "Business Address $i, Nairobi",
                'latitude' => $vendorUser->latitude,
                'longitude' => $vendorUser->longitude,
                'operating_hours' => json_encode([
                    'monday' => ['08:00', '20:00'],
                    'tuesday' => ['08:00', '20:00'],
                    'wednesday' => ['08:00', '20:00'],
                    'thursday' => ['08:00', '20:00'],
                    'friday' => ['08:00', '20:00'],
                    'saturday' => ['09:00', '20:00'],
                    'sunday' => ['10:00', '18:00'],
                ]),
                'is_open' => true,
                'is_verified' => true,
                'rating' => rand(30, 50) / 10, // 3.0 to 5.0
            ]);
        }

        // Create rider users
        for ($i = 1; $i <= 5; $i++) {
            $riderUser = User::create([
                'name' => "Rider $i",
                'email' => "rider$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '+254730000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'user_type' => 'rider',
                'email_verified_at' => now(),
                'is_verified' => true,
                'latitude' => -1.2921 + (rand(-100, 100) / 10000),
                'longitude' => 36.8219 + (rand(-100, 100) / 10000),
            ]);

            Rider::create([
                'user_id' => $riderUser->id,
                'vehicle_type' => $i % 2 == 0 ? 'car' : 'motorcycle',
                'vehicle_number' => 'KEN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'license_number' => 'DL-' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'is_available' => true,
                'current_latitude' => $riderUser->latitude,
                'current_longitude' => $riderUser->longitude,
                'is_verified' => true,
                'rating' => rand(30, 50) / 10, // 3.0 to 5.0
            ]);
        }

        // Create categories
        $categories = [
            [
                'name' => 'Gas Cylinders',
                'slug' => 'gas-cylinders',
                'description' => 'Cooking gas cylinders in various sizes',
                'icon' => '⛽',
                'is_active' => true,
            ],
            [
                'name' => 'Water',
                'slug' => 'water',
                'description' => 'Drinking water and water containers',
                'icon' => '💧',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $gasCategory = Category::where('slug', 'gas-cylinders')->first();
        $waterCategory = Category::where('slug', 'water')->first();

        // Create gas products
        $gasProducts = [
            [
                'name' => '6kg Gas Cylinder',
                'slug' => 'gas-6kg',
                'description' => 'Standard 6kg cooking gas cylinder with safety valve',
                'image' => 'products/gas-6kg.jpg',
                'base_price' => 1200,
                'delivery_fee' => 50,
                'final_price' => 1250,
                'unit' => 'cylinder',
                'sizes' => json_encode(['6kg']),
                'size_prices' => json_encode(['6kg' => 1250]),
                'is_active' => true,
                'stock_quantity' => 100,
            ],
            [
                'name' => '12kg Gas Cylinder',
                'slug' => 'gas-12kg',
                'description' => 'Family size 12kg cooking gas cylinder',
                'image' => 'products/gas-12kg.jpg',
                'base_price' => 2300,
                'delivery_fee' => 50,
                'final_price' => 2350,
                'unit' => 'cylinder',
                'sizes' => json_encode(['12kg']),
                'size_prices' => json_encode(['12kg' => 2350]),
                'is_active' => true,
                'stock_quantity' => 80,
            ],
            [
                'name' => '25kg Gas Cylinder',
                'slug' => 'gas-25kg',
                'description' => 'Commercial size 25kg cooking gas cylinder',
                'image' => 'products/gas-25kg.jpg',
                'base_price' => 4500,
                'delivery_fee' => 100,
                'final_price' => 4600,
                'unit' => 'cylinder',
                'sizes' => json_encode(['25kg']),
                'size_prices' => json_encode(['25kg' => 4600]),
                'is_active' => true,
                'stock_quantity' => 50,
            ],
        ];

        foreach ($gasProducts as $product) {
            Product::create(array_merge($product, ['category_id' => $gasCategory->id]));
        }

        // Create water products
        $waterProducts = [
            [
                'name' => '20L Water Container',
                'slug' => 'water-20l',
                'description' => 'Pure drinking water in 20L container',
                'image' => 'products/water-20l.jpg',
                'base_price' => 100,
                'delivery_fee' => 30,
                'final_price' => 130,
                'unit' => 'container',
                'sizes' => json_encode(['20L']),
                'size_prices' => json_encode(['20L' => 130]),
                'is_active' => true,
                'stock_quantity' => 200,
            ],
            [
                'name' => '40L Water Container',
                'slug' => 'water-40l',
                'description' => 'Pure drinking water in 40L container',
                'image' => 'products/water-40l.jpg',
                'base_price' => 200,
                'delivery_fee' => 40,
                'final_price' => 240,
                'unit' => 'container',
                'sizes' => json_encode(['40L']),
                'size_prices' => json_encode(['40L' => 240]),
                'is_active' => true,
                'stock_quantity' => 150,
            ],
            [
                'name' => 'Water Dispenser',
                'slug' => 'water-dispenser',
                'description' => 'Hot and cold water dispenser',
                'image' => 'products/water-dispenser.jpg',
                'base_price' => 3500,
                'delivery_fee' => 150,
                'final_price' => 3650,
                'unit' => 'piece',
                'sizes' => null,
                'size_prices' => null,
                'is_active' => true,
                'stock_quantity' => 30,
            ],
        ];

        foreach ($waterProducts as $product) {
            Product::create(array_merge($product, ['category_id' => $waterCategory->id]));
        }

        // Assign products to vendors
        $vendors = Vendor::all();
        $products = Product::all();

        foreach ($vendors as $vendor) {
            // Each vendor gets a random selection of products
            $randomProducts = $products->random(rand(3, count($products)));
            
            foreach ($randomProducts as $product) {
                $vendor->products()->attach($product->id, [
                    'stock_quantity' => rand(10, 50),
                    'is_available' => true,
                    'custom_price' => null,
                ]);
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Test Accounts:');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Customer: customer1@example.com / password');
        $this->command->info('Vendor: vendor1@example.com / password');
        $this->command->info('Rider: rider1@example.com / password');
    }
}