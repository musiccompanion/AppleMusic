<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\TrackNumber,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class TrackNumberTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersExceptZeroAreAccepted()
    {
        $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->then(function(int $number) {
                $trackNumber = TrackNumber::of($number);

                $this->assertSame($number, $trackNumber->toInt());
            });
    }

    public function testNumbersBelowOneAreRejected()
    {
        $this
            ->forAll(Set\Integers::below(1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                TrackNumber::of($negative);
            });
    }
}
