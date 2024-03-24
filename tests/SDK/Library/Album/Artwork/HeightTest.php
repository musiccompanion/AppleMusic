<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Library\Album\Artwork\Height,
    Exception\DomainException,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\Height as HeightSet;
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
                $height = Height::of($number);

                $this->assertSame($number, $height->toInt());
                $this->assertSame((string) $number, $height->toString());
            });
    }

    public function testOf()
    {
        $this
            ->forAll(HeightSet::any())
            ->then(function($height) {
                $this->assertInstanceOf(Height::class, Height::of($height->toInt()));
                $this->assertSame($height->toInt(), Height::of($height->toInt())->toInt());
            });
    }

    public function testNegativeNumbersAreNotAccepted()
    {
        $this
            ->forAll(Set\Integers::below(1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                Height::of($negative);
            });
    }
}
