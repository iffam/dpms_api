<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitUsage extends Model
{
    /** @use HasFactory<\Database\Factories\PermitUsageFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'permit_id',
        'zone_id',
    ];


    public function permit()
    {
        return $this->belongsTo(Permit::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
