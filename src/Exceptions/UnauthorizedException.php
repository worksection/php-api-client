<?php

namespace Worksection\Api\Exceptions;

use Throwable;

class UnauthorizedException extends ResponseException
{

	public function __construct(string $message = "Unauthorized", int $code = 0, ?Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}

}
