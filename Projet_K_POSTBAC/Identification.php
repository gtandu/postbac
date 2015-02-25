<?php
		require_once('connexion.php');
		require_once('acceptationId.php');
		require('debut.php'); 
		ini_set('display_errors',1);
?>	
	<section class="identification1">
	<header>
		<h1 style=" margin-left:auto; margin-right:auto; width:60%;"> Bievenue sur le portail d'Admission PostBac </h1>
	</header>
	<html>
	<head>
		<title>Projet PostBac</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
	<body>


	<div style=" margin-left:auto; margin-right:auto; width:40%;">
		
  		<form action="acceptationId.php" method="post" class="pure-form pure-form-aligned">
	    <fieldset>
	        <div class="pure-control-group">
	            <label for="name">Identifiant</label>
	            <input name="name" type="text" placeholder="Identifiant">
	        </div>

	        <div class="pure-control-group">
	            <label for="password">Mot de Passe</label>
	            <input name="password" type="password" placeholder="Password">
	        </div>
	      
	        <div class="pure-controls">
	            <label for="cb" class="pure-checkbox">
	                <input id="cb" type="checkbox"> Se souvenir de moi </label>

	            

	            <button type="submit" class="pure-button pure-input-1-2 pure-button-primary">Me Connecter</button>
	        </div>

	        

	    </fieldset>
	</form>
	</div>
	</section>
	
	

	


<?php
		require('fin.php'); 
?>
