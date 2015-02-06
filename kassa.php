<?php 
	include("header.php"); 
	require_once 'Validate.php';
	$validator = new Validate();
?>
<div id="main">
	<h2>Kassa</h2>
	<?php
		if(count($_SESSION["ostoskori"])>0){ 
			$nimi = "";
			$osoite = ""; 
			$pnro = "";
			$ptoimpaikka = "";
			$email = "";
			if(isset($_SESSION["userId"])){ // Käyttäjän ilmoittamat osoitteet
				$sql = $dbh->prepare('SELECT Asiakas.nimi, Asiakas.osoite, Asiakas.postinumero, Asiakas.postitoimipaikka, Asiakas.email FROM Asiakas WHERE Asiakas.id = :id');
				$sql->bindParam(":id", $_SESSION["userId"]);
				$ok = $sql->execute();
				if(!$ok) {print_r($sql->errorInfo());}
				while($row = $sql->fetch(PDO::FETCH_ASSOC) ) { // Tiedot jotka näytetään toimitusosoitteessa, jos kirjautunut sisään
				$nimi = $row["nimi"];
				$osoite = $row["osoite"]; 
				$pnro = $row["postinumero"];
				$ptoimpaikka = $row["postitoimipaikka"];
				$email = $row["email"];
				}
			} else {
			if (isset($_POST["sposti"]) && isset($_POST["password"]) && $sql->rowCount() == 0){ // Tarkistetaan, jos käyttäjää ei löytynyt.
				echo "<p>Väärä sähköposti tai salasana.</p>";
			} else {
				echo "<p>Kirjaudu sisään:</p>"; // Sisäänkirjautumisformi
			}
			echo(" 
			<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">
			<table><tr><td>Sähköposti: </td><td><input type=\"text\" name=\"sposti\" placeholder=\"Sähköposti\"/></td></tr>
			<tr><td>Salasana: </td><td><input type=\"password\" name=\"password\" placeholder=\"Salasana\"/></td></tr>
			<tr><td></td><td><input type=\"submit\" value=\"Kirjaudu\"/></td></tr></table>
			<p><a href=\"register.php\">Rekisteröidy</a></p>
			</form><hr/>");
			}
			$validi = $validator->email($_POST["email"], array('check_domain' => 'true')); // Tarkistetaan onko validi sähköposti ja domain PEAR:n Validate avulla
			if(isset($_POST["tilaus"]) && $_POST["nimi"]>0 && $_POST["osoite"]>0 && $_POST["pnro"]>0 && $_POST["ptoimpaikka"] > 0 && $_POST["email"] > 0 && $validi){ // Tilauksen käsitteleminen
				$nimi = test_input($_POST["nimi"]);
				$osoite = test_input($_POST["osoite"]); 
				$pnro = test_input($_POST["pnro"]);
				$ptoimpaikka = test_input($_POST["ptoimpaikka"]);
				$sql = $dbh->prepare("INSERT INTO Tilaus (nimi, osoite, postinumero, postitoimipaikka, tila_id) VALUES (:nimi, :osoite, :postinro, :toimipaikka, \"1\")" );
				$sql->bindParam(":nimi", $nimi);
				$sql->bindParam(":osoite", $osoite);
				$sql->bindParam(":postinro", $pnro);
				$sql->bindParam(":toimipaikka", $ptoimpaikka);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$sql = $dbh->prepare('SELECT LAST_INSERT_ID()'); // Palauttaa viimeksi laitetun primary keyn
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$tilausID = $sql->fetch(PDO::FETCH_NUM);
				foreach($_SESSION["ostoskori"] as $tuote => $maara){
					$sql = $dbh->prepare("INSERT INTO Tuotetilaus (tilaus_id, teos_isbn, kpl) VALUES (:tilausID, :teosID, :maara)" );
					$sql->bindParam(":tilausID", $tilausID[0]);
					$sql->bindParam(":teosID", $tuote);
					$sql->bindParam(":maara", $maara);
					$ok = $sql->execute();
					if(!$ok) {print_r( $sql->errorInfo() );}
					$sql = $dbh->prepare("UPDATE Teos SET varastosaldo = varastosaldo - :maara WHERE isbn = :isbn" );
					$sql->bindParam(":maara", $maara);
					$sql->bindParam(":isbn", $tuote);
					$ok = $sql->execute();
					if(!$ok) {print_r( $sql->errorInfo() );}
				}
				unset($_SESSION["ostoskori"]);
				echo "<p>Kiitos tilauksestasi.</p>";
			} else { // Toimitusosoitteen kysyminen ja yhteenveto tilauksesta
				echo "<h3>Toimitusosoite</h3>";
				echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
				echo "<table><tr><td>Nimi: </td><td><input type=\"text\" name=\"nimi\" value=\"" . $nimi . "\"/></td></tr>";
				echo "<tr><td>Osoite: </td><td><input type=\"text\" name=\"osoite\" value=\"" . $osoite . "\"/></td></tr>";
				echo "<tr><td>Postinumero: </td><td><input type=\"text\" name=\"pnro\" value=\"" . $pnro . "\"/></td></tr>";
				echo "<tr><td>Postitoimipaikka: </td><td><input type=\"text\" name=\"ptoimpaikka\" value=\"" . $ptoimpaikka . "\"/></td></tr>";
				echo "<tr><td>Sähköposti: </td><td><input type=\"text\" name=\"kassaEmail\" value=\"" . $email . "\"/></td></tr>";
				echo "</table><hr/><h3>Tilaustuotteet</h3><p><i>Varaston yli menevät tuotteet toimitetaan jälkitoimituksena.</i></p>";
				
				echo "<table class=\"kassa\"><tr class=\"kassa\"><th class=\"kassa\">Tuote</th><th class=\"kassa\">Määrä</th><th class=\"kassa\">Kappalehinta</th><th class=\"kassa\">Kokonaishinta</th></tr>";
				$tilausSumma = 0;
				foreach($_SESSION["ostoskori"] as $tuote => $maara){ // Tilauksen tiedot
					$sql = $dbh->prepare('SELECT Teos.isbn, Teos.teos, Kirjailija.kirjailija, Teos.varastosaldo, Teos.hinta FROM Teos,Kirjailija WHERE Teos.kirjailija_id=Kirjailija.id AND Teos.isbn = :isbn');
					$sql->bindParam(':isbn', $tuote);
					$ok = $sql->execute();
					if(!$ok) {print_r($sql->errorInfo());}
					while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
						$kokonaishinta = $row["hinta"]*$maara;
						$tilausSumma += $kokonaishinta;
						echo "<tr><td class=\"kassa\"><b>" . $row["teos"] . "</b><br/><i>" . $row["kirjailija"] . "</i></td><td class=\"kassa\">" . $maara . " kpl</td><td class=\"kassa\">" . $row["hinta"] . " €</td><td class=\"kassa\">" . $kokonaishinta . " €</td></tr>";
					}
				}
				echo "</table><p class=\"right\"><b>Tilauksen kokonaishinta: " . $tilausSumma . " €</b></p>";
				echo "<input type=\"submit\" name=\"tilaus\" value=\"Vahvista tilaus\"/></form>";
			}
		} else { 
			echo "<p>Ei tuotteita.</p>"; // Näytetään jos ostoskorissa ei ole tuotteita
		}
	?>
	
</div>
<?php include("footer.php"); ?>