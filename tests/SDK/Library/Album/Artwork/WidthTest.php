<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Library\Album\Artwork\Width,
    Exception\DomainException,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\Width as WidthSet;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class WidthTest extends TestCase
{
    use BlackBox;

    public function testItCanBeOfAnyNaturalNumber()
    {
        $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->then(function(int $number) {
                $width = new Width($number);

                $this->assertSame($number, $width->toInt());
                $this->assertSame((string) $number, $width->toString());
            });
    }

    public function testOf()
    {
        $this
            ->forAll(WidthSet::any())
            ->then(function($width) {
                $this->assertInstanceOf(Width::class, Width::of($width->toInt()));
                $this->assertSame($width->toInt(), Width::of($width->toInt())->toInt());
            });

        $this->assertNull(Width::of(null));
    }

    public function testNegativeNumbersAreNotAccepted()
    {
        $this
            ->forAll(Set\Integers::below(1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                new Width($negative);
            });
    }
}
