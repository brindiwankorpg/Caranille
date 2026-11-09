<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['tradeCharacterId'])
&& isset($_POST['tradeMessage'])
&& isset($_POST['token'])
&& isset($_POST['addTrade']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;
		
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['tradeCharacterId'])
        && $_POST['tradeCharacterId'] >= 0)
        {
            // On récupère l'id du formulaire précédent
            $tradeCharacterId = $_POST['tradeCharacterId'];
            $tradeMessage = $_POST['tradeMessage'];
            
            // On fait une requête pour vérifier si le personnage choisi existe et qu'il ne s'agit pas du joueur
            $characterQuery = $bdd->prepare("SELECT * FROM car_characters
            WHERE characterId = ?
            AND characterId != ?");
            $characterQuery->execute([$tradeCharacterId, $characterId]);
            $characterRow = $characterQuery->rowCount();

            // Si le compte existe
            if ($characterRow == 1)
            {
                // On vérifie si il n'y a aucune demande ou échange avec ce joueur (les parenthèses regroupent les deux conditions)
                $tradeRequestQuery = $bdd->prepare("SELECT * FROM car_characters
                WHERE characterId = ?

                AND ((SELECT COUNT(*) FROM car_trades_requests
                WHERE tradeRequestCharacterOneId = ?
                AND tradeRequestCharacterTwoId = characterId
                OR tradeRequestCharacterOneId = characterId
                AND tradeRequestCharacterTwoId = ?) > 0

                OR (SELECT COUNT(*) FROM car_trades
                WHERE tradeCharacterOneId = ?
                AND tradeCharacterTwoId = characterId
                OR tradeCharacterOneId = characterId
                AND tradeCharacterTwoId = ?) > 0)");
                $tradeRequestQuery->execute([$tradeCharacterId, $characterId, $characterId, $characterId, $characterId]); 
                $tradeRequestRow = $tradeRequestQuery->rowCount();
                
                // S'il n'y a aucune demande ou échange avec ce joueur
                if ($tradeRequestRow == 0)
                {
                    // On crée la demande d'échange
                    $addTradeRequest = $bdd->prepare("INSERT INTO car_trades_requests VALUES(
                    NULL,
                    :characterId,
                    :tradeCharacterId,
                    :tradeMessage)");
                    $addTradeRequest->execute([
                    'characterId' => $characterId,
                    'tradeCharacterId' => $tradeCharacterId,
                    'tradeMessage' => $tradeMessage]);
                    $addTradeRequest->closeCursor();
                    
                    $notificationDate = date('Y-m-d H:i:s');
                    $notificationMessage = "$characterName souhaite lancer un échange avec vous !";
                    
                    // On envoie une notification au premier joueur
                    $addNotification = $bdd->prepare("INSERT INTO car_notifications VALUES(
                    NULL,
                    :tradeCharacterId,
                    :notificationDate,
                    :notificationMessage,
                    'No')");
                    $addNotification->execute(array(
                    'tradeCharacterId' => $tradeCharacterId,  
                    'notificationDate' => $notificationDate,
                    'notificationMessage' => $notificationMessage));
                    $addNotification->closeCursor();
                    ?>
                    
                    Votre demande d'échange a bien été envoyée
                    
                    <hr>
                    
                    <form method="POST" action="index.php">
                        <input type="submit" name="edit" class="btn btn-secondary btn-lg" value="Retour">
                    </form>
                    
                    <?php
                }
                // S'il y a une demande ou échange avec ce joueur
                else
                {
                    ?>
                    
                    Une demande d'échange existe déjà avec ce joueur
                    
                    <hr>
                    
                    <form method="POST" action="index.php">
                        <input type="submit" name="back" class="btn btn-secondary btn-lg" value="Retour">
                    </form>
                    
                    <?php
                    
                }
                $tradeRequestQuery->closeCursor();
            }
            // Si le personnage n'existe pas ou qu'il s'agit du joueur
            else
            {
                echo "Erreur : Ce personnage n'existe pas ou il s'agit de votre propre personnage";
            }
            $characterQuery->closeCursor(); 
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
