<?php
require_once("DataItem.php");

function lineToObject($lineText)
{
	$lineArray = explode("|", $lineText);
	$dataObject = new DataItem(
		lineArray[0], // stb
		lineArray[1], // title
		lineArray[2], // provider
		lineArray[3], // date
		lineArray[4], // rev
		lineArray[5]  // viewTime
	);

	return $dataObject;
}