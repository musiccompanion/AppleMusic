<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\SDK\Storefront\Name;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class NameTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function(string $string) {
                $name = new Name($string);

                $this->assertSame($string, $name->toString());
            });
    }
}
