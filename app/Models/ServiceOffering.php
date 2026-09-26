<?php

namespace App\Models;

use Database\Factories\ServiceOfferingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'business_area',
    'slug',
    'name',
    'category',
    'starting_price_omr',
    'price_note',
    'summary',
    'capabilities',
    'sales_playbook',
    'position',
    'is_active',
])]
class ServiceOffering extends Model
{
    /** @use HasFactory<ServiceOfferingFactory> */
    use HasFactory;

    /**
     * @param  Builder<ServiceOffering>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'sales_playbook' => 'array',
            'starting_price_omr' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }
}
