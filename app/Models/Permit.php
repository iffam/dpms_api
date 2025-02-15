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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'permit_request_application_id',
        'permit_type',
        'active_at',
        'expired_at',
        'suspended_at',
    ];

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

    public function permitRequestApplication(): BelongsToMany
    {
        return $this->belongsToMany(PermitRequestApplication::class)->withTimestamps();
    }
}
