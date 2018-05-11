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
 *
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
     * Arreglo asociativo con los parámetros de configuración necesarios.<br><br>
     * <b>driver</b> Driver a usar para la conexión a la base de datos.
     * Ejemplo pgsql, mysql<br>
     * <b>host</b> Dirección IP donde se encuentra la base de datos. Ejemplo localhost<br>
     * <b>port</b> Puerto de conexión de la base de datos. Ejemplo 5432, 3306<br>
     * <b>db_name</b> Nombre de la base de datos a usar en la conexión. Ejemplo mydb<br>
     * <b>user</b> Usuario de la base de datos.<br>
     * <b>password</b> Contraseña del usuario de la base de datos.<br>
     * <b>hash</b> Método a usar para encriptar las contraseñas en la base datos. Ejemplo md5, sha1<br><br>
     * Para más información del HASH ver http://php.net/manual/en/function.hash.php<br>
     *
     * @var array
     * @link http://php.net/manual/en/function.hash.php Más información para la configuración del HASH
     */
    protected $config;

    /**
     * Constructor de la clase DataSource
     *
     * @param array $config
     *            Arreglo asociativo con los parámetros de configuración
     *            necesarios.<br>driver, host, port, db_name, user, password, hash.<br><br>
     */
    public function __construct(array $config)
    {
        $this->db_params = array();
        $this->db_instance = null;
        $this->config = $config;
    }

    /**
     * Borra el parámetro indicado
     *
     * @param string $param
     *            Parámetro a borrar del set de parámetros a trabajar en una consulta
     * @return $this
     */
    protected function deleteDbParam(string $param)
    {
        if (isset($this->db_params[$param]) === true) {
            unset($this->db_params[$param]);
        }
        return $this;
    }

    /**
     * Borra la pila de parámetros usados en una consulta
     *
     * @return $this
     */
    protected function deleteDbParams()
    {
        $this->db_params = array();
        return $this;
    }

    /**
     * Define un parámetro con su valor y el tipo de parámetro para transferido
     * a la consulta SQL
     *
     * @param string $param
     *            Nombre del parámetro
     * @param mixed $value
     *            Valor del parámetro
     * @param int $type
     *            Tipo de parámetro. Ejemplo PDO::PARAM_STR
     * @return $this
     */
    protected function setDbParam(string $param, $value, int $type)
    {
        $this->db_params[$param] = array(
            'value' => $value,
            'type' => $type
        );
        return $this;
    }

    /**
     * Comprueba la existencia de un parámetro definido
     *
     * @param string $param
     *            nombre del parámetro
     * @return bool
     */
    protected function hasDbParam(string $param): bool
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
                $dsn = $this->config['driver'] . ':host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['db_name'];
                $this->db_instance = new \PDO($dsn, $this->config['user'], $this->config['password']);
                $this->db_instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->db_instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            }
            return $this->db_instance;
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        }
    }

    /**
     * Inicializa una transacción
     *
     * @return $this
     */
    protected function beginTransaction()
    {
        $this->db_instance->beginTransaction();
        return $this;
    }

    /**
     * Confirma una transacción
     *
     * @return $this
     */
    protected function commit()
    {
        $this->db_instance->commit();
        return $this;
    }

    /**
     * Retrocede una transacción
     *
     * @return $this
     */
    protected function rollBack()
    {
        $this->db_instance->rollBack();
        return $this;
    }

    /**
     * Asigna los parámetros establecidos en la variable $db_params
     *
     * @param \PDOStatement $stmt
     *            Estamento utilizado en la conexión
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

    /**
     * SELECT
     * Método usado para realizar consultas tipo SELECT
     *
     * @param string $sql
     * @param object $class_object
     *            [opcional]
     * @return array
     * @throws \PDOException
     */
    protected function query(string $sql, $class_object = null)
    {
        try {
            $stmt = $this->bindParams($this->getConection()->prepare($sql));
            $stmt->execute();
            return $this->getResultsObject($stmt, $class_object);
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        } finally {
            $this->deleteDbParams();
        }
    }

    /**
     * INSERT, UPDATE, DELETE
     * Método para realizar consultas tipo INSERT, UPDATE y DELETE a la base datos.
     * Las consultas tipo INSERT devuelven el ID con el que fue insertado.
     * Las consultas tipo UPDATE y DELETE devuelven un cero (0).
     *
     * @param string $sql
     *            Consulta SQL
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
            } else {
                return 0;
            }
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        } finally {
            $this->deleteDbParams();
        }
    }

    /**
     * Método para preparar una excepción del tipo Exception
     *
     * @param \PDOException $exc
     * @throws \Exception
     */
    protected function throwNewExceptionFromException(\Exception $exc): void
    {
        $code = (strlen($exc->getCode()) > 0) ? $exc->getCode() : '0';
        $previous = ($exc->getPrevious() !== null) ? $exc->getPrevious() : '';
        throw new \Exception($exc->getMessage(), $code, $previous);
    }

    /**
     * Método para obtener los resultados como un objeto de PHP genérico o como
     * un objeto de una clase definida.
     *
     * @param \PDOStatement $stmt
     *            Estamento que contiene la respuesta a la consulta realizada.
     * @param object $class_object
     *            Clase del objeto a usar para dar respuesta de ese tipo de objeto.
     * @return mixed La respuesta puede ser en un objeto genérico de PHP o el tipo de objeto pasado en $class_object
     * @throws \Exception
     */
    private function getResultsObject(\PDOStatement $stmt, $class_object)
    {
        try {
            $answer = array();
            if ($class_object === null) {
                $answer = $stmt->fetchAll(\PDO::FETCH_OBJ);
            } else if (class_exists($class_object) === true) {
                $tmp = $stmt->fetchAll();
                $i = 0;
                $class_object = new $class_object($this->config);
                foreach ($tmp as $row) {
                    $answer[$i] = clone $class_object;
                    foreach ($row as $column => $value) {
                        $column = 'set' . str_replace("_", "", ucwords($column, "_"));
                        $answer[$i]->$column($value);
                    }
                    $i ++;
                }
            } else {
                throw new \PDOException('El objeto "' . $class_object . '" no es un objeto válido.');
            }
            return $answer;
        } catch (\PDOException $exc) {
            throw new \PDOException($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
        }
    }

    /**
     * Método para preparar una excepción del tipo PDOException
     *
     * @param \PDOException $exc
     * @throws \Exception
     */
    private function throwNewExceptionFromPDOException(\PDOException $exc): void
    {
        $code = (strlen($exc->getCode()) > 0) ? $exc->getCode() : '0';
        $previous = ($exc->getPrevious() !== null) ? $exc->getPrevious() : '';
        throw new \Exception($exc->getMessage(), $code, $previous);
    }
}
