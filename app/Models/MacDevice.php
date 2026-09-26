<?php

namespace App\Models;

use Database\Factories\MacDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'token_hash', 'last_status', 'last_seen_at'])]
#[Hidden(['token_hash'])]
class MacDevice extends Model
{
    /** @use HasFactory<MacDeviceFactory> */
    use HasFactory;

    /**
     * @return HasMany<MacAgentCommand, $this>
     */
    public function commands(): HasMany
    {
        return $this->hasMany(MacAgentCommand::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'last_status' => 'array',
        ];
    }
}
