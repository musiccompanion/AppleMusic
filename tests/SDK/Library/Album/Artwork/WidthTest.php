<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Library\Album\Artwork\Width,
    Exception\DomainException,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\Width as WidthSet;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class WidthTest extends TestCase
{
    use BlackBox;

    public function testItCanBeOfAnyNaturalNumber(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $width = Width::of($number);

                $this->assertSame($number, $width->toInt());
                $this->assertSame((string) $number, $width->toString());
            });
    }

    public function testOf(): BlackBox\Proof
    {
        return $this
            ->forAll(WidthSet::any())
            ->prove(function($width) {
                $this->assertInstanceOf(Width::class, Width::of($width->toInt()));
                $this->assertSame($width->toInt(), Width::of($width->toInt())->toInt());
            });
    }

    public function testNegativeNumbersAreNotAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                Width::of($negative);
            });
    }
}
