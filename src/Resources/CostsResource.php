<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Cost;
use Worksection\Api\Models\CostTotal;
use Worksection\Api\Resource;

class CostsResource extends Resource
{
	/**
	 * Get expense records.
	 * Optional params: id_project, id_task, datestart, dateend, is_timer, filter
	 *
	 * @return Cost[]
	 */
	public function list(array $params = []): array
	{
		return array_map(fn(array $i) => Cost::fromArray($i), $this->callAction('get_costs', $params));
	}

	/**
	 * Get expense totals/summary.
	 * Optional params: id_project, id_task, datestart, dateend, is_timer, filter,
	 *   extra (projects, tasks, tasks_top_level)
	 */
	public function total(array $params = []): CostTotal
	{
		$arr = $this->callAction('get_costs_total', $params);

		$data = $arr['total'] ?? [];
		$data['projects'] = $arr['projects'] ?? [];

		return CostTotal::fromArray($data);
	}

	/**
	 * Create an expense record.
	 * Requires id_task and at least one of: time, money.
	 * Optional params: email_user_from, is_rate, comment, date (DD.MM.YYYY)
	 *
	 * @return int cost ID
	 */
	public function create(int $taskId, array $params): int
	{
		$data = $this->callAction('add_costs', ['id_task' => $taskId] + $params);

		return $data['id'] ?? 0;
	}

	/**
	 * Update an expense record.
	 * Optional params: time, money, is_rate, comment, date (DD.MM.YYYY)
	 */
	public function update(int $costsId, array $params = []): void
	{
		$this->callAction('update_costs', ['id_costs' => $costsId] + $params);
	}

	/**
	 * Delete an expense record.
	 */
	public function delete(int $costsId): void
	{
		$this->callAction('delete_costs', ['id_costs' => $costsId]);
	}
}
