<?php

$Table .= 'Base';

$skeleton = <<<EOT
<?php

namespace $app\\Model\\Base;

use $app\\model\\$DataBase;
use NogalSE\\Interfaces\\ITable;

/**
 * [AGREGAR DESCRIPCIÓN DE LA CLASE]
 */
class $Table extends $DataBase implements ITable
{

  const SEQUENCE = '$sequence';$length
$private

  public function __construct(array \$config = array())
  {
    parent::__construct(\$config);$default
  }
$getter$setter
$selectAll

$selectById

$save

$update

$delete

}

EOT;
