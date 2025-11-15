<?php

namespace App\Services;

use Exception;

class PaymentGatewayService
{
    public function processPayment(string $cardNumber): array
    {

        $firstDigit = substr($cardNumber, 0, 1);

        $errorResponse = [
            'status' => false,
            'message' => 'Payment declined.'
        ];

        $approvedResponse = [
            'status' => true,
            'message' => 'Payment approved.'
        ];

        if ($firstDigit === '5') {
            return $approvedResponse;
        }

        if ($firstDigit === '4') {
            return $errorResponse;
        }

        if ($firstDigit === '3') {
            $randomPercent = rand(1, 100);

            if ($randomPercent <= 70) {
                return $approvedResponse;
            } else {
                return $errorResponse;
            }
        }

        return $errorResponse;
    }
}
