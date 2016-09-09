<?php
namespace hedronium\Jables\exceptions;

class SchemaException extends \Exception
{
  public $errors = [];

  public function __construct($errors)
  {
    $this->errors = $errors;

    $str = '';

    foreach ($errors as $error) {
      $str .= "-----#!#!#!#!#!#!#!#!----\n";
      $str .= 'Table    : '.$error['table']."\n";
      $str .= 'Path     : '.$error['path']."\n";
      $str .= 'Property : '.$error['property']."\n";
      $str .= 'Error    : '.$error['message']."\n";
      $str .= "\n";
    }

    parent::__construct($str);
  }
}
