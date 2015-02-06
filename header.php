<?php 
// if($_SERVER["HTTPS"] != "on")
// {
    // header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    // exit();
// }
session_start(); 
if(!isset($_SESSION["ostoskori"])){ // Luodaan ostoskorisessio, jos sellaista ei jo ole
	$_SESSION["ostoskori"] = array();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>Book Store</title>
		<link rel="stylesheet" type="text/css" href="stylesheet.css" />
		<?php
			if(isset($_POST["logout"]) && $_POST["logout"]=="Kirjaudu ulos"){ // Tarkistetaan kirjauduttiinko ulos
				unset($_SESSION["userId"]);
			}
			include("dbcon.php"); 
			function test_input($data){ 						// Testataan annettu data ja poistetaan haitalliset merkinnät
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}
			if(isset($_POST["password"]) && isset($_POST["sposti"])){
				$salasana = hash("sha256",$_POST["password"]); 	// Hashataan annettu salasana
				$sposti = test_input($_POST["sposti"]);			// Testataan annettu sähköposti injekxztioiden varalta
				$sql = $dbh->prepare('SELECT Asiakas.id, Asiakas.email, Asiakas.salasana FROM Asiakas WHERE Asiakas.email = :email AND Asiakas.salasana = :salasana');
				$sql->bindParam(":email", $sposti);
				$sql->bindParam(":salasana", $salasana);
				$ok = $sql->execute();
				if(!$ok) {print_r($sql->errorInfo());}
				$asiakas = $sql->fetchAll();
				if ($sql->rowCount() > 0){ 						// Lisätään asiakassessio, jos kannasta löytyi asiakas
					$_SESSION["userId"] = $asiakas[0][0];
				}
			}
			$kpl = 0;
			foreach($_SESSION["ostoskori"] as $tuote => $maara){
				$kpl+=$maara;
			} 
		?>
	</head>
	<body>
		<div class="wrap">
			<header> 
			</header>
			<nav>
				<a class="nav" href="./">Etusivu</a> 
				<a class="nav" href="selaus.php">Haku</a> 
				<a class="nav" href="ostoskori.php">Ostoskori<?php if($kpl>0){echo" - " . $kpl . "kpl";}?></a> 
				<a class="lang" href="en/"><img src="enflag.png" alt="EN"/></a>
				<form class="login" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
					<?php
					if(isset($_SESSION["userId"])){ // Kirjaudu ulos
						echo("Olet kirjautunut sisään. <input type=\"submit\" name=\"logout\" value=\"Kirjaudu ulos\"/>");
					} else {
					if (isset($_POST["sposti"]) && isset($_POST["password"]) && $sql->rowCount() == 0){ // Tarkistetaan, jos käyttäjää ei löytynyt.
						echo "Väärä sähköposti tai salasana.";
					} else {
						echo "Kirjaudu sisään:"; // Sisäänkirjautumisformi
					}
					echo(" 
					<input class=\"text\" type=\"text\" name=\"sposti\" placeholder=\"Sähköposti\"/>
					<input class=\"text\" type=\"password\" name=\"password\" placeholder=\"Salasana\"/>
					<input type=\"submit\" value=\"Kirjaudu\"/>
					<a href=\"register.php\">Rekisteröidy</a>");
					}
					?>
				</form>
			</nav>
			<div id="menu">
				<?php
						// Kategorioittain hakeminen
						// Kerätään kannasta haetut tulokset tauluihin.
					$sql = $dbh->prepare('SELECT DISTINCT Kirjailija.kirjailija FROM Kirjailija ORDER BY Kirjailija.kirjailija');
					$ok = $sql->execute();
					if(!$ok) {print_r($sql->errorInfo());}
					$kirjailijat = $sql->fetchAll();
					
					$sql = $dbh->prepare('SELECT DISTINCT Kansityyppi.tyyppi FROM Kansityyppi ORDER BY Kansityyppi.tyyppi');
					$ok = $sql->execute();
					if(!$ok) {print_r($sql->errorInfo());}
					$kannet = $sql->fetchAll();
					
					$sql = $dbh->prepare('SELECT DISTINCT Julkaisija.julkaisija FROM Julkaisija ORDER BY Julkaisija.julkaisija');
					$ok = $sql->execute();
					if(!$ok) {print_r($sql->errorInfo());}
					$julkaisijat = $sql->fetchAll();
					
					echo "<h2>Pikahaku</h2><h3>Kansityyppi</h3><p>"; // Tulostetaan kansityypit, julkaisijat ja kirjailijat.
					foreach($kannet as $kansi){
						echo "<a href=\"./selaus.php?kansi=" . str_replace(' ','%20',$kansi[0]) . "\">" . $kansi[0] . "</a><br/>";
					}
					echo "</p><h3>Julkaisija</h3><p>";
					foreach($julkaisijat as $julkaisija){
						echo "<a href=\"./selaus.php?julkaisija=" . str_replace(' ','%20',$julkaisija[0]) . "\">" . $julkaisija[0] . "</a><br/>";
					}
					echo "</p><h3>Kirjailija</h3><p>";
					foreach($kirjailijat as $kirjailija){
						echo "<a href=\"./selaus.php?kirjailija=" . str_replace(' ','%20',$kirjailija[0]) . "\">" . $kirjailija[0] . "</a><br/>";
					}	
				?>
			</div>