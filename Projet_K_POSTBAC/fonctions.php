<?php
//header ('Content-type: text/html; charset=UTF-8');
require('connexion.php'); //Permet la connexion à la base de données
ini_set('display_errors', 1); 
error_reporting(E_ALL); //Affiche toutes les erreurs









//---------------------------------------------------------------------------------//
//       Fonction pour l'intégration du fichier excel (converti en CSV)            //
//---------------------------------------------------------------------------------//											

//Fonction qui récupère un fichier CSV sous forme d'array 
function get_array($fileCsv) {

    if (($file = fopen($fileCsv, 'r')) !== FALSE){

      while (($line = fgetcsv($file,'',';')) !== FALSE) {

        $array_data[] = $line;

        }

    }

  fclose($file);

  return $array_data;

}

//Fonction qui prépare la requête CREATE TABLE avec la premiere ligne du fichier csv (titre) comme champs
//ATTENTION: IL FAUT QUE LES CHAMPS DE LA PREMIERE LIGNE DU FICHIER SOIENT CONCATENE 
function createTable($nomTable, $array_file, $primaryCles1){

	$req= 'CREATE TABLE if not exists '.$nomTable.' ( ';

	$i=0; 

	while ($i<sizeof($array_file[0])){

		if (is_numeric(str_replace ( ",", ".", $array_file[1][$i])))
		{
			if ($array_file[0][$i]=='Moyenne') // On force la colonne Moyenne a être un float
			{
				$req=$req.$array_file[0][$i].' FLOAT, ';
			}
			else
			{	
				$req=$req.$array_file[0][$i].' INTEGER, ';
			}
		}
	
		elseif(is_string(str_replace ( ",", ".", $array_file[1][$i])))
		{
			$req=$req.str_replace ( ",", ".", $array_file[0][$i]).' VARCHAR(50), ';
		}
		
		$i++;

	}

	//Définition des clés primaires 

	$req=$req.'PRIMARY KEY ('.$primaryCles1.'))';

	return $req;
}

// fonction qui prépare la requête d'insertion des fichier csv 
function prepareInsert($array_file, $nomTable, $line){

	if ($line>(count($array_file)-1))
	{
		return 'Erreur $line: cette ligne n\'existe pas';
	}

	$insert= 'INSERT INTO '.$nomTable.' VALUES( ';

	for($y=0; $y<count($array_file[0]);$y++)
	{
		if (is_numeric(str_replace ( ",", ".", $array_file[$line][$y]))==FALSE)
		{
			if($y==(count($array_file[0])-1))
			{
				if ($array_file[$line][$y]==NULL)
				{
					$insert=$insert.'NULL)';
				}
				else
				{
					if (strstr($array_file[$line][$y], "\"")) 
					{
						$chaine=str_replace ( "\"", " ' ", $array_file[$line][$y]);

						$insert=$insert."\"".$chaine."\"".' ) ';
					} 
					else
					{
						$insert=$insert."\"".$array_file[$line][$y]."\"".' ) ';			
					}
				}
			}
			else
			{
				if ($array_file[$line][$y]==NULL)
				{
					$insert=$insert.'NULL, ';
				}
				else
				{
					if (strstr($array_file[$line][$y], "\"")) 
					{
						$chaine=str_replace ( "\"", " ' ", $array_file[$line][$y]);

						$insert=$insert."\"".$chaine."\"".' , ';
					}
					else
					{
						$insert=$insert."\"".$array_file[$line][$y]."\"".', ';
					}
				}	
			}
		}
		else
		{
			if($y==(count($array_file[0])-1))
			{
				$insert=$insert.str_replace ( ",", ".", $array_file[$line][$y]).') ';
			}
			else
			{
				$insert=$insert.str_replace ( ",", ".", $array_file[$line][$y]).', ';
			}	
		}	
	}

	return $insert;
}

//Fonction qui insert toutes les lignes du fichier csv dans la base
function insert($bd, $nomTable, $array_file){

	for ($i=1;$i<count($array_file);$i++)
	{
		$insert=prepareInsert($array_file, $nomTable, $i);	

		$reqInsert = $bd->prepare($insert);

		$reqInsert->execute();
	}

	return TRUE;
}

//----------------------------------------------------------------------------------//
//       Fonction pour la créaction, la gestion et l'affichage des enseignants     //
//--------------------------------------------------------------------------------//	

//Creation de la table des enseignants 
function createTableID($bd){

	$req=$bd->prepare(' CREATE TABLE IF NOT EXISTS  identification  (
 `login` VARCHAR( 50 ) NOT NULL ,
 `nom` VARCHAR( 50 ) NOT NULL ,
 `prenom` VARCHAR( 50 ) NOT NULL ,
  `email` VARCHAR( 50 ) NOT NULL ,
  `mdp` VARCHAR( 50 ) NOT NULL ,
 `matiere` VARCHAR( 50 ) NOT NULL ,
 `admin` INTEGER NOT NULL,
  PRIMARY KEY (login))');
	$req->execute();
}
//createTableID($bd);
//Nombre d'enseignant enregister dans la base 
function nbEnseignant($bd){
	
	$query='SELECT COUNT( * ) FROM identification';
	$req=$bd->prepare($query);
	
	if($req->execute()){

		$rep = $req->fetch(PDO::FETCH_NUM);
		return $rep[0];

	} 
}

// Fonction qui genere des mots de passe aleatoirement
function generer_mot_de_passe($nb_caractere)
{
        $mot_de_passe = "";
       
        $chaine = "abcdefghjkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ023456789+@!$%?&";
        $longeur_chaine = strlen($chaine);
       
        for($i = 1; $i <= $nb_caractere; $i++)
        {
            $place_aleatoire = mt_rand(0,($longeur_chaine-1));
            $mot_de_passe .= $chaine[$place_aleatoire];
        }

        return $mot_de_passe;   
}

function generer_login($bd, $nom, $prenom)
{
	$login="";
	$login=$prenom[0].$nom;
	$tab=array();
	
	$query='SELECT login FROM identification where nom = :nom and prenom = :prenom';
	$req=$bd->prepare($query, array(PDO::ATTR_CURSOR, PDO::CURSOR_SCROLL));
    $req->bindValue('nom', $nom);
	$req->bindValue('prenom', $prenom);
	$req->execute();
	$nbreq = $req->rowCount();
	
	$rep = $req->fetchAll(PDO::FETCH_COLUMN,0);

	for($i=0; $i<count($rep) ; $i++){
		$tab[$i]=$rep[$i];
	}
	
	if($nbreq!=0){
		// Login deja existant dans la bdd
		$max = count($rep)-1;
		$derniercaractere = substr($tab[$max],-1);
		if(is_numeric($derniercaractere))
			// Dernier caractere est un nombre on incremente
		{
			$login.=$derniercaractere+1;
			return $login;
		}
		
		else
		{
			// Sinon on cree le login tout simplement

			$login=$login.'1';
			return $login;
			
		}	
			
	}
	
	else
		// Login pas dans la base. 1ere creation
	{
		return $login;
	}
	
}
//Insertion des données de l'enseignant a partir du formulaire 
function insertDataEnseignants($bd){

	$mdp=generer_mot_de_passe(8);
    if(isset($_GET['nom']) && trim($_GET['nom']!=NULL) && isset($_GET['prenom']) && trim($_GET['prenom']!=NULL) && isset($_GET['matiere']) && trim($_GET['matiere']!=NULL) && isset($_GET['email']) && trim($_GET['email']))
    {

		$login = generer_login($bd, $_GET['nom'], $_GET['prenom']);
		$derniercaractere = substr($login,-1);
        $query='INSERT INTO identification VALUE ( :login, :nom, :prenom, :email ,:mdp, :matiere, 0)';
        $req=$bd->prepare($query);
        $req->bindValue('login', $login);
        $req->bindValue(':nom', $_GET['nom']);
        $req->bindValue(':prenom', $_GET['prenom']);

        $req->bindValue(':email', $_GET['email']);

        $req->bindValue(':mdp', $mdp);
        $req->bindValue(':matiere', $_GET['matiere']);
       
        if($req->execute())
        {
        	//mail( $_GET['email'], 'Identifiant et Mot de passe PostBac', 'le message', null, 'karine.ouldbraham@gmail.com');
        	echo '<center><div style="margin-left: auto; margin-right: auto; width: 28%; "><p style="color:red;"><strong>'. $_GET['nom'] .' '. $_GET['prenom'] .' à été enregistré !</strong></p></div></center>';
        };
    }
}

function majMdpEnseignant($bd){
	// A finir
	if(isset($_POST['mdp_actuel']) && trim($_POST['mdp_actuel']!=NULL) && isset($_POST['mdp_new']) && trim($_POST['mdp_new']!=NULL))
	{
		$query='UPDATE identification SET mdp = :mdp_new WHERE login = :login and mdp = :mdp_actuel';
		$req=$bd->prepare($query);
		$req->bindValue('login',$_SESSION['name']);
		$req->bindValue('mdp_actuel',$_POST['mdp_actuel']);
		$req->bindValue('mdp_new', $_POST['mdp_new']);
	}
}

function MajEmailEnseignant($bd){
	
	

}

// Récupération des enseignant dans la table et affichage du tableau 
function afficheProf($bd){

	$query="SELECT * FROM identification WHERE admin=0";
	$req=$bd->prepare($query);
	$req->execute();
	
	//Affiche le tableau des profs en fonction de si l'utilisateur est administrateur 
	if ($_SESSION['admin']==1){

		echo '
		<table class="pure-table" border="1" CELLPADDING="20" style="width: 57%;">
		<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
		<tr class="pure-table-odd">
		<th>Nom</th>
		<th>Prénom</th>
		<th>Matière</th>
		<th>Modifier</th>
		<th>Supprimer</th>
		</tr>';

		while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
		{
			echo '
			<form method="post" action="contenu.php">
			<tr>
			<td style="text-align:center;" id="nom">'.$tmp1['nom'].'</td>
			<td style="text-align:center;">'.$tmp1['prenom'].'</td>
			<td style="text-align:center;">'.$tmp1['matiere'].'</td>
			<td style="text-align:center;"><a href="modifProf.php class="modifier"><i style="padding-left:2em;" class="fa fa-file-o"></i></a></td>
			<td style="text-align:center;"><INPUT type="image" src="effacer.png" ></td>
			</tr>
			<input type="hidden" name="supProf" value="'.$tmp1['login'].'"/>
			</form>';

		}
		echo '</table>';
	}
	else 
	{
		echo '
		<table class="pure-table" border="1" CELLPADDING="20" style="width: 57%;">
		<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
		<tr class="pure-table-odd">
		<th>Nom</th>
		<th>Prénom</th>
		<th>Matière</th>
		</tr>';

		while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
		{
			echo '
			<tr>
			<td style="text-align:center;">'.$tmp1['nom'].'</td> // id
			<td style="text-align:center;">'.$tmp1['prenom'].'</td>
			<td style="text-align:center;">'.$tmp1['matiere'].'</td>
			</tr>';
		}
		echo '</table>';
	}
}

//Fonction qui supprime un enseignant dont le login est passé en paramètre
function supprimeProf($bd, $loginProf){

	$query="DELETE FROM identification WHERE login=:login ";
	$req=$bd->prepare($query);
	$req->bindValue('login',$loginProf);
	$req->execute();

}
//supprimeProf($bd, "GTandu2");

function afficheEleve($f,$bd)//Affiche les eleves en fonction de $f (les boutons Alternance/initiale au-dessus de la liste )
{							 // avec une checkbox avec comme valeur le num de l'eleve
	if ($_SESSION['admin']==1){
		
		
		echo '<form action="dossierATraiter.php" method = "post">
		<center><input style="margin-top:2em; padding-left:2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" value="Attribuer"/></center>';
		

		echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="15" style="width: 57%;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr><th>Nom</th><th>Prénom</th><th>Numero</th><th>Bac</th><th>Moyenne</th><th>BonusMalus</th><th>AvisCE</th><th>Selectionner</th></tr> ';

		if ($f == 'fi')
		{
			$req = $bd->prepare('select * from AtraiterFI');
			$req->execute();
		}
		else
		{
			$req = $bd->prepare('select * from AtraiterFA');
			$req->execute();
			
		}

		
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
			<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
			'</td><td><input type="checkbox" name="selection[]" value="'.$rep['Numero'].'"/></td></tr>';
		}
		
		echo "</form></table></center>";


	}
	else{

		echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="15" style="width: 57%;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr><th>Nom</th> <th>Prénom</th><th>Numero</th><th>Bac</th><th>Moyenne</th><th>BonusMalus</th><th>AvisCE</th>';
 		echo '</div>';

		if ($f == 'fi')
		{
			$req = $bd->prepare('select * from AtraiterFI');
			$req->execute();
		}
		else
		{
			$req = $bd->prepare('select * from AtraiterFA');
			$req->execute();
			
		}
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
			<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
			'</td></tr>';
		}

		echo "</table></center>";
	}

}

//----------------------------------------------------------------------------------//
//       Fonction pour la créaction, la gestion et l'affichage des enseignants     //
//--------------------------------------------------------------------------------//	

//Création des tables des etudiant FA et FI avec leurs moyennes
function tableEtudiantAvecMoyenne($bd){

	/////////////////////CAS FA/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('CREATE TABLE IF NOT EXISTS EtudiantFA AS 
	
			SELECT  MoyenneFA.RangProvisoire, MoyenneFA.Numero, MoyenneFA.Nom, MoyenneFA.Prénom, MoyenneFA.InfosDiplôme, fichierFA.Spécialité,
			
			MoyenneFA.Moyenne, MoyenneFA.NombreDeBonusMalusAppliqués, fichierFA.AvisDuCE

			FROM MoyenneFA, fichierFA WHERE MoyenneFA.Numero = fichierFA.Numero ORDER BY Moyenne DESC ');
	$req->execute();

	$req = $bd->prepare('ALTER TABLE EtudiantFA ADD PRIMARY KEY (Numero)');
	$req->execute();
	echo 'EtudiantFA TABLE CREATE';

	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('CREATE TABLE IF NOT EXISTS EtudiantFI AS 
	
			SELECT  MoyenneFI.RangProvisoire, MoyenneFI.Numero, MoyenneFI.Nom, MoyenneFI.Prénom, MoyenneFI.InfosDiplôme, fichierFI.Spécialité,
			
			MoyenneFI.Moyenne, MoyenneFI.NombreDeBonusMalusAppliqués, fichierFI.AvisDuCE

			FROM MoyenneFI, fichierFI WHERE MoyenneFI.Numero = fichierFI.Numero ORDER BY Moyenne DESC ');
	$req->execute();

	$req = $bd->prepare('ALTER TABLE EtudiantFI ADD PRIMARY KEY (Numero)');
	$req->execute();
	echo 'EtudiantFI TABLE CREATE';

}
//tableEtudiantAvecMoyenne($bd);


//On créer des vues sur le élèves sélectionné au premier tour
function eleveSelectionner($bd){
	/////////////////////CAS FA/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('Select count(*) from EtudiantFA');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4
	
	$req = $bd->prepare('CREATE VIEW SelectionneFA (RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE)
						AS SELECT 
						RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFA ORDER BY Moyenne DESC LIMIT '.$calcul);//on insere ds la nouvelle table les eleves du 1° quart qui sont admis d'office
	$req->execute();
	echo 'SelectionneFA VIEW CREATE';
	
	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////
	
	$req = $bd->prepare('Select count(*) from EtudiantFI');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4

	$req = $bd->prepare('CREATE VIEW SelectionneFI (RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE)
						AS SELECT 
						RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFI ORDER BY Moyenne DESC LIMIT '.$calcul);//on insere ds la nouvelle table les eleves du 1° quart qui sont admis d'office
	$req->execute();
	echo 'SelectionneFI VIEW CREATE';

}
//eleveSelectionner($bd);

//Génére les differentes tables et vues pour le traitement des élèves
function eleveATraiter($bd)
{	
	
	/////////////////////CAS FA/////////////////////
	//											  //
	////////////////////////////////////////////////

	$req = $bd->prepare('Select count(*) from EtudiantFA');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	 $calcul = $rep[0]/4;
	 $req = $bd->prepare('CREATE TABLE IF NOT EXISTS AtraiterFA  
	 					  AS SELECT  
	 					  RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFA 
	 					  ORDER BY Moyenne DESC LIMIT :limite, :offset');

	 $req->bindValue(':limite', $calcul, PDO::PARAM_INT);
	 $calcul = $calcul*3;
	 $req->bindValue(':offset', $calcul, PDO::PARAM_INT);
	 $req->execute();

	 $req = $bd->prepare('ALTER TABLE AtraiterFA ADD PRIMARY KEY (Numero)');
	 $req->execute();
	  echo 'AtraiterFA TABLE CREATE';
	
	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////
	
	$req = $bd->prepare('Select count(*) from EtudiantFI');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4

	$req = $bd->prepare('CREATE TABLE IF NOT EXISTS AtraiterFI  
	 					  AS SELECT  
	 					  RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFI 
	 					  ORDER BY Moyenne DESC LIMIT :limite, :offset');

	 $req->bindValue(':limite', $calcul, PDO::PARAM_INT);
	 $calcul = $calcul*3;
	 $req->bindValue(':offset', $calcul, PDO::PARAM_INT);
	 $req->execute();

	 $req = $bd->prepare('ALTER TABLE AtraiterFI ADD PRIMARY KEY (Numero)');
	 $req->execute();
	 echo 'AtraiterFI TABLE CREATE';
	
}
//eleveATraiter($bd);

//On créer une vue pour les élèves qui ont postulé dans les deux filieres (438)
function elevePostuleFAFI($bd)
{
	$req = $bd->prepare('CREATE VIEW EtudiantFIFA (Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE) 
						AS SELECT EtudiantFI.Numero, EtudiantFI.Nom, EtudiantFI.Prénom, EtudiantFI.Moyenne, EtudiantFI.InfosDiplôme, EtudiantFI.Spécialité, EtudiantFI.NombreDeBonusMalusAppliqués, EtudiantFI.AvisDuCE
						FROM EtudiantFI, EtudiantFA
						WHERE EtudiantFI.Numero = EtudiantFA.Numero
						ORDER BY Moyenne DESC');
	$req->execute();
}
//elevePostuleFAFI($bd);

function supprimerNULL($bd){

	$req = $bd->prepare("DELETE FROM AtraiterFA WHERE Moyenne IS NULL");

	$req->execute();

	$req = $bd->prepare("DELETE FROM AtraiterFI WHERE Moyenne IS NULL");

	$req->execute();

}
//supprimerNULL($bd);

function bonusMalusTotal($bd, $ine, $bn) //applique les bonus/malus aux eleves ON A BESOIN DU FORMULAIRE BORDEL DE MERDE!!!!!!
{
	$req = $bd->prepare('UPDATE m SET Moyenne = Moyenne+ :bn  WHERE Numero = :ine');//On icrémente la moyenne de l'eleve de la valeur de bn
	$req->bindValue(':bn', $bn);
	$req->bindValue(':ine', $ine);
	$req->execute();
}

/*liste des fonction



->fonctions des malus automatiques

->fonctions des bonus automatiques



->Affiche touts les etudiants


*/





?>

