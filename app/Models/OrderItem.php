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
        'price'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
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
    
    // Accessor untuk total harga item (quantity x price)
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price;
    }
    
    // Accessor untuk mendapatkan nama produk
    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : 'Produk tidak ditemukan';
    }
    
    // Method untuk menyimpan harga saat ini dari produk
    public function setPriceFromProduct()
    {
        if ($this->product) {
            $this->price = $this->product->price;
        }
    }
}