<?php
require_once("include.php");
require_once("TransformedData.php");

// Initialize the data class
$data = new Data(PATH_TO_DATA_FILE);

$options = getopt("s::o::f::");

// Check for valid input
if (sizeof($options) == 0)
{
	die("Usage: php query.php -sFIELDS,TO,SELECT -oFIELDS,TO,ORDER,BY -fFIELD=VALUE\n");
}

$selectFields = explode(",", $options[s]); // Get select options.


$transformedData = new TransformedData($data); // Initialize data array for transformations (SELECT, ORDER BY, FILTER

$transformedData->printSelect($selectFields);