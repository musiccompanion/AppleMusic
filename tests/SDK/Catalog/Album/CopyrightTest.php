<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\Copyright;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class CopyrightTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Strings::any())
            ->prove(function(string $string) {
                $copyright = Copyright::of($string);

                $this->assertSame($string, $copyright->toString());
            });
    }
}
