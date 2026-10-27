<?php

namespace App\Helper;
class CommonHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function getCurrentAccountingYear(){
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Determine accounting year (e.g., "2425" for FY 2024-25)
        $accountingYear = ($currentMonth < 4)
            ? sprintf("%02d%02d", ($currentYear - 1) % 100, $currentYear % 100)
            : sprintf("%02d%02d", $currentYear % 100, ($currentYear + 1) % 100);

        return $accountingYear;
    }

}
