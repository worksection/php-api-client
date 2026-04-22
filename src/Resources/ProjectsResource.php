<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\CustomField;
use Worksection\Api\Models\Project;
use Worksection\Api\Models\ProjectGroup;
use Worksection\Api\Models\Tag;
use Worksection\Api\Models\TagGroup;
use Worksection\Api\Resource;

class ProjectsResource extends Resource
{
	/**
	 * Get all projects.
	 * Optional params: filter (active, pending, archived), extra (text, html, options, users)
	 *
	 * @return Project[]
	 */
	public function list(array $params = []): array
	{
		return array_map(fn(array $i) => Project::fromArray($i), $this->callAction('get_projects', $params));
	}

	/**
	 * Get a single project by ID.
	 * Optional params: extra (text, html, options, users)
	 */
	public function get(int $projectId, array $params = []): Project
	{
		$data = $this->callAction('get_project', ['id_project' => $projectId] + $params);
		return Project::fromArray($data);
	}

	/**
	 * Create a new project.
	 * Optional params: email_user_from, email_manager, email_user_to, members, text,
	 *   company, datestart, dateend, extra, max_time, max_money, tags, options.*
	 */
	public function create(string $title, array $params = []): Project
	{
		$data = $this->callAction('post_project', ['title' => $title] + $params);
		return Project::fromArray($data);
	}

	/**
	 * Update an existing project.
	 * Optional params: email_manager, email_user_to, members, title, datestart, dateend,
	 *   extra, max_time, max_money, tags, options.*
	 */
	public function update(int $projectId, array $params = []): Project
	{
		$data = $this->callAction('update_project', ['id_project' => $projectId] + $params);
		return Project::fromArray($data);
	}

	/**
	 * Archive (close) a project.
	 */
	public function close(int $projectId): void
	{
		$this->callAction('close_project', ['id_project' => $projectId]);
	}

	/**
	 * Activate (unarchive) a project.
	 */
	public function activate(int $projectId): void
	{
		$this->callAction('activate_project', ['id_project' => $projectId]);
	}

	/**
	 * Add members to a project.
	 * @param array $members Array of member emails
	 */
	public function addMembers(int $projectId, array $members): void
	{
		$this->callAction('add_project_members', ['id_project' => $projectId, 'members' => $members]);
	}

	/**
	 * Remove members from a project.
	 * @param array $members Array of member emails
	 */
	public function removeMembers(int $projectId, array $members): void
	{
		$this->callAction('delete_project_members', ['id_project' => $projectId, 'members' => $members]);
	}

	/**
	 * Get all project groups (folders).
	 *
	 * @return ProjectGroup[]
	 */
	public function groups(): array
	{
		return array_map(fn(array $i) => ProjectGroup::fromArray($i), $this->callAction('get_project_groups'));
	}

	/**
	 * Create a new project group (folder).
	 */
	public function createGroup(string $title): ProjectGroup
	{
		return ProjectGroup::fromArray($this->callAction('add_project_group', ['title' => $title]));
	}

	/**
	 * Get all project tags.
	 * Optional params: group, type, access
	 *
	 * @return Tag[]
	 */
	public function tags(array $params = []): array
	{
		return array_map(fn(array $i) => Tag::fromArray($i), $this->callAction('get_project_tags', $params));
	}

	/**
	 * Create new project tags.
	 *
	 * @return Tag[]
	 */
	public function createTags(string $group, ...$names): array
	{
		return array_map(fn(array $i) => Tag::fromArray($i), $this->callAction('add_project_tags', ['group' => $group, 'title' => implode(',', $names)]));
	}

	/**
	 * Add or remove project tags on a project.
	 */
	public function updateTags(int $projectId, array $addIds = [], array $removeIds = []): void
	{
		$this->callAction('update_project_tags', [
		  'id_project' => $projectId,
		  'plus' => implode(',', $addIds),
		  'minus' => implode(',', $removeIds),
		]);
	}

	/**
	 * Get all project tag groups.
	 * Optional params: type, access
	 *
	 * @return TagGroup[]
	 */
	public function tagGroups(array $params = []): array
	{
		return array_map(fn(array $i) => TagGroup::fromArray($i), $this->callAction('get_project_tag_groups', $params));
	}

	/**
	 * Create a new project tag group.
	 * @param string $access "public" or "private"
	 */
	public function createTagGroup(string $title, string $access): TagGroup
	{
		$data = $this->callAction('add_project_tag_groups', [
		  'title' => $title,
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
		return array_map(fn(array $i) => CustomField::fromArray($i), $this->callAction('get_project_custom_fields'));
	}
}
