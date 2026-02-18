<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AuctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@larabids.com')->first();
        $superAdmin = User::where('email', 'superadmin@larabids.com')->first();

        if (!$admin || !$superAdmin) {
            return;
        }

        $categories = \App\Models\Category::active()->get();

        $priceRanges = [
            'electronics' => [500, 3000],
            'watches' => [1000, 15000],
            'vintage-cars' => [20000, 250000],
            'jewelry' => [800, 12000],
            'art' => [200, 5000],
            'default' => [100, 5000]
        ];

        $images = [
            'electronics' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800',
            'watches' => 'https://images.unsplash.com/photo-1524338198850-8a2ff63aaceb?auto=format&fit=crop&w=800',
            'vintage-cars' => 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=800',
            'jewelry' => 'https://images.unsplash.com/photo-1535633302743-20914fd267d5?auto=format&fit=crop&w=800',
            'art' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?auto=format&fit=crop&w=800',
            'default' => 'https://images.unsplash.com/photo-1513584684374-8bdb74ea9ce1?auto=format&fit=crop&w=800'
        ];

        foreach ($categories as $category) {
            $count = rand(3, 5);
            
            // Determine parent slug for style/image selection
            $parent = $category->parent ?? $category;
            $parentSlug = $parent->slug;
            
            $range = $priceRanges[$parentSlug] ?? $priceRanges['default'];
            $image = $images[$parentSlug] ?? $images['default'];

            for ($i = 1; $i <= $count; $i++) {
                $price = rand($range[0], $range[1]);
                $title = "Premium " . ($category->parent ? $category->name . " " : "") . $parent->name . " Model #" . rand(100, 999);
                
                Auction::create([
                    'user_id' => rand(0, 1) ? $admin->id : $superAdmin->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'description' => "This is a premium listing for a $title. It belongs to the " . ($category->parent ? $category->parent->name . ' > ' : '') . $category->name . " category. High value and authenticated quality.",
                    'starting_price' => $price,
                    'current_price' => $price + (rand(5, 50) * 10),
                    'image' => $image,
                    'start_time' => Carbon::now(),
                    'end_time' => Carbon::now()->addDays(rand(3, 14)),
                    'status' => 'active',
                ]);
            }
        }
    }
}
