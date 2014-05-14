<?php
require_once("include.php");
require_once("TransformedData.php");

// Initialize the data class
$data = new Data(PATH_TO_DATA_FILE);

$options = getopt("s::o::f::g::");

// Check for valid input
if (sizeof($options) == 0)
{
	die("Usage: php query.php -sFIELDS,TO,SELECT -oFIELDS,TO,ORDER,BY -fFIELD=VALUE\n");
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
$filterFields = explode(",", $options[f]); // Get filter options.
$groupByFields = explode(",", $options[g]); // Get group by options.

$transformedData = new TransformedData($data); // Initialize data array for transformations (SELECT, ORDER BY, FILTER

$transformedData->filter($filterFields);
$transformedData->aggregation($selectFields, $aggregateFields, $groupByFields[0]);
$transformedData->orderBy($orderByFields);
$transformedData->printSelect($selectFields);
