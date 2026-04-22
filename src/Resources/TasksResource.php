<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\CustomField;
use Worksection\Api\Models\Tag;
use Worksection\Api\Models\TagGroup;
use Worksection\Api\Models\Task;
use Worksection\Api\Models\UploadedFile;
use Worksection\Api\Resource;

class TasksResource extends Resource
{
	/**
	 * Get all tasks across all projects.
	 * Optional params: extra (text,html,files,comments,relations,subtasks,archive), filter=active
	 *
	 * @return Task[]
	 */
	public function list(int $projectId = 0, array $params = []): array
	{
		if ($projectId) {
			return array_map(fn(array $i) => Task::fromArray($i), $this->callAction('get_tasks', ['id_project' => $projectId] + $params));
		} else {
			return array_map(fn(array $i) => Task::fromArray($i), $this->callAction('get_all_tasks', $params));
		}
	}

	/**
	 * Get a single task by ID.
	 * Optional params: extra (text, html, files, comments, relations, subtasks, subscribers, custom_fields)
	 */
	public function get(int $taskId, array $params = []): Task
	{
		$data = $this->callAction('get_task', ['id_task' => $taskId] + $params);
		return Task::fromArray($data);
	}

	/**
	 * Create a new task.
	 * Optional params: id_parent, email_user_from, email_user_to, priority, text, todo,
	 *   datestart, dateend, subscribe, mention, hidden, max_time, max_money, tags, files
	 */
	public function create(int $projectId, string $title, array $params = []): Task
	{
		if (isset($params['files']) && is_array($params['files'])) {
			$params['files'] = array_map(
			  function(UploadedFile $f) { return $f->toArray(); },
			  array_filter($params['files'], function(UploadedFile $f) { return $f->id !== ''; })
			);
		} else {
			unset($params['files']);
		}

		$data = $this->callAction('post_task', ['id_project' => $projectId, 'title' => $title] + $params);
		return Task::fromArray($data);
	}

	/**
	 * Update an existing task.
	 * Optional params: email_user_to, priority, title, datestart, dateend, dateclosed,
	 *   max_time, max_money, tags
	 */
	public function update(int $taskId, array $params = []): Task
	{
		$data = $this->callAction('update_task', ['id_task' => $taskId] + $params);
		return Task::fromArray($data);
	}

	/**
	 * Mark a task as complete.
	 */
	public function complete(int $taskId): void
	{
		$this->callAction('complete_task', ['id_task' => $taskId]);
	}

	/**
	 * Reopen a completed task.
	 */
	public function reopen(int $taskId): void
	{
		$this->callAction('reopen_task', ['id_task' => $taskId]);
	}

	/**
	 * Search tasks by parameters. At least one of: id_project, id_task,
	 * email_user_from, email_user_to, filter must be provided.
	 * Optional params: status (active/done)
	 *
	 * @return Task[]
	 */
	public function search(array $params): array
	{
		return array_map(fn(array $i) => Task::fromArray($i), $this->callAction('search_tasks', $params));
	}

	/**
	 * Subscribe a member to a task.
	 */
	public function subscribe(int $taskId, string $email): void
	{
		$this->callAction('subscribe', ['id_task' => $taskId, 'email_user' => $email]);
	}

	/**
	 * Unsubscribe a member from a task.
	 */
	public function unsubscribe(int $taskId, string $email): void
	{
		$this->callAction('unsubscribe', ['id_task' => $taskId, 'email_user' => $email]);
	}

	/**
	 * Get all task tags.
	 * Optional params: group, type, access
	 *
	 * @return Tag[]
	 */
	public function tags(array $params = []): array
	{
		return array_map(fn(array $i) => Tag::fromArray($i), $this->callAction('get_task_tags', $params));
	}

	/**
	 * Create new task tags
	 *
	 * @return Tag[]
	 */
	public function createTags(string $group, ...$names): array
	{
		return array_map(fn(array $i) => Tag::fromArray($i), $this->callAction('add_task_tags', ['group' => $group, 'title' => implode(',', $names)]));
	}

	/**
	 * Add or remove task tags on a task.
	 */
	public function updateTags(int $taskId, array $addIds = [], array $removeIds = []): void
	{
		$this->callAction('update_task_tags', [
		  'id_task' => $taskId,
		  'plus' => implode(',', $addIds),
		  'minus' => implode(',', $removeIds),
		]);
	}

	/**
	 * Get all task tag groups.
	 * Optional params: type, access
	 *
	 * @return TagGroup[]
	 */
	public function tagGroups(array $params = []): array
	{
		return array_map(fn(array $i) => TagGroup::fromArray($i), $this->callAction('get_task_tag_groups', $params));
	}

	/**
	 * Create a new task tag group.
	 *
	 * @param string $access "public" or "private"
	 */
	public function createTagGroup(string $title, string $access): TagGroup
	{
		$data = $this->callAction('add_task_tag_groups', [
		  'title' => $title,
		  'type' => 'label',
		  'access' => $access,
		]);

		return TagGroup::fromArray($data[0] ?? []);
	}

	/**
	 * Get all project custom fields.
	 *
	 * @return CustomField[]
	 */
	public function customFields(): array
	{
		return array_map(fn(array $i) => CustomField::fromArray($i), $this->callAction('get_task_custom_fields'));
	}
}
