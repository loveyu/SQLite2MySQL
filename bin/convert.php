<?php
/**
 * User: loveyu
 * Date: 2018/8/31
 * Time: 2:10
 */

declare(strict_types=1);

use CliArgs\CliArgs;
use Loveyu\SQLite2MySQL\Convert;


require_once __DIR__."/../vendor/autoload.php";

$CliArgs = new CliArgs([
    "sqlite" => 's',
    "mysql"  => "m"
]);

$sqlite = $CliArgs->getArg("s");
$mysql = $CliArgs->getArg("m");

return Convert::stdConvert($sqlite,$mysql);