<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }
// Si le joueur n'a pas les droits modérateur ou administrateur (Accès 1 ou 2) on le redirige vers l'accueil du chat
if ($accountAccess < 1) { exit(header("Location: index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['chatMessageId'])
&& isset($_POST['token'])
&& isset($_POST['deleteMessage']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;
		
	    // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
	    if (ctype_digit($_POST['chatMessageId'])
	    && $_POST['chatMessageId'] >= 1)
	    {
	        // On récupère l'id du formulaire précédent
	        $chatMessageId = $_POST['chatMessageId'];
	        
	        // On fait une requête pour vérifier si le message choisi existe
	        $chatQuery = $bdd->prepare("SELECT * FROM car_chat
	        WHERE chatMessageId = ?");
	        $chatQuery->execute([$chatMessageId]);
	        $chatRow = $chatQuery->rowCount();
	
	        // Si le message existe
	        if ($chatRow == 1) 
	        {
	            $chatQuery->closeCursor();

	            // On vide le chat de la base de données
	            $deleteChat = $bdd->prepare("DELETE FROM car_chat
	            WHERE chatMessageId = ?");
	            $deleteChat->execute([$chatMessageId]);
	            $deleteChat->closeCursor();
	            
	            // On redirige le joueur vers le chat
	            exit(header("Location: index.php"));
	        }
	        // Si le message n'existe pas
	        else
	        {
	            $chatQuery->closeCursor();
	            echo "Erreur : Ce message n'existe pas";
	        }
	    }
	    // Si tous les champs numériques ne contiennent pas un nombre
	    else
	    {
	        echo "Erreur : Les champs de type numérique ne peuvent contenir qu'un nombre entier";
	    }
    }
    // Si le token de sécurité n'est pas correct
    else
    {
        echo "Erreur : La session a expiré, veuillez réessayer";
    }
}
// Si toutes les variables $_POST n'existent pas
else
{
    echo "Erreur : Tous les champs n'ont pas été remplis";
}

require_once("../../html/footer.php"); ?>
