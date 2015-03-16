<?php
		
		session_start();
		ini_set('memory_limit','64M');
		/* require_once('connexion.php'); */
		require('debut.php');
		require('menu.php'); 
		require('fonctions.php');

?>


<br>
<center><p id='textAccueil'><strong>Cet espace permet l'affichage des différents candidats, veuillez selectionner mode d'affichage :</strong></p></center> 
<br>

<SCRIPT>
var index
function  sort_int(p1,p2) { return p1[index]-p2[index]; }			//fonction pour trier les nombres
function sort_char(p1,p2) { return ((p1[index]>=p2[index])<<1)-1; }	//fonction pour trier les strings

function TableOrder(e,Dec)  //Dec= 0:Croissant, 1:Décroissant
{ //---- Détermine : oCell(cellule) oTable(table) index(index cellule) -----//
	var FntSort = new Array()
	if(!e) e=window.event
	for(oCell=e.srcElement?e.srcElement:e.target;oCell.tagName!="TH";oCell=oCell.parentNode);	//determine la cellule sélectionnée
	for(oTable=oCell.parentNode;oTable.tagName!="TABLE";oTable=oTable.parentNode);				//determine l'objet table parent
	for(index=0;oTable.rows[0].cells[index]!=oCell;index++);									//determine l'index de la cellule

 //---- Copier Tableau Html dans Table JavaScript ----//
	var Table = new Array()
	for(r=1;r<oTable.rows.length;r++) Table[r-1] = new Array()

	for(c=0;c<oTable.rows[0].cells.length;c++)	//Sur toutes les cellules
	{	var Type;
		objet=oTable.rows[1].cells[c].innerHTML.replace(/<\/?[^>]+>/gi,"")
		if(objet.match(/^[0-9]+(\.[0-9]{1,2})?$/))		{ FntSort[c]=sort_int;  Type=0; } //nombre
		else									{ FntSort[c]=sort_char; Type=1; } //Chaine de caractère

		for(r=1;r<oTable.rows.length;r++)		//De toutes les rangées
		{	objet=oTable.rows[r].cells[c].innerHTML.replace(/<\/?[^>]+>/gi,"")
			objet.replace(null,0);
			switch(Type)		
			{	case 0: Table[r-1][c]=parseFloat(objet.replace(/[^0-9.-]/g,"")); break; //nombre
				case 1: Table[r-1][c]=objet.toLowerCase(); break; //Chaine de caractère
			}
			Table[r-1][c+oTable.rows[0].cells.length] = oTable.rows[r].cells[c].innerHTML
		}
	}

 //--- Tri Table ---//
	Table.sort(FntSort[index]);
	if(Dec) Table.reverse();

 //---- Copier Table JavaScript dans Tableau Html ----//
	for(c=0;c<oTable.rows[0].cells.length;c++)	//Sur toutes les cellules
		for(r=1;r<oTable.rows.length;r++)		//De toutes les rangées 
			oTable.rows[r].cells[c].innerHTML=Table[r-1][c+oTable.rows[0].cells.length];  
}
</SCRIPT>

	<center>
	<form method="GET">
			
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="fi" value="Filière initiale"/>
		
			<input style="padding-left: 2em; padding-right:2em; border-radius: 10px;" type="submit" class="pure-button pure-input-1-2 pure-button-primary" name="fa" value="Filière alternance"/>
			
	</form>
	</center>
	
<!-- script qui prend en paramètre la référence du form dans lequel il se trouve et le nom des chekbox à cocher -->	
<script>
function CocheTout(ref, name) {
	var form = ref;
 
	while (form.parentNode && form.nodeName.toLowerCase() != 'form'){ 
		form = form.parentNode; 
	}
 
	var elements = form.getElementsByTagName('input');
 
	for (var i = 0; i < elements.length; i++) {
		if (elements[i].type == 'checkbox' && elements[i].name == name) {
			elements[i].checked = ref.checked;
		}
	}
}	
</script>
	

<?php



if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fi'])){

	echo "<center>";
	afficheEleve('fi',$bd); // à la place de 'fi', on mettra le $_POST du bouton, 
	echo "</center>";					//celui qui decide si on affiche les alternants ou les initiales
}
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fa'])){
	
	echo "<center>";
	afficheEleve('fa',$bd);
	echo "</center>";

}

print_r($_GET);


?>

<?php

echo '</table>';
echo '</form>';

?>





<?php
		require('fin.php'); 
?>
