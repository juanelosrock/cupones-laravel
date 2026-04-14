<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_type', 'document_number', 'name', 'lastname',
        'email', 'phone', 'birth_date', 'gender', 'city_id',
        'address', 'status', 'created_via',
        'data_treatment_accepted', 'data_treatment_accepted_at', 'acceptance_ip',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'data_treatment_accepted' => 'boolean',
            'data_treatment_accepted_at' => 'datetime',
        ];
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function meta()
    {
        return $this->hasMany(CustomerMeta::class);
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function acceptances()
    {
        return $this->hasMany(DocumentAcceptance::class);
    }

    public function smsRecipients()
    {
        return $this->hasMany(SmsRecipient::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_customers')
            ->withPivot('source', 'import_batch')
            ->using(CampaignCustomer::class);
    }

    public function getMeta(string $key): mixed
    {
        return $this->meta()->where('key', $key)->value('value');
    }

    public function setMeta(string $key, mixed $value): void
    {
        $this->meta()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->lastname}");
    }
}