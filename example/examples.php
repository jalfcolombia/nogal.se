<?php

require_once 'src/Interfaces/ITable.php';
require_once 'src/DataSource.php';
require_once 'output/DataBase.php';
require_once 'output/Rol.php';
require_once 'output/Usuario.php';

use MyApp\Model\Rol;
use MyApp\Model\Usuario;

try
{
  $config = array(
    'driver'   => 'pgsql',
    'host'     => 'localhost',
    'port'     => 5432,
    'db_name'  => 'prueba',
    'user'     => 'postgres',
    'password' => '123456'
  );

  // Insertar Rol
  $rol    = new Rol($config);
  $rol_id = $rol->setRolname('Administrador')->save();

  // Insertar Usuario
  $usuario    = new Usuario($config);
  $usuario_id = $usuario->setNickname('jalf')->setPassword('123')->setRolId($rol_id)->save();

  // Modificar el Usuario insertado
  $usuario->setPassword('321AxY')->update();

  // Modificación alguien que apenas llega en una petición con ID 35 y solo modificará el nickname
  $usuario = new Usuario($config);
  $user    = $usuario->selectById(35);
  // validamos que lo haya encontrado
  if (count($user) > 0)
  {
    $user[0]->setNickname('NUEVO NICKNAME')->update();
  }

  // Eliminado lógico pensando en que el ID fuese 23
  $usuario->setId(23)->delete();

  // Eliminado físico pensando en que el ID fuese 32
  $usuario->setId(32)->delete(false);

  // listar todos los datos de la tabla Usuario
  $users = $usuario->selectAll();
  echo '<pre>';
  print_r($users);
  echo '</pre>';

  // Listando todos los datos de la tabla Usuario ordenandolos por la columna nickname
  $users = $usuario->selectAll('nickname');
  echo '<pre>';
  print_r($users);
  echo '</pre>';

  // Listando todos los datos de la tabla Usuario ordenandolos por la columna nickname y de forma descendente
  $users = $usuario->selectAll('nickname', 'DESC');
  echo '<pre>';
  print_r($users);
  echo '</pre>';

  // Listando todos los datos de la tabla Usuario
  // ordenandolos por la columna nickname
  // de forma ascendente
  // y mostrando la página 2 teniendo en cuenta que fuese de 10 en 10 (11 = donde empezar a mostrar, 10 = cuantos debo de mostrar a partir del 11)
  $users = $usuario->selectAll('nickname', 'ASC', 11, 10);
  echo '<pre>';
  print_r($users);
  echo '</pre>';

  // Listando todos los datos de la tabla Usuario
  // ordenandolos por la columna nickname
  // de forma ascendente
  // y mostrando la página 3 teniendo en cuenta que fuese de 10 en 10 (21 = donde empezar a mostrar, 10 = cuantos debo de mostrar a partir del 11)
  // pero el resultado necesito enviarlo a una solicitud AJAX
  $users = $usuario->selectAll('nickname', 'ASC', 21, 10, null);
  echo json_encode($users);

  // Necesito buscar un registro por el ID 69
  $user = $usuario->selectById(69);
  echo $user->getRolId(); // imprime el id del rol
  echo $user->getRolId(true); // imprime el nombre del rol asignado
}
catch (PDOException $exc)
{
  echo '<pre>';
  echo $exc->getCode() . "\n";
  echo $exc->getMessage() . "\n";
  echo $exc->getLine() . "\n";
  echo $exc->getFile() . "\n";
  echo $exc->getTraceAsString();
  echo '</pre>';
}
