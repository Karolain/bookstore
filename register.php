<?php 
	include("header.php"); 
	require_once 'Validate.php';
	$validator = new Validate();
?>
<div id="main">
	<?php 
		echo "<h2>Rekisteröidy</h2>";
		if(isset($_SESSION["userId"])){ // Näytetään sisäänkirjautuneelle
			echo "<p>Olet jo rekisteröitynyt.</p>";
		} else {
			$validi = $validator->email($_POST["email"], array('check_domain' => 'true')); // Tarkistetaan onko validi sähköposti ja domain PEAR:n Validate avulla
			if($validi){
				$sql = $dbh->prepare('SELECT Asiakas.email FROM Asiakas WHERE Asiakas.email = :email');
				$sql->bindParam(":email", $_POST["email"]);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
				if($sql->rowCount() > 0){
					$spostiOlemassa = true;
				}
			} // Kilometri-iffi, jossa tarkistetaan kaikki mahdolliset virheet rekisteröitymisessä
			if(!isset($_POST["email"]) || !isset($_POST["salasana"]) || !isset($_POST["salasanab"]) || !isset($_POST["nimi"]) || !isset($_POST["osoite"]) || !isset($_POST["postinumero"]) || !isset($_POST["postitoimipaikka"]) || strlen($_POST["email"]) < 1 || strlen(test_input($_POST["nimi"])) < 1 || strlen(test_input($_POST["osoite"])) < 1 || strlen(test_input($_POST["postinumero"])) < 1 || strlen(test_input($_POST["postitoimipaikka"])) < 1  || $_POST["salasanab"] != $_POST["salasana"] || strlen($_POST["salasana"]) < 8 || !$validi || $spostiOlemassa){
				if($_POST["salasanab"] != $_POST["salasana"] && isset($_POST["salasanab"]) && isset($_POST["salasana"])){
					echo "<p>Salasanat eivät täsmää.</p>";
				}
				if (strlen($_POST["salasana"])<8 && isset($_POST["salasana"])){
					echo "<p>Salasanan pitää olla yli 8 merkkiä.</p>";
				}
				if (!$validi && isset($_POST["email"])){
					echo "<p>Sähköposti ei ole validi.</p>";
				}
				if ($spostiOlemassa){
					echo "<p>Sähköposti on jo olemassa.</p>";
				} // Rekisteröitymisformi
				echo("<form action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\" method=\"post\">
					<p>Täytä kaikki kohdat. Salasana vähintään 8 merkkiä.</p>
					<table>
						<tr>
							<td>Sähköposti:</td><td><input type=\"email\" name=\"email\" placeholder=\"Sähköposti\"/></td>
						</tr><tr>
							<td>Salasana:</td><td><input type=\"password\" name=\"salasana\" placeholder=\"Salasana\"/></td>
						</tr><tr>
							<td>Salasana uudelleen:</td><td><input type=\"password\" name=\"salasanab\" placeholder=\"Salasana uudelleen\"/></td>
						</tr><tr>
							<td>Nimi:</td><td><input type=\"text\" name=\"nimi\" placeholder=\"Nimi\"/></td>
						</tr><tr>
							<td>Osoite:</td><td><input type=\"text\" name=\"osoite\" placeholder=\"Osoite\"/></td>
						</tr><tr>
							<td>Postinumero:</td><td><input type=\"text\" name=\"postinumero\" placeholder=\"Postinumero\"/></td>
						</tr><tr>
							<td>Postitoimipaikka:</td><td><input type=\"text\" name=\"postitoimipaikka\" placeholder=\"Postitoimipaikka\"/></td>
						</tr><tr>
							<td></td><td><input type=\"submit\" value=\"Rekisteröidy\"/></td>
						</tr>
					</table>
				</form>");
			} else {
				echo "<p>Kiitos rekisteröitymisestäsi.</p>";
				// Rekisteröitymistietojen testaaminen
				$nimi = test_input($_POST["nimi"]);
				$osoite = test_input($_POST["osoite"]);
				$postinro = test_input($_POST["postinumero"]);
				$toimipaikka = test_input($_POST["postitoimipaikka"]);
				$email = $_POST["email"];
				$salasana = hash("sha256",$_POST["salasana"]);
				// Tietojen lisääminen kantaan
				$sql = $dbh->prepare("INSERT INTO Asiakas (nimi, osoite, postinumero, postitoimipaikka, email, salasana) VALUES (:nimi, :osoite, :postinro, :toimipaikka, :email, :salasana)" );
				$sql->bindParam(":nimi", $nimi);
				$sql->bindParam(":osoite", $osoite);
				$sql->bindParam(":postinro", $postinro);
				$sql->bindParam(":toimipaikka", $toimipaikka);
				$sql->bindParam(":email", $email);
				$sql->bindParam(":salasana", $salasana);
				$ok = $sql->execute();
				if(!$ok) {print_r( $sql->errorInfo() );}
			}
		}
	?>
</div>
<?php include("footer.php"); ?>