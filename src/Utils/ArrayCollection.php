<?php

namespace Battleship\Utils;

use Battleship\App\Cell;

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
    public function where($params = [])
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
                    $result->push($item);
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
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @param $item
     * @param null $key
     * @return Cell
     */
    public function push($item, $key = null)
    {
        if (isset($key)) {
            $this->data[$key] = $item;
        } else {
            $this->data[] = $item;
        }

        return $item;
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        $this->data = array_filter($this->data, function ($item) {
            return isset($item);
        });
    }

    /**
     * @return bool
     */
    public function isEmpty()
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

}