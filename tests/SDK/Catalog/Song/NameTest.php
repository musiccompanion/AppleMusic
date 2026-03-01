<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\Name;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class NameTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Strings::any())
            ->prove(function(string $string) {
                $name = Name::of($string);

                $this->assertSame($string, $name->toString());
            });
    }
}
