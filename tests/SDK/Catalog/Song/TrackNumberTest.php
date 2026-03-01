<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\TrackNumber,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class TrackNumberTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersExceptZeroAreAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $trackNumber = TrackNumber::of($number);

                $this->assertSame($number, $trackNumber->toInt());
            });
    }

    public function testNumbersBelowOneAreRejected(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                TrackNumber::of($negative);
            });
    }
}
