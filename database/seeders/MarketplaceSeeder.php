<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Marketplace Categories Only (No Dummy Products)
        $categoriesData = [
            [
                'name' => 'Elektronik & Gadget',
                'slug' => 'elektronik-gadget',
                'description' => 'Smartphone, TWS, Smartwatch, Audio & Aksesoris Gadget Terlengkap',
                'icon' => '📱',
            ],
            [
                'name' => 'Fashion & Pakaian',
                'slug' => 'fashion-pakaian',
                'description' => 'Sneakers, Kemeja, Kaos Oversize, Jaket Hoodie & Tas Trendi',
                'icon' => '👕',
            ],
            [
                'name' => 'Makanan & Minuman',
                'slug' => 'makanan-minuman',
                'description' => 'Kopi Arabika, Camilan Renyah, Makanan Siap Saji & Minuman',
                'icon' => '🍜',
            ],
            [
                'name' => 'Rumah Tangga & Dapur',
                'slug' => 'rumah-tangga-dapur',
                'description' => 'Humidifier, Set Pisau Masak, Lampu Estetik & Perlengkapan Rumah',
                'icon' => '🏠',
            ],
            [
                'name' => 'Kecantikan & Skincare',
                'slug' => 'kecantikan-skincare',
                'description' => 'Sunscreen, Serum Pencerah, Skincare & Parfum Original',
                'icon' => '💄',
            ],
            [
                'name' => 'Hobi, Gaming & Sport',
                'slug' => 'hobi-gaming-sport',
                'description' => 'Mechanical Keyboard, Gamepad Wireless, Matras Yoga & Perlengkapan Olahraga',
                'icon' => '🎮',
            ],
        ];

        foreach ($categoriesData as $c) {
            Category::updateOrCreate(
                ['slug' => $c['slug']],
                $c
            );
        }
    }
}
