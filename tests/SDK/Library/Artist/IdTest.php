<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Artist;

use MusicCompanion\AppleMusic\{
    SDK\Library\Artist\Id,
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
        $chars = Set\Regex::for('^[a-zA-Z0-9]{7}$');

        $this
            ->forAll($chars)
            ->then(function(string $chars) {
                $string = 'r.'.$chars;
                $id = new Id($string);

                $this->assertSame($string, (string) $id);
            });
    }

    public function testAnyRandomStringIsRejected()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function(string $string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                new Id($string);
            });
    }
}
