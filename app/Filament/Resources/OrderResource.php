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
            ])
            ->actions([
                // Tombol Setujui
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'menunggu_persetujuan')
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
                
                // Tombol Tolak
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
                        $record->update(['status' => 'ditolak']);
                        
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
            ])
            ->bulkActions([
                // Hapus bulk actions atau buat minimal
                Tables\Actions\BulkActionGroup::make([
                    // Bulk action untuk approve multiple orders
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Orders Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui semua order yang dipilih?')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'menunggu_persetujuan') {
                                    $record->update(['status' => 'disetujui']);
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Orders Disetujui')
                                ->body("{$count} order berhasil disetujui.")
                                ->success()
                                ->send();
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
        $count = static::getModel()::whereNotIn('status', ['selesai'])->count();
        return $count > 0 ? (string) $count : null;
    }
    
    // Warna badge notification
    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'menunggu_persetujuan')->count();
        
        if ($pendingCount > 0) {
            return 'warning'; // Kuning jika ada yang menunggu persetujuan
        }
        
        $activeCount = static::getModel()::whereIn('status', ['disetujui', 'dikirim'])->count();
        
        if ($activeCount > 0) {
            return 'info'; // Biru jika ada yang aktif/dikirim
        }
        
        return 'success'; // Hijau jika tidak ada yang pending
    }
}