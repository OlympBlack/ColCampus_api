<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CollectionImmobiliere extends Model
{
    use HasFactory;

    protected $table = 'collections_immobilieres';

    protected $fillable = [
        'slug',
        'title',
        'room_type_value',
        'price_range',
        'fallback_count',
        'image',
        'hero_banner',
        'description',
        'hero_description',
        'features',
        'ideal_for',
        'avg_rent',
        'ordre',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'fallback_count' => 'integer',
        'ordre' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'hero_banner_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function getHeroBannerUrlAttribute(): ?string
    {
        if (!$this->hero_banner) {
            return $this->image_url;
        }

        if (str_starts_with($this->hero_banner, 'http://') || str_starts_with($this->hero_banner, 'https://')) {
            return $this->hero_banner;
        }

        return Storage::disk('public')->url($this->hero_banner);
    }

    /**
     * Scope active collections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre', 'asc')->orderBy('id', 'asc');
    }
}
