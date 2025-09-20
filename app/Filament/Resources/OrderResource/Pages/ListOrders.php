<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Kosongkan header actions karena tidak ada create
            // atau bisa tambahkan action lain seperti export jika diperlukan
        ];
    }
    
    // Override judul halaman
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Manajemen Order';
    }
    
    // Override subtitle jika diperlukan
    public function getSubheading(): ?string
    {
        return 'Kelola persetujuan dan status order pelanggan';
    }
}