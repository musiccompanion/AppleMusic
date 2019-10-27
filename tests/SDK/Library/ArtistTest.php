<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Artist;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Artist\{
    Id,
    Name,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;

class ArtistTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Id::any(), Name::any())
            ->take(1000)
            ->then(function($id, $name) {
                $artist = new Artist($id, $name);

                $this->assertSame($id, $artist->id());
                $this->assertSame($name, $artist->name());
            });
    }
}
