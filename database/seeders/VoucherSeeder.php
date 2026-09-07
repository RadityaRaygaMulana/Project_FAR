<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vouchers = [
            // 1. KATEGORI ONGKIR (SHIPPING)
            [
                'code' => 'ONGKIRFREE',
                'name' => 'Bebas Ongkir Se-Indonesia',
                'category' => 'shipping',
                'type' => 'fixed',
                'reward_amount' => 15000,
                'max_discount' => null,
                'min_spend' => 30000,
                'description' => 'Gratis ongkos kirim hingga Rp 15.000 untuk pengiriman ke seluruh penjuru Indonesia.',
                'quota' => 5000,
                'is_active' => true,
            ],
            [
                'code' => 'ONGKIRSUPER',
                'name' => 'Potongan Ongkir Tanpa Minimal',
                'category' => 'shipping',
                'type' => 'fixed',
                'reward_amount' => 10000,
                'max_discount' => null,
                'min_spend' => 0,
                'description' => 'Potongan ongkos kirim langsung Rp 10.000 tanpa syarat minimal belanja.',
                'quota' => 5000,
                'is_active' => true,
            ],
            [
                'code' => 'ONGKIRXTRA',
                'name' => 'Bebas Ongkir XTRA Rp 20.000',
                'category' => 'shipping',
                'type' => 'fixed',
                'reward_amount' => 20000,
                'max_discount' => null,
                'min_spend' => 50000,
                'description' => 'Subsidi ongkos kirim spesial hingga Rp 20.000 untuk belanja lebih hemat.',
                'quota' => 3000,
                'is_active' => true,
            ],

            // 2. KATEGORI DISKON PRODUK (DISCOUNT)
            [
                'code' => 'DISKONMEMBER',
                'name' => 'Diskon Belanja Member Rp 15.000',
                'category' => 'discount',
                'type' => 'fixed',
                'reward_amount' => 15000,
                'max_discount' => null,
                'min_spend' => 50000,
                'description' => 'Potongan harga langsung Rp 15.000 untuk semua produk belanjaan kamu.',
                'quota' => 5000,
                'is_active' => true,
            ],
            [
                'code' => 'DISKON20',
                'name' => 'Diskon Kilat 20% s/d Rp 25.000',
                'category' => 'discount',
                'type' => 'percentage',
                'reward_amount' => 20,
                'max_discount' => 25000,
                'min_spend' => 75000,
                'description' => 'Diskon 20% hingga Rp 25.000 untuk belanja hemat di katalog pilihan.',
                'quota' => 3000,
                'is_active' => true,
            ],
            [
                'code' => 'SUPERHEMAT',
                'name' => 'Potongan Super Hemat Rp 30.000',
                'category' => 'discount',
                'type' => 'fixed',
                'reward_amount' => 30000,
                'max_discount' => null,
                'min_spend' => 100000,
                'description' => 'Potongan belanja besar Rp 30.000 untuk transaksi mulai dari Rp 100.000.',
                'quota' => 2000,
                'is_active' => true,
            ],
            [
                'code' => 'KILAT5RB',
                'name' => 'Potongan Instan Rp 5.000',
                'category' => 'discount',
                'type' => 'fixed',
                'reward_amount' => 5000,
                'max_discount' => null,
                'min_spend' => 0,
                'description' => 'Potongan langsung Rp 5.000 tanpa syarat minimal belanja untuk semua pengguna.',
                'quota' => 10000,
                'is_active' => true,
            ],
        ];

        foreach ($vouchers as $item) {
            Voucher::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
