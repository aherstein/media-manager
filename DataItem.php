<?php

class DataItem
{
	public $stb;
	public $title;
	public $provider;
	public $date;
	public $rev;
	public $viewTime;

	function __construct($stb = "", $title = "", $provider = "", $date = "", $rev = "", $viewTime = "")
	{
		$this->stb = $stb;
		$this->title = $title;
		$this->provider = $provider;
		$this->date = $date;
		$this->rev = $rev;
		$this->viewTime = $viewTime;
	}	
}
?>