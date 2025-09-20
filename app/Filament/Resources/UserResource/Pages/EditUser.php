<?php

// File: app/Filament/Resources/UserResource/Pages/EditUser.php
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->visible(function () {
                    $currentUser = Auth::user();
                    $record = $this->getRecord();
                    
                    // Cek apakah user adalah admin
                    if (!$currentUser || $currentUser->role !== 'admin') {
                        return false;
                    }
                    
                    // Admin tidak bisa hapus dirinya sendiri
                    if ($currentUser->id === $record->id) {
                        return false;
                    }
                    
                    return true;
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function mount(int | string $record): void
    {
        $currentUser = Auth::user();
        
        // Pastikan hanya admin yang bisa edit
        abort_unless($currentUser && $currentUser->role === 'admin', 403);
        
        parent::mount($record);
        
        // Setelah record dimuat, cek apakah record yang akan diedit adalah customer
        $recordToEdit = $this->getRecord();
        if ($recordToEdit->role === 'customer') {
            abort(403, 'Admin tidak diizinkan mengedit user customer.');
        }
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengguna berhasil diperbarui';
    }
}
