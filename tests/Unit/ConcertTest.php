<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

use App\Concert;

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
}
