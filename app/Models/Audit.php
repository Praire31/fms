<?php

// app/Models/Audit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',       // snapshot ya role
        'action',
        'target',
        'ip_address',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
