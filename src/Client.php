<?php

namespace Worksection\Api;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Worksection\Api\Exceptions\ResponseException;
use Worksection\Api\Exceptions\UnauthorizedException;
use Worksection\Api\Models\DownloadedFile;
use Worksection\Api\Resources\CommentsResource;
use Worksection\Api\Resources\CostsResource;
use Worksection\Api\Resources\MembersResource;
use Worksection\Api\Resources\ProjectsResource;
use Worksection\Api\Resources\TasksResource;
use Worksection\Api\Resources\TimersResource;
use Worksection\Api\Resources\UserResource;
use Worksection\Api\Resources\ContactsResource;
use Worksection\Api\Resources\EventsResource;
use Worksection\Api\Resources\FilesResource;
use Worksection\Api\Resources\WebhookResource;

/**
 * @property-read Oauth $oauth
 * @property-read UserResource $user
 * @property-read MembersResource $members
 * @property-read TasksResource $tasks
 * @property-read ProjectsResource $projects
 * @property-read CommentsResource $comments
 * @property-read CostsResource $costs
 * @property-read TimersResource $timers
 * @property-read ContactsResource $contacts
 * @property-read EventsResource $events
 * @property-read FilesResource $files
 * @property-read WebhookResource $webhook
 */
class Client
{
	public const VERSION = '2.1.0';

	private const TOKEN_TYPE_ACCESS = 1;
	private const TOKEN_TYPE_API_KEY = 2;

	private const ADMIN_PATH = '/api/admin/v2';
	private const USER_PATH = '/api/oauth2';

	public string $origin;

	private array $token;
	private array $client;

	private Http $http;

	public function __construct(string $origin) {
		$this->origin = trim($origin, '/');
		$this->token = [];
		$this->client = [];

		$this->http = Http::create($this->origin);
	}

	public function setClient(array $client): self
	{
		if (!isset($client['client_id'])) throw new Exception('client_id is required.');
		if (!isset($client['client_secret'])) throw new Exception('client_secret is required.');
		if (!isset($client['redirect_uri'])) throw new Exception('redirect_uri is required.');

		if (isset($client['scope']) && !is_array($client['scope']))
			throw new Exception('scope must be an array.');

		$this->client['client_id'] = $client['client_id'];
		$this->client['client_secret'] = $client['client_secret'];
		$this->client['redirect_uri'] = $client['redirect_uri'];
		$this->client['scope'] = $client['scope'] ?? [];
		$this->client['auth_url'] = $client['auth_url'] ?? null;
		$this->client['token_url'] = $client['token_url'] ?? null;
		$this->client['refresh_url'] = $client['refresh_url'] ?? null;

		return $this;
	}

	public function getClientId(): string { return $this->client['client_id'] ?? ''; }
	public function getClientSecret(): string { return $this->client['client_secret'] ?? ''; }
	public function getRedirectUri(): string { return $this->client['redirect_uri'] ?? ''; }
	public function getScope(): array { return $this->client['scope'] ?? []; }

	protected function setToken(int $type, ?array $token): void
	{
		if ($type !== self::TOKEN_TYPE_ACCESS && $type !== self::TOKEN_TYPE_API_KEY)
			throw new Exception('invalid token type');

		if ($type === self::TOKEN_TYPE_ACCESS && !isset($token['access_token']))
			throw new Exception('access_token is required.');

		if ($type === self::TOKEN_TYPE_API_KEY && !isset($token['api_key']))
			throw new Exception('api_key is required.');

		$this->token = ['type' => $type] + ($token ?: []);
	}

	/**
	 * @param string|array $token
	 * @return static
	 */
	public function setAccessToken($token): self
	{
		if (is_array($token)) {
			$this->setToken(self::TOKEN_TYPE_ACCESS, $token);
		} elseif (is_string($token)) {
			$this->setToken(self::TOKEN_TYPE_ACCESS, ['access_token' => $token]);
		} else {
			throw new Exception('invalid token.');
		}

		return $this;
	}

	public function setApiKey(string $apiKey): self
	{
		$this->setToken(self::TOKEN_TYPE_API_KEY, ['api_key' => $apiKey]);
		return $this;
	}

	public function getAccessToken(): string {
		if ($this->token['type'] !== self::TOKEN_TYPE_ACCESS) throw new Exception('access_token not set');
		return $this->token['access_token'] ?? '';
	}
	public function hasAccessToken(): bool {
		return $this->token['type'] === self::TOKEN_TYPE_ACCESS && !empty($this->token['access_token']);
	}

	public function getRefreshToken(): string {
		if ($this->token['type'] !== self::TOKEN_TYPE_ACCESS) throw new Exception('access_token not set');
		return $this->token['refresh_token'] ?? '';
	}
	public function hasRefreshToken(): bool {
		return $this->token['type'] === self::TOKEN_TYPE_ACCESS && !empty($this->token['refresh_token']);
	}
	public function unsetRefreshToken(): self
	{
		if ($this->hasRefreshToken()) $this->token['refresh_token'] = '';

		return $this;
	}

	public function getApiKey(): string {
		if ($this->token['type'] !== self::TOKEN_TYPE_API_KEY) throw new Exception('api_key not set');
		return $this->token['api_key'] ?? '';
	}
	public function hasApiKey(): bool {
		return $this->token['type'] === self::TOKEN_TYPE_API_KEY && !empty($this->token['api_key']);
	}

	public function getOauth(): Oauth
	{
		if (!isset($this->client['client_id'])) throw new Exception('client_id is required.');
		if (!isset($this->client['client_secret'])) throw new Exception('client_secret is required.');
		if (!isset($this->client['redirect_uri'])) throw new Exception('redirect_uri is required.');

		return new Oauth($this->client);
	}

	private array $cachedResources = [];

	public function __get(string $name)
	{
		if ($name === 'oauth') return $this->getOauth();
		if (isset($this->cachedResources[$name])) return $this->cachedResources[$name];

		if ($name === 'user')     return $this->cachedResources[$name] = new UserResource($this);
		if ($name === 'members')  return $this->cachedResources[$name] = new MembersResource($this);
		if ($name === 'tasks')    return $this->cachedResources[$name] = new TasksResource($this);
		if ($name === 'projects') return $this->cachedResources[$name] = new ProjectsResource($this);
		if ($name === 'comments') return $this->cachedResources[$name] = new CommentsResource($this);
		if ($name === 'costs')    return $this->cachedResources[$name] = new CostsResource($this);
		if ($name === 'timers')   return $this->cachedResources[$name] = new TimersResource($this);
		if ($name === 'contacts') return $this->cachedResources[$name] = new ContactsResource($this);
		if ($name === 'events')   return $this->cachedResources[$name] = new EventsResource($this);
		if ($name === 'files')    return $this->cachedResources[$name] = new FilesResource($this);
		if ($name === 'webhook')  return $this->cachedResources[$name] = new WebhookResource($this);

		throw new Exception('Undefined property: ' . $name);
	}

	protected function processActionResponse(ResponseInterface $response, string $action): array
	{
		$body = $response->getBody()->getContents();
		$data = json_decode($body, true);

		if (isset($data['status']) && $data['status'] === 'error')
			throw new ResponseException($action . ': ' . $data['message'] ?? 'Unknown error');

		unset($data['status']);

		return $data['data'] ?? $data;
	}

	public function getAdminToken(string $action): string
	{
		if (!$this->hasApiKey()) throw new Exception('API key not set');
		$t = time();
		return $t . '_' . hash_hmac('sha256', $action.':'.$t, $this->getApiKey());
	}

	private function createAdminUrl(string $action): string
	{
		return $this->origin . self::ADMIN_PATH . '?' . http_build_query([ 'action' => $action ]);
	}

	private function createUserUrl(string $action): string
	{
		return $this->origin . self::USER_PATH . '?' . http_build_query(['action' => $action]);
	}

	private function createJsonBody(array $data = []): string
	{
		return $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : '';
	}

	private function createMultipartData(array $files): array
	{
		$multipart = [];

		foreach ($files as $file) {
			if (!is_array($file)) throw new Exception('Invalid file format');
			if (empty($file['path']) && empty($file['contents'])) throw new Exception('File path or content is required');
			if (empty($file['name']) && empty($file['path'])) throw new Exception('File name is required');

			$multipart[] = [
			  'name'     => $file['key'] ?? 'files',
			  'contents' => $file['content'] ?? fopen($file['path'], 'r'),
			  'filename' => $file['name'] ?? basename($file['path']),
			];
		}

		return $multipart;
	}

	public function callUserUpload(string $action, array $files): array
	{
		$request = new Request('POST', $this->createUserUrl($action));
		$request = $request
		  ->withHeader('Accept', 'application/json')
		  ->withHeader('Authorization', 'Bearer ' . $this->getAccessToken());

		return $this->processActionResponse($this->send($request, ['multipart' => $this->createMultipartData($files)]), $action);
	}

	public function callAdminUpload(string $action, array $files): array
	{
		$request = new Request('POST', $this->createAdminUrl($action));
		$request = $request->withHeader('Accept', 'application/json')
		  ->withHeader('Authorization', 'Admin ' . $this->getAdminToken($action));

		return $this->processActionResponse($this->send($request, ['multipart' => $this->createMultipartData($files)]), $action);
	}

	public function callAdminAction(string $action, array $params = []): array
	{
		$request = new Request('POST', $this->createAdminUrl($action), [], $this->createJsonBody($params));
		$request = $request->withHeader('Content-Type', 'application/json; charset=utf-8')
		  ->withHeader('Accept', 'application/json')
		  ->withHeader('Authorization', 'Admin ' . $this->getAdminToken($action));

		return $this->processActionResponse($this->send($request), $action);
	}

	public function callUserAction(string $action, array $params = []): array
	{
		$request = new Request('POST', $this->createUserUrl($action), [], $this->createJsonBody($params));
		$request = $request->withHeader('Content-Type', 'application/json; charset=utf-8')
		  ->withHeader('Accept', 'application/json')
		  ->withHeader('Authorization', 'Bearer ' . $this->getAccessToken());

		return $this->processActionResponse($this->send($request), $action);
	}

	public function download(Request $request, array $options = []): DownloadedFile
	{
		$response = $this->send($request, [ 'sink' => $options['sink'] ?? null ]);

		if (strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
			$body = $response->getBody()->getContents();
			$data = json_decode($body, true);
			throw new ResponseException($data['message'] ?? 'Unknown download error');
		}

		if (!$response->hasHeader('Content-Disposition')) {
			throw new ResponseException('Failed to download file');
		}

		return DownloadedFile::fromArray(
		  $this->parseContentDisposition($response->getHeaderLine('Content-Disposition'))
		);
	}

	public function callAdminDownload(string $action, array $params = [], array $options = []): DownloadedFile
	{
		$request = new Request('POST', $this->createAdminUrl($action), [], $this->createJsonBody($params));
		$request = $request->withHeader('Content-Type', 'application/json; charset=utf-8')
		  ->withHeader('Accept', '*/*')
		  ->withHeader('Authorization', 'Admin ' . $this->getAdminToken($action));

		return $this->download($request, $options);
	}

	public function callUserDownload(string $action, array $params = [], array $options = []): DownloadedFile
	{
		$request = new Request('POST', $this->createUserUrl($action), [], $this->createJsonBody($params));
		$request = $request->withHeader('Content-Type', 'application/json; charset=utf-8')
		  ->withHeader('Accept', '*/*')
		  ->withHeader('Authorization', 'Bearer ' . $this->getAccessToken());

		return $this->download($request, $options);
	}

	public function send(RequestInterface $request, array $options = []): ResponseInterface
	{
		$retry_attempt = $options['retry_attempt'] ?? 0;
		unset($options['retry_attempt']);

		$request = $request->withHeader('Sdk-Client', 'php-sdk:' . self::VERSION);
		$response = $this->http->send($request, $options);

		if ($response->getStatusCode() == 401) {
			$exception = UnauthorizedException::fromResponse($response);

			if ($this->hasRefreshToken() && strpos($exception->getMessage(), 'token is expired') !== false && $retry_attempt === 0) {
				$token = $this->oauth->fetchAccessTokenByRefreshToken($this->getRefreshToken());
				$this->setAccessToken($token);

				return $this->send($request, ['retry_attempt' => $retry_attempt + 1] + $options);
			}

			throw $exception;
		}

		if ($response->getStatusCode() != 200) throw ResponseException::fromResponse($response);

		return $response;
	}

	private function parseContentDisposition(string $header): array {
		$result = [];

		[$type, $rest] = array_pad(explode(';', $header, 2), 2, '');
		$result['type'] = strtolower(trim($type));

		preg_match_all('/([a-zA-Z0-9_*]+)=(".*?"|[^;]*)/', $rest, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$key = strtolower($match[1]);
			$value = trim($match[2], "\"'");

			if (substr($key, -1) === '*') {
				[$charset, , $encoded] = explode("'", $value, 3);
				$value = urldecode($encoded);

				if (strtoupper($charset) !== 'UTF-8') {
					$value = mb_convert_encoding($value, 'UTF-8', $charset);
				}

				$key = rtrim($key, '*');
			}

			$result[$key] = $value;
		}

		return $result;
	}
}
