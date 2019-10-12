<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Album\Id,
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
            ->forAll(new Set\NaturalNumbers)
            ->then(function(int $number) {
                $id = new Id($number);

                $this->assertSame($number, $id->toInt());
                $this->assertSame((string) $number, (string) $id);
            });
    }

    public function testNegativeNumbersAreRejected()
    {
        $this
            ->forAll(Set\Integers::of(null, 0))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                new Id($negative);
            });
    }
}
