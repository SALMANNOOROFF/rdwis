<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class HiringTypeMap extends Model
{
    protected $table = 'hrforms.hiring_type_map';

    protected $fillable = [
        'ctc_type',
        'hiring_type',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Resolve mapped hiring type from ctc_type.
     */
    public static function resolveHiringType(?string $ctcType): string
    {
        if (empty($ctcType)) {
            return 'Fresh';
        }

        $row = static::where('is_active', true)
            ->where(function ($q) use ($ctcType) {
                $q->where('ctc_type', $ctcType)
                  ->orWhereRaw('UPPER(ctc_type) = UPPER(?)', [$ctcType]);
            })
            ->first();

        if ($row && !empty($row->hiring_type)) {
            return $row->hiring_type;
        }

        // Standard fallback
        $norm = strtoupper(trim($ctcType));
        return match ($norm) {
            'HG', 'CF', 'FRESH' => 'Fresh',
            'CR', 'RENEWAL'     => 'Renewal',
            'CE', 'EXTENSION'   => 'Extension',
            'RH', 'REHIRING'    => 'Rehiring',
            default             => 'Fresh',
        };
    }
}
