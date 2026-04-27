<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          // 1. Create roles first
            UserSeeder::class,          // 2. Create users & assign roles
            CategorySeeder::class,      // 3. Seed categories
            AuctionSeeder::class,       // 4. Original 80-auction seeder
            LiveAuctionSeeder::class,   // 5. 10 Live | 8 Upcoming | 7 Closed
            TestimonialSeeder::class,   // 6. Testimonials
        ]);
    }
}

