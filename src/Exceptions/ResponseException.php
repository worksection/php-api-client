<?php

namespace Worksection\Api\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Worksection\Api\Exception;

class ResponseException extends Exception
{

	public static function fromResponse(ResponseInterface $response): self
	{
		$data = [];

		if ($body = $response->getBody()->getContents()) {
			$data = json_decode($body, true, 100) ?: [];
		}

		$message = $data['errorDescription'] ?? $data['message'] ?? null;

		if (!$message) {
			if ($response->getStatusCode() == 401) {
				$message = 'Unauthorized';
			} else {
				$message = 'Failed to fetch data from API';
			}
		}

		return new static($message, $response->getStatusCode());
	}

}
