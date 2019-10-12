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

    public function test_any_country_code_is_accepted()
    {
        $char = Set\Chars::of()->filter(static function($char): bool {
            return \in_array($char, \range('a', 'z'), true);
        });

        $this
            ->forAll($char, $char)
            ->then(function(string $char1, string $char2) {
                $id = new Id($char1.$char2);

                $this->assertSame($char1.$char2, (string) $id);
            });
    }

    public function test_any_other_string_is_not_accepted()
    {
        $this
            ->forAll(
                Set\Strings::of()->filter(static function($string): bool {
                    return !\preg_match('~^[a-z]{2}$~', $string);
                })
            )
            ->then(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Id($string);
            });
    }
}
