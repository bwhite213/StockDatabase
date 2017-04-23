<?php

// Get the start date of the fund
function getFundStartDate($selling, $dbh)
{
	try{
		$sql = "SELECT start_date FROM fund_cash WHERE name = :selling";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':selling', $selling);
		$query->execute();
		$value = $query->fetch(PDO::FETCH_BOTH);
	
		return $value['start_date'];
	}
	catch (PDOException $e)
	{
		echo "Error getting fund start date<br>";
	}
}

//Returns the date the individual invested in the fund.
function getFundInvestmentDate($seller,$selling,$dbh)
{	
	try{
		$sql = "SELECT purchase_date FROM individual_fund WHERE person = :seller AND fund = :fund";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':seller', $seller);
		$query->bindValue(':fund', $selling);
		$query->execute();
		$value = $query->fetch(PDO::FETCH_BOTH);
	
		return $value['purchase_date'];
	}
	catch (PDOException $e)
	{
		echo "Error getting fund investment date<br>";
	}
}

// Updates the Fund_cash table so the fund has cash- :amount and remove initial investment from start_cash
function removeCashFromFund($name, $amount, $initial, $dbh) 
{
		
		// Get the fund_cash Percentage
		$percentage = getFundCashPercentage($name ,$dbh);
		$cashToSubtract = $amount*$percentage;
	try 
	{
		$sql = "UPDATE fund_cash SET cash = cash - :amount, start_cash = start_cash-:initial WHERE name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':amount', $cashToSubtract);
		$query->bindValue(':initial', $initial);
		$query->execute();	
	}
	catch (PDOException $e)
	{
		echo "Error removing cash from fund.<br>";
	}
}

// Used when individual sells a fund.
function removeSharesFromFund($fund ,$stock , $sharesToDivest, $dbh)
{
	try{
		$sql = "UPDATE fund_stock SET shares=shares-:divest WHERE name=:fund AND symbol=:stock";
		$query = $dbh->prepare($sql);
		$query->bindValue(':fund', $fund);
		$query->bindValue(':stock', $stock);
		$query->bindValue(':divest', $sharesToDivest);
		$query->execute();
	}
	catch (PDOException $e)
	{
		echo "Error removing shares from fund<br>";
	}
}	

//Get the investment made into the fund by an individual.
function getIndividualsInvestment($seller,$selling, $dbh)
{
	try{
		$sql = "SELECT shares FROM individual_fund where person= :name AND fund = :fund";
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $seller);
		$query->bindValue(':fund', $selling);
		$query->execute();
		$rs = $query->fetch(PDO::FETCH_OBJ);

		return $rs->shares;
	}
	catch (PDOException $e)
	{
		echo "Error getting individuals investment into a fund<br>";
	}
}

//Used when selling funds
function getInitialStockPrice($stock, $date , $dbh)
{
	try{
		$sql = "SELECT adj_close FROM stock_data where symbol = :stock AND date >= :date ORDER BY date ASC LIMIT 1";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':stock', $stock);
	 	$query->bindValue(':date', $date);
		$query->execute();
		$adj_close_value = $query->fetch(PDO::FETCH_BOTH);
	
		return $adj_close_value[0];
	}
	catch (PDOException $e)
	{
		echo "Error getting initial stock price. <br>".$e;
	}
}

//Remove the individual_fund information from the table
function removeIndividualFund($seller, $selling, $dbh)
{
	try{
		$sql = "DELETE FROM individual_fund WHERE person = :name AND fund = :fund";
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $seller);
	 	$query->bindValue(':fund', $selling);
		$query->execute();
	}
	catch (PDOException $e)
	{
		echo "Error removing individuals fund<br>".$e;
	}
}

//Used when selling funds to give cash back to individual. 
function addCashToIndividualSelling($name, $cash, $dbh)
{
	try{
		// Insert or update the Individual if currently in database.
		$sql = "UPDATE individual_cash SET cash = cash + :cash, ret = ret + :cash WHERE name = :name";
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':cash', $cash);		
		$query->execute();
	}
	catch (PDOException $e)
	{
		echo "Error adding cash to individaul when selling.<br>".$e;
	}	
}

//Used to insert values into ind_fund_return table. 
function insertIntoIndFundReturn($name, $fund,$return, $dbh)
{
	try {
		$stmt = $dbh->prepare("INSERT INTO ind_fund_return VALUES (:name, :fund, :return)");
		$stmt->bindValue(':name', $name);	
		$stmt->bindValue(':fund', $fund);
		$stmt->bindValue(':return', $return);
		$stmt->execute();
	}
	catch (PDOException $e)
	{
		echo "Error Inserting into ind_fund_return.<br>".$e;
	}
}

?>
