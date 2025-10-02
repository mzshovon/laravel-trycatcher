<?php

namespace Mzshovon\LaravelTryCatcher\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = ['level','message','trace','context'];
    protected $casts = ['context' => 'array'];
}
