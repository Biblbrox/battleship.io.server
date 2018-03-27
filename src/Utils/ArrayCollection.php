<?php

namespace Battleship\Utils;


/**
 * Class ArrayCollection
 * @package Battleship\Utils
 */
class ArrayCollection implements ArrayCollectionInterface
{

    /**
     * @var $data
     */
    protected $data = [];

    /**
     * @param array $params
     * @return ArrayCollection
     */
    public function where($params = []) : ArrayCollection
    {
        $result = new ArrayCollection();

        if (empty($params)) {
            return $this;
        }

        foreach ($this->data as $item) {

            $add = true;
            foreach ($params as $key => $value) {
                if (!isset($item->{$key}) || $item->{$key} != $value) {
                    $add = false;
                }

                if ($add) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * @return mixed|null
     */
    public function first()
    {
        return isset($this->data[0]) ? $this->data[0] : null;
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->data);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return null !== key($this->data);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param callable $callback
     */
    public function forEach(callable $callback) : void
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Argument of forEach must be callable");
        }

        $refl = new \ReflectionFunction($callback);
        $argc = $refl->getNumberOfParameters();

        if ($argc < 1 || $argc > 2) {
            throw new
            \InvalidArgumentException("Callable must have one or two arguments: item and/or key of collection");
        }

        foreach ($this->data as $key => $item) {
            $argc === 2 ? $callback($item, $key) : $callback($item);
        }
    }
}