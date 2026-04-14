<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAcceptance extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'customer_id', 'legal_document_id', 'accepted_at',
        'ip_address', 'user_agent', 'channel', 'session_id',
    ];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function legalDocument()
    {
        return $this->belongsTo(LegalDocument::class);
    }
}