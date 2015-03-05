
<?php
		session_start();
		require_once('connexion.php');
		require('fonctions.php');
		require('debut.php');
		require('menu.php'); 
	
?>
// A Faire le CSS et modif avec le bouton enregistrer

<?php
	$req=$bd -> prepare('SELECT * FROM identification');
	$req->execute();


	while($rep = $req->fetch(PDO::FETCH_ASSOC))
	{
		if (isset($_SESSION['name']) && $_SESSION['name'] == $rep['nom']){
			// On recupere l'email et la matiere du prof
			$mail = $rep['email'];
			$matiere = $rep['matiere'];
		}
		
		
	}
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="profil.js"></script>
<form method="post" class="pure-form pure-form-aligned" action="profil.php">
    <fieldset><center>
        <div class="pure-control-group">
            <label for="nom">Identifiant :</label>
			<?php
				echo $_SESSION['name']; // On affiche le nom du prof qui a sa session d'active
			?>
        </div>
		<br/>
		<div class="pure-control-group">
            <label for="matiere">Matière :</label>
			<?php
				echo $matiere; // On affiche la matiere que du prof
			?> 
        </div>
		
		<br/>
		
		<span> Changer de mot de passe </span>
		
        <div class="pure-control-group">
		<p>
            <label for="mdp_actuel">Mot de passe actuel</label>
            <span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
			<input id="mdp_actuel" class="form-control" type="password" placeholder="Mot de passe actuel" name ="mdp_actuel">
		</p>
		<p>
			<label for="mdp_new">Nouveau mot de passe</label>
			<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
			<input id="mdp_new" class="form-control" type="password" placeholder="Nouveau mot de passe" name ="mdp_new">
		</p>
		<p>
			<label for="mdp_confirm">Retappez mot de passe</label>
			<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
			<input id="mdp_confirm" class="form-control" type="password" placeholder="Retappez mot de passe" name ="mdp_confirm">
			<span id="erreur-confirm" class="erreur">Différents !</span>
        </p>
		
		<div style="margin-left: auto; margin-right: auto; width: 35%;"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-primary">Enregistrer</button></div>
		
		<br/>
		
		<span> Changer d'adresse mail <span>
			<p>
				<label for="email">Email </label>
				<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>
				<input class="form-control" type="email" placeholder="Adresse Email" name="adresse_mail" value="<?php echo $mail?>">
				
			</p>
			
			<p>
				<label for="mdp_actuel">Mot de passe</label>
				<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
				<input class="form-control" type="password" placeholder="Mot de passe" name ="mdp_actuel">
			</p>
		</div>

        <div style="margin-left: auto; margin-right: auto; width: 35%;"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-primary">Enregistrer</button></div>
         
        </div>
    </fieldset></center>
</form>

