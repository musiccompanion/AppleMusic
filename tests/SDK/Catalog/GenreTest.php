<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Genre;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class GenreTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted()
    {
        $this
            ->forAll(new Set\Strings)
            ->then(function(string $string) {
                $genre = new Genre($string);

                $this->assertSame($string, $genre->toString());
            });
    }
}
