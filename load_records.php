<?php
include '/var/www/dbc.php';
include '/var/www/isHelpers.php';
include '/var/www/buyFunctions.php';
include '/var/www/sellFunctions.php';

$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
set_time_limit(0);
ignore_user_abort(1);
	
// Buttons for clearing table returning and showing queries."
echo "<div align=\"center\">";
echo "<h1>Records Loaded!</h1>";
echo "<form action=\"viewQueries.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Display Query Results\">";
echo "</form>";	
echo "<form action=\"clearTables.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"clearTables\" value=\"Clear Database\">";
echo "</form>";	
echo "<form action=\"load_records.html\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"load_records\" value=\"Load More records\">";
echo "</form>";	
echo "</div>";
echo "<h3>Record file errors:</h3>";

//Ensure there are no errors uploading the file.	
if ($_FILES['file']['error'] > 0)
  {
  	echo 'Error: ' . $_FILES['file']['error'] . '<br>';
  }
else 
  {
	$filename = $_FILES['file']['tmp_name'];
		
	$fh = fopen("$filename", "r");
	$linenum = 1;
	
//Read the contents of the records file dealing with errors appropriately.
	while (($data = fgetcsv($fh, ',')) !== False)
	{
		// date range
		date_default_timezone_set('America/New_York');
		$beggining = new DateTime('01/01/2005');
		$end = new DateTime('12/30/2013');

		if($data[0] == "fund")
		{	
			$currDate = new DateTime($data[3]);
			$DATE =  $currDate->format('Y-m-d');
			if ($currDate >= $beggining && $currDate <= $end)
			{
				addCashToFund($data[1],$data[2],$DATE,1, $dbh);
			}
			else 
			{
				echo "Transaction skipped: Date out of range ".$DATE." for fund: ".$data[1]." ".$data[2]." ".$DATE."<br>" ;
			}			
		}
		elseif($data[0] == "individual")
		{			
			$currDate = new DateTime($data[3]);
			$DATE =  $currDate->format('Y-m-d');
			if ($currDate >= $beggining && $currDate <= $end)
			{
				addCashToIndividual($data[1],$data[2],$DATE, $dbh);
			}
			else 
			{

				echo "Transaction skipped: Date out of range ".$DATE." for individual: ".$data[1]." ".$data[2]." ".$DATE."<br>" ;
			}
		}
		elseif($data[0] == "sell")
		{
			$currDate = new DateTime($data[3]);
			$DATE =  $currDate->format('Y-m-d');
			if ($currDate >= $beggining && $currDate <= $end)
			{
				sell($data[1],$data[2],$DATE, $dbh);
			}
			else 
			{
				echo "Transaction skipped: Date out of range ".$DATE." for sell: ".$data[1]." ".$data[2]." ".$DATE."<br>" ;
			}
			
		}
		elseif($data[0] == "buy")
		{
	 		//Check to see if the buyer is a fund or Individual
			if(isFund($data[1], $dbh)){
				$currDate = new DateTime($data[4]);
				$DATE =  $currDate->format('Y-m-d');
				if ($currDate >= $beggining && $currDate <= $end)
				{
					fundBuy($data[1], $data[2], $data[3], $DATE, $dbh);
				}
				else 
				{
					echo "Transaction skipped: Date out of range ".$DATE." for fundBuy: ".$data[1]." ".$data[2]." ".$data[3]." ".$DATE."<br>" ;
				}				
				
			}
			elseif(isIndividual($data[1], $dbh)){
				$currDate = new DateTime($data[4]);

				$DATE =  $currDate->format('Y-m-d');
				if ($currDate >= $beggining && $currDate <= $end)
				{
					indBuy($data[1], $data[2], $data[3], $DATE, $dbh);
				}
				else 
				{
					echo "Transaction skipped: Date out of range ".$DATE." for IndBuy: ".$data[1]." ".$data[2]." ".$data[3]." ".$DATE."<br>" ;
				}				
				
			}
			else
			{	
			 	echo "Error Loading: ".$data[1]." trying to buy ".$data[2].".<br>";					
			}
		}
		elseif($data[0] == "sellbuy")
		{
			$currDate = new DateTime($data[4]);
			$DATE =  $currDate->format('Y-m-d');
			
			if ($currDate >= $beggining && $currDate <= $end)
			{
				sellBuy($data[1],$data[2],$data[3],$DATE, $dbh);
			}
			else 
			{
				echo "Transaction skipped: Date out of range ".$DATE." for Sellbuy: ".$data[1]." ".$data[2]." ".$data[3]." ".$DATE."<br>" ;
			}
			
		}
		//Else the input is invalid.
		else
		{
			echo "There was an error at line: ".$linenum." of the uploaded record file  <br>";
		}

		$linenum++;
	}
	
	fclose($fh);
  }

?>


