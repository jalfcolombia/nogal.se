<?php

$skeleton = <<<BLOCK
<?php

namespace $app\\model;

use NogalSE\\DataSource;

class $DataBase extends DataSource
{

  public function __construct(array \$config = array())
  {
    parent::__construct(\$config);
  }

}

BLOCK;
