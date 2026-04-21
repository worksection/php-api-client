<?php

namespace Worksection\Api\Models;

class Task extends Model
{

	public int $id = 0;
	public string $name = '';
	public string $text = '';
	public string $page = '';
	public string $status = '';
	public int $priority = 0;
	public ?User $user_from = null;
	public ?User $user_to = null;
	public ?Project $project = null;
	public string $date_added = '';
	public string $date_start = '';
	public string $date_end = '';
	public string $time_end = '';
	public string $max_time = '';
	public string $max_money = '';
	public array $tags = [];
	public array $custom_fields = [];

	public static function fromArray(array $data): self
	{
		if (isset($data['custom_fields'])) {
			foreach ($data['custom_fields'] as &$value) $value = CustomValue::fromArray($value);
		}

		return parent::fromArray($data);
	}
}
