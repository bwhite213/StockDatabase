<?php
	// This file displays all stock data in the database for the selected company.
try {
 	include '/var/www/dbc.php';

	//if to/from left empty display full range else display within set range from form.
	if (count($_POST['from']) == 0 || count($_POST['to']) == 0 || count($_POST['to']) == "YYYY-MM-DD" || $_POST['from'] == "YYYY-MM-DD")
	{
		$stmt = $dbh->prepare("SELECT * FROM stock_data WHERE symbol=:sym");
		$stmt->bindValue(':sym', $_POST['symbol']);	
		$stmt->execute();
		echo "<h2>There are ". $stmt->rowCount()." entries for ". $_POST['symbol']. "</h2>";
	}else 
	{
		$stmt = $dbh->prepare("SELECT * FROM stock_data WHERE symbol=:sym AND date BETWEEN :from AND :to");
		$stmt->bindValue(':sym', $_POST['symbol']);	
		$stmt->bindValue(':from', $_POST['from']);
		$stmt->bindValue(':to', $_POST['to']);
		$stmt->execute();
		echo "<h2>There are ".$stmt->rowCount()." entries for ".$_POST['symbol']. " between ".$_POST['from']." and ".$_POST['to']."</h2>";
	}
	
	echo "<a href=\"/show_stock_data.html\">Return to the form</a><hr>";

	echo "<table border=1>";
		echo "<tr>";            
			echo "<th> Symbol </th>";
			echo "<th> Date </th>";
			echo "<th> Open </th>";
			echo "<th> High </th>";
			echo "<th> Low </th>";
			echo "<th> Close </th>";
			echo "<th> Volume </th>";
			echo "<th> Adjusted Close</th>";
		echo "</tr>"; 

		while($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
			echo "<tr>";     
				echo "<td>".$rs->symbol."</td>";       
				echo "<td>".$rs->date."</td>";
				echo "<td>".$rs->open."</td>";
				echo "<td>".$rs->high."</td>";
				echo "<td>".$rs->low."</td>";
				echo "<td>".$rs->close."</td>";
				echo "<td>".$rs->volume."</td>";
				echo "<td>".$rs->adj_close."</td>";
			echo "</tr>";
		}
   	echo "</table>";
   } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
?>


