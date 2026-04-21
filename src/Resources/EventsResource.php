<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Resource;

class EventsResource extends Resource
{

	/**
	 * Get recent events/activity log.
	 * @param string $period Period string (e.g. '7d', '30d')
	 */
	public function list(string $period, int $projectId = 0, array $params = []): array
	{
		if ($projectId) $params['id_project'] = $projectId;

		return $this->callAction('get_events', ['period' => $period] + $params);
	}

}
