<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

class Memo extends Model
{
    use SoftDeletes;

    protected $table = 'memos';

    protected $fillable = [
        'title',
        'reference_number',
        'description',
        'category',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
        'recipient_type',
        'recipient_ids',
        'memo_date',
        'expires_at',
        'status',
        'published_at',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'memo_date'     => 'date',
        'expires_at'    => 'datetime',
        'published_at'  => 'datetime',
        'file_size'     => 'integer',
    ];

    /**
     * The user who uploaded this memo.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * All acknowledgments submitted for this memo.
     */
    public function acknowledgments(): HasMany
    {
        return $this->hasMany(MemoAcknowledgment::class, 'memo_id');
    }

    /**
     * Check if a specific user has acknowledged this memo.
     */
    public function isAcknowledgedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        $userId = is_numeric($user) ? $user : $user->id;

        return $this->acknowledgments()->where('user_id', $userId)->exists();
    }

    /**
     * Retrieve the acknowledgment record for a specific user.
     */
    public function getAcknowledgmentFor($user): ?MemoAcknowledgment
    {
        if (!$user) {
            return null;
        }

        $userId = is_numeric($user) ? $user : $user->id;

        return $this->acknowledgments()->where('user_id', $userId)->first();
    }

    /**
     * Determine if a user is authorized to view/access this memo.
     */
    public function canBeAccessedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        // Admins, Super-Users, managers, or the uploader can always access
        if ($this->uploaded_by === $user->id) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super-User', 'Admin', 'Super Admin'])) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('manage_memos')) {
            return true;
        }

        // Check recipient scope
        if ($this->recipient_type === 'all') {
            return true;
        }

        if ($this->recipient_type === 'roles' && !empty($this->recipient_ids)) {
            return method_exists($user, 'hasAnyRole') && $user->hasAnyRole($this->recipient_ids);
        }

        if ($this->recipient_type === 'users' && !empty($this->recipient_ids)) {
            return in_array($user->id, (array) $this->recipient_ids);
        }

        return false;
    }

    /**
     * Resolve the target recipient User models for notification and tracking.
     */
    public function targetRecipients()
    {
        if ($this->recipient_type === 'all') {
            return User::all();
        }

        if ($this->recipient_type === 'roles' && !empty($this->recipient_ids)) {
            return User::role($this->recipient_ids)->get();
        }

        if ($this->recipient_type === 'users' && !empty($this->recipient_ids)) {
            return User::whereIn('id', (array) $this->recipient_ids)->get();
        }

        return collect();
    }

    /**
     * Calculate summary statistics for acknowledgment tracking.
     */
    public function acknowledgmentStats(): array
    {
        $recipients = $this->targetRecipients();
        $total = $recipients->count();
        $acknowledged = $this->acknowledgments()->count();
        $pending = max(0, $total - $acknowledged);
        $percentage = $total > 0 ? round(($acknowledged / $total) * 100, 1) : 0;

        return [
            'total'        => $total,
            'acknowledged' => $acknowledged,
            'pending'      => $pending,
            'percentage'   => $percentage,
        ];
    }

    /**
     * Human-readable file size format.
     */
    public function formattedFileSize(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if memo is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Generate WhatsApp share URL with prefilled text and memo link.
     */
    public function whatsAppShareUrl(): string
    {
        $url = route('memos.show', $this->id);
        $ref = $this->reference_number ? " [{$this->reference_number}]" : '';
        $text = "Please review and acknowledge this official memo:\n\n"
              . "📋 *{$this->title}*{$ref}\n"
              . "📅 Date: " . ($this->memo_date ? $this->memo_date->format('d M Y') : date('d M Y')) . "\n\n"
              . "🔗 Click to open & acknowledge:\n{$url}";

        return 'https://api.whatsapp.com/send?text=' . urlencode($text);
    }
}
