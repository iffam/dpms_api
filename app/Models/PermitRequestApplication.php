<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermitRequestApplication extends Model
{
    /** @use HasFactory<\Database\Factories\PermitRequestApplicationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'permit_type',
        'status',
        'status_remarks',
        'active_at',
        'expired_at',
        'zones',
        'justification',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTimestamps();
    }
}
