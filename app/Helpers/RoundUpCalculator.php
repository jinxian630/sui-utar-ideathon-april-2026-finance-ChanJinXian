<?php

namespace App\Helpers;

class RoundUpCalculator
{
    /**
     * Given 4.37 returns 0.63. Returns 0.00 for whole dollar amounts.
     */
    public static function calculate(float $amount): float
    {
        $cents   = round(fmod($amount, 1) * 100);
        $roundUp = $cents > 0 ? (100 - $cents) / 100 : 0.00;
        return round($roundUp, 4);
    }
}
