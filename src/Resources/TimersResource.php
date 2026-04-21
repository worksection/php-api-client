<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Timer;
use Worksection\Api\Resource;

class TimersResource extends Resource
{

	/**
	 * @return Timer[]
	 */
	public function all(): array
	{
		return array_map(fn (array $i) => Timer::fromArray($i), $this->callAdminAction('get_timers'));
	}

	public function stop($timerId): void
	{
		$this->callAdminAction('stop_timer', ['timer' => $timerId]);
	}
}
