<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\{
    Key,
    Exception\DomainException,
};
use Innmind\Stream\Readable;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class KeyTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Set\Elements::of(...range('A', 'Z'), ...range(0, 9)),
                Set\Elements::of(...range('A', 'Z'), ...range(0, 9))
            )
            ->take(100)
            ->then(function($id, $teamId) {
                $id = \implode(\array_pad([], 10, $id));
                $teamId = \implode(\array_pad([], 10, $teamId));

                $key = new Key(
                    $id,
                    $teamId,
                    $content = $this->createMock(Readable::class)
                );

                $this->assertSame($id, $key->id());
                $this->assertSame($teamId, $key->teamId());
                $this->assertSame($content, $key->content());
            });
    }

    public function testThrowWhenInvalidId()
    {
        $this
            ->forAll(
                new Set\Strings,
                Set\Elements::of(...range('A', 'Z'), ...range(0, 9))
            )
            ->take(100)
            ->then(function($id, $teamId) {
                $teamId = \implode(\array_pad([], 10, $teamId));

                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("Invalid key id '$id'");

                new Key(
                    $id,
                    $teamId,
                    $this->createMock(Readable::class)
                );
            });
    }

    public function testThrowWhenInvalidTeamId()
    {
        $this
            ->forAll(
                Set\Elements::of(...range('A', 'Z'), ...range(0, 9)),
                new Set\Strings
            )
            ->take(100)
            ->then(function($id, $teamId) {
                $id = \implode(\array_pad([], 10, $id));

                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("Invalid team id '$teamId'");

                new Key(
                    $id,
                    $teamId,
                    $this->createMock(Readable::class)
                );
            });
    }
}
