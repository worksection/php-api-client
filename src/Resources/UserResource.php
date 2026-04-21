<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Timer;
use Worksection\Api\Models\User;
use Worksection\Api\Resource;

class UserResource extends Resource
{
	public function profile(): User {
		return User::fromArray($this->callUserAction('me'));
	}

	public function timer(): ?Timer {
		$arr = $this->callUserAction('get_my_timer');
		return count($arr) === 1 ? Timer::fromArray($arr[0]) : null;
	}

	public function start_timer(int $taskId): void {
		$this->callUserAction('start_my_timer', [ 'id_task' => $taskId ]);
	}

	public function stop_timer(string $comment = ''): void {
		$this->callUserAction('stop_my_timer', [ 'comment' => $comment ]);
	}

	public function discard_timer(): void {
		$this->callUserAction('delete_my_timer');
	}
}
