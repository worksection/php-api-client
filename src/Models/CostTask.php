<?php

namespace Worksection\Api\Models;

class CostTask extends Model
{

	  public int $id = 0;
	  public string $name = '';
	  public string $page = '';
	  public string $status = '';
	  public int $priority = 0;
	  public string $time = '';
	  public string $money = '';


	public static function fromArray(array $data): self
	{
		return parent::fromArray([
		  	'tasks' => 'array'
	  	] + $data);
	}
}
