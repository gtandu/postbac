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

	//On ouvre le fichier csv
    if (($file = fopen($fileCsv, 'r')) !== FALSE){

    	//On enregistre les lignes dans un array
      while (($line = fgetcsv($file,'',';')) !== FALSE) {

        $array_data[] = $line;

        }

    }

  fclose($file);
  //On retourne le tableau du fichier csv 
  return $array_data;

}

//Fonction qui prépare la requête CREATE TABLE avec la premiere ligne du fichier csv (titre) comme champs
//ATTENTION: IL FAUT QUE LES CHAMPS DE LA PREMIERE LIGNE DU FICHIER SOIENT CONCATENE 
function createTable($nomTable, $array_file, $primaryCles1){

	$req= 'CREATE TABLE if not exists '.htmlentities($nomTable).' ( ';

	$i=0; 

	//Décomposition du fichier csv
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

	$req=$req.'PRIMARY KEY ('.htmlentities($primaryCles1).')';

	return $req;
}

// fonction qui prépare la requête d'insertion des fichier csv 
function prepareInsert($array_file, $nomTable, $line){


	//On vérifie que la ligne existe

	if ($line>(count($array_file)-1))
	{
		return 'Erreur $line: cette ligne n\'existe pas';
	}



	$insert= 'INSERT INTO '.$nomTable.' VALUES( ';

	//On prepare la requete d'insertion

	$insert= 'INSERT INTO '.htmlentities($nomTable).' VALUES( ';



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


	//on retourne la requête d'insertion pour une ligne du tableau 

	return $insert;

}

//Fonction qui insert toutes les lignes du fichier csv dans la base
function insert($bd, $nomTable, $array_file){

	//On prepare la requete de chaque ligne du tableau et on l'execute
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
 	`cle` VARCHAR ( 32 ),
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

        return htmlentities($mot_de_passe);   
}

// Fonction qui genere un login en fonction du nom et du prenom et evite les doublons a la creation
function generer_login($bd, $nom, $prenom)
{
	$login="";
	$login=$prenom[0].$nom;
	$tab=array();
    $dizaine=0;
	
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
		$max = count($rep);
		if($max>1)
			// Dernier caractere est un nombre on incremente
		{
                return $login.$max;
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
		return htmlentities($login);
	}
	
}
//Insertion des données de l'enseignant a partir du formulaire 
function insertDataEnseignants($bd){

	$mdp=generer_mot_de_passe(8);
	//On verifie que tous les champs on été saisie 
    if(isset($_GET['nom']) && trim($_GET['nom']!=NULL) && isset($_GET['prenom']) && trim($_GET['prenom']!=NULL) && isset($_GET['matiere']) && trim($_GET['matiere']!=NULL) && isset($_GET['email']) && trim($_GET['email']))
    {
    	//On genere un mot de passe
		$login = generer_login($bd, htmlentities($_GET['nom']), htmlentities($_GET['prenom']));
		$derniercaractere = substr($login,-1);

		//on prépare la requete
        $query='INSERT INTO identification VALUE ( :login, :nom, :prenom, :email ,:mdp, :matiere, 0, 0)';
        $req=$bd->prepare($query);
        $req->bindValue('login', $login);
        $req->bindValue(':nom', htmlentities($_GET['nom']));
        $req->bindValue(':prenom', htmlentities($_GET['prenom']));

        $req->bindValue(':email', htmlentities($_GET['email']));

        $req->bindValue(':mdp', $mdp);
        $req->bindValue(':matiere', htmlentities($_GET['matiere']));
       
       	//si la requete a été executé, on envoi un mail récapitulatif
        if($req->execute())
        {

        	mail( htmlentities($_GET['email']), 'Identifiant et Mot de passe PostBac', 'le message', null, 'tbrandon91@hotmail.fr');

        	echo '<center><div style="margin-left: auto; margin-right: auto; width: 28%; "><p style="color:red;"><strong>'. htmlentities($_GET['nom']) .' '. htmlentities($_GET['prenom']) .' à été enregistré !</strong></p></div></center>';

        };
    }
}
 
function modifDataEnseignants($bd){
//Modification des données de l'enseigant à partir du formulaire modifProf
	
	//On verifie que les champs du formulaire on bien été saisie 
    if(isset($_POST['nom']) && trim($_POST['nom']!=NULL) && isset($_POST['prenom']) && trim($_POST['prenom']!=NULL) && isset($_POST['matiere']) && trim($_POST['matiere']!=NULL) && isset($_POST['email']) && trim($_POST['email']))
    {

		
		//on prepare la requete 
        $query='UPDATE identification SET nom=:nom, prenom=:prenom, email=:email, matiere=:matiere WHERE login=:login  ';
        $req=$bd->prepare($query);
        $req->bindValue('login', htmlentities($_POST['login']));
        $req->bindValue(':nom', htmlentities($_POST['nom']));
        $req->bindValue(':prenom', htmlentities($_POST['prenom']));
		$req->bindValue(':email', htmlentities($_POST['email']));
		$req->bindValue(':matiere', htmlentities($_POST['matiere']));
       
       //si la requete est éxécuté on affiche un message 
        if($req->execute())
        {
        	
        	echo '<center><div style="margin-left: auto; margin-right: auto; width: 28%; "><p style="color:red;"><strong>'. htmlentities($_POST['nom']) .' '. htmlentities($_POST['prenom']) .' à bien été moddifier !</strong></p></div></center>';
        };
    }
}


// Met a jour le mot de passe d'un enseignant sur sa page de profil
function majMdpEnseignant($bd){
	
	if(isset($_POST['mdp_actuel']) && trim($_POST['mdp_actuel']!=NULL) && isset($_POST['new_mdp']) && trim($_POST['new_mdp']!=NULL))
	{
		$query='UPDATE identification SET mdp =:new_mdp WHERE login =:login and mdp = :mdp_actif';
		
		$req=$bd->prepare($query);
		$req->bindValue('login',$_SESSION['name']);
		$req->bindValue('mdp_actif',htmlentities($_POST['mdp_actuel']));
		$req->bindValue('new_mdp',htmlentities($_POST['new_mdp']));
		$req->execute();
        
		if($req->rowCount()==1)
		{
			return $msg="Mot de passe changé !";
		}
		
		else
		{
			return $msg="Erreur mot de passe actuel";
		}
		
	}
}

function MajEmailEnseignant($bd){
	
	if(isset($_POST['adresse_mail_actif'])&& trim($_POST['adresse_mail_actif']!=NULL) && isset($_POST['mdp_actif']) && trim($_POST['mdp_actif']!=NULL) && isset($_POST['new_adresse_mail']) && trim($_POST['new_adresse_mail']!=NULL) )
	{
		$query='Select login from identification WHERE login =:login and mdp = :mdp_actif and email= :mail_actif';
		
		$req=$bd->prepare($query);
		$req->bindValue('login',$_SESSION['name']);
		$req->bindValue('mdp_actif',htmlentities($_POST['mdp_actif']));
		$req->bindValue('mail_actif', htmlentities($_POST['adresse_mail_actif']));
		$req->execute();
		
		if($req->rowCount()==1)
		{
			$cle = md5(microtime(TRUE)*100000);
			
			// Insertion de la clé dans la base de données (à adapter en INSERT si besoin)
			$req = $bd->prepare("UPDATE identification SET cle = :cle where login = :login ");
			$req->bindParam(':cle', $cle);
			$req->bindParam(':login', $_SESSION['name']);
			$req->execute();
			
			// Préparation du mail contenant le lien d'activation
			$login = $_SESSION['name'];
			$destinataire = htmlentities($_POST['new_adresse_mail']);
			$sujet = "Activer votre compte" ;
			$entete = "From: changementemail@postbac.com" ;
			 
			// Le lien d'activation est composé du login(log), de la clé(cle) et du mail
			// Adresse d'activation a adapter !
			$message = "Bienvenue sur Postbac,
			 
			Pour confirmer le changement de votre adresse mail, veuillez cliquer sur le lien ci dessous ou copier/coller dans votre navigateur internet.
			
			http://localhost/postbac/Projet_K_POSTBAC/validationMail.php?log=$login&mail=$destinataire&cle=$cle
			 
			 
			---------------
			Ceci est un mail automatique, Merci de ne pas y répondre.";
 
			mail($destinataire, $sujet, $message, $entete) ; // Envoi du mail
			
			return $msg="Mail de confirmation envoyé a votre nouvelle adresse!";
		}
		
		else
		{
			return $msg="Erreur mot de passe actuel";
		}
		
	}		

}

// Récupération des enseignant dans la table et affichage du tableau 
function afficheProf($bd){

	//on récupere les enseignants dans la base 
	$query="SELECT * FROM identification WHERE admin=0";
	$req=$bd->prepare($query);
	$req->execute();
	
	//Affiche le tableau des profs en fonction de si l'utilisateur est administrateur 
	if ($_SESSION['admin']==1){

		echo '
		<table class="pure-table" border="1" CELLPADDING="20" style="width: 57%;">
		<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
		<tr class="pure-table-odd" style= "background-color:#F0F0F0;">
		<th>Nom</th>
		<th>Prénom</th>
		<th>Matière</th>
		<th>Candidats attribués</th>
		<th>Modifier</th>
		<th>Supprimer</th>
		</tr>';

		while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
		{
			// on compte le nombre de candidats correspondant à l'enseignant
			$nbCandidats=countCandidatProf($bd, $tmp1['login']);
			echo '<tr>
			<td style="text-align:center;" id="nom">'.$tmp1['nom'].'</td>
			<td style="text-align:center;">'.$tmp1['prenom'].'</td>
			<td style="text-align:center;">'.$tmp1['matiere'].'</td>
			<td style="text-align:center;">'.$nbCandidats.'</td>
			<td style="text-align:center;"><form method="post" action="modifProf.php"><INPUT type="image" src="modifier.png" >
				<input type="hidden" name="modif" value="'.$tmp1['login'].'"/></form></td>
			<td style="text-align:center;"><form method="post" action="contenu.php"><INPUT type="image" src="effacer.png" >
				<input type="hidden" name="supProf" value="'.$tmp1['login'].'"/></form></td>
			</tr>';

		}
		echo '</table>';
	}
	else 
	{
		echo '
		<table class="pure-table" border="1" CELLPADDING="20" style="width: 57%;">
		<CAPTION style="padding: 2em;"> <strong>LISTE DES ENSEIGNANTS</strong> </CAPTION>
		<tr class="pure-table-odd" style= "background-color:#F0F0F0;">
		<th>Nom</th>
		<th>Prénom</th>
		<th>Matière</th>
		<th>Candidats attribués</th>
		</tr>';

		while($tmp1=$req->fetch(PDO::FETCH_ASSOC))
		{
			// on compte le nombre de candidats correspondant à l'enseignant
			$nbCandidats=countCandidatProf($bd, $tmp1['login']);
			echo '
			<tr>
			<td style="text-align:center;">'.$tmp1['nom'].'</td>
			<td style="text-align:center;">'.$tmp1['prenom'].'</td>
			<td style="text-align:center;">'.$tmp1['matiere'].'</td>
			<td style="text-align:center;">'.$nbCandidats.'</td>
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

// fonction qui renvoie le nombre de candidats attribué à un enseignant donnée ($prof)
function countCandidatProf($bd, $prof){

	//On recupere les élève FI qui ont un enseignant attribué
	$query="SELECT count(*) FROM EtudiantFI WHERE enseignant =  :prof";
	$req=$bd->prepare($query);
	$req->bindValue('prof',$prof);
	$req->execute();
	$rep = $req->fetch(PDO::FETCH_ASSOC);

	// print_r($rep);

	//On recupere les élève FA qui ont un enseignant attribué
	$query2="SELECT count(*) FROM EtudiantFA WHERE enseignant =  :prof";
	$req2=$bd->prepare($query2);
	$req2->bindValue('prof',$prof);
	$req2->execute();
	$rep2 = $req2->fetch(PDO::FETCH_ASSOC);

	// print_r($rep2);

	return $rep['count(*)']+$rep2['count(*)'];

}
// $test=countCandidatProf($bd, 'KOuld');


//----------------------------------------------------------------------------------//
//       Fonction pour la gestion et l'affichage des étudiants                     //
//--------------------------------------------------------------------------------//

//fonction qui retourne True si l'étudiant ($num) a postulé dans les deux filière
function postule2filiere($bd,$num){
	
	//On recupere les etudiants qui postule pour les deux filiere
	$query="SELECT Numero FROM EtudiantFIFA";
	$req=$bd->prepare($query);
	$req->execute();	

	//on verifie si l'étudiant passé en parametre existe dans EtudiantFIFA
	while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			//si oui, 
			if ($num == $rep['Numero'])
			{
				return 'OUI';
			}


		}
		return 'NON';

}

//Ajoute une colonne pour l'identifiant de l'enseignant qui se chargera du dossier dans les fichiers Etudiants 
//retourn TRUE : colonne déja ajouté, FALSE sinon
function ajoutColonneEnseignant($bd){

	//Vérifie si la colonne 'enseignant' existe dans la table EtudiantFA
	$query="SHOW COLUMNS FROM EtudiantFA LIKE 'enseignant'";
	$req=$bd->prepare($query);
	$req->execute();

	$query2="SHOW COLUMNS FROM EtudiantFI LIKE 'enseignant'";
	$req2=$bd->prepare($query2);
	$req2->execute();

	$return=FALSE;
	
	if ($req->fetch(PDO::FETCH_ASSOC) == false)
	{
		$query="ALTER TABLE EtudiantFA ADD enseignant VARCHAR(255)";
		$req=$bd->prepare($query);
		$req->execute();
	}
	else{
		$return=TRUE; 
	}

	if ($req2->fetch(PDO::FETCH_ASSOC) == false)
	{
		$query="ALTER TABLE EtudiantFI ADD enseignant VARCHAR(255)";
		$req3=$bd->prepare($query);
		$req3->execute();
	}
	else{
		$return=TRUE;
	}

	return $return;
	
}
// $test = ajoutColonneEnseignant($bd);
// echo $test;


//Fonction qui ajoute le login($prof) d'un enseigant à un candidat($id) dans la colonne prévu à cet effet(enseignant) dans la table etudiant passé en paramètre($filiere) 
function ajoutLoginEnseignant($bd, $prof, $filiere, $id){

	$colonne=ajoutColonneEnseignant($bd);

	if ($colonne !== FALSE){

		// On prépare la requete qui va insérer l'id de l'enseignant
		$query="UPDATE ".$filiere." SET enseignant = :prof WHERE Numero = :id";
		$req=$bd->prepare($query);
		$req->bindValue('prof',$prof);
		$req->bindValue('id',$id);
		$req->execute();

		return TRUE;
	}	
}
// ajoutLoginEnseignant($bd, 'test', 'EtudiantFA', 646617);

//Renvoi les informations de l'étudiant qui est passé en paramètre ($id)
function searchEleve($bd, $id, $fi){

	$query="SELECT Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, AvisDuCE FROM ".$fi." WHERE Numero = :id ";
	$req=$bd->prepare($query);
	$req->bindValue('id',$id);
	$req->execute();
	return $req->fetch(PDO::FETCH_ASSOC);
}
// $test = searchEleve($bd, 646617, 'AtraiterFA');
// print_r($test);

function pagination($nombreDePages, $pageActuelle, $f)
{
	echo '<p align="center">Page : '; //Pour l'affichage, on centre la liste des pages
	for($i=1; $i<=$nombreDePages; $i++) //On fait notre boucle
	{
		 //On va faire notre condition
		 if($i==$pageActuelle) //Si il s'agit de la page actuelle...
		 {
			 echo ' [ '.$i.' ] '; 
			 
		 }	
		 else //Sinon...
		 {
			if($f == 'fi')
			{
				echo ' <a href="liste.php?fi=Filière+initiale&page='.$i.'">'.$i.'</a> ';
			}
			 
			else{
				echo ' <a href="liste.php?fi=Filière+alternance&page='.$i.'">'.$i.'</a> ';
			}
			 
			  
		 }
	}
}
	
function afficheEleve($f,$bd)//Affiche les eleves en fonction de $f (les boutons Alternance/initiale au-dessus de la liste )
{
	
	
	
	
	// avec une checkbox avec comme valeur le num de l'eleve
	if ($_SESSION['admin']==1){
		
		
		
		echo '<form action="dossierATraiter.php" method = "post" >
		<center><input style="margin-top:2em; padding-left:2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" value="Attribuer"/><br/></center><br/>';

		//chekbox qui permet de cocher tous les chekbox avec le nom selection 
		

		// echo' Tout (dé)cocher <input onclick="CocheTout(this, \'selection[]\');" type="checkbox"><br/>';

		echo' Tout (dé)cocher <input onclick="CocheTout(this, \'selection[]\');" type="checkbox"><br/>';
		
		
		// Si le on choisit filiere initiale
		if ($f == 'fi')
		{
			$nb_lignes_total= $bd->prepare('SELECT COUNT(*) AS total FROM AtraiterFI'); //Nous récupérons le contenu de la requête dans $retour_total, requete differentes dans chaque cas
			$nb_lignes_total->execute();
			while($donnees_total=$nb_lignes_total->fetch(PDO::FETCH_ASSOC))
			{
				$total = $donnees_total['total']; //On récupère le total pour le placer dans la variable $total.
			}
			
			$messagesParPage=25; //Nous allons afficher 5 messages par page.

			//Nous allons maintenant compter le nombre de pages.
			$nombreDePages=ceil($total/$messagesParPage);
			
			
			if(isset($_GET['page'])) // Si la variable $_GET['page'] existe...
			{
				$pageActuelle=intval($_GET['page']);
			 
				if($pageActuelle>$nombreDePages) // Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
				{
					$pageActuelle=$nombreDePages;
				}
			}
			
			else // Sinon
			{
				$pageActuelle=1; // La page actuelle est la n°1    
			}

			$premiereEntree=($pageActuelle-1)*$messagesParPage; // On calcul la première entrée à lire
			
			pagination($nombreDePages, $pageActuelle, $f);
				
			echo '<center><table  class="pure-table-horizontal" border="1" CELLPADDING="10" style="width: 40% id=trier;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
		<thead>
 		<tr style="background-color:#F0F0F0;">
			<th><div class=arrow2>Nom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th><div class=arrow2>Prenom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>Numero</th>
			<th><div class=arrow2>Bac</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th><div class=arrow2>Moyenne</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>BonusMalus</th>
			<th>AvisCE</th>
			<th>Selectionner</th>
		</tr> </thead><tbody>';
			
 
		 
		
		////////////////////////////////////
		echo '</p>';
			$req = $bd->prepare('select * from AtraiterFI order by moyenne DESC LIMIT '.$premiereEntree.', '.$messagesParPage.'');
			$req->execute();
			
			//Permet de récuperer le nom de la filiere dans la variable POST
			echo '<input type="hidden" name="filiere" value="'.$f.'"/>';

			while($rep = $req->fetch(PDO::FETCH_ASSOC))
			{	
				$filiere=postule2filiere($bd,$rep['Numero']);
				if ($filiere == 'NON'){
					echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
					<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
					'</td><td><input type="checkbox" name="selection[]" value="'.$rep['Numero'].'"/></td></tr>';
				}
				
			}
			
			
			
		}
		
		
		// Choisit filiere alternance
		else
		{
			$nb_lignes_total= $bd->prepare('SELECT COUNT(*) AS total FROM AtraiterFA'); //Nous récupérons le contenu de la requête dans $retour_total, requete differentes dans chaque cas
			$nb_lignes_total->execute();
			while($donnees_total=$nb_lignes_total->fetch(PDO::FETCH_ASSOC))
			{
				$total = $donnees_total['total']; //On récupère le total pour le placer dans la variable $total.
			}
			
			$messagesParPage=25; //Nous allons afficher 5 messages par page.

			//Nous allons maintenant compter le nombre de pages.
			$nombreDePages=ceil($total/$messagesParPage);
			
			
			if(isset($_GET['page'])) // Si la variable $_GET['page'] existe...
			{
				$pageActuelle=intval($_GET['page']);
			 
				if($pageActuelle>$nombreDePages) // Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
				{
					$pageActuelle=$nombreDePages;
				}
			}
			
			else // Sinon
			{
				$pageActuelle=1; // La page actuelle est la n°1    
			}

			$premiereEntree=($pageActuelle-1)*$messagesParPage; // On calcul la première entrée à lire
			
			pagination($nombreDePages, $pageActuelle, $f);
			
			echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="10" style="width: 40% id=trier;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr style="background-color:#F0F0F0;">
			<th><div class=arrow2>Nom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th><div class=arrow2>Prenom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>Numero</th>
			<th><div class=arrow2>Bac</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th style = width:9%;><div class=arrow2>Moyenne</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>BonusMalus</th>
			<th>AvisCE</th>
			<th>Postule en Initiale</th>
			<th>Selectionner</th>
			</tr> ';
			

			$req = $bd->prepare('select * from AtraiterFA order by moyenne DESC LIMIT '.$premiereEntree.', '.$messagesParPage.'');
			$req->execute();


			//Permet de récuperer le nom de la filiere dans la variable POST
			echo '<input type="hidden" name="filiere" value="'.$f.'"/>';

			while($rep = $req->fetch(PDO::FETCH_ASSOC))
			{
				$filiere=postule2filiere($bd,$rep['Numero']);
				echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
				<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
				'</td><td>'.$filiere.'</td><td><input type="checkbox" name="selection[]" value="'.$rep['Numero'].'"/></td></tr>';
			}
			
		
			while($rep = $req->fetch(PDO::FETCH_ASSOC))
			{
				echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>';
				if($rep['Moyenne']==NULL)echo '<td>0</td>';
				else echo '<td>'.$rep['Moyenne'].'</td>';
				echo '<td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
				'</td><td><input type="checkbox" name="selection[]" value="'.$rep['Numero'].'"/></td></tr>';
			}
		
		echo "</tbody></form></table></center>";
		
		}
	}
	else{
		
		echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="10" style="width: 40% id=trier;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr style="background-color:#F0F0F0;">
			<th><div class=arrow2>Nom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th><div class=arrow2>Prenom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>Numero</th>
			<th><div class=arrow2>Bac</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th style = width:10%;><div class=arrow2>Moyenne</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>BonusMalus</th>
			<th>AvisCE</th>
			</tr> ';

		if ($f == 'fi')
		{
			$nb_lignes_total= $bd->prepare('SELECT COUNT(*) AS total FROM AtraiterFI'); //Nous récupérons le contenu de la requête dans $retour_total, requete differentes dans chaque cas
			$nb_lignes_total->execute();
			while($donnees_total=$nb_lignes_total->fetch(PDO::FETCH_ASSOC))
			{
				$total = $donnees_total['total']; //On récupère le total pour le placer dans la variable $total.
			}
			
			$messagesParPage=25; //Nous allons afficher 5 messages par page.

			//Nous allons maintenant compter le nombre de pages.
			$nombreDePages=ceil($total/$messagesParPage);
			
			
			
			
			if(isset($_GET['page'])) // Si la variable $_GET['page'] existe...
			{
				$pageActuelle=intval($_GET['page']);
			 
				if($pageActuelle>$nombreDePages) // Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
				{
					$pageActuelle=$nombreDePages;
				}
			}
			
			else // Sinon
			{
				$pageActuelle=1; // La page actuelle est la n°1    
			}
	
			$premiereEntree=($pageActuelle-1)*$messagesParPage; // On calcul la première entrée à lire
			pagination($nombreDePages, $pageActuelle, $f);
			
			$req = $bd->prepare('select * from AtraiterFI order by moyenne DESC LIMIT '.$premiereEntree.', '.$messagesParPage.'');
			$req->execute();
		}
		
		else
		{
			$nb_lignes_total= $bd->prepare('SELECT COUNT(*) AS total FROM AtraiterFI'); //Nous récupérons le contenu de la requête dans $retour_total, requete differentes dans chaque cas
			$nb_lignes_total->execute();
			while($donnees_total=$nb_lignes_total->fetch(PDO::FETCH_ASSOC))
			{
				$total = $donnees_total['total']; //On récupère le total pour le placer dans la variable $total.
			}
			
			$messagesParPage=25; //Nous allons afficher 5 messages par page.

			//Nous allons maintenant compter le nombre de pages.
			$nombreDePages=ceil($total/$messagesParPage);
			
			
			if(isset($_GET['page'])) // Si la variable $_GET['page'] existe...
			{
				$pageActuelle=intval($_GET['page']);
			 
				if($pageActuelle>$nombreDePages) // Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
				{
					$pageActuelle=$nombreDePages;
				}
			}
			
			else // Sinon
			{
				$pageActuelle=1; // La page actuelle est la n°1    
			}
			
			$premiereEntree=($pageActuelle-1)*$messagesParPage; // On calcul la première entrée à lire
			pagination($nombreDePages, $pageActuelle, $f);
			$req = $bd->prepare('select * from AtraiterFA order by moyenne DESC LIMIT '.$premiereEntree.', '.$messagesParPage.'');
			$req->execute();
			
		}
		
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>';
			if($rep['Moyenne']==NULL)echo '<td>0</td>';
			else echo '<td>'.$rep['Moyenne'].'</td>';
			echo '<td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].
			'</td></tr>';
		}
		echo "</table></center>";
	}
	
	
	echo '</p>';
	

}


//Renvoi la valeur du cookie correspondant au champs déja saisi par l'enseignant
function bonusMalusInsertForm($num, $champs){

		if( isset($_COOKIE[$num]) ){
		
				//on recupere les cookie et on met les valeur dans un array 
				$cookie = $_COOKIE[$num];
				$cookie = stripslashes($cookie);
				$val = json_decode($cookie, true);
				return $val[$champs];


			}
}


//Fonction qui retourne tous les candidats ayant été attribuer a un enseignant donné en paramètre ($prof).
function afficheCandidatDuProf($bd, $prof){

	//on récupere les étudiant à traité par le prof passé en parametre de la table FI
	$query="SELECT Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFI WHERE enseignant =  :prof";
	$req=$bd->prepare($query);
	$req->bindValue('prof',$prof);
	$req->execute();

	//on récupere les étudiant à traité par le prof passé en parametre de la table FA
	$query2="SELECT Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFA WHERE enseignant =  :prof";
	$req2=$bd->prepare($query2);
	$req2->bindValue('prof',$prof);
	$req2->execute();

	if ($_SESSION['admin']==1)// pour la session admin 
	{
		//on prépare les entêtes du tableau 
		echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="10" style="width: 40% id=trier;">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr>
			<th><div class=arrow2>Nom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th><div class=arrow2>Prenom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>Numero</th>
			<th><div class=arrow2>Bac</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th style = width:10%;><div class=arrow2>Moyenne</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>BonusMalus</th>
			<th>AvisCE</th>
			<th>Postule</th>
		</tr> ';
	
		//on remplis le tableau avec les élèves de la la base 
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep['Nom'].'</td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
				<td>'.$rep['Moyenne'].'</td><td>'.$rep['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep['AvisDuCE'].'</td><td>Filière Initiale</td></tr>';
		}

		while($rep2 = $req2->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$rep2['Nom'].'</td><td>'.$rep2['Prénom'].'</td><td>'.$rep2['Numero'].'</td><td>'.$rep2['InfosDiplôme'].'</td>
				<td>'.$rep2['Moyenne'].'</td><td>'.$rep2['NombreDeBonusMalusAppliqués'].'</td><td>'.$rep2['AvisDuCE'].'</td><td>Filière Alternance</td></tr>';
		}

		echo "</table></center>";

	}
	else // pour les session enseignants
	{
		//on prépare les entêtes du tableau 
		echo '<center><table class="pure-table-horizontal" border="1" CELLPADDING="10" style="width: 40% id=trier;">
		<FORM method = "post" style="margin-left:auto; margin-right:auto; width:20%;" action="dossiers.php">
		<CAPTION style="padding: 2em;"><strong>LISTE DES ELEVES</strong></CAPTION>
 		<tr>
			<th><div class=arrow2>Nom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th><div class=arrow2>Prenom</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>Numero</th>
			<th><div class=arrow2>Bac</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th style = width:10%;><div class=arrow2>Moyenne</div><div class=arrow><div><span onclick=TableOrder(event,0)>&#9650;</span></div><div><span onclick=TableOrder(event,1)>&#9660;</span></div></div></th>
			<th>Dossier</th>
			<th>Lettre M.</th>
			<th>Autre</th>
			<th>AvisCE</th>
			<th>Postule</th>
			<th>Validation temporaire</th>
		</tr> ';

		//on remplis le tableau avec les élèves de la la base + un champs pour la saisi des bonus/malus 
		while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			
			echo '<FORM method = "post" action="dossiers.php">
			<tr><td><a href="URL demandé à gerard">'.$rep['Nom'].'</a></td><td>'.$rep['Prénom'].'</td><td>'.$rep['Numero'].'</td><td>'.$rep['InfosDiplôme'].'</td>
			<td>'.$rep['Moyenne'].'</td><td><input type="text" size="3" name="dossier" value="'.bonusMalusInsertForm($rep['Numero'], 'dossier').'"></td><td><input type="text" size="3" name="lettre" value="'.bonusMalusInsertForm($rep['Numero'], 'lettre').'"></td><td><input type="text" size="3" name="autre" value="'.bonusMalusInsertForm($rep['Numero'], 'autre').'"></td><td>'.$rep['AvisDuCE'].'</td><td>Filière Initiale</td><td><center><input class="pure-button pure-input-1-2 pure-button-primary" type="submit" name="validerTmp" value="Ok"></center></td></tr><input type="hidden" name="NumEtudiant" value="'.$rep['Numero'].'"/><input type="hidden" name="filiere" value="EtudiantFI"/></FORM>';
		}

		while($rep2 = $req2->fetch(PDO::FETCH_ASSOC))
		{
			echo '<FORM method = "post" action="dossiers.php">
			<tr><td><a href="URL demandé gerard">'.$rep2['Nom'].'</a></td><td>'.$rep2['Prénom'].'</td><td>'.$rep2['Numero'].'</td><td>'.$rep2['InfosDiplôme'].'</td>
			<td>'.$rep2['Moyenne'].'</td><td><input type="text" size="3" name="dossier" value="'.bonusMalusInsertForm($rep2['Numero'], 'dossier').'"></td><td><input type="text" size="3" name="lettre" value="'.bonusMalusInsertForm($rep2['Numero'], 'lettre').'"></td><td><input type="text" size="3" name="autre" value="'.bonusMalusInsertForm($rep2['Numero'], 'autre').'"></td><td>'.$rep2['AvisDuCE'].'</td><td>Filière Alternance</td><td><center><input class="pure-button pure-input-1-2 pure-button-primary" type="submit" name="validerTmp" value="Ok"></center></td></tr><input type="hidden" name="NumEtudiant" value="'.$rep2['Numero'].'"/><input type="hidden" name="filiere" value="EtudiantFA"/></FORM>';
		}

		// echo '<input class="pure-button pure-input-1-2 pure-button-primary" style="margin-top: 1.5em; border-radius:3px;" type="submit" name="formFinal" value="Validation Final">';

		echo "</FORM></table></center>";
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
	echo '..............EtudiantFA TABLE CREATE..............<br>';

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
	echo '..............EtudiantFI TABLE CREATE..............<br>';

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
	echo '.................SelectionneFA VIEW CREATE..............<br>';
	
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
	echo '.................SelectionneFI VIEW CREATE..............<br>';

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
	
	 $calcul = $rep[0]/4;// On calcule le 1/4
	 $req = $bd->prepare('CREATE VIEW AtraiterFA (RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE) 
	 					  AS SELECT  
	 					  RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFA 
	 					  ORDER BY Moyenne DESC LIMIT :limite, :offset');

	 $req->bindValue(':limite', $calcul, PDO::PARAM_INT);
	 $calcul = $calcul*3; //On calcule  les 3/4 du milieu
	 $req->bindValue(':offset', $calcul, PDO::PARAM_INT);
	 $req->execute();

	 // $req = $bd->prepare('ALTER TABLE AtraiterFA ADD PRIMARY KEY (Numero)');
	 // $req->execute();
	  echo '.................AtraiterFA VIEW CREATE.................<br>';
	
	/////////////////////CAS FI/////////////////////
	//											  //
	////////////////////////////////////////////////
	
	$req = $bd->prepare('Select count(*) from EtudiantFI');//on compte le nbre d'eleves
	$req->execute();
	
	$rep = $req->fetch(PDO::FETCH_NUM);
	
	$calcul = $rep[0]/4; // On calcule le 1/4

	$req = $bd->prepare('CREATE VIEW AtraiterFI (RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE)
	 					  AS SELECT  
	 					  RangProvisoire, Numero, Nom, Prénom, Moyenne, InfosDiplôme, Spécialité, NombreDeBonusMalusAppliqués, AvisDuCE FROM EtudiantFI 
	 					  ORDER BY Moyenne DESC LIMIT :limite, :offset');

	 $req->bindValue(':limite', $calcul, PDO::PARAM_INT);
	 $calcul = $calcul*3;
	 $req->bindValue(':offset', $calcul, PDO::PARAM_INT);
	 $req->execute();

	 // $req = $bd->prepare('ALTER TABLE AtraiterFI ADD PRIMARY KEY (Numero)');
	 // $req->execute();
	 echo '.................AtraiterFI VIEW CREATE.................<br>';
	
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

	echo '.................EtudiantFAFI VIEW CREATE.................<br>';
}
//elevePostuleFAFI($bd);

//Supprime de la base de données tous les candidats qui ont une moyenne NULL ou = 0
function supprimerNULL($bd){

	$req = $bd->prepare("DELETE FROM EtudiantFA WHERE Moyenne IS NULL OR Moyenne=0");

	$req->execute();

	$req2 = $bd->prepare("DELETE FROM EtudiantFI WHERE Moyenne IS NULL OR Moyenne=0");

	$req2->execute();

}
//supprimerNULL($bd);

//applique les bonus/malus aux eleves 
function bonusMalusTotal($bd, $filiere, $prof) {

	//On recupere le numero des étudiants attribuer a l'enseignant (prof)
	$query="SELECT Numero FROM EtudiantFI WHERE enseignant =  :prof";
	$req=$bd->prepare($query);
	$req->bindValue('prof',$prof);
	$req->execute();

	$query="SELECT Numero FROM EtudiantFA WHERE enseignant =  :prof";
	$req2=$bd->prepare($query);
	$req2->bindValue('prof',$prof);
	$req2->execute();


	while($rep = $req->fetch(PDO::FETCH_ASSOC))
		{
			$total = bonusMalusInsertForm($rep['Numero'], 'dossier') + bonusMalusInsertForm($rep['Numero'], 'lettre') + bonusMalusInsertForm($rep['Numero'], 'autre');
			$query="UPDATE EtudiantFI SET NombreDeBonusMalusAppliqués = :total WHERE Numero = :id";
			$req3=$bd->prepare($query);
			$req3->bindValue('total',$total);
			$req3->bindValue('id',$rep['Numero']);
			$req3->execute();			
		}

	while($rep2 = $req2->fetch(PDO::FETCH_ASSOC))
		{
			$total = bonusMalusInsertForm($rep2['Numero'], 'dossier') + bonusMalusInsertForm($rep2['Numero'], 'lettre') + bonusMalusInsertForm($rep2['Numero'], 'autre');
			$query="UPDATE EtudiantFI SET NombreDeBonusMalusAppliqués = :total WHERE Numero = :id";
			$req4=$bd->prepare($query);
			$req4->bindValue('total',$total);
			$req4->bindValue('id',$rep2['Numero']);
			$req4->execute();
		}

	

}






?>

