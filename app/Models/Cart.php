<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'cart';
    protected $primaryKey = 'cart_id';
    
    protected $fillable = [
        'id',
        'product_id',
        'quantity'
    ];
    
    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
    
    // Relationship dengan Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
    
    // Accessor untuk total harga item
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->product->price;
    }
}