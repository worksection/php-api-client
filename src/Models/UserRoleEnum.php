<?php

namespace Worksection\Api\Models;

class UserRoleEnum
{
	const Owner = 'owner';
	const Admin = 'account admin';
	const TeamAdmin = 'team admin';
	const DepartmentAdmin = 'department admin';
	const User = 'user';
	const Guest = 'guest';
	const Reader = 'reader';
}
