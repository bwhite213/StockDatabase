<?php
    try {
	$dsn = "mysql:host=localhost;port=3306;dbname=stock_history";
	$opt = array(
		PDO::ATTR_ERRMODE	=> PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_PERSISTENT => false
		);
		
	$dbh = new PDO($dsn, 'stocks', 'stockpass', $opt);
    }
    catch (PDOException $e) {
        die('unable to connect to database ' . $e->getMessage());
    }   	 

 
?>
