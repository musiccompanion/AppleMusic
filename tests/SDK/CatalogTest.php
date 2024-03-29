<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Catalog,
    Catalog\Artist,
    Catalog\Album,
    Catalog\Song,
    Catalog\Search,
    HttpTransport,
};
use Innmind\TimeContinuum\{
    Clock,
    Earth,
};
use Innmind\HttpTransport\{
    Transport,
    Success,
};
use Innmind\Http\{
    Header\Authorization,
    Header\AuthorizationValue,
    ProtocolVersion,
    Response,
    Response\StatusCode,
};
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\{
    Either,
    Sequence,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\{
    Storefront as StorefrontSet,
    Catalog\Artist as ArtistSet,
    Catalog\Album as AlbumSet,
    Catalog\Song as SongSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};

class CatalogTest extends TestCase
{
    use BlackBox;

    public function testArtist()
    {
        $this
            ->forAll(
                StorefrontSet\Id::any(),
                ArtistSet\Id::any(),
            )
            ->then(function($storefront, $id) {
                $catalog = new Catalog(
                    $this->createMock(Clock::class),
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $storefront,
                );
                $response1 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "data": [
                        {
                          "id": "178834",
                          "type": "artists",
                          "href": "/v1/catalog/fr/artists/178834",
                          "attributes": {
                            "genreNames": [
                              "Rock"
                            ],
                            "name": "Bruce Springsteen",
                            "url": "https://music.apple.com/fr/artist/bruce-springsteen/178834"
                          },
                          "relationships": {
                            "albums": {
                              "data": [
                                {
                                  "id": "1459884961",
                                  "type": "albums",
                                  "href": "/v1/catalog/fr/albums/1459884961"
                                }
                              ],
                              "href": "/v1/catalog/fr/artists/178834/albums",
                              "next": "/v1/catalog/fr/artists/178834/albums?offset=1"
                            }
                          }
                        }
                      ]
                    }
                    JSON),
                );
                $response2 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "data": [
                        {
                          "id": "203708420",
                          "type": "albums",
                          "href": "/v1/catalog/fr/albums/203708420",
                          "attributes": {
                            "artwork": {
                              "width": 6000,
                              "height": 6000,
                              "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                              "bgColor": "d9c8b6",
                              "textColor1": "100707",
                              "textColor2": "441016",
                              "textColor3": "382e2a",
                              "textColor4": "623436"
                            },
                            "artistName": "Bruce Springsteen",
                            "isSingle": false,
                            "url": "https://music.apple.com/fr/album/born-in-the-u-s-a/203708420",
                            "isComplete": true,
                            "genreNames": [
                              "Rock",
                              "Musique",
                              "Rock & roll",
                              "Arena rock",
                              "Pop",
                              "Pop/Rock",
                              "Hard rock"
                            ],
                            "trackCount": 12,
                            "isMasteredForItunes": true,
                            "releaseDate": "1984-06-04",
                            "name": "Born In the U.S.A.",
                            "recordLabel": "Columbia",
                            "copyright": "℗ 1984 Bruce Springsteen",
                            "playParams": {
                              "id": "203708420",
                              "kind": "album"
                            },
                            "editorialNotes": {
                              "standard": "Avec <i>Born In the U.S.A.</i>, Bruce Springsteen signe son plus grand succès, plaçant sept titres dans le top dix des classements américains. Point d’orgue de sa carrière, l’album le consacre auprès du grand public. Intégrant des éléments électroniques, il contraste musicalement avec son prédécesseur, le très acoustique <i>Nebraska</i>, sans rompre avec ses thèmes sociaux. C’est aussi un enregistrement fondateur du heartland rock, ce genre brut et sincère qui veut donner une voix aux cols bleus.",
                              "short": "Hit après hit, un album unique pour entrer définitivement dans la mémoire collective."
                            }
                          }
                        }
                      ]
                    }
                    JSON),
                );
                $fulfill
                    ->expects($matcher = $this->exactly(2))
                    ->method('__invoke')
                    ->willReturnCallback(function($request) use ($matcher, $storefront, $id, $authorization, $response1, $response2) {
                        $this->assertSame('GET', $request->method()->toString());
                        $this->assertSame($authorization, $request->headers()->get('authorization')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));

                        match ($matcher->numberOfInvocations()) {
                            1 => $this->assertSame(
                                "/v1/catalog/{$storefront->toString()}/artists/{$id->toString()}",
                                $request->url()->path()->toString(),
                            ),
                            2 => $this->assertSame(
                                '/v1/catalog/fr/artists/178834/albums',
                                $request->url()->path()->toString(),
                            ),
                        };

                        if ($matcher->numberOfInvocations() === 2) {
                            $this->assertSame('offset=1', $request->url()->query()->toString());
                        }

                        return match ($matcher->numberOfInvocations()) {
                            1 => Either::right(new Success(
                                $request,
                                $response1,
                            )),
                            2 => Either::right(new Success(
                                $request,
                                $response2,
                            )),
                        };
                    });

                $artist = $catalog->artist($id)->match(
                    static fn($artist) => $artist,
                    static fn() => null,
                );

                $this->assertInstanceOf(Artist::class, $artist);
                $this->assertSame(178834, $artist->id()->toInt());
                $this->assertSame('Bruce Springsteen', $artist->name()->toString());
                $this->assertSame(
                    'https://music.apple.com/fr/artist/bruce-springsteen/178834',
                    $artist->url()->toString(),
                );
                $this->assertCount(1, $artist->genres());
                $this->assertSame(
                    'Rock',
                    $artist
                        ->genres()
                        ->find(static fn() => true)
                        ->match(
                            static fn($genre) => $genre->toString(),
                            static fn() => null,
                        ),
                );
                $albums = $artist->albums()->toList();
                $this->assertCount(2, $albums);
                $this->assertSame(1459884961, \current($albums)->toInt());
                \next($albums);
                $this->assertSame(203708420, \current($albums)->toInt());
            });
    }

    public function testAlbum()
    {
        $this
            ->forAll(
                StorefrontSet\Id::any(),
                AlbumSet\Id::any(),
            )
            ->then(function($storefront, $id) {
                $catalog = new Catalog(
                    new Earth\Clock,
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $storefront,
                );
                $response = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "data": [
                        {
                          "id": "203708420",
                          "type": "albums",
                          "href": "/v1/catalog/fr/albums/203708420",
                          "attributes": {
                            "artwork": {
                              "width": 6000,
                              "height": 6000,
                              "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                              "bgColor": "d9c8b6",
                              "textColor1": "100707",
                              "textColor2": "441016",
                              "textColor3": "382e2a",
                              "textColor4": "623436"
                            },
                            "artistName": "Bruce Springsteen",
                            "isSingle": false,
                            "url": "https://music.apple.com/fr/album/born-in-the-u-s-a/203708420",
                            "isComplete": true,
                            "genreNames": [
                              "Rock",
                              "Musique",
                              "Hard rock",
                              "Rock & roll",
                              "Arena rock",
                              "Pop",
                              "Pop/Rock"
                            ],
                            "trackCount": 12,
                            "isMasteredForItunes": true,
                            "releaseDate": "1984-06-04",
                            "name": "Born In the U.S.A.",
                            "recordLabel": "Columbia",
                            "copyright": "℗ 1984 Bruce Springsteen",
                            "playParams": {
                              "id": "203708420",
                              "kind": "album"
                            },
                            "editorialNotes": {
                              "standard": "Avec <i>Born In the U.S.A.</i>, Bruce Springsteen signe son plus grand succès, plaçant sept titres dans le top dix des classements américains. Point d’orgue de sa carrière, l’album le consacre auprès du grand public. Intégrant des éléments électroniques, il contraste musicalement avec son prédécesseur, le très acoustique <i>Nebraska</i>, sans rompre avec ses thèmes sociaux. C’est aussi un enregistrement fondateur du heartland rock, ce genre brut et sincère qui veut donner une voix aux cols bleus.",
                              "short": "Hit après hit, un album unique pour entrer définitivement dans la mémoire collective."
                            }
                          },
                          "relationships": {
                            "tracks": {
                              "data": [
                                {
                                  "id": "203708455",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203708455",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/c4/e7/0d/c4e70dda-9011-caf6-bc47-e80c93412dba/mzaf_702934268268391713.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/born-in-the-u-s-a/203708420?i=203708455",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 279784,
                                    "releaseDate": "1984-06-04",
                                    "name": "Born in the U.S.A.",
                                    "isrc": "USSM18400406",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203708455",
                                      "kind": "song"
                                    },
                                    "trackNumber": 1,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203708535",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203708535",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/a9/9f/ab/a99fab25-2d13-ed7a-7722-cade1c86abaf/mzaf_6582552387780196632.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/cover-me/203708420?i=203708535",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 208800,
                                    "releaseDate": "1984-06-04",
                                    "name": "Cover Me",
                                    "isrc": "USSM18400407",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203708535",
                                      "kind": "song"
                                    },
                                    "trackNumber": 2,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203708645",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203708645",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/e5/5e/53/e55e53da-2c2c-67aa-ae65-f234c3348229/mzaf_4396318545655358861.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/darlington-county/203708420?i=203708645",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 290252,
                                    "releaseDate": "1984-06-04",
                                    "name": "Darlington County",
                                    "isrc": "USSM18400408",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203708645",
                                      "kind": "song"
                                    },
                                    "trackNumber": 3,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203708785",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203708785",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/65/b0/01/65b00187-a2a8-a3d1-664c-5313646dc72e/mzaf_8849721794015928653.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/working-on-the-highway/203708420?i=203708785",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 195117,
                                    "releaseDate": "1984-06-04",
                                    "name": "Working on the Highway",
                                    "isrc": "USSM18400409",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203708785",
                                      "kind": "song"
                                    },
                                    "trackNumber": 4,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203708853",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203708853",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/f6/c0/18/f6c018a6-9ae5-c52b-721a-40045e054ccb/mzaf_8370981876139034547.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/downbound-train/203708420?i=203708853",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 217355,
                                    "releaseDate": "1984-06-04",
                                    "name": "Downbound Train",
                                    "isrc": "USSM18400410",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203708853",
                                      "kind": "song"
                                    },
                                    "trackNumber": 5,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203708893",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203708893",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview113/v4/02/cf/2e/02cf2e86-6361-9f51-d422-c03ced7deaea/mzaf_6817689753099025577.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/im-on-fire/203708420?i=203708893",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 159312,
                                    "releaseDate": "1984-06-04",
                                    "name": "I'm on Fire",
                                    "isrc": "USSM18400411",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203708893",
                                      "kind": "song"
                                    },
                                    "trackNumber": 6,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203709047",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203709047",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/94/ce/83/94ce8367-a9d8-83f7-d1e7-0dbc64b4b13c/mzaf_9125104819940748510.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/no-surrender/203708420?i=203709047",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 242345,
                                    "releaseDate": "1984-06-04",
                                    "name": "No Surrender",
                                    "isrc": "USSM18400412",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203709047",
                                      "kind": "song"
                                    },
                                    "trackNumber": 7,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203709090",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203709090",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/Music/v4/f5/83/4a/f5834a4f-f26f-872a-8f52-57d44a391938/mzaf_6821390060406210043.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/bobby-jean/203708420?i=203709090",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 228109,
                                    "releaseDate": "1984-06-04",
                                    "name": "Bobby Jean",
                                    "isrc": "USSM18400413",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203709090",
                                      "kind": "song"
                                    },
                                    "trackNumber": 8,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203709128",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203709128",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview19/v4/94/f1/42/94f142ba-8545-5892-3b9c-70cc4002c385/mzaf_7380149150498881083.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/im-goin-down/203708420?i=203709128",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 211928,
                                    "releaseDate": "1984-06-04",
                                    "name": "I'm Goin' Down",
                                    "isrc": "USSM18400414",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203709128",
                                      "kind": "song"
                                    },
                                    "trackNumber": 9,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203709191",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203709191",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/Music6/v4/88/50/32/8850321d-28b1-9d91-beb3-e2cc203c2971/mzaf_8172273184968413215.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/glory-days/203708420?i=203709191",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 258310,
                                    "releaseDate": "1984-06-04",
                                    "name": "Glory Days",
                                    "isrc": "USSM18400415",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203709191",
                                      "kind": "song"
                                    },
                                    "trackNumber": 10,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203709340",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203709340",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/ab/b3/48/abb34824-1510-708e-57d7-870206be5ba2/mzaf_8515316732595919510.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/dancing-in-the-dark/203708420?i=203709340",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 245298,
                                    "releaseDate": "1984-05-04",
                                    "name": "Dancing In the Dark",
                                    "isrc": "USSM18400416",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203709340",
                                      "kind": "song"
                                    },
                                    "trackNumber": 11,
                                    "composerName": "Bruce Springsteen"
                                  }
                                },
                                {
                                  "id": "203709448",
                                  "type": "songs",
                                  "href": "/v1/catalog/fr/songs/203709448",
                                  "attributes": {
                                    "previews": [
                                      {
                                        "url": "https://audio-ssl.itunes.apple.com/itunes-assets/Music/v4/0f/c6/93/0fc69334-9bcf-8b69-b59f-94289d9a2586/mzaf_2198334946621456856.plus.aac.p.m4a"
                                      }
                                    ],
                                    "artwork": {
                                      "width": 6000,
                                      "height": 6000,
                                      "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                                      "bgColor": "d9c8b6",
                                      "textColor1": "100707",
                                      "textColor2": "441016",
                                      "textColor3": "382e2a",
                                      "textColor4": "623436"
                                    },
                                    "artistName": "Bruce Springsteen",
                                    "url": "https://music.apple.com/fr/album/my-hometown/203708420?i=203709448",
                                    "discNumber": 1,
                                    "genreNames": [
                                      "Rock",
                                      "Musique"
                                    ],
                                    "durationInMillis": 277507,
                                    "releaseDate": "1984-06-04",
                                    "name": "My Hometown",
                                    "isrc": "USSM18400417",
                                    "albumName": "Born In the U.S.A.",
                                    "playParams": {
                                      "id": "203709448",
                                      "kind": "song"
                                    },
                                    "trackNumber": 12,
                                    "composerName": "Bruce Springsteen"
                                  }
                                }
                              ],
                              "href": "/v1/catalog/fr/albums/203708420/tracks"
                            },
                            "artists": {
                              "data": [
                                {
                                  "id": "178834",
                                  "type": "artists",
                                  "href": "/v1/catalog/fr/artists/178834"
                                }
                              ],
                              "href": "/v1/catalog/fr/albums/203708420/artists"
                            }
                          }
                        }
                      ]
                    }
                    JSON),
                );
                $fulfill
                    ->expects($this->once())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($storefront, $id, $authorization) {
                        return $request->url()->path()->toString() === "/v1/catalog/{$storefront->toString()}/albums/{$id->toString()}" &&
                            $request->method()->toString() === 'GET' &&
                            $authorization === $request->headers()->get('authorization')->match(
                                static fn($header) => $header,
                                static fn() => null,
                            );
                    }))
                    ->willReturnCallback(static fn($request) => Either::right(new Success(
                        $request,
                        $response,
                    )));

                $album = $catalog->album($id)->match(
                    static fn($album) => $album,
                    static fn() => null,
                );

                $this->assertInstanceOf(Album::class, $album);
                $this->assertSame(203708420, $album->id()->toInt());
                $this->assertSame(6000, $album->artwork()->width()->toInt());
                $this->assertSame(6000, $album->artwork()->height()->toInt());
                $this->assertSame(
                    'https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg',
                    $album->artwork()->url()->toString(),
                );
                $this->assertSame(
                    '#d9c8b6',
                    $album
                        ->artwork()
                        ->backgroundColor()
                        ->match(
                            static fn($color) => $color->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    '#100707',
                    $album
                        ->artwork()
                        ->textColor1()
                        ->match(
                            static fn($color) => $color->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    '#441016',
                    $album
                        ->artwork()
                        ->textColor2()
                        ->match(
                            static fn($color) => $color->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    '#382e2a',
                    $album
                        ->artwork()
                        ->textColor3()
                        ->match(
                            static fn($color) => $color->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    '#623436',
                    $album
                        ->artwork()
                        ->textColor4()
                        ->match(
                            static fn($color) => $color->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame('Born In the U.S.A.', $album->name()->toString());
                $this->assertFalse($album->single());
                $this->assertSame(
                    'https://music.apple.com/fr/album/born-in-the-u-s-a/203708420',
                    $album->url()->toString(),
                );
                $this->assertTrue($album->complete());
                $this->assertCount(7, $album->genres());
                $this->assertSame(
                    'Rock',
                    $album
                        ->genres()
                        ->find(static fn() => true)
                        ->match(
                            static fn($genre) => $genre->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertCount(12, $album->tracks());
                $this->assertTrue($album->masteredForItunes());
                $this->assertSame('1984-6-4', $album->release()->match(
                    static fn($point) => \sprintf(
                        '%s-%s-%s',
                        $point->year()->toString(),
                        $point->month()->toInt(),
                        $point->day()->toInt(),
                    ),
                    static fn() => null,
                ));
                $this->assertSame('Columbia', $album->recordLabel()->toString());
                $this->assertSame('℗ 1984 Bruce Springsteen', $album->copyright()->toString());
                $this->assertCount(1, $album->artists());
            });
    }

    public function testSong()
    {
        $this
            ->forAll(
                StorefrontSet\Id::any(),
                SongSet\Id::any(),
            )
            ->then(function($storefront, $id) {
                $catalog = new Catalog(
                    new Earth\Clock,
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $storefront,
                );
                $response = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "data": [
                        {
                          "id": "203708455",
                          "type": "songs",
                          "href": "/v1/catalog/us/songs/203708455",
                          "attributes": {
                            "previews": [
                              {
                                "url": "https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/c4/e7/0d/c4e70dda-9011-caf6-bc47-e80c93412dba/mzaf_702934268268391713.plus.aac.p.m4a"
                              }
                            ],
                            "artwork": {
                              "width": 6000,
                              "height": 6000,
                              "url": "https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg",
                              "bgColor": "d9c8b6",
                              "textColor1": "100707",
                              "textColor2": "441016",
                              "textColor3": "382e2a",
                              "textColor4": "623436"
                            },
                            "artistName": "Bruce Springsteen",
                            "url": "https://music.apple.com/us/album/born-in-the-u-s-a/203708420?i=203708455",
                            "discNumber": 1,
                            "genreNames": [
                              "Rock",
                              "Music"
                            ],
                            "durationInMillis": 279784,
                            "releaseDate": "1984-06-04",
                            "name": "Born in the U.S.A. track",
                            "isrc": "USSM18400406",
                            "albumName": "Born In the U.S.A.",
                            "playParams": {
                              "id": "203708455",
                              "kind": "song"
                            },
                            "trackNumber": 2,
                            "composerName": "Bruce Springsteen"
                          },
                          "relationships": {
                            "artists": {
                              "data": [
                                {
                                  "id": "178834",
                                  "type": "artists",
                                  "href": "/v1/catalog/us/artists/178834"
                                }
                              ],
                              "href": "/v1/catalog/us/songs/203708455/artists"
                            },
                            "albums": {
                              "data": [
                                {
                                  "id": "203708420",
                                  "type": "albums",
                                  "href": "/v1/catalog/us/albums/203708420"
                                }
                              ],
                              "href": "/v1/catalog/us/songs/203708455/albums"
                            }
                          }
                        }
                      ]
                    }
                    JSON),
                );
                $fulfill
                    ->expects($this->once())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($storefront, $id, $authorization) {
                        return $request->url()->path()->toString() === "/v1/catalog/{$storefront->toString()}/songs/{$id->toString()}" &&
                            $request->method()->toString() === 'GET' &&
                            $authorization === $request->headers()->get('authorization')->match(
                                static fn($header) => $header,
                                static fn() => null,
                            );
                    }))
                    ->willReturnCallback(static fn($request) => Either::right(new Success(
                        $request,
                        $response,
                    )));

                $song = $catalog->song($id)->match(
                    static fn($song) => $song,
                    static fn() => null,
                );

                $this->assertInstanceOf(Song::class, $song);
                $this->assertSame(203708455, $song->id()->toInt());
                $this->assertCount(1, $song->previews());
                $this->assertSame(
                    'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview71/v4/c4/e7/0d/c4e70dda-9011-caf6-bc47-e80c93412dba/mzaf_702934268268391713.plus.aac.p.m4a',
                    $song
                        ->previews()
                        ->find(static fn() => true)
                        ->match(
                            static fn($preview) => $preview->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(6000, $song->artwork()->width()->toInt());
                $this->assertSame(6000, $song->artwork()->height()->toInt());
                $this->assertSame(
                    'https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg',
                    $song->artwork()->url()->toString(),
                );
                $this->assertSame(
                    '#d9c8b6',
                    $song->artwork()->backgroundColor()->match(
                        static fn($color) => $color->toString(),
                        static fn() => null,
                    ),
                );
                $this->assertSame('#100707', $song->artwork()->textColor1()->match(
                    static fn($color) => $color->toString(),
                    static fn() => null,
                ));
                $this->assertSame('#441016', $song->artwork()->textColor2()->match(
                    static fn($color) => $color->toString(),
                    static fn() => null,
                ));
                $this->assertSame('#382e2a', $song->artwork()->textColor3()->match(
                    static fn($color) => $color->toString(),
                    static fn() => null,
                ));
                $this->assertSame('#623436', $song->artwork()->textColor4()->match(
                    static fn($color) => $color->toString(),
                    static fn() => null,
                ));
                $this->assertSame(
                    'https://music.apple.com/us/album/born-in-the-u-s-a/203708420?i=203708455',
                    $song->url()->toString(),
                );
                $this->assertSame(1, $song->discNumber()->match(
                    static fn($number) => $number->toInt(),
                    static fn() => null,
                ));
                $this->assertCount(2, $song->genres());
                $this->assertSame(279784, $song->duration()->match(
                    static fn($duration) => $duration->toInt(),
                    static fn() => null,
                ));
                $this->assertSame('1984-6-4', $song->release()->match(
                    static fn($point) => \sprintf(
                        '%s-%s-%s',
                        $point->year()->toString(),
                        $point->month()->toInt(),
                        $point->day()->toInt(),
                    ),
                    static fn() => null,
                ));
                $this->assertSame('Born in the U.S.A. track', $song->name()->toString());
                $this->assertSame('USSM18400406', $song->isrc()->match(
                    static fn($isrc) => $isrc->toString(),
                    static fn() => null,
                ));
                $this->assertSame(2, $song->trackNumber()->match(
                    static fn($trackNumber) => $trackNumber->toInt(),
                    static fn() => null,
                ));
                $this->assertSame('Bruce Springsteen', $song->composer()->name());
                $this->assertCount(1, $song->artists());
                $this->assertCount(1, $song->albums());
            });
    }

    public function testGenres()
    {
        $this
            ->forAll(StorefrontSet\Id::any())
            ->then(function($storefront) {
                $catalog = new Catalog(
                    $this->createMock(Clock::class),
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $storefront,
                );
                $response1 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "next": "/v1/catalog/{$storefront->toString()}/genres?offset=1",
                      "data": [
                        {
                          "id": "34",
                          "type": "genres",
                          "href": "/v1/catalog/fr/genres/34",
                          "attributes": {
                            "name": "Musique"
                          }
                        }
                      ]
                    }
                    JSON),
                );
                $response2 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "data": [
                        {
                          "id": "20",
                          "type": "genres",
                          "href": "/v1/catalog/fr/genres/20",
                          "attributes": {
                            "name": "Alternative"
                          }
                        }
                      ]
                    }
                    JSON),
                );
                $fulfill
                    ->expects($matcher = $this->exactly(2))
                    ->method('__invoke')
                    ->willReturnCallback(function($request) use ($matcher, $storefront, $authorization, $response1, $response2) {
                        $this->assertSame('GET', $request->method()->toString());
                        $this->assertSame($authorization, $request->headers()->get('authorization')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));

                        match ($matcher->numberOfInvocations()) {
                            1 => $this->assertSame(
                                "https://api.music.apple.com/v1/catalog/{$storefront->toString()}/genres",
                                $request->url()->toString(),
                            ),
                            2 => $this->assertSame(
                                "https://api.music.apple.com/v1/catalog/{$storefront->toString()}/genres?offset=1",
                                $request->url()->toString(),
                            ),
                        };

                        return match ($matcher->numberOfInvocations()) {
                            1 => Either::right(new Success(
                                $request,
                                $response1,
                            )),
                            2 => Either::right(new Success(
                                $request,
                                $response2,
                            )),
                        };
                    });

                $genres = $catalog->genres();

                $this->assertInstanceOf(Sequence::class, $genres);
                $genres = $genres->toList();
                $this->assertCount(2, $genres);
                $this->assertSame('Musique', \current($genres)->toString());
                \next($genres);
                $this->assertSame('Alternative', \current($genres)->toString());
            });
    }

    public function testSearch()
    {
        $this
            ->forAll(
                StorefrontSet\Id::any(),
                DataSet\Strings::any(),
            )
            ->take(100)
            ->then(function($storefront, $term) {
                $catalog = new Catalog(
                    $this->createMock(Clock::class),
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $storefront,
                );
                $response1 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "results": {
                        "songs": {
                          "href": "/v1/catalog/{$storefront->toString()}/search?limit=1&term=foo&types=songs",
                          "next": "/v1/catalog/{$storefront->toString()}/search?offset=1&term=foo&types=songs",
                          "data": [
                            {
                              "id": "482678717",
                              "type": "songs",
                              "href": "/v1/catalog/{$storefront->toString()}/songs/482678717"
                            }
                          ]
                        },
                        "albums": {
                          "href": "/v1/catalog/{$storefront->toString()}/search?limit=1&term=foo&types=albums",
                          "next": "/v1/catalog/{$storefront->toString()}/search?offset=1&term=foo&types=albums",
                          "data": [
                            {
                              "id": "1468503258",
                              "type": "albums",
                              "href": "/v1/catalog/{$storefront->toString()}/albums/1468503258"
                            }
                          ]
                        },
                        "artists": {
                          "href": "/v1/catalog/{$storefront->toString()}/search?limit=1&term=foo&types=artists",
                          "next": "/v1/catalog/{$storefront->toString()}/search?offset=1&term=foo&types=artists",
                          "data": [
                            {
                              "id": "205748310",
                              "type": "artists",
                              "href": "/v1/catalog/{$storefront->toString()}/artists/205748310"
                            }
                          ]
                        }
                      }
                    }
                    JSON),
                );
                $response2 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "results": {
                        "artists": {
                          "href": "/v1/catalog/{$storefront->toString()}/search?limit=1&term=foo&types=artists",
                          "data": [
                            {
                              "id": "205748311",
                              "type": "artists",
                              "href": "/v1/catalog/{$storefront->toString()}/artists/205748311"
                            }
                          ]
                        }
                      }
                    }
                    JSON),
                );
                $response3 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "results": {
                        "albums": {
                          "href": "/v1/catalog/{$storefront->toString()}/search?limit=1&term=foo&types=albums",
                          "data": [
                            {
                              "id": "1468503259",
                              "type": "albums",
                              "href": "/v1/catalog/{$storefront->toString()}/albums/1468503259"
                            }
                          ]
                        }
                      }
                    }
                    JSON),
                );
                $response4 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "results": {
                        "songs": {
                          "href": "/v1/catalog/fr/search?limit=1&term=foo&types=songs",
                          "data": [
                            {
                              "id": "482678718",
                              "type": "songs",
                              "href": "/v1/catalog/fr/songs/482678718"
                            }
                          ]
                        }
                      }
                    }
                    JSON),
                );
                $fulfill
                    ->expects($matcher = $this->exactly(4))
                    ->method('__invoke')
                    ->willReturnCallback(function($request) use ($matcher, $storefront, $term, $authorization, $response1, $response2, $response3, $response4) {
                        $term = \urlencode($term);

                        $this->assertSame('GET', $request->method()->toString());
                        $this->assertSame($authorization, $request->headers()->get('authorization')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));

                        match ($matcher->numberOfInvocations()) {
                            1 => $this->assertSame(
                                "https://api.music.apple.com/v1/catalog/{$storefront->toString()}/search?term=$term&types=artists,albums,songs&limit=25",
                                $request->url()->toString(),
                            ),
                            2 => $this->assertSame(
                                "https://api.music.apple.com/v1/catalog/{$storefront->toString()}/search?offset=1&term=foo&types=artists",
                                $request->url()->toString(),
                            ),
                            3 => $this->assertSame(
                                "https://api.music.apple.com/v1/catalog/{$storefront->toString()}/search?offset=1&term=foo&types=albums",
                                $request->url()->toString(),
                            ),
                            4 => $this->assertSame(
                                "https://api.music.apple.com/v1/catalog/{$storefront->toString()}/search?offset=1&term=foo&types=songs",
                                $request->url()->toString(),
                            ),
                        };

                        return match ($matcher->numberOfInvocations()) {
                            1 => Either::right(new Success(
                                $request,
                                $response1,
                            )),
                            2 => Either::right(new Success(
                                $request,
                                $response2,
                            )),
                            3 => Either::right(new Success(
                                $request,
                                $response3,
                            )),
                            4 => Either::right(new Success(
                                $request,
                                $response4,
                            )),
                        };
                    });

                $search = $catalog->search($term);
                $artists = $search->artists()->toList();
                $albums = $search->albums()->toList();
                $songs = $search->songs()->toList();

                $this->assertInstanceOf(Search::class, $search);
                $this->assertSame($term, $search->term());
                $this->assertCount(2, $artists);
                $this->assertCount(2, $albums);
                $this->assertCount(2, $songs);
                $this->assertSame('205748310', \current($artists)->toString());
                \next($artists);
                $this->assertSame('205748311', \current($artists)->toString());
                $this->assertSame('1468503258', \current($albums)->toString());
                \next($albums);
                $this->assertSame('1468503259', \current($albums)->toString());
                $this->assertSame('482678717', \current($songs)->toString());
                \next($songs);
                $this->assertSame('482678718', \current($songs)->toString());
            });
    }
}
