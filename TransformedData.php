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
			echo $dataItem->select($selectArray);
		}
	}


	public function orderBy($orderByArray)
	{
		if (!isset($orderByArray[0]) || $orderByArray[0] == "") return; // If no order by, do dothing

		// Lowercase all fields
		for($i = 0; $i < sizeof($orderByArray); $i++)
		{
			$orderByArray[$i] = strtolower($orderByArray[$i]);
		}

//		// Sort the array
		for ($i = 0; $i < sizeof($this->data); $i++)
		{
			$dataItem = $this->data[$i];
			$j = $i;

			while ($j > 0 && $this->data[$j - 1]->$orderByArray[0] > $dataItem->$orderByArray[0])
			{
				// Swap
				$temp = $this->data[$j];
				$this->data[$j] = $this->data[$j - 1];
				$this->data[$j - 1] = $temp;
				$j--;
			}

			if ((isset($orderByArray[1]) && $orderByArray[1] != "") && $this->data[$j - 1]->$orderByArray[0] == $dataItem->$orderByArray[0]) // Items have the same value for first sort option
			{
				$k = $j;

				while ($k > 0 && $this->data[$k - 1]->$orderByArray[1] > $dataItem->$orderByArray[1])
				{
					// Swap
					$temp = $this->data[$k];
					$this->data[$k] = $this->data[$k - 1];
					$this->data[$k - 1] = $temp;
					$k--;
				}
			}

		}
	}


	/**
	 * @param $filterArray array format [FILTER=VALUE, FILTER=VALUE, etc.]
	 */
	public function filter($filterArray)
	{
		if (sizeof($filterArray) == 0 || $filterArray[0] == "") return; // Nothing to filter by

		$transformedData = array(); // Initialize array to add objects that match the criteria to

		foreach ($this->data as $dataItem)
		{
			foreach ($filterArray as $filterItem)
			{
				// Parse filter string
				$filterExploded = explode("=", $filterItem);
				$filterName = strtolower($filterExploded[0]);
				$filterValue = $filterExploded[1];

				if ($dataItem->$filterName == $filterValue)
				{
					array_push($transformedData, $dataItem);
					break;
				}
			}
		}
		$this->data = $transformedData;
	}
} 