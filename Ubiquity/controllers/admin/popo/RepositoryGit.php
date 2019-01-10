<?php
namespace Ubiquity\controllers\admin\popo;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\git\UGitRepository;
use Ubiquity\utils\git\GitFile;
use Ubiquity\utils\git\GitFileStatus;
use Ubiquity\utils\base\UString;

class RepositoryGit{
	public static $GIT_SETTINGS="git/settings";
	private $name;
	private $initialized;
	private $files;
	private $remoteUrl;
	private $user;
	private $password;
	private $commits;
	/**
	 * @var UGitRepository
	 */
	private $repository;
	
	/**
	 * @return mixed
	 */
	public function getRepository() {
		return $this->repository;
	}

	/**
	 * @param mixed $repository
	 */
	public function setRepository($repository) {
		$this->repository = $repository;
	}

	/**
	 * @return mixed
	 */
	public function getRemoteUrl() {
		return $this->remoteUrl;
	}
	
	/**
	 * @return mixed
	 */
	public function getAuthRemoteUrl() {
		$remoteUrl= $this->remoteUrl;
		if(UString::isNotNull($this->user) && UString::isNotNull($this->password) && strpos($remoteUrl, "//")!==false)
			return str_replace("//", "//".$this->user.":".$this->password."@", $remoteUrl);
		return false;
	}
	
	public function setRepoRemoteUrl(){
		if($url=$this->getAuthRemoteUrl()){
			$activeRemoteUrl =$this->repository->getRemoteUrl();
			if (UString::isNull ( $activeRemoteUrl )) {
				$this->repository->addRemote ( "origin", $url );
			} else {
				$this->repository->setRemoteUrl ( "origin", $url );
			}
		}
		return $url;
	}
	

	/**
	 * @return mixed
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return mixed
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param mixed $remoteUrl
	 */
	public function setRemoteUrl($remoteUrl) {
		$remoteUrl=preg_replace('@\/\/.*?\@@', "//", $remoteUrl);
		$this->remoteUrl = $remoteUrl;
	}

	/**
	 * @param mixed $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @return multitype:
	 */
	public function getFiles() {
		return $this->files;
	}

	/**
	 * @param multitype: $files
	 */
	public function setFiles($files) {
		$this->files = $files;
	}
	
	public function addFiles($files){
		$this->files= array_merge($this->files,$files);
	}

	/**
	 * @return mixed
	 */
	public function getInitialized() {
		return $this->initialized;
	}

	/**
	 * @param mixed $initialized
	 */
	public function setInitialized($initialized) {
		$this->initialized = $initialized;
	}

	public function __construct($name=""){
		$this->name=$name;
		$this->files=[];
		$this->commits=[];
	}
	

	/**
	 * @return multitype:
	 */
	public function getCommits() {
		return $this->commits;
	}
	
	public function hasCommits(){
		return sizeof($this->commits)>0;
	}

	/**
	 * @param multitype: $commits
	 */
	public function setCommits($commits) {
		$this->commits = $commits;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return \Ubiquity\controllers\admin\popo\RepositoryGit
	 */
	public static function init($getFiles=true){
		$result=new RepositoryGit();
		$isValid=false;
		if(CacheManager::$cache->exists(self::$GIT_SETTINGS)){
			$gitSettings=CacheManager::$cache->fetch(self::$GIT_SETTINGS);
			$isValid=isset($gitSettings["name"]);
		}
		if(!$isValid)
			$gitSettings["name"]=Startup::getApplicationName();
	
		$initialized=false;
		if(file_exists(Startup::getApplicationDir().\DS.".git")){
			$repo=new UGitRepository(Startup::getApplicationDir().\DS.".git");
			if($getFiles){
				$result->addFiles(self::loadUntrackedFiles($repo));
				$result->addFiles(self::loadModifiedFiles($repo));
			}
			$result->setRemoteUrl($repo->getRemoteUrl());
			$result->setRepository($repo);
			$result->setCommits($repo->getCommits());
			$initialized=true;
		}
		$gitSettings["initialized"]=$initialized;
		URequest::setValuesToObject($result,$gitSettings);
		return $result;
	}
	
	public static function loadUntrackedFiles(UGitRepository $gitRepo){
		$files=$gitRepo->getUntrackedFiles();
		$result=[];
		if(isset($files)){
			foreach ($files as $file){
				$result[$file]=new GitFile($file,GitFileStatus::$UNTRACKED);
			}
		}
		return $result;
	}
	
	public static function loadModifiedFiles(UGitRepository $gitRepo){
		$files=$gitRepo->getModifiedFiles();
		$result=[];
		if(isset($files)){
			foreach ($files as $file){
				switch ($file[0]){
					case "M":
						$status=GitFileStatus::$MODIFIED;
						break;
					case "D":
						$status=GitFileStatus::$DELETED;
						break;
					default:
						$status=GitFileStatus::$NONE;
				}
				if(isset($file[1]))
					$result[$file[1]]=new GitFile($file[1],$status);
			}
		}
		return $result;
	}
	
	public function __toString(){
		$status="";
		if(!$this->initialized)
			$status="not initialized";
		return "{$this->name} [{$status}]";
	}
}
