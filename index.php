<?php include("header.php"); ?>
	<div id="main">
		<?php
			// kaikkien teosten hakeminen kannasta
			$sql = $dbh->prepare('SELECT Teos.isbn, Teos.teos, Kirjailija.kirjailija, Julkaisija.julkaisija, Kansityyppi.tyyppi, Teos.varastosaldo, Teos.hinta, Teos.kuvaus FROM Teos,Kirjailija,Julkaisija,Kansityyppi WHERE Teos.kirjailija_id=Kirjailija.id AND Teos.julkaisija_id=Julkaisija.id AND Teos.kansityyppi_id=Kansityyppi.id ORDER BY RAND() LIMIT 5');
			$ok = $sql->execute();
			if(!$ok) {print_r($sql->errorInfo());}
			while($row = $sql->fetch(PDO::FETCH_ASSOC) ) {
				include "teos.php";
			}
			// lopuksi aikanaan suljetaan: $dbh = null ;
			$dbh=null;
		?>
	</div>
<?php include("footer.php"); ?>