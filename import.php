<?php
require_once("include.php");

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

// Process input file into native object and add to data file
$totalProcessed = 0;
foreach ($inputFileArray as $inputFileLine)
{
	$data->addDataItem(DataItem::lineToObject($inputFileLine));
	$totalProcessed++;
}

echo "$totalProcessed items processed.\n";