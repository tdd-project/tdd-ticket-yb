<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

use App\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        // Act
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assert
        $this->assertResponseStatus(201);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $orders = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($orders);
        $this->assertEquals(3, $orders->tickets->count());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assert
        $this->assertResponseStatus(422);
    }
}
