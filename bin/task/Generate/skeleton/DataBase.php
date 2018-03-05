<?php

$skeleton = <<<BLOCK
<?php

namespace NogalSE;

use NogalSE\\DataSource;

class $DataBase extends DataSource
{

  public function __construct(\$config)
  {
    parent::__construct(\$config);
  }
}

BLOCK;
