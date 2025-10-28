<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthToken extends Model
{
    use HasFactory;

    public const TYPE_ACCESS = 'access';

    public const TYPE_REFRESH = 'refresh';

    protected $table = 'auth_tokens';

    protected $fillable = [
        'user_id',
        'token_hash',
        'type',
        'refresh_token_id',
        'expires_at',
        'last_used_at',
        'revoked',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function refreshToken(): BelongsTo
    {
        return $this->belongsTo(self::class, 'refresh_token_id');
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(self::class, 'refresh_token_id');
    }

    public function scopeActiveAccessToken($query, string $tokenHash)
    {
        return $query->where('type', self::TYPE_ACCESS)
            ->where('token_hash', $tokenHash)
            ->where('revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->expires_at;

        if (! $expiresAt instanceof CarbonInterface) {
            return false;
        }

        return $expiresAt->isPast();
    }

    public function revoke(): void
    {
        if ($this->revoked) {
            return;
        }

        $this->forceFill(['revoked' => true])->save();
    }

    public function markUsed(): void
    {
        $this->forceFill([
            'last_used_at' => now(),
        ])->save();
    }
}
