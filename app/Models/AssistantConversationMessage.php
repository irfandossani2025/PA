<?php

namespace App\Models;

use Database\Factories\AssistantConversationMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'role', 'content', 'mac_agent_command_id'])]
class AssistantConversationMessage extends Model
{
    /** @use HasFactory<AssistantConversationMessageFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<MacAgentCommand, $this>
     */
    public function command(): BelongsTo
    {
        return $this->belongsTo(MacAgentCommand::class, 'mac_agent_command_id');
    }
}
