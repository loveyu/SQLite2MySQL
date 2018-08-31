<?php
/**
 * User: loveyu
 * Date: 2018/8/31
 * Time: 2:14
 */

declare(strict_types=1);

namespace Loveyu\SQLite2MySQL;

use Medoo\Medoo;


class Convert
{
    public static function error(string $msg)
    {
        fwrite(STDOUT, $msg.PHP_EOL);
    }

    public static function log(string $msg)
    {
        echo $msg, PHP_EOL;
    }

    public static function stdConvert(string $sqlite, string $mysql): int
    {
        if(!is_file($sqlite) || !is_readable($sqlite)) {
            self::error("Can not read sqlite file.");
            return 1;
        }
        $sqlite_pdo = new Medoo([
            'database_type' => 'sqlite',
            'database_file' => $sqlite
        ]);
        $mysql_cfg = self::parseMySQL($mysql);

        $mysql_pdo = new Medoo($mysql_cfg);

        $migrate = new TableConvert($sqlite_pdo, $mysql_pdo);
        try {
            $migrate->migrate();
        } catch(\Exception $exception) {
            self::error($exception->getMessage());
            self::error($exception->getTraceAsString());

            return 2;
        }
        return 0;
    }

    private static function parseMySQL(string $mysql): array
    {
        $parseUrl = parse_url($mysql);
        if(empty($parseUrl['path'])){
            throw new \RuntimeException("Error DB Name");
        }
        return [
            'database_type' => isset($parseUrl['scheme'])?$parseUrl['scheme']:"mysql",
            'database_name' => trim($parseUrl['path'], "/"),
            'server'        => isset($parseUrl['host']) ? $parseUrl['host'] : 'localhost',
            'username'      => isset($parseUrl['user']) ? $parseUrl['user'] : 'root',
            'password'      => isset($parseUrl['pass']) ? $parseUrl['pass'] : '',
            'charset'       => 'utf8mb4',
            'port'          => isset($parseUrl['port']) ? (int)$parseUrl['port'] : 3306,
        ];
    }
}