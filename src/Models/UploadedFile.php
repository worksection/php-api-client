<?php

namespace Worksection\Api\Models;

class UploadedFile extends Model
{
	public string $id = '';
	public string $ext = '';
	public string $name = '';
	public ?string $icon = null;
	public ?string $error = null;
}
