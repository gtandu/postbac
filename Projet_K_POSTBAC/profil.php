<?php
	session_start();
	require_once('debut.php');
	require_once('menu.php');
	require_once('connexion.php');
	require_once('fonctions.php');
	
?>

<center><p id='textAccueil'> <strong>Cet espace permet de modifier vos coordonées personnelles</strong></p></center> 

<?php
$msg=""; // Message pour le mot de passe
$msgE=""; // Message pour l'email


// On tente de changer le mot de passe uniquement si la valeur contient quelque chose    
if(!empty($_POST['new_mdp']))
{
	$msg=majMdpEnseignant($bd); // On recupere le resultat Mot de passe changé ou Erreur
    
}


// On tente de changer l'email uniquement si la valeur contient quelque chose
if(!empty($_POST['new_adresse_mail']))
{
	$msgE=majEmailEnseignant($bd); // On recupere le resultat email changé ou Erreur
}


?>
<?php
	
	
	$req=$bd -> prepare('SELECT * FROM identification');
	$req->execute();
	

	while($rep = $req->fetch(PDO::FETCH_ASSOC))
	{
		if (isset($_SESSION['name']) && $_SESSION['name'] == $rep['login']){
			// On recupere l'email et la matiere du prof
			$mail = $rep['email'];
			$matiere = $rep['matiere'];
		}
		
		
	}
	
	
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="profil.js"></script>

<br>
<br>

    <center><div class="pure-control-group">
        <label for="nom">Identifiant :</label>
		<?php
			echo $_SESSION['name']; // On affiche le nom du prof qui a sa session d'active
		?>
    </div></center>
		
	<br/>
		
	<center><div class="pure-control-group">
        <label for="matiere">Matière :</label>
		<?php
			echo $matiere; // On affiche la matiere du prof
		?> 
    </div></center>
		
		<br/>

<center><div style="width:100%;">
	<form method="post" class="pure-form pure-form-aligned"  action="profil.php">
    <fieldset>		

    	<div class="pure-control-group" style="position:relative; width:40%; float:left; left:10%; margin-top:2em; ">
		<center><span><strong>Changer de mot de passe </strong></span></center>
       
		<p>
			<p style=" margin-left:auto; margin-right:auto; width:73%; color: red; "><?php echo $msg; ?></p>
            <label for="mdp_actuel">Mot de passe actuel</label>
            <span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
			<input id="mdp_actuel" class="form-control" type="password" placeholder="Mot de passe actuel" name ="mdp_actuel">
			
		</p>
		
		<p>
			<label for="mdp_new">Nouveau mot de passe</label>
			<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
			<input id="mdp_new" class="form-control" type="password" placeholder="Nouveau mot de passe" name ="new_mdp">
			<span id="erreur-mdp" class="erreur" style="color: red;">Trop court !</span>
		</p>
		
		<p>
			<label for="mdp_confirm">Retappez mot de passe</label>
			<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
			<input id="mdp_confirm" class="form-control" type="password" placeholder="Retappez mot de passe" name ="mdp_confirm">
			<span id="erreur-confirm" class="erreur" style="color: red;">Différents !</span>
        </p>
		
		<div style="margin-left: auto; margin-right: auto; width: 35%;">
			<button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-primary" id="enregitrermdp">Enregistrer</button>
		</div>
		</div>
		<br/>
		
		<div class="pure-control-group" style="position:relative; width:40%; float:right; right:15%; margin-top: 1em;">
		<center><span><strong> Changer d'adresse mail </strong><span></center>
			<p>
				<label for="email">Email actuel </label>
				<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>
				<input class="form-control" type="email" placeholder="Adresse Email Actuel" name="adresse_mail_actif" value="<?php echo $mail?>">
				
			</p>
			
			<p>
				<label for="email">Nouvelle Email </label>
				<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>
				<input class="form-control" type="email" placeholder="Nouvelle Adresse Email" name="new_adresse_mail">
				
			</p>
			
			<p>
				<label for="mdp_actuel">Mot de passe</label>
				<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
				<input class="form-control" type="password" placeholder="Mot de passe" name ="mdp_actif">
				<p style=" margin-left:auto; margin-right:auto; width:73%; color: red; "><?php echo $msgE; ?></p>
			</p>
		
        <div style="margin-left: auto; margin-right: auto; width: 35%;">
        	<button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-primary">Enregistrer</button>
        </div>
        </div>
    
    </fieldset>
</form>
</div></center>

