<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $primaryKey = 'order_id';
    
    protected $fillable = [
        'id', // user_id
        'total_amount', // TAMBAHAN: total harga keseluruhan
        'order_type',
        'status',
        'payment_method',
        'payment_status', // TAMBAHAN: status pembayaran
        'shipping_address'
    ];
    
    // Cast untuk enum values
    protected $casts = [
        'total_amount' => 'decimal:2', // TAMBAHAN: cast untuk total_amount
        'order_type' => 'string',
        'status' => 'string',
        'payment_method' => 'string',
        'payment_status' => 'string', // TAMBAHAN: cast untuk payment_status
    ];
    
    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
    
    // Relationship dengan Order Items
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
    
    // TAMBAHAN: Relationship dengan Invoice
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'order_id');
    }
    
    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    // Scope untuk filter berdasarkan order type
    public function scopeByOrderType($query, $type)
    {
        return $query->where('order_type', $type);
    }
    
    // TAMBAHAN: Scope untuk filter berdasarkan payment status
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }
    
    // Scope untuk order yang menunggu persetujuan
    public function scopePending($query)
    {
        return $query->where('status', 'menunggu_persetujuan');
    }
    
    // Scope untuk order yang sudah disetujui
    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui');
    }
    
    // Accessor untuk label status
    public function getStatusLabelAttribute()
    {
        $labels = [
            'menunggu_persetujuan' => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    // Accessor untuk label order type
    public function getOrderTypeLabelAttribute()
    {
        $labels = [
            'ambil_ditempat' => 'Ambil Ditempat',
            'kirim_paket' => 'Kirim Paket'
        ];
        
        return $labels[$this->order_type] ?? $this->order_type;
    }
    
    // Accessor untuk label payment method
    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'cash_on_delivery' => 'Cash On Delivery',
            'online_payment' => 'Online Payment'
        ];
        
        return $labels[$this->payment_method] ?? $this->payment_method;
    }
    
    // TAMBAHAN: Accessor untuk label payment status
    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Sudah Dibayar',
            'canceled' => 'Dibatalkan'
        ];
        
        return $labels[$this->payment_status] ?? $this->payment_status;
    }
    
    // Accessor untuk alamat pengiriman yang disesuaikan dengan tipe order
    public function getFormattedShippingAddressAttribute()
    {
        if ($this->order_type === 'ambil_ditempat') {
            return 'Ambil Ditempat';
        }
        
        return $this->shipping_address ?? '-';
    }
    
    // TAMBAHAN: Method untuk menghitung total amount dari order items
    public function calculateTotalAmount()
    {
        return $this->orderItem()->sum(DB::raw('quantity * unit_price'));
    }
    
    // TAMBAHAN: Method untuk update total amount
    public function updateTotalAmount()
    {
        $this->total_amount = $this->calculateTotalAmount();
        $this->save();
    }
}