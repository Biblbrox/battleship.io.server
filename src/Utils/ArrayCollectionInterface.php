<?php
declare(strict_types=1);
namespace Battleship\Utils;

/**
 * Interface ArrayCollectionInterface
 * @package Battleship\Utils
 */
interface ArrayCollectionInterface extends \Iterator, \ArrayAccess, \Countable
{
    /**
     * @return mixed|null
     */
    public function first();

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * This function call $callback on every $element of collection
     * The callable func must take two arguments: $item, $key
     * @param callable $callback
     */
    public function forEach(callable $callback) : void;

    /**
     * @param array $params
     * @return ArrayCollection
     */
    public function where($params = []);
}