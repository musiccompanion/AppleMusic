# Apple Music SDK (non official)

[![Build Status](https://github.com/MusicCompanion/AppleMusic/workflows/CI/badge.svg)](https://github.com/MusicCompanion/AppleMusic/actions?query=workflow%3ACI)
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
use Innmind\TimeContinuum\Period\Earth\Hour;

$os = Factory::build();

$sdk = new SDK(
    $os->clock(),
    $os->remote()->http(),
    new Key( // @see https://help.apple.com/developer-account/#/devce5522674 to understand howto generate the key
        'KEY_ID',
        'TEAM_ID',
        $os->filesystem()->mount('config_dir')->get('AuthKey_TEAM_ID.p8')->content()
    ),
    new Hour(1) // expire the generated token after an hour
);

$sdk->storefronts()->all(); // set<SDK\Storefront>
$catalog = $sdk->catalog(new SDK\Storefront\Id('fr'));
$result = $catalog->search('Pendulum Live at Brixton');
$albums = [];

foreach ($result->albums() as $id) {
    $albums[] = $catalog->album($id);
}

// @see https://developer.apple.com/documentation/applemusicapi/getting_keys_and_creating_tokens
// to retrieve the user token
$sdk->library($userToken)->artists(); // set<SDK\Library\Artist>
```
