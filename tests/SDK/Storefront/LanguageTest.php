<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\{
    SDK\Storefront\Language,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class LanguageTest extends TestCase
{
    use BlackBox;

    public function testAnyCountryCodeIsAccepted(): BlackBox\Proof
    {
        $char = Set\Elements::of(...\range('a', 'z'));

        return $this
            ->forAll($char, $char)
            ->prove(function(string $char1, string $char2) {
                $language = Language::of($char1.$char2);

                $this->assertSame($char1.$char2, $language->toString());
            });
    }

    public function testRegionalLanguageIsAccepted(): BlackBox\Proof
    {
        $char = Set\Elements::of(...\range('a', 'z'));
        $region = Set\Elements::of(...\range('A', 'Z'));

        return $this
            ->forAll($char, $char, $region, $region)
            ->prove(function(string $char1, string $char2, $region1, $region2) {
                $language = Language::of($char1.$char2.'-'.$region1.$region2);

                $this->assertSame($char1.$char2.'-'.$region1.$region2, $language->toString());
            });
    }

    public function testChineseCodesAreAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Elements::of(
                'zh-Hans-CN',
                'zh-Hant-HK',
                'zh-Hant-TW',
            ))
            ->prove(function(string $code) {
                $language = Language::of($code);

                $this->assertSame($code, $language->toString());
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

                Language::of($string);
            });
    }
}
