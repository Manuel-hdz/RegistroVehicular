<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationPolicy extends Model
{
    use HasFactory;

    public const FIXED_RANGES = [
        1 => '1 año',
        2 => '2 años',
        3 => '3 años',
        4 => '4 años',
        5 => '5 años',
        6 => '6 a 10 años',
        11 => '11 a 15 años',
        16 => '16 a 20 años',
        21 => '21 a 25 años',
        26 => '26 a 30 años',
        31 => '31 a 35 años',
    ];

    protected $fillable = [
        'service_year',
        'vacation_days',
        'notes',
        'active',
    ];

    protected $casts = [
        'service_year' => 'integer',
        'vacation_days' => 'integer',
        'active' => 'boolean',
    ];

    public static function fixedRanges(): array
    {
        return self::FIXED_RANGES;
    }
}
