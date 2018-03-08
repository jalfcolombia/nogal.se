<?php

namespace NogalSE\intf;

interface ITask
{

    public function __construct($value, $params);

    public function main();

}
