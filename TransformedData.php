<?php
require_once("include.php");

class TransformedData
{
	protected $data = array();


	function __construct(Data $data)
	{
		$this->data = $data->toArray();
	}


	// Transform functions (SELECT, ORDER BY, FILTER)

	/**
	 * Prints out the transformed data based on selected fields passed in.
	 */
	public function printSelect($selectArray)
	{
		foreach ($this->data as $dataItem)
		{
			echo $dataItem->select($selectArray) . "\n";
		}
	}


	public function transformOrderBy($orderByArray)
	{
		foreach ($this->data as $dataItem)
		{

		}
	}

	public function transformFilter($filter)
	{
		$transformedData = array(); // Initialize array to add objects that match the criteria to

		foreach ($this->data as $dataItem)
		{

		}
	}
} 