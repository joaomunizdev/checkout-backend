<?php

namespace Tests\Unit;

use App\Services\PaymentGatewayService;
use Tests\TestCase;

class PaymentGatewayServiceTest extends TestCase
{
    protected PaymentGatewayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentGatewayService();
    }

    public function test_payment_is_approved_for_card_starting_with_5(): void
    {
        $response = $this->service->processPayment('5555444433332222');

        $this->assertTrue($response['status']);
        $this->assertEquals('Payment approved.', $response['message']);
    }

    public function test_payment_is_declined_for_card_starting_with_4(): void
    {
        $response = $this->service->processPayment('4444555533332222');

        $this->assertFalse($response['status']);
        $this->assertEquals('Payment declined.', $response['message']);
    }

    public function test_payment_is_declined_for_other_card_numbers(): void
    {
        $response = $this->service->processPayment('1111222233334444');

        $this->assertFalse($response['status']);
        $this->assertEquals('Payment declined.', $response['message']);
    }
}
