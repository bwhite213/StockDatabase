<?php

include 'sellHelpers.php';

// Sell Functions
// Sells the stock/fund $selling for $seller on the specified $date
function sell($seller, $selling, $date, $dbh )
{
	// Check if the seller is an individual or a fund.
	if (isIndividual($seller ,$dbh))
	{
		indSell( $seller, $selling, $date, $dbh);		
	}
	elseif(isFund($seller, $dbh))
	{
		if (isStock($selling, $dbh))
		{
			fundSellStock($seller, $selling, $date, $dbh);
		}
		else
		{	
			echo "Error sell(): funds can only buy/sell stocks: ". $selling."<br>";
		}
	}
	else 
	{
		echo "Error sell(): seller: ".$seller." is not an individual or a fund.";
	}
}

function fundSellStock($seller, $selling, $date, $dbh)
{
	// Update the sellers cash with current stock price and users shares in the stock.
		
	$sql = "SELECT shares, percentage FROM fund_stock WHERE name = :fund AND symbol = :symbol";
	$query = $dbh->prepare($sql);
	$query->bindValue(':fund', $seller);
	$query->bindValue(':symbol', $selling);
	$query->execute();
	
	try{
		if ($query->rowCount() > 0)
		{
			$rs = $query->fetch(PDO::FETCH_OBJ);
			$percentage = $rs->percentage;
			$shares = $rs->shares;

			$currStockPrice = getStockPrice($selling,$date,$dbh);
			$returnToCash = $currStockPrice * $shares;

			//Update the funds cash and percentage with returns from the stock sold.	
			$sql = "UPDATE fund_cash SET cash=cash+:cash, percentage = percentage+:perc WHERE name = :name";
			$query = $dbh->prepare($sql);
			$query->bindValue(':name', $seller);
			$query->bindValue(':cash', $returnToCash);
			$query->bindValue(':perc', $percentage);
			$query->execute();

			// remove from fund_stock
			$sql = "DELETE FROM fund_stock WHERE name = :name AND symbol=:selling";
			$query = $dbh->prepare($sql);
			$query->bindValue(':name', $seller);
			$query->bindValue(':selling', $selling);
			$query->execute();
		

		}
		else
		{
			"Error fundSellStock(): Fund ".$seller." doesn't own ".$selling."<br>";
		}
	}
	catch (PDOException $e)
	{
		echo "Error fund selling stock<br>";
	}
	
}

function sellBuy($name, $selling, $buying, $date, $dbh)
{
	//Check if the seller is an individual or a fund
	if (isIndividual($name, $dbh))
	{
		//First sell the stock requested.
		$previousCash = getIndCash($name,$dbh);
		sell($name, $selling, $date, $dbh);
		$currCash = getIndCash($name,$dbh);
	
		//Then buy the new stock with all proceeds
		$amount = $currCash - $previousCash ;
		indBuy($name, $buying, $amount, $date ,$dbh);
		
	}
	elseif(isFund($name, $dbh))
	{
		//First ensure that fund is selling & buying a stock to continue.
		if (isStock($selling,$dbh) && isStock($buying,$dbh))
		{
		//Then sell the stock requested.
		$previousCash = getFundCash($name,$date,$dbh);
		sell($name, $selling, $date, $dbh);
		$currCash = getFundCash($name,$date,$dbh);

		//Then buy the new stock with all proceeds
		$amount = $currCash - $previousCash ;
		fundBuy($name, $buying, $amount, $date ,$dbh);
		}
		else
		{	
			echo "Transaction skipped: Fund trying to sellbuy non stock. Selling: ".$selling." Buying: ".$buying .".<br>";
		}
	}
	else 
	{
		echo "Error sellBuy(): ".$name. " trying to sell ".$selling." for ".$buying.".<br>";
	}

}
	
function indSell($seller, $selling, $date, $dbh)
{
	// See if the individual is selling a fund or a stock.
	// When selling, also calculate and add to ind_fund_return
	if (isFund($selling,$dbh))
	{		
		try{
			//Calculate the individuals return from the fund and add it to individual_cash.
			$individualsInvestment = getIndividualsInvestment($seller,$selling, $dbh);
			$investmentDate = getFundInvestmentDate($seller,$selling,$dbh);
			$fundsStartValue = getFundTotalCash($selling, $investmentDate,$dbh);
			$fundCurrentTotalCapital = getFundTotalCash($selling, $date, $dbh);
			$individualsReturn = ($individualsInvestment/$fundsStartValue)*($fundCurrentTotalCapital);		
	 
			// Now add the cash to the $seller and remove it from the fund_cash.
			$prevCashVal = getIndCash($seller, $dbh); 
			
			// Calculate total return and add to ind_fund_return table
			$totalReturn = $individualsReturn/$prevCashVal;
			$dateSold = strtotime($date);
			$dateBought = strtotime($investmentDate);
			$numDaysHeld = ceil(abs($dateSold - $dateBought) / 86400);
			if ($numDaysHeld > 0)
			{
				$annualizedReturn = pow($totalReturn, (1/($numDaysHeld/365)));
				insertIntoIndFundReturn($seller, $selling, $annualizedReturn, $dbh);
				addCashToIndividualSelling($seller, $individualsReturn, $dbh);
			}

			// Fund sells all shares held by individual
			// Get the stocks currently owned by the $fund
			$sqlind = "SELECT symbol, shares, percentage, purchase_date FROM fund_stock where name = :name";	
			$query_ind = $dbh->prepare($sqlind);
			$query_ind->bindValue(':name', $selling);
			$query_ind->execute();		
		
			while ($rs = $query_ind->fetch(PDO::FETCH_OBJ))
			{				
				// Get this individual_fund Start date to use for currStockPrice^
				$fundInvestmentDate = getFundInvestmentDate($seller,$selling,$dbh);
				$investmentDateStockPrice = getInitialStockPrice($rs->symbol, $fundInvestmentDate, $dbh);

				//Calculate the number of shares to sell and update fund_stock 
				$sharesToDivest = ($individualsInvestment*$rs->percentage)/$investmentDateStockPrice;
			
				//Remove these shares from the fund_stock table
				removeSharesFromFund($selling, $rs->symbol, $sharesToDivest, $dbh);
			}


			// Now just remove the percentage of cash from fund_cash, and remove initial investment
			removeCashFromFund($selling, $individualsReturn,$individualsInvestment, $dbh);
			// remove entry from individual_fund table
			//removeIndividualFund($seller, $selling, $dbh);
			//Remove the stock from the individuals_fund table
			$sql = "DELETE FROM individual_fund WHERE fund = :symbol AND person = :person";
			$query = $dbh->prepare($sql);
			$query->bindValue(':symbol', $selling);
			$query->bindValue(':person', $seller);
			$query->execute();
		}
		catch (PDOException $e)
		{
			echo "Error individual selling fund.<br>";
		}
	}
	elseif(isStock($selling,$dbh))
	//Individual, sell the shares and update individuals cash. 
	{
		try{
			$sqlind = "SELECT * FROM individual_fund where person=:seller AND fund=:fund";	
			$query_ind = $dbh->prepare($sqlind);
			$query_ind->bindValue(':seller', $seller);
			$query_ind->bindValue(':fund', $selling);
			$query_ind->execute();
			$ind_result = $query_ind->fetch(PDO::FETCH_ASSOC);

			// get the close value of the stock we are selling on the nearest date.
			$value = (float) getStockPrice($selling, $date, $dbh) * (float) $ind_result['shares'];
			$currCash = getIndCash($seller ,$dbh);
			$newCashValue = (float) $value + (float) $currCash;

			// Calculate total return and add to ind_fund_return
			$dateSold = strtotime($date);
			$dateBought = strtotime(getFundInvestmentDate($seller,$selling,$dbh));
			$numDaysHeld = ceil(abs($dateSold - $dateBought) / 86400);
			if ( $numDaysHeld > 0)
			{
				$annualizedReturn = pow($value, (1/($numDaysHeld/365)));
				insertIntoIndFundReturn($seller, $selling, $annualizedReturn, $dbh);
				//Add cash to the individual_cash
				addCashToIndividualSelling($seller, $value, $dbh);
			}

			//Remove the stock from the individuals_fund table
			$sql = "DELETE FROM individual_fund WHERE fund = :symbol AND person = :person";
			$query = $dbh->prepare($sql);
			$query->bindValue(':symbol', $selling);
			$query->bindValue(':person', $seller);
			$query->execute();
		}
		catch (PDOException $e)
		{
			echo "Error Individual selling Stock<br>";
		}
	}
	else
	{	
		echo "Error indSell(): Not selling a stock or a fund.<br>";
	}	
}
	

?>
