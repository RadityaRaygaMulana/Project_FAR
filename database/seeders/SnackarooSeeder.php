<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SnackarooSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Snack Pedas Gurih',
                'slug' => 'snack-pedas-gurih',
                'description' => 'Jajanan renyah bercita rasa pedas nampol dan aroma daun jeruk yang khas.',
                'icon' => '🌶️',
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Basreng Pedas Daun Jeruk',
                        'slug' => 'basreng-pedas-daun-jeruk',
                        'description' => 'Bakso goreng renyah bumbu cabai rawit melimpah dengan aroma daun jeruk segar. Gurih dan bikin nagih!',
                        'price' => 18000,
                        'discount_price' => 15000,
                        'weight_grams' => 200,
                        'spiciness_level' => 4,
                        'stock' => 50,
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'Keripik Kaca / Beling Pedas',
                        'slug' => 'keripik-kaca-pedas',
                        'description' => 'Keripik singkong tipis transparan super renyah dengan taburan bumbu cabai pedas ekstra.',
                        'price' => 15000,
                        'discount_price' => null,
                        'weight_grams' => 150,
                        'spiciness_level' => 5,
                        'stock' => 35,
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'Makaroni Spiral Daun Jeruk',
                        'slug' => 'makaroni-spiral-daun-jeruk',
                        'description' => 'Makaroni spiral krispi dibalut bumbu pedas asin dengan potongan daun jeruk gurih.',
                        'price' => 14000,
                        'discount_price' => 12000,
                        'weight_grams' => 180,
                        'spiciness_level' => 3,
                        'stock' => 60,
                        'is_featured' => false,
                    ],
                    [
                        'name' => 'Cimol Kering Bumbu Rujak Pedas',
                        'slug' => 'cimol-kering-bumbu-rujak-pedas',
                        'description' => 'Cimol kering renyah bumbu rujak pedas manis yang renyah di luar kenyal renyah di dalam.',
                        'price' => 16000,
                        'discount_price' => null,
                        'weight_grams' => 170,
                        'spiciness_level' => 3,
                        'stock' => 40,
                        'is_featured' => false,
                    ],
                ],
            ],
            [
                'name' => 'Sweet Treats & Cookies',
                'slug' => 'sweet-treats-cookies',
                'description' => 'Camilan manis premium yang lumer di mulut dan pas untuk teman ngopi atau ngeteh.',
                'icon' => '🍫',
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Keripik Pisang Cokelat Lumer',
                        'slug' => 'keripik-pisang-cokelat-lumer',
                        'description' => 'Keripik pisang kepok renyah diselimuti cokelat premium tebal yang manis dan lumer.',
                        'price' => 22000,
                        'discount_price' => 19500,
                        'weight_grams' => 220,
                        'spiciness_level' => 0,
                        'stock' => 45,
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'Sus Kering Cokelat Melted',
                        'slug' => 'sus-kering-cokelat-melted',
                        'description' => 'Kue sus mini renyah dengan isian cokelat melimpah yang meleleh saat digigit.',
                        'price' => 20000,
                        'discount_price' => null,
                        'weight_grams' => 200,
                        'spiciness_level' => 0,
                        'stock' => 30,
                        'is_featured' => false,
                    ],
                    [
                        'name' => 'Brownies Crispy Choco Chips',
                        'slug' => 'brownies-crispy-choco-chips',
                        'description' => 'Kepingan brownies tipis super renyah dengan taburan choco chips legit.',
                        'price' => 25000,
                        'discount_price' => 22000,
                        'weight_grams' => 150,
                        'spiciness_level' => 0,
                        'stock' => 25,
                        'is_featured' => true,
                    ],
                ],
            ],
            [
                'name' => 'Keripik & Kerupuk Gurih',
                'slug' => 'keripik-kerupuk-gurih',
                'description' => 'Camilan gurih asin tradisional renyah dengan resep rempah alami.',
                'icon' => '🥔',
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Kerupuk Seblak Kering Gurih',
                        'slug' => 'kerupuk-seblak-kering-gurih',
                        'description' => 'Kerupuk mawar mini bantet dengan aroma kencur dan rasa gurih yang meledak.',
                        'price' => 15000,
                        'discount_price' => null,
                        'weight_grams' => 200,
                        'spiciness_level' => 2,
                        'stock' => 50,
                        'is_featured' => false,
                    ],
                    [
                        'name' => 'Keripik Tempe Renyah Original',
                        'slug' => 'keripik-tempe-renyah-original',
                        'description' => 'Tempe goreng tepung tipis dengan rempah bawang dan ketumbar pilihan.',
                        'price' => 18000,
                        'discount_price' => 16000,
                        'weight_grams' => 250,
                        'spiciness_level' => 0,
                        'stock' => 30,
                        'is_featured' => false,
                    ],
                ],
            ],
            [
                'name' => 'Paket Bundling & Hampers',
                'slug' => 'paket-bundling-hampers',
                'description' => 'Paket hemat kombinasi berbagai snack favorit Snackaroo dengan harga lebih terjangkau.',
                'icon' => '🎁',
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Bundling Teman Nonton (3 Snacks)',
                        'slug' => 'bundling-teman-nonton-3-snacks',
                        'description' => 'Paket komplit: Basreng Daun Jeruk + Keripik Pisang Cokelat + Makaroni Spiral. Hemat 20%!',
                        'price' => 54000,
                        'discount_price' => 45000,
                        'weight_grams' => 600,
                        'spiciness_level' => 2,
                        'stock' => 20,
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'Super Spicy Squad Box (4 Snacks)',
                        'slug' => 'super-spicy-squad-box-4-snacks',
                        'description' => 'Khusus pecinta pedas ekstrem: Basreng Level 4, Keripik Kaca Level 5, Makaroni Level 3, & Seblak Kering!',
                        'price' => 64000,
                        'discount_price' => 52000,
                        'weight_grams' => 730,
                        'spiciness_level' => 5,
                        'stock' => 15,
                        'is_featured' => true,
                    ],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $products = $catData['products'];
            unset($catData['products']);

            $category = Category::create($catData);

            foreach ($products as $prodData) {
                $category->products()->create($prodData);
            }
        }
    }
}
