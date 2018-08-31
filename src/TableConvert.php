<?php
/**
 * User: loveyu
 * Date: 2018/9/1
 * Time: 2:32
 */
declare(strict_types=1);

namespace Loveyu\SQLite2MySQL;

use Medoo\Medoo;

class TableConvert
{
    /**
     * @var Medoo
     */
    private $sqliteMedoo;
    /**
     * @var Medoo
     */
    private $mysqlMedoo;

    /**
     * TableConvert constructor.
     * @param Medoo $sqliteMedoo
     * @param Medoo $mysqlMedoo
     */
    public function __construct(Medoo $sqliteMedoo, Medoo $mysqlMedoo)
    {
        $this->sqliteMedoo = $sqliteMedoo;
        $this->mysqlMedoo = $mysqlMedoo;
    }

    public function migrate()
    {
        $tables = $this->getTables();
        foreach($tables as $table) {
            echo $table, PHP_EOL;
        }
    }

    private function getTables()
    {
        $list = $this->sqliteMedoo->select("sqlite_master", ["name"], [
            "type"  => "table",
            "ORDER" => ["name" => "ASC"]
        ]);
        $tables = array_column($list, "name");
        return (array)$tables;
    }
}