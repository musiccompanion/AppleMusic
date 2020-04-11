<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\Duration,
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

    public function testRealNumbersExceptZeroAreAccepted()
    {
        $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->then(function(int $number) {
                $duration = new Duration($number);

                $this->assertSame($number, $duration->toInt());
            });
    }

    public function testNumbersBelowOneAreRejected()
    {
        $this
            ->forAll(Set\Integers::below(1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                new Duration($negative);
            });
    }
}
