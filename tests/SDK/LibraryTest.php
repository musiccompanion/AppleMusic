<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Library,
    Library\Artist,
    Library\Album,
    Library\Song,
    Storefront,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header\Authorization,
    Header\AuthorizationValue,
    Header\Header,
    Header\Value\Value,
    Message\Response,
};
use Innmind\Stream\Readable;
use Innmind\Immutable\SetInterface;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\{
    Artist as ArtistSet,
    Album as AlbumSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;

class LibraryTest extends TestCase
{
    use BlackBox;

    public function testStorefront()
    {
        $library = new Library(
            $fulfill = $this->createMock(Transport::class),
            $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
            $userToken = new Header('Music-User-Token', new Value('token'))
        );
        $fulfill
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function($request) use ($authorization, $userToken): bool {
                return (string) $request->url() === '/v1/me/storefront' &&
                    (string) $request->method() === 'GET' &&
                    $request->headers()->get('authorization') === $authorization &&
                    $request->headers()->get('music-user-token') === $userToken;
            }))
            ->willReturn($response = $this->createMock(Response::class));
        $response
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Readable::class));
        $body
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(<<<JSON
{
  "data": [
    {
      "id": "fr",
      "type": "storefronts",
      "href": "/v1/storefronts/fr",
      "attributes": {
        "explicitContentPolicy": "allowed",
        "defaultLanguageTag": "fr-FR",
        "name": "France",
        "supportedLanguageTags": [
          "fr-FR",
          "en-GB"
        ]
      }
    }
  ]
}
JSON
            );

        $storefront = $library->storefront();

        $this->assertInstanceOf(Storefront::class, $storefront);
        $this->assertSame('fr', (string) $storefront->id());
        $this->assertSame('France', (string) $storefront->name());
        $this->assertSame('fr-FR', (string) $storefront->defaultLanguage());
        $this->assertCount(2, $storefront->supportedLanguages());
    }

    public function testArtists()
    {
        $library = new Library(
            $fulfill = $this->createMock(Transport::class),
            $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
            $userToken = new Header('Music-User-Token', new Value('token'))
        );
        $fulfill
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->callback(static function($request) use ($authorization, $userToken): bool {
                return (string) $request->url() === '/v1/me/library/artists' &&
                    (string) $request->method() === 'GET' &&
                    $request->headers()->get('authorization') === $authorization &&
                    $request->headers()->get('music-user-token') === $userToken;
            }))
            ->willReturn($response = $this->createMock(Response::class));
        $response
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Readable::class));
        $body
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(<<<JSON
{
  "next": "/v1/me/library/artists?offset=25",
  "data": [
    {
      "id": "r.2S6SRHl",
      "type": "library-artists",
      "href": "/v1/me/library/artists/r.2S6SRHl",
      "attributes": {
        "name": "\"Weird Al\" Yankovic"
      }
    }
  ],
  "meta": {
    "total": 1158
  }
}
JSON
            );
        $fulfill
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->callback(static function($request) use ($authorization, $userToken): bool {
                return (string) $request->url() === '/v1/me/library/artists?offset=25' &&
                    (string) $request->method() === 'GET' &&
                    $request->headers()->get('authorization') === $authorization &&
                    $request->headers()->get('music-user-token') === $userToken;
            }))
            ->willReturn($response = $this->createMock(Response::class));
        $response
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Readable::class));
        $body
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(<<<JSON
{
  "data": [
    {
      "id": "r.o860e82",
      "type": "library-artists",
      "href": "/v1/me/library/artists/r.o860e82",
      "attributes": {
        "name": "(hed) p.e."
      }
    }
  ],
  "meta": {
    "total": 1158
  }
}
JSON
            );

        $artists = $library->artists();

        $this->assertInstanceOf(SetInterface::class, $artists);
        $this->assertSame(Artist::class, (string) $artists->type());
        $this->assertCount(2, $artists);
        $this->assertSame('r.2S6SRHl', (string) $artists->current()->id());
        $this->assertSame('"Weird Al" Yankovic', (string) $artists->current()->name());
        $artists->next();
        $this->assertSame('r.o860e82', (string) $artists->current()->id());
        $this->assertSame('(hed) p.e.', (string) $artists->current()->name());
    }

    public function testAlbums()
    {
        $this
            ->forAll(ArtistSet\Id::any())
            ->then(function($artist) {
                $library = new Library(
                    $fulfill = $this->createMock(Transport::class),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $userToken = new Header('Music-User-Token', new Value('token'))
                );
                $fulfill
                    ->expects($this->at(0))
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($artist, $authorization, $userToken): bool {
                        return (string) $request->url() === "/v1/me/library/artists/$artist/albums?include=artists" &&
                            (string) $request->method() === 'GET' &&
                            $request->headers()->get('authorization') === $authorization &&
                            $request->headers()->get('music-user-token') === $userToken;
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('__toString')
                    ->willReturn(<<<JSON
{
  "next": "/v1/me/library/artists/{$artist}/albums?offset=1",
  "data": [
    {
      "id": "l.wXEf8fr",
      "type": "library-albums",
      "href": "/v1/me/library/albums/l.wXEf8fr",
      "attributes": {
        "playParams": {
          "id": "l.wXEf8fr",
          "kind": "album",
          "isLibrary": true
        },
        "artistName": "(hed) p.e.",
        "trackCount": 5,
        "name": "Skull & Bonus"
      },
      "relationships": {
        "artists": {
          "data": [
            {
              "id": "r.o860e82",
              "type": "library-artists",
              "href": "/v1/me/library/artists/r.o860e82",
              "attributes": {
                "name": "(hed) p.e."
              }
            }
          ],
          "href": "/v1/me/library/albums/l.wXEf8fr/artists"
        }
      }
    }
  ],
  "meta": {
    "total": 2
  }
}
JSON
                    );
                $fulfill
                    ->expects($this->at(1))
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($artist, $authorization, $userToken): bool {
                        return (string) $request->url() === "/v1/me/library/artists/$artist/albums?offset=1&include=artists" &&
                            (string) $request->method() === 'GET' &&
                            $request->headers()->get('authorization') === $authorization &&
                            $request->headers()->get('music-user-token') === $userToken;
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('__toString')
                    ->willReturn(<<<JSON
{
  "data": [
    {
      "id": "l.gACheFi",
      "type": "library-albums",
      "href": "/v1/me/library/albums/l.gACheFi",
      "attributes": {
        "trackCount": 22,
        "artistName": "(hed) p.e.",
        "name": "Truth Rising",
        "playParams": {
          "id": "l.gACheFi",
          "kind": "album",
          "isLibrary": true
        },
        "artwork": {
          "width": 1200,
          "height": 1200,
          "url": "https://is2-ssl.mzstatic.com/image/thumb/Music/c5/98/81/mzi.ljuovcvg.jpg/{w}x{h}bb.jpeg"
        }
      },
      "relationships": {
        "artists": {
          "data": [
            {
              "id": "r.o860e82",
              "type": "library-artists",
              "href": "/v1/me/library/artists/r.o860e82",
              "attributes": {
                "name": "(hed) p.e."
              }
            }
          ],
          "href": "/v1/me/library/albums/l.gACheFi/artists"
        }
      }
    }
  ],
  "meta": {
    "total": 2
  }
}
JSON
                    );

                $albums = $library->albums($artist);

                $this->assertInstanceOf(SetInterface::class, $albums);
                $this->assertSame(Album::class, (string) $albums->type());
                $this->assertCount(2, $albums);
                $this->assertSame('l.wXEf8fr', (string) $albums->current()->id());
                $this->assertSame('Skull & Bonus', (string) $albums->current()->name());
                $this->assertFalse($albums->current()->hasArtwork());
                $this->assertCount(1, $albums->current()->artists());
                $this->assertSame('r.o860e82', (string) $albums->current()->artists()->current());
                $albums->next();
                $this->assertSame('l.gACheFi', (string) $albums->current()->id());
                $this->assertTrue($albums->current()->hasArtwork());
                $this->assertSame('1200', (string) $albums->current()->artwork()->width());
                $this->assertSame('1200', (string) $albums->current()->artwork()->height());
                $this->assertSame(
                    'https://is2-ssl.mzstatic.com/image/thumb/Music/c5/98/81/mzi.ljuovcvg.jpg/{w}x{h}bb.jpeg',
                    (string) $albums->current()->artwork()->url()
                );
            });
    }

    public function testSongs()
    {
        $this
            ->forAll(AlbumSet\Id::any())
            ->then(function($album) {
                $library = new Library(
                    $fulfill = $this->createMock(Transport::class),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $userToken = new Header('Music-User-Token', new Value('token'))
                );
                $fulfill
                    ->expects($this->at(0))
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($album, $authorization, $userToken): bool {
                        return (string) $request->url() === "/v1/me/library/albums/$album/tracks?include=albums,artists" &&
                            (string) $request->method() === 'GET' &&
                            $request->headers()->get('authorization') === $authorization &&
                            $request->headers()->get('music-user-token') === $userToken;
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('__toString')
                    ->willReturn(<<<JSON
{
  "next": "/v1/me/library/albums/{$album}/tracks?offset=1",
  "data": [
    {
      "id": "i.mmpYYzeu4J0XGD",
      "type": "library-songs",
      "href": "/v1/me/library/songs/i.mmpYYzeu4J0XGD",
      "attributes": {
        "artistName": "(hed) p.e.",
        "durationInMillis": 325459,
        "albumName": "Skull & Bonus",
        "trackNumber": 1,
        "name": "Judgement Day",
        "genreNames": [
          "Rapcore, Punk, Rap"
        ]
      },
      "relationships": {
        "artists": {
          "data": [
            {
              "id": "r.o860e82",
              "type": "library-artists",
              "href": "/v1/me/library/artists/r.o860e82",
              "attributes": {
                "name": "(hed) p.e."
              }
            }
          ],
          "href": "/v1/me/library/songs/i.mmpYYzeu4J0XGD/artists"
        },
        "albums": {
          "data": [
            {
              "id": "l.wXEf8fr",
              "type": "library-albums",
              "href": "/v1/me/library/albums/l.wXEf8fr",
              "attributes": {
                "artistName": "(hed) p.e.",
                "name": "Skull & Bonus",
                "playParams": {
                  "id": "l.wXEf8fr",
                  "kind": "album",
                  "isLibrary": true
                },
                "trackCount": 5
              }
            }
          ],
          "href": "/v1/me/library/songs/i.mmpYYzeu4J0XGD/albums"
        }
      }
    }
  ],
  "meta": {
    "total": 2
  }
}
JSON
                    );
                $fulfill
                    ->expects($this->at(1))
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($album, $authorization, $userToken): bool {
                        return (string) $request->url() === "/v1/me/library/albums/$album/tracks?offset=1&include=albums,artists" &&
                            (string) $request->method() === 'GET' &&
                            $request->headers()->get('authorization') === $authorization &&
                            $request->headers()->get('music-user-token') === $userToken;
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('__toString')
                    ->willReturn(<<<JSON
{
  "data": [
    {
      "id": "i.b1Jvv08sqZgz8A",
      "type": "library-songs",
      "href": "/v1/me/library/songs/i.b1Jvv08sqZgz8A",
      "attributes": {
        "albumName": "Skull & Bonus",
        "playParams": {
          "id": "i.b1Jvv08sqZgz8A",
          "kind": "song",
          "isLibrary": true,
          "reporting": false
        },
        "trackNumber": 2,
        "artistName": "(hed) p.e.",
        "durationInMillis": 435487,
        "genreNames": [
          "Rapcore, Punk, Rap"
        ],
        "name": "Takeover (feat. Axe Murder Boyz & DGAF)"
      },
      "relationships": {
        "artists": {
          "data": [
            {
              "id": "r.o860e82",
              "type": "library-artists",
              "href": "/v1/me/library/artists/r.o860e82",
              "attributes": {
                "name": "(hed) p.e."
              }
            }
          ],
          "href": "/v1/me/library/songs/i.b1Jvv08sqZgz8A/artists"
        },
        "albums": {
          "data": [
            {
              "id": "l.wXEf8fr",
              "type": "library-albums",
              "href": "/v1/me/library/albums/l.wXEf8fr",
              "attributes": {
                "playParams": {
                  "id": "l.wXEf8fr",
                  "kind": "album",
                  "isLibrary": true
                },
                "trackCount": 5,
                "artistName": "(hed) p.e.",
                "name": "Skull & Bonus"
              }
            }
          ],
          "href": "/v1/me/library/songs/i.b1Jvv08sqZgz8A/albums"
        }
      }
    }
  ],
  "meta": {
    "total": 2
  }
}
JSON
                    );

                $songs = $library->songs($album);

                $this->assertInstanceOf(SetInterface::class, $songs);
                $this->assertSame(Song::class, (string) $songs->type());
                $this->assertCount(2, $songs);
                $this->assertSame('i.mmpYYzeu4J0XGD', (string) $songs->current()->id());
                $this->assertSame('Judgement Day', (string) $songs->current()->name());
                $this->assertSame('325459', (string) $songs->current()->duration());
                $this->assertSame('1', (string) $songs->current()->trackNumber());
                $this->assertCount(1, $songs->current()->genres());
                $this->assertSame('Rapcore, Punk, Rap', (string) $songs->current()->genres()->current());
                $this->assertCount(1, $songs->current()->artists());
                $this->assertCount(1, $songs->current()->albums());
                $this->assertSame('r.o860e82', (string) $songs->current()->artists()->current());
                $this->assertSame('l.wXEf8fr', (string) $songs->current()->albums()->current());
                $songs->next();
                $this->assertSame('i.b1Jvv08sqZgz8A', (string) $songs->current()->id());
                $this->assertSame('Takeover (feat. Axe Murder Boyz & DGAF)', (string) $songs->current()->name());
            });
    }
}
