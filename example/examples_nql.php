<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

use NogalSE\NQL;

try {
  $nql = new NQL('pgsql');
  $nql->select('id, name, created_at')
    ->from('usuario')
    ->where('deleted_at IS NULL')
    ->condition(NQL::_AND, 'created_at BETWEEN :facha1 AND :fecha2')
    ->condition(NQL::_OR, 'actived', false)
    ->limit(0)
    ->offset(10)
    ->orderBy('id', NQL::ASC);
  echo $nql;

  $nql->insert('usuario', 'nickname, password')
    ->Values(':nickname, :password');
  echo "<br>\n" . $nql;

  $nql->update('usuario')
    ->set('nickname = :nickname, password = :password')
    ->where('id = :id')
    ->condition(NQL::_AND, 'deleted_at IS NULL');
  echo "<br>\n" . $nql;

  $nql->update('usuario')
    ->set('nickname, password', false)
    ->where('id', false)
    ->condition(NQL::_AND, 'deleted_at IS NULL');
  echo "<br>\n" . $nql;

  $nql->delete('usuario')
    ->where('id', false)
    ->condition(NQL::_AND, 'deleted_at IS NULL');
  echo "<br>\n" . $nql;
}
catch (\Exception $exc) {
  echo $exc->getMessage();
}
