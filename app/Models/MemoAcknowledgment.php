<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoAcknowledgment extends Model
{
    protected $table = 'memo_acknowledgments';

    protected $fillable = [
        'memo_id',
        'user_id',
        'acknowledged_at',
        'ip_address',
        'user_agent',
        'confirmation_statement',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class, 'memo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
