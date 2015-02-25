<?php
	header ('Content-type: text/html; charset=UTF-8');
	header ('Conternt-Type: texte/csv');
	error_reporting(E_ALL);
		
		require('fonctions.php');
		require_once('connexion.php');
$dossier = 'C:\wamp\www\PostBac/';
$fichier = basename($_FILES['fich']['name']);
$taille_maxi = 1000000;
$taille = filesize($_FILES['fich']['tmp_name']);
$extensions = array('.csv', '.CSV');
$extension = strrchr($_FILES['fich']['name'], '.'); 
//Début des vérifications de sécurité...
if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
{
     $erreur = 'Vous devez uploader un fichier de type csv';
}
if($taille>$taille_maxi)
{
     $erreur = 'Le fichier est trop gros...';
}
if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
{
     //On formate le nom du fichier ici...
     $fichier = strtr($fichier, 
          'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 
          'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
     $fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);
     if(move_uploaded_file($_FILES['fich']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
     {
          echo 'Upload effectué avec succès !';
		  
		  
		  $fichierOk = str_replace('.csv' ,'',$fichier);
		  echo $fichierOk;
		  $fileCsv1=$fichier;
		  
		  $fichierFA= get_array($fileCsv1);
		 
		  $query= createTable("$fichierOk", $fichierFA, $fichierFA[0][1]);
		  $requete=$bd->prepare($query);
		  $requete->execute();
		  
		  $insertion=insert($bd, "$fichierOk", $fichierFA);
		  
     }
     else //Sinon (la fonction renvoie FALSE).
     {
          echo 'Echec de l\'upload !';
     }
}
else
{
     echo $erreur;
}
?>