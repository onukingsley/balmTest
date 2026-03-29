<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Models
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* =========================
         * USERS
         * ========================= */

        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@skincare.com',
            'address' => 'Head Office',
            'image' => 'userImage/01.jpg',
            'user_role' => '0',
            'phone_number' => '0800000000',
            'password' => Hash::make('admin123'),
        ]);

        // Customers
        for ($i = 2; $i <= 102; $i++) {
            User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'address' => fake()->address(),
                'image' => 'userImage/' . str_pad($i, 2, '0', STR_PAD_LEFT).'.jpg',
                'user_role' => '1',
                'phone_number' => '08' . fake()->unique()->numberBetween(10000000, 99999999),
                'password' => Hash::make('password'),
            ]);
        }

        /* =========================
         * CATEGORIES (SKINCARE)
         * ========================= */
        $categories = [
            ['Cleansers', 'Face and body cleansing products'],
            ['Moisturizers', 'Hydrating creams and lotions'],
            ['Serums', 'Targeted treatment serums'],
            ['Sunscreens', 'Daily UV protection'],
            ['Toners', 'Balancing and soothing toners'],
            ['Masks', 'Weekly treatment masks'],
            ['Eye Care', 'Under-eye treatments'],
            ['Body Care', 'Full body skincare'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'title' => $cat[0],
                'description' => $cat[1],
                'category_image' => 'product/' . str_pad($i, 2, '0', STR_PAD_LEFT).'.jpg',

            ]);
        }

        /* =========================
         * BRANDS
         * ========================= */
        $brands = [
            ['GlowLab', 'Dermatologist tested skincare'],
            ['PureDerm', 'Gentle skincare for all skin types'],
            ['SkinTheory', 'Science-backed skincare solutions'],
            ['Lumière', 'Luxury hydration and glow'],
            ['DermaPlus', 'Clinical strength skincare'],
            ['NatureLeaf', 'Natural & organic skincare'],
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'title' => $brand[0],
                'description' => $brand[1],
                'brand_image' => 'product/' . str_pad($i, 2, '0', STR_PAD_LEFT).'.jpg',

            ]);
        }

        /* =========================
         * PRODUCTS
         * ========================= */
        $productTitles = [
            'Hydrating Facial Cleanser',
            'Vitamin C Brightening Serum',
            'Hyaluronic Acid Moisturizer',
            'SPF 50 Daily Sunscreen',
            'Soothing Aloe Toner',
            'Retinol Night Cream',
            'Charcoal Detox Mask',
            'Under Eye Repair Gel',
            'Body Nourishing Lotion',
            'Gentle Foam Cleanser',
        ];

        $statuses = ['out_of_stock', 'Discount', 'Hot','Recommended'];

        $productCount = 300;

        for ($i = 1; $i <= $productCount; $i++) {
            $price = fake()->numberBetween(800, 12000); // stored as string
            $discount = fake()->boolean(40)
                ? (string)($price - fake()->numberBetween(200, 1000))
                : null;

            Product::create([
                'category_id' => fake()->numberBetween(1, count($categories)),
                'brand_id' => fake()->numberBetween(1, count($brands)),
                'product_image' => 'product/' . str_pad($i, 2, '0', STR_PAD_LEFT).'.jpg',
                'discount_price' => $discount,
                'price' => (string)$price,
                'title' => fake()->randomElement($productTitles),
                'quantity' => (string)fake()->numberBetween(0, 250),
                'description' => fake()->sentence(18),
                'status' => fake()->randomElement($statuses),
            ]);
        }

        /* =========================
         * CARTS
         * ========================= */
      /*  for ($i = 1; $i <= 250; $i++) {
            Cart::create([
                'user_id' => fake()->numberBetween(2, 102),
                'product_id' => fake()->numberBetween(1, $productCount),
                'quantity' => (string)fake()->numberBetween(1, 2),
            ]);
        }*/

        /* =========================
         * ORDERS
         * ========================= */
        $orderStatuses = ['processing', 'delivered', 'cancelled'];

        for ($i = 1; $i <= 50; $i++) {
            Order::create([
                'user_id' => fake()->numberBetween(2, 102),
                'product_id' => fake()->numberBetween(1, $productCount),
                'quantity' => (string)fake()->numberBetween(1, 3),
                'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                'invoice_number' => 'INV-' . fake()->numberBetween(100000, 999999),
                'total_price' => (string)fake()->numberBetween(1500, 25000),
                'status' => fake()->randomElement($orderStatuses),
                'delivery_address' => fake()->address,
                'created_at' => fake()->dateTimeBetween('-4 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
