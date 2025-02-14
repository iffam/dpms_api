<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permit extends Model
{
    /** @use HasFactory<\Database\Factories\PermitFactory> */
    use HasFactory, SoftDeletes;

    const TYPE_RESTRICTED = 'restricted';
    const TYPE_TEMPORARY = 'temporary';
    const TYPE_PERMANENT = 'permanent';

    protected $casts = [
        'active_at' => 'datetime',
        'expired_at' => 'datetime'
    ];

    public function isRestricted(): bool
    {
        return $this->permit_type === self::TYPE_RESTRICTED;
    }

    public function getRestrictions(): array
    {
        if (!$this->isRestricted()) {
            return [];
        }

        return [
            'requires_supervision' => true,
        ];
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class)->withTimestamps();
    }
}
