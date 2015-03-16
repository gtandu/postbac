<?php
		session_start();
		require_once('connexion.php');
		//require('debut.php'); 
		ini_set('memory_limit','64M');
		ini_set('display_errors',1);
		$msg="";
		
?>

<?php

		


  $req=$bd -> prepare('SELECT * FROM identification');
  $req->execute();

 if(!empty($_POST))
  {
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			//print_r($_POST);
			if (isset($_POST['name']) && $_POST['name'] == $rep['nom']){
				if (isset($_POST['password']) && $_POST['password'] == $rep['mdp']){
					
					$_SESSION['admin']=$rep['admin'];
					$_SESSION['name']=$rep['nom'];
					echo '<script language="Javascript">
					document.location.replace("accueil.php");
					</script>';
					//header('location: accueil.php');
					

				}
				else{
					$msg = 'Mot de passe incorrect !';
				}
			}
			
		}	
	}



 ?>
	<section class="identification1">
	<header>
		<h1 style=" margin-left:auto; margin-right:auto; width:60%; color: blue;"> Bienvenue sur le portail d'Admission PostBac </h1>
	</header>
	<html>
	<head>
		<title>Projet PostBac</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
	<body>


	<div style=" margin-left:auto; margin-right:auto; width:40%;">
		
  		<form action="identification.php" method="post" class="pure-form pure-form-aligned">
	    <fieldset>
	        <div class="pure-control-group">
	            <label for="name">Identifiant</label>
	            <input name="name" type="text" placeholder="Identifiant">
	        </div>

	        <div class="pure-control-group">
	            <label for="password">Mot de Passe</label>
	            <input name="password" type="password" placeholder="Password">
				<?php
					echo $msg ;
				?>
	        </div>
	      
	        <div class="pure-controls">
	            <label for="cb" class="pure-checkbox">
	                <input id="cb" type="checkbox"> Se souvenir de moi </label>

	            

	            <button type="submit" class="pure-button pure-input-1-2 pure-button-primary">Me Connecter</button>
	        </div>

	        

	    </fieldset>
	</form>
	</div>
	</section>
	
	

	


<?php
		require('fin.php'); 
?>
