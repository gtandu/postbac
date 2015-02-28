console.log("Ce programme JS vient d'être chargé");
$(document).ready(function()
{
	$('#erreur-confirm').hide();
	console.log("Le document est prêt");
	
		$('#mdp_new').keyup(function(event)
	{
		console.log("Caractere mdp");
		var mdp=$("#mdp_new").val();
		$('#erreur-mdp').toggle(mdp.length<6);
		var confirm=$('#mdp_confirm').val();
		$('#erreur-confirm').toggle(mdp!==confirm);
	});
	
	$('#mdp_confirm').keyup(function(event)
	{
		console.log("Caractere confirmation mdp");
		var mdp=$('#mdp_new').val();
		var confirm=$('#mdp_confirm').val();
		$('#erreur-confirm').toggle(mdp!==confirm);
		
	});
		console.log("La mise en place est finie. En attente d'événements...");
});