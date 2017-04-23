<?php
include '/var/www/dbc.php';

//First load the lines into the database then display the resulting queries.

if(preg_match("/^[  a-zA-Z]+/", $_REQUEST['records'])){

//TODO change this to read the lines from the Text area...
	while (($data = fgetcsv($fh, ',')) !== False)
	{
		if($data[0] == "fund")
		{	
			// TODO CHECK DATES DIFFERENTLY THROUGH BUYS AND SELLS NOT HERE.
			addCashToFund($data[1],$data[2],$data[3],1, $dbh);
		}
		elseif($data[0] == "individual")
		{
			addCashToIndividual($data[1],$data[2],$data[3], $dbh);
		}
		elseif($data[0] == "sell")
		{
			sell($data[1],$data[2],$data[3], $dbh);
		}
		elseif($data[0] == "buy")
		{
	 		//Check to see if the buyer is a fund or Individual
			if(isFund($data[1], $dbh)){
				fundBuy($data[1], $data[2], $data[3], $data[4], $dbh);
			}
			elseif(isIndividual($data[1], $dbh)){
				indBuy($data[1], $data[2], $data[3], $data[4], $dbh);
			}
			else{	
			 	echo "Error while: ".$data[1]." trying to buy ".$data[2].".<br>";					
			}
		}
		elseif($data[0] == "sellbuy")
		{
			sellBuy($data[1],$data[2],$data[3],$data[4], $dbh);
			//TODO Implement this!
		}
		//Else the input is invalid.
		else
		{	// HANDLE THIS ERROR DIFFERENTLY, POPUP WINDOW? *******8/////
			echo "There was an error at line: ".$linenum." of the uploaded record file  <br>";
		}

		$linenum++;
	}
	echo "Records loaded succesfully <br>";
	fclose($fh);

}
  ?>
