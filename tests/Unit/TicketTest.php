<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_released()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);

        $order = $concert->orderTickets('john@example.com', 1);
        $ticket = $order->tickets()->first();

        // Act
        $ticket->release();

        // Assert
        $this->assertNull($ticket->fresh()->order_id);
    }
}