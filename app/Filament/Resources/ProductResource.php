<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    
    protected static ?string $navigationLabel = 'Produk';
    
    protected static ?string $modelLabel = 'Produk';
    
    protected static ?string $pluralModelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama produk'),
                            
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'category_name')
                            ->required()
                            ->placeholder('Pilih kategori produk')
                            ->searchable()
                            ->preload(),
                            
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(4)
                            ->placeholder('Masukkan deskripsi produk'),
                            
                        FileUpload::make('image')
                            ->label('Gambar Produk')
                            ->image()
                            ->disk('public')
                            ->directory('images')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('450')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->placeholder('Upload gambar produk (maksimal 2MB)')
                            ->downloadable()
                            ->openable()
                            ->deletable(true)
                            ->moveFiles(),
                            
                        TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->minValue(0)
                            ->step(1000),
                            
                        TextInput::make('stock')
                            ->label('Stok')
                            ->required()
                            ->numeric()
                            ->placeholder('0')
                            ->minValue(0)
                            ->step(1),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->getStateUsing(function ($rowLoop, $livewire) {
                        return $rowLoop->iteration + ($livewire->getTableRecordsPerPage() * ($livewire->getTablePage() - 1));
                    })
                    ->alignCenter()
                    ->width('60px'),
                    
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                    
                TextColumn::make('category.category_name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                    
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->disk('public')
                    ->height(60)
                    ->width(80)
                    ->defaultImageUrl('/images/no-image.png'),
                    
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignRight()
                    ->weight('medium'),
                    
                TextColumn::make('stock')
                    ->label('Stok')
                    ->formatStateUsing(function ($state) {
                        return $state == 0 ? 'Kosong' : number_format($state);
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state == '0' => 'danger',
                        (int)$state <= 10 => 'warning',
                        default => 'success',
                    })
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filter Kategori')
                    ->relationship('category', 'category_name')
                    ->placeholder('Semua Kategori')
                    ->preload(),
                    
                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Stok Kosong')
                    ->query(fn (Builder $query): Builder => $query->where('stock', 0))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '>', 0)->where('stock', '<=', 10))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Produk')
                    ->modalDescription('Apakah Anda yakin ingin menghapus produk ini? Data yang dihapus tidak dapat dikembalikan.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Dipilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Produk Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus produk yang dipilih? Data yang dihapus tidak dapat dikembalikan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->emptyStateHeading('Belum ada produk')
            ->emptyStateDescription('Mulai dengan menambahkan produk pertama Anda.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Produk')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}