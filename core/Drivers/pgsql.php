<?php

/**
 * This file is part of the NogalSE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NogalSE\Driver;

use NogalSE\Interfaces\IDriver;

/**
 * @author Julián Andrés Lasso Figueroa <jalasso69@misena.edu.co>
 */
class pgsql implements IDriver
{

  /**
   * 
   * @var string
   */
  private $nql;

  public function __construct()
  {
    $this->nql = '';
  }

  /**
   * 
   * @param string $table
   * @return $this
   */
  public function delete(string $table)
  {
    $this->nql = 'DELETE FROM ' . $table . ' ';
    return $this;
  }

  /**
   * 
   * @param string $table
   * @return $this
   */
  public function from(string $table)
  {
    $this->nql .= 'FROM ' . $table . ' ';
    return $this;
  }

  /**
   * 
   * @param string $table
   * @param string $columns
   * @return $this
   */
  public function insert(string $table, string $columns)
  {
    $this->nql = 'INSERT INTO ' . $table . ' (' . $columns . ') ';
    return $this;
  }

  /**
   * 
   * @param int $limit
   * @return $this
   */
  public function limit(float $limit)
  {
    $this->nql .= 'LIMIT ' . $limit . ' ';
    return $this;
  }

  /**
   * 
   * @param int $offset
   * @return $this
   */
  public function offset(int $offset)
  {
    $this->nql .= 'OFFSET ' . $offset . ' ';
    return $this;
  }

  /**
   * 
   * @param string $typeOrder
   * @return $this
   */
  public function orderBy(string $columns, string $typeOrder)
  {
    $this->nql .= 'ORDER BY ' . $columns . ' ' . $typeOrder . ' ';
    return $this;
  }

  /**
   * 
   * @param string $columns
   * @return $this
   */
  public function select(string $columns)
  {
    $this->nql = 'SELECT ' . $columns . ' ';
    return $this;
  }

  /**
   * 
   * @param string $columnsAndValues
   * @param bool $raw
   * @return $this
   */
  public function set(string $columnsAndValues, bool $raw = true)
  {
    if ($raw === true) {
      $this->nql .= 'SET ' . $columnsAndValues . ' ';
    }
    elseif ($raw === false) {
      $columnsAndValues = str_replace(' ', '', $columnsAndValues);
      $data             = explode(',', $columnsAndValues);
      $set              = '';
      foreach ($data as $column) {
        $set .= $column . ' = :' . $column . ', ';
      }
      $set       = substr($set, 0, -2);
      $this->nql .= 'SET ' . $set . ' ';
    }
    return $this;
  }

  /**
   * 
   * @param string $table
   * @return $this
   */
  public function update(string $table)
  {
    $this->nql = 'UPDATE ' . $table . ' ';
    return $this;
  }

  /**
   * 
   * @param string $values
   */
  public function values(string $values)
  {
    $this->nql .= 'VALUES (' . $values . ') ';
    return $this;
  }

  /**
   * 
   * @param string $condition
   * @param bool $raw
   * @return $this
   */
  public function where(string $condition, bool $raw = true)
  {
    if ($raw === true) {
      $this->nql .= 'WHERE ' . $condition . ' ';
    }
    elseif ($raw === false) {
      $this->nql .= 'WHERE ' . $condition . ' = :' . $condition . ' ';
    }
    return $this;
  }

  /**
   * 
   * @param string $typeCondition
   * @param string $condition
   * @param bool $raw
   * @return $this
   */
  public function condition(string $typeCondition, string $condition, bool $raw = true)
  {
    if ($raw === true) {
      $this->nql .= $typeCondition . ' ' . $condition . ' ';
    }
    elseif ($raw === false) {
      $this->nql .= $typeCondition . ' ' . $condition . ' = :' . $condition . ' ';
    }
    return $this;
  }

  /**
   * 
   * @return string
   */
  public function __toString()
  {
    return substr($this->nql, 0, -1);
  }

}
