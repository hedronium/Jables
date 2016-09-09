<?php
namespace hedronium\Jables\exceptions;

class ParseException extends \Exception
{
  public $table = '';
  public $exception_message = '';

  public function __construct($table, $path, $exception_message)
  {
    $this->table = $table;
    $this->exception_message = $exception_message;

    $str = '';
    $str .= 'Table : '.$table. "\n";
    $str .= 'Path  : '.$path. "\n";
    $str .= 'Error : '.$exception_message. "\n";

    parent::__construct($str);
  }
}
