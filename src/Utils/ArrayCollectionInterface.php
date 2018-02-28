<?php

namespace Battleship\Utils;

use Battleship\App\Cell;

/**
 * Interface ArrayCollectionInterface
 * @package Battleship\Utils
 */
interface ArrayCollectionInterface extends \Iterator
{
    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key);

    /**
     * @param $item
     * @param null $key
     * @return Cell
     */
    public function push($item, $key = null);

    /**
     * @return mixed|null
     */
    public function first();

    /**
     * @param $key
     */
    public function remove($key);

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @param array $params
     * @return ArrayCollection
     */
    public function where($params = []);
}