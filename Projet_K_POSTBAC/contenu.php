<?php
	session_start();
	require_once('debut.php');
	require_once('menu.php');
	require_once('connexion.php');
	require_once('fonctions.php');
	//require_once('Integration.php');	


?>




<br>
<center><p id='textAccueil'> <strong>Cet espace permet la gestion des différents enseignants qui traiteront les dossiers des candidats</strong></p></center> 

<?php


<<<<<<< HEAD
if (htmlentities($_SESSION['admin'])==1){
=======
if ($_SESSION['admin']==1){
>>>>>>> origin/master


 echo '<center><div style="margin-left: auto; margin-right: auto; width: 20%; ">
 		<a href="FormulaireProf.php">
 			<button style="padding-left: 2em; padding-right:2em; margin-top:1em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary">Ajouter Enseignant</button>
 		</a>
 	</div></center>';

}

?>

<?php
//----------------------------Pour supprimer un enseignant---------------------//

//on récupere tous les enseignants de la table 
$query="SELECT * FROM identification";
$req=$bd->prepare($query);
$req->execute();

//Pour chaque enseignants
while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
{

//On test quel est celui qui a été selectionné pour la suppression
if (isset($_POST['supProf']) && $_POST['supProf']==$tmp1['login'])
	{

	//Affichage d'une confirmation pour la suppression
	echo '<center><p style="color: red;">Etes-vous sures de vouloir supprimer "'.$tmp1['nom'].' '.$tmp1['prenom'].'" de la base de données des enseignants ?</p></center>';
	echo '<center>
	<form method="post" action="contenu.php">
	<input class="pure-button pure-input-1-2 pure-button-primary" type="submit" name="delete" value="oui">
	<input class="pure-button pure-input-1-2 pure-button-primary" type="submit" name="delete" value="non">
	<input type="hidden" name="login" value="'.$tmp1['login'].'"/> 
	</form>
	</center>';

	}
}

//si confirmation accepté 
if (isset($_POST['delete']) && $_POST['delete']=='oui')
{
	$login=htmlentities($_POST['login']);
	//On supprime l'enseignant de la base de données
	supprimeProf($bd, $login);

}


// print_r($_POST);
// print_r($_GET);


echo "<center>";
afficheProf($bd);
echo "</center>";

?> 







<?php
require_once('fin.php');
?>
