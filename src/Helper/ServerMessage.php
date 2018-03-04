<?php


namespace Battleship\Helper;

/**
 * *********************************
 * Messages of the attacker:
 * {
 *     msg: "hit",
 *     row: ...,
 *     column: ....,
 *     userId: ....
 * },
 * *********************************
 * Messages to the attacker:
 * {
 *     msg: "win"
 * },
 * {
 *     msg: "enemyInjured",
 *     row: ....,
 *     column: ....
 * },
 * {
 *     msg: "youMissed",
 *     row: ....,
 *     column: ....
 * }
 * *********************************
 * Messages to the attacked:
 * {
 *     msg: "lost"
 * },
 * {
 *     msg: "youInjured",
 *     row: ....,
 *     column: ....
 * },
 * {
 *     msg: "enemyMissed",
 *     row: ....,
 *     column: ....
 * }
 * *********************************
 * Messages of the connecting player:
 * {
 *     msg: "findRoom"
 * }
 * *********************************
 * Messages to the connecting player:
 * {
 *     msg: "onConnection", // Sending when user connecting to the server.
 *     board: ....,
 *     id: ....
 * },
 * {
 *     msg: "enemyFound",
 *     enemyId: ....,
 *     walkingUserId: ....  // Walking user ID.
 * }
 * *********************************
 **/

/**
 * Class ServerMessage
 * @package Battleship\Helper
 */
class ServerMessage
{
    const HIT = "hit";
    const WIN = "win";
    const ENEMY_INJURED = "enemyInjured";
    const YOU_MISSED = "youMissed";
    const LOST = "lost";
    const YOU_INJURED = "youInjured";
    const ENEMY_MISSED = "enemyMissed";
    const FIND_ROOM = "findRoom";
    const ON_CONNECTION = "onConnection";
    const ENEMY_FOUND = "enemyFound";
    const YOU_FALL = "youFall";
    const ENEMY_FALL = "enemyFall";
    const ENEMY_DISCONNECT = "enemyDisconnect";
}