<?php

namespace Worksection\Api\Models;

class ProjectGroup extends Model
{
	public int $id = 0;
	public string $title = '';
	public string $type = '';
	public bool $client = false;
}
