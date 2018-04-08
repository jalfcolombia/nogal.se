<?php

/**
 * This file is part of the NogalSE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
  public function Select(string $columns);

  /**
   * 
   * @param string $table
   * @param string $columns
   */
  public function Insert(string $table, string $columns);

  /**
   * 
   * @param string $table
   */
  public function Update(string $table);

  /**
   * 
   * @param string $table
   */
  public function Delete(string $table);

  /**
   * 
   * @param string $table
   */
  public function From(string $table);

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
  public function Condition(string $typeCondition, string $condition, bool $raw = true);

  /**
   * 
   * @param float $limit
   */
  public function Limit(float $limit);

  /**
   * 
   * @param int $offset
   */
  public function Offset(int $offset);

  /**
   * 
   * @param string $columns
   * @param string $typeOrder
   */
  public function OrderBy(string $columns, string $typeOrder);

  /**
   * 
   * @param string $values
   */
  public function Values(string $values);

  /**
   * 
   * @param string $columnsAndValues
   * @param bool $raw
   */
  public function Set(string $columnsAndValues, bool $raw = true);

  /**
   * 
   */
  public function __toString();

}
