<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\{
    SDK\Library\Song\TrackNumber,
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

    public function testAnyStringIsAccepted()
    {
        $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->then(function(int $number) {
                $trackNumber = TrackNumber::of($number);

                $this->assertSame($number, $trackNumber->toInt());
                $this->assertSame((string) $number, $trackNumber->toString());
            });
    }

    public function testThrowWhenNegativeNumber()
    {
        $this
            ->forAll(Set\Integers::below(1))
            ->then(function(int $number) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $number);

                TrackNumber::of($number);
            });
    }
}
