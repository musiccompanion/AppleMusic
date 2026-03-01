<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\{
    SDK\Storefront\Id,
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

    public function testAnyCountryCodeIsAccepted(): BlackBox\Proof
    {
        $char = Set\Elements::of(...\range('a', 'z'));

        return $this
            ->forAll($char, $char)
            ->prove(function(string $char1, string $char2) {
                $id = Id::of($char1.$char2);

                $this->assertSame($char1.$char2, $id->toString());
            });
    }

    public function testAnyOtherStringIsNotAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set\Strings::any()->filter(static function($string): bool {
                    return !\preg_match('~^[a-z]{2}$~', $string);
                }),
            )
            ->prove(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                Id::of($string);
            });
    }
}
