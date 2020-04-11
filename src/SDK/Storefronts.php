<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\Immutable\Set;

interface Storefronts
{
    /**
     * @return Set<Storefront>
     */
    public function all(): Set;
}
