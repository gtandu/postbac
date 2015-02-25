<?php 

/** Afficher toutes les erreurs */
header ('Content-type: text/html; charset=UTF-8');
header ('Conternt-Type: texte/csv');
error_reporting(E_ALL);
require('connexion.php'); //Permet la connexion à la base de données
require_once('debut.php'); //En-tête html
require_once('fonctions.php');



//Fichiers à intégrer
$fileCsv1='fichierFA.csv';
$fileCsv2='fichierFI.csv';
$fileCsv3='MoyenneFI.csv';
$fileCsv4='MoyenneFA.csv';
//$fileCsv5='BonusMalus.csv';

//Récupération du fichier CSV sous forme d'array
$fichierFA= get_array($fileCsv1);
$fichierFI= get_array($fileCsv2);
$MoyenneFI=get_array($fileCsv3);
$MoyenneFA=get_array($fileCsv4);
//$BonusMalus=get_array($fileCsv5);
//print_r($MoyenneFI);
// print_r($MoyenneFA);

//Definition des caractère de la base de données en UTF8
//mysql_query("SET NAMES UTF8");

//Préparation de la requete pour crée la table en paramètre indiquer les primary keys
$query= createTable('fichierFA', $fichierFA, $fichierFA[0][1]);
$query2= createTable('fichierFI', $fichierFI, $fichierFA[0][1]);
$query3=createTable('MoyenneFI',$MoyenneFI, $MoyenneFI[0][1]);
echo $query3;
$query4=createTable('MoyenneFA',$MoyenneFA, $MoyenneFA[0][1]);
//$query5=createTable('BonusMalus',$BonusMalus, $BonusMalus[0][1]);

// creation de la table fichier
$requete=$bd->prepare($query);
$requete2=$bd->prepare($query2);
$requete3=$bd->prepare($query3);
$requete4=$bd->prepare($query4);
//$requete5=$bd->prepare($query5);
$requete->execute();
$requete2->execute();
$requete3->execute();
$requete4->execute();
//$requete5->execute();

// Test de la fonction prepareInser($array_file, $line)--> Utilisation seulement pour les tests
//$prepare=prepareInsert($test,1651);
//echo $prepare;
//$reqInsert=$bd->prepare($prepare);
//$reqInsert->execute();


// Insertion de toutes les lignes du fichier dans la base avec la fonction insert($bd,$array_file)
$insertion=insert($bd, 'fichierFA', $fichierFA);
$insertion2=insert($bd,'fichierFI',$fichierFI);
$insertion3=insert($bd, 'MoyenneFI',$MoyenneFI);
$insertion4=insert($bd, 'MoyenneFA',$MoyenneFA);
//$insertion5=insert($bd, 'BonusMalus',$BonusMalus);

//Test si tous les fichiers sont inséré dans la base retourne 1 si c'est bon 
echo $insertion;
echo $insertion2;
echo $insertion3;
echo $insertion4;
//echo $insertion5;



tableEtudiantAvecMoyenne($bd);
eleveSelectionner($bd);
eleveATraiter($bd);
elevePostuleFAFI($bd);
 





require_once('fin.php');
?>
