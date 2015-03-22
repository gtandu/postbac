<?php
	session_start();
	require_once('debut.php');
	require_once('menu.php');
	require_once('connexion.php');
	require_once('fonctions.php');
	//require_once('Integration.php');	


?>

<?php


// Récupération des variables nécessaires à l'activation
$login = $_GET['log'];
$cle = $_GET['cle'];
$mail = $_GET['mail'];
 
// Récupération de la clé correspondant au $login dans la base de données
$req = $bd->prepare("SELECT cle FROM identification WHERE login = :login ");
if($req->execute(array(':login' => $login)) && $row = $req->fetch())
  {
    $clebdd = $row['cle'];	// Récupération de la clé
  }
 
 

if($cle == $clebdd) // On compare nos deux clés	
{
  // Si elles correspondent on active le compte !	
	echo "Votre adresse mail a bien était changé !";

	// La requête qui va mettre a jour l'adresse mail
	$query='UPDATE identification SET email =:new_email WHERE login =:login';
	$req=$bd->prepare($query);
	$req->bindValue('login',$login);
	$req->bindValue('new_email',$mail);
	$req->execute();
}

else // Si les deux clés sont différentes on provoque une erreur...
{
  echo "Erreur ! Votre compte ne peut être activé...";
}


header ("Refresh: 10;URL=profil.php");
// Redirection vers page_suivante.php après un délai de 10 secondes
// durant lesquelles la page actuelle (page_premiere.php, par exemple) est affichée

 
?>
