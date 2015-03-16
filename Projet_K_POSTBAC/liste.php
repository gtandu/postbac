<?php
		
		session_start();
		ini_set('memory_limit','64M');
		/* require_once('connexion.php'); */
		require('debut.php');
		require('menu.php'); 
		require('fonctions.php');

?>


<br>
<center><p id='textAccueil'><strong>Cet espace permet l'affichage des différents candidats, veuillez selectionner mode d'affichage :</strong></p></center> 
<br>
	<center>
	<form method="GET">
			
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="fi" value="Filière initiale"/>
		
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="fa" value="Filière alternance"/>
			
	</form>
	</center>
	
<!-- script qui prend en paramètre la référence du form dans lequel il se trouve et le nom des chekbox à cocher -->	
<script>
function CocheTout(ref, name) {
	var form = ref;
 
	while (form.parentNode && form.nodeName.toLowerCase() != 'form'){ 
		form = form.parentNode; 
	}
 
	var elements = form.getElementsByTagName('input');
 
	for (var i = 0; i < elements.length; i++) {
		if (elements[i].type == 'checkbox' && elements[i].name == name) {
			elements[i].checked = ref.checked;
		}
	}
}	
</script>
	

<?php



if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fi'])){

	echo "<center>";
	afficheEleve('fi',$bd); // à la place de 'fi', on mettra le $_POST du bouton, 
	echo "</center>";					//celui qui decide si on affiche les alternants ou les initiales
}
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fa'])){
	
	echo "<center>";
	afficheEleve('fa',$bd);
	echo "</center>";

}

print_r($_GET);


?>

<?php

echo '</table>';
echo '</form>';

?>


	








<?php
		require('fin.php'); 
?>
