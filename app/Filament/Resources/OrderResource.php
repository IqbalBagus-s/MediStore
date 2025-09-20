<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationLabel = 'Manajemen Order';
    
    protected static ?string $modelLabel = 'Order';
    
    protected static ?string $pluralModelLabel = 'Orders';

    public static function form(Form $form): Form
    {
        // Kosongkan form karena tidak ada create/edit
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // No. (urutan biasa)
                TextColumn::make('row_number')
                    ->label('No.')
                    ->rowIndex()
                    ->alignCenter(),
                
                // Pemesan (dari name tabel user)
                TextColumn::make('user.name')
                    ->label('Pemesan')
                    ->searchable()
                    ->sortable(),
                
                // Total Amount
                TextColumn::make('total_amount')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
                
                // Tipe Order
                TextColumn::make('order_type')
                    ->label('Tipe Order')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'ambil_ditempat' => 'Ambil Ditempat',
                            'kirim_paket' => 'Kirim Paket',
                            default => $state
                        };
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'ambil_ditempat' => 'success',
                        'kirim_paket' => 'primary',
                        default => 'gray'
                    }),
                
                // Metode Pembayaran
                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'cash_on_delivery' => 'Cash On Delivery',
                            'online_payment' => 'Online Payment',
                            default => $state
                        };
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'cash_on_delivery' => 'warning',
                        'online_payment' => 'success',
                        default => 'gray'
                    }),
                
                // Status Pembayaran
                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'pending' => 'Menunggu Pembayaran',
                            'paid' => 'Sudah Dibayar',
                            'canceled' => 'Dibatalkan',
                            default => $state
                        };
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'canceled' => 'danger',
                        default => 'gray'
                    }),
                
                // Alamat Pengiriman (kondisional berdasarkan tipe order)
                TextColumn::make('shipping_address')
                    ->label('Alamat Pengiriman')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->order_type === 'ambil_ditempat') {
                            return 'Ambil Ditempat';
                        }
                        return $state ?? '-';
                    })
                    ->wrap()
                    ->limit(50),
                
                // Status
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'menunggu_persetujuan' => 'Menunggu Persetujuan',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                            'dikirim' => 'Dikirim',
                            'selesai' => 'Selesai',
                            default => $state
                        };
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'menunggu_persetujuan' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'dikirim' => 'info',
                        'selesai' => 'gray',
                        default => 'gray'
                    }),
                
                // Tanggal Order
                TextColumn::make('created_at')
                    ->label('Tanggal Order')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'menunggu_persetujuan' => 'Menunggu Persetujuan',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                    ])
                    ->placeholder('Semua Status'),
                    
                Tables\Filters\SelectFilter::make('order_type')
                    ->label('Tipe Order')
                    ->options([
                        'ambil_ditempat' => 'Ambil Ditempat',
                        'kirim_paket' => 'Kirim Paket',
                    ])
                    ->placeholder('Semua Tipe'),
                    
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'canceled' => 'Dibatalkan',
                    ])
                    ->placeholder('Semua Status Pembayaran'),
                    
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash_on_delivery' => 'Cash On Delivery',
                        'online_payment' => 'Online Payment',
                    ])
                    ->placeholder('Semua Metode'),
            ])
            ->actions([
                // Tombol Setujui - hanya untuk order dengan payment_status = 'paid' dan status = 'menunggu_persetujuan'
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === 'menunggu_persetujuan' && 
                        $record->payment_status === 'paid'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Order')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui order ini?')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->action(function ($record) {
                        $record->update(['status' => 'disetujui']);
                        
                        Notification::make()
                            ->title('Order Disetujui')
                            ->body("Order dari {$record->user->name} berhasil disetujui.")
                            ->success()
                            ->send();
                    }),
                
                // Tombol Tolak - hanya untuk order dengan status = 'menunggu_persetujuan'
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'menunggu_persetujuan')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Order')
                    ->modalDescription('Apakah Anda yakin ingin menolak order ini?')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'ditolak',
                            'payment_status' => 'canceled'
                        ]);
                        
                        Notification::make()
                            ->title('Order Ditolak')
                            ->body("Order dari {$record->user->name} berhasil ditolak.")
                            ->danger()
                            ->send();
                    }),
                
                // Tombol Kirim (hanya untuk order yang disetujui dan tipe kirim_paket)
                Action::make('ship')
                    ->label('Kirim')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn ($record) => 
                        $record->status === 'disetujui' && 
                        $record->order_type === 'kirim_paket'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Order')
                    ->modalDescription('Apakah Anda yakin order ini sudah dikirim?')
                    ->modalSubmitActionLabel('Ya, Sudah Dikirim')
                    ->action(function ($record) {
                        $record->update(['status' => 'dikirim']);
                        
                        Notification::make()
                            ->title('Order Dikirim')
                            ->body("Order dari {$record->user->name} berhasil diupdate ke status dikirim.")
                            ->info()
                            ->send();
                    }),
                
                // Tombol Selesai (untuk menandai order selesai)
                Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-badge')
                    ->color('gray')
                    ->visible(fn ($record) => 
                        in_array($record->status, ['disetujui', 'dikirim'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Order')
                    ->modalDescription('Apakah Anda yakin order ini sudah selesai?')
                    ->modalSubmitActionLabel('Ya, Selesai')
                    ->action(function ($record) {
                        $record->update(['status' => 'selesai']);
                        
                        Notification::make()
                            ->title('Order Selesai')
                            ->body("Order dari {$record->user->name} berhasil diselesaikan.")
                            ->success()
                            ->send();
                    }),
                
                // Tombol untuk menampilkan pesan jika belum dibayar
                Action::make('payment_pending')
                    ->label('Menunggu Pembayaran')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->disabled()
                    ->visible(fn ($record) => 
                        $record->status === 'menunggu_persetujuan' && 
                        $record->payment_status === 'pending'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk action untuk approve multiple orders (hanya yang sudah paid)
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Orders Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui semua order yang dipilih? (Hanya order yang sudah dibayar yang akan disetujui)')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'menunggu_persetujuan' && $record->payment_status === 'paid') {
                                    $record->update(['status' => 'disetujui']);
                                    $count++;
                                }
                            }
                            
                            if ($count > 0) {
                                Notification::make()
                                    ->title('Orders Disetujui')
                                    ->body("{$count} order yang sudah dibayar berhasil disetujui.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Tidak Ada Order yang Disetujui')
                                    ->body('Tidak ada order yang memenuhi syarat untuk disetujui (harus sudah dibayar).')
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Order')
            ->emptyStateDescription('Belum ada order yang masuk ke sistem.')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }

    public static function getRelations(): array
    {
        return [
            // Kosongkan relations atau tambahkan jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            // Hanya halaman list, hapus create dan edit
            'index' => Pages\ListOrders::route('/'),
        ];
    }
    
    // Hilangkan kemampuan create
    public static function canCreate(): bool
    {
        return false;
    }
    
    // Hilangkan kemampuan edit
    public static function canEdit($record): bool
    {
        return false;
    }
    
    // Hilangkan kemampuan delete jika diperlukan
    public static function canDelete($record): bool
    {
        return false;
    }
    
    // Menambahkan badge notifikasi pada navigation menu
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'menunggu_persetujuan')
                                   ->where('payment_status', 'paid')
                                   ->count();
        return $count > 0 ? (string) $count : null;
    }
    
    // Warna badge notification
    public static function getNavigationBadgeColor(): ?string
    {
        $paidPendingCount = static::getModel()::where('status', 'menunggu_persetujuan')
                                               ->where('payment_status', 'paid')
                                               ->count();
        
        if ($paidPendingCount > 0) {
            return 'success'; // Hijau jika ada yang sudah dibayar menunggu persetujuan
        }
        
        $pendingPaymentCount = static::getModel()::where('status', 'menunggu_persetujuan')
                                                 ->where('payment_status', 'pending')
                                                 ->count();
        
        if ($pendingPaymentCount > 0) {
            return 'warning'; // Kuning jika ada yang menunggu pembayaran
        }
        
        $activeCount = static::getModel()::whereIn('status', ['disetujui', 'dikirim'])->count();
        
        if ($activeCount > 0) {
            return 'info'; // Biru jika ada yang aktif/dikirim
        }
        
        return null; // Tidak tampilkan badge jika tidak ada yang pending
    }
}