<?php 
// if($_SERVER["HTTPS"] != "on")
// {
    // header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    // exit();
// }
session_start(); 
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>Book Store</title>
		<link rel="stylesheet" type="text/css" href="stylesheet.css" />
		<style>
			a:link,a:active,a:visited{
				color:#361100;
				font-weight:bold;
				text-decoration:none;
			}
			a:hover{
				color:#753100;
				font-weight:bold;
				text-decoration:none;
			}
			.loginDiv{
				width:400px;
				margin:auto;
				padding:10px;
				border:#361100 solid 2px;
				border-radius:15px;
				background:#d6b4a2;
				margin-top:10px;
				margin-bottom:10px;
			}
			input {
				width:150px;
			}
			.content {
				padding-left:50px;
				padding-right:50px;
				text-align:center;
			}
			.muutosTaulu {
				text-align:left;
				margin:auto;
				width:700px;
			}
		</style>
		<?php
			$ilmoitus = "";
			if(isset($_POST["logout"]) && $_POST["logout"]=="Kirjaudu ulos"){ // Tarkistetaan kirjauduttiinko ulos
				unset($_SESSION["hallinta"]);
			}
			include("dbcon.php"); 
			function test_input($data){ 						// Testataan annettu data ja poistetaan haitalliset merkinnät
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}
			if(isset($_POST["password"]) && isset($_POST["tunnus"])){
				$salasana = hash("sha256",$_POST["password"]); 	// Hashataan annettu salasana
				$tunnus = test_input($_POST["tunnus"]);			// Testataan annettu sähköposti injektioiden varalta
				$sql = $dbh->prepare('SELECT Hallinta.tunnus, Hallinta.arvo FROM Hallinta WHERE Hallinta.tunnus = :tunnus AND Hallinta.arvo = :salasana');
				$sql->bindParam(":tunnus", $tunnus);
				$sql->bindParam(":salasana", $salasana);
				$ok = $sql->execute();
				if(!$ok) {print_r($sql->errorInfo());}
				$hallinta = $sql->fetchAll();
				if ($sql->rowCount() > 0){ 						// Lisätään hallintasessio
					$_SESSION["hallinta"] = $hallinta[0][0];
				}
			}
			if(isset($_POST["uusiSalasana"]) && $_POST["vaihdaSalasana"]=="Vaihda salasana"){ // Salasanan vaihtaminen hallinnan tunnukseen
				$uusiSalasana = hash("sha256",$_POST["uusiSalasana"]);
				$sql = $dbh->prepare('UPDATE Hallinta SET arvo = :uusiSalasana WHERE Hallinta.tunnus = :tunnus');
				$sql->bindParam(":tunnus", $_SESSION["hallinta"]);
				$sql->bindParam(":uusiSalasana", $uusiSalasana);
				$ok = $sql->execute();
				if(!$ok) {print_r($sql->errorInfo());}
				$ilmoitus = "Salasanan vaihtaminen onnistui.";
			}
			if(isset($_POST["addNewWriter"]) && $_POST["addNewWriter"]=="Lisää uusi kirjailija"){ // Kirjailijan lisääminen
				// Uuden kirjailijan tietojen testaaminen
				$nimi = test_input($_POST["uusiKirjailija"]);
				// Tietojen lisääminen kantaan
				$sql = $dbh->prepare("INSERT INTO Kirjailija (kirjailija) VALUES (:nimi)" );
				$sql->bindParam(":nimi", $nimi);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$ilmoitus = "Kirjailijan lisääminen onnistui.";
			}
			if(isset($_POST["addNewPublisher"]) && $_POST["addNewPublisher"]=="Lisää uusi julkaisija"){ // Julkaisijan lisääminen
				// Uuden kirjailijan tietojen testaaminen
				$nimi = test_input($_POST["uusiJulkaisija"]);
				// Tietojen lisääminen kantaan
				$sql = $dbh->prepare("INSERT INTO Julkaisija (julkaisija) VALUES (:nimi)" );
				$sql->bindParam(":nimi", $nimi);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$ilmoitus = "Julkaisijan lisääminen onnistui.";
			}
			if(isset($_POST["addNewBook"]) && $_POST["addNewBook"]=="Lisää uusi kirja"){ // Kirjan lisääminen
				// Uuden kirjan tietojen testaaminen
				$nimi = test_input($_POST["uudenKirjanNimi"]);
				$uudenKirjanISBN = test_input($_POST["uudenKirjanISBN"]);
				$kirjanKirjailija = test_input($_POST["kirjanKirjailija"]);
				$kirjanJulkaisija = test_input($_POST["kirjanJulkaisija"]);
				$kirjanKansi = test_input($_POST["kirjanKansi"]);
				$varastosaldo = test_input($_POST["uudenKirjanVarastosaldo"]);
				$kirjanHinta = test_input($_POST["uudenKirjanHinta"]);
				$kuvaus = test_input($_POST["kirjanKuvaus"]);
				// Tietojen lisääminen kantaan
				$sql = $dbh->prepare("INSERT INTO Teos (isbn, teos, kirjailija_id, julkaisija_id, kansityyppi_id, kuvaus, varastosaldo, hinta) VALUES (:isbn, :nimi, :kirjanKirjailija, :julkaisija, :kansi, :kuvaus, :varastosaldo, :kirjanHinta)" );
				$sql->bindParam(":nimi", $nimi);
				$sql->bindParam(":isbn", $uudenKirjanISBN);
				$sql->bindParam(":kirjanKirjailija", $kirjanKirjailija);
				$sql->bindParam(":julkaisija", $kirjanJulkaisija);
				$sql->bindParam(":kansi", $kirjanKansi);
				$sql->bindParam(":kuvaus", $kuvaus);
				$sql->bindParam(":varastosaldo", $varastosaldo);
				$sql->bindParam(":kirjanHinta", $kirjanHinta);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$ilmoitus = "Kirjan lisääminen onnistui.";
			}
			if(isset($_POST["editSelectedBook"]) && $_POST["editSelectedBook"]=="Muokkaa kirjaa"){ // Kirjan muokkaaminen
				// Kirjan tietojen testaaminen
				$nimi = test_input($_POST["muokattuKirjanNimi"]);
				$kirjanISBN = test_input($_POST["muokattavanKirjanISBN"]);
				$kirjanKirjailija = test_input($_POST["muokattuKirjanKirjailija"]);
				$kirjanJulkaisija = test_input($_POST["muokattuKirjanJulkaisija"]);
				$kirjanKansi = test_input($_POST["muokattuKirjanKansi"]);
				$varastosaldo = test_input($_POST["muokattuKirjanVarastosaldo"]);
				$kirjanHinta = test_input($_POST["muokattuKirjanHinta"]);
				$kuvaus = test_input($_POST["muokattuKirjanKuvaus"]);
				// Tietojen lisääminen kantaan
				$sql = $dbh->prepare("UPDATE Teos SET teos = :nimi, kirjailija_id = :kirjanKirjailija, julkaisija_id = :julkaisija, kansityyppi_id = :kansi, kuvaus = :kuvaus, varastosaldo = :varastosaldo, hinta = :kirjanHinta WHERE Teos.isbn = :isbn" );
				$sql->bindParam(":nimi", $nimi);
				$sql->bindParam(":isbn", $kirjanISBN);
				$sql->bindParam(":kirjanKirjailija", $kirjanKirjailija);
				$sql->bindParam(":julkaisija", $kirjanJulkaisija);
				$sql->bindParam(":kansi", $kirjanKansi);
				$sql->bindParam(":kuvaus", $kuvaus);
				$sql->bindParam(":varastosaldo", $varastosaldo);
				$sql->bindParam(":kirjanHinta", $kirjanHinta);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$ilmoitus = "Kirjan muokkaaminen onnistui.";
			}
			if(isset($_GET["postita"])){ // Tilauksen postittaminen, tila odottaa -> toimitettu
				$id = test_input($_GET["postita"]);
				$sql = $dbh->prepare("UPDATE Tilaus SET Tilaus.tila_id = 2 WHERE Tilaus.id = :id");
				$sql->bindParam(":id", $id);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$ilmoitus = "Tilaus postitettu onnistuneesti.";
			}
			if(isset($_GET["poista"])){ // Tilauksen poistaminen
				$id = test_input($_GET["poista"]);
				$sql = $dbh->prepare("DELETE FROM Tilaus WHERE Tilaus.id = :id");
				$sql->bindParam(":id", $id);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				$ilmoitus = "Tilaus poistettu onnistuneesti.";
			}
		?>
	</head>
	<body>
		<div style="margin:auto;width:1200px;background:#fff;border:#361100 solid 2px;border-radius:15px;padding-bottom:20px;">
			<header></header>
			<div class="loginDiv">
			<?php
				echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
				if(isset($_SESSION["hallinta"])){ // Kirjaudu ulos
					echo("Olet kirjautunut sisään. <input type=\"submit\" name=\"logout\" value=\"Kirjaudu ulos\"/>");
				} else {
				if (isset($_POST["tunnus"]) && isset($_POST["password"]) && $sql->rowCount() == 0){ // Tarkistetaan, jos käyttäjää ei löytynyt.
					echo "Väärä tunnus tai salasana.";
				} else {
					echo "Kirjaudu sisään:"; // Sisäänkirjautumisformi
				}
				echo("<table><tr><td>Tunnus: </td><td><input type=\"text\" name=\"tunnus\" placeholder=\"Tunnus\"/></td></tr>
				<tr><td>Salasana: </td><td><input type=\"password\" name=\"password\" placeholder=\"Salasana\"/></td></tr>
				<tr><td></td><td><input type=\"submit\" value=\"Kirjaudu\"/></td></tr></table>
				</form>");
				}
			?>
			</div>
			<div class="content">
			<?php
				if(isset($_SESSION["hallinta"])){ // Hallinnan toiminnot omina nappeinaan
					echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
					echo "<input type=\"submit\" name=\"changePWD\" value=\"Vaihda salasana\" /><br/>";
					echo "<input type=\"submit\" name=\"addBook\" value=\"Lisää uusi kirja\" /><br/>";
					echo "<input type=\"submit\" name=\"addWriter\" value=\"Lisää uusi kirjailija\" /><br/>";
					echo "<input type=\"submit\" name=\"addPublisher\" value=\"Lisää uusi julkaisija\" /><br/>";
					echo "<input type=\"submit\" name=\"listOrders\" value=\"Listaa tilaukset\" /><br/>";
					echo "<input type=\"submit\" name=\"editBook\" value=\"Muokkaa kirjaa\" /><br/>";
					echo "</form><hr/>";
					echo $ilmoitus;
					$ilmoitus = "";
					if(isset($_POST["changePWD"])){ // Salasanan vaihtaminen
						echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
						echo "Anna uusi salasana: <input type=\"password\" name=\"uusiSalasana\" /><br/>";
						echo "<input type=\"submit\" name=\"vaihdaSalasana\" value=\"Vaihda salasana\" /><br/>";
						echo "</form><hr/>";
					}
					if(isset($_POST["addWriter"])){ // Kirjailijan lisääminen
						echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
						echo "Anna uuden kirjailijan nimi: <input type=\"text\" name=\"uusiKirjailija\" /><br/>";
						echo "<input type=\"submit\" name=\"addNewWriter\" value=\"Lisää uusi kirjailija\" /><br/>";
						echo "</form><hr/>";
					}
					if(isset($_POST["addPublisher"])){ // Julkaisijan lisääminen
						echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
						echo "Anna uuden julkaisijan nimi: <input type=\"text\" name=\"uusiJulkaisija\" /><br/>";
						echo "<input type=\"submit\" name=\"addNewPublisher\" value=\"Lisää uusi julkaisija\" /><br/>";
						echo "</form><hr/>";
					}
					if(isset($_POST["addBook"])){ // Kirjan lisääminen
						$sql = $dbh->prepare('SELECT DISTINCT Kirjailija.id, Kirjailija.kirjailija FROM Kirjailija ORDER BY Kirjailija.kirjailija');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$kirjailijat = $sql->fetchAll();
						
						$sql = $dbh->prepare('SELECT DISTINCT Kansityyppi.id, Kansityyppi.tyyppi FROM Kansityyppi ORDER BY Kansityyppi.tyyppi');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$kannet = $sql->fetchAll();
						
						$sql = $dbh->prepare('SELECT DISTINCT Julkaisija.id, Julkaisija.julkaisija FROM Julkaisija ORDER BY Julkaisija.julkaisija');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$julkaisijat = $sql->fetchAll();
						
						echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
						echo "<table class=\"muutosTaulu\"><tr><td>Anna kirjan nimi: </td><td><input type=\"text\" name=\"uudenKirjanNimi\" /></td></tr>"; 	// Kirjan nimi
						echo "<tr><td>Anna kirjan isbn (13 numeroa): </td><td><input type=\"text\" name=\"uudenKirjanISBN\" /></td></tr>"; 					// ISBN
						echo "<tr><td>Valitse kirjailija: </td><td><select name=\"kirjanKirjailija\">";														// Kirjailija
						foreach ($kirjailijat as $kirjailija){
							echo "<option value=\""  . $kirjailija[0] . "\">" . $kirjailija[1] . "</option>";
						} 
						echo "</select></td></tr>";
						echo "<tr><td>Valitse julkaisija: </td><td><select name=\"kirjanJulkaisija\">";														// Julkaisija
						foreach ($julkaisijat as $julkaisija){
							echo "<option value=\""  . $julkaisija[0] . "\">" . $julkaisija[1] . "</option>";
						} 
						echo "</select></td></tr>";
						echo "<tr><td>Valitse kansityyppi: </td><td><select name=\"kirjanKansi\">";															// Kansityyppi
						foreach ($kannet as $kansi){
							echo "<option value=\""  . $kansi[0] . "\">" . $kansi[1] . "</option>";
						}
						echo "</select></td></tr>";
						echo "<tr><td>Anna kirjan varastosaldo: </td><td><input type=\"text\" name=\"uudenKirjanVarastosaldo\" /></td></tr>";				// Varastosaldo
						echo "<tr><td>Anna kirjan hinta: </td><td><input type=\"text\" name=\"uudenKirjanHinta\" /></td></tr>";								// Hinta			
						echo "<tr><td>Anna kirjan kuvaus: </td><td><br/><textarea name=\"kirjanKuvaus\" rows=\"4\" cols=\"50\"></textarea></td></tr>";		// Kuvaus
						echo "<tr><td></td><td><input type=\"submit\" name=\"addNewBook\" value=\"Lisää uusi kirja\" /></td></tr></table>";					// Tietojen lähettäminen käsiteltäväksi
						echo "</form><hr/>";
					}
					if(isset($_POST["editBook"])){ // Muokattavan kirjan valitseminen
						$sql = $dbh->prepare('SELECT DISTINCT Teos.isbn, Teos.teos FROM Teos ORDER BY Teos.teos');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$teokset = $sql->fetchAll();
						
						echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
						echo "Valitse muokattava kirja: </td><td><select name=\"muokattavanKirjanISBN\">";
						foreach ($teokset as $teos){
							echo "<option value=\""  . $teos[0] . "\">" . $teos[1] . "</option>";
						} 
						echo "</select><br/><input type=\"submit\" name=\"selectBook\" value=\"Valitse kirja\" /><hr/>";
					}	
					if(isset($_POST["selectBook"])){ // Kirjan muokkaaminen
						$sql = $dbh->prepare('SELECT DISTINCT Kirjailija.id, Kirjailija.kirjailija FROM Kirjailija ORDER BY Kirjailija.kirjailija');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$kirjailijat = $sql->fetchAll();
						
						$sql = $dbh->prepare('SELECT DISTINCT Kansityyppi.id, Kansityyppi.tyyppi FROM Kansityyppi ORDER BY Kansityyppi.tyyppi');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$kannet = $sql->fetchAll();
						
						$sql = $dbh->prepare('SELECT DISTINCT Julkaisija.id, Julkaisija.julkaisija FROM Julkaisija ORDER BY Julkaisija.julkaisija');
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						$julkaisijat = $sql->fetchAll();
						
						$sql = $dbh->prepare('SELECT Teos.teos, Teos.kirjailija_id, Teos.julkaisija_id, Teos.kansityyppi_id, Teos.varastosaldo, Teos.hinta, Teos.kuvaus FROM Teos WHERE Teos.isbn = :isbn');
						$muokattavanKirjanISBN = test_input($_POST["muokattavanKirjanISBN"]);
						$sql->bindParam(":isbn", $muokattavanKirjanISBN);
						$ok = $sql->execute();
						if(!$ok) {print_r($sql->errorInfo());}
						while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
							$nimi = $row["teos"];
							$kirjailijaID = $row["kirjailija_id"]; 
							$julkaisijaID = $row["julkaisija_id"]; 
							$kansiID = $row["kansityyppi_id"]; 
							$varasto = $row["varastosaldo"];
							$hinta = $row["hinta"];
							$kuvaus = $row["kuvaus"];
						}
						
						echo "<form action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\" method=\"post\">";
						echo "<input type=\"hidden\" name=\"muokattavanKirjanISBN\" value=\"" . $muokattavanKirjanISBN . "\"/><table class=\"muutosTaulu\"><tr><td>Anna kirjan nimi: </td><td><input type=\"text\" name=\"muokattuKirjanNimi\" value=\"" . $nimi . "\"/></td></tr>";
						echo "<tr><td>Valitse uusi kirjailija: </td><td><select name=\"muokattuKirjanKirjailija\">";
						foreach ($kirjailijat as $kirjailija){
							echo "<option value=\""  . $kirjailija[0] . "\"";
							if ($kirjailija[0] == $kirjailijaID){
								echo "selected";
							}
							echo ">" . $kirjailija[1] . "</option>";
						} 
						echo "</select></td></tr>";
						echo "<tr><td>Valitse uusi julkaisija: </td><td><select name=\"muokattuKirjanJulkaisija\">";
						foreach ($julkaisijat as $julkaisija){
							echo "<option value=\""  . $julkaisija[0] . "\"";
							if ($julkaisija[0] == $julkaisijaID){
								echo "selected";
							}
							echo ">" . $julkaisija[1] . "</option>";
						} 
						echo "</select></td></tr>";
						echo "<tr><td>Valitse uusi kansi: </td><td><select name=\"muokattuKirjanKansi\">";
						foreach ($kannet as $kansi){
							echo "<option value=\""  . $kansi[0] . "\"";
							if ($kansi[0] == $kansiID){
								echo "selected";
							}
							echo ">" . $kansi[1] . "</option>";
						}
						echo "</select></td></tr>";
						echo "<tr><td>Anna kirjan uusi varastosaldo: </td><td><input type=\"text\" name=\"muokattuKirjanVarastosaldo\" value=\"" . $varasto . "\"/></td></tr>";
						echo "<tr><td>Anna kirjan uusi hinta: </td><td><input type=\"text\" name=\"muokattuKirjanHinta\" value=\"" . $hinta . "\"/></td></tr>";						
						echo "<tr><td>Anna kirjan uusi kuvaus: </td><td><br/><textarea name=\"muokattuKirjanKuvaus\" rows=\"4\" cols=\"50\">" . $kuvaus . "</textarea></td></tr>";
						echo "<tr><td></td><td><input type=\"submit\" name=\"editSelectedBook\" value=\"Muokkaa kirjaa\" /></td></tr></table>";
						echo "</form><hr/>";
					}
					if(isset($_POST["listOrders"])){ // Tilausten listaaminen
						$sql = $dbh->prepare('SELECT Tilaus.id, Tilaus.nimi, Tilaus.osoite, Tilaus.postinumero, Tilaus.postitoimipaikka, Tila.tyyppi FROM Tilaus, Tila WHERE Tila.id = Tilaus.tila_id');
						$ok = $sql->execute();
						if(!$ok) {print_r( $sql->errorInfo() );}
						echo "<table class=\"muutosTaulu\"><tr><th>ID</th><th>Toimitusosoite</th><th>Tila</th><th>Toiminnot</th></tr>";
						while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
							echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nimi"] . "</br>" . $row["osoite"] . "</br>" . $row["postinumero"] . " " . $row["postitoimipaikka"] . "</td><td>" . $row["tyyppi"] . "</td><td><a href=\"./hallinta.php?handle=" . $row["id"] ." \">Käsittele tilaus</a></td></tr>";
						}
						echo "</table>";
					}
					if(isset($_GET["handle"])){ // Yksittäisen tilauksen tietojen tulostus ja käsittelyn valitseminen
						$tilausID = test_input($_GET["handle"]);
						$sql = $dbh->prepare('SELECT Teos.teos, Tuotetilaus.teos_isbn, Tuotetilaus.kpl FROM Tilaus, Teos, Tuotetilaus WHERE Teos.isbn = Tuotetilaus.teos_isbn AND Tilaus.id = Tuotetilaus.tilaus_id AND Tilaus.id = :id');
						$sql->bindParam(":id", $tilausID);
						$ok = $sql->execute();
						if(!$ok) {print_r( $sql->errorInfo() );}
						$tuotteet = $sql->fetchAll();
						$sql = $dbh->prepare('SELECT Tilaus.id, Tilaus.nimi, Tilaus.osoite, Tilaus.postinumero, Tilaus.postitoimipaikka, Tila.tyyppi FROM Tilaus, Tila WHERE Tila.id = Tilaus.tila_id AND Tilaus.id = :id');
						$sql->bindParam(":id", $tilausID);
						$ok = $sql->execute();
						if(!$ok) {print_r( $sql->errorInfo() );}
						echo "<table class=\"muutosTaulu\"><tr><th>ID</th><th>Toimitusosoite</th><th>Tuotteet</th><th>Tila</th><th>Toiminnot</th></tr>";
						while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
							echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nimi"] . "</br>" . $row["osoite"] . "</br>" . $row["postinumero"] . " " . $row["postitoimipaikka"] . "</td><td>";
							foreach($tuotteet as $tuote){
								echo "<b>" . $tuote[0] . "</b>, " . $tuote[2] . " kpl<br/>" . $tuote[1] . "<br/>";
							}
							echo "</td><td>" . $row["tyyppi"] . "</td><td><a href=\"./hallinta.php?postita=" . $row["id"] ." \">Postita tilaus</a><br/><a href=\"./hallinta.php?poista=" . $row["id"] ." \">Poista tilaus</a></td></tr>";
						}
						echo "</table>";
					}
				}
			?>
			</div>
		</div>
	</body>
</html>