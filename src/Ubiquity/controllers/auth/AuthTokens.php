<?php


namespace Ubiquity\controllers\auth;


use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UASystem;

class AuthTokens {
	protected int $length;
	protected int $duration;
	protected string $root;
	protected bool $sameOrigin;
	const CACHE_KEY='auth';

	/**
	 * AuthTokens constructor.
	 * @param string $root
	 * @param int $length
	 * @param int $duration
	 * @param bool $sameOrigin
	 */
	public function __construct(string $root,int $length = 10, int $duration = 3600,bool $sameOrigin=false){
		$this->root='/'.\trim($root,'/').'/';
		$this->length=$length;
		$this->duration=$duration;
		$this->sameOrigin=$sameOrigin;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function tokenGenerator() {
		return \bin2hex ( \random_bytes ( $this->length??10 ) );
	}

	/**
	 * @return string
	 */
	protected function getOrigin():string{
		return $this->generateOrigin(['platform'=>UASystem::getPlatform(),'browser'=>UASystem::getBrowserComplete(),'ip'=>$_SERVER['REMOTE_ADDR']]);
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function generateOrigin(array $data):string{
		$data=['platform'=>$data['platform']??'','browser'=>$data['browser']??'','ip'=>$data['ip']??''];
		return \md5(\json_encode($data));
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function generateToken():string {
		do {
			$token = $this->tokenGenerator ();
		} while ( $this->exists($token) );
		return $token;
	}

	/**
	 * @param string $token
	 * @return string
	 */
	protected function getKey(string $token):string{
		return self::CACHE_KEY.($this->root).$token;
	}

	/**
	 * @param string $token
	 * @return bool
	 */
	public function exists(string $token):bool{
		return CacheManager::$cache->exists($this->getKey($token));
	}

	/**
	 * @param string $token
	 * @return bool
	 */
	public function expired(string $token):bool{
		$tokenKey=$this->getKey($token);
		$expired= CacheManager::$cache->expired($tokenKey,$this->duration);
		if($expired){
			CacheManager::$cache->remove($tokenKey);
		}
		return $expired;
	}

	/**
	 * Stores some data associated to a new token.
	 *
	 * @param array $data
	 * @return string
	 * @throws \Ubiquity\exceptions\CacheException
	 */
	public function store(array $data):string{
		if($this->sameOrigin){
			$data['origin']=$this->getOrigin();
		}
		$token=$this->generateToken();
		CacheManager::$cache->store($this->getKey($token),$data);
		return $token;
	}

	/**
	 * Removes an existing token.
	 *
	 * @param string $token
	 */
	public function remove(string $token){
		CacheManager::$cache->remove($this->getKey($token));
	}

	/**
	 * Gets the data associated to a token.
	 *
	 * @param $token
	 * @return false|mixed
	 */
	public function fetch($token){
		$tokenKey=$this->getKey($token);
		if(CacheManager::$cache->exists($tokenKey) && !CacheManager::$cache->expired($tokenKey,$this->duration)) {
			$data= CacheManager::$cache->fetch($tokenKey);
			if(!$this->sameOrigin || $this->isSameOrigin($data)){
				return $data;
			}
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	protected function isSameOrigin($data):bool{
		return ($data['origin']??'')===$this->getOrigin();
	}

	/**
	 * @param int $length
	 */
	public function setLength(int $length): void {
		$this->length = $length;
	}

	/**
	 * @param int $duration
	 */
	public function setDuration(int $duration): void {
		$this->duration = $duration;
	}

	/**
	 * @param bool $sameOrigin
	 */
	public function setSameOrigin(bool $sameOrigin): void {
		$this->sameOrigin = $sameOrigin;
	}

	public function setParams(int $length,int $duration,bool $sameOrigin){
		$this->length=$length;
		$this->duration=$duration;
		$this->sameOrigin=$sameOrigin;
	}

}