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
use Innmind\Immutable\{
    Set,
    Sequence,
};
use function Innmind\Immutable\{
    unwrap,
    first,
};
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
            $userToken = new Header('Music-User-Token', new Value('token')),
        );
        $fulfill
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function($request) use ($authorization, $userToken): bool {
                return $request->url()->toString() === '/v1/me/storefront' &&
                    $request->method()->toString() === 'GET' &&
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
            ->method('toString')
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
        $this->assertSame('fr', $storefront->id()->toString());
        $this->assertSame('France', $storefront->name()->toString());
        $this->assertSame('fr-FR', $storefront->defaultLanguage()->toString());
        $this->assertCount(2, $storefront->supportedLanguages());
    }

    public function testArtists()
    {
        $library = new Library(
            $fulfill = $this->createMock(Transport::class),
            $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
            $userToken = new Header('Music-User-Token', new Value('token')),
        );
        $fulfill
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive(
                [$this->callback(static function($request) use ($authorization, $userToken): bool {
                    return $request->url()->toString() === '/v1/me/library/artists' &&
                        $request->method()->toString() === 'GET' &&
                        $request->headers()->get('authorization') === $authorization &&
                        $request->headers()->get('music-user-token') === $userToken;
                })],
                [$this->callback(static function($request) use ($authorization, $userToken): bool {
                    return $request->url()->toString() === '/v1/me/library/artists?offset=25' &&
                        $request->method()->toString() === 'GET' &&
                        $request->headers()->get('authorization') === $authorization &&
                        $request->headers()->get('music-user-token') === $userToken;
                })],
            )
            ->will($this->onConsecutiveCalls(
                $response1 = $this->createMock(Response::class),
                $response2 = $this->createMock(Response::class),
            ));
        $response1
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Readable::class));
        $body
            ->expects($this->once())
            ->method('toString')
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
        $response2
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Readable::class));
        $body
            ->expects($this->once())
            ->method('toString')
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

        $this->assertInstanceOf(Sequence::class, $artists);
        $this->assertSame(Artist::class, $artists->type());
        $artists = unwrap($artists);
        $this->assertCount(2, $artists);
        $this->assertSame('r.2S6SRHl', \current($artists)->id()->toString());
        $this->assertSame('"Weird Al" Yankovic', \current($artists)->name()->toString());
        \next($artists);
        $this->assertSame('r.o860e82', \current($artists)->id()->toString());
        $this->assertSame('(hed) p.e.', \current($artists)->name()->toString());
    }

    public function testAlbums()
    {
        $this
            ->forAll(ArtistSet\Id::any())
            ->then(function($artist) {
                $library = new Library(
                    $fulfill = $this->createMock(Transport::class),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $userToken = new Header('Music-User-Token', new Value('token')),
                );
                $fulfill
                    ->expects($this->exactly(2))
                    ->method('__invoke')
                    ->withConsecutive(
                        [$this->callback(static function($request) use ($artist, $authorization, $userToken): bool {
                            return $request->url()->toString() === "/v1/me/library/artists/{$artist->toString()}/albums?include=artists" &&
                                $request->method()->toString() === 'GET' &&
                                $request->headers()->get('authorization') === $authorization &&
                                $request->headers()->get('music-user-token') === $userToken;
                        })],
                        [$this->callback(static function($request) use ($artist, $authorization, $userToken): bool {
                            return $request->url()->toString() === "/v1/me/library/artists/{$artist->toString()}/albums?offset=1&include=artists" &&
                                $request->method()->toString() === 'GET' &&
                                $request->headers()->get('authorization') === $authorization &&
                                $request->headers()->get('music-user-token') === $userToken;
                        })],
                    )
                    ->will($this->onConsecutiveCalls(
                        $response1 = $this->createMock(Response::class),
                        $response2 = $this->createMock(Response::class),
                    ));
                $response1
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('toString')
                    ->willReturn(<<<JSON
{
  "next": "/v1/me/library/artists/{$artist->toString()}/albums?offset=1",
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
                $response2
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('toString')
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

                $this->assertInstanceOf(Set::class, $albums);
                $this->assertSame(Album::class, $albums->type());
                $albums = unwrap($albums);
                $this->assertCount(2, $albums);
                $this->assertSame('l.wXEf8fr', \current($albums)->id()->toString());
                $this->assertSame('Skull & Bonus', \current($albums)->name()->toString());
                $this->assertFalse(\current($albums)->hasArtwork());
                $this->assertCount(1, \current($albums)->artists());
                $this->assertSame('r.o860e82', first(\current($albums)->artists())->toString());
                \next($albums);
                $this->assertSame('l.gACheFi', \current($albums)->id()->toString());
                $this->assertTrue(\current($albums)->hasArtwork());
                $this->assertSame('1200', \current($albums)->artwork()->width()->toString());
                $this->assertSame('1200', \current($albums)->artwork()->height()->toString());
                $this->assertSame(
                    'https://is2-ssl.mzstatic.com/image/thumb/Music/c5/98/81/mzi.ljuovcvg.jpg/{w}x{h}bb.jpeg',
                    \current($albums)->artwork()->url()->toString(),
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
                    $userToken = new Header('Music-User-Token', new Value('token')),
                );
                $fulfill
                    ->expects($this->exactly(2))
                    ->method('__invoke')
                    ->withConsecutive(
                        [$this->callback(static function($request) use ($album, $authorization, $userToken): bool {
                            return $request->url()->toString() === "/v1/me/library/albums/{$album->toString()}/tracks?include=albums,artists" &&
                                $request->method()->toString() === 'GET' &&
                                $request->headers()->get('authorization') === $authorization &&
                                $request->headers()->get('music-user-token') === $userToken;
                        })],
                        [$this->callback(static function($request) use ($album, $authorization, $userToken): bool {
                            return $request->url()->toString() === "/v1/me/library/albums/{$album->toString()}/tracks?offset=1&include=albums,artists" &&
                                $request->method()->toString() === 'GET' &&
                                $request->headers()->get('authorization') === $authorization &&
                                $request->headers()->get('music-user-token') === $userToken;
                        })],
                    )
                    ->will($this->onConsecutiveCalls(
                        $response1 = $this->createMock(Response::class),
                        $response2 = $this->createMock(Response::class),
                    ));
                $response1
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('toString')
                    ->willReturn(<<<JSON
{
  "next": "/v1/me/library/albums/{$album->toString()}/tracks?offset=1",
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
                $response2
                    ->expects($this->once())
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->expects($this->once())
                    ->method('toString')
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

                $this->assertInstanceOf(Set::class, $songs);
                $this->assertSame(Song::class, $songs->type());
                $songs = unwrap($songs);
                $this->assertCount(2, $songs);
                $this->assertSame('i.mmpYYzeu4J0XGD', \current($songs)->id()->toString());
                $this->assertSame('Judgement Day', \current($songs)->name()->toString());
                $this->assertSame('325459', \current($songs)->duration()->toString());
                $this->assertSame('1', \current($songs)->trackNumber()->toString());
                $this->assertCount(1, \current($songs)->genres());
                $this->assertSame('Rapcore, Punk, Rap', first(\current($songs)->genres())->toString());
                $this->assertCount(1, \current($songs)->artists());
                $this->assertCount(1, \current($songs)->albums());
                $this->assertSame('r.o860e82', first(\current($songs)->artists())->toString());
                $this->assertSame('l.wXEf8fr', first(\current($songs)->albums())->toString());
                \next($songs);
                $this->assertSame('i.b1Jvv08sqZgz8A', \current($songs)->id()->toString());
                $this->assertSame('Takeover (feat. Axe Murder Boyz & DGAF)', \current($songs)->name()->toString());
            });
    }
}
