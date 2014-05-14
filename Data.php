<?php
require_once("DataItem.php");

class Data
{
	protected $pathToDataFile;


	/**
	 * @param $pathToDataFile
	 */
	function __construct($pathToDataFile)
	{
		$this->pathToDataFile = $pathToDataFile;

		// Check if data file exists, and if not, create it
		if (!file_exists($this->pathToDataFile))
		{
			file_put_contents($this->pathToDataFile, "");
		}
	}

	/**
	 * Gets raw data from data file
	 * @return string
	 */
	function __toString()
	{
		return file_get_contents($this->pathToDataFile);
	}


	/**
	 * Converts the data in the data file to an array for parsing
	 * @return array
	 */
	private function dataAsArray()
	{
		$dataArray = array(); // Initialize data array

		// Build local data array
		$fileArray = file($this->pathToDataFile); // Get input file by line

		// Process input file into native object
		foreach ($fileArray as $fileLine)
		{
			array_push($dataArray, DataItem::lineToObject($fileLine));
		}

		return $dataArray;
	}





	/** Records in the datastore should be unique by STB, TITLE and DATE
	 *
	 * @param DataItem $dataItemToCheck
	 * @returns false if no duplicate, otherwise returns duplicate item already existing in data
	 */
	private function isDuplicate(DataItem $dataItemToCheck)
	{
		foreach ($this->dataAsArray() as $dataItemInData)
		{
			if (
				$dataItemToCheck->stb == $dataItemInData->stb &&
				$dataItemToCheck->title == $dataItemInData->title &&
				$dataItemToCheck->date == $dataItemInData->date
			)
			{
				return $dataItemInData;
			}
		}

		return false;
	}


	/**
	 * Overwrites a data item if it exists.
	 *
	 * @param DataItem $dataItemToDelete
	 * @param DataItem $dataItemToWrite
	 */
	private function overwriteData(DataItem $dataItemToDelete, DataItem $dataItemToWrite)
	{
		foreach ($this->dataAsArray() as $dataItem)
		{
			if ($dataItem == $dataItemToDelete)
			{
				// Delete from data file
				$dataAsString = (string) $this;
				$dataToWrite = str_replace($dataItemToDelete, $dataItemToWrite, $dataAsString); // Replace old data row with new
				file_put_contents($this->pathToDataFile, trim($dataToWrite) . PHP_EOL); // Write new data file
				return;
			}
		}
	}


	/**
	 * Adds a single item to the data file
	 *
	 * @param DataItem $dataItem
	 */
	public function addDataItem(DataItem $dataItem)
	{
		$duplicate = $this->isDuplicate($dataItem); // Check for duplicates

		if ($duplicate === false)
		{
			file_put_contents($this->pathToDataFile, trim($dataItem) . PHP_EOL, FILE_APPEND); // Add item to file
		}
		else
		{
			echo "Duplicate found, overwriting.\n";
			$this->overwriteData($duplicate, $dataItem);
		}
	}
} 