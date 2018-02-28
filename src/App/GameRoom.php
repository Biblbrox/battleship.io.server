<?php

namespace Battleship\App;

/**
 * Class GameRoom
 * @package Battleship\App
 */
class GameRoom
{
    /**
     * User 2.
     * @var Player $user1
     */
    public $user1;

    /**
     * User 1.
     * @var Player $user2
     */
    public $user2;

    /**
     * @var array $users
     */
    public $users = [];

    /**
     * The user who created this room.
     * @var Player $createdBy
     */
    public $createdBy;

    /**
     * The user who is walking now.
     * @var Player $walkingUser
     */
    public $walkingUser;

    /**
     * Will emit when room will be full.
     * @var callable $onFull
     */
    public $onFull;

    /**
     * GameRoom constructor.
     * @param Player $creator
     */
    public function __construct(Player $creator)
    {
        $this->createdBy = $creator;
        $this->user1     = $creator;
        $this->user2     = null;
        $this->onFull    = function() {};
        $this->users[]   = $creator;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function containsUser($userId)
    {
        return (isset($this->user1) && $this->user1->id === $userId)
            || (isset($this->user2) && $this->user2->id === $userId);
    }

    /**
     * @return bool
     */
    public function isFull()
    {
        return (isset($this->user1) && isset($this->user2));
    }

    /**
     * Add user to the room.
     * If room already is full then nothing.
     * @param Player $user
     */
    public function addUser(Player $user)
    {
        if (!$this->isFull()) {
            $this->user2 = $user;
            $this->walkingUser = $this->user1;

            $this->user1->enemy = $this->user2;
            $this->user2->enemy = $this->user1;

            $this->users[] = $user;

            if (!is_callable($this->onFull)) {
                throw new \Exception('$onFull field must be callable');
            }

            ($this->onFull)();
        }
    }
}