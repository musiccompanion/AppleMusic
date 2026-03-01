<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Album\Id,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class IdTest extends TestCase
{
    use BlackBox;

    public function testRealNumbersAreAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbers::any())
            ->prove(function(int $number) {
                $id = Id::of($number);

                $this->assertSame($number, $id->toInt());
                $this->assertSame((string) $number, $id->toString());
            });
    }

    public function testNegativeNumbersAreRejected(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(0))
            ->prove(function(int $negative) {
                try {
                    Id::of($negative);
                    $this->fail('it should throw');
                } catch (DomainException $e) {
                    $this->assertSame((string) $negative, $e->getMessage());
                }
            });
    }
}
