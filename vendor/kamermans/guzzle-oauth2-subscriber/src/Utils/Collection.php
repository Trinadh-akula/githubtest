<?php

namespace kamermans\OAuth2\Utils;

use ArrayAccess;
use Traversable;
use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * Key value pair collection object
 */
class Collection implements
    ArrayAccess,
    IteratorAggregate,
    Countable
{

    /** @var array */
    protected $data = [];

    /**
     * @param array $data Associative array of data to set
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function offsetGet($offset): ?string
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Create a new collection from an array, validate the keys, and add default
     * values where missing
     *
     * @param array $config   Configuration values to apply.
     * @param array $defaults Default parameters
     * @param array $required Required parameter names
     *
     * @return self
     * @throws \InvalidArgumentException if a parameter is missing
     */
    public static function fromConfig(
        array $config = [],
        array $defaults = [],
        array $required = []
    ): Collection
    {
        $data = $config + $defaults;

        if ($missing = array_diff($required, array_keys($data))) {
            throw new \InvalidArgumentException(
                'Config is missing the following keys: ' .
                implode(', ', $missing)
            );
        }

        return new self($data);
    }

    /**
     * Removes all key value pairs
     *
     * @return Collection
     */
    public function clear(): Collection
    {
        $this->data = [];

        return $this;
    }

    /**
     * Get a specific key value.
     *
     * @param string $key Key to retrieve.
     *
     * @return mixed|null Value of the key or NULL
     */
    public function get(string $key): string
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set a key value pair
     *
     * @param string $key   Key to set
     * @param string  $value Value to set
     *
     * @return Collection Returns a reference to the object
     */
    public function set(string $key, string $value): Collection
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Add a value to a key.  If a key of the same name has already been added,
     * the key value will be converted into an array and the new value will be
     * pushed to the end of the array.
     *
     * @param string $key   Key to add
     * @param mixed  $value Value to add to the key
     *
     * @return Collection Returns a reference to the object.
     */
    public function add($key, $value): Collection
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        } elseif (is_array($this->data[$key])) {
            $this->data[$key][] = $value;
        } else {
            $this->data[$key] = [$this->data[$key], $value];
        }

        return $this;
    }

    /**
     * Remove a specific key value pair
     *
     * @param string $key A key to remove
     *
     * @return Collection
     */
    public function remove(string $key): Collection
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Get all keys in the collection
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Returns whether or not the specified key is present.
     *
     * @param string $key The key for which to check the existence.
     *
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Checks if any keys contains a certain value
     *
     * @param string $value Value to search for
     *
     * @return mixed Returns the key if the value was found or NULL if the value
     *     was not found.
     */
    public function hasValue(string $value): ?string
    {
        $val = array_search($value, $this->data, true);
        return $val === false ? null : $val;
    }

    /**
     * Replace the data of the object with the value of an array
     *
     * @param array $data Associative array of data
     *
     * @return Collection Returns a reference to the object
     */
    public function replace(array $data): Collection
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Add and merge in a Collection or array of key value pair data.
     *
     * @param Collection|array $data Associative array of key value pair data
     *
     * @return Collection Returns a reference to the object.
     */
    public function merge(Traversable $data): Collection
    {
        foreach ($data as $key => $value) {
            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * Over write key value pairs in this collection with all of the data from
     * an array or collection.
     *
     * @param array|\Traversable $data Values to override over this config
     *
     * @return self
     */
    public function overwriteWith(Traversable $data): Collection
    {
        if (is_array($data)) {
            $this->data = $data + $this->data;
        } elseif ($data instanceof Collection) {
            $this->data = $data->toArray() + $this->data;
        } else {
            foreach ($data as $key => $value) {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns a Collection containing all the elements of the collection after
     * applying the callback function to each one.
     *
     * The callable should accept three arguments:
     * - (string) $key
     * - (string) $value
     * - (array) $context
     *
     * The callable must return a the altered or unaltered value.
     *
     * @param callable $closure Map function to apply
     * @param array    $context Context to pass to the callable
     *
     * @return Collection
     */
    public function map(callable $closure, array $context = []): Collection
    {
        $collection = new static();
        foreach ($this as $key => $value) {
            $collection[$key] = $closure($key, $value, $context);
        }

        return $collection;
    }

    /**
     * Iterates over each key value pair in the collection passing them to the
     * callable. If the callable returns true, the current value from input is
     * returned into the result Collection.
     *
     * The callable must accept two arguments:
     * - (string) $key
     * - (string) $value
     *
     * @param callable $closure Evaluation function
     *
     * @return Collection
     */
    public function filter(callable $closure): Collection
    {
        $collection = new static();
        foreach ($this->data as $key => $value) {
            if ($closure($key, $value)) {
                $collection[$key] = $value;
            }
        }

        return $collection;
    }
}
