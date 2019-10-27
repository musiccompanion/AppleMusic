<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\{
    SDK\Library\Song\Duration,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class DurationTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted()
    {
        $this
            ->forAll(new Set\NaturalNumbersExceptZero)
            ->then(function(int $number) {
                $duration = new Duration($number);

                $this->assertSame($number, $duration->toInt());
                $this->assertSame((string) $number, (string) $duration);
            });
    }

    public function testThrowWhenNegativeNumber()
    {
        $this
            ->forAll(Set\Integers::of(null, 1))
            ->then(function(int $number) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $number);

                new Duration($number);
            });
    }
}
