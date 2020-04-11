<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\SDK\Library\Song\Name;
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
            ->forAll(new Set\Strings)
            ->then(function(string $string) {
                $name = new Name($string);

                $this->assertSame($string, $name->toString());
            });
    }
}
