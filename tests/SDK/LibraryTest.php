<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Library,
    Library\Artist,
    Library\Album,
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
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Artist as ArtistSet;
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
}
