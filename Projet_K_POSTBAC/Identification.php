<?php
		session_start();//Initialisation d'une session 
		require_once('connexion.php');
		require('debut.php'); 
		ini_set('memory_limit','64M');
		ini_set('display_errors',1);//permettre l' affichage des erreurs 
		
?>

	
	<center><h1> Bienvenue sur le portail d'Admission PostBac </h1></center>
	
	
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
			if (isset($_POST['name']) && $_POST['name'] == $rep['nom']){
				if (isset($_POST['password']) && $_POST['password'] == $rep['mdp']){
						
					$_SESSION['admin']=$rep['admin'];
					$_SESSION['name']=$rep['nom'];
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

	<div style=" margin-left:auto; margin-right:auto; width:50%;">
		
  		<form action="identification.php" method="post" class="pure-form pure-form-aligned">
	    <fieldset>
	        <div class="pure-control-group">
	            <label for="name">Identifiant</label>
	            <input name="name" type="text" placeholder="Identifiant">
	        </div>

	        <div class="pure-control-group">
	            <label for="password">Mot de Passe</label>
	            <input id="mdp" name="password" type="password" placeholder="Password">
	        </div>
	      
	        <p style=" margin-left:auto; margin-right:auto; width:73%; color: red; "><?php echo $msg; ?></p>

	        <div class="pure-controls" style="margin-left:auto; margin-right:auto; width:55%;">
	            

	            <a style="padding-left:0.5em;" href="mdpOublier.php">Mot de passe oublié ?</a> <br><br>

	            <button type="submit" class="pure-button pure-input-1-2 pure-button-primary">Me Connecter</button>
	        </div>

	        

	    </fieldset>
	</form>
	</div>
	</section>
	
	

	


<?php
		require('fin.php'); 
?>
