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
	 * @validator("ip","6")
	 */
	private $ipV6;
	
	/**
	 * @validator("ip","4_no_priv")
	 */
	private $ipV4Noprive;
	
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
		$this->ip="192.168.1.0";
		$this->regexPhone="06.72.86.20.13";
		$this->url="http://ubiquity.kobject.net";
		$this->ipV4Noprive="192.168.1.0";
		$this->ipV6="FE80:0000:0000:0000:0202:B3FF:FE1E:8329";
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
	/**
	 * @return mixed
	 */
	public function getIpV6() {
		return $this->ipV6;
	}

	/**
	 * @return mixed
	 */
	public function getIpV4Noprive() {
		return $this->ipV4Noprive;
	}

	/**
	 * @param mixed $ipV6
	 */
	public function setIpV6($ipV6) {
		$this->ipV6 = $ipV6;
	}

	/**
	 * @param mixed $ipV4Noprive
	 */
	public function setIpV4Noprive($ipV4Noprive) {
		$this->ipV4Noprive = $ipV4Noprive;
	}


}

