<?php
		session_start();//Initialisation d'une session 
		require_once('connexion.php');
		require('debut.php'); 
		ini_set('memory_limit','64M');
		ini_set('display_errors',1);//permettre l' affichage des erreurs 
		
?>

	<br><br>
	<center><h1> Bienvenue sur le portail d'Admission PostBac </h1></center>
	<br><br>
	
<?php

		


  $req=$bd -> prepare('SELECT * FROM identification');
  $req->execute();

  $msg= "";

  //------------------------Vérification des identifiants et mots de passes lors de la connection-------------//

 if(!empty($_POST))
  {
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			//print_r($_POST);
			if (isset($_POST['name']) && $_POST['name'] == $rep['login']){
				if (isset($_POST['password']) && $_POST['password'] == $rep['mdp']){

					$_SESSION['admin']=$rep['admin'];
					$_SESSION['name']=$rep['login'];
					echo '<script language="Javascript">
					document.location.replace("accueil.php");
					</script>';
				}
				else{
					$msg = "Identifiant ou Mot de passe incorrect !";
				}
			}
			
		}	
	}



 ?>

 <!------------------Formulaire d'identification---------------->

	<div style="width:90%;">
		
  		<center><form action="identification.php" method="post" class="pure-form pure-form-aligned">
	    
	        <div class="pure-control-group">
	            <label for="name">Identifiant</label>
	            <input name="name" type="text" placeholder="Identifiant">
	        </div>

	        <div class="pure-control-group">
	            <label for="password">Mot de Passe</label>
	            <input id="mdp" name="password" type="password" placeholder="Password">
	        </div>
	      
	        <p style=" margin-left:auto; margin-right:auto; width:73%; color: red; "><?php echo $msg; ?></p>

	        <div class="pure-controls" style="margin-left: auto; margin-right: auto; width:30%;">
	            

	            <a href="mdpOublier.php">Mot de passe oublié ?</a> <br><br>

	            <button type="submit" class="pure-button pure-input-1-2 pure-button-primary">Me Connecter</button>
	        </div>

	        

	  
	</form></center>
	</div>
	</section>
	
	

	


<?php
		require('fin.php'); 
?>
