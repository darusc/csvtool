<?php

namespace Csvtool\Validators;

use Carbon\Carbon;
use DateTime;

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
        // Create a new datetime with the unix timestamp 0 (corresponding to 1970-01-01)
        $datetime = new DateTime();
        $datetime->setTimestamp(0);

        // Format it using the format we need to test
        $formatted = $datetime->format($format);

        // If the format is valid strtotime should return 0 as that is the timestamp
        // we created the datetime with, otherwise the format is not valid
        // TODO find a better way of determining if the given format is valid or not
        // TODO this approach doesn't work very well with hours and might give false results
        // return strtotime($formatted . ' UTC') === 0;
        return true;
    }
}