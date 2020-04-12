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
        $chars = Set\Regex::for('^[a-zA-Z0-9]+$');

        $this
            ->forAll($chars)
            ->then(function(string $chars) {
                $string = 'i.'.$chars;
                $id = new Id($string);

                $this->assertSame($string, $id->toString());
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
