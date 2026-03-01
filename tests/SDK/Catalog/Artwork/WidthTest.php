<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Artwork\Width,
    Exception\DomainException,
};
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

    public function testNegativeNumbersAreNotAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                try {
                    Width::of($negative);
                    $this->fail('it should throw');
                } catch (DomainException $e) {
                    $this->assertSame((string) $negative, $e->getMessage());
                }
            });
    }
}
