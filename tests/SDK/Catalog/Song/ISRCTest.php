<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\ISRC,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ISRCTest extends TestCase
{
    use BlackBox;

    public function testOnlyISO3901StringsAreAccepted()
    {
        $c = Set\Elements::of(...\range('A', 'Z'));
        $x = Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9));
        $y = Set\Elements::of(...\range(0, 9));
        $n = Set\Elements::of(...\range(0, 9));

        $this
            ->forAll(
                $c,
                $c,
                $x,
                $x,
                $x,
                $y,
                $y,
                $n,
                $n,
                $n,
                $n,
                $n,
            )
            ->then(function(...$bits) {
                $string = \implode('', $bits);

                $isrc = new ISRC($string);

                $this->assertSame($string, $isrc->toString());
            });
    }

    public function testAnyRandomStringWillThrowAnException()
    {
        $this
            ->forAll(new Set\Strings)
            ->then(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new ISRC($string);
            });
    }
}
