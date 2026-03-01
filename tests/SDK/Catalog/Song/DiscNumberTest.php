<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\DiscNumber,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class DiscNumberTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersExceptZeroAreAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $discNumber = DiscNumber::of($number);

                $this->assertSame($number, $discNumber->toInt());
            });
    }

    public function testNumbersBelowOneAreRejected(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                DiscNumber::of($negative);
            });
    }
}
