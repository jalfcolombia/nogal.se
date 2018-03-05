<?php

namespace NogalSE\Interfaces;

/**
 * @author Julián Andrés Lasso Figueroa <jalasso69@misena.edu.co>
 */
interface IDriver
{

  /**
   * 
   * @param string $columns
   */
  public function select(string $columns);

  /**
   * 
   * @param string $table
   * @param string $columns
   */
  public function insert(string $table, string $columns);

  /**
   * 
   * @param string $table
   */
  public function update(string $table);

  /**
   * 
   * @param string $table
   */
  public function delete(string $table);

  /**
   * 
   * @param string $table
   */
  public function from(string $table);

  /**
   * 
   * @param string $condition
   * @param bool $raw
   */
  public function where(string $condition, bool $raw = true);

  /**
   * 
   * @param string $typeCondition
   * @param string $condition
   * @param bool $raw
   */
  public function condition(string $typeCondition, string $condition, bool $raw = true);

  /**
   * 
   * @param float $limit
   */
  public function limit(float $limit);

  /**
   * 
   * @param int $offset
   */
  public function offset(int $offset);

  /**
   * 
   * @param string $columns
   * @param string $typeOrder
   */
  public function orderBy(string $columns, string $typeOrder);

  /**
   * 
   * @param string $values
   */
  public function values(string $values);

  /**
   * 
   * @param string $columnsAndValues
   * @param bool $raw
   */
  public function set(string $columnsAndValues, bool $raw = true);

  /**
   * 
   */
  public function __toString();

}
