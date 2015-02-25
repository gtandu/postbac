<?php
		
		session_start();
		ini_set('memory_limit','64M');
		/* require_once('connexion.php'); */
		require('debut.php');
		require('menu.php'); 
		require('fonctions.php');

?>



<p id='textAccueil'><strong>Cet espace permet l'affichage des différents candidats, veuillez selectionner mode d'affichage :</strong></p> 

	<div style="margin-left: auto; margin-right:auto; width: 35%; padding-top:2em; ">
	<form method="GET">
			
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="affiche" value="Filière initiale"/>
		
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="affiche" value="Filière alternance"/>
			
	</form>
	</div>



<?php



if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['affiche']) && $_GET['affiche']=='Filière initiale'){

	afficheEleve('fi',$bd); // à la place de 'fi', on mettra le $_POST du bouton, 
						//celui qui decide si on affiche les alternants ou les initiales
}
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['affiche']) && $_GET['affiche']=='Filière alternance'){
	

	afficheEleve('fa',$bd);

}




?>

<?php

echo '</table>';
echo '</form>';

?>


	








<?php
		require('fin.php'); 
?>
