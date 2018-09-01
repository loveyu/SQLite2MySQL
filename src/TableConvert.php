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
        $this->mysqlMedoo->pdo->beginTransaction();
        foreach($tables as $table) {
            echo "Create Table .....[{$table}]...", PHP_EOL;
            $sql = $this->getMigrateSqlFromTable($table);
            $this->mysqlMedoo->exec($sql);

            echo "Transfer Data ....[{$table}]...", PHP_EOL;
            $this->dataTransfer($table);
        }
        $this->mysqlMedoo->pdo->commit();
    }

    private function dataTransfer(string $table)
    {
        $stmt = $this->sqliteMedoo->query("select * from {$table}");
        $loop = true;
        $insert_list = [];
        $i = 0;
        while($loop) {
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($data === false) {
                break;
            }
            $insert_list[] = $data;
            $i++;
            if($i === 1000) {
                $this->mysqlMedoo->insert($table, $insert_list);
                $i = 0;
                $insert_list = [];
            }
        }
        if(!empty($insert_list)) {
            $this->mysqlMedoo->insert($table, $insert_list);
        }
    }

    private function getMigrateSqlFromTable(string $table)
    {
        $stmt = $this->sqliteMedoo->query("PRAGMA table_info(\"{$table}\")");
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $sql = "DROP TABLE IF EXISTS `{$table}`;\n";
        $sql .= "CREATE TABLE IF NOT EXISTS `{$table}`(\n";
        $fields = [];
        $keys = [];
        $generateType = function($item) use ($table) {
            $type = "text";
            $input_type = $item['type'];
            $input_size = 0;
            if(preg_match("/^varchar\\(([\d]+)\\)$/i", $input_type, $matches) == 1) {
                $input_type = "varchar";
                $input_size = (int)$matches[1];
            }
            switch(strtolower($input_type)) {
                case "text":
                    $type = "text";
                    if(!empty($item['pk']) && $item['pk'] == 1) {
                        $type = "varchar(768)";
                    }
                    break;
                case "varchar":
                    $type = "varchar({$input_size})";
                    break;
                case "integer":
                case "int":
                    $type = "int(11)";
                    break;
                case "short":
                    $type = "int(11)";
                    break;
                case "long":
                    $type = "bigint(20)";
                    break;
                case "double":
                    $type = "double";
                    break;
                case "byte[]":
                case "blob":
                case "blog":
                    $type = "blob";
                    break;
                default:
                    print_r($table);
                    print_r($item);
                    exit;
            }
            if(!empty($item['notnull']) && $item['notnull'] == 1) {
                $type .= " NOT NULL";
            }
            if($item['dflt_value'] !== null && $item['dflt_value'] !== "") {
                $type .= " DEFAULT '{$item['dflt_value']}'";
            }
            return $type;
        };
        foreach($list as $item) {
            if($item['pk'] == "1") {
                $keys[] = " PRIMARY KEY ( `{$item['name']}` )";
            }
            $type = $generateType($item);
            $fields[] = "`{$item['name']}` {$type}";
        }
        $sql .= implode(",\n", array_merge($fields, $keys))."\n);";
        return $sql;
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