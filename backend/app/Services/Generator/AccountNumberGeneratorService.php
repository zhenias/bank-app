<?php

namespace App\Services\Generator;

trait AccountNumberGeneratorService
{
    private const string BANK_SORT_CODE = '12345678';

    /**
     * Generuje poprawny 26-cyfrowy numer konta (NRB).
     */
    public function generate(): string
    {
        $clientNumber = $this->generateClientNumber();

        $accountBase = self::BANK_SORT_CODE . $clientNumber;

        $tmpChecksumStr = $accountBase . '252100';

        $checksum = $this->calculateChecksum($tmpChecksumStr);

        return $checksum . $accountBase;
    }

    /**
     * Generuje unikalny 16-cyfrowy numer klienta.
     */
    private function generateClientNumber(): string
    {
        $random = '';
        for ($i = 0; $i < 16; ++$i) {
            $random .= random_int(0, 9);
        }

        return $random;
    }

    /**
     * Oblicza sumę kontrolną modulo 97 (standard IBAN).
     */
    private function calculateChecksum(string $ibanBase): string
    {
        $remainder = 0;
        $digits    = str_split($ibanBase);

        foreach ($digits as $digit) {
            $remainder = (int) (($remainder . $digit) % 97);
        }

        $checksum = 98 - $remainder;

        return str_pad((string) $checksum, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Konwertuj NRB na format IBAN.
     */
    public function toIBAN(string $nrb): string
    {
        return 'PL' . $nrb;
    }

    /**
     * Formatuj numer do wyświetlenia (co 4 cyfry spacja).
     */
    public function format(string $nrb): string
    {
        // NRB: 98123456780000000012345678
        // Format: 98 1234 5678 0000 0000 1234 5678
        return chunk_split($nrb, 4, ' ');
    }
}
