<?php
require_once("DataItem.php");
require_once("Data.php");

define("PATH_TO_DATA_FILE", "data.txt");

function lineToObject($lineText)
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

// Initialize the data class
$data = new Data(PATH_TO_DATA_FILE);

// Process the input file
$inputFileName = $argv[1];

// Check if input file name is blank
if ($inputFileName == "")
{
	die("No filename was provided to import!\n");
}

// Check if input file exists
if (!file_exists($inputFileName))
{
	die("The file $inputFileName does not exist!\n");
}

$inputFileArray = file($inputFileName); // Get input file by line

// Process input file into native object
$inputDataItems = array();
foreach ($inputFileArray as $inputFileLine)
{
	array_push($inputDataItems, lineToObject($inputFileLine));
}

// Add items to data file
foreach($inputDataItems as $inputDataItem)
{
	$data->addData($inputDataItem);
}

echo count($inputDataItems) . " items processed.\n";