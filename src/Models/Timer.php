<?php

namespace Worksection\Api\Models;

class Timer extends Model
{

	public int $id = 0;
	public string $time = '';
	public string $date_started = '';
	public ?User $user_from = null;
	public ?Task $task = null;

}
