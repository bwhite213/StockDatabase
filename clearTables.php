<?php

include '/var/www/dbc.php';

	clear_table("fund_cash", $dbh);
	clear_table("fund_stock", $dbh);
	clear_table("individual_cash", $dbh); 
	clear_table("individual_fund", $dbh);
	clear_table("fund_NWRank", $dbh);
	clear_table("ind_nw", $dbh);
	clear_table("ind_fund_return", $dbh);

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

echo "Records succesfully cleared from database.";

echo "<div align=\"center\">";
echo "<a href=\"load_records.html\">Click to Load New Records</a>";
echo "</div>";

?>
