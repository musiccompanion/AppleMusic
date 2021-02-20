<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Artist;

use MusicCompanion\AppleMusic\{
    SDK\Library\Artist\Id,
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

    public function testStringsOfSpecifiedFormatAreAccepted()
    {
        $chars = Set\Decorate::immutable(
            static fn(array $chars) => \implode('', $chars),
            Set\Sequence::of(
                Set\Decorate::immutable(
                    static fn($ord) => \chr($ord),
                    new Set\Either(
                        Set\Integers::between(48, 57), // 0-9
                        Set\Integers::between(65, 90), // A-Z
                        Set\Integers::between(97, 122), // a-z
                    ),
                ),
                Set\Integers::between(1, 15),
            ),
        );

        $this
            ->forAll($chars)
            ->then(function(string $chars) {
                $string = 'r.'.$chars;
                $id = new Id($string);

                $this->assertSame($string, $id->toString());
            });
    }

    public function testAnyRandomStringIsRejected()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Id($string);
            });
    }
}
