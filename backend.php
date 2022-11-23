<?php declare(strict_types=1);
/**
 * Hybula Looking Glass
 *
 * Does the actual backend work for executed commands.
 *
 * @copyright 2022 Hybula B.V.
 * @license Mozilla Public License 2.0
 * @version 0.1
 * @since File available since release 0.1
 * @link https://github.com/hybula/lookingglass
 */

require __DIR__.'/LookingGlass.php';
require __DIR__.'/config.php';

use Hybula\LookingGlass;

LookingGlass::validateConfig();
LookingGlass::startSession();

if (isset($_SESSION[LookingGlass::SESSION_TARGET_HOST]) &&
    isset($_SESSION[LookingGlass::SESSION_TARGET_METHOD]) &&
    isset($_SESSION[LookingGlass::SESSION_CALL_BACKEND])
) {
    unset($_SESSION[LookingGlass::SESSION_CALL_BACKEND]);


    switch ($_SESSION[LookingGlass::SESSION_TARGET_METHOD]) {
        case LookingGlass::METHOD_PING:
            LookingGlass::ping($_SESSION[LookingGlass::SESSION_TARGET_HOST]);
            break;
        case LookingGlass::METHOD_PING6:
            LookingGlass::ping6($_SESSION[LookingGlass::SESSION_TARGET_HOST]);
            break;
        case LookingGlass::METHOD_MTR:
            LookingGlass::mtr($_SESSION[LookingGlass::SESSION_TARGET_HOST]);
            break;
        case LookingGlass::METHOD_MTR6:
            LookingGlass::mtr6($_SESSION[LookingGlass::SESSION_TARGET_HOST]);
            break;
        case LookingGlass::METHOD_TRACEROUTE:
            LookingGlass::traceroute($_SESSION[LookingGlass::SESSION_TARGET_HOST]);
            break;
        case LookingGlass::METHOD_TRACEROUTE6:
            LookingGlass::traceroute6($_SESSION[LookingGlass::SESSION_TARGET_HOST]);
            break;
    }
}
