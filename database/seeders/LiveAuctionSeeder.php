<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LiveAuctionSeeder extends Seeder
{
    /**
     * Seed live (active now), upcoming, and closed auctions with realistic bids.
     *
     * Distribution:
     *   - 10 LIVE     → status=active, start_time <= now, end_time > now
     *   - 8  UPCOMING → status=active, start_time > now
     *   - 7  CLOSED   → status=closed, end_time < now, has winner
     */
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. Fetch users and categories
        // ----------------------------------------------------------------
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No users found – run UserSeeder first.');
            return;
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->error('No categories found – run CategorySeeder first.');
            return;
        }

        // ----------------------------------------------------------------
        // 2. Catalogue of realistic auction items
        // ----------------------------------------------------------------
        $catalogue = [
            [
                'title'       => 'Apple MacBook Pro 16" M3 Max – Space Black',
                'image'       => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=1200',
                'base_price'  => 185000,
                'increment'   => 5000,
            ],
            [
                'title'       => 'Rolex Submariner Date – Black Cerachrom',
                'image'       => 'https://images.unsplash.com/photo-1523275335684-37861a47ab3b?q=80&w=1200',
                'base_price'  => 750000,
                'increment'   => 25000,
            ],
            [
                'title'       => 'Samsung Galaxy S24 Ultra 5G 512 GB Titanium',
                'image'       => 'https://images.unsplash.com/photo-1678911820864-e2c567c655d7?q=80&w=1200',
                'base_price'  => 110000,
                'increment'   => 2000,
            ],
            [
                'title'       => 'Porsche 911 GT3 RS (2023) – Guards Red',
                'image'       => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1200',
                'base_price'  => 4500000,
                'increment'   => 100000,
            ],
            [
                'title'       => '2-Carat VS1 Diamond Platinum Solitaire Ring',
                'image'       => 'https://images.unsplash.com/photo-1515562141521-7a1dd0dbba18?q=80&w=1200',
                'base_price'  => 320000,
                'increment'   => 10000,
            ],
            [
                'title'       => 'Abstract Original Oil-on-Canvas – "Eternal Dawn"',
                'image'       => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?q=80&w=1200',
                'base_price'  => 75000,
                'increment'   => 5000,
            ],
            [
                'title'       => 'Sony PlayStation 5 Pro – Collector Bundle',
                'image'       => 'https://images.unsplash.com/photo-1607853202273-232359c0b573?q=80&w=1200',
                'base_price'  => 85000,
                'increment'   => 1000,
            ],
            [
                'title'       => 'Audemars Piguet Royal Oak Offshore – Khaki Green',
                'image'       => 'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?q=80&w=1200',
                'base_price'  => 1200000,
                'increment'   => 50000,
            ],
            [
                'title'       => 'ASUS ROG Zephyrus G16 RTX 4090 Gaming Laptop',
                'image'       => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?q=80&w=1200',
                'base_price'  => 230000,
                'increment'   => 5000,
            ],
            [
                'title'       => 'Vintage Blue Sapphire & 22-Karat Gold Necklace',
                'image'       => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=1200',
                'base_price'  => 265000,
                'increment'   => 10000,
            ],
            [
                'title'       => 'Chevrolet Corvette C8 Convertible – Arctic White',
                'image'       => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?q=80&w=1200',
                'base_price'  => 3800000,
                'increment'   => 100000,
            ],
            [
                'title'       => 'Meta Quest Pro VR Headset – Like New',
                'image'       => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?q=80&w=1200',
                'base_price'  => 45000,
                'increment'   => 1000,
            ],
            [
                'title'       => 'Patek Philippe Nautilus 5711/1A – Blue Dial',
                'image'       => 'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?q=80&w=1200',
                'base_price'  => 2800000,
                'increment'   => 100000,
            ],
            [
                'title'       => 'RTX 4090 24 GB Custom Watercooled GPU Rig',
                'image'       => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?q=80&w=1200',
                'base_price'  => 195000,
                'increment'   => 5000,
            ],
            [
                'title'       => 'Rare Pink Freshwater Pearl Jewellery Set',
                'image'       => 'https://images.unsplash.com/photo-1535633302723-997f858509ec?q=80&w=1200',
                'base_price'  => 58000,
                'increment'   => 2000,
            ],
            [
                'title'       => 'Lamborghini Huracán EVO Spyder – 2022 Model',
                'image'       => 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?q=80&w=1200',
                'base_price'  => 6500000,
                'increment'   => 200000,
            ],
            [
                'title'       => 'Omega Speedmaster Professional Moonwatch – Full-Set',
                'image'       => 'https://images.unsplash.com/photo-1526045431048-f857369aba09?q=80&w=1200',
                'base_price'  => 420000,
                'increment'   => 15000,
            ],
            [
                'title'       => 'DJI Inspire 3 Professional Cinema Drone Kit',
                'image'       => 'https://images.unsplash.com/photo-1524143986875-3b098d78b363?q=80&w=1200',
                'base_price'  => 380000,
                'increment'   => 10000,
            ],
            [
                'title'       => 'Signed Vincent van Gogh Exhibition Reprint (Framed)',
                'image'       => 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?q=80&w=1200',
                'base_price'  => 55000,
                'increment'   => 2500,
            ],
            [
                'title'       => 'iPhone 15 Pro Max 1 TB – Natural Titanium',
                'image'       => 'https://images.unsplash.com/photo-1510557880182-3d4d3cba3f9e?q=80&w=1200',
                'base_price'  => 135000,
                'increment'   => 2000,
            ],
            [
                'title'       => 'Aston Martin DB11 Volante – Sunburst Red 2021',
                'image'       => 'https://images.unsplash.com/photo-1502877338535-766e1452684a?q=80&w=1200',
                'base_price'  => 5800000,
                'increment'   => 200000,
            ],
            [
                'title'       => 'Hublot Big Bang Unico – Titanium Chronograph',
                'image'       => 'https://images.unsplash.com/photo-1508685096489-7aac2914b2b8?q=80&w=1200',
                'base_price'  => 680000,
                'increment'   => 20000,
            ],
            [
                'title'       => 'Dell XPS 17 OLED Touch 4K – i9 RTX 4080',
                'image'       => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=1200',
                'base_price'  => 215000,
                'increment'   => 5000,
            ],
            [
                'title'       => 'Emerald Cut Diamond Drop Earrings – 18K White Gold',
                'image'       => 'https://images.unsplash.com/photo-1544256718-3bcf237f3974?q=80&w=1200',
                'base_price'  => 290000,
                'increment'   => 10000,
            ],
            [
                'title'       => 'Lenovo ThinkPad X1 Carbon Gen 12 – Ultra Slim',
                'image'       => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1200',
                'base_price'  => 145000,
                'increment'   => 3000,
            ],
        ];

        // ----------------------------------------------------------------
        // 3. Build auction definitions
        //    Slots 0-9   → LIVE       (10 auctions)
        //    Slots 10-17 → UPCOMING   (8 auctions)
        //    Slots 18-24 → CLOSED     (7 auctions)
        // ----------------------------------------------------------------
        $definitions = [];

        foreach ($catalogue as $idx => $item) {
            if ($idx < 10) {
                // LIVE – currently running
                $start  = Carbon::now()->subHours(rand(2, 36));
                $end    = Carbon::now()->addHours(rand(4, 72));
                $status = 'active';
                $type   = 'live';
            } elseif ($idx < 18) {
                // UPCOMING – not started yet
                $start  = Carbon::now()->addHours(rand(6, 96));
                $end    = Carbon::now()->addHours(rand(120, 240));
                $status = 'active';
                $type   = 'upcoming';
            } else {
                // CLOSED – finished
                $start  = Carbon::now()->subDays(rand(7, 30));
                $end    = Carbon::now()->subDays(rand(1, 6));
                $status = 'closed';
                $type   = 'closed';
            }

            $definitions[] = array_merge($item, [
                'start'  => $start,
                'end'    => $end,
                'status' => $status,
                'type'   => $type,
            ]);
        }

        // ----------------------------------------------------------------
        // 4. Persist auctions
        // ----------------------------------------------------------------
        $created = ['live' => 0, 'upcoming' => 0, 'closed' => 0];

        foreach ($definitions as $def) {
            $seller   = $users->random();
            $category = $categories->random();

            /** @var Auction $auction */
            $auction = Auction::create([
                'user_id'        => $seller->id,
                'category_id'    => $category->id,
                'title'          => $def['title'] . ' [#' . strtoupper(Str::random(5)) . ']',
                'description'    => $this->buildDescription($def['title']),
                'starting_price' => $def['base_price'],
                'current_price'  => $def['base_price'],
                'image'          => $def['image'],
                'start_time'     => $def['start'],
                'end_time'       => $def['end'],
                'status'         => $def['status'],
                'min_increment'  => $def['increment'],
                'location'       => $this->randomLocation(),
                'is_resubmitted' => false,
                'winner_id'      => null,
            ]);

            // Gallery images (reuse same image as extras for demo)
            for ($g = 0; $g < rand(2, 4); $g++) {
                $auction->images()->create([
                    'image_path' => $def['image'],
                    'sort_order' => $g,
                ]);
            }

            // Bids
            if ($def['type'] === 'live') {
                $this->seedBids($auction, rand(3, 12), $users, $seller->id);
            } elseif ($def['type'] === 'closed') {
                $this->seedBids($auction, rand(6, 18), $users, $seller->id);
                // Assign winner = highest bidder
                $winner = $auction->bids()->orderBy('amount', 'desc')->first();
                if ($winner) {
                    $auction->update(['winner_id' => $winner->user_id]);
                }
            }
            // UPCOMING auctions get no bids (bidding hasn't opened yet)

            $created[$def['type']]++;
        }

        $this->command->info(
            sprintf(
                'LiveAuctionSeeder done → %d Live | %d Upcoming | %d Closed',
                $created['live'],
                $created['upcoming'],
                $created['closed']
            )
        );
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Seed progressive bids for an auction.
     * Bidder is always a user OTHER than the seller.
     */
    private function seedBids(Auction $auction, int $count, $users, int $sellerId): void
    {
        $bidders = $users->filter(fn($u) => $u->id !== $sellerId);
        if ($bidders->isEmpty()) {
            $bidders = $users; // fallback if only 1 user
        }

        $price     = $auction->starting_price;
        $increment = $auction->min_increment;
        $bidTime   = $auction->start_time->copy()->addMinutes(5);

        for ($j = 0; $j < $count; $j++) {
            $price    += rand(1, 4) * $increment;
            $bidder    = $bidders->random();
            $bidTime   = $bidTime->addMinutes(rand(5, 60));

            Bid::create([
                'auction_id' => $auction->id,
                'user_id'    => $bidder->id,
                'amount'     => $price,
                'created_at' => $bidTime,
                'updated_at' => $bidTime,
            ]);

            $auction->update(['current_price' => $price]);
        }
    }

    /** Build a short, realistic auction description. */
    private function buildDescription(string $title): string
    {
        return "**{$title}** is available in this exclusive online auction on LaraBids.\n\n"
            . "This is a verified, authenticated item in excellent condition. "
            . "Bidders are encouraged to review all images and ask questions before placing a bid.\n\n"
            . "**Details:**\n"
            . "- Condition: Grade A / Like New\n"
            . "- Authentication: Certificate of Authenticity included\n"
            . "- Shipping: Express Insured Delivery available\n"
            . "- Payment: Due within 48 hours of auction close\n\n"
            . "_Happy Bidding!_";
    }

    /** Return a random Indian city + state string. */
    private function randomLocation(): string
    {
        $locations = [
            'Mumbai, Maharashtra', 'Delhi, Delhi', 'Bengaluru, Karnataka',
            'Hyderabad, Telangana', 'Chennai, Tamil Nadu', 'Kolkata, West Bengal',
            'Pune, Maharashtra', 'Ahmedabad, Gujarat', 'Jaipur, Rajasthan',
            'Surat, Gujarat', 'Lucknow, Uttar Pradesh', 'Chandigarh, Punjab',
        ];
        return $locations[array_rand($locations)];
    }
}
