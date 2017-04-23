<?php
// this code is only called once to load the annualized rate of return for all stocks.
// As well as to calculate the stocks which are stricly increasing each year.
//TODO another file for these two  ^ also calculate total risk for each stock here?

include '/var/www/dbc.php';
include '/var/www/isHelpers.php';

try{
	// Get list of all stock names
	$sql = "SELECT DISTINCT symbol from stock_data";
	$query = $dbh->prepare($sql);
	$query->execute();
	
	// Loop through all stocks and calculate their Annual Rate of return
	// Add it to the stock_ror table
	while ($rs = $query->fetch(PDO::FETCH_OBJ))
	{
		$symbol = $rs->symbol;	
		//Calculate ROR from stock activity at beggining of every year. 2005-2013
		$begDate = "2005-01-01";
		$endDate = "2013-12-30";
		$begPrice = getStockPrice($symbol, $begDate, $dbh);
		$endPrice = getStockPrice($symbol, $endDate, $dbh);
	
		$days = ceil(abs(strtotime($begDate) - strtotime($endDate))/ 86400);

		$annualizedReturn = pow($endPrice - $begPrice, (1/($days/365)));
	
		if (is_nan($annualizedReturn))
		{
			$annualizedReturn = $annualizedReturn = pow($begPrice - $endPrice, (1/($days/365)))*-1;
		}
		
		$sql = "INSERT INTO stock_aror VALUES (:symbol, :aror)";
		$innerquery = $dbh->prepare($sql);
		$innerquery->bindValue(':symbol', $symbol);
		$innerquery->bindValue(':aror', $annualizedReturn);
		$innerquery->execute();	
	}

}
catch (PDOException $e)
{
	echo "Error inputting information".$e;
}
	
?>
