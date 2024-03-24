<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\Name;
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
                $name = Name::of($string);

                $this->assertSame($string, $name->toString());
            });
    }
}
