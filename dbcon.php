<?php // avataan tietokantayhteys
	try {
		$dbh = new PDO('mysql:host=dbhost.fi;dbname=bookstore',"user","pass");
	}
	catch (PDOException $e) { die ("Virhe: ".$e->getMessage() ) ; }
?>