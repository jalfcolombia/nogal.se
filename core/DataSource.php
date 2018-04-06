<?php

/**
 * This file is part of the NogalSE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NogalSE;

/**
 * Clase controladora de la conexión a la base de datos
 * @author Julián Andrés Lasso Figueroa <jalasso69@misena.edu.co>
 */
class DataSource
{

  /**
   * Pila de parámetros a usar en una sentencia SQL
   * 
   * @var array
   */
  private $db_params;

  /**
   * Instancia de la clase PDO
   * 
   * @var \PDO
   */
  private $db_instance;

  /**
   * Arreglo asociativo con los parámetros de configuración necesarios.
   * driver, host, port, dbname, user, password, hash
   * 
   * @var array
   */
  protected $config;

  /**
   * 
   * @param type $config Arreglo asociativo con los parámetros de configuración
   * necesarios. driver, host, port, dbname, user, password, hash
   */
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
  protected function deleteDbParam(string $param): DataSource
  {
    if (isset($this->db_params[$param]) === true) {
      unset($this->db_params[$param]);
    }
    return $this;
  }

  /**
   * Borra la pila de parámetros usados en una consulta
   *
   * @return DataSource
   */
  protected function deleteDbParams(): DataSource
  {
    $this->db_params = array();
    return $this;
  }

  /**
   * Define un parámetro con su valor y el tipo de parámetro para transferido
   * a la consulta SQL
   * 
   * @param string $param Nombre del parámetro
   * @param mixed $value Valor del parámetro
   * @param int $type Tipo de parámetro. Ejemplo PDO::PARAM_STR
   * @return DataSource
   */
  protected function setDbParam(string $param, $value, int $type): DataSource
  {
    $this->db_params[$param] = array('value' => $value, 'type' => $type);
    return $this;
  }

  /**
   * Comprueba la existencia de un parámetro definido
   * 
   * @param type $param nombre del parámetro
   * @return bool
   */
  protected function hasDbParam($param): bool
  {
    return isset($this->db_params[$param]);
  }

  /**
   * Retorna el nombre del controlador de la base de datos ya establecido
   * 
   * @return string
   */
  protected function getDbDriver(): string
  {
    return $this->config['driver'];
  }

  /**
   * Devueve la instancia de conexión de la base de datos.
   * 
   * @return \PDO
   * @throws \Exception
   */
  protected function getConection(): \PDO
  {
    try {
      if ($this->db_instance === null) {
        $dsn               = $this->config['driver'] . ':host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['db_name'];
        $this->db_instance = new \PDO($dsn, $this->config['user'], $this->config['password']);
        $this->db_instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db_instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
      }
      return $this->db_instance;
    }
    catch (\PDOException $exc) {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
  }

  /**
   * Asigna los parámetros establecidos en la variable $db_params
   * 
   * @param \PDOStatement $stmt Estamento utilizado en la conexión
   * @return \PDOStatement
   */
  private function bindParams(\PDOStatement $stmt): \PDOStatement
  {
    if (count($this->db_params) > 0) {
      foreach ($this->db_params as $param => $data) {
        $stmt->bindParam($param, $data['value'], $data['type']);
      }
    }
    return $stmt;
  }

  private function getResultsObject(\PDOStatement $stmt, $class_object)
  {
    try {
      $answer = array();
      if ($class_object === null) {
        $answer = $stmt->fetchAll(\PDO::FETCH_OBJ);
      }
      else if (class_exists($class_object) === true) {
        $tmp          = $stmt->fetchAll();
        $i            = 0;
        $class_object = new $class_object($this->config);
        foreach ($tmp as $row) {
          $answer[$i] = clone $class_object;
          foreach ($row as $column => $value) {
            $column = 'set' . str_replace("_", "", ucwords($column, "_"));
            $answer[$i]->$column($value);
          }
          $i++;
        }
      }
      else {
        throw new \PDOException('El objeto "' . $class_object . '" no es un objeto válido.');
      }
      return $answer;
    }
    catch (\PDOException $exc) {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
  }

  /**
   * SELECT
   * Método usado para realizar consultas tipo SELECT
   * 
   * @param string $sql
   * @param object $class_object [opcional]
   * @return array
   * @throws \PDOException
   */
  protected function query(string $sql, $class_object = null)
  {
    try {
      $stmt = $this->bindParams($this->getConection()->prepare($sql));
      $stmt->execute();
      return $this->getResultsObject($stmt, $class_object);
    }
    catch (\PDOException $exc) {
      throw new \Exception($exc->getMessage());
    }
    finally {
      $this->deleteDbParams();
    }
  }

  /**
   * INSERT, UPDATE, DELETE
   * Método para realizar consultas tipo INSERT, UPDATE y DELETE a la base datos.
   * Las consultas tipo INSERT devuelven el ID con el que fue insertado.
   * Las consultas tipo UPDATE y DELETE devuelven un cero (0).
   * 
   * @param string $sql Consulta SQL
   * @param string $getLastIdInsert
   * @return int
   * @throws \PDOException
   */
  protected function execute(string $sql, $getLastIdInsert = null): int
  {
    try {
      $stmt = $this->bindParams($this->getConection()->prepare($sql));
      $stmt->execute();
      preg_match('/^(insert into )/i', $sql, $matches);
      if (count($matches) > 0) {
        return $getLastIdInsert !== null ? $this->getConection()->lastInsertId($getLastIdInsert) : $this->getConection()->lastInsertId();
      }
      else {
        return 0;
      }
    }
    catch (\PDOException $exc) {
      throw new \Exception($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
    }
    finally {
      $this->deleteDbParams();
    }
  }

}
