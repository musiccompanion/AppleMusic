<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\EditorialNotes;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class EditorialNotesTest extends TestCase
{
    use BlackBox;

    public function testAnyStringIsAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Strings::any(), Set\Strings::any())
            ->prove(function(string $standard, string $short) {
                $editorialNotes = EditorialNotes::of($standard, $short);

                $this->assertSame($standard, $editorialNotes->standard());
                $this->assertSame($short, $editorialNotes->short());
            });
    }
}
