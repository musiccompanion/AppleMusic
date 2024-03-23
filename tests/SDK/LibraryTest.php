<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Library,
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
    Header\Header,
    Header\Value\Value,
    ProtocolVersion,
    Response,
    Response\StatusCode,
};
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\{
    Sequence,
    Either,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\{
    Library\Artist as ArtistSet,
    Library\Album as AlbumSet,
    Storefront as StorefrontSet
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;

class LibraryTest extends TestCase
{
    use BlackBox;

    public function testStorefront()
    {
        $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt'));
        $userToken = new Header('Music-User-Token', new Value('token'));
        $fulfill = $this->createMock(Transport::class);
        $response = Response::of(
            StatusCode::ok,
            ProtocolVersion::v11,
            null,
            Content::ofString(<<<JSON
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
            JSON),
        );
        $fulfill
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function($request) use ($authorization, $userToken): bool {
                return $request->url()->toString() === 'https://api.music.apple.com/v1/me/storefront' &&
                    $request->method()->toString() === 'GET' &&
                    $authorization === $request->headers()->get('authorization')->match(
                        static fn($header) => $header,
                        static fn() => null,
                    ) &&
                    $userToken === $request->headers()->get('music-user-token')->match(
                        static fn($header) => $header,
                        static fn() => null,
                    );
            }))
            ->willReturnCallback(static fn($request) => Either::right(new Success(
                $request,
                $response,
            )));

        $library = Library::of(
            new HttpTransport($fulfill),
            $authorization,
            $userToken,
        )->match(
            static fn($library) => $library,
            static fn() => null,
        );

        $this->assertInstanceOf(Library::class, $library);

        $storefront = $library->storefront();

        $this->assertInstanceOf(Storefront::class, $storefront);
        $this->assertSame('fr', $storefront->id()->toString());
        $this->assertSame('France', $storefront->name()->toString());
        $this->assertSame('fr-FR', $storefront->defaultLanguage()->toString());
        $this->assertCount(2, $storefront->supportedLanguages());
    }

    public function testArtists()
    {
        $this
            ->forAll(StorefrontSet::any())
            ->then(function($storefront) {
                $library = new Library(
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $userToken = new Header('Music-User-Token', new Value('token')),
                    $storefront,
                );
                $response1 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
                    {
                      "next": "/v1/me/library/artists?offset=25",
                      "data": [
                        {
                          "id": "r.2S6SRHl",
                          "type": "library-artists",
                          "href": "/v1/me/library/artists/r.2S6SRHl",
                          "attributes": {
                            "name": "\"Weird Al\" Yankovic"
                          },
                          "relationships": {
                            "catalog": {
                                "data": [{
                                    "id": "1234"
                                }]
                            }
                          }
                        }
                      ],
                      "meta": {
                        "total": 1158
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
                      "data": [
                        {
                          "id": "r.o860e82",
                          "type": "library-artists",
                          "href": "/v1/me/library/artists/r.o860e82",
                          "attributes": {
                            "name": "(hed) p.e."
                          },
                          "relationships": {
                            "catalog": {
                                "data": []
                            }
                          }
                        }
                      ],
                      "meta": {
                        "total": 1158
                      }
                    }
                    JSON),
                );
                $fulfill
                    ->expects($matcher = $this->exactly(2))
                    ->method('__invoke')
                    ->willReturnCallback(function($request) use ($matcher, $authorization, $userToken, $response1, $response2) {
                        $this->assertSame('GET', $request->method()->toString());
                        $this->assertSame($authorization, $request->headers()->get('authorization')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));
                        $this->assertSame($userToken, $request->headers()->get('music-user-token')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));

                        match ($matcher->numberOfInvocations()) {
                            1 => $this->assertSame(
                                'https://api.music.apple.com/v1/me/library/artists?include=catalog',
                                $request->url()->toString(),
                            ),
                            2 => $this->assertSame(
                                'https://api.music.apple.com/v1/me/library/artists?offset=25&include=catalog',
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

                $artists = $library->artists();

                $this->assertInstanceOf(Sequence::class, $artists);
                $artists = $artists->toList();
                $this->assertCount(2, $artists);
                $this->assertSame('r.2S6SRHl', \current($artists)->id()->toString());
                $this->assertSame('"Weird Al" Yankovic', \current($artists)->name()->toString());
                $this->assertSame(1234, \current($artists)->catalog()->match(
                    static fn($id) => $id->toInt(),
                    static fn() => null,
                ));
                \next($artists);
                $this->assertSame('r.o860e82', \current($artists)->id()->toString());
                $this->assertSame('(hed) p.e.', \current($artists)->name()->toString());
                $this->assertNull(\current($artists)->catalog()->match(
                    static fn($id) => $id->toInt(),
                    static fn() => null,
                ));
            });
    }

    public function testAlbums()
    {
        $this
            ->forAll(ArtistSet\Id::any(), StorefrontSet::any())
            ->then(function($artist, $storefront) {
                $library = new Library(
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $userToken = new Header('Music-User-Token', new Value('token')),
                    $storefront,
                );
                $response1 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
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
                    JSON),
                );
                $fulfill
                    ->expects($matcher = $this->exactly(2))
                    ->method('__invoke')
                    ->willReturnCallback(function($request) use ($matcher, $artist, $authorization, $userToken, $response1, $response2) {
                        $this->assertSame('GET', $request->method()->toString());
                        $this->assertSame($authorization, $request->headers()->get('authorization')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));
                        $this->assertSame($userToken, $request->headers()->get('music-user-token')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));

                        match ($matcher->numberOfInvocations()) {
                            1 => $this->assertSame(
                                "https://api.music.apple.com/v1/me/library/artists/{$artist->toString()}/albums?include=artists",
                                $request->url()->toString(),
                            ),
                            2 => $this->assertSame(
                                "https://api.music.apple.com/v1/me/library/artists/{$artist->toString()}/albums?offset=1&include=artists",
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

                $albums = $library->albums($artist);

                $this->assertInstanceOf(Sequence::class, $albums);
                $albums = $albums->toList();
                $this->assertCount(2, $albums);
                $this->assertSame('l.wXEf8fr', \current($albums)->id()->toString());
                $this->assertSame('Skull & Bonus', \current($albums)->name()->toString());
                $this->assertFalse(\current($albums)->artwork()->match(
                    static fn() => true,
                    static fn() => false,
                ));
                $this->assertCount(1, \current($albums)->artists());
                $this->assertSame(
                    'r.o860e82',
                    \current($albums)
                        ->artists()
                        ->find(static fn() => true)
                        ->match(
                            static fn($album) => $album->toString(),
                            static fn() => null,
                        ),
                );
                \next($albums);
                $this->assertSame('l.gACheFi', \current($albums)->id()->toString());
                $this->assertSame(
                    '1200',
                    \current($albums)
                        ->artwork()
                        ->flatMap(static fn($artwork) => $artwork->width())
                        ->match(
                            static fn($width) => $width->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    '1200',
                    \current($albums)
                        ->artwork()
                        ->flatMap(static fn($artwork) => $artwork->height())
                        ->match(
                            static fn($height) => $height->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    'https://is2-ssl.mzstatic.com/image/thumb/Music/c5/98/81/mzi.ljuovcvg.jpg/{w}x{h}bb.jpeg',
                    \current($albums)->artwork()->match(
                        static fn($artwork) => $artwork->url()->toString(),
                        static fn() => null,
                    ),
                );
            });
    }

    public function testSongs()
    {
        $this
            ->forAll(AlbumSet\Id::any(), StorefrontSet::any())
            ->then(function($album, $storefront) {
                $library = new Library(
                    new HttpTransport(
                        $fulfill = $this->createMock(Transport::class),
                    ),
                    $authorization = new Authorization(new AuthorizationValue('Bearer', 'jwt')),
                    $userToken = new Header('Music-User-Token', new Value('token')),
                    $storefront,
                );
                $response1 = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString(<<<JSON
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
                    JSON),
                );
                $fulfill
                    ->expects($matcher = $this->exactly(2))
                    ->method('__invoke')
                    ->willReturnCallback(function($request) use ($matcher, $album, $authorization, $userToken, $response1, $response2) {
                        $this->assertSame('GET', $request->method()->toString());
                        $this->assertSame($authorization, $request->headers()->get('authorization')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));
                        $this->assertSame($userToken, $request->headers()->get('music-user-token')->match(
                            static fn($header) => $header,
                            static fn() => null,
                        ));

                        match ($matcher->numberOfInvocations()) {
                            1 => $this->assertSame(
                                "https://api.music.apple.com/v1/me/library/albums/{$album->toString()}/tracks?include=albums,artists",
                                $request->url()->toString(),
                            ),
                            2 => $this->assertSame(
                                "https://api.music.apple.com/v1/me/library/albums/{$album->toString()}/tracks?offset=1&include=albums,artists",
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

                $songs = $library->songs($album);

                $this->assertInstanceOf(Sequence::class, $songs);
                $songs = $songs->toList();
                $this->assertCount(2, $songs);
                $this->assertSame('i.mmpYYzeu4J0XGD', \current($songs)->id()->toString());
                $this->assertSame('Judgement Day', \current($songs)->name()->toString());
                $this->assertSame('325459', \current($songs)->duration()->match(
                    static fn($duration) => $duration->toString(),
                    static fn() => null,
                ));
                $this->assertSame('1', \current($songs)->trackNumber()->match(
                    static fn($number) => $number->toString(),
                    static fn() => null,
                ));
                $this->assertCount(1, \current($songs)->genres());
                $this->assertSame(
                    'Rapcore, Punk, Rap',
                    \current($songs)
                        ->genres()
                        ->find(static fn() => true)
                        ->match(
                            static fn($genre) => $genre->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertCount(1, \current($songs)->artists());
                $this->assertCount(1, \current($songs)->albums());
                $this->assertSame(
                    'r.o860e82',
                    \current($songs)
                        ->artists()
                        ->find(static fn() => true)
                        ->match(
                            static fn($artist) => $artist->toString(),
                            static fn() => null,
                        ),
                );
                $this->assertSame(
                    'l.wXEf8fr',
                    \current($songs)
                        ->albums()
                        ->find(static fn() => true)
                        ->match(
                            static fn($album) => $album->toString(),
                            static fn() => null,
                        ),
                );
                \next($songs);
                $this->assertSame('i.b1Jvv08sqZgz8A', \current($songs)->id()->toString());
                $this->assertSame('Takeover (feat. Axe Murder Boyz & DGAF)', \current($songs)->name()->toString());
            });
    }
}
