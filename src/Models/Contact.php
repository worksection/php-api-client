<?php

namespace Worksection\Api\Models;

class Contact extends Model
{

	  public int $id = 0;
	  public string $first_name = '';
	  public string $last_name = '';
	  public string $name = '';
	  public string $title = '';
	  public string $url = '';
	  public string $group = '';
	  public string $email = '';
	  public ?string $phone = null;
	  public ?string $phone2 = null;
	  public ?string $phone3 = null;
	  public ?string $address = null;
	  public ?string $address2 = null;
	  public ?array $services = null;
	  public ?string $contacts = null;
	  public ?string $data_added = null;

}
