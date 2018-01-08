<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date()
    {
        // Arrange
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        // Act
        $date = $concert->formatted_date;

        // Assert
        $this->assertEquals('December 1, 2016', $date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
        // Arrange
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        // Act
        $time = $concert->formatted_start_time;

        // Assert
        $this->assertEquals('5:00pm', $time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars()
    {
        // Arrange
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        // Act
        $price = $concert->ticket_price_in_dollars;

        // Assert
        $this->assertEquals(67.50, $price);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published()
    {
        // Arrange
        $publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        // Act
        $publishedConcerts = Concert::published()->get();

        // Assert
        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function can_order_concert_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(3);

        // Act
        $order = $concert->orderTickets('john@example.com', 3);

        // Assert
        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function can_add_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $concert->addTickets(50);

        // Assert
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        // Act
        $order = $concert->orderTickets('john@example.com', 30);

        // Assert
        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        // Act
        try {
            $order = $concert->orderTickets('john@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            // Assert
            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /** @test */
    function cannot_order_tickets_that_have_already_been_purchased()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);
        $concert->orderTickets('jane@example.com', 8);

        // Act
        try {
            $order = $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            // Assert
            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }
}
