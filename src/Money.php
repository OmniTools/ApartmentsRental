<?php

namespace OmniTools\ApartmentsRental;

final class Money
{
    /** Empty optional form fields represent zero; persist integer cents. */
    public static function toCents($amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Der Preis muss eine Zahl sein.');
        }
        $cents = round((float) $amount * 100);
        if (!is_finite($cents) || $cents >= PHP_INT_MAX || $cents < PHP_INT_MIN) {
            throw new \InvalidArgumentException('Der Preis liegt außerhalb des gültigen Bereichs.');
        }
        return (int) $cents;
    }
}
