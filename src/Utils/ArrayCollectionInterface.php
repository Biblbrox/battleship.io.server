<?php

namespace Battleship\Utils;

/**
 * Interface ArrayCollectionInterface
 * @package Battleship\Utils
 */
interface ArrayCollectionInterface extends \Iterator, \ArrayAccess
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
     * @param callable $callback
     */
    public function forEach(callable $callback) : void; // TODO: fix function

    /**
     * @param array $params
     * @return ArrayCollection
     */
    public function where($params = []);
}