<?php

// NogalSE generate:/home/julian/NetBeansProjects/NogalORM/database.yml output:/home/julian/NetBeansProjects/NogalORM/output/
$help = <<<HELP

The NogalSE project seeks to implement a way of working with the DAO development pattern in PHP7 for SENA apprentices in a basic way and also for anyone else who wants to make use of this library under MIT license.
  
The way to use the NogalSE command is as follows:
  
  ./vendor/nogal.se/bin/NogalSE generate:[FILE PATH YAML] output:[DIRECTORY PATH TARGET]
  
Example:
  
  ./vendor/nogal.se/bin/NogalSE generate:/var/www/database.yml output:/var/www/model/


HELP;

echo $help;