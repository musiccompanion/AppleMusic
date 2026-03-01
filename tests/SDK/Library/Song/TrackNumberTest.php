<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\{
    SDK\Library\Song\TrackNumber,
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

    public function testAnyStringIsAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $trackNumber = TrackNumber::of($number);

                $this->assertSame($number, $trackNumber->toInt());
                $this->assertSame((string) $number, $trackNumber->toString());
            });
    }

    public function testThrowWhenNegativeNumber(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $number) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $number);

                TrackNumber::of($number);
            });
    }
}
