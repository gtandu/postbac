<?php
	session_start();
	require_once('debut.php');
	require_once('connexion.php');
	require_once('fonctions.php');
	//require_once('Integration.php');
	require('menu.php'); 
	error_reporting(E_ALL); //Afficher toutes les erreurs 
	
?>


<center><p id='textAccueil'><strong>En selectionnant un enseignant vous verrez afficher les candidats qu'il devra traiter: </strong></p></center> 


<FORM method = "post" style="margin-left:auto; margin-right:auto; width:20%;" action="dossiers.php">
<center><SELECT name="prof" size="<?php nbEnseignant($bd) ?>" style="width:200px;">


<?php


//Requête pour récupérer tous les enseignant (sauf l'admin)
$queryProf='SELECT * FROM identification WHERE admin=0';
$req=$bd->prepare($queryProf);
$req->execute();

//Affiche chaque enseignant dans une liste déroulante (<SELECT>)
while($tmp1=$req->fetch(PDO::FETCH_ASSOC)){

	echo 	"<OPTION value=".$tmp1['login'].">".$tmp1['nom']." ".$tmp1['prenom'];

}

?>


</SELECT></center>
</FORM>


















<?php
		require('fin.php'); 
?>
