<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageConfig extends Model
{
    protected $fillable = [
        'name', 'template',
        'logo_url', 'hero_image_url', 'brand_color', 'bg_color',
        'heading', 'subheading', 'body_html', 'button_text',
        'success_heading', 'success_text', 'footer_text',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function smsCampaigns()
    {
        return $this->hasMany(SmsCampaign::class, 'landing_config_id');
    }

    /** Returns the default config or null. */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->latest()->first();
    }
}
