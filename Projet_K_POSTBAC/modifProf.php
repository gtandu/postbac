<?php
	require_once('debut.php');
	require_once('menu.php');
	require_once('connexion.php');
	require_once('fonctions.php');
    

?>



<center><p id='textAccueil'> <strong>Cet espace permet l'ajout des différents enseignants qui traiteront les dossiers des candidats</strong></p></center> 

<?php
if(isset($_POST['modif'])){
$login= htmlentities($_POST['modif']);
$query="SELECT * FROM identification where login=:login";
$req=$bd->prepare($query);
$req->bindValue('login',$login);
$req->execute();
$donnees=$req->fetch();
}
?>

<center><a href="contenu.php"><button style="padding-left: 2em; padding-right:2em; margin-top:1em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary">Liste des Enseignants</button></a></center>


<form method="post" class="pure-form pure-form-aligned" id="ajoutProf" action="ajoutProf.php">

<?php
//Recupération des données associées au pseudo 
if(isset($_POST['modif'])){
$login= htmlentities($_POST['modif']);
$query="SELECT * FROM identification where login=:login";
$req=$bd->prepare($query);
$req->bindValue('login',$login);
$req->execute();
$donnees=$req->fetch();
}
?>

<input type="hidden" name="login" value="<?php echo $login;?>"/>
    <fieldset>
        <center><div class="pure-control-group">
            <label for="nom">Nom</label>
            <input size=35 id="name" type="text" placeholder="Nom" name="nom" value="<?php echo $donnees['nom']; ?>">
        </div>

        <div class="pure-control-group">
            <label for="prenom">Prénom</label>
            <input size=35 id="password" type="text" placeholder="Prénom" name="prenom" value="<?php echo $donnees['prenom']; ?>">
        </div>

        <div class="pure-control-group">
            <label for="email">Email </label>
            <input size=35 id="email" type="email" placeholder="Email Enseignant" name="email" value="<?php echo $donnees['email']; ?>">
        </div>

        <div class="pure-control-group">
            <label for="matiere">Matière</label>
            <input size=35 id="email" type="text" placeholder="Matière de l'enseignant" name="matiere" value="<?php echo $donnees['matiere']; ?>">
        </div>

        <div style="margin-top: 2em; margin-left:auto; width: 85%;"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-primary">Modifier</button></div>
         </center>
        </div>
    </fieldset>
</form>