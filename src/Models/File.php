<?php

namespace Worksection\Api\Models;

class File extends Model
{

	public int $id = 0;
	public string $page = '';
	public string $name = '';
	public int $size = 0;
	public string $date_added = '';
	public ?User $user_from = null;
}
