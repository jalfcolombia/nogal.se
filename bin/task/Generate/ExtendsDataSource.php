<?php

namespace NogalSE\Task\Generate;

class ExtendsDataSource
{

<<<<<<< HEAD
  public function main($DataBase, $output, $app)
  {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'skeleton' . DIRECTORY_SEPARATOR . 'DataBase.php';
    $file = fopen($output . $DataBase . '.php', 'w');
    fwrite($file, $skeleton);
    fclose($file);
  }
=======
    public function main($DataBase, $output, $app)
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'skeleton' . DIRECTORY_SEPARATOR . 'DataBase.php';
        $file = fopen($output . $DataBase . '.php', 'w');
        fwrite($file, $skeleton);
        fclose($file);
    }
>>>>>>> 0.0.3

}
