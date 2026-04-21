<?php

namespace Worksection\Api\Models;

class CustomField extends Model
{
	  public int $id = 0;
	  public string $type = '';
	  public string $name = '';
	  public string $descr = '';
	  public array $options = [];
}
