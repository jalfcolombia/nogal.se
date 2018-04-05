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
interface ITable
{

  /**
   * 
   * @param string $order_by
   * @param string $order
   * @param int $limit
   * @param int $offset
   */
  public function selectAll(string $order_by = 'id', string $order = 'ASC', int $limit = 0, int $offset = 0);

  /**
   * 
   */
  public function save();

  /**
   * 
   */
  public function update();

  /**
   * 
   * @param bool $logical
   */
  public function delete(bool $logical = true);

}
