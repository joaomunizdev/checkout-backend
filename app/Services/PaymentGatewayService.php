<?php

namespace App\Services;

use Exception;

class PaymentGatewayService
{
    public function processPayment(string $cardNumber): array
    {
        $firstDigit = substr($cardNumber, 0, 1);

        if ($firstDigit === '5') {
            return [
                'status' => 'approved',
                'message' => 'Payment approved.'
            ];
        }

        if ($firstDigit === '4') {
            throw new Exception('Payment declined.');
        }

        if ($firstDigit === '3') {
            $randomPercent = rand(1, 100);

            if ($randomPercent <= 70) {
                return [
                    'status' => 'approved',
                    'message' => 'Payment approved.'
                ];
            } else {
                throw new Exception('Payment declined.');
            }
        }

        throw new Exception('Payment error on gateway...');
    }
}
