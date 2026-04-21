<?php

namespace Worksection\Api\Models;

class Project extends Model
{

	public int $id = 0;
	public string $name = '';
	public string $page = '';
	public string $status = '';
	public string $date_added = '';
	public string $date_start = '';
	public string $date_end = '';
	public string $max_time = '';
	public string $max_money = '';
	public array $tags = [];
	public array $options = [];
	public array $users = [];
	public array $custom_fields = [];

	public static function fromArray(array $data): self
	{
		if (isset($data['users'])) {
			foreach ($data['users'] as &$value) $value = ProjectUser::fromArray($value);
		}
		if (isset($data['custom_fields'])) {
			foreach ($data['custom_fields'] as &$value) $value = CustomValue::fromArray($value);
		}

		return parent::fromArray($data);
	}
}
