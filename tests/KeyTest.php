<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\{
    Key,
    Exception\DomainException,
};
use Innmind\Filesystem\File\Content;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class KeyTest extends TestCase
{
    use BlackBox;

    public function testInterface(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9)),
                Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9)),
            )
            ->prove(function($id, $teamId) {
                $id = \implode(\array_pad([], 10, $id));
                $teamId = \implode(\array_pad([], 10, $teamId));

                $key = Key::of(
                    $id,
                    $teamId,
                    $content = Content::none(),
                );

                $this->assertSame($id, $key->id());
                $this->assertSame($teamId, $key->teamId());
                $this->assertSame($content, $key->content());
            });
    }

    public function testThrowWhenInvalidId(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set\Strings::any(),
                Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9)),
            )
            ->prove(function($id, $teamId) {
                $teamId = \implode(\array_pad([], 10, $teamId));

                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("Invalid key id '$id'");

                Key::of(
                    $id,
                    $teamId,
                    Content::none(),
                );
            });
    }

    public function testThrowWhenInvalidTeamId(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9)),
                Set\Strings::any(),
            )
            ->prove(function($id, $teamId) {
                $id = \implode(\array_pad([], 10, $id));

                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("Invalid team id '$teamId'");

                Key::of(
                    $id,
                    $teamId,
                    Content::none(),
                );
            });
    }
}
