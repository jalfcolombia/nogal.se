<?php

namespace NogalSE\Task\Generate;

class CRUD
{

    private $selectAll;

    private $selectById;

    private $save;

    private $update;

    private $delete;

    private $app;

    public function __construct($app)
    {
        $this->selectAll = '';
        $this->selectById = '';
        $this->save = '';
        $this->update = '';
        $this->delete = '';
        $this->app = $app;
    }

    public function getSelectAll()
    {
        return $this->selectAll;
    }

    public function getSelectById()
    {
        return $this->selectById;
    }

    public function getSave()
    {
        return $this->save;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function getDelete()
    {
        return $this->delete;
    }

    public function setSelectAll($selectAll)
    {
        $this->selectAll = $selectAll;
    }

    public function setSelectById($selectById)
    {
        $this->selectById = $selectById;
    }

    public function setSave($save)
    {
        $this->save = $save;
    }

    public function setUpdate($update)
    {
        $this->update = $update;
    }

    public function setDelete($delete)
    {
        $this->delete = $delete;
    }

    public function main($table, $columns)
    {
        unset($columns['_sequence']);
        $TableName = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
        $primary_key = array();
        $idsOrderBy = '';
        $select = '';
        $deleted = false;
        $paramsComment = '';
        $ids = '';
        $paramsSelectById = '';
        $setDbParamID = '';
        $saveParams = '';
        $saveValues = '';
        $setDbParamColumns = '';
        $setUpdate = '';
        $setDbParamIDUpdate = '';
        $setDbParamColumnsUpdate = '';
        $deleted_at = false;
        $deleted_at_bind = null;
        
        foreach ($columns as $column => $attribute) {
            // array para llaves primarias
            if (isset($attribute['primary_key']) === true and $attribute['primary_key'] === true) {
                $primary_key[] = array(
                    'column' => $column,
                    'type' => $attribute['type'],
                    'name' => ((isset($attribute['name']) === true) ? $attribute['name'] : $column)
                );
            } else if (isset($attribute['primary_key']) === false and $column !== 'created' and $column !== 'deleted' and (isset($attribute['updated']) === false)) {
                
                $columnTempRaw = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                $columnTempName = (isset($attribute['name']) === true) ? $attribute['name'] : $column;
                
                $setUpdate .= $columnTempRaw . " = :" . $columnTempName . ", ";
                if ($column === 'updated' and isset($attribute['default']) and ! preg_match("/^(__)([a-zA-Z0-9\w]+)/", $attribute['default'])) {
                    $setUpdate = str_replace(":$columnTempName", $attribute['default'], $setUpdate);
                } else if ($column === 'updated' and isset($attribute['default']) and preg_match("/^(__)([a-zA-Z0-9\w]+)/", $attribute['default'])) {
                    $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $columnTempName)));
                    $setDbParamColumns .= "\n      \$this->setDbParam(':$columnTempName', \$this->get$columCamelCase(), \\PDO::PARAM_STR);";
                }
            }
            
            // select para el selectAll y selectById
            if ($column !== 'deleted') {
                
                $columnTempRaw = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                $columnTempName = (isset($attribute['name']) === true) ? $attribute['name'] : $column;
                
                $select .= $columnTempRaw . ', ';
                // Columnas sin AUTO INCREMENTO
                if (isset($attribute['auto_increment']) === false and $column !== 'updated') {
                    // $columnTemp = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                    $saveParams .= $columnTempRaw . ", ";
                    $saveValues .= ":" . $columnTempName . ", ";
                    
                    if (isset($attribute['type']) === true and isset($attribute['primary_key']) === false) {
                        $typeBind = '';
                        switch ($attribute['type']) {
                            case 'int':
                                $typeBind = "\\PDO::PARAM_INT";
                                break;
                            case 'string':
                                $typeBind = "\\PDO::PARAM_STR";
                                break;
                            case 'file':
                                $typeBind = "\\PDO::PARAM_STR";
                                break;
                            case 'bool':
                                $typeBind = "\\PDO::PARAM_BOOL";
                                break;
                        }
                        // $columnTemp = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                        $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $columnTempName)));
                        $setDbParamColumns .= "\n      \$this->setDbParam(':$columnTempName', \$this->get$columCamelCase(), $typeBind);";
                        $setDbParamColumnsUpdate .= "\n      \$this->setDbParam(':$columnTempName', \$this->get$columCamelCase(), $typeBind);";
                        if (isset($attribute['updated']) === true and $attribute['updated'] === false) {
                            $setDbParamColumnsUpdate = str_replace("\n      \$this->setDbParam(':$columnTempName', \$this->get$columCamelCase(), $typeBind);", '', $setDbParamColumnsUpdate);
                        }
                    }
                    if ($column === 'created' and preg_match("/^(__)([a-zA-Z0-9\w]+)/", $attribute['default'])) {
                        // $columnTemp = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                        $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $columnTempName)));
                        $setDbParamColumns .= "\n      \$this->setDbParam(':$columnTempName', \$this->get$columCamelCase(), \\PDO::PARAM_STR);";
                    } else if ($column === 'created') {
                        // $columnTemp = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                        $saveValues = str_replace(":$columnTempName", $attribute['default'], $saveValues);
                    }
                }
            } else if ($column === 'deleted') {
                // WHERE deleted_at IS NULL
                // $columnDeletedName = (isset($attribute['column']) === true) ? $attribute['column'] : $column;
                $deleted = "WHERE " . ((isset($attribute['column']) === true) ? $attribute['column'] : $column) . " IS NULL";
            }
        }
        
        foreach ($primary_key as $attribute) {
            $idsOrderBy .= $attribute['column'] . ', ';
            $paramsComment .= "\n   * @param " . $attribute['type'] . " \$" . $attribute['name'] . " [AGREGUE UNA DESCRIPCIÓN]";
            $ids .= $attribute['type'] . " \$" . $attribute['name'] . ", ";
            $paramsSelectById .= $attribute['column'] . " = :" . $attribute['name'] . " AND ";
            
            $typeBind = '';
            switch ($attribute['type']) {
                case 'int':
                    $typeBind = "\\PDO::PARAM_INT";
                    break;
                case 'string':
                    $typeBind = "\\PDO::PARAM_STR";
                    break;
                case 'file':
                    $typeBind = "\\PDO::PARAM_STR";
                    break;
                case 'bool':
                    $typeBind = "\\PDO::PARAM_BOOL";
                    break;
            }
            $setDbParamID .= "\n      \$this->setDbParam(':" . $attribute['name'] . "', \$" . $attribute['name'] . ", $typeBind);";
            $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute['name'])));
            $setDbParamIDUpdate .= "\n      \$this->setDbParam(':" . $attribute['name'] . "', \$this->get$columCamelCase(), $typeBind);";
        }
        
        $select = substr($select, 0, - 2);
        $idsOrderBy = substr($idsOrderBy, 0, - 2);
        $paramsSelectById = substr($paramsSelectById, 0, - 5);
        $saveParams = substr($saveParams, 0, - 2);
        $saveValues = substr($saveValues, 0, - 2);
        $setUpdate = substr($setUpdate, 0, - 2);
        
        if ($deleted === false) {
            $paramsSelectById = "WHERE " . $paramsSelectById;
        } else {
            $paramsSelectById = $deleted . " AND " . $paramsSelectById;
        }
        
        if (isset($columns['deleted']) === true and isset($columns['deleted']['column']) === true and preg_match("/^(__)([a-zA-Z0-9\w]+)/", $columns['deleted']['default'])) {
            $deleted_at = $columns['deleted']['column'] . ' = :' . $columns['deleted']['column'];
            $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $columns['deleted']['column'])));
            $deleted_at_bind = "\n        \$this->setDbParam(':" . $columns['deleted']['column'] . "', \$this->get$columCamelCase(), \PDO::PARAM_STR);";
        } else if (isset($columns['deleted']) === true and isset($columns['deleted']['column']) === true and ! preg_match("/^(__)([a-zA-Z0-9\w]+)/", $columns['deleted']['default'])) {
            $deleted_at = $columns['deleted']['column'] . ' = ' . $columns['deleted']['default'];
        } else if (isset($columns['deleted']) === true and preg_match("/^(__)([a-zA-Z0-9\w]+)/", $columns['deleted']['default'])) {
            $deleted_at = 'deleted' . ' = :deleted';
            $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', 'deleted')));
            $deleted_at_bind = "\n        \$this->setDbParam(':deleted', \$this->get$columCamelCase(), \PDO::PARAM_STR);";
        } else if (isset($columns['deleted']) === true and ! preg_match("/^(__)([a-zA-Z0-9\w]+)/", $columns['deleted']['default'])) {
            $deleted_at = 'deleted' . ' = ' . $columns['deleted']['default'];
        }
        
        $this->selectAll($TableName, $idsOrderBy, $select, $table, $deleted);
        $this->selectById($paramsComment, $TableName, $ids, $select, $table, $paramsSelectById, $setDbParamID);
        $this->save($table, $saveParams, $saveValues, $setDbParamColumns);
        $this->update($table, $setUpdate, $paramsSelectById, $setDbParamColumnsUpdate, $setDbParamIDUpdate);
        $this->delete($table, $paramsSelectById, $setDbParamIDUpdate, $deleted_at, $deleted_at_bind);
    }

    private function selectAll($TableName, $idsOrderBy, $select, $table, $deleted)
    {
        $app = $this->app;
        $this->selectAll = <<<selectALL
  /**
   * [AGREGUE UN COMENTARIO]
   * 
   * @param string \$order_by Columna o columnas por las que se ordenará la información
   * @param string \$order Tipo de orden ASC o DESC
   * @param int \$limit Cantidad de registros que mostrarán a partir del offset dado
   * @param int \$offset Registro en el que se empezará a dar la respuesta
   * @param string \$output_type null en caso de que se requiera una respuesta en array y no en objetos del tipo $TableName
   * @return \\$app\\model\\$TableName
   * @throws \\Exception
   */
  public function selectAll(string \$order_by = '$idsOrderBy', string \$order = 'ASC', int \$limit = -1, int \$offset = -1, \$output_type = __CLASS__)
  {
    try {
      \$sql = 'SELECT $select FROM $table $deleted ORDER BY %s %s' . ((\$offset >= 0) ? ' LIMIT %u OFFSET %u' : '');
      if (\$offset >= 0) {
        \$sql = sprintf(\$sql, \$order_by, \$order, \$limit, \$offset);
      }
      else {
        \$sql = sprintf(\$sql, \$order_by, \$order);
      }
      return \$this->query(\$sql, \$output_type);
    }
    catch (\\Exception \$exc) {
      throw new \\Exception(\$exc->getMessage(), \$exc->getCode(), \$exc->getPrevious());
    }
  }
selectALL;
    }

    private function selectById($paramsComment, $TableName, $ids, $select, $table, $paramsSelectById, $setDbParamID)
    {
        $app = $this->app;
        $this->selectById = <<<selectById
  /**
   * [AGREGUE UN COMENTARIO]
   * $paramsComment
   * @param string \$output_type null en caso de que se requiera una respuesta en array y no en objetos del tipo $TableName
   * @return \\$app\\model\\$TableName
   * @throws \\Exception
   */
  public function selectById($ids\$output_type = __CLASS__)
  {
    try {
      \$sql = 'SELECT $select FROM $table $paramsSelectById';$setDbParamID
      return \$this->query(\$sql, \$output_type);
    }
    catch (\\Exception \$exc) {
      throw new \\Exception(\$exc->getMessage(), \$exc->getCode(), \$exc->getPrevious());
    }
  }
selectById;
    }

    private function save($table, $saveParams, $saveValues, $setDbParamColumns)
    {
        $this->save = <<<save
  /**
   * [AGREGUE UN COMENTARIO]
   * 
   * @return int Retorna el ID con el que fue registrado la tupla
   * @throws \\Exception
   */
  public function save()
  {
    try {
      \$sql = 'INSERT INTO $table ($saveParams) VALUES ($saveValues)';$setDbParamColumns
      return \$this->execute(\$sql, (\$this->getDbDriver() === 'pgsql') ? self::SEQUENCE : null);
    }
    catch (\\Exception \$exc) {
      throw new \\Exception(\$exc->getMessage(), \$exc->getCode(), \$exc->getPrevious());
    }
  }
save;
    }

    private function update($table, $setUpdate, $paramsSelectById, $setDbParamColumnsUpdate, $setDbParamIDUpdate)
    {
        $this->update = <<<UPDATE
  /**
   * [AGREGUE UN COMENTARIO]
   * 
   * @return boolean Retorna TRUE si la actualización se realizó exitosamente
   * @throws \\Exception
   */
  public function update()
  {
    try {
      \$sql = 'UPDATE $table SET $setUpdate $paramsSelectById';$setDbParamColumnsUpdate$setDbParamIDUpdate
      \$this->execute(\$sql);
      return true;
    }
    catch (\\Exception \$exc) {
      throw new \\Exception(\$exc->getMessage(), \$exc->getCode(), \$exc->getPrevious());
    }
  }
UPDATE;
    }

    private function delete($table, $paramsSelectById, $setDbParamID, $deleted_at, $deleted_at_bind)
    {
        if ($deleted_at === false) {
            $this->delete = <<<DELETE
  /**
   * [AGREGUE UN COMENTARIO]
   * 
   * @param bool \$logical FALSE para borrado físico el borrado lógico no está implementado en esta tabla
   * @return boolean
   * @throws \\Exception
   */
  public function delete(bool \$logical = false)
  {
    try {
      if (\$logical === true) {
        throw new \\Exception('El borrado lógico es la tabla "$table" no está implementado', 500);
      }
      else if (\$logical === false) {
        \$sql = 'DELETE FROM $table $paramsSelectById';
      }
      else {
        throw new \\Exception('El borrado lógico no está implementado para la tabla "$table"', 500);
      }$setDbParamID
      \$this->execute(\$sql);
      return true;
    }
    catch (\\Exception \$exc) {
      throw new \\Exception(\$exc->getMessage(), \$exc->getCode(), \$exc->getPrevious());
    }
  }
DELETE;
        } else {
            $this->delete = <<<DELETE
  /**
   * [AGREGUE UN COMENTARIO]
   * 
   * @param bool \$logical TRUE para borrado lógico o FALSE para borrado físico
   * @return boolean
   * @throws \\Exception
   */
  public function delete(bool \$logical = true)
  {
    try {
      if (\$logical === true) {
        \$sql = 'UPDATE $table SET $deleted_at $paramsSelectById';$deleted_at_bind
      }
      else if (\$logical === false) {
        \$sql = 'DELETE FROM $table $paramsSelectById';
      }
      else {
        throw new \\Exception('El borrado lógico solo puede ser falso o verdadero para borrado físico', 500);
      }$setDbParamID
      \$this->execute(\$sql);
      return true;
    }
    catch (\\Exception \$exc) {
      throw new \\Exception(\$exc->getMessage(), \$exc->getCode(), \$exc->getPrevious());
    }
  }
DELETE;
        }
    }
}
