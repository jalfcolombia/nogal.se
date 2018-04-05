<?php

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
