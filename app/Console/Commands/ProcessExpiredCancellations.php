<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:process-cancellations')]
#[Description('Otomatis batalkan pesanan yang pengajuan pembatalannya melewati batas waktu 3 hari (72 jam)')]
class ProcessExpiredCancellations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa pesanan dengan pengajuan pembatalan kadaluarsa (> 3 hari)...');

        $cancelledCount = Order::autoCancelExpiredRequests();

        if ($cancelledCount > 0) {
            $this->info("Berhasil membatalkan {$cancelledCount} pesanan kadaluarsa dan mengembalikan stok barang.");
        } else {
            $this->info('Tidak ada pengajuan pembatalan yang kadaluarsa.');
        }

        return Command::SUCCESS;
    }
}
