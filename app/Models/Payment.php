<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillabled = [
        'usere_id',
        'amount',
        'tranaction_id',
        'status',
        'message'
    ];
}
