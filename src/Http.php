<?php

namespace Worksection\Api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Http
{
	private \GuzzleHttp\Client $client;

	public function __construct(\GuzzleHttp\Client $client)
	{
		$this->client = $client;
	}

	public function send(RequestInterface $request, array $options = []): ResponseInterface
	{
		return $this->client->send($request, $options);
	}

	public static function create(?string $base_uri = null): self
	{
		$client = new \GuzzleHttp\Client([
		  'base_uri' => $base_uri,
		  'timeout' => 30,
		  'http_errors' => false,
		]);

		return new static($client);
	}
}
