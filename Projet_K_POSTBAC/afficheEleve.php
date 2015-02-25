<?php
	session_start();
	require_once('connexion.php');
	require('debut.php');
	require('menu.php');

?>


		<h3>Voici la liste complete des eleves</h3>
		

		<p id='textAccueil'> <strong>Cet espace permet la gestion des différents dossiers des candidats</strong></p> 

<?php


$query="SELECT * FROM MoyenneFI";
$req=$bd->prepare($query);
$req->execute();

$rep = $req->fetch(PDO::FETCH_ASSOC);

if (empty($rep)){
echo '
<table class="pure-table-horizontal" border="1" CELLPADDING="15" style="margin-left: 15%; margin-top: 3%; width: 57%;">
<CAPTION style="padding: 2em;"> <strong>LISTE DES ELEVES</strong> </CAPTION>
<tr>
<th>Numero</th>
<th>Nom</th>
<th>Prénom</th>
<th>Moyenne</th>
<th>Selectionner</th>
</tr>';
}
else{
//pb ne renvoie que la premiere ligne 
$tmp= array_keys($rep);

echo '
<table class="pure-table" border="1" CELLPADDING="15" style="margin-left: 15%; margin-top: 3%; width: 57%;">
<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
<tr>
<th>Numero</th>
<th>Nom</th>
<th>Prénom</th>
<th>Moyenne</th>
<th>Selectionner</th>

</tr>';

	while($tmp1=$req->fetch(PDO::FETCH_ASSOC)){

		echo '
		<tr >
		<td style="text-align:center;">'.$tmp1['Numero'].'</td>
		<td style="text-align:center;">'.$tmp1['Nom'].'</td>
		<td style="text-align:center;">'.$tmp1['Prénom'].'</td>
		<td style="text-align:center;">'.$tmp1['Moyenne'].'</td>
		<td style="text-align:center;"><form><input type="checkbox"></td>
		
		</tr>';

	}

	echo '</table>';

}
?>
	






<?php
	require('fin.php'); 
?>