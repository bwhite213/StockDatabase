<?php

include '/var/www/dbc.php';
include '/var/www/isHelpers.php';

// Called once to populate stock_increasing table with symbols of annually increasing stocks.
try{
	// Get list of all stock names
	$sql = "SELECT DISTINCT symbol from stock_data";
	$query = $dbh->prepare($sql);
	$query->execute();
	
	// Loop through all stocks and see which are constantly increasing through the year.
	// Add to stock_increasing
	while ($rs = $query->fetch(PDO::FETCH_OBJ))
	{
		$symbol = $rs->symbol;	
		
		// Get stock price on january first of each year. from 2005-2013
		$p1 = getStockPrice($symbol, "2005-01-01", $dbh);				
		$p2 = getStockPrice($symbol, "2006-01-01", $dbh);				
		$p3 = getStockPrice($symbol, "2007-01-01", $dbh);				
		$p4 = getStockPrice($symbol, "2008-01-01", $dbh);				
		$p5 = getStockPrice($symbol, "2009-01-01", $dbh);				
		$p6 = getStockPrice($symbol, "2010-01-01", $dbh);				
		$p7 = getStockPrice($symbol, "2011-01-01", $dbh);				
		$p8 = getStockPrice($symbol, "2012-01-01", $dbh);				
		$p9 = getStockPrice($symbol, "2013-01-01", $dbh);		
		
		//Ensure all 9 stock prices are stictly increasing.
		if ($p1 < $p2 && $p2 < $p3 && $p3 < $p4 && $p4 < $p5 && $p5 < $p6 && $p6 < $p7 && $p7 < $p8 && $p8 < $p9)
		{
			//Insert into the stock_increasing table
			$sql = "INSERT INTO stock_increasing VALUES (:symbol)";
			$innerquery = $dbh->prepare($sql);
			$innerquery->bindValue(':symbol', $symbol);
			$innerquery->execute();

		}

	}


}
catch (PDOException $e)
{
	echo "Error inputting information".$e;
}

?>
