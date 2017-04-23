<?php

include '/var/www/dbc.php';

try{
	// Get list of all stock names
	$sql = "SELECT DISTINCT symbol from stock_data";
	$query = $dbh->prepare($sql);
	$query->execute();
	
	// Loop through all stocks and calculate their Risk
	// Add to stock_risk table
	while ($rs = $query->fetch(PDO::FETCH_OBJ))
	{
		$symbol = $rs->symbol;	
		
		//Calculate the risk of a stock loop through all qoutes
		$sql = "SELECT symbol, adj_close FROM stock_data where symbol = :symbol";
		$innerquery = $dbh->prepare($sql);
		$innerquery->bindValue(':symbol', $symbol);
		$innerquery->execute();				
			
		$currMax = 0;
		$maxDrop = 0;
		//loop through qoutes for current stock
		while ($rs2 = $innerquery->fetch(PDO::FETCH_OBJ))
		{
			$close = $rs2->adj_close;
			if ($close >= $currMax)
			{
				$currMax = $close;
			}

			$currRisk = $currMax - $close;
			
			if ($currRisk >= $maxDrop)
			{
				$maxDrop = $currRisk;
			}
		}

		$sql = "INSERT INTO stock_risk VALUES (:symbol, :risk)";
		$innerquery = $dbh->prepare($sql);
		$innerquery->bindValue(':symbol', $symbol);
		$innerquery->bindValue(':risk', $maxDrop);
		$innerquery->execute();	
	}

	echo "Risk successfully loaded <br>";

}
catch (PDOException $e)
{
	echo "Error inputting information".$e;
}

?>
