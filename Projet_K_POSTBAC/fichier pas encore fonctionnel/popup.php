<?php
require_once('debut.php');
?>
<form method="POST" action="upload.php" enctype="multipart/form-data">
     <!-- On limite le fichier à 1000Ko -->
     <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
     <input  type="file" value="ok" name="fich">
     <input  type="submit" name="envoyer" value="Envoyer le fichier">
	 
</form>

<?php
require_once('fin.php');
?>