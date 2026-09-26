<?php

namespace App\Models;

use Database\Factories\MacAgentCommandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mac_device_id', 'action', 'payload', 'status', 'requires_approval', 'result', 'claimed_at', 'completed_at'])]
class MacAgentCommand extends Model
{
    /** @use HasFactory<MacAgentCommandFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<MacDevice, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(MacDevice::class, 'mac_device_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'completed_at' => 'datetime',
            'payload' => 'array',
            'requires_approval' => 'boolean',
            'result' => 'array',
        ];
    }
}
