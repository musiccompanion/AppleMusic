<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Library\Album\Artwork\Height,
    Exception\DomainException,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\Height as HeightSet;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class HeightTest extends TestCase
{
    use BlackBox;

    public function testItCanBeOfAnyNaturalNumber(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $height = Height::of($number);

                $this->assertSame($number, $height->toInt());
                $this->assertSame((string) $number, $height->toString());
            });
    }

    public function testOf(): BlackBox\Proof
    {
        return $this
            ->forAll(HeightSet::any())
            ->prove(function($height) {
                $this->assertInstanceOf(Height::class, Height::of($height->toInt()));
                $this->assertSame($height->toInt(), Height::of($height->toInt())->toInt());
            });
    }

    public function testNegativeNumbersAreNotAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                Height::of($negative);
            });
    }
}
