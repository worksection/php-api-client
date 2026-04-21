<?php

namespace Worksection\Api;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Worksection\Api\Exceptions\ResponseException;

class Oauth
{

	const AUTHORIZE_URL = 'https://worksection.com/oauth2/authorize';
	const ACCESS_TOKEN_URL = 'https://worksection.com/oauth2/token';
	const REFRESH_TOKEN_URL = 'https://worksection.com/oauth2/refresh';

	public const AVAILABLE_SCOPES = [
	  'projects_read', 'projects_write', 'tasks_read', 'tasks_write', 'costs_read', 'costs_write',
	  'tags_read', 'tags_write', 'comments_read', 'comments_write', 'files_read', 'files_write',
	  'users_read', 'users_write', 'contacts_read', 'contacts_write', 'administrative'
	];

	protected string $authorizationUrl;
	protected string $tokenUrl;
	protected string $refreshUrl;

	protected string $clientId;
	protected string $clientSecret;
	protected string $redirectUri;
	protected array $scope;

	private Http $httpClient;

	public function __construct(
	  array $config = []
	) {
		$this->authorizationUrl = $config['auth_url'] ?? self::AUTHORIZE_URL;
		$this->tokenUrl = $config['token_url'] ?? self::ACCESS_TOKEN_URL;
		$this->refreshUrl = $config['refresh_url'] ?? self::REFRESH_TOKEN_URL;

		$this->setClientCredentials($config['client_id'] ?? '', $config['client_secret'] ?? '');
		$this->setRedirectUri($config['redirect_uri'] ?? '');

		if (isset($config['scope']) && is_array($config['scope'])) {
			$this->setScope($config['scope']);
		} else {
			$this->setScope(['projects_read', 'tasks_read', 'comments_read', 'files_read', 'users_read', 'contacts_read']);
		}

		$this->httpClient = Http::create();
	}

	public function setClientCredentials(string $clientId, string $clientSecret): static
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		return $this;
	}

	public function setScope(array $scope): static
	{
		$this->scope = array_filter($scope, fn(string $scope) => in_array($scope, self::AVAILABLE_SCOPES));
		return $this;
	}

	public function setRedirectUri(string $redirectUri): static
	{
		$this->redirectUri = $redirectUri;
		return $this;
	}

	public function getAuthorizationUrl(string $state): string
	{
		if (empty($this->authorizationUrl)) throw new Exception('Authorization URL is not set.');
		if (empty($this->clientId)) throw new Exception('Client ID is not set.');
		if (empty($this->clientSecret)) throw new Exception('Client Secret is not set.');
		if (empty($this->redirectUri)) throw new Exception('Redirect URI is not set.');

		return $this->authorizationUrl . '?' . http_build_query([
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'response_type' => 'code',
			'redirect_uri' => $this->redirectUri,
			'state' => $state,
			'scope' => implode(',', $this->scope),
	  	]);
	}

	public function fetchAccessTokenByRefreshToken(string $refreshToken): array
	{
		if (empty($refreshToken)) throw new Exception('Refresh token is required.');

		if (empty($this->refreshUrl)) throw new Exception('Refresh Token URL is not set.');
		if (empty($this->clientId)) throw new Exception('Client ID is not set.');
		if (empty($this->clientSecret)) throw new Exception('Client Secret is not set.');

		$params = [
		  'client_id' => $this->clientId,
		  'client_secret' => $this->clientSecret,
		  'grant_type' => 'refresh_token',
		  'refresh_token' => $refreshToken,
		];

		$response = $this->httpClient->send(
		  new Request(
			'GET',
			$this->refreshUrl . '?' . http_build_query($params),
			[
			  'Cache-Control' => 'no-store',
			  'Accept' => 'application/json',
			]
		  )
		);

		return $this->handleTokenResponse($response);
	}

	public function fetchAccessTokenByAuthCode(string $code): array
	{
		if (empty($code)) throw new Exception('Authorization code is required.');

		if (empty($this->tokenUrl)) throw new Exception('Token URL is not set.');
		if (empty($this->clientId)) throw new Exception('Client ID is not set.');
		if (empty($this->clientSecret)) throw new Exception('Client Secret is not set.');
		if (empty($this->redirectUri)) throw new Exception('Redirect URI is not set.');

		$params = [
		  'client_id' => $this->clientId,
		  'client_secret' => $this->clientSecret,
		  'grant_type' => 'authorization_code',
		  'code' => $code,
		  'redirect_uri' => $this->redirectUri,
		];

		$response = $this->httpClient->send(
		  new Request(
			'GET',
			$this->tokenUrl . '?' . http_build_query($params),
			[
			  'Cache-Control' => 'no-store',
			  'Accept' => 'application/json',
			]
		  )
		);

		return $this->handleTokenResponse($response);
	}

	protected function handleTokenResponse(ResponseInterface $response): array
	{
		if ($response->getStatusCode() != 200) throw ResponseException::fromResponse($response);

		try {
			$data = json_decode((string) $response->getBody(), true, 100, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			throw new ResponseException('Invalid token response.');
		}

		$error = $data['errorDescription'] ?? $data['error'] ?? null;

		if ($error) throw new ResponseException($error);
		if (empty($data['access_token'])) throw new ResponseException('Failed to get access token.');

		return $data;
	}
}
