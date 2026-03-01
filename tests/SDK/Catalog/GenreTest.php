<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Genre;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class GenreTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Strings::any())
            ->prove(function(string $string) {
                $genre = Genre::of($string);

                $this->assertSame($string, $genre->toString());
            });
    }
}
