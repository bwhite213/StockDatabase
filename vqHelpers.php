<?php

include "/var/www/isHelpers.php";
include "/var/www/sellFunctions.php";
include "/var/www/buyFunctions.php";

// used to clear the tables after setting up and viewing them.
function clear_table($table, $dbh)
{
	try {
	    $dbh->beginTransaction();

	    $stmt = $dbh->prepare("TRUNCATE TABLE $table");

	    $stmt->execute();

	    $dbh->commit();
	} catch(PDOException $ex) {
	    //Something went wrong rollback!
	    $dbh->rollBack();
	    echo $ex->getMessage();
	}    
}

//Show query 1
function showPortfolioReturn($dbh)
{
	// Get all PNW values
	$query = $dbh->prepare("SELECT name, appreciation from fund_NWRank ORDER BY appreciation DESC LIMIT 20");
	$query->execute();
	$count = 1;
	echo "<h2>#1: Rank top 20 portfolios by final Return</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Fund Name </th>";
			echo "<th> Return </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->name."</td>"; 
			echo "<td>".$rs->appreciation."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

//Show query 2
function showIndividualReturn($dbh)
{
	// Get all Individuals values
	$query = $dbh->prepare("SELECT name, ret from individual_cash ORDER BY ret DESC LIMIT 20");
	$query->execute();
	$count = 1;
	echo "<h2>#2: Rank top 20 Individuals by final Return</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Individual Name </th>";
			echo "<th> Return </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->name."</td>"; 
			echo "<td>".$rs->ret."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

//Show query 3
function showStockAROR($dbh)
{
	// Get all PNW values
	$query = $dbh->prepare("SELECT * from stock_aror ORDER BY AROR DESC LIMIT 25");
	$query->execute();
	$count = 1;
	
	echo "<h2>#3: Rank top 25 stocks by Annualized rate of return</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Stock Symbol </th>";
			echo "<th> Annualized R.O.R </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->symbol."</td>"; 
			echo "<td>".$rs->AROR."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

// Query 4 Display the portfolios Net worth rank.
function showPortfolioNW($dbh)
{
	// Get all PNW values
	$query = $dbh->prepare("SELECT name, nw from fund_NWRank ORDER BY nw DESC LIMIT 20");
	$query->execute();
	$count = 1;
	echo "<h2>#4: Rank top 20 portfolios by final Net Worth</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Fund Name </th>";
			echo "<th> Net Worth </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->name."</td>"; 
			echo "<td>".$rs->nw."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

// Show query 5
function showIndividualNW($dbh)
{
	// Get all PNW values
	$query = $dbh->prepare("SELECT * from ind_nw ORDER BY nw DESC LIMIT 20");
	$query->execute();
	$count = 1;
	
	echo "<h2>#5: Rank top 20 individuals by final Net Worth</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Fund Name </th>";
			echo "<th> Net Worth </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->Name."</td>"; 
			echo "<td>".$rs->nw."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}


//Show query 6
function showIndividualFundReturn($dbh)
{
	// Get all Individuals values
	$query = $dbh->prepare("select * from ind_fund_return group by name, fund order by ret DESC LIMIT 20");
	$query->execute();	
	$count = 1;
	echo "<h2>#6: Rank top 20 Individuals by fund/stock annualized rate of return</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Individual Name </th>";
			echo "<th> Fund Name </th>";
			echo "<th> Rate of Return </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->name."</td>";			
			echo "<td>".$rs->fund."</td>"; 
			echo "<td>".$rs->ret."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

//Show query 7
function showStockRisk($dbh)
{
	// Get all Individuals values
	$query = $dbh->prepare(" select a.symbol as 'Symbol',b.symbol as 'BSymbol', b.risk as 'Risk', AROR from stock_aror as a, stock_risk as b having Symbol = BSymbol ORDER BY Risk ASC LIMIT 5");
	$query->execute();
	$count = 1;
	echo "<h2>#7: Top 5 lowest risk stocks.</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Symbol </th>";
			echo "<th> Risk </th>";
			echo "<th> AROR </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->Symbol."</td>";			
			echo "<td>".$rs->Risk."</td>"; 			
			echo "<td>".$rs->AROR."</td>";     
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

//Show query 8
function showIncreasingStocks($dbh)
{
	// Get all Individuals values
	$query = $dbh->prepare(" select * from stock_increasing");
	$query->execute();
	$count = 1;
	echo "<h2>#8: Stocks Strictly increasing every year.</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Rank </th>";
			echo "<th> Symbol </th>";
		echo "</tr>"; 

	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->symbol."</td>";
		echo "</tr>";
		$count = $count + 1;
	}
	echo "</table>";
}

//Show query Mystery Query
function showMysteryQuery($dbh)
{
	$query = $dbh->prepare("SELECT person, fund, nw FROM individual_fund, ind_nw WHERE name=person GROUP BY fund ORDER BY nw DESC");
	$query->execute();
	$count = 1;
	echo "<h2>Mystery Query: Display names of individuals who are majority participants in 
funds/stocks along with the fund name and ranks of net worth</h2>";
	echo "<table border=1>";
		echo "<tr>";           
			echo "<th> Net Worth Rank </th>";
			echo "<th> Name </th>";
			echo "<th> Fund </th>";
			echo "<th> Individuals Net Worth </th>";
		echo "</tr>"; 
	$prevName = "";
	
	while($rs = $query->fetch(PDO::FETCH_OBJ)) {
		// Since ranked by NW is the last name is the same as the previous dont increment counter.		
		if (strcmp($prevName, $rs->person) == 0 || strcmp("", $prevName) == 0)
		{}
		else{$count = $count + 1; }

		echo "<tr>";     
			echo "<td>".$count."</td>";			
			echo "<td>".$rs->person."</td>";			
			echo "<td>".$rs->fund."</td>";			
			echo "<td>".$rs->nw."</td>";
		echo "</tr>";
		$prevName = $rs->person;
	}
	echo "</table>";
}





/////////*****SETUP FUNCTIONS******//////////
//Setup Portfolio net worth data.
function setupPortfolioNW($dbh)
{
	// get the list of all fund names
	$query = $dbh->prepare("SELECT name FROM fund_cash");
	$query->execute();

	// for each fund, get the total net worth and add it to fund_NWRank
	while ($rs = $query->fetch(PDO::FETCH_OBJ))
	{		
		try{		
		$name = $rs->name;
		$appreciation = getAppreciationOfFund($name, "2013-12-30", $dbh);
		$netWorth = getFundTotalCash($name, "2013-12-30", $dbh);
		//insert the info into the table
		$stmt = $dbh->prepare("INSERT INTO fund_NWRank VALUES (:name, :value, :appreciation)");
		$stmt->bindValue(':name', $name);	
		$stmt->bindValue(':value', $netWorth);	
		$stmt->bindValue(':appreciation', $appreciation);
		$stmt->execute();	
		}
		catch(PDOException $e)
		{	
			
		}	
	}
}

// Calculates individuals net worth and adds it to the ind_nw table 
// Also adds the return to individuals who have not yet sold their fund/stock
function setupIndividualNW($dbh)
{
	try{
		//Sell all funds still owned by individuals
		// also calculate annualized return of investment
		$query = $dbh->prepare("SELECT person, fund, shares FROM individual_fund");
		$query->execute();
		while ($rs = $query->fetch(PDO::FETCH_OBJ))
		{
			// Get individuals cash before selling
			$prevCash = getIndCash($rs->person, $dbh);	
			$investmentDate = strtotime(getFundInvestmentDate($rs->person, $rs->fund, $dbh));
			sell($rs->person,$rs->fund, "2013-12-30", $dbh);	
			//Get individuals cash after selling and calculate return from selling
			$currCash = getIndCash($rs->person, $dbh);
			$totalReturn = ($currCash-$prevCash)/$prevCash;
			$dateSold = strtotime("2013-12-30");
			$numDaysHeld = ceil(abs($dateSold - $investmentDate) / 86400);
			if ($numDaysHeld > 0)
			{
				$annualizedReturn = pow($totalReturn, (1/($numDaysHeld/365)));
				insertIntoIndFundReturn($rs->person, $rs->fund,$annualizedReturn, $dbh);
			}	

			//BUY STOCK BACK FOR CALCULATING MYSTERY QUERY
			indBuy($rs->person, $rs->fund, $currCash-$prevCash, "2013-12-30", $dbh);
		}

		// get the list of all ind names
		$query = $dbh->prepare("SELECT name, cash, ret FROM individual_cash");
		$query->execute();

		// for each Individual, get their cash and add it to ind_nw table
		while ($rs = $query->fetch(PDO::FETCH_OBJ))
		{		
			$name = $rs->name;
			//insert the info into the table
			$stmt = $dbh->prepare("INSERT INTO ind_nw VALUES (:name, :value)");
			$stmt->bindValue(':name', $name);	
			$stmt->bindValue(':value', $rs->cash + $rs->ret);
			$stmt->execute();		
		}
	}
	catch(PDOException $e)
	{	
		
	}
}

function addToNW($person, $value, $dbh)
{

	//insert the info into the table
	$stmt = $dbh->prepare("INSERT INTO ind_nw VALUES (:name, :value) ON DUPLICATE KEY UPDATE SET value = value + :value");
	$stmt->bindValue(':name', $person);	
	$stmt->bindValue(':value', $value);
	$stmt->execute();
}


?>
