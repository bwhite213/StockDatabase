<?php
// Buy functions

include 'buyHelpers.php';

//Individual with $name wants to buy fund/stock 
function indBuy($name, $buy, $amount, $date ,$dbh)
{
	// Ensure the individual has enough money and remove it from ind_cash
	if (indSufficientFunds($name, $amount, $dbh)){
	
		//See if the individual is buying a stock or a fund.
		if (isStock($buy , $dbh)){
			try{
				// Get shares from current share price closest to buy date
				$stockPrice = getStockPrice($buy, $date, $dbh);

				$shares = (float) $amount/$stockPrice;
	
				$sql = "INSERT INTO individual_fund(person, fund, shares, purchase_date) VALUES(:name, :buy, :shares, :purchase_date)";	
				$query = $dbh->prepare($sql);
				$query->bindValue(':name', $name);
				$query->bindValue(':buy', $buy);
				$query->bindValue(':shares', $shares);
				$query->bindValue(':purchase_date', $date);
				$query->execute();
			}
			catch (PDOException $e)
			{
				echo "Error Individual buying stock<br>";
			}			
		}
		elseif(isFund($buy , $dbh))
		{
			try{
				individualInvestInFund($name, $buy, $amount, $date, $dbh);
			}
			catch (PDOException $e)
			{
				echo "Error Individual investing in a fund<br>";
			}
		}
		else
		{
			echo "Error indBuy: ". $name ." buying ".$buy." is not a fund/stock. <br>" ;
		}
	}
	else
	{
		echo "Error indBuy: The individual ".$name." has insufficient funds. <br>";
	}
}

//Called when fund: $name wants to buy stock: $buy
function fundBuy($name, $buy, $amount, $date ,$dbh)
{
	// Ensure that the fund :name has sufficient money in fund_cash
	$currentFundsCash = getFundCash($name,$date, $dbh);

	if ($currentFundsCash >= $amount)
	{
		//Funds can only invest in stocks
		if (isStock($buy, $dbh))
		{
			// Get the fund_cash percentage
			$currentCashPercentage = getFundCashPercentage($name,$dbh);
			$currentTotalAssets = getFundTotalCash($name, $date, $dbh);			
	
			// Calculate the percentage of total assets to investing into the stock
			$percentTotalAssets = (float) $amount/$currentTotalAssets;
			$newFundCP = $currentCashPercentage - $percentTotalAssets;

			//Update the fund_cash percentage and remove the funds $amount
			subtractFundCash($name, $newFundCP, $amount, $dbh);
			
			//Invest into the stock add to fund_stock 
			fundInvestHelper($name, $buy, $amount, $percentTotalAssets, $date, $dbh);
		}
		else
		{
			echo "Error fundBuy(): Fund: ".$name." can not buy: ".$buy." not a stock. <br>";
		}
	}
	else
	{
		echo "Error fundBuy(): ".$name." doesn't have sufficient funds. Current funds: ".$currentFundsCash." <br>";
	}
}

?>
