<?php

$Table .= 'Base';

$skeleton = <<<EOT
<?php

namespace NogalSE\\Table\\Base;

use NogalSE\\$DataBase;
use NogalSE\\Interfaces\\ITable;

/**
 * [AGREGAR DESCRIPCIÓN DE LA CLASE]
 */
class $Table extends $DataBase implements ITable
{

  const SEQUENCE = '$sequence';$length
$private

  public function __construct(\$config)
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
