<?php

namespace Worksection\Api\Models;

class Comment extends Model
{
	  public int $id = 0;
	  public string $text = '';
	  public string $date_added = '';
	  public ?User $user_from = null;
}
