<?php

namespace Battleship\Utils;

interface ArrayCollectionInterface extends \Iterator
{
    public function get($key);

    public function push($item, $key = null);

    public function first();

    public function remove($key);

    public function isEmpty();

    public function where($params = []);
}