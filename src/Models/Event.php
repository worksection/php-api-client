<?php

namespace Worksection\Api\Models;

class Event
{

	public string $action = '';
	public string $date_added = '';
	public ?EventObject $object = null;
	public ?User $user_from = null;
	public array $new = [];
	public array $old = [];

	public function isPost(): bool { return $this->action === EventActionEnum::Post; }
	public function isUpdate(): bool { return $this->action === EventActionEnum::Update; }
	public function isDelete(): bool { return $this->action === EventActionEnum::Delete; }
	public function isTaskClose(): bool { return $this->action === EventActionEnum::TaskClose; }
	public function isTaskReopen(): bool { return $this->action === EventActionEnum::TaskReopen; }

}
