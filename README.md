# Apple Music SDK (non official)

| `develop` |
|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MusicCompanion/AppleMusic/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/MusicCompanion/AppleMusic/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/MusicCompanion/AppleMusic/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/MusicCompanion/AppleMusic/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/MusicCompanion/AppleMusic/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/MusicCompanion/AppleMusic/build-status/develop) |

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
