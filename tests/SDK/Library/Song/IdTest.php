<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\{
    SDK\Library\Song\Id,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class IdTest extends TestCase
{
    use BlackBox;

    public function testStringsOfSpecifiedFormatAreAccepted()
    {
        $char = Set\Chars::of()->filter(static function(string $char): bool {
            return (bool) \preg_match('~^[a-zA-Z0-9]$~', $char);
        });

        $this
            ->forAll(...\array_fill(0, 14, $char))
            ->take(1000)
            ->then(function(string ...$chars) {
                $string = 'i.'.implode('', $chars);
                $id = new Id($string);

                $this->assertSame($string, (string) $id);
            });
    }

    public function testAnyRandomStringIsRejected()
    {
        $this
            ->forAll(new Set\Strings)
            ->then(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Id($string);
            });
    }
}
