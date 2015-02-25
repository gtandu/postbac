<?php

		require_once('connexion.php');
		ini_set('memory_limit','64M');


  $req=$bd -> prepare('SELECT * FROM identification');
  $req->execute();

 if(!empty($_POST))
  {
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			//print_r($_POST);
			if (isset($_POST['name']) && $_POST['name'] == $rep['nom']){
				if (isset($_POST['password']) && $_POST['password'] == $rep['mdp']){
					//echo 'reussie';
					session_start();
					//$_SESSION['id']=$rep['id'];
					//$_SESSION['name']=$rep['name'];
					$_SESSION['admin']=$rep['admin'];
					header('location: accueil.php');
					

				}
				else{
					//echo 'echec';
					//$msg = 'Mot de pas incorrect !';
					header('location: identification.php');

				}
			}
			
		}	
	}



 ?>