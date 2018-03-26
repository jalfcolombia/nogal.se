<?php

namespace NogalSE;

/**
 * 
 */
class DataSource
{

  /**
   *
   * @var array
   */
  private $db_params;

  /**
   *
   * @var \PDO
   */
  private $db_instance;

  /**
   *
   * @var array
   */
  protected $config;

  public function __construct($config)
  {
    $this->db_params   = array();
    $this->db_instance = null;
    $this->config      = $config;
  }
  
  /**
   * Borra el parámetro indicado
   * 
   * @param string $param Parámetro a borrar del set de parámetros a trabajar en una consulta
   * @return DataSource
   */
  protected function deleteDbParam(string $param)
  {
    if (isset($this->db_params[$param]) === true)
    {
      unset($this->db_params[$param]);
    }
    return $this;
  }
  
  /**
   * Borra la pila de parámetros usados en una consulta
   *
   * @return DataSource
   */
  protected function deleteDbParams()
  {
    $this->db_params = array();
    return $this->
  }

  /**
   * 
   * @param string $param
   * @param mixed $value
   * @param int $type
   * @return DataSource
   */
  protected function setDbParam(string $param, $value, int $type)
  {
    $this->db_params[$param] = array('value' => $value, 'type' => $type);
    return $this;
  }

  protected function hasDbParam($param)
  {
    return isset($this->db_params[$param]);
  }

  /**
   * 
   * @return string
   */
  protected function getDbDriver()
  {
    return $this->config['driver'];
  }

  /**
   * 
   * @return \PDO
   * @throws \Exception
   */
  protected function getConection() : \PDO
  {
    try
    {
      if ($this->db_instance === null)
      {
        $dsn               = $this->config['driver'] . ':host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['db_name'];
        $this->db_instance = new \PDO($dsn, $this->config['user'], $this->config['password']);
        $this->db_instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db_instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
      }
      return $this->db_instance;
    }
    catch (\PDOException $exc)
    {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
  }

  /**
   * 
   * @param \PDOStatement $stmt
   * @return \PDOStatement
   */
  private function bindParams(\PDOStatement $stmt) : \PDOStatement
  {
    if (count($this->db_params) > 0)
    {
      foreach ($this->db_params as $param => $data)
      {
        $stmt->bindParam($param, $data['value'], $data['type']);
      }
    }
    return $stmt;
  }

  private function getResultsObject(\PDOStatement $stmt, $class_object)
  {
    try
    {
      $answer = array();
      if ($class_object === null)
      {
        $answer = $stmt->fetchAll(\PDO::FETCH_OBJ);
      }
      else if (class_exists($class_object) === true)
      {
        $tmp          = $stmt->fetchAll();
        $i            = 0;
        $class_object = new $class_object($this->config);
        foreach ($tmp as $row)
        {
          $answer[$i] = clone $class_object;
          foreach ($row as $column => $value)
          {
            $column = 'set' . str_replace("_", "", ucwords($column, "_"));
            $answer[$i]->$column($value);
          }
          $i++;
        }
      }
      else
      {
        throw new \PDOException('El objeto "' . $class_object . '" no es un objeto válido.');
      }
      return $answer;
    }
    catch (\PDOException $exc)
    {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
  }

  /**
   * SELECT
   * 
   * @param string $sql
   * @param object $class_object
   * @return array
   * @throws \PDOException
   */
  protected function query(string $sql, $class_object = null)
  {
    try
    {
      $stmt = $this->bindParams($this->getConection()->prepare($sql));
      $stmt->execute();
      return $this->getResultsObject($stmt, $class_object);
    }
    catch (\PDOException $exc)
    {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
    finally
    {
      $this->deleteDbParams();
    }
  }

  /**
   * INSERT, UPDATE, DELETE
   * 
   * @param string $sql
   * @param string $getLastIdInsert
   * @return int
   * @throws \PDOException
   */
  protected function execute(string $sql, $getLastIdInsert = null) : int
  {
    try
    {
      $stmt = $this->bindParams($this->getConection()->prepare($sql));
      $stmt->execute();
      return $getLastIdInsert !== null ? $this->getConection()->lastInsertId($getLastIdInsert) : $this->getConection()->lastInsertId();
    }
    catch (\PDOException $exc)
    {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
    finally
    {
      $this->deleteDbParams();
    }
  }

}
