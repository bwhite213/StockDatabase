<?php

//Used to initialize funds with cash
function addCashToFund($name, $cash, $date, $percentage, $dbh)
{
	// Insert or update the fund if currently in database.
	try{	
		$sql = "INSERT INTO fund_cash VALUES (:name, :cash*percentage, :percentage, :start_date, :start_cash) 
	ON DUPLICATE KEY 
	UPDATE name=:name, cash=cash+(:cash*percentage), percentage=percentage";
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':cash', $cash);	
		$query->bindValue(':percentage', $percentage);
		$query->bindValue(':start_date', $date);
		$query->bindValue(':start_cash', $cash);	
		$query->execute();
	}
	catch (PDOException $e)
	{
		echo "Error adding cash to fund<br>";
	}
}

//Used to initialize individuals
function addCashToIndividual($name, $cash, $date, $dbh)
{
	try{
		// Insert or update the Individual if currently in database.
		$sql = "INSERT INTO individual_cash VALUES (:name, :cash, :start_date, :ret) ON DUPLICATE KEY UPDATE cash=cash+:cash";
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':cash', $cash);
		$query->bindValue(':ret', (float) 0);	
		$query->bindValue(':start_date', $date);
		$query->execute();	
	}
	catch (PDOException $e)
	{
		echo "Error adding cash to individual<br>";
	}
}

// individual with $name invests $amount in current $fund and is 
// dispersed between funds assets.
function individualInvestInFund($name, $buy, $amount, $date, $dbh) 
{
	// See if fund has made no investments
	$fundCashPercentage = getFundCashPercentage($buy, $dbh);
	// Disperse cash into the fund $buy
	addFundCash($buy, $fundCashPercentage, $amount, $date, $dbh);
	
	try{
		// Add to individual_fund
		$sql = "INSERT INTO individual_fund(person, fund, shares, purchase_date) VALUES(:name, :buy, :amount, :purchase_date)";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':buy', $buy);
		$query->bindValue(':amount', $amount);
		$query->bindValue(':purchase_date', $date);
		$query->execute();

		// update the fund_cash start_value to add investment
		$sql = "UPDATE fund_cash SET start_cash = start_cash+:cash WHERE name = :buy";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':buy', $buy);
		$query->bindValue(':cash', $amount);
		$query->execute();
		}
	catch (PDOException $e)
	{
		echo "Error investing into fund<br>";
	}

}

// Called to ensure the individual has appropriate funds and removes the funds from them
function indSufficientFunds($name, $amount , $dbh)
{
	try
	{
		$sql = "SELECT * FROM individual_cash where cash >= :amount AND name = :name";	
		$query = $dbh->prepare($sql);
		$query->bindValue(':amount', $amount);
		$query->bindValue(':name', $name);
		$query->execute();

		if($query->rowCount() > 0){
			// Remove the amount from the individual
			$sql = "UPDATE individual_cash SET cash = cash - :amount WHERE name = :name";	
			$query = $dbh->prepare($sql);
			$query->bindValue(':amount', $amount);
			$query->bindValue(':name', $name);
			$query->execute();		
			return true;
		}else{
			return false;
		}
	}
	catch (PDOException $e)
	{
		echo "Error checking individuals funds<br>";
	}
}

?> 
