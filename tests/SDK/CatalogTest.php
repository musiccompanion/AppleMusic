<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Catalog,
    Catalog\Artist,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header\Authorization,
    Header\AuthorizationValue,
    Message\Response,
};
use Innmind\Stream\Readable;
use Fixtures\MusicCompanion\AppleMusic\SDK\{
    Storefront as StorefrontSet,
    Catalog\Artist as ArtistSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;

class CatalogTest extends TestCase
{
    use BlackBox;

    public function testArtist()
    {
        $this
            ->forAll(
                StorefrontSet\Id::any(),
                ArtistSet\Id::any()
            )
            ->take(1000)
            ->then(function($storefront, $id) {
                $catalog = new Catalog(
                    $fulfill = $this->createMock(Transport::class),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $storefront
                );
                $fulfill
                    ->expects($this->at(0))
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($storefront, $id, $authorization) {
                        return (string) $request->url()->path() === "/v1/catalog/$storefront/artists/$id" &&
                            (string) $request->method() === 'GET' &&
                            $request->headers()->get('authorization') === $authorization;
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
JSON
                    );
                $fulfill
                    ->expects($this->at(1))
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($authorization) {
                        return (string) $request->url()->path() === '/v1/catalog/fr/artists/178834/albums' &&
                            (string) $request->url()->query() === 'offset=1' &&
                            (string) $request->method() === 'GET' &&
                            $request->headers()->get('authorization') === $authorization;
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
JSON
                    );

                $artist = $catalog->artist($id);

                $this->assertInstanceOf(Artist::class, $artist);
                $this->assertSame($id, $artist->id());
                $this->assertSame('Bruce Springsteen', (string) $artist->name());
                $this->assertSame(
                    'https://music.apple.com/fr/artist/bruce-springsteen/178834',
                    (string) $artist->url()
                );
                $this->assertCount(1, $artist->genres());
                $this->assertSame('Rock', (string) $artist->genres()->current());
                $this->assertCount(2, $artist->albums());
                $this->assertSame(1459884961, $artist->albums()->current()->toInt());
                $artist->albums()->next();
                $this->assertSame(203708420, $artist->albums()->current()->toInt());
            });
    }
}
