<?php

try {

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  if (isset($argv[1]) === true and $argv[1] === '--help') {
    require_once 'help.php';
  }
  else if (isset($argv[1]) === true) {
    $data  = explode(':', $argv[1], 2);
    unset($argv[0], $argv[1]);
    $class = ucfirst($data[0]);
    require_once __DIR__ . '/lib/ITask.php';
    require_once __DIR__ . '/task/' . $class . '.php';
    $class = 'NogalSE\\Task\\' . $class;
    $task  = new $class($data[1], $argv);
    $task->main();
  }
  else {
    require_once 'version.php';
    echo "\n\033[33m NogalSE version " . NOGALSE_VERSION . "\n\n  ";
  }
}
catch (Exception $exc) {
  echo $exc->getMessage();
}
