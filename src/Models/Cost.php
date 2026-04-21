<?php

namespace Worksection\Api\Models;

class Cost extends Model
{
	  public int $id = 0;
	  public string $comment = '';
	  public string $time = '';
	  public string $money = '';
	  public string $date = '';
	  public int $is_timer = 0;
	  public ?User $user_from = null;
	  public ?Task $task = null;
}
