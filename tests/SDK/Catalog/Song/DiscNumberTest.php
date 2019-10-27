<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\DiscNumber,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class DiscNumberTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersExceptZeroAreAccepted()
    {
        $this
            ->forAll(new Set\NaturalNumbersExceptZero)
            ->then(function(int $number) {
                $discNumber = new DiscNumber($number);

                $this->assertSame($number, $discNumber->toInt());
            });
    }

    public function testNumbersBelowOneAreRejected()
    {
        $this
            ->forAll(Set\Integers::of(null, 1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                new DiscNumber($negative);
            });
    }
}
