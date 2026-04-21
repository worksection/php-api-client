<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Comment;
use Worksection\Api\Resource;

class CommentsResource extends Resource
{
	/**
	 * Get comments for a task.
	 * Optional params: extra=files
	 *
	 * @return Comment[]
	 */
	public function list(int $taskId, array $params = []): array
	{
		return array_map(
			fn(array $i) => Comment::fromArray($i),
			$this->callAction('get_comments', ['id_task' => $taskId] + $params)
		);
	}

	/**
	 * Create a comment on a task.
	 *
	 * Optional params: email_user_from, hidden, mention
	 */
	public function create(int $taskId, string $text, array $params = []): Comment
	{
		return Comment::fromArray(
		  $this->callAction('post_comment', ['id_task' => $taskId, 'text' => $text] + $params)
		);
	}
}
