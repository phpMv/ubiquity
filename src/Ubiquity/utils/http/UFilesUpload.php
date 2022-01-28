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
	public const SUCCESS_MESSAGE='uploadSuccess';
	public const NO_FILE_SENT_ERR='uploadNoFileSentErr';
	public const FILE_SIZE_ERR='uploadFileSizeErr';
	public const UNKNOWN_ERR='uploadUnknownErr';
	public const MIME_TYPE_ERR='uploadMimeTypeErr';
	public const EXISTING_FILE_ERR='uploadExistingFileErr';
	public const UPLOAD_ERR='uploadErr';
	public const INVALID_FORMAT_ERR='uploadInvalidFormatErr';
	
	private const IMAGES_MIME_TYPES=['bmp'=>'image/bmp','gif'=>'image/gif','ico'=>'image/vnd.microsoft.icon','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','svg'=>'image/svg+xml','png'=>'image/png','tif'=>'image/tiff','tiff'=>'image/tiff'];
	
	private int $maxFileSize=100000;
	private ?array $allowedMimeTypes=['pdf'=>'application/pdf'];
	private array $messages;
	private string $destDir;
	private array $messageTypes=[
			self::SUCCESS_MESSAGE=>'The file %s has been uploaded.',
			self::NO_FILE_SENT_ERR=>'No file sent.',
			self::FILE_SIZE_ERR=>'Exceeded file size limit for %s.',
			self::UNKNOWN_ERR=>'Unknown Error.',
			self::MIME_TYPE_ERR=>'The mime-type %s is not allowed for %s!',
			self::EXISTING_FILE_ERR=>'Sorry, The file %s already exists.',
			self::UPLOAD_ERR=>'Sorry, there was an error uploading the file %s.',
			self::INVALID_FORMAT_ERR=>'Invalid error format!'
	];
	
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
		$this->allowType($only,self::IMAGES_MIME_TYPES);
	}
	
	public function allowAllMimeTypes(): void{
		$this->allowedMimeTypes=null;
	}
	
	private function allowType(bool $only,array $typeArray): void {
		if($only){
			$this->allowedMimeTypes=$typeArray;
		}else{
			$this->allowedMimeTypes+=$typeArray;
		}
	}
	
	private function checkErrors(array $file):void{
		if (!isset($file['error']) || \is_array($file['error'])){
			throw new FileUploadException($this->getMessageType(self::INVALID_FORMAT_ERR));
		}
		switch ($file['error']) {
			case \UPLOAD_ERR_OK:
				break;
			case \UPLOAD_ERR_NO_FILE:
				throw new FileUploadException($this->getMessageType(self::NO_FILE_SENT_ERR));
			case \UPLOAD_ERR_INI_SIZE:
			case \UPLOAD_ERR_FORM_SIZE:
				throw new FileUploadException($this->getMessageType(self::FILE_SIZE_ERR,$this->getDisplayedFileName($file)));
			default:
				throw new FileUploadException($this->getMessageType(self::UNKNOWN_ERR,$this->getDisplayedFileName($file)));
		}
	}
	
	private function getMessageType(string $type,string ...$params){
		return \vsprintf($this->messageTypes[$type]??('No message for '.$type), $params);
	}
	
	private function checkTypeMime(array $file): bool {
		$allowedMimeTypes=$this->getAllowedMimeTypes();
		if(\is_array($allowedMimeTypes)) {
			$finfo = new \finfo(\FILEINFO_MIME_TYPE);
			if(\array_search($finfo->file($file['tmp_name']), $this->getAllowedMimeTypes(), true) === false){
				$this->messages['errors'][]=$this->getMessageType(self::MIME_TYPE_ERR, $finfo->file($file['tmp_name']),$this->getDisplayedFileName($file));
				return false;
			}
		}
		return true;
	}
	
	private function checkFileSize(array $file): bool{
		if ($file['size'] > $this->maxFileSize) {
			$this->messages['errors'][]=$this->getMessageType(self::FILE_SIZE_ERR,$this->getDisplayedFileName($file));
			return false;
		}
		return true;
	}
	
	private function getDisplayedFileName(array $file): string {
		return \htmlspecialchars(\basename($file['name']));
	}
	
	/**
	 * Redefine the messages displayed.
	 * @param array $messages
	 */
	public function setMessageTypes(array $messages): void {
		$this->messageTypes=\array_merge($this->messageTypes,$messages);
	}
	
	/**
	 * Get the message types displayed.
	 * @return array
	 */
	public function getMessageTypes(): array {
		return $this->messageTypes;
	}

	/**
	 * Uploads files to $destDir directory.
	 * @param string|null $destDir is relative to ROOT app
	 * @param bool $force if True, replace existing files
	 * @param callable|null $fileNameCallback returns an updated version of the dest filename i.e. function($filename){ return '_'.$filename;}
	 */
	public function upload(string $destDir=null,bool $force=true,?callable $filenameCallback=null): void {
		$destDir??=$this->destDir;
		$this->messages=[];
		$dest=\ROOT.\DS.$destDir.\DS;
		UFileSystem::safeMkdir(\ROOT.\DS.$destDir.\DS);
		try{
			foreach($_FILES as $file){
				$this->checkErrors($file);
				if($this->checkTypeMime($file) && $this->checkFileSize($file)) {
					$filename = \basename($file['name']);
					if(isset($filenameCallback) && \is_callable($filenameCallback)){
						$filename=$filenameCallback($filename)??$filename;
					}
					$dFileName=\htmlspecialchars($filename);
					if ($force || !\file_exists($dest . $filename)) {
						if (\move_uploaded_file($file['tmp_name'], $dest . $filename)) {
							$this->messages['success'][] = $this->getMessageType(self::SUCCESS_MESSAGE, $dFileName);
						} else {
							$this->messages['errors'][] = $this->getMessageType(self::UPLOAD_ERR, $dFileName);
						}
					} else {
						$this->messages['errors'][] = $this->getMessageType(self::EXISTING_FILE_ERR, $dFileName);
					}
				}
			}
		}catch(\Exception $e){
			$this->messages['errors'][]=$e->getMessage();
		}
	}
	
	/**
	 * Returns true if the upload generated at least one error.
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
