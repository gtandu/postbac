<?php
		ini_set('memory_limit','64M');
		require('debut.php');
		require('menu.php'); 
		require_once('connexion.php');
		require('fonctions.php');


	if ($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['selection'])){

		print_r($_POST);
		echo "<br>";

		if(!isset($_POST['selection'])){
   			echo "Aucune checkbox n'a été cochée";
		}

	// $query = 'SELECT nom, prénom FROM atraiterfa, atraiterfi WHERE Numero=677990';


	// foreach($_POST['selection'] as $valeur)
	// {
 //   		$query.= 'AND $valeur ."a été cochée<br>';
	// }

	}
?>
<p id='textAccueil'> <strong>Veuillez selectionner l'enseignant qui se chargera des canditats suivant : </strong></p> 

<FORM style="margin-left:auto; margin-right:auto; width:15%;">
<SELECT name="prof" size="1">


<?php

$queryProf='SELECT nom, prenom FROM identification';
$req=$bd->prepare($queryProf);
$req->execute();

$rep = $req->fetch(PDO::FETCH_ASSOC);

while($tmp1=$req->fetch(PDO::FETCH_ASSOC)){

	echo 	"<OPTION>".$tmp1['nom']." ".$tmp1['prenom'];

}


?>


</SELECT>
</FORM>



<?php
require_once('fin.php')
?>