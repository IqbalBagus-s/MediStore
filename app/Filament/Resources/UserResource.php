<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Pengguna';
    
    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama User')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->maxLength(255),
                    
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->placeholder(fn (string $operation): string => 
                        $operation === 'create' ? 'Masukkan password' : 'Kosongkan jika tidak ingin mengubah password'
                    ),
                    
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'customer' => 'Customer',
                    ])
                    ->required()
                    ->default('customer'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex()
                    ->alignCenter(),
                    
                TextColumn::make('name')
                    ->label('Nama User')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'customer' => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'customer' => 'Customer',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Role')
                    ->options([
                        'admin' => 'Admin',
                        'customer' => 'Customer',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(function (User $record) {
                        $currentUser = Auth::user();
                        
                        // Jika user yang login bukan admin, sembunyikan tombol edit
                        if (!$currentUser || $currentUser->role !== 'admin') {
                            return false;
                        }
                        
                        // Admin tidak bisa edit user customer, hanya bisa edit admin lain
                        if ($record->role === 'customer') {
                            return false;
                        }
                        
                        return true;
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(function (User $record) {
                        $currentUser = Auth::user();
                        
                        // Hanya admin yang bisa hapus
                        if (!$currentUser || $currentUser->role !== 'admin') {
                            return false;
                        }
                        
                        // Admin tidak bisa hapus dirinya sendiri
                        if ($currentUser->id === $record->id) {
                            return false;
                        }
                        
                        return true;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () {
                            $user = Auth::user();
                            return $user && $user->role === 'admin';
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    // Hanya admin yang bisa mengakses resource ini
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }
    
    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }
    
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        
        // Harus admin untuk bisa edit
        if (!$user || $user->role !== 'admin') {
            return false;
        }
        
        // Admin tidak bisa edit user customer
        if ($record->role === 'customer') {
            return false;
        }
        
        return true;
    }
    
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return false;
        }
        
        // Admin tidak bisa hapus dirinya sendiri
        if ($user->id === $record->id) {
            return false;
        }
        
        return true;
    }
}