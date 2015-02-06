<?php
	$dir  = opendir("teokset");	// Kuvien hakeminen teoksiin, kuvan nimi on teoksen isbn
	$kuva = "empty.png";
	while (false !== ($file = readdir($dir))){
		$path_parts = pathinfo($file);
		if($path_parts['filename'] == $row["isbn"] && $path_parts['extension'] == 'png' ){
			$kuva = $file;
		} 
	} // Tuotteen tulostaminen
	echo "<h2><a href=\"./tuote.php?id=" . $row["isbn"] . "\">" . $row["teos"] . "</a></h2><table><tr><td><img style=\"max-height:200px; max-width:130px;\" src=\"teokset/". $kuva ."\" alt=\"Kuva\" /></td><td class=\"left\"><p><b>Kirjailija:</b> " . $row["kirjailija"] . "</p>";
	echo "<p><b>Julkaisija:</b> " . $row["julkaisija"] . "<br/><b>ISBN:</b> " . $row["isbn"] . "<br/><b>Kansityyppi:</b> " . $row["tyyppi"];
	echo "<br/><b>Varastosaldo:</b> " . $row["varastosaldo"] . "<br/><b>Hinta:</b> " . $row["hinta"] . " €</p><p>" . $row["kuvaus"]  . "</p></td>";
	echo "</tr><tr><td></td><td><a href=\"./ostoskori.php?tuote=" . $row["isbn"] . "\"><img src=\"add2Cart.png\" alt=\"Lisää ostoskoriin\"/></a></td></tr></table>";
?>