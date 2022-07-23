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

    public function testAnyCountryCodeIsAccepted()
    {
        $char = Set\Elements::of(...\range('a', 'z'));

        $this
            ->forAll($char, $char)
            ->then(function(string $char1, string $char2) {
                $language = new Language($char1.$char2);

                $this->assertSame($char1.$char2, $language->toString());
            });
    }

    public function testRegionalLanguageIsAccepted()
    {
        $char = Set\Elements::of(...\range('a', 'z'));
        $region = Set\Elements::of(...\range('A', 'Z'));

        $this
            ->forAll($char, $char, $region, $region)
            ->then(function(string $char1, string $char2, $region1, $region2) {
                $language = new Language($char1.$char2.'-'.$region1.$region2);

                $this->assertSame($char1.$char2.'-'.$region1.$region2, $language->toString());
            });
    }

    public function testChineseCodesAreAccepted()
    {
        $this
            ->forAll(Set\Elements::of(
                'zh-Hans-CN',
                'zh-Hant-HK',
                'zh-Hant-TW',
            ))
            ->then(function(string $code) {
                $language = new Language($code);

                $this->assertSame($code, $language->toString());
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

                new Language($string);
            });
    }
}
