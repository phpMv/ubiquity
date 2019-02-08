<?php

namespace services;

class TestClassString {
	
	/**
	 * @validator("email")
	 */
	private $email;
	
	/**
	 * @validator("ip")
	 */
	private $ip;
	
	/**
	 * @validator("regex","/(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}/")
	 */
	private $regexPhone;
	
	/**
	 * @validator("url")
	 */
	private $url;
	
	public function __construct(){
		$this->email="mymail@orga.local";
		$this->ip="127.0.0.1";
		$this->regexPhone="06.72.86.20.13";
		$this->url="http://ubiquity.kobject.net";
	}
	/**
	 * @return mixed
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return mixed
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * @return mixed
	 */
	public function getRegexPhone() {
		return $this->regexPhone;
	}

	/**
	 * @return mixed
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @param mixed $ip
	 */
	public function setIp($ip) {
		$this->ip = $ip;
	}

	/**
	 * @param mixed $regexPhone
	 */
	public function setRegexPhone($regexPhone) {
		$this->regexPhone = $regexPhone;
	}

	/**
	 * @param mixed $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

}

