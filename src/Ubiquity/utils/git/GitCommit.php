<?php

namespace Ubiquity\utils\git;

class GitCommit {
	private $cHash;
	private $lHash;
	private $author;
	private $cDate;
	private $summary;
	private $pushed;
	
	public function __construct($cHash="",$author="",$cDate="",$summary="",$lHash="",$pushed=true){
		$this->cHash=$cHash;
		$this->lHash=$lHash;
		$this->author=$author;
		$this->cDate=$cDate;
		$this->summary=$summary;
		$this->pushed=$pushed;
	}
	/**
	 * @return string
	 */
	public function getLHash() {
		return $this->lHash;
	}

	/**
	 * @return string
	 */
	public function getCHash() {
		return $this->cHash;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function getCDate() {
		return $this->cDate;
	}

	/**
	 * @return string
	 */
	public function getSummary() {
		return $this->summary;
	}

	/**
	 * @param string $cHash
	 */
	public function setCHash($cHash) {
		$this->cHash = $cHash;
	}

	/**
	 * @param string $author
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * @param string $cDate
	 */
	public function setCDate($cDate) {
		$this->cDate = $cDate;
	}

	/**
	 * @param string $summary
	 */
	public function setSummary($summary) {
		$this->summary = $summary;
	}
	/**
	 * @return string
	 */
	public function getPushed() {
		return $this->pushed;
	}

	/**
	 * @param string $pushed
	 */
	public function setPushed($pushed) {
		$this->pushed = $pushed;
	}

	
}

