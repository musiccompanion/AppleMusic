# Apple Music SDK (non official)

[![Build Status](https://github.com/MusicCompanion/AppleMusic/workflows/CI/badge.svg?branch=master)](https://github.com/MusicCompanion/AppleMusic/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/MusicCompanion/AppleMusic/branch/develop/graph/badge.svg)](https://codecov.io/gh/MusicCompanion/AppleMusic)
[![Type Coverage](https://shepherd.dev/github/MusicCompanion/AppleMusic/coverage.svg)](https://shepherd.dev/github/MusicCompanion/AppleMusic)

This is a sdk to consume part of the Apple Music API.

## Installation

```sh
composer require music-companion/apple-music
```

## Usage

```php
use MusicCompanion\AppleMusic\{
    SDK,
    Key,
};
use Innmind\OperatingSystem\Factory;
use Innmind\TimeContinuum\Earth\Period\Hour;
use Innmind\Url\Path;
use Innmind\Filesystem\Name;
use Innmind\Immutable\Set;

$os = Factory::build();

$sdk = SDK::of(
    $os->clock(),
    $os->remote()->http(),
    Key::of( // @see https://help.apple.com/developer-account/#/devce5522674 to understand howto generate the key
        'KEY_ID',
        'TEAM_ID',
        $os
            ->filesystem()
            ->mount(Path::of('config_dir/'))
            ->get(new Name('AuthKey_TEAM_ID.p8'))
            ->match(
                static fn($file) => $file->content(),
                static fn() => throw new \RuntimeException('Key file not found'),
            ),
    ),
    new Hour(1) // expire the generated token after an hour
);

$sdk->storefronts()->all(); // Set<SDK\Storefront>
$catalog = $sdk->catalog(new SDK\Storefront\Id('fr'));
$result = $catalog->search('Pendulum Live at Brixton');
$albums = $result->albums()->map($catalog->album(...));

// @see https://developer.apple.com/documentation/applemusicapi/getting_keys_and_creating_tokens
// to retrieve the user token
$sdk->library($userToken)->match(
    static fn($libray) => $libray->artists(), // Set<SDK\Library\Artist>
    static fn() => throw new \RuntimeException('Invalid user token'),
);
```
