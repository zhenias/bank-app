<?php

namespace App\Services\Generator;

use App\Enums\Card\CardNetwork;
use App\Enums\Card\CardType;

trait CardNumberGeneratorService
{
    /**
     * Generate a valid 16-digit card number for given network.
     */
    public function generateCardNumber(CardNetwork $network = CardNetwork::VISA): string
    {
        $prefixes     = $network->prefix();
        $prefix       = $prefixes[array_rand($prefixes)];
        $prefixLength = strlen($prefix);

        $randomLength = 15 - $prefixLength;

        $number = $prefix;
        for ($i = 0; $i < $randomLength; ++$i) {
            $number .= random_int(0, 9);
        }

        return $number . $this->calculateLuhnChecksum($number);
    }

    /**
     * Generate CVV for given network.
     */
    public function generateCvv(CardNetwork $network = CardNetwork::VISA): string
    {
        $length = $network->cvvLength();

        return str_pad((string) random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Generate expiry date (4 years from now).
     */
    public function generateExpiryDate(): array
    {
        $now        = now();
        $expiryDate = $now->copy()->addYears(4);

        return [
            'month' => (int) $expiryDate->format('m'),
            'year'  => (int) $expiryDate->format('Y'),
        ];
    }

    /**
     * Generate full card data.
     */
    public function generateCardData(
        CardNetwork $network = CardNetwork::VISA,
        CardType $type = CardType::DEBIT,
    ): array {
        $expiry = $this->generateExpiryDate();

        return [
            'card_number' => $this->generateCardNumber($network),
            'cvv'         => $this->generateCvv($network),
            'exp_month'   => $expiry['month'],
            'exp_year'    => $expiry['year'],
            'network'     => $network->value,
            'type'        => $type->value,
        ];
    }

    /**
     * Validate card number using Luhn algorithm.
     */
    public function isValidCardNumber(string $number): bool
    {
        $number = str_replace(' ', '', $number);

        if (16 !== strlen($number) || ! is_numeric($number)) {
            return false;
        }

        $checksum = (int) $number[15];
        $payload  = substr($number, 0, 15);

        return $this->calculateLuhnChecksum($payload) === (string) $checksum;
    }

    /**
     * Detect card network from number.
     */
    public function detectNetwork(string $number): ?CardNetwork
    {
        $number = str_replace(' ', '', $number);

        foreach (CardNetwork::cases() as $network) {
            foreach ($network->prefix() as $prefix) {
                if (str_starts_with($number, $prefix)) {
                    return $network;
                }
            }
        }

        return null;
    }

    /**
     * Mask card number for display.
     */
    public function maskCardNumber(string $number): string
    {
        $number = str_replace(' ', '', $number);

        return substr($number, 0, 4) . ' **** **** ' . substr($number, -4);
    }

    /**
     * Format card number (groups of 4).
     */
    public function formatCardNumber(string $number): string
    {
        $number = str_replace(' ', '', $number);

        return trim(chunk_split($number, 4, ' '));
    }

    /**
     * Calculate Luhn checksum digit.
     */
    private function calculateLuhnChecksum(string $payload): string
    {
        $digits = str_split($payload);
        $sum    = 0;
        $double = true;

        for ($i = count($digits) - 1; $i >= 0; --$i) {
            $digit = (int) $digits[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = ! $double;
        }

        $checksum = (10 - ($sum % 10)) % 10;

        return (string) $checksum;
    }
}
