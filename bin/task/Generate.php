<?php

namespace NogalSE\Task;

use NogalSE\intf\ITask;

class Generate implements ITask
{

    private $main_value;
    private $params;
    private $dataSource;
    private $output;
    private $app;

    public function __construct($value, $params)
    {
        $this->main_value = $value;
        $this->params = $params;
        $this->dataSource = 'DataBase';
        $data = explode(':', $params[2], 2);
        $this->output = ($data[0] === 'output') ? $data[1] : false;
        $data = explode(':', $params[3], 2);
        $this->app = ($data[0] === 'app') ? $data[1] : false;
    }

    public function main()
    {
        if (is_dir($this->output) === false) {
            throw new \Exception('El directorio otorgado ("' . $this->output . '") no existe o no es un directorio.');
        } else {
            $yaml = yaml_parse_file($this->main_value);
            $this->generateExtendsDataSource();
            $yaml = $this->createArrayConfig($yaml);
            foreach ($yaml as $table => $columns) {
                $this->createTable($table, $columns);
            }
        }
    }

    private function generateExtendsDataSource()
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Generate' . DIRECTORY_SEPARATOR . 'ExtendsDataSource' . '.php';
        $class = new Generate\ExtendsDataSource();
        $class->main($this->dataSource, $this->output, $this->app);
    }

    private function createArrayConfig($yaml)
    {
        $arrayTemp = array();
        foreach ($yaml as $table => $columns) {
            if (!preg_match("/^(~)([a-zA-Z0-9\w]+)/", $table)) {
                foreach ($columns as $column => $content) {
                    if ($column === '~behavior_log') {
                        $yaml[$table] = array_merge($yaml[$table], $yaml['~behavior_log']);
                        unset($yaml[$table][$column]);
                    } elseif ($column !== '_sequence' and is_string($content) === true and preg_match("/^(~)([a-zA-Z0-9\w]+)/", $content)) {
                        $yaml[$table][$column] = $yaml[$content];
                        $arrayTemp[] = $content;
                    }
                }
            }
        }
        foreach ($arrayTemp as $columnTemplate) {
            unset($yaml[$columnTemplate]);
        }
        unset($yaml['~behavior_log']);
        return $yaml;
    }

    private function createTable($table, $columns)
    {
        $app = $this->app;
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Generate' . DIRECTORY_SEPARATOR . 'Table.php';
        $gTable = new Generate\Table();
        $gTable->main($table, $columns);

        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Generate' . DIRECTORY_SEPARATOR . 'CRUD.php';
        $gCrud = new Generate\CRUD($this->app);
        $gCrud->main($table, $columns);

        $DataBase = $this->dataSource;
        $Table = str_replace(' ', '', ucfirst(str_replace('_', ' ', $table)));
        $sequence = $gTable->getSequence();
        $length = $gTable->getLength();
        $private = $gTable->getPrivate();
        $default = $gTable->getDefault();
        $getter = $gTable->getGetter();
        $setter = $gTable->getSetter();
        $selectAll = $gCrud->getSelectAll();
        $selectById = $gCrud->getSelectById();
        $save = $gCrud->getSave();
        $update = $gCrud->getUpdate();
        $delete = $gCrud->getDelete();
        include __DIR__ . DIRECTORY_SEPARATOR . 'Generate' . DIRECTORY_SEPARATOR . 'skeleton' . DIRECTORY_SEPARATOR . 'TableBase.php';
        if (is_dir($this->output . 'base/') === false) {
            mkdir($this->output . 'base/');
        }
        $file = fopen($this->output . 'base/' . $Table . '.php', 'w');
        fwrite($file, $skeleton);
        fclose($file);
        $TableBase = $Table;
        $Table = str_replace('Base', '', $Table);
        include __DIR__ . DIRECTORY_SEPARATOR . 'Generate' . DIRECTORY_SEPARATOR . 'skeleton' . DIRECTORY_SEPARATOR . 'Table.php';
        if (is_file($this->output . $Table . '.php') === false) {
            $file = fopen($this->output . $Table . '.php', 'w');
            fwrite($file, $skeleton);
            fclose($file);
        }
    }

}
