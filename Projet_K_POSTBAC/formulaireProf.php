<?php
    session_start();
	require_once('debut.php');
	require_once('menu.php');
	require_once('connexion.php');
	require_once('fonctions.php');
    
	//require_once('Integration.php');	

?>



<center><p id='textAccueil'> <strong>Cet espace permet l'ajout des différents enseignants qui traiteront les dossiers des candidats</strong></p></center> 



<center><a href="contenu.php"><button style="padding-left: 2em; padding-right:2em; margin-top:1em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary">Liste des Enseignants</button></a></center>

<form method="$_GET" class="pure-form pure-form-aligned" id="ajoutProf" action="ajoutProf.php">
    <fieldset>
        <center><div class="pure-control-group">
            <label for="nom">Nom</label>
            <input size=35 id="name" type="text" placeholder="Nom" name="nom">
        </div>

        <div class="pure-control-group">
            <label for="prenom">Prénom</label>
            <input size=35 id="password" type="text" placeholder="Prénom" name="prenom">
        </div>

        <div class="pure-control-group">
            <label for="email">Email </label>
            <input size=35 id="email" type="email" placeholder="Email Enseignant" name="email">
        </div>

        <div class="pure-control-group">
            <label for="matiere">Matière</label>
            <input size=35 id="email" type="text" placeholder="Matière de l'enseignant" name="matiere">
        </div>

        <div style="margin-top: 2em; margin-left:auto; width: 85%;"><button style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-button-primary">Ajouter</button></div>
        </center>
         
        </div>
    </fieldset>
</form>















<?php
require_once('fin.php');
?>


