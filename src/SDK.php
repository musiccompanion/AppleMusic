<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic;

interface SDK
{
    public function jwt(): string;
    public function storefronts(): SDK\Storefronts;
    public function library(string $userToken): SDK\Library;
    public function catalog(SDK\Storefront\Id $storefront): SDK\Catalog;
}
