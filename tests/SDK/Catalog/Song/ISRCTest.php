<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\ISRC,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class ISRCTest extends TestCase
{
    use BlackBox;

    public function testOnlyISO3901StringsAreAccepted(): BlackBox\Proof
    {
        $c = Set\Elements::of(...\range('A', 'Z'));
        $x = Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9));
        $y = Set\Elements::of(...\range(0, 9));
        $n = Set\Elements::of(...\range(0, 9));

        return $this
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
            ->prove(function(...$bits) {
                $string = \implode('', $bits);

                $isrc = ISRC::of($string);

                $this->assertSame($string, $isrc->toString());
            });
    }

    public function testAnyRandomStringWillThrowAnException(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Strings::any())
            ->prove(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                ISRC::of($string);
            });
    }
}
