<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\{
    SDK\Storefront\Id,
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

    public function testAnyCountryCodeIsAccepted()
    {
        $char = Set\Elements::of(...\range('a', 'z'));

        $this
            ->forAll($char, $char)
            ->then(function(string $char1, string $char2) {
                $id = new Id($char1.$char2);

                $this->assertSame($char1.$char2, $id->toString());
            });
    }

    public function testAnyOtherStringIsNotAccepted()
    {
        $this
            ->forAll(
                Set\Strings::any()->filter(static function($string): bool {
                    return !\preg_match('~^[a-z]{2}$~', $string);
                }),
            )
            ->then(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Id($string);
            });
    }
}
