<?php

namespace Worksection\Api\Resources;

use Worksection\Api\Models\Contact;
use Worksection\Api\Models\ContactGroup;
use Worksection\Api\Resource;

class ContactsResource extends Resource
{
	/**
	 * Get all contacts.
	 *
	 * @return Contact[]
	 */
	public function list(): array
	{
		return array_map(fn(array $i) => Contact::fromArray($i), $this->callAction('get_contacts'));
	}

	/**
	 * Create a new contact.
	 * Optional params: title, group, phone, phone2, phone3, phone4, address, address2
	 */
	public function create(string $email, string $name, array $params = []): Contact
	{
		return Contact::fromArray($this->callAction('add_contact', ['email' => $email, 'name' => $name] + $params));
	}

	/**
	 * Get all contact groups.
	 *
	 * @return ContactGroup[]
	 */
	public function groups(): array
	{
		return array_map(fn(array $i) => ContactGroup::fromArray($i), $this->callAction('get_contact_groups'));
	}

	/**
	 * Create a new contact group.
	 */
	public function createGroup(string $title): ContactGroup
	{
		return ContactGroup::fromArray($this->callAction('add_contact_group', ['title' => $title]));
	}
}
