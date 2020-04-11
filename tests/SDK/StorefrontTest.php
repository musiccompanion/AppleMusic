<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Storefront;
use Fixtures\MusicCompanion\AppleMusic\SDK\Storefront\{
    Id,
    Name,
    Language,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;

class StorefrontTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Language::any(),
                Language::any(),
                Language::any()
            )
            ->then(function($id, $name, $defaultLanguage, $supportedLanguage1, $supportedLanguage2) {
                $storefront = new Storefront(
                    $id,
                    $name,
                    $defaultLanguage,
                    $supportedLanguage1,
                    $supportedLanguage2
                );

                $this->assertSame($id, $storefront->id());
                $this->assertSame($name, $storefront->name());
                $this->assertSame($defaultLanguage, $storefront->defaultLanguage());
                $this->assertInstanceOf(Set::class, $storefront->supportedLanguages());
                $this->assertSame(Storefront\Language::class, (string) $storefront->supportedLanguages()->type());
                $this->assertSame(
                    [$supportedLanguage1, $supportedLanguage2],
                    unwrap($storefront->supportedLanguages())
                );
            });
    }
}
