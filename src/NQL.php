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

use NogalSE\Interfaces\IDriver;

/**
 * Nogal Query Language (NQL) proporciona algunos métodos para realizar
 * operaciones básicas sobre entidades tales como la creación, borrado, carga,
 * filtrado y ordenación. En ocasiones, sin embargo, es necesario realizar
 * consultas más complejas que no pueden resolverse con estos métodos.
 * @author Julián Andrés Lasso Figueroa <jalasso69@misena.edu.co>
 */
class NQL implements IDriver
{

  /**
   * Valor para condicional AND de SQL
   */
  const _AND = 'AND';

  /**
   * Valor para condicional OR de SQL
   */
  const _OR = 'OR';

  /**
   * Valor para orden ascendente
   */
  const ASC = 'ASC';

  /**
   * Valor para orden descendente
   */
  const DESC = 'DESC';

  /**
   * Controlador de PDO a usar, Ejemplo: pgsql, mysql
   * 
   * @var string
   */
  private $driver;

  /**
   * Dirección de la carpeta contenedora de las clases controladoras para
   * compatibilidad con NQL y los motores de bases de datos
   * 
   * @var string
   */
  private $pathDrivers;

  /**
   * Variable contenedora del objeto referente a la clase del motor de
   * bases de datos para NQL
   * 
   * @var object
   */
  private $driverClass;

  public function __construct(string $driver, string $pathDrivers = null)
  {
    $this->driver      = $driver;
    $this->pathDrivers = $pathDrivers;
    $this->loadDriver();
    $class             = 'NogalSE\\Driver\\' . $this->driver;
    $this->driverClass = new $class();
  }

  /**
   * Carga el controlador para Nogal Query Language referenciado en el
   * constructor de la clase NQL
   * 
   * @throws \Exception
   */
  private function loadDriver()
  {
    if ($this->pathDrivers !== null) {
      if (is_file($this->pathDrivers . $this->driver . '.php') === true) {
        require_once $this->pathDrivers . $this->driver . '.php';
      }
      else {
        throw new \Exception('El controlador ' . $this->driver . ' no existe');
      }
    }
  }

  /**
   * Agrega una condición AND u OR según la necesidad
   * 
   * @param string $typeCondition Condicionales NQL::_AND u NQL::_OR
   * @param string $condition Condición a utilizar. Ejemplo id = 33
   * @param bool $raw true: "id = 69" o false: "id" = "id = :id" esta última
   * opción permite enlazar (bind) con la clase PDOStatement
   * @return $this
   */
  public function condition(string $typeCondition, string $condition, bool $raw = true)
  {
    $this->driverClass->Condition($typeCondition, $condition, $raw);
    return $this;
  }

  /**
   * Inicializa un SQL con la palabra clave DELETE
   * 
   * @param string $table Tabla de donde se borrará la información
   * @return $this
   */
  public function delete(string $table)
  {
    $this->driverClass->Delete($table);
    return $this;
  }

  /**
   * Complementa el uso de las palabra clave SELECT
   * 
   * @param string $table Tabla en la cual se realizará la consulta
   * @return $this
   */
  public function from(string $table)
  {
    $this->driverClass->From($table);
    return $this;
  }

  /**
   * Inicializa un SQL con la palabra clave INSERT INTO
   * 
   * @param string $table
   * @param string $columns
   * @return $this
   */
  public function insert(string $table, string $columns)
  {
    $this->driverClass->Insert($table, $columns);
    return $this;
  }

  /**
   * Agrega a la consulta SQL la clausula LIMIT
   * 
   * @param float $limit
   * @return $this
   */
  public function limit(float $limit)
  {
    $this->driverClass->Limit($limit);
    return $this;
  }

  /**
   * Agrega a la consulta SQL la clausula OFFSET (Clusula complementaria de LIMIT)
   * 
   * @param int $offset
   * @return $this
   */
  public function offset(int $offset)
  {
    $this->driverClass->Offset($offset);
    return $this;
  }

  /**
   * Finaliza una consulta SQL con la clausula ORDER BY
   * 
   * @param string $columns
   * @param string $typeOrder
   * @return $this
   */
  public function orderBy(string $columns, string $typeOrder)
  {
    $this->driverClass->OrderBy($columns, $typeOrder);
    return $this;
  }

  /**
   * Inicializa un SQL con la palabra clave SELECT
   * 
   * @param string $columns
   * @return $this
   */
  public function select(string $columns)
  {
    $this->driverClass->Select($columns);
    return $this;
  }

  /**
   * Complementa una sentencia SQL inicializada con la palabra clave UPDATE
   * 
   * @param string $columnsAndValues
   * @param bool $raw
   * @return $this
   */
  public function set(string $columnsAndValues, bool $raw = true)
  {
    $this->driverClass->Set($columnsAndValues, $raw);
    return $this;
  }

  /**
   * Inicializa un SQL con la palabra clave UPDATE
   * 
   * @param string $table
   * @return $this
   */
  public function update(string $table)
  {
    $this->driverClass->Update($table);
    return $this;
  }

  /**
   * Complementa una sentencia SQL inicializada con la palabra clave INSERT INTO
   * 
   * @param string $values
   * @return $this
   */
  public function values(string $values)
  {
    $this->driverClass->Values($values);
    return $this;
  }

  /**
   * Complementa una sentencia SQL con la clausula WHERE
   * 
   * @param string $condition
   * @param bool $raw
   * @return $this
   */
  public function where(string $condition, bool $raw = true)
  {
    $this->driverClass->Where($condition, $raw);
    return $this;
  }

  /**
   * Retorna la consulta creada con NQL
   * 
   * @return string
   */
  public function __toString(): string
  {
    return $this->driverClass->__toString();
  }

}
