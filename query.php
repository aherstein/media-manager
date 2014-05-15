<?php
require_once("include.php");
require_once("TransformedData.php");

// Initialize the data class
$data = new Data(PATH_TO_DATA_FILE);

$options = getopt("s::o::f::g::"); // Get options from command line

// Check for valid input
if (sizeof($options) == 0)
{
	die("Usage: php query.php -sFIELDS,TO,SELECT:optional-aggregate-function -gGROUPBY -oFIELDS,TO,ORDER,BY -f'FIELD=VALUE AND/OR FIELD2=VALUE2'\n");
}

$selectFieldsAll = explode(",", $options['s']); // Get select options.

// Split select by aggregate functions
$selectFields = array();
$aggregateFields = array();
for ($i = 0; $i < count($selectFieldsAll); $i++)
{
	$split = explode(":", $selectFieldsAll[$i]);
	$selectFields[$i] = $split[0];
	$aggregateFields[$i] = $split[1];
}

if ($selectFields[0] == "" && count($selectFields) == 1) // No fields were selected
{
	die("No fields were selected. Make sure there is no space between the option letter and the parameter. e.g. -sTITLE,DATE\n");
}

$orderByFields = explode(",", $options['o']); // Get order by options.
$filterString = $options['f']; // Get filter string
$groupByFields = explode(",", $options['g']); // Get group by options.

$transformedData = new TransformedData($data); // Initialize data array for transformations (SELECT, ORDER BY, FILTER

// Execute data transformations
$transformedData->filter($filterString);
$transformedData->aggregation($selectFields, $aggregateFields, $groupByFields[0]);
$transformedData->orderBy($orderByFields);
$transformedData->printSelect($selectFields);

