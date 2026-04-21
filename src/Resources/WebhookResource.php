<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Webhook;
use Worksection\Api\Resource;

class WebhookResource extends Resource
{
	/**
	 * Get webhooks.
	 *
	 * @return Webhook[]
	 */
	public function list(): array
	{
		return array_map(fn(array $i) => Webhook::fromArray($i), $this->callAction('get_webhooks'));
	}

	/**
	 * Create a webhook.
	 * Optional params: projects, http_user, http_pass
	 *
	 * @param string $url
	 * @param string[] $events Available events: post_task, post_comment, post_project, update_task, update_comment, update_project, delete_task, delete_comment, close_task
	 * @param array $params
	 * @return int
	 */
	public function create(string $url, array $events, array $params): int
	{
		$data = $this->callAction('add_webhook', [
		  'url' => $url, 'events' => implode(',', $events)
	  	] + $params);

		return $data['id'] ?? 0;
	}

	public function delete(int $id): void
	{
		$this->callAction('delete_webhook', ['id' => $id]);
	}
}
