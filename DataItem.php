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

	function __toString()
	{
		return $this->stb . "|" . $this->title . "|" . $this->provider . "|" . $this->date . "|" . $this->rev . "|" . $this->viewTime;
	}

	public static function lineToObject($lineText)
	{
		$lineArray = explode("|", $lineText);
		$dataObject = new DataItem(
			$lineArray[0], // stb
			$lineArray[1], // title
			$lineArray[2], // provider
			$lineArray[3], // date
			$lineArray[4], // rev
			$lineArray[5] // viewTime
		);

		return $dataObject;
	}
}
?>