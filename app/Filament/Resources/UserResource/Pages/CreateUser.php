<?php

// File: app/Filament/Resources/UserResource/Pages/CreateUser.php
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function mount(): void
    {
        // Pastikan hanya admin yang bisa mengakses halaman ini
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);
        
        parent::mount();
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengguna berhasil ditambahkan';
    }
}

