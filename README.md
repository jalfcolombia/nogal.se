# Nogal (Student Edition)
It is a tiny implementation of the PHP7 ORM programming model based on DAO (Data Access Object) for use in the learning environments of the National Learning Service - SENA in Colombia.
## Installation
For you to be able to use Nogal SE in your project, you must have the [Composer](https://getcomposer.org/) tool installed.
```console
composer require sena/nogal.se
```
## Estructura del modelo
Nogal (Edición Estudiante) propone la siguiente estructura.
```console
+--------------------
| model/
|-- base/
|---- UserBase.php
|-- User.php
+--------------------
```
Para poder explicar lo anterior debemos partir de que usaremos la siguiente estructura SQL para la tabla _User_.
```sql
CREATE TABLE DbUser (
    Id int,
    Nick varchar(20),
    Password varchar(32),
    Actived boolean DEFAULT true,
    Created_at timestamp DEFAULT NOW()
);
```
### Archivo _User.php_
El siguiente código sería entonces el contenido del archivo _User.php_
```php
<?php

namespace MyApp\model;

use MyApp\model\base\UserBase;

class User extends UserBase
{
  
}
```
En estos archivos se ubicaría la lógica del negocio que tenga que ver con la tabla _User_, por ejemplo: necesito consultar el id de un usuario.
```php
 <?php
 
 namespace MyApp\model;
 
 use MyApp\model\base\UserBase;
 
 class User extends UserBase
 {
   public function SearchIdByUser()
   {
     $sql  = 'SELECT Id FROM DbUser WHERE Nick = :nick';
     $this->SetDbParam(':nick', $this->getNick(), \PDO::PARAM_STR);
     $data = $this->Query($sql);
     if (count($data) > 0) {
       return $data;
     }
     else {
       return false;
     }
   }
 }
```
 ### Archivo _UserBase.php_
 Este archivo contiene la estructura base de la tabla _User_ y podemos expresarla de la siguiente forma:
```php
```