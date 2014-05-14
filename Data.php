<?php
require_once("DataItem.php");

class Data
{
	protected $pathToDataFile;
	protected $dataArray = array();


	function __construct($pathToDataFile)
	{
		$this->pathToDataFile = $pathToDataFile;
		// Check if data file exists, and if not, create it
		if (!file_exists($this->pathToDataFile))
		{
			file_put_contents($this->pathToDataFile, "");
		}

		// Build local data array
		$fileArray = file($pathToDataFile); // Get input file by line

		// Process input file into native object
		foreach ($fileArray as $fileLine)
		{
			array_push($this->dataArray, lineToObject($fileLine));
		}
	}


	//Get raw data from local object
	function __toString()
	{
		$returnString = "";
		foreach ($this->dataArray as $dataItem)
		{
			$returnString .= $dataItem;
		}

		return $returnString;
	}


	/** Records in the datastore should be unique by STB, TITLE and DATE
	 *
	 * @returns false if no duplicate, otherwise returns duplicate item already existing in data
	 */
	private function isDuplicate(DataItem $dataItemToCheck)
	{
		foreach ($this->dataArray as $dataItemInData)
		{
			if (
				$dataItemToCheck->stb == $dataItemInData->stb &&
				$dataItemToCheck->title == $dataItemInData->title &&
				$dataItemToCheck->date == $dataItemInData->date
			)
			{
				return true;
			}
		}
		return false;
	}

	private function overwriteData(DataItem $dataItemToDelete, DataItem $dataItemToWrite)
	{

	}


	public function addData(DataItem $dataItem)
	{
		$duplicate = $this->isDuplicate($dataItem); // Check for duplicates

		if ($duplicate === false)
		{
			array_push($dataArray, $dataItem); // Add item to local array
			file_put_contents($this->pathToDataFile, trim($dataItem) . PHP_EOL, FILE_APPEND); // Add item to file
		}
		else
		{
			$this->overwriteData($duplicate, $dataItem);
		}
	}
} 