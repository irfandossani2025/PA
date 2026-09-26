<?php

namespace App\Models;

use Database\Factories\MacTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'mac_device_id', 'title', 'request', 'status', 'result'])]
class MacTask extends Model
{
    /** @use HasFactory<MacTaskFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<MacDevice, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(MacDevice::class, 'mac_device_id');
    }

    /** @return HasMany<MacAgentCommand, $this> */
    public function commands(): HasMany
    {
        return $this->hasMany(MacAgentCommand::class);
    }

    protected function casts(): array
    {
        return ['result' => 'array'];
    }
}
