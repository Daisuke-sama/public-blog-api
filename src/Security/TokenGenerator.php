<?php


namespace App\Security;


class TokenGenerator
{
    private const SYMBOLS = 'ASDFGHJKL:POIUYTREWZXCVBNM<>(*&^%$34256789';

    public function getRandomSecureToken(int $length = 30): string
    {
        $token = '';
        $maxNumber = strlen(self::SYMBOLS);

        for ($i = 0; $i < $length; $i++) {
            $token .= self::SYMBOLS[random_int(0, $maxNumber - 1)];
        }

        return $token;
    }
}