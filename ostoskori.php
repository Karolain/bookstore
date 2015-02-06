<?php include("header.php"); 
if(isset($_GET["tuote"])){ // Lisätään tuote ostoskorisessioon
	$_SESSION["ostoskori"][test_input($_GET["tuote"])] = 1;
}
if(isset($_GET["muuta"]) && isset($_GET["uusiMaara"])){ // Muutetaan tuotteen määrää
	if($_GET["uusiMaara"]<=0){
		unset($_SESSION["ostoskori"][$_GET["muuta"]]);
	} else {
		$_SESSION["ostoskori"][test_input($_GET["muuta"])] = test_input($_GET["uusiMaara"]);
	}
}
if(isset($_GET["poista"])){ // Poistetaan tuote sessiosta
	unset($_SESSION["ostoskori"][$_GET["poista"]]);
}
?>
<div id="main">
	<h2>Ostoskori</h2>
	<?php // Tulostetaan ostoskoritaulu jos on tuotteita
		if(count($_SESSION["ostoskori"])>0){
			echo "<table class=\"ostoskori\"><tr class=\"ostoskori\"><th class=\"ostoskori\">Tuote</th><th class=\"ostoskori\">Varasto</th><th class=\"ostoskori\">Määrä</th><th class=\"ostoskori\">Kappalehinta</th><th class=\"ostoskori\">Kokonaishinta</th></tr>";
			$tilausSumma = 0;
			foreach($_SESSION["ostoskori"] as $tuote => $maara){
				$sql = $dbh->prepare('SELECT Teos.isbn, Teos.teos, Kirjailija.kirjailija, Teos.varastosaldo, Teos.hinta FROM Teos,Kirjailija WHERE Teos.kirjailija_id=Kirjailija.id AND Teos.isbn = :isbn');
				$sql->bindParam(':isbn', $tuote);
				$ok = $sql->execute();
				if(!$ok) {print_r($sql->errorInfo());}
				while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
					$kokonaishinta = $row["hinta"]*$maara;
					$tilausSumma += $kokonaishinta;
					echo "<tr><td class=\"ostoskori\"><b>" . $row["teos"] . "</b><br/><i>" . $row["kirjailija"] . "</i><br /><a class=\"ostoskori\" href=\"./ostoskori.php?poista=" . $row["isbn"] . "\">Poista tuote</a></td><td class=\"ostoskori\"> " . $row["varastosaldo"] . " kpl</td><td class=\"ostoskori\"><a class=\"ostoskori\" href=\"./ostoskori.php?muuta=" . $row["isbn"] . "&amp;uusiMaara=" . ($maara-1) . "\"><b>- </b></a>" . $maara . " kpl<a class=\"ostoskori\" href=\"./ostoskori.php?muuta=" . $row["isbn"] . "&amp;uusiMaara=" . ($maara+1) . "\"><b> +</b></a></td><td class=\"ostoskori\">" . $row["hinta"] . " €</td><td class=\"ostoskori\">" . $kokonaishinta . " €</td></tr>";
				}
			}
			echo "</table><p class=\"right\"><b>Tilauksen kokonaishinta: " . $tilausSumma . " €</b></p>";
			echo "<p class=\"right\"><a href=\"./kassa.php\"><img src=\"kassalle.png\" alt=\"Siirry kassalle\"/></a></p>";
		} else { 
			echo "<p>Ostoskorissasi ei ole tuotteita.</p>";
		}
	?>
</div>
<?php include("footer.php"); ?>