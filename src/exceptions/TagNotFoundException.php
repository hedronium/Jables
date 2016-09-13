<?php
namespace hedronium\Jables\exceptions;

class TagNotFoundException extends \Exception
{
  public $tag = '';

  public function __construct($tag)
  {
    $this->tag = $tag;

    parent::__construct("The tag '$tag' was not found.");
  }
}
