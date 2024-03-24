<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Artist;
use Innmind\Immutable\Maybe;
use Fixtures\MusicCompanion\AppleMusic\SDK\{
    Library\Artist\Id,
    Library\Artist\Name,
    Catalog\Artist\Id as Catalog,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;

class ArtistTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Id::any(), Name::any(), Catalog::any())
            ->then(function($id, $name, $catalog) {
                $artist = Artist::of($id, $name, Maybe::of($catalog));

                $this->assertSame($id, $artist->id());
                $this->assertSame($name, $artist->name());
                $this->assertSame($catalog, $artist->catalog()->match(
                    static fn($catalog) => $catalog,
                    static fn() => null,
                ));
            });
    }
}
