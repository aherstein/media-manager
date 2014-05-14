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

$selectFieldsAll = explode(",", $options[s]); // Get select options.

// Split select by aggregate functions
$selectFields = array();
$aggregateFields = array();
for ($i = 0; $i < count($selectFieldsAll); $i++)
{
	$split = explode(":", $selectFieldsAll[$i]);
	$selectFields[$i] = $split[0];
	$aggregateFields[$i] = $split[1];
}

$orderByFields = explode(",", $options[o]); // Get order by options.
// $filterFields = explode(",", $options[f]); // Get filter options.
$filterString = $options[f];
$groupByFields = explode(",", $options[g]); // Get group by options.

$transformedData = new TransformedData($data); // Initialize data array for transformations (SELECT, ORDER BY, FILTER

// Execute data transformations
$transformedData->filter($filterString);
if ($groupByFields[0] != "") $transformedData->aggregation($selectFields, $aggregateFields, $groupByFields[0]);
$transformedData->orderBy($orderByFields);
$transformedData->printSelect($selectFields);
