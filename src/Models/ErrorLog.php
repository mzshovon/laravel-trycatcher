<?php

namespace Mzshovon\LaravelTryCatcher\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $level
 * @property string $message
 * @property string $trace
 * @property array $context
 */
class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = ['level','message','trace','context'];
    protected $casts = ['context' => 'array'];
}
