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
		for($i = 0; $i < count($orderByArray); $i++)
		{
			$orderByArray[$i] = strtolower($orderByArray[$i]);
		}

//		// Sort the array
		for ($i = 0; $i < count($this->data); $i++)
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
		if (count($filterArray) == 0 || $filterArray[0] == "") return; // Nothing to filter by

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


	function aggregation($selectFields, $aggregateFields, $groupByField)
	{
		// Check to see if selected fields and aggregate fields have the same length, and if not, do nothing.
		if (count($selectFields) != count($aggregateFields))
		{
			echo "Invalid parameters for aggregation!\n";
			return;
		}

		$groupByField = strtolower($groupByField); // Lowercase field name

		$aggregatedDataIterations = array(); // Initialize array for storing aggregated data iterations

		for($i = 0; $i < count($selectFields); $i++)
		{
			$selectField = strtolower($selectFields[$i]); // Lowercase field name
			switch (strtolower($aggregateFields[$i]))
			{
				case "min":
					array_push($aggregatedDataIterations, $this->aggregateMin($selectField, $groupByField));
					break;
				case "max":
					array_push($aggregatedDataIterations, $this->aggregateMax($selectField, $groupByField));
					break;
				case "sum":
					array_push($aggregatedDataIterations, $this->aggregateSum($selectField, $groupByField));
					break;
				case "count":
					array_push($aggregatedDataIterations, $this->aggregateCount($selectField, $groupByField));
					break;
				case "collect":
					array_push($aggregatedDataIterations, $this->aggregateCollect($selectField, $groupByField));
					break;
				case "":
					break;
				default:
					echo "Invalid aggregate function!";
			}
		}

		// Merge aggregated data sets together

		// Initialize merged array
		$aggregatedData = array();
		for ($i = 0; $i < count($aggregatedDataIterations[0]); $i++)
		{ 
			$aggregatedData[$i] = new DataItem();
		}

		for($i = 0; $i < count($aggregatedDataIterations); $i++)
		{
			for($j = 0; $j < count($aggregatedDataIterations[$i]); $j++)
			{
				foreach (array("stb","title","provider","date","rev","viewtime","count") as $field)
				{
					if ($aggregatedDataIterations[$i][$j]->$field != "")
					{
						$aggregatedData[$j]->$field = $aggregatedDataIterations[$i][$j]->$field;
					}
				}
			}
		}
		// print_r($aggregatedData);
		$this->data = $aggregatedData; // Set global data array to the result of the aggregation and merge.
	}


	protected static function buildAggregateData($groupByField, $selectField, $map)
	{
		$transformedData = array();
		$col1 = array_keys($map);
		$col2 = array_values($map);
		for ($i = 0; $i < count($map); $i++)
		{
			// Build string for collect field
			if (is_array($col2[$i]))
			{
				$col2[$i] = "[" . implode(",", $col2[$i]) . "]";
			}

			$transformedData[$i] = new DataItem();
			$transformedData[$i]->$groupByField = $col1[$i];
			$transformedData[$i]->$selectField = $col2[$i];
		}
		return $transformedData;
	}


	protected function aggregateMin($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";
			return;
		}

		$map = array();
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField]))
			{
				if ($this->data[$i]->$selectField < $map[$this->data[$i]->$groupByField])
				{
					$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField;
				}
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField;
			}
		}
		
		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}



	protected function aggregateMax($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";
			return;
		}

		$map = array();
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField]))
			{
				if ($this->data[$i]->$selectField > $map[$this->data[$i]->$groupByField])
				{
					$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField;
				}
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField;
			}
		}
		
		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}



	protected function aggregateSum($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";
			return;
		}

		$map = array();
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField]))
			{
				
				$map[$this->data[$i]->$groupByField] += $this->data[$i]->$selectField;
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField;
			}
		}
		
		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}



	protected function aggregateCount($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";
			return;
		}

		$map = array();
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField]))
			{
				
				$map[$this->data[$i]->$groupByField]++;
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = 1;
			}
		}
		
		return TransformedData::buildAggregateData($groupByField, "count", $map);
	}



	protected function aggregateCollect($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";
			return;
		}

		$map = array();
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField]))
			{
				if (!in_array($this->data[$i]->$selectField, $map[$this->data[$i]->$groupByField]))
				{
					array_push($map[$this->data[$i]->$groupByField], $this->data[$i]->$selectField);
				}
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = array();
				array_push($map[$this->data[$i]->$groupByField], $this->data[$i]->$selectField);
			}
		}
		
		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}
} 
