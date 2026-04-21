<?php

namespace Worksection\Api\Models;

class User extends Model
{

	public int $id = 0;
	public string $first_name = '';
	public string $last_name = '';
	public string $name = '';
	public string $title = '';
	public ?string $rate = null;
	public string $avatar = '';
	public string $group = '';
	public string $department = '';
	public string $role = '';
	public string $email = '';
	public ?string $phone = null;
	public ?string $phone2 = null;
	public ?string $phone3 = null;
	public ?string $address = null;
	public ?string $address2 = null;

	public function isOwner(): bool { return $this->role === UserRoleEnum::Owner; }
	public function isAdmin(): bool { return $this->role === UserRoleEnum::Admin; }
	public function isTeamAdmin(): bool { return $this->role === UserRoleEnum::TeamAdmin; }
	public function isDepartmentAdmin(): bool { return $this->role === UserRoleEnum::DepartmentAdmin; }
	public function isUser(): bool { return $this->role === UserRoleEnum::User; }
	public function isGuest(): bool { return $this->role === UserRoleEnum::Guest; }
	public function isReader(): bool { return $this->role === UserRoleEnum::Reader; }
}
