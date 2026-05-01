<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\User;
use App\Models\Category;
use App\Models\Bid;
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
        DB::table('payments')->truncate();
        Auction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Fetch required relationships
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please seed users first.');
            return;
        }

        $allCategories = Category::whereNotNull('parent_id')->get();
        if ($allCategories->isEmpty()) {
            $allCategories = Category::all();
        }
        
        if ($allCategories->isEmpty()) {
            $this->command->error('No categories found. Please seed categories first.');
            return;
        }

        // 🟢 Category Wise Realistic Data with Premium Images
        $dataMapping = [
            'Laptops' => [
                'titles' => ['Apple MacBook Pro M4 Max 16"', 'Razer Blade 18 Gaming Beast', 'Dell XPS 17 OLED Display', 'ASUS ROG Zephyrus G16 (2024)', 'Lenovo Legion 9i Gen 8'],
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1525547718571-03943bc3ba91?auto=format&fit=crop&q=80&w=1200'
                ],
                'price_range' => [45000, 350000]
            ],
            'Smartphones' => [
                'titles' => ['iPhone 15 Pro Max 1TB Titanium', 'Samsung Galaxy S24 Ultra 5G', 'Google Pixel 8 Pro Obsidian', 'OnePlus 12 Flowy Emerald', 'Nothing Phone (2) Special Edition'],
                'images' => [
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba3f9e?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1678911820864-e2c567c655d7?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1696446701796-da61225697cc?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1616348436168-de43ad0db179?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&q=80&w=1200'
                ],
                'price_range' => [35000, 180000]
            ],
            'Luxury Watches' => [
                'titles' => ['Rolex Submariner Date Black Dial', 'Audemars Piguet Royal Oak Blue', 'Patek Philippe Nautilus 5711', 'Omega Speedmaster Moonwatch', 'Hublot Big Bang Unico'],
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-37861a47ab3b?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1508685096489-7aac2914b2b8?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1526045431048-f857369aba09?auto=format&fit=crop&q=80&w=1200'
                ],
                'price_range' => [150000, 950000]
            ],
            'Classic Cars' => [
                'titles' => ['Ford Mustang Shelby GT500 1967', 'Porsche 911 Turbo S (992)', 'Lamborghini Miura S SV', 'Chevrolet Corvette C1 1958', 'Aston Martin DB5 Silver Birch'],
                'images' => [
                    'https://images.unsplash.com/photo-1549392848-6a3ea6542d90?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&q=80&w=1200'
                ],
                'price_range' => [800000, 5000000]
            ],
            'Art' => [
                'titles' => ['Abstract Oil on Canvas - Eternal Sun', 'Post-Modernism Sculpture (Bronze)', 'Signed Picasso-Style Sketch', 'Renaissance Restoration Piece', 'Digital NFT Physical Counterpart'],
                'images' => [
                    'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1511193311914-0346f16efe90?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1574333084133-22bd186e3100?auto=format&fit=crop&q=80&w=1200'
                ],
                'price_range' => [30000, 250000]
            ],
        ];

        $faker = \Faker\Factory::create();
        
        // Status counts for balanced variety
        $counts = [
            'live' => 15,      // Currently active
            'upcoming' => 15,  // Active but starting in future
            'pending' => 10,   // Admin approval needed
            'closed' => 10,    // Ended
        ];

        foreach ($counts as $type => $count) {
            for ($i = 0; $i < $count; $i++) {
                
                if ($type === 'live') {
                    $status = 'active';
                    $start = Carbon::now()->subHours(rand(1, 48));
                    $end = Carbon::now()->addHours(rand(24, 120));
                } elseif ($type === 'upcoming') {
                    $status = 'active';
                    $start = Carbon::now()->addHours(rand(6, 72));
                    $end = Carbon::now()->addDays(rand(5, 10));
                } elseif ($type === 'pending') {
                    $status = 'pending';
                    $start = Carbon::now()->addDays(rand(2, 5));
                    $end = Carbon::now()->addDays(rand(12, 20));
                } else { // closed
                    $status = 'closed';
                    $start = Carbon::now()->subDays(rand(10, 30));
                    $end = Carbon::now()->subDays(rand(1, 5));
                }

                // Pick a random category
                $cat = $allCategories->random();
                $catName = $cat->name;
                
                // Map common names to our dataMapping keys
                $mappedKey = 'Art';
                if (str_contains($catName, 'Laptop') || str_contains($catName, 'Computer')) $mappedKey = 'Laptops';
                elseif (str_contains($catName, 'Phone') || str_contains($catName, 'Mobile')) $mappedKey = 'Smartphones';
                elseif (str_contains($catName, 'Watch')) $mappedKey = 'Luxury Watches';
                elseif (str_contains($catName, 'Car') || str_contains($catName, 'Vehicle')) $mappedKey = 'Classic Cars';
                
                $mapping = $dataMapping[$mappedKey] ?? $dataMapping['Art'];
                
                $titleIndex = array_rand($mapping['titles']);
                $title = $mapping['titles'][$titleIndex] . " [#" . strtoupper(Str::random(5)) . "]";
                $image = $mapping['images'][$titleIndex];
                $price = rand($mapping['price_range'][0], $mapping['price_range'][1]);

                $description = "**" . $title . "** is now available for auction. This item has been verified for authenticity and is in excellent condition.\n\n" . 
                               $faker->paragraphs(2, true) . "\n\n" .
                               "**Key Highlights:**\n" .
                               "- Guaranteed Authenticity\n" .
                               "- Insured Shipping Available\n" .
                               "- Verified Seller\n" .
                               "- Item Location: " . $this->randomLocation();

                $seller = $users->random();

                $auction = Auction::create([
                    'user_id'       => $seller->id,
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
                    'location'      => $this->randomLocation(),
                    'is_resubmitted'=> false,
                    'winner_id'     => null,
                ]);

                // Add gallery images (3 random images from same category)
                $galleryImages = $mapping['images'];
                shuffle($galleryImages);
                for ($g = 0; $g < 3; $g++) {
                    $auction->images()->create([
                        'image_path' => $galleryImages[$g % count($galleryImages)],
                        'sort_order' => $g,
                    ]);
                }

                // Seed Bids for LIVE and CLOSED auctions
                if ($type === 'live') {
                    $this->seedBids($auction, rand(2, 8), $users, $seller->id);
                } elseif ($type === 'closed') {
                    $this->seedBids($auction, rand(5, 12), $users, $seller->id);
                    
                    // Set winner as highest bidder
                    $highestBid = $auction->bids()->orderBy('amount', 'desc')->first();
                    if ($highestBid) {
                        $auction->update(['winner_id' => $highestBid->user_id]);
                    }
                }
            }
        }

        $this->command->info('AuctionSeeder completed successfully with Live, Upcoming, Pending, and Closed statuses.');
    }

    /**
     * Seed progressive bids for an auction.
     */
    private function seedBids($auction, $count, $users, $sellerId)
    {
        $bidders = $users->filter(fn($u) => $u->id !== $sellerId);
        if ($bidders->isEmpty()) $bidders = $users;

        $currentPrice = (float)$auction->starting_price;
        $increment = (float)$auction->min_increment;
        $baseTime = $auction->start_time->copy();

        for ($j = 0; $j < $count; $j++) {
            $currentPrice += (rand(1, 3) * $increment);
            $bidder = $bidders->random();
            
            Bid::create([
                'auction_id' => $auction->id,
                'user_id'    => $bidder->id,
                'amount'     => $currentPrice,
                'created_at' => $baseTime->addMinutes($j * rand(15, 60)),
            ]);

            $auction->update(['current_price' => $currentPrice]);
        }
    }

    /**
     * Return a random Indian location.
     */
    private function randomLocation(): string
    {
        $locations = [
            'Mumbai, Maharashtra', 'Delhi, Delhi', 'Bengaluru, Karnataka',
            'Hyderabad, Telangana', 'Chennai, Tamil Nadu', 'Ahmedabad, Gujarat',
            'Pune, Maharashtra', 'Surat, Gujarat', 'Jaipur, Rajasthan',
            'Lucknow, Uttar Pradesh', 'Kolkata, West Bengal', 'Chandigarh, Punjab',
        ];
        return $locations[array_rand($locations)];
    }
}
