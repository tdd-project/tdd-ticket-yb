<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Billing\FakePaymentGateway;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        // Arrange
        $paymentGateway = new FakePaymentGateway;

        // Act
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        // Assert
        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }
}
