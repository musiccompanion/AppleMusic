<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\Id as Model;
use Innmind\BlackBox\Set;

final class Id implements Set
{
    private $set;

    private function __construct()
    {
        $this->set = new Set\NaturalNumbers;
    }

    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new self;
    }

    public function take(int $size): Set
    {
        $self = clone $this;
        $self->set = $this->set->take($size);

        return $self;
    }

    public function filter(callable $predicate): Set
    {
        $self = clone $this;
        $self->set = $this->set->filter($predicate);

        return $self;
    }

    /**
     * @return \Generator<Model>
     */
    public function values(): \Generator
    {
        foreach ($this->set->values() as $int) {
            yield new Model($int);
        }
    }
}
