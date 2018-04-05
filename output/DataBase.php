<?php

namespace demo\model;

use NogalSE\DataSource;

class DataBase extends DataSource
{

  public function __construct($config)
  {
    parent::__construct($config);
  }
}
