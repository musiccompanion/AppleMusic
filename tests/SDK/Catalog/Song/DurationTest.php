<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\Duration,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class DurationTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersExceptZeroAreAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $duration = Duration::of($number);

                $this->assertSame($number, $duration->toInt());
            });
    }

    public function testNumbersBelowOneAreRejected(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                Duration::of($negative);
            });
    }
}
