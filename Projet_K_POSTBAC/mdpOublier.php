<?php
	
		require_once('connexion.php');
		require('debut.php'); 
		ini_set('memory_limit','64M');
		ini_set('display_errors',1);//permettre l' affichage des erreurs 
		
?>

<center><h1> Bienvenue sur le portail d'Admission PostBac </h1></center>

<br>

<center><p id='textAccueil'> <strong>Vous avez perdu ou oublié votre mot de passe pour vous identifier sur AdmissionPostbac et accéder à votre espace.</strong></p></center>	
<center><a style="text-align: center;" href="Identification.php">Revenir à la Connexion</a></center> 

<br><br>

<form style=" margin-left:auto; margin-right:auto; width:38%;" class="pure-form" methode="GET">
    <fieldset>
        <legend>Merci de renseigner les champs suivant:</legend>
        <br>

        <input name="email" type="email" placeholder="Email">
        <input name="name" type="text" placeholder="Identifiant">

        <button type="submit" class="pure-button pure-button-primary">Envoyer</button>
    </fieldset>
</form>


<?php

  $req=$bd -> prepare('SELECT * FROM identification');
  $req->execute();

if(!empty($_GET))
  {
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			if (isset($_GET['name']) && $_GET['name'] == $rep['nom']){
				//mail( $_GET['email'], 'Identifiant et Mot de passe PostBac', 'votre mot de passe est'.$rep['mdp'], null, 'karine.ouldbraham@gmail.com');
				echo '<p style="margin-left:auto; margin-right:auto; width:38%; color:red;">Votre mot de passe vous a été envoyé !</p>';
			}
		}
	}

?>