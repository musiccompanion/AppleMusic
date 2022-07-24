<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\Exception\DomainException;
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Key
{
    private string $id;
    private string $teamId;
    private Content $content;

    private function __construct(string $id, string $teamId, Content $content)
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

    /**
     * @psalm-pure
     *
     * @throws DomainException When the id or teamId are invalid
     */
    public static function of(string $id, string $teamId, Content $content): self
    {
        return new self($id, $teamId, $content);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function teamId(): string
    {
        return $this->teamId;
    }

    public function content(): Content
    {
        return $this->content;
    }
}
