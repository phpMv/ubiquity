<?php


namespace Ubiquity\utils\http;

use Ubiquity\exceptions\FileUploadException;
use Ubiquity\utils\base\UFileSystem;

/**
 * File Uploader class utility.
 * Ubiquity\utils\http$UFilesUpload
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 0.0.0
 *
 */

class UFilesUpload {
	private const IMAGES_MIME_TYPES=['bmp'=>'image/bmp','git'=>'image/gif','ico'=>'image/vnd.microsoft.icon','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','svg'=>'image/svg+xml','png'=>'image/png','tif'=>'image/tiff','tiff'=>'image/tiff'];
	
	private int $maxFileSize=100000;
	private ?array $allowedMimeTypes=['pdf'=>'application/pdf'];
	private array $messages;
	private string $destDir;

	public function __construct($destDir='upload') {
		$this->destDir=$destDir;
	}

	/**
	 * @param array|null $allowedMimeTypes
	 * @param int $maxFileSize
	 */
	public function setUploadOptions(?array $allowedMimeTypes=null,int $maxFileSize=100000): void {
		$this->allowedMimeTypes=$allowedMimeTypes;
		$this->maxFileSize=$maxFileSize;
	}

	/**
	 * @return int
	 */
	public function getMaxFileSize(): int {
		return $this->maxFileSize;
	}

	/**
	 * @param int $maxFileSize
	 */
	public function setMaxFileSize(int $maxFileSize): void {
		$this->maxFileSize = $maxFileSize;
	}

	/**
	 * @return array|string[]|null
	 */
	public function getAllowedMimeTypes(): ?array {
		return $this->allowedMimeTypes;
	}

	/**
	 * @param array|string[]|null $allowedMimeTypes
	 */
	public function setAllowedMimeTypes(?array $allowedMimeTypes): void {
		$this->allowedMimeTypes = $allowedMimeTypes;
	}
	
	public function allowImages(bool $only=true): void {
		if($only){
			$this->allowedMimeTypes=self::IMAGES_MIME_TYPES;
		}else{
			$this->allowedMimeTypes+=self::IMAGES_MIME_TYPES;
		}
	}

	public function allowAllMimeTypes(): void{
		$this->allowedMimeTypes=null;
	}
	
	private function checkErrors(array $file):void{
		if (!isset($file['error']) ||	\is_array($file['error'])){
			throw new FileUploadException('Invalid error format.');
		}
		switch ($file['error']) {
			case \UPLOAD_ERR_OK:
				break;
			case \UPLOAD_ERR_NO_FILE:
				throw new FileUploadException('No file sent.');
			case \UPLOAD_ERR_INI_SIZE:
			case \UPLOAD_ERR_FORM_SIZE:
				throw new FileUploadException('Exceeded file size limit.');
			default:
				throw new FileUploadException('Unknown errors.');
		}
	}

	private function checkTypeMime(array $file): bool {
		$allowedMimeTypes=$this->getAllowedMimeTypes();
		if(\is_array($allowedMimeTypes)) {
			$finfo = new \finfo(\FILEINFO_MIME_TYPE);
			if(\array_search($finfo->file($file['tmp_name']), $this->getAllowedMimeTypes(), true) === false){
				$this->messages['errors'][]=\sprintf('The mime-type %s is not allowed for %s!',$finfo->file($file['tmp_name']),$this->getDisplayedFileName($file));
				return false;
			}
		}
		return true;
	}

	private function checkFileSize(array $file): bool{
		if ($file['size'] > $this->maxFileSize) {
			$this->messages['errors'][]=\sprintf('Exceeded file size limit for %s.',$this->getDisplayedFileName($file));
			return false;
		}
		return true;
	}

	private function getDisplayedFileName(array $file): string {
		return \htmlspecialchars(basename($file['name']));
	}

	/**
	 * Uploads files to $destDir directory.
	 * @param string $destDir is relative to ROOT app
	 * @param bool $force if True, replace existing files
	 */
	public function upload(string $destDir=null,bool $force=true): void {
		$destDir??=$this->destDir;
		$this->messages=[];
		$dest=\ROOT.\DS.$destDir.\DS;
		UFileSystem::safeMkdir(\ROOT.\DS.$destDir.\DS);
		try{
			foreach($_FILES as $file){
				$this->checkErrors($file);
				if($this->checkTypeMime($file) && $this->checkFileSize($file)) {
					$filename = \basename($file['name']);
					if ($force || !\file_exists($dest . $filename)) {
						if (\move_uploaded_file($file['tmp_name'], $dest . $filename)) {
							$this->messages['success'][] = 'The file ' . \htmlspecialchars($filename) . ' has been uploaded.';
						} else {
							$this->messages['errors'][] = 'Sorry, there was an error uploading the file.' . \htmlspecialchars($filename);
						}
					} else {
						$this->messages['errors'][] = 'Sorry, The file ' . \htmlspecialchars($filename) . ' already exists.';
					}
				}
			}
		}catch(\Exception $e){
			$this->messages['errors'][]=$e->getMessage();
		}
	}

	/**
	 * Returns true true if the upload generated at least one error.
	 * @return bool
	 */
	public function hasErrors(): bool {
		return isset($this->messages['errors']) && \count($this->messages['errors'])>0;
	}

	/**
	 * Returns true if at least one file has been uploaded.
	 * @return bool
	 */
	public function isSuccess(): bool {
		return isset($this->messages['success']) && \count($this->messages['success'])>0;
	}

	/**
	 * Returns all success messages.
	 * @return array|mixed
	 */
	public function getMessages(): array {
		return $this->messages['success']??[];
	}

	/**
	 * Returns all error messages.
	 * @return array|mixed
	 */
	public function getErrorMessages(): array {
		return $this->messages['errors']??[];
	}
}
