<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    protected $primaryKey = 'product_id';
    
    use HasFactory, SoftDeletes; // Jika menggunakan soft delete
    
    protected $fillable = [
        'category_id',
        'name', 
        'description',
        'image',
        'price',
        'stock'
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}