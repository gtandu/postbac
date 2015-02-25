<?php
		session_start();
		session_destroy();
		require_once('connexion.php');
		ini_set('memory_limit','64M');




header('location: identification.php');





?>