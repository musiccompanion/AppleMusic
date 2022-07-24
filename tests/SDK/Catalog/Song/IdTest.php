<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Song\Id,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class IdTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersAreAccepted()
    {
        $this
            ->forAll(Set\NaturalNumbers::any())
            ->then(function(int $number) {
                $id = Id::of($number);

                $this->assertSame($number, $id->toInt());
                $this->assertSame((string) $number, $id->toString());
            });
    }

    public function testNegativeNumbersAreRejected()
    {
        $this
            ->forAll(Set\Integers::below(0))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                Id::of($negative);
            });
    }
}
