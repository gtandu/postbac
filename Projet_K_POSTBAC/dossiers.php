<?php
	session_start();	
	
	//une fois que l'utilisateur clique sur 'ok' dans validation temporaire
	if (isset($_POST['validerTmp']) && isset($_POST['NumEtudiant']))
	{
				$numEtudiant=$_POST['NumEtudiant'];
				$bonusMalus= array('dossier' => htmlentities($_POST['dossier']), 
									'lettre' => htmlentities($_POST['lettre']),
									'autre' => htmlentities($_POST['autre']));
			
		$json = json_encode($bonusMalus);
		setcookie($numEtudiant, $json, time()+3600*24);
		echo '<script language="Javascript">
					document.location.replace("dossiers.php");
			  </script>';

	}
	require_once('debut.php');
	require_once('connexion.php');
	require_once('fonctions.php');
	//require_once('Integration.php');
	require('menu.php'); 
	error_reporting(E_ALL); //Afficher toutes les erreurs 
	
?>





<?php
//Affichage si l'utilisateur est administrateur
if ($_SESSION['admin']==1){

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

	echo '<br>';

	//Bouton pour valider l'attribution 
	echo '<input class="pure-button pure-input-1-2 pure-button-primary" style="margin-top: 1.5em; border-radius:3px;" type="submit" name="attribuer" value="Valider">';

?>

	</SELECT></center>
	</FORM>

<?php
	if (isset($_POST['prof']))
	{
		//On récupere le login de l'enseignant séléctionné
		$prof=$_POST['prof'];
		//On affiche le tableau des candidats qui ont été attribué a l'enseigant séléctionné
		afficheCandidatDuProf($bd, $prof);
	}
	// print_r($_POST);
	print_r($_SESSION);

}
// Affichage si l'utilisateur est un enseignant
else{

	echo "<center><p id='textAccueil'><strong>Voici la liste des candidats que vous devrez traiter en remplissant les champs correspondants: </strong></p></center>";

	// print_r($_SESSION);

	if (isset($_GET['formFinal']))
	{
		echo '<center><p style="color: red;">Etes-vous sures de vouloir valider se formulaire ? Aucune modification ne pourra être faites par la suite !</p></center>';
		echo '<center>
		<form method="get" action="dossiers.php">
		<input class="pure-button pure-input-1-2 pure-button-primary" type="submit" name="ValidationFinal" value="oui">
		<input class="pure-button pure-input-1-2 pure-button-primary" type="submit" name="ValidationFinal" value="non">
		</form>
		</center>';
	}

	if (isset($_SESSION['name']))
	{
		//On récupere le login de l'enseignant.
		$prof=$_SESSION['name'];
		//On affiche le tableau des candidats qui ont été attribué à l'enseigant 
		afficheCandidatDuProf($bd, $prof);
	}

	

	echo "<center><FORM method = 'get' style='margin-left:auto; margin-right:auto; width:20%;' action='dossiers.php'>";

	//Bouton pour valider le formulaire FINAL 
	echo '<input class="pure-button pure-input-1-2 pure-button-primary" style="margin-top: 1.5em; border-radius:3px;" type="submit" name="formFinal" value="Validation Final">';

	echo '<Form></center>';

	//une fois que l'utilisateur clique sur 'ok' dans validation temporaire
	if (isset($_POST['validerTmp']) && isset($_POST['NumEtudiant']))
	{
		$cookie = $_COOKIE[$numEtudiant];
		$cookie = stripslashes($cookie);
		$bonusMalus = json_decode($cookie, true);

	}

	// unset($_COOKIE['214346']);
	// print_r($_COOKIE);
	// print_r($_POST);
	// print_r($_GET);

}

?>
















<?php
		require('fin.php'); 
?>
