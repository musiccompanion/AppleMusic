<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\EditorialNotes;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class EditorialNotesTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted()
    {
        $this
            ->forAll(Set\Strings::any(), Set\Strings::any())
            ->then(function(string $standard, string $short) {
                $editorialNotes = new EditorialNotes($standard, $short);

                $this->assertSame($standard, $editorialNotes->standard());
                $this->assertSame($short, $editorialNotes->short());
            });
    }
}
