<?php

namespace Worksection\Api;

use Worksection\Api\Models\DownloadedFile;

abstract class Resource
{
	protected Client $client;

	public function __construct(Client $client) {
		$this->client = $client;
	}

	protected function callDownload(string $action, array $params, array $options = []): DownloadedFile
	{
		if ($this->client->hasAccessToken()) return $this->callUserDownload($action, $params, $options);
		if ($this->client->hasApiKey()) return $this->callAdminDownload($action, $params, $options);

		throw new Exception('No access token or API key set');
	}

	protected function callAdminDownload(string $action, array $params, array $options = []): DownloadedFile
	{
		return $this->client->callAdminDownload($action, $params, $options);
	}

	protected function callUserDownload(string $action, array $params, array $options = []): DownloadedFile
	{
		return $this->client->callUserDownload($action, $params, $options);
	}

	protected function callUpload(string $action, array $files): array
	{
		if ($this->client->hasAccessToken()) return $this->callUserUpload($action, $files);
		if ($this->client->hasApiKey()) return $this->callAdminUpload($action, $files);

		throw new Exception('No access token or API key set');
	}

	protected function callAdminUpload(string $action, array $files): array
	{
		return $this->client->callAdminUpload($action, $files);
	}

	protected function callUserUpload(string $action, array $files): array
	{
		return $this->client->callUserUpload($action, $files);
	}

	protected function callAction(string $action, array $params = []): array
	{
		if ($this->client->hasAccessToken()) return $this->callUserAction($action, $params);
		if ($this->client->hasApiKey()) return $this->callAdminAction($action, $params);

		throw new Exception('No access token or API key set');
	}

	protected function callUserAction(string $action, array $params = []): array
	{
		return $this->client->callUserAction($action, $params);
	}

	protected function callAdminAction(string $action, array $params = []): array
	{
		return $this->client->callAdminAction($action, $params);
	}
}
