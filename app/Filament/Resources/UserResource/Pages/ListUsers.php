<?php

// File: app/Filament/Resources/UserResource/Pages/ListUsers.php
namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengguna')
                ->visible(function () {
                    $user = Auth::user();
                    return $user && $user->role === 'admin';
                }),
        ];
    }
    
    public function mount(): void
    {
        // Pastikan hanya admin yang bisa mengakses halaman ini
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);
        
        parent::mount();
    }
}