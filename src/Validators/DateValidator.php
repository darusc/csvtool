<?php

namespace Csvtool\Validators;

use Carbon\Carbon;

class DateValidator
{
    public static function isValidDateTime(string $datetime): bool
    {
        try {
            Carbon::parse($datetime);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function isValidFormat(string $format): bool
    {
        $now = Carbon::now();
        $formatted = $now->format($format);

        return $formatted !== "FALSE";
    }
}