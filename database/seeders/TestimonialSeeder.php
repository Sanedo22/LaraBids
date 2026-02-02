<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Testimonial::create([
            'name' => 'James Rothwell',
            'role' => 'Private Collector',
            'content' => 'LaraBids redefined my concept of online auctions. The transparency of the platform and the ease of bidding are simply unmatched in the industry.',
            'avatar_url' => 'https://ui-avatars.com/api/?name=James+Roth&background=4e73df&color=ffffff',
            'is_active' => true,
        ]);

        \App\Models\Testimonial::create([
            'name' => 'Sarah Jenkins',
            'role' => 'Antique Dealer',
            'content' => 'As a professional dealer, I need a platform I can trust. LaraBids has become my go-to for sourcing rare items for my shop.',
            'avatar_url' => 'https://ui-avatars.com/api/?name=Sarah+Jenkins&background=1cc88a&color=ffffff',
            'is_active' => true,
        ]);

        \App\Models\Testimonial::create([
            'name' => 'Michael Chen',
            'role' => 'Tech Enthusiast',
            'content' => 'Found an amazing vintage camera here at a great price. The bidding process was exciting and the shipping was fast and secure.',
            'avatar_url' => 'https://ui-avatars.com/api/?name=Michael+Chen&background=36b9cc&color=ffffff',
            'is_active' => true,
        ]);
    }
}
