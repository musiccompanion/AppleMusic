<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\LazySet;
use Innmind\Immutable\{
    SetInterface,
    Set,
};
use PHPUnit\Framework\TestCase;

class LazySetTest extends TestCase
{
    private $set;

    public function setUp(): void
    {
        $this->set = LazySet::of('int', function() {
            yield 1;
            yield 2;
            yield 3;
            yield 4;
        });
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            SetInterface::class,
            $this->set
        );
        $this->assertSame('int', (string) $this->set->type());
    }

    public function testIterator()
    {
        $this->assertSame(
            [1, 2, 3, 4],
            \iterator_to_array($this->set)
        );
        $this->assertSame(
            [1, 2, 3, 4],
            $this->set->toPrimitive()
        );
    }

    public function testSize()
    {
        $this->assertSame(4, $this->set->size());
        $this->assertSame(4, $this->set->count());
    }

    public function testIntersect()
    {
        $this->assertSame(
            [3, 4],
            $this->set->intersect(Set::of('int', 3, 4, 5, 6))->toPrimitive()
        );
    }

    public function testAdd()
    {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            $this->set->add(5)->toPrimitive()
        );
    }

    public function testContains()
    {
        $this->assertTrue($this->set->contains(3));
        $this->assertFalse($this->set->contains(5));
    }

    public function testRemove()
    {
        $this->assertSame(
            [1, 2, 4],
            $this->set->remove(3)->toPrimitive()
        );
    }

    public function testDiff()
    {
        $this->assertSame(
            [1, 2, 3],
            $this->set->diff(Set::of('int', 4, 5, 6))->toPrimitive()
        );
    }

    public function testEquals()
    {
        $this->assertTrue($this->set->equals(Set::of('int', 1, 2, 3, 4)));
        $this->assertFalse($this->set->equals(Set::of('int', 1)));
    }

    public function testFilter()
    {
        $this->assertSame(
            [2, 4],
            $this
                ->set
                ->filter(static function($i): bool {
                    return $i % 2 === 0;
                })
                ->toPrimitive()
        );
    }

    public function testForeach()
    {
        $total = 0;

        $this->assertSame(
            $this->set,
            $this->set->foreach(function($i) use (&$total) {
                $total += $i;
            })
        );
        $this->assertSame(10, $total);
    }

    public function testGroupBy()
    {
        $groups = $this->set->groupBy(static function($i) {
            return $i % 2;
        });

        $this->assertSame([1, 3], $groups->get(1)->toPrimitive());
        $this->assertSame([2, 4], $groups->get(0)->toPrimitive());
    }

    public function testMap()
    {
        $this->assertSame(
            [2, 4, 6, 8],
            $this
                ->set
                ->map(static function($i) {
                    return $i * 2;
                })
                ->toPrimitive()
        );
    }

    public function testPartition()
    {
        $groups = $this->set->partition(static function($i) {
            return $i % 2 === 0;
        });

        $this->assertSame([1, 3], $groups->get(false)->toPrimitive());
        $this->assertSame([2, 4], $groups->get(true)->toPrimitive());
    }

    public function testJoin()
    {
        $this->assertSame('1|2|3|4', (string) $this->set->join('|'));
    }

    public function testSort()
    {
        $this->assertSame(
            [4, 3, 2, 1],
            $this
                ->set
                ->sort(static function($a, $b) {
                    return $b > $a;
                })
                ->toPrimitive()
        );
    }

    public function testMerge()
    {
        $this->assertSame(
            [1, 2, 3, 4, 5, 6],
            $this->set->merge(Set::of('int', 4, 5, 6))->toPrimitive()
        );
    }

    public function testReduce()
    {
        $this->assertSame(
            10,
            $this->set->reduce(
                0,
                static function($total, $i) {
                    return $total + $i;
                }
            )
        );
    }

    public function testClear()
    {
        $this->assertTrue($this->set->clear()->equals(Set::of('int')));
    }

    public function testEmpty()
    {
        $this->assertFalse($this->set->empty());
    }
}
