<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $primaryKey = 'invoice_id';
    
    protected $fillable = [
        'order_id',
        'invoice_number'
    ];
    
    // Relationship dengan Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    
    // Generate invoice number otomatis
    public static function generateInvoiceNumber()
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        
        // Cari invoice terakhir di tahun dan bulan ini
        $lastInvoice = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', Carbon::now()->month)
                          ->orderBy('invoice_id', 'desc')
                          ->first();
        
        if ($lastInvoice) {
            // Ambil nomor urut dari invoice terakhir
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: INV-YYYY-MM-0001
        return sprintf('INV-%04d-%02d-%04d', $year, $month, $nextNumber);
    }
    
    // Boot method untuk auto-generate invoice number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }
    
    // Accessor untuk mendapatkan user dari order
    public function getUserAttribute()
    {
        return $this->order ? $this->order->user : null;
    }
    
    // Accessor untuk mendapatkan total amount dari order
    public function getTotalAmountAttribute()
    {
        return $this->order ? $this->order->total_amount : 0;
    }
    
    // Scope untuk pencarian berdasarkan invoice number
    public function scopeByInvoiceNumber($query, $invoiceNumber)
    {
        return $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
    }
    
    // Method untuk mendapatkan invoice dengan format yang lebih lengkap
    public function getFormattedInvoiceAttribute()
    {
        return [
            'invoice_number' => $this->invoice_number,
            'order_id' => $this->order_id,
            'customer_name' => $this->user->name ?? 'N/A',
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}