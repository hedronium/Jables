<?php
namespace hedronium\Jables\exceptions;

class NameCollisionException extends \Exception
{
  public $file_1 = '';
  public $file_2 = '';

  public function __construct($file_1, $file_2)
  {
    $this->file_1 = $file_1;
    $this->file_2 = $file_2;

    parent::__construct("Two files result in the same table name:\n$file_1\n$file_2");
  }
}
