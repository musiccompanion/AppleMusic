<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Library,
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
}
