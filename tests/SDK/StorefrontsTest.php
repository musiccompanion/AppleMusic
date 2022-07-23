<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Storefronts,
    Storefront,
    HttpTransport,
};
use Innmind\HttpTransport\{
    Transport,
    Success,
};
use Innmind\Http\{
    Header\Authorization,
    Header\AuthorizationValue,
    Message\Request,
    Message\Response,
    Message\StatusCode,
};
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\{
    Set,
    Either,
};
use PHPUnit\Framework\TestCase;

class StorefrontsTest extends TestCase
{
    public function testAll()
    {
        $storefronts = new Storefronts(
            new HttpTransport(
                $send = $this->createMock(Transport::class),
            ),
            $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
        );
        $response = $this->createMock(Response::class);
        $response
            ->method('statusCode')
            ->willReturn(StatusCode::ok);
        $response
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Content::class));
        $send
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function($request) use ($authorization): bool {
                return $request->url()->toString() === 'https://api.music.apple.com/v1/storefronts' &&
                    $request->method()->toString() === 'GET' &&
                    $authorization === $request->headers()->get('authorization')->match(
                        static fn($header) => $header,
                        static fn() => null,
                    );
            }))
            ->willReturn(Either::right(new Success(
                $this->createMock(Request::class),
                $response,
            )));
        $body
            ->expects($this->once())
            ->method('toString')
            ->willReturn(<<<JSON
{
  "data": [
    {
      "id": "ai",
      "type": "storefronts",
      "href": "/v1/storefronts/ai",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Anguilla",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ag",
      "type": "storefronts",
      "href": "/v1/storefronts/ag",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Antigua and Barbuda",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ar",
      "type": "storefronts",
      "href": "/v1/storefronts/ar",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Argentina",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "am",
      "type": "storefronts",
      "href": "/v1/storefronts/am",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Armenia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "au",
      "type": "storefronts",
      "href": "/v1/storefronts/au",
      "attributes": {
        "supportedLanguageTags": [
          "en-AU"
        ],
        "defaultLanguageTag": "en-AU",
        "name": "Australia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "at",
      "type": "storefronts",
      "href": "/v1/storefronts/at",
      "attributes": {
        "supportedLanguageTags": [
          "de-DE",
          "en-GB"
        ],
        "defaultLanguageTag": "de-DE",
        "name": "Austria",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "az",
      "type": "storefronts",
      "href": "/v1/storefronts/az",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Azerbaijan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "bh",
      "type": "storefronts",
      "href": "/v1/storefronts/bh",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Bahrain",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "bb",
      "type": "storefronts",
      "href": "/v1/storefronts/bb",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Barbados",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "by",
      "type": "storefronts",
      "href": "/v1/storefronts/by",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Belarus",
        "explicitContentPolicy": "prohibited"
      }
    },
    {
      "id": "be",
      "type": "storefronts",
      "href": "/v1/storefronts/be",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR",
          "nl"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Belgium",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "bz",
      "type": "storefronts",
      "href": "/v1/storefronts/bz",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "es-MX"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Belize",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "bm",
      "type": "storefronts",
      "href": "/v1/storefronts/bm",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Bermuda",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "bo",
      "type": "storefronts",
      "href": "/v1/storefronts/bo",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Bolivia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "bw",
      "type": "storefronts",
      "href": "/v1/storefronts/bw",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Botswana",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "br",
      "type": "storefronts",
      "href": "/v1/storefronts/br",
      "attributes": {
        "supportedLanguageTags": [
          "pt-BR",
          "en-GB"
        ],
        "defaultLanguageTag": "pt-BR",
        "name": "Brazil",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "vg",
      "type": "storefronts",
      "href": "/v1/storefronts/vg",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "British Virgin Islands",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "bg",
      "type": "storefronts",
      "href": "/v1/storefronts/bg",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Bulgaria",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "kh",
      "type": "storefronts",
      "href": "/v1/storefronts/kh",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Cambodia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ca",
      "type": "storefronts",
      "href": "/v1/storefronts/ca",
      "attributes": {
        "supportedLanguageTags": [
          "en-CA",
          "fr-CA"
        ],
        "defaultLanguageTag": "en-CA",
        "name": "Canada",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "cv",
      "type": "storefronts",
      "href": "/v1/storefronts/cv",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Cape Verde",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ky",
      "type": "storefronts",
      "href": "/v1/storefronts/ky",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Cayman Islands",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "cl",
      "type": "storefronts",
      "href": "/v1/storefronts/cl",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Chile",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "cn",
      "type": "storefronts",
      "href": "/v1/storefronts/cn",
      "attributes": {
        "supportedLanguageTags": [
          "zh-Hans-CN",
          "en-GB"
        ],
        "defaultLanguageTag": "zh-Hans-CN",
        "name": "China mainland",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "co",
      "type": "storefronts",
      "href": "/v1/storefronts/co",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Colombia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "cr",
      "type": "storefronts",
      "href": "/v1/storefronts/cr",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Costa Rica",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "cy",
      "type": "storefronts",
      "href": "/v1/storefronts/cy",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "el",
          "tr"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Cyprus",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "cz",
      "type": "storefronts",
      "href": "/v1/storefronts/cz",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "cs"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Czech Republic",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "dk",
      "type": "storefronts",
      "href": "/v1/storefronts/dk",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "da"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Denmark",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "dm",
      "type": "storefronts",
      "href": "/v1/storefronts/dm",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Dominica",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "do",
      "type": "storefronts",
      "href": "/v1/storefronts/do",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Dominican Republic",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ec",
      "type": "storefronts",
      "href": "/v1/storefronts/ec",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Ecuador",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "eg",
      "type": "storefronts",
      "href": "/v1/storefronts/eg",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Egypt",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "sv",
      "type": "storefronts",
      "href": "/v1/storefronts/sv",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "El Salvador",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ee",
      "type": "storefronts",
      "href": "/v1/storefronts/ee",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Estonia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "fj",
      "type": "storefronts",
      "href": "/v1/storefronts/fj",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Fiji",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "fi",
      "type": "storefronts",
      "href": "/v1/storefronts/fi",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fi"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Finland",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "fr",
      "type": "storefronts",
      "href": "/v1/storefronts/fr",
      "attributes": {
        "supportedLanguageTags": [
          "fr-FR",
          "en-GB"
        ],
        "defaultLanguageTag": "fr-FR",
        "name": "France",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "gm",
      "type": "storefronts",
      "href": "/v1/storefronts/gm",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Gambia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "de",
      "type": "storefronts",
      "href": "/v1/storefronts/de",
      "attributes": {
        "supportedLanguageTags": [
          "de-DE",
          "en-GB"
        ],
        "defaultLanguageTag": "de-DE",
        "name": "Germany",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "gh",
      "type": "storefronts",
      "href": "/v1/storefronts/gh",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Ghana",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "gr",
      "type": "storefronts",
      "href": "/v1/storefronts/gr",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "el"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Greece",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "gd",
      "type": "storefronts",
      "href": "/v1/storefronts/gd",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Grenada",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "gt",
      "type": "storefronts",
      "href": "/v1/storefronts/gt",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Guatemala",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "gw",
      "type": "storefronts",
      "href": "/v1/storefronts/gw",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Guinea-Bissau",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "hn",
      "type": "storefronts",
      "href": "/v1/storefronts/hn",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Honduras",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "hk",
      "type": "storefronts",
      "href": "/v1/storefronts/hk",
      "attributes": {
        "supportedLanguageTags": [
          "zh-Hant-HK",
          "zh-Hant-TW",
          "en-GB"
        ],
        "defaultLanguageTag": "zh-Hant-HK",
        "name": "Hong Kong",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "hu",
      "type": "storefronts",
      "href": "/v1/storefronts/hu",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "hu"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Hungary",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "in",
      "type": "storefronts",
      "href": "/v1/storefronts/in",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "hi"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "India",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "id",
      "type": "storefronts",
      "href": "/v1/storefronts/id",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "id"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Indonesia",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "ie",
      "type": "storefronts",
      "href": "/v1/storefronts/ie",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Ireland",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "il",
      "type": "storefronts",
      "href": "/v1/storefronts/il",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "he"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Israel",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "it",
      "type": "storefronts",
      "href": "/v1/storefronts/it",
      "attributes": {
        "supportedLanguageTags": [
          "it",
          "en-GB"
        ],
        "defaultLanguageTag": "it",
        "name": "Italy",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "jp",
      "type": "storefronts",
      "href": "/v1/storefronts/jp",
      "attributes": {
        "supportedLanguageTags": [
          "ja",
          "en-US"
        ],
        "defaultLanguageTag": "ja",
        "name": "Japan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "jo",
      "type": "storefronts",
      "href": "/v1/storefronts/jo",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Jordan",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "kz",
      "type": "storefronts",
      "href": "/v1/storefronts/kz",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Kazakhstan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ke",
      "type": "storefronts",
      "href": "/v1/storefronts/ke",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Kenya",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "kr",
      "type": "storefronts",
      "href": "/v1/storefronts/kr",
      "attributes": {
        "supportedLanguageTags": [
          "ko",
          "en-GB"
        ],
        "defaultLanguageTag": "ko",
        "name": "Korea, Republic of",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "kg",
      "type": "storefronts",
      "href": "/v1/storefronts/kg",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Kyrgyzstan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "la",
      "type": "storefronts",
      "href": "/v1/storefronts/la",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Lao People's Democratic Republic",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "lv",
      "type": "storefronts",
      "href": "/v1/storefronts/lv",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Latvia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "lb",
      "type": "storefronts",
      "href": "/v1/storefronts/lb",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Lebanon",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "lt",
      "type": "storefronts",
      "href": "/v1/storefronts/lt",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Lithuania",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "lu",
      "type": "storefronts",
      "href": "/v1/storefronts/lu",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR",
          "de-DE"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Luxembourg",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "mo",
      "type": "storefronts",
      "href": "/v1/storefronts/mo",
      "attributes": {
        "supportedLanguageTags": [
          "zh-Hant-HK",
          "zh-Hant-TW",
          "en-GB"
        ],
        "defaultLanguageTag": "zh-Hant-HK",
        "name": "Macao",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "my",
      "type": "storefronts",
      "href": "/v1/storefronts/my",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ms"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Malaysia",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "mt",
      "type": "storefronts",
      "href": "/v1/storefronts/mt",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Malta",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "mu",
      "type": "storefronts",
      "href": "/v1/storefronts/mu",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Mauritius",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "mx",
      "type": "storefronts",
      "href": "/v1/storefronts/mx",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Mexico",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "fm",
      "type": "storefronts",
      "href": "/v1/storefronts/fm",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Micronesia, Federated States of",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "md",
      "type": "storefronts",
      "href": "/v1/storefronts/md",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Moldova",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "mn",
      "type": "storefronts",
      "href": "/v1/storefronts/mn",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Mongolia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "np",
      "type": "storefronts",
      "href": "/v1/storefronts/np",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Nepal",
        "explicitContentPolicy": "prohibited"
      }
    },
    {
      "id": "nl",
      "type": "storefronts",
      "href": "/v1/storefronts/nl",
      "attributes": {
        "supportedLanguageTags": [
          "nl",
          "en-GB"
        ],
        "defaultLanguageTag": "nl",
        "name": "Netherlands",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "nz",
      "type": "storefronts",
      "href": "/v1/storefronts/nz",
      "attributes": {
        "supportedLanguageTags": [
          "en-AU"
        ],
        "defaultLanguageTag": "en-AU",
        "name": "New Zealand",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ni",
      "type": "storefronts",
      "href": "/v1/storefronts/ni",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Nicaragua",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ne",
      "type": "storefronts",
      "href": "/v1/storefronts/ne",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Niger",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ng",
      "type": "storefronts",
      "href": "/v1/storefronts/ng",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Nigeria",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "no",
      "type": "storefronts",
      "href": "/v1/storefronts/no",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "nb"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Norway",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "om",
      "type": "storefronts",
      "href": "/v1/storefronts/om",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Oman",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "pa",
      "type": "storefronts",
      "href": "/v1/storefronts/pa",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Panama",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "pg",
      "type": "storefronts",
      "href": "/v1/storefronts/pg",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Papua New Guinea",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "py",
      "type": "storefronts",
      "href": "/v1/storefronts/py",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Paraguay",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "pe",
      "type": "storefronts",
      "href": "/v1/storefronts/pe",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Peru",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ph",
      "type": "storefronts",
      "href": "/v1/storefronts/ph",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Philippines",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "pl",
      "type": "storefronts",
      "href": "/v1/storefronts/pl",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "pl"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Poland",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "pt",
      "type": "storefronts",
      "href": "/v1/storefronts/pt",
      "attributes": {
        "supportedLanguageTags": [
          "pt-PT",
          "en-GB"
        ],
        "defaultLanguageTag": "pt-PT",
        "name": "Portugal",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ro",
      "type": "storefronts",
      "href": "/v1/storefronts/ro",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ro"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Romania",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ru",
      "type": "storefronts",
      "href": "/v1/storefronts/ru",
      "attributes": {
        "supportedLanguageTags": [
          "ru",
          "en-GB",
          "uk"
        ],
        "defaultLanguageTag": "ru",
        "name": "Russia",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "sa",
      "type": "storefronts",
      "href": "/v1/storefronts/sa",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Saudi Arabia",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "sg",
      "type": "storefronts",
      "href": "/v1/storefronts/sg",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "zh-Hans-CN"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Singapore",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "sk",
      "type": "storefronts",
      "href": "/v1/storefronts/sk",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "sk"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Slovakia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "si",
      "type": "storefronts",
      "href": "/v1/storefronts/si",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Slovenia",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "za",
      "type": "storefronts",
      "href": "/v1/storefronts/za",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "South Africa",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "es",
      "type": "storefronts",
      "href": "/v1/storefronts/es",
      "attributes": {
        "supportedLanguageTags": [
          "es-ES",
          "ca",
          "en-GB"
        ],
        "defaultLanguageTag": "es-ES",
        "name": "Spain",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "lk",
      "type": "storefronts",
      "href": "/v1/storefronts/lk",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Sri Lanka",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "kn",
      "type": "storefronts",
      "href": "/v1/storefronts/kn",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "St. Kitts and Nevis",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "sz",
      "type": "storefronts",
      "href": "/v1/storefronts/sz",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Swaziland",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "se",
      "type": "storefronts",
      "href": "/v1/storefronts/se",
      "attributes": {
        "supportedLanguageTags": [
          "sv",
          "en-GB"
        ],
        "defaultLanguageTag": "sv",
        "name": "Sweden",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ch",
      "type": "storefronts",
      "href": "/v1/storefronts/ch",
      "attributes": {
        "supportedLanguageTags": [
          "de-CH",
          "de-DE",
          "en-GB",
          "fr-FR",
          "it"
        ],
        "defaultLanguageTag": "de-CH",
        "name": "Switzerland",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "tw",
      "type": "storefronts",
      "href": "/v1/storefronts/tw",
      "attributes": {
        "supportedLanguageTags": [
          "zh-Hant-TW",
          "en-GB"
        ],
        "defaultLanguageTag": "zh-Hant-TW",
        "name": "Taiwan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "tj",
      "type": "storefronts",
      "href": "/v1/storefronts/tj",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Tajikistan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "th",
      "type": "storefronts",
      "href": "/v1/storefronts/th",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "th"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Thailand",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "tt",
      "type": "storefronts",
      "href": "/v1/storefronts/tt",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "fr-FR"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Trinidad and Tobago",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "tr",
      "type": "storefronts",
      "href": "/v1/storefronts/tr",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "tr"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Turkey",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "tm",
      "type": "storefronts",
      "href": "/v1/storefronts/tm",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Turkmenistan",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ae",
      "type": "storefronts",
      "href": "/v1/storefronts/ae",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "ar"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "UAE",
        "explicitContentPolicy": "opt-in"
      }
    },
    {
      "id": "ug",
      "type": "storefronts",
      "href": "/v1/storefronts/ug",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Uganda",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "ua",
      "type": "storefronts",
      "href": "/v1/storefronts/ua",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "uk",
          "ru"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Ukraine",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "gb",
      "type": "storefronts",
      "href": "/v1/storefronts/gb",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "United Kingdom",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "us",
      "type": "storefronts",
      "href": "/v1/storefronts/us",
      "attributes": {
        "supportedLanguageTags": [
          "en-US",
          "es-MX"
        ],
        "defaultLanguageTag": "en-US",
        "name": "United States",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "uz",
      "type": "storefronts",
      "href": "/v1/storefronts/uz",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Uzbekistan",
        "explicitContentPolicy": "prohibited"
      }
    },
    {
      "id": "ve",
      "type": "storefronts",
      "href": "/v1/storefronts/ve",
      "attributes": {
        "supportedLanguageTags": [
          "es-MX",
          "en-GB"
        ],
        "defaultLanguageTag": "es-MX",
        "name": "Venezuela",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "vn",
      "type": "storefronts",
      "href": "/v1/storefronts/vn",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB",
          "vi"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Vietnam",
        "explicitContentPolicy": "allowed"
      }
    },
    {
      "id": "zw",
      "type": "storefronts",
      "href": "/v1/storefronts/zw",
      "attributes": {
        "supportedLanguageTags": [
          "en-GB"
        ],
        "defaultLanguageTag": "en-GB",
        "name": "Zimbabwe",
        "explicitContentPolicy": "allowed"
      }
    }
  ]
}
JSON
            );

        $all = $storefronts->all();

        $this->assertInstanceOf(Set::class, $all);
        $all = $all->toList();
        $this->assertCount(115, $all);
        $this->assertSame('ai', \current($all)->id()->toString());
        $this->assertSame('Anguilla', \current($all)->name()->toString());
        $this->assertSame('en-GB', \current($all)->defaultLanguage()->toString());
        \next($all);
        $this->assertSame('ag', \current($all)->id()->toString());
    }
}
