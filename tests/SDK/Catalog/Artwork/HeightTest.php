<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Artwork\Height,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class HeightTest extends TestCase
{
    use BlackBox;

    public function testItCanBeOfAnyNaturalNumber()
    {
        $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->then(function(int $number) {
                $height = new Height($number);

                $this->assertSame($number, $height->toInt());
                $this->assertSame((string) $number, (string) $height);
            });
    }

    public function testNegativeNumbersAreNotAccepted()
    {
        $this
            ->forAll(Set\Integers::below(1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                new Height($negative);
            });
    }
}
