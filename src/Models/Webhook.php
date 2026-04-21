<?php

namespace Worksection\Api\Models;

class Webhook extends Model
{
	public int $id = 0;
	public string $url = '';
	public string $events = '';
	public string $status = '';
	public string $projects = '';
}
