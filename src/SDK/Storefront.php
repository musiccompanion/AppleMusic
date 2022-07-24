<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Storefront\{
    Id,
    Name,
    Language,
};
use Innmind\Immutable\Set;

/**
 * @psalm-immutable
 */
final class Storefront
{
    private Id $id;
    private Name $name;
    private Language $defaultLanguage;
    /** @var Set<Language> */
    private Set $supportedLanguages;

    /**
     * @param Set<Language> $supportedLanguages
     */
    public function __construct(
        Id $id,
        Name $name,
        Language $defaultLanguage,
        Set $supportedLanguages,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->defaultLanguage = $defaultLanguage;
        $this->supportedLanguages = $supportedLanguages;
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
     * @return Set<Language>
     */
    public function supportedLanguages(): Set
    {
        return $this->supportedLanguages;
    }
}
