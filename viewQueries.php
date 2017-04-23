<?php
include '/var/www/dbc.php';
include '/var/www/vqHelpers.php';

echo "<form action=\"load_records.html\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"load_records\" value=\"Load More records\">";
echo "</form>";
echo "<form action=\"index.html\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Return Home\">";
echo "</form>";

///*******Setup tables********///
// Setup individual net worth table 
// sells funds still held by the individual at "2013-12-13" to calculate return and Net worth 
//(buys the stock back after selling to calculate mystery query)
setupIndividualNW($dbh);
// setup portfolios net worth table
setupPortfolioNW($dbh);


//****Query # 1 ******//
//rank all porfolios by total return.
//Display the table info.
showPortfolioReturn($dbh);
//Button to export the data to csv
echo "<form action=\"exports/exportPortfolioReturn.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 2 ******//
//rank all individuals by total return.
//Display the table info.
showIndividualReturn($dbh);
//Button to export the data to csv
echo "<form action=\"exports/exportIndividualReturn.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 3 ******//
// Rank top 25 stocks in annualized rate of return
//Display the table info.
showStockAROR($dbh);
//Button to export the data to csv
echo "<form action=\"exports/exportStockAROR.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 4 ******//
//rank all porfolios by final net worth.
// Net worth table already created.
//Display the table info.
showPortfolioNW($dbh);
//Button to export the data to csv
echo "<form action=\"exports/exportPortfolioNW.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 5 ******//
//rank all individuals by final net worth.
//Display the table info.
showIndividualNW($dbh);
//Button to export the data to csv
echo "<form action=\"exports/exportIndividualNW.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 6 ******//
//rank all individuals by stocks/funds by return
//Display the table info.
showIndividualFundReturn($dbh);
//Button to export the data to csv
echo "<form action=\"exports/exportIndividualFundReturn.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 7 ******//
//top 5 lowest risk stocks
//Display the table info.
showStockRisk($dbh);
echo "<form action=\"exports/exportStockRisk.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Query # 8 ******//
//Increasing stocks per year
//Display the table info.
showIncreasingStocks($dbh);
echo "<form action=\"exports/exportIncreasingStocks.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

//****Mystery Query ******//
//Increasing stocks per year
//Display the table info.
showMysteryQuery($dbh);
echo "<form action=\"exports/exportIncreasingStocks.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" name=\"showQueries\" value=\"Export Results\">";
echo "</form>";

clear_table("fund_NWRank", $dbh);
clear_table("ind_nw", $dbh);

?>
