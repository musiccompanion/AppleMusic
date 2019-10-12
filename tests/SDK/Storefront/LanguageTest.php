<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\{
    SDK\Storefront\Language,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class LanguageTest extends TestCase
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
                $language = new Language($char1.$char2);

                $this->assertSame($char1.$char2, (string) $language);
            });
    }

    public function test_regional_language_is_accepted()
    {
        $char = Set\Chars::of()->filter(static function($char): bool {
            return \in_array($char, \range('a', 'z'), true);
        });
        $region = Set\Chars::of()->filter(static function($char): bool {
            return \in_array($char, \range('A', 'Z'), true);
        });

        $this
            ->forAll($char, $char, $region, $region)
            ->then(function(string $char1, string $char2, $region1, $region2) {
                $language = new Language($char1.$char2.'-'.$region1.$region2);

                $this->assertSame($char1.$char2.'-'.$region1.$region2, (string) $language);
            });
    }

    public function test_chinese_codes_are_accepted()
    {
        $this
            ->forAll(Set\Elements::of(
                'zh-Hans-CN',
                'zh-Hant-HK',
                'zh-Hant-TW'
            ))
            ->then(function(string $code) {
                $language = new Language($code);

                $this->assertSame($code, (string) $language);
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

                new Language($string);
            });
    }
}
