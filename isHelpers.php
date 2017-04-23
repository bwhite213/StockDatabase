<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);


//Returns true if the name given is a stock (in stock_data table)
function isStock($selling , $dbh)
{
	try{
		$sqlind = "SELECT * FROM stock_data where symbol = :symbol";	
		$query_ind = $dbh->prepare($sqlind);
		$query_ind->bindValue(':symbol', $selling);
		$query_ind->execute();

		if ($query_ind->rowCount() > 0)
		{
			return true;
		}
	}
	catch (PDOException $e)
	{
		echo "Error checking isStock<br>";
	}

	return false;
}

//Returns true if the name given is a fun (in fund_cash table)
function isFund($name, $dbh)
{

	$sql = "SELECT * FROM fund_cash where name = :name";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->execute();

	try{
		if ($query->rowCount() > 0)
		{
			return true;
		}
	}
	catch (PDOException $e)
	{
		echo "Error checking isFund()<br>";
	}

	return false;
}

//Returns true if the name given is an individual (in individual_cash table)
function isIndividual($name , $dbh)
{

	$sql = "SELECT * FROM individual_cash where name = :name";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->execute();

	try{
		if ($query->rowCount() > 0)
		{
			return true;
		}
		}
	catch (PDOException $e)
	{
		echo "Error checking isIndividual<br>";
	}

	return false;
}

//*****GETTERS/SETTERS ****//
//Returns an individuals current cash value. not including assets
function getIndCash($seller ,$dbh)
{
	try{
		$sql = "SELECT cash FROM individual_cash where name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $seller);
		$query->execute();
		$value = $query->fetch(PDO::FETCH_BOTH);
	
		return $value['cash'];
	}
	catch (PDOException $e)
	{
		echo "Error getting individuals cash<br>";
	}
}

//Returns the adj_close of the stock that is >= the date given (the next open day)
function getStockPrice($stock, $date , $dbh)
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
		echo "Error getting stock price.<br>";
	}

	return 0;
}


//Gets the funds current percentage of value in cash.
function getFundCashPercentage($name ,$dbh)
{
	try{
		$sql = "SELECT percentage FROM fund_cash where name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->execute();
		$value = $query->fetch(PDO::FETCH_BOTH);
	
		return $value['percentage'];
	}
	catch (PDOException $e)
	{
		echo "Error getting fund percentage.<br>";
	}
	return 0;
}

//Get the funds total cash including assets at the current date
//Used when calculating percentages during buys.
function getFundTotalCash($name, $currDate, $dbh)
{ 
	try{
		$sql = "SELECT cash, percentage FROM fund_cash where name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->execute();
		$value = $query->fetch(PDO::FETCH_BOTH);
	
		$cashHeld = $value['cash'];

		//Add up assets if there are any.
		$sql = "SELECT shares, symbol FROM fund_stock where name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->execute();

	 	$cashAndAssets = $cashHeld;

		while($rs = $query->fetch(PDO::FETCH_OBJ))
		{
			$currStock = $rs->symbol;
			$shares = $rs->shares;
			$currStockPrice = getStockPrice($currStock, $currDate ,$dbh);
	
			$cashAndAssets = $cashAndAssets + ($currStockPrice * $shares);
		}

		return $cashAndAssets;
	}
	catch (PDOException $e)
	{
		echo "Error getting funds total cash<br>";
	}
	
}

function getIndCashAndStock($name,$currDate, $fund, $dbh)
{
	try{	
		$cashHeld = getIndCash($name,$dbh);

		//Add up assets if there are any.
		$sql = "SELECT shares, symbol FROM individual_fund where name = :name and fund = :fund";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':fund', $fund);
		$query->execute();

	 	$cashAndAssets = $cashHeld;

		$currStock = $rs->symbol;
		$shares = $rs->shares;
		$currStockPrice = getStockPrice($currStock, $currDate ,$dbh);

		$cashAndAssets = $cashAndAssets + ($currStockPrice * $shares);

		return $cashAndAssets;
	}
	catch (PDOException $e)
	{
		echo "Error getting ind total cash and stock<br>".$e;
	}
}

//Get the Individuals total cash including assets at the current date
//Used when calculating percentages during buys.
function getIndTotalCash($name, $currDate, $dbh)
{ 
	try{	
		$cashHeld = getIndCash($name,$dbh);

		//Add up assets if there are any.
		$sql = "SELECT shares, fund FROM individual_fund where name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->execute();

	 	$cashAndAssets = $cashHeld;

		while($rs = $query->fetch(PDO::FETCH_OBJ))
		{
			$currStock = $rs->fund;
			if( isStock($currStock,$dbh))
			{
				$shares = $rs->shares;
				$currStockPrice = getStockPrice($currStock, $currDate ,$dbh);
	
				$cashAndAssets = $cashAndAssets + ($currStockPrice * $shares);
			}
			else 
			{
			}
		}

		return $cashAndAssets;
	}
	catch (PDOException $e)
	{
		echo "Error getting Ind total cash<br>".$e;
	}
	
}

//Gets the funds cash without assets.
function getFundCash($name, $currDate, $dbh)
{ 
	$sql = "SELECT cash, percentage FROM fund_cash where name = :name";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->execute();
	$value = $query->fetch(PDO::FETCH_BOTH);
	
	$cashHeld = $value['cash'];

	//Add up assets if there are any.
	$sql = "SELECT shares, symbol FROM fund_stock where name = :name";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->execute();

 	return $cashHeld;
	
}

// Updates the Fund_cash table so the fund has cash- :amount and :percantage given.
function subtractFundCash($name, $percentage, $amount, $dbh) 
{
	$sql = "UPDATE fund_cash SET cash = cash - :amount, percentage = :percentage  WHERE name = :name";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->bindValue(':amount', $amount);
	$query->bindValue(':percentage', $percentage);
	$query->execute();	
}

// Updates the Fund_cash table and fund_stock so the fund has cash- :amount and :percantage given.
function addFundCash($name, $percentage, $amount, $date, $dbh) 
{
	$sql = "UPDATE fund_cash SET cash = cash + :amount, percentage = :percentage  WHERE name = :name";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->bindValue(':amount', $amount * $percentage);
	$query->bindValue(':percentage', $percentage);
	$query->execute();

	// If the percentage is not 1 then disperse funds through all of the stocks
	// in the portfolio.
	if ($percentage != (float) 1)
	{
		$sql = "SELECT shares, symbol, percentage FROM fund_stock where name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->execute();

		while($rs = $query->fetch(PDO::FETCH_OBJ))
		{	// for each stock owned by fund. add number of shares with 
			// $percentage and $amount
			$currStock = $rs->symbol;
			$currShares = $rs->shares;
			$percOfStock = $rs->percentage;
			$currStockPrice = getStockPrice($currStock, $date ,$dbh);
			$sharesToInvest = ($amount*$percOfStock)/$currStockPrice;
			//Update the funds stock to hold the right amount
			// Of shares currShares+sharesToInvest			
			updateFundStock($name, $currStock, $sharesToInvest + $currShares,$dbh);	
		}
	}
}

// Returns the Appreciation of the fund at the current date.
function getAppreciationOfFund($fund, $date, $dbh)
{	
	$currFundCash = getFundTotalCash($fund, $date, $dbh);
	$fundStartValue = getFundStartValue($fund, $dbh);

	// Subtract shares still owned by others.
	$sql = "SELECT shares FROM individual_fund WHERE fund = :fund";
	$query = $dbh->prepare($sql);
	$query->bindValue(':fund', $fund);
	$query->execute();

	$appreciation = $currFundCash;

	while ($rs = $query->fetch(PDO::FETCH_OBJ))
	{
		$share = $rs->shares/$fundStartValue;
		$appreciation = $appreciation - ($share*$currFundCash);
	}

	$appreciation = $appreciation/$fundStartValue;
	return $appreciation;
}

// Gets the funds start value
function getFundStartValue($fund, $dbh)
{
	$sql = "SELECT start_cash FROM fund_cash where name= :name";
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $fund);
	$query->execute();
	
	$rs = $query->fetch(PDO::FETCH_OBJ);
	return $rs->start_cash;
}

// Gets the shares a person owns of a fund
function getIndividualFundShares($seller, $selling, $date, $dbh)
{
	$sql = "SELECT shares FROM individual_fund where person= :name AND fund = :fund";
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $seller);
	$query->bindValue(':fund', $selling);
	$query->execute();
	
	$rs = $query->fetch(PDO::FETCHOBJ);
	$share =  $rs->shares;
	$share = $share/getFundTotalCash($selling, $date, $dbh);
}

// Used by addCashToFund when distributing cash between stocks
function updateFundStock($name, $currStock, $newShares, $dbh)
{
	$sql = "UPDATE fund_stock SET shares = :shares WHERE name = :name AND symbol = :symbol";
	$query = $dbh->prepare($sql);
	$query->bindValue(':shares', $newShares);
	$query->bindValue(':name', $name);
	$query->bindValue(':symbol', $currStock);
	$query->execute();
}

// Used for funds to invest in a stock after the amount has been removed from the fund
function fundInvestHelper($name, $buy, $amount, $percentage, $date, $dbh) 
{	
	// calculate the number of shares to invest.
	$stockPrice = getStockPrice($buy, $date, $dbh);
	$shares = (float) $amount/$stockPrice;
		
	//Insert info into fund_stock table
	$sql = "INSERT INTO fund_stock(name, symbol, shares, percentage, purchase_date) VALUES(:name, :buy, :shares, :percentage, :purchase_date)
ON DUPLICATE KEY UPDATE shares=shares+:shares, percentage =percentage+:percentage";	
	$query = $dbh->prepare($sql);
	$query->bindValue(':name', $name);
	$query->bindValue(':buy', $buy);
	$query->bindValue(':shares', $shares);
	$query->bindValue(':percentage', $percentage);
	$query->bindValue(':purchase_date', $date);
	$query->execute();

}

?>
