<?php

namespace Worksection\Api\Models;

/**
 * @property CostTask[] $tasks
 */
class CostProject extends Model
{
	  public int $id = 0;
	  public string $name = '';
	  public string $page = '';
	  public string $time = '';
	  public string $money = '';
	  public array $monthly = []; // ["2024-04": { "time": "2:00", "money": "20.00" }],
	  public array $tasks = [];

	public static function fromArray(array $data): self
	{
		return parent::fromArray([
		  	'tasks' => isset($data['tasks'])
			  ? array_combine(array_column($data['tasks'], 'id'), array_map(fn(array $i) => CostTask::fromArray($i), $data['tasks']))
			  : [],
	  	] + $data);
	}
}
