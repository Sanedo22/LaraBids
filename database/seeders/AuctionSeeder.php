<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear old auction data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bids')->truncate();
        DB::table('watchlists')->truncate();
        DB::table('auction_images')->truncate();
        DB::table('auction_registrations')->truncate();
        Auction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Fetch required relationships
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        // 🟢 Category Wise Realistic Data
        $dataMapping = [
            'Laptops' => [
                'titles' => ['Apple MacBook Pro M3 Max 16"', 'Razer Blade 18 Performance Laptop', 'Dell XPS 17 InfinityEdge', 'ASUS ROG Zephyrus G16'],
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=1200',
                    'https://images.unsplash.com/photo-1603302576837-37561b2e2302?q=80&w=1200',
                    'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=1200',
                    'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1200'
                ],
                'price_range' => [45000, 250000]
            ],
            'Smartphones' => [
                'titles' => ['iPhone 15 Pro Max 1TB Titanium', 'Samsung Galaxy S24 Ultra', 'Google Pixel 8 Pro', 'OnePlus 12 Special Edition'],
                'images' => [
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba3f9e?q=80&w=1200',
                    'https://images.unsplash.com/photo-1678911820864-e2c567c655d7?q=80&w=1200',
                    'https://images.unsplash.com/photo-1696446701796-da61225697cc?q=80&w=1200',
                    'https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=1200'
                ],
                'price_range' => [35000, 150000]
            ],
            'Luxury Watches' => [
                'titles' => ['Rolex Submariner Date Black Dial', 'Audemars Piguet Royal Oak', 'Omega Speedmaster Professional', 'Hublot Big Bang Unico'],
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-21481017106d?q=80&w=1200',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?q=80&w=1200',
                    'https://images.unsplash.com/photo-1508685096489-7aac2914b2b8?q=80&w=1200',
                    'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?q=80&w=1200'
                ],
                'price_range' => [150000, 850000]
            ],
            'Classic Cars' => [
                'titles' => ['Ford Mustang Shelby GT500 1967', 'Porsche 911 Turbo S', 'Mercedes-Benz 300SL Gullwing', 'Chevrolet Corvette C2'],
                'images' => [
                    'https://images.unsplash.com/photo-1549392848-6a3ea6542d90?q=80&w=1200',
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1200',
                    'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?q=80&w=1200',
                    'https://images.unsplash.com/photo-1583121274602-3e2820c69888?q=80&w=1200'
                ],
                'price_range' => [500000, 2500000]
            ],
            'Rings' => [
                'titles' => ['2-Carat Diamond Platinum Ring', 'Sapphire & Gold Eternity Band', 'Vintage Ruby Engagement Ring', 'Emerald Cut Diamond Ring'],
                'images' => [
                    'https://images.unsplash.com/photo-1515562141521-7a1dd0dbba18?q=80&w=1200',
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=1200',
                    'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?q=80&w=1200',
                    'https://images.unsplash.com/photo-1544256718-3bcf237f3974?q=80&w=1200'
                ],
                'price_range' => [45000, 450000]
            ],
            'Paintings' => [
                'titles' => ['Abstract Oil on Canvas - Genesis', 'Modern Landscape Painting', 'Signed Post-Impressionist Sketch', 'Contemporary Pop Art Piece'],
                'images' => [
                    'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?q=80&w=1200',
                    'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?q=80&w=1200',
                    'https://images.unsplash.com/photo-1511193311914-0346f16efe90?q=80&w=1200',
                    'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?q=80&w=1200'
                ],
                'price_range' => [25000, 150000]
            ],
        ];

        $allCategories = Category::whereNotNull('parent_id')->get();
        if ($allCategories->isEmpty()) {
            $allCategories = Category::all();
        }

        $totalAuctions = 50;
        
        for ($i = 1; $i <= $totalAuctions; $i++) {
            
            // Status distribution: 15 Active, 10 Upcoming, 15 Expired (Closed), 10 Pending
            if ($i <= 15) {
                // ACTIVE (LIVE)
                $status = 'active';
                $start = Carbon::now()->subHours(rand(1, 48));
                $end = Carbon::now()->addHours(rand(24, 72));
            } elseif ($i <= 25) {
                // UPCOMING
                $status = 'active';
                $start = Carbon::now()->addHours(rand(12, 48));
                $end = Carbon::now()->addHours(rand(100, 200));
            } elseif ($i <= 40) {
                // EXPIRED (CLOSED)
                $status = 'closed';
                $start = Carbon::now()->subDays(rand(10, 20));
                $end = Carbon::now()->subDays(rand(1, 4));
            } else {
                // PENDING (Wait for Admin)
                $status = 'pending';
                $start = Carbon::now()->addDays(rand(2, 5));
                $end = Carbon::now()->addDays(rand(10, 15));
            }

            // Pick a random specific category with data
            $cat = $allCategories->random();
            $catName = $cat->name;
            $mapping = $dataMapping[$catName] ?? $dataMapping['Laptops'];
            
            $titleIndex = array_rand($mapping['titles']);
            $title = $mapping['titles'][$titleIndex] . " (Lot #" . rand(1001, 9999) . ")";
            $image = $mapping['images'][$titleIndex];
            $price = rand($mapping['price_range'][0], $mapping['price_range'][1]);

            $description = "Exquisite " . $title . " available for auction in the " . $catName . " category. This premium item is part of a private collection and is maintained in pristine condition. Includes full certification, original packaging, and express worldwide delivery. Guaranteed high-value investment opportunity for collectors.";

            $auction = Auction::create([
                'user_id'       => $users->random()->id,
                'category_id'   => $cat->id,
                'title'         => $title,
                'description'   => $description,
                'starting_price'=> $price,
                'current_price' => $price,
                'image'         => $image,
                'start_time'    => $start,
                'end_time'      => $end,
                'status'        => $status,
                'min_increment' => ($price > 100000) ? 1000 : 500,
                'is_resubmitted'=> false,
                'winner_id'     => null, // NO BIDS = NO WINNER
            ]);

            // Add gallery images (2 more random from same mapping)
            $galleryCount = rand(2, 3);
            for ($g = 0; $g < $galleryCount; $g++) {
                $auction->images()->create([
                    'image_path' => $mapping['images'][array_rand($mapping['images'])],
                    'sort_order' => $g,
                ]);
            }
        }
    }
}
