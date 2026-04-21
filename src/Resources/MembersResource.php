<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Schedule;
use Worksection\Api\Models\User;
use Worksection\Api\Models\UserGroup;
use Worksection\Api\Resource;

class MembersResource extends Resource
{
	/**
	 * Get all account members.
	 *
	 * @return User[]
	 */
	public function list(): array
	{
		return array_map(fn(array $i) => User::fromArray($i), $this->callAction('get_users'));
	}

	/**
	 * Invite a new member to the account.
	 * Optional params: first_name, last_name, title, group, department, role
	 */
	public function create(string $email, array $params = []): User
	{
		$data = $this->callAction('add_user', ['email' => $email] + $params);
		return User::fromArray($data);
	}

	/**
	 * Get all member groups (teams).
	 *
	 * @return UserGroup[]
	 */
	public function groups(): array
	{
		return array_map(fn(array $i) => UserGroup::fromArray($i), $this->callAction('get_user_groups'));
	}

	/**
	 * Create a new member group (team).
	 * Optional params: client (1 if a contact group)
	 */
	public function createGroup(string $title, array $params = []): UserGroup
	{
		return UserGroup::fromArray($this->callAction('add_user_group', ['title' => $title] + $params));
	}

	/**
	 * TODO: Action is invalid
	 *
	 * Get a member work schedule.
	 * Optional params: users (array of emails), datestart, dateend
	 *
	 * @return Schedule[]
	 */
	public function schedule(array $params = []): array
	{
		$arr = $this->callAction('get_users_schedule', $params);

		return array_combine(
		  array_column($arr, 'id'),
		  array_map(fn(array $i) => Schedule::fromArray($i), $arr),
		);
	}

	/**
	 * Update member work schedules.
	 * @param array $data Schedule data keyed by user email
	 */
	public function updateSchedule(array $data): void
	{
		$this->callAction('update_users_schedule', ['data' => $data]);
	}
}
