<?php
		ini_set('memory_limit','64M');
		require('debut.php');
		require('menu.php');
		require_once('connexion.php');
		require('fonctions.php');


?>
<center><p id='textAccueil'><strong>Veuillez selectionner l'enseignant qui se chargera des canditats sélectionés : </strong></p></center> 


<FORM method = "post" style="margin-left:auto; margin-right:auto; width:20%;" action="dossierATraiter.php">
<center><SELECT name="prof" size="<?php nbEnseignant($bd) ?>" style="width:200px;">


<?php


//Requête pour récupérer tous les enseignant (sauf l'admin)
$queryProf='SELECT * FROM identification WHERE admin=0';
$req=$bd->prepare($queryProf);
$req->execute();

//Affiche chaque enseignant dans une liste déroulante (<SELECT>)
while($tmp1=$req->fetch(PDO::FETCH_ASSOC)){

	echo 	"<OPTION value=".$tmp1['login'].">".$tmp1['nom']." ".$tmp1['prenom'];

}

echo '<br>';

//Bouton pour valider l'attribution 
echo '<input class="pure-button pure-input-1-2 pure-button-primary" style="margin-top: 1.5em; border-radius:3px;" type="submit" name="attribuer" value="Valider">';
// Input caché pour récupérer la valeur de la filière sélectionnée
echo '<input type="hidden" name="filiere" value="'.$_POST['filiere'].'"/>';

if (isset($_POST['selection'])){
	
	for ($i=0; $i<count($_POST['selection']);$i++)// On enregistre les id des élève dans un input hidden
	{
 	 	echo '<input type="hidden" name="num'.$i.'" value="'.$_POST['selection'][$i].'"/>';
 	}	
 	//On enregistre le nombre d'élève à attribuer
 	echo '<input type="hidden" name="nombre" value="'.count($_POST['selection']).'"/>';
}			


// //Une fois le bouton Valider cliqué, on insert le login de l'enseignant séléctionné (<select>) dans la colonne des etudiants
if ( isset($_POST['attribuer']) && isset($_POST['filiere']) && isset($_POST['nombre'])){

	//On ajoute la colonne "enseignant" dans la table concerné
	$ajout = ajoutColonneEnseignant($bd);
	$affiche=FALSE;
	//On vérifie que l'ajout a bien été fait pour continué
	if ($ajout == false){
		echo $ajout;
		echo '<br><br><center><strong><p style="color: red;">Erreur lors de l\'attribution !</p></strong></center>';
	}
	else{
		//on enregistre la filiere dans laquel on va faire la modif 
		$filiere = 'Etudiant';
 		$filiere .= strtoupper($_POST['filiere']);//mettre les lettre en majuscule 
 		// echo $filiere;
 		//Tant que le nombre d'élève n'est pas atteint
 		for($i=0; $i<=$_POST['nombre']-1; $i++)
 		{
 			//on enregistre le nom de la variable
 			$num='num';
 			$num.=$i;
 			//On ajoute le login de l'enseignant dans la colonne 'enseigant' du candidat
 			$res = ajoutLoginEnseignant($bd, $_POST['prof'], $filiere, $_POST[$num]);

 			if ($res == FALSE){

 				echo '<br><br><center><strong><p style="color: red;">Erreur lors de l\'attribution !</p></strong></center>';
 			}
 			else{
 				$affiche=TRUE;
 			}
 		}
 		if ($affiche){
 			echo '<br><br><center><strong><p style="color: red;">Attibution réussie !</p></strong></center>';
 		}
 		echo '<br><center><a href="liste.php">Revenir à la liste des candidats</a></center>';
	}
}

?>

</SELECT></center>
</FORM>



<?php
// print_r($_POST);


//Affiche dans un tableau les élèves sélectionnés
if ($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['selection']) && isset($_POST['filiere']) )
{

		//On enregistre le nom du tableau dans lequel on devra chercher les élèves
		$filiere = 'Atraiter';
 		$filiere .= $_POST['filiere'];
		
		//création du tableau récapitulatif
		echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="15" style="width: 57%;">
			<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES SELECTIONNES</strong></CAPTION>
 			<tr><th>Numero</th><th>Nom</th><th>Prénom</th><th>Bac</th><th>Moyenne</th><th>AvisCE</th></tr> ';

 		 for ($i=0; $i<count($_POST['selection']);$i++)
 		 {

 		 	//On récupere toutes les infos du candidats dans sa table
 			$eleve = searchEleve($bd, $_POST['selection'][$i], $filiere);
 			 			
 			//si la requetes renvoie une réponse, on affiche les infos
 			if ($eleve != false)
 			{
 				echo '<tr><td>'.$eleve['Numero'].'</td><td>'.$eleve['Nom'].'</td><td>'.$eleve['Prénom'].'</td><td>'.$eleve['InfosDiplôme'].'</td>
				<td>'.$eleve['Moyenne'].'</td><td>'.$eleve['AvisDuCE'].'</td></tr>';
 			}
 			else
 			{
 				echo 'Erreur : Un ou Plusieurs élève non pas été trouvés !';
 			}
		 }
		echo '</table></center>';	
}



?>



<?php
require_once('fin.php')
?>