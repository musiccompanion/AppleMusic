<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    StreamInterface,
    Set,
    Str,
};

/**
 * @template T
 */
final class LazySet implements SetInterface
{
    private Str $type;
    private \Closure $factory;
    private \Generator $active;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $type)
    {
        $this->type = Str::of($type);
        $this->factory = static function() {
            if (false) {
                yield;
            }
        };
        $this->active = ($this->factory)();
    }

    /**
     * @param string $type Type T
     * @param callable(): \Generator $factory
     */
    public static function of(string $type, callable $factory): self
    {
        $active = $factory();

        if (!$active instanceof \Generator) {
            throw new \TypeError('Argument 2 must be of type callable(): \Generator');
        }

        $self = new self($type);
        $self->factory = $factory;
        $self->active = $active;

        return $self;
    }

    /**
     * @return T
     */
    public function current()
    {
        return $this->active->current();
    }

    public function key(): int
    {
        return $this->active->key();
    }

    public function next(): void
    {
        $this->active->next();
    }

    public function rewind(): void
    {
        $this->active = ($this->factory)();
    }

    public function valid(): bool
    {
        return $this->active->valid();
    }

    public function size(): int
    {
        $size = 0;

        foreach ($this as $_) {
            ++$size;
        }

        return $size;
    }

    public function count(): int
    {
        return $this->size();
    }

    public function toPrimitive(): array
    {
        return \iterator_to_array($this);
    }

    /**
     * {@inheritdoc}
     */
    public function type(): Str
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function intersect(SetInterface $set): SetInterface
    {
        return $this->load()->intersect($set);
    }

    /**
     * {@inheritdoc}
     */
    public function add($element): SetInterface
    {
        return $this->load()->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element): bool
    {
        foreach ($this as $_ => $value) {
            if ($value === $element) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($element): SetInterface
    {
        return $this->load()->remove($element);
    }

    /**
     * {@inheritdoc}
     */
    public function diff(SetInterface $set): SetInterface
    {
        return $this->load()->diff($set);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SetInterface $set): bool
    {
        return $this->load()->equals($set);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): SetInterface
    {
        $set = $this->clear();

        foreach ($this as $_ => $value) {
            if ($predicate($value)) {
                $set = $set->add($value);
            }
        }

        return $set;
    }

    /**
     * {@inheritdoc}
     */
    public function foreach(callable $function): SetInterface
    {
        foreach ($this as $_ => $value) {
            $function($value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy(callable $discriminator): MapInterface
    {
        return $this->load()->groupBy($discriminator);
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $function): SetInterface
    {
        return $this->load()->map($function);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(callable $predicate): MapInterface
    {
        return $this->load()->partition($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function join(string $separator): Str
    {
        return $this->load()->join($separator);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $function): StreamInterface
    {
        return $this->load()->sort($function);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(SetInterface $set): SetInterface
    {
        return $this->load()->merge($set);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce($carry, callable $reducer)
    {
        foreach ($this as $_ => $value) {
            $carry = $reducer($carry, $value);
        }

        return $carry;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): SetInterface
    {
        return Set::of((string) $this->type);
    }

    public function empty(): bool
    {
        return $this->size() === 0;
    }

    /**
     * @return SetInterface<T>
     */
    private function load(): SetInterface
    {
        return Set::of((string) $this->type, ...$this);
    }
}
