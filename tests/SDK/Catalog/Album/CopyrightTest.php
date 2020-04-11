<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\Copyright;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class CopyrightTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted()
    {
        $this
            ->forAll(new Set\Strings)
            ->then(function(string $string) {
                $copyright = new Copyright($string);

                $this->assertSame($string, $copyright->toString());
            });
    }
}
