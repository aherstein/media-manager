<?php

class DataItem
{
	public $stb;
	public $title;
	public $provider;
	public $date;
	public $rev;
	public $viewtime;
	public $count;

	function __construct($stb = "", $title = "", $provider = "", $date = "", $rev = "", $viewtime = "")
	{
		$this->stb = trim($stb);
		$this->title = trim($title);
		$this->provider = trim($provider);
		$this->date = trim($date);
		$this->rev = trim($rev);
		$this->viewtime = trim($viewtime);
	}

	function __toString()
	{
		return $this->stb . "," . $this->title . "," . $this->provider . "," . $this->date . "," . $this->rev . "," . $this->viewtime;
	}


	/**
	 * @param array $fields Array of fields to select from this object
	 * @returns string representation of object with selected fields
	 */
	public function select($fields)
	{
		// If no fields were provided to select, or user passed in a * character, then select all
		if (sizeof($fields) == 0 || $fields[0] == "" || $fields[0] == "*")
		{
			return (string) $this;
		}

		$returnArray = array();
		foreach ($fields as $field)
		{
			$field = strtolower($field);
			array_push($returnArray, $this->$field);
		}

		// Add count value if it has been set
		if ($this->count != "")
		{
			array_push($returnArray, $this->count);
		}

		return implode(",", $returnArray) . "\n";
	}


	/**
	 * Converts pipe separated values to internal DataItem object
	 * @return DataItem
	 */
	public static function lineToObject($lineText)
	{
		$lineArray = explode("|", $lineText);
		$dataObject = new DataItem(
			$lineArray[0], // stb
			$lineArray[1], // title
			$lineArray[2], // provider
			$lineArray[3], // date
			$lineArray[4], // rev
			$lineArray[5] // viewtime
		);

		return $dataObject;
	}
}
?>