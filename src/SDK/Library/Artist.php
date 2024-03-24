<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\{
    Library\Artist\Id,
    Library\Artist\Name,
    Catalog\Artist\Id as Catalog,
};
use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Artist
{
    private Id $id;
    private Name $name;
    /** @var Maybe<Catalog> */
    private Maybe $catalog;

    /**
     * @param Maybe<Catalog> $catalog
     */
    private function __construct(Id $id, Name $name, Maybe $catalog)
    {
        $this->id = $id;
        $this->name = $name;
        $this->catalog = $catalog;
    }

    /**
     * @psalm-pure
     *
     * @param Maybe<Catalog> $catalog
     */
    public static function of(Id $id, Name $name, Maybe $catalog): self
    {
        return new self($id, $name, $catalog);
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return Maybe<Catalog>
     */
    public function catalog(): Maybe
    {
        return $this->catalog;
    }
}
