<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Page;
use App\Models\FAQ;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BlogPost::factory()->count(30)->create();
        Product::factory()->count(40)->create();
        Page::factory()->count(10)->create();
        FAQ::factory()->count(20)->create();
    }
}
