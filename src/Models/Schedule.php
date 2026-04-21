<?php

namespace Worksection\Api\Models;

/**
 * @property int $id user`s ID
 * @property array $dates ["2021-01-04" => "vacation", "2021-03-13": "workday", "2021-10-15": "sick-leave", "2021-12-24": "vacation",]
 */
class Schedule extends Model
{
	public int $id = 0;
	public string $email = '';
	public string $name = '';
	public string $group = '';
	public string $department = '';
	public array $dates = [];
}
