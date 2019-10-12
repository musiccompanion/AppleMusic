<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Storefront\{
    Id,
    Name,
    Language,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Storefront
{
    private $id;
    private $name;
    private $defaultLanguage;
    private $supportedLanguages;

    public function __construct(
        Id $id,
        Name $name,
        Language $defaultLanguage,
        Language ...$supportedLanguages
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->defaultLanguage = $defaultLanguage;
        $this->supportedLanguages = Set::of(Language::class, ...$supportedLanguages);
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function defaultLanguage(): Language
    {
        return $this->defaultLanguage;
    }

    /**
     * @return SetInterface<Language>
     */
    public function supportedLanguages(): SetInterface
    {
        return $this->supportedLanguages;
    }
}
