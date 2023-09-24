<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Artist;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Artist\Id,
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
                try {
                    Id::of($negative);
                    $this->fail('it should throw');
                } catch (DomainException $e) {
                    $this->assertSame((string) $negative, $e->getMessage());
                }
            });
    }
}
