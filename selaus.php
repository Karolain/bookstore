<?php include("header.php"); ?>
<div id="main">
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="get">
		<h2>Haku</h2>
		<table>
			<tr>
				<td>Nimi:</td><td><input type="text" name="kirjailija" placeholder="Kirjailija"/></td>
			</tr><tr>
				<td>Osoit:</td><td><input type="text" name="julkaisija" placeholder="Julkaisija"/></td>
			</tr><tr>
				<td>Kansityyppi:</td><td><input type="text" name="kansi" placeholder="Kansityyppi"/></td>
			</tr><tr>
				<td></td><td><input type="submit" value="Hae"/></td>
			</tr>
		</table>
	</form>
	<hr />
	<?php 
		if(isset($_GET["kirjailija"]) || isset($_GET["julkaisija"]) || isset($_GET["kansi"])){
			// Hakutulosten käsittely ja teosten hakeminen kannasta.
			$sql = $dbh->prepare('SELECT Teos.isbn, Teos.teos, Kirjailija.kirjailija, Julkaisija.julkaisija, Kansityyppi.tyyppi, Teos.varastosaldo, Teos.hinta, Teos.kuvaus FROM Teos,Kirjailija,Julkaisija,Kansityyppi WHERE Teos.kirjailija_id=Kirjailija.id AND Teos.julkaisija_id=Julkaisija.id AND Teos.kansityyppi_id=Kansityyppi.id AND Kirjailija.kirjailija LIKE ? AND Julkaisija.julkaisija LIKE ? AND Kansityyppi.tyyppi LIKE ?');
			$ok = $sql->execute(array("%".test_input($_GET["kirjailija"])."%","%".test_input($_GET["julkaisija"])."%","%".test_input($_GET["kansi"])."%"));
			if(!$ok) {print_r($sql->errorInfo());}
			if($sql->rowCount() == 0){
				echo "<p>Ei hakutuloksia.</p>";
			} else {
				while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
					include "teos.php";
				}
			}
			// lopuksi aikanaan suljetaan: $dbh = null ;
			$dbh=null;
		}
	?>
</div>
<?php include("footer.php"); ?>