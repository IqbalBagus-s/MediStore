<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $primaryKey = 'order_item_id';
    
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price', // TAMBAHAN: harga per unit saat order dibuat
        'price', // harga disimpan agar tidak berubah
        'total_price' // TAMBAHAN: quantity × unit_price
    ];
    
    protected $casts = [
        'unit_price' => 'decimal:2', // TAMBAHAN: cast untuk unit_price
        'price' => 'decimal:2',
        'total_price' => 'decimal:2', // TAMBAHAN: cast untuk total_price
        'quantity' => 'integer'
    ];
    
    // Relationship dengan Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    
    // Relationship dengan Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
    
    // UBAH: Accessor untuk total harga item menggunakan total_price dari database
    public function getTotalPriceAttribute($value)
    {
        // Jika total_price sudah ada di database, gunakan itu
        if ($value !== null) {
            return $value;
        }
        
        // Fallback ke perhitungan quantity x unit_price
        return $this->quantity * $this->unit_price;
    }
    
    // Accessor untuk mendapatkan nama produk
    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : 'Produk tidak ditemukan';
    }
    
    // UBAH: Method untuk menyimpan harga saat ini dari produk
    public function setPriceFromProduct()
    {
        if ($this->product) {
            $this->unit_price = $this->product->price;
            $this->price = $this->product->price; // Untuk kompatibilitas
            $this->total_price = $this->quantity * $this->unit_price;
        }
    }
    
    // TAMBAHAN: Method untuk menghitung dan update total_price
    public function calculateTotalPrice()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        return $this->total_price;
    }
    
    // TAMBAHAN: Boot method untuk auto-calculate total_price
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($orderItem) {
            // Auto calculate total_price jika unit_price dan quantity ada
            if ($orderItem->unit_price && $orderItem->quantity) {
                $orderItem->total_price = $orderItem->quantity * $orderItem->unit_price;
            }
        });
    }
}