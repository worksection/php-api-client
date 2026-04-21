<?php

namespace Worksection\Api\Resources;

use Psr\Http\Message\StreamInterface;
use Worksection\Api\Exception;
use Worksection\Api\Models\DownloadedFile;
use Worksection\Api\Models\File;
use Worksection\Api\Models\UploadedFile;
use Worksection\Api\Resource;

class FilesResource extends Resource
{
	/**
	 * Get files.
	 *
	 * @return File[]
	 */
	public function list(array $params = []): array
	{
		if (empty($params['id_task']) && empty($params['id_project'])) {
			throw new \InvalidArgumentException('Either id_task or id_project is required.');
		}

		return array_map(
			fn(array $i) => File::fromArray($i),
			$this->callAction('get_files', $params)
		);
	}

	/**
	 * @param int $id_file
	 * @param string|resource|StreamInterface $sink
	 */
	public function download(int $id_file, $sink): DownloadedFile
	{
		return $this->callDownload('download', ['id_file' => $id_file], ['sink' => $sink]);
	}

	/**
	 * @param string[] $files File paths
	 * @return array
	 * @throws Exception
	 */
	public function upload(array $files): array
	{
		$files = array_map(function(string $file) {
			return [ 'key' => 'files[]', 'path' => $file ];
		}, $files);

		return array_map(
			fn(array $i) => UploadedFile::fromArray($i),
			$this->callUpload('upload_files', $files)
		);
	}
}
