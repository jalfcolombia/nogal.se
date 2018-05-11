<?php

namespace NogalSE\Task\Generate;

class Table
{

    private $behavior;

    private $sequence;

    private $length;

    private $private;

    private $default;

    private $getter;

    private $setter;

    public function __construct()
    {
        $this->behavior = array(
            'private' => '',
            'default' => '',
            'getter' => '',
            'setter' => ''
        );
        $this->sequence = '';
        $this->length = '';
        $this->private = '';
        $this->default = '';
        $this->getter = '';
        $this->setter = '';
    }

    public function main($table, $columns)
    {
        foreach ($columns as $colum => $data) {
            switch ($colum) {
                // generando la secuencia en caso de PostgreSQL
                case '_sequence':
                    $this->sequence = $data;
                    unset($columns['_sequence']);
                    break;
                // behavior para created
                case 'created':
                    $column = (isset($columns['created']['name']) === true) ? $columns['created']['name'] : $columns['created']['column'];
                    $this->behavior['private'] .= "\n  private \$$column;" . (((isset($columns['updated']) === true) or (isset($columns['deleted']) === true)) ? "\n" : null);
                    if (isset($columns['created']['not_null']) === true and $columns['created']['not_null'] === true) {
                        if (strpos($columns['created']['default'], ':') !== false) {
                            $default = str_replace(':', '', $columns['created']['default']);
                        } else {
                            $default = "'" . $columns['created']['default'] . "'";
                        }
                        $this->behavior['default'] .= "    \$this->$column = $default;" . ((isset($columns['updated']) === true) or (isset($columns['deleted']) === true)) ? "\n" : null;
                    }
                    
                    $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $column)));
                    $this->behavior['getter'] .= <<<GETTER

  public function get$columCamelCase()
  {
    return (string) \$this->$column;
  }

GETTER;
                    $this->behavior['setter'] .= <<<SETTER

  /**
   * 
   * @return \$this
   */
  public function set$columCamelCase(string \$$column)
  {
    \$this->$column = \$$column;
    return \$this;
  }

SETTER;
                    break;
                // behavior para updated
                case 'updated':
                    $column = (isset($columns['updated']['name']) === true) ? $columns['updated']['name'] : $columns['updated']['column'];
                    $this->behavior['private'] .= "  private \$$column;" . ((isset($columns['deleted']) === true) ? "\n" : null);
                    
                    if (isset($columns['updated']['not_null']) === true and $columns['updated']['not_null'] === true) {
                        if (strpos($columns['updated']['default'], ':') !== false) {
                            $default = str_replace(':', '', $columns['updated']['default']);
                        } else {
                            $default = "'" . $columns['updated']['default'] . "'";
                        }
                        $this->behavior['default'] .= "    \$this->$column = $default;" . (isset($columns['deleted']) === true) ? "\n" : null;
                    }
                    
                    $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $column)));
                    $this->behavior['getter'] .= <<<GETTER

  public function get$columCamelCase()
  {
    return (string) \$this->$column;
  }

GETTER;
                    $this->behavior['setter'] .= <<<SETTER

  /**
   * 
   * @return \$this
   */
  public function set$columCamelCase(string \$$column)
  {
    \$this->$column = \$$column;
    return \$this;
  }

SETTER;
                    break;
                // behavior para deleted
                case 'deleted':
                    $column = (isset($columns['deleted']['name']) === true) ? $columns['deleted']['name'] : $columns['deleted']['column'];
                    $this->behavior['private'] .= "  private \$$column;";
                    
                    if (isset($columns['deleted']['not_null']) === true and $columns['deleted']['not_null'] === true) {
                        if (strpos($columns['updated']['default'], ':') !== false) {
                            $default = str_replace(':', '', $columns['deleted']['default']);
                        } else {
                            $default = "'" . $columns['deleted']['default'] . "'";
                        }
                        $this->behavior['default'] .= "    \$this->$column = $default;";
                    }
                    
                    $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $column)));
                    $this->behavior['getter'] .= <<<GETTER

  public function get$columCamelCase()
  {
    return (string) \$this->$column;
  }

GETTER;
                    $this->behavior['setter'] .= <<<SETTER

  /**
   * 
   * @return \$this
   */
  public function set$columCamelCase(string \$$column)
  {
    \$this->$column = \$$column;
    return \$this;
  }

SETTER;
                    break;
                // cualquier otra columna
                default:
                    $this->generateColumn($table, $colum, $data);
                    break;
            }
        }
        
        if (is_array($this->behavior) === false) {
            $this->behavior = null;
        }
    }

    private function generateColumn($table, $column, $data)
    {
        $type = $data['type'];
        $columnName = (isset($data['name']) === true) ? $data['name'] : $column;
        // LENGTH
        if ($type === 'string' or $type === 'file') {
            $this->length .= "\n  const " . strtoupper($columnName) . "_LENGTH = " . $data['length'] . ";";
        }
        
        // private
        $this->private .= "\n  private \$$columnName;";
        
        // default
        if (isset($data['default']) === true and $data['not_null'] === true) {
            $default = '';
            if (strpos($data['default'], '__') !== false) {
                $default = str_replace('__', '', $data['default']);
            } else {
                if ($type === 'bool' and $data['default'] === true) {
                    $default = 'true';
                } else if ($type === 'bool' and $data['default'] === false) {
                    $default = 'false';
                } else if ($type === 'int') {
                    $default = $data['default'];
                } else if ($type === 'string' or $data['type'] === 'file') {
                    $default = "'" . $data['default'] . "'";
                }
            }
            $this->default .= "\n    \$this->$columnName = $default;";
        }
        
        $columCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $columnName)));
        
        if (isset($data['foreign_key']) === false) {
            $type = ($type === 'file') ? 'string' : $type;
            $this->getter .= <<<GETTER

  public function get$columCamelCase()
  {
    return ($type) \$this->$columnName;
  }

GETTER;
        } else if (isset($data['foreign_key']) === true and isset($data['table']) === true and isset($data['display']) === true) {
            $tableFK = $data['table'];
            $displayFK = $data['display'];
            $fk = $data['foreign_key'];
            // \$sql = 'SELECT $tableFK.$displayFK AS custom FROM $tableFK WHERE $tableFK.$fk = $table.$column AND ';
            $typeBind = '';
            switch ($type) {
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
            $type = ($type === 'file') ? 'string' : $type;
            $this->getter .= <<<GETTER

  public function get$columCamelCase(bool \$deep = false)
  {
    if (\$deep === true) {
      \$sql = 'SELECT $tableFK.$displayFK AS $displayFK FROM $tableFK WHERE $tableFK.$fk = :$columnName';
      \$answer = \$this->setDbParam(':$columnName', \$this->$columnName, $typeBind)->query(\$sql)[0]->$displayFK;
    }
    else if (\$deep === false) {
      \$answer = ($type) \$this->$columnName;
    }
    else {
      throw new \\Exception('La opción \$deep solo puede ser falso o verdadero', 500);
    }
    return \$answer;
  }

GETTER;
        }
        
        $this->setter .= <<<SETTER

  /**
   * 
   * @return \$this
   */
  public function set$columCamelCase($type \$$columnName)
  {
    \$this->$columnName = \$$columnName;
    return \$this;
  }

SETTER;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function getBehavior()
    {
        return $this->behavior;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getPrivate()
    {
        return $this->private . $this->getBehavior()['private'];
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getGetter()
    {
        return $this->getter . $this->getBehavior()['getter'];
    }

    public function getSetter()
    {
        return $this->setter . $this->getBehavior()['setter'];
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    public function setBehavior($behavior)
    {
        $this->behavior = $behavior;
    }

    public function setLength($length)
    {
        $this->length = $length;
    }

    public function setPrivate($private)
    {
        $this->private = $private;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function setGetter($getter)
    {
        $this->getter = $getter;
    }

    public function setSetter($setter)
    {
        $this->setter = $setter;
    }
}
