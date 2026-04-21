<?php

namespace Worksection\Api\Models;

/**
 * @property Project[] $projects
 */
class CostTotal extends Model
{

	  public string $time = '';
	  public string $money = '';
	  public array $projects = [];

	public static function fromArray(array $data): self
	{
		return parent::fromArray([
		  'projects' => isset($data['projects'])
			? array_combine(array_column($data['projects'], 'id'), array_map(fn(array $i) => CostProject::fromArray($i), $data['projects']))
		  	: []
	  	] + $data);
	}
}
