<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\Exception\DomainException;
use Innmind\Stream\Readable;
use Innmind\Immutable\Str;

final class Key
{
    private $id;
    private $teamId;
    private $content;

    public function __construct(string $id, string $teamId, Readable $content)
    {
        if (!Str::of($id)->matches('~^[A-Z0-9]{10}$~')) {
            throw new DomainException("Invalid key id '$id'");
        }

        if (!Str::of($teamId)->matches('~^[A-Z0-9]{10}$~')) {
            throw new DomainException("Invalid team id '$teamId'");
        }

        $this->id = $id;
        $this->teamId = $teamId;
        $this->content = $content;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function teamId(): string
    {
        return $this->teamId;
    }

    public function content(): Readable
    {
        return $this->content;
    }
}
