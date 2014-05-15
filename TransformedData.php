<?php
require_once("include.php");

class TransformedData
{
	protected $data = array();


	/**
	 * @param Data $data Data object returned from the Data class, which in turn comes from the raw file.
	 */
	function __construct(Data $data)
	{
		$this->data = $data->toArray();
	}

	////////////////////////////////////////////////////////////////////////////////
	// Transform functions (SELECT, ORDER BY, FILTER)
	////////////////////////////////////////////////////////////////////////////////

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


	/**
	 * Sorts the data array using insertion sort. If two elements are the same, sort by the second field.
	 * @param $orderByArray Fields to order by (Maximum of two)
	 */
	public function orderBy($orderByArray)
	{
		if (!isset($orderByArray[0]) || $orderByArray[0] == "") return; // If no order by, do dothing

		// Lowercase all fields
		for ($i = 0; $i < count($orderByArray); $i++)
		{
			$orderByArray[$i] = strtolower($orderByArray[$i]);
		}

		// Sort the array
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
	 * Filter the results based on a boolen string (e.g. THIS=THAT AND THESE=THOSE OR THEY=THEM)
	 * @param $filterString string Boolean string for filtering
	 */
	public function filter($filterString)
	{
		if ($filterString == "") return; // Nothing to filter by

		$filterPartsAnd = explode(" AND ", $filterString); // Parse boolean filter for AND

		// Initialize array to add objects that match the criteria to
		$transformedData = array();
		for ($i = 0; $i < count($filterPartsAnd); $i++)
		{
			$transformedData[$i] = array();
		}

		// Loop though the generated array of ANDs and ORs, calculating the OR portions.
		// Each AND section gets put into a separate array to be merged later.
		foreach ($this->data as $dataItem)
		{
			for ($i = 0; $i < count($filterPartsAnd); $i++)
			{
				$filterPartsOr = explode(" OR ", $filterPartsAnd[$i]); // Parse boolean filter for OR

				foreach ($filterPartsOr as $filterItem)
				{
					// Parse filter string
					$filterExploded = explode("=", $filterItem);
					$filterName = strtolower($filterExploded[0]);
					$filterValue = str_replace("\"", "", $filterExploded[1]); // Remove quotes

					if ($dataItem->$filterName == $filterValue)
					{
						array_push($transformedData[$i], $dataItem); // Match – add it to the array
						break;
					}
				}
			}
		}

		if (count($transformedData) == 1) // If there is only one AND portion, then there no arrays to merge. Just write the first filtered array.
		{
			$this->data = $transformedData[0];
		}
		else
		{
			$transformedDataAnded = call_user_func_array('array_intersect', $transformedData); // Calls the array intersect function with an arbitrary number of arrays

			// The merged data comes back with keys preserved, which throws off the sorting function, so we need to reset the indices
			$transformedDataIndicesFixed = array();
			foreach ($transformedDataAnded as $dataItem)
			{
				array_push($transformedDataIndicesFixed, $dataItem);
			}

			$this->data = $transformedDataIndicesFixed;
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// Aggregation functions and aggregation helper functions
	////////////////////////////////////////////////////////////////////////////////

	/**
	 * Function to process what kind of aggregator per field to run.
	 */
	function aggregation($selectFields, $aggregateFields, $groupByField)
	{
		// Check to see if selected fields and aggregate fields have the same length, and if not, do nothing.
		if (count($selectFields) != count($aggregateFields))
		{
			echo "Invalid parameters for aggregation!\n";

			return;
		}

		// Check if there are any aggregation parameters
		$aggregationParametersExist = false;
		foreach ($aggregateFields as $aggregateField)
		{
			$aggregationParametersExist = $aggregateField != "";
		}
		if (!$aggregationParametersExist)
		{
			return;
		}

		$groupByField = strtolower($groupByField); // Lowercase field name

		$aggregatedDataIterations = array(); // Initialize array for storing aggregated data iterations

		for ($i = 0; $i < count($selectFields); $i++)
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

		// Initialize merged array
		$aggregatedData = array();
		for ($i = 0; $i < count($aggregatedDataIterations[0]); $i++)
		{
			$aggregatedData[$i] = new DataItem();
		}

		// Merge aggregated data sets together
		for ($i = 0; $i < count($aggregatedDataIterations); $i++)
		{
			for ($j = 0; $j < count($aggregatedDataIterations[$i]); $j++)
			{
				foreach (array("stb", "title", "provider", "date", "rev", "viewtime", "count") as $field)
				{
					if ($aggregatedDataIterations[$i][$j]->$field != "")
					{
						$aggregatedData[$j]->$field = $aggregatedDataIterations[$i][$j]->$field;
					}
				}
			}
		}

		$this->data = $aggregatedData; // Set global data array to the result of the aggregation and merge.
	}


	/**
	 * Takes in the resulting map from an aggregate function and creates a list of DataItems for outputting
	 */
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


	/**
	 * Function to calculate the minimum value for a given field.
	 */
	protected function aggregateMin($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";

			return;
		}

		$map = array(); // Initialize map for keeping track of mins
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField])) // Field already exists in map
			{
				if ($this->data[$i]->$selectField < $map[$this->data[$i]->$groupByField]) // Incoming value is less than existing value
				{
					$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField; // Overwrite existing value with new min
				}
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField; // field doesn't exist – add it
			}
		}

		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}


	/**
	 * Function to calculate the maximum value for a given field.
	 */
	protected function aggregateMax($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";

			return;
		}

		$map = array(); // Initialize map for keeping track of maxes
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField])) // Field already exists in map
			{
				if ($this->data[$i]->$selectField > $map[$this->data[$i]->$groupByField]) // Incoming value is greater than existing value
				{
					$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField; // Overwrite existing value with new min
				}
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField; // field doesn't exist – add it
			}
		}

		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}


	/**
	 * Function to calculate the sum of all values for a given field.
	 */
	protected function aggregateSum($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";

			return;
		}

		$map = array(); // Initialize map for keeping track of sums
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField])) // Field already exists in map
			{
				$map[$this->data[$i]->$groupByField] += $this->data[$i]->$selectField; // Add incoming value to existing value
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = $this->data[$i]->$selectField; // field doesn't exist – add it
			}
		}

		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}


	/**
	 * Function to calculate the unique count for a given field.
	 */
	protected function aggregateCount($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";

			return;
		}

		// Check for group by field
		if ($selectField != $groupByField)
		{
			die("Group by parameter must be same as the field to get counts for!\n");

			return;
		}

		$map = array(); // Initialize map for keeping track of counts
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField])) // Field already exists in map
			{
				$map[$this->data[$i]->$groupByField]++;// Add one to existing value
			}
			else
			{
				$map[$this->data[$i]->$groupByField] = 1; // field doesn't exist – add it and initialize to 1
			}
		}

		return TransformedData::buildAggregateData($groupByField, "count", $map);
	}


	/**
	 * Function to calculate the unique values for a given field.
	 */
	protected function aggregateCollect($selectField, $groupByField)
	{
		// Check for group by field
		if ($groupByField == "")
		{
			echo "Group by parameter is required for aggregate functions!\n";

			return;
		}

		$map = array(); // Initialize map for keeping track of collects
		for ($i = 0; $i < count($this->data); $i++)
		{
			if (isset($map[$this->data[$i]->$groupByField])) // Field already exists in map
			{
				if (!in_array($this->data[$i]->$selectField, $map[$this->data[$i]->$groupByField])) // Field doesn't already exist in unique values list
				{
					array_push($map[$this->data[$i]->$groupByField], $this->data[$i]->$selectField); // Add to array.
				}
			}
			else
			{
				// field doesn't exist – initialize array and add it.
				$map[$this->data[$i]->$groupByField] = array();
				array_push($map[$this->data[$i]->$groupByField], $this->data[$i]->$selectField);
			}
		}

		return TransformedData::buildAggregateData($groupByField, $selectField, $map);
	}
} 
