<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['tradeId'])
&& isset($_POST['token'])
&& isset($_POST['addGold']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;
		
		// Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));
        
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['tradeId'])
        && $_POST['tradeId'] >= 0)
        {
            // On récupère l'id du formulaire précédent
            $tradeId = $_POST['tradeId'];
            
            // On fait une requête pour vérifier si cette demande existe et est bien attribué au joueur
            $tradeQuery = $bdd->prepare("SELECT * FROM car_trades
            WHERE (tradeCharacterOneId = ?
            OR tradeCharacterTwoId = ?)
            AND tradeId = ?");
            $tradeQuery->execute([$characterId, $characterId, $tradeId]);
            $tradeRow = $tradeQuery->rowCount();
            
            // Si cette échange existe et est attribuée au joueur
            if ($tradeRow > 0) 
            {
                // On fait une requête pour récupérer le montant de l'argent que le joueur a proposé
                $tradeGoldQuery = $bdd->prepare("SELECT * FROM car_trades_golds
                WHERE tradeGoldCharacterId = ?
                AND tradeGoldTradeId = ?");
                $tradeGoldQuery->execute([$characterId, $tradeId]);
                $tradeGoldRow = $tradeGoldQuery->rowCount();
    
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                $tradeGoldQuantity = 0;
                while ($tradeGold = $tradeGoldQuery->fetch())
                {
                    // On récupère les informations du montant du nombre de pièce d'or mise en échange
                    $tradeGoldQuantity = $tradeGold['tradeGoldQuantity'];
                }
                $tradeGoldQuery->closeCursor();

                // On remet à zéro la somme qu'il y avait dans l'échange
                $updateGoldTrade = $bdd->prepare("UPDATE car_trades_golds SET
                tradeGoldQuantity = 0
                WHERE tradeGoldCharacterId = :characterId
                AND tradeGoldTradeId = :tradeId");
                $updateGoldTrade->execute(array(
                'characterId' => $characterId,
                'tradeId' => $tradeId));
                $updateGoldTrade->closeCursor();
                
                $date = date('Y-m-d H:i:s');
                
                // On met l'échange à jour
                $updateTrade = $bdd->prepare("UPDATE car_trades SET
                tradeLastUpdate = :tradeLastUpdate,
                tradeCharacterOneTradeAccepted = 'No',
                tradeCharacterTwoTradeAccepted = 'No'
                WHERE tradeId = :tradeId");
                $updateTrade->execute(array(
                'tradeLastUpdate' => $date,
                'tradeId' => $tradeId));
                $updateTrade->closeCursor(); 
                            
                // Si le joueur est revenu sur cette page c'est qu'il souhaite modifier l'argent de l'échange, on lui rend donc l'argent qu'il avait mit avant
                $updateGoldTrade = $bdd->prepare("UPDATE car_characters SET
                characterGold = characterGold + :tradeGoldQuantity
                WHERE characterId = :characterId");
                $updateGoldTrade->execute(array(
                'tradeGoldQuantity' => $tradeGoldQuantity,
                'characterId' => $characterId));
                $updateGoldTrade->closeCursor();

				// Si le joueur a mis plus de 0 pièce d'or on lui indique qu'il va être remboursé pour mettre une nouvelle mise
                if ($tradeGoldQuantity > 0) 
				{ 
                	?>
					
					<p>Attention : Les <strong><?php echo htmlspecialchars($tradeGoldQuantity); ?></strong> pièce(s) d'or que vous aviez placées dans cet échange vous ont été rendues.<br />
					Veuillez saisir ci-dessous le nouveau montant total que vous souhaitez proposer.</p>

                	<?php 
                }
				// Si le joueur n'avait pas mit de pièce d'or on lui propose de saisir un montant
                else 
				{
                	?>
					
                    <p>Veuillez saisir ci-dessous le montant total de pièces d'or que vous souhaitez ajouter à cet échange.</p>

                	<?php 
                } 
                ?>
                
                <form method="POST" action="addGoldEnd.php">
                    Pièces d'or : <input type="number" name="tradeGold" value="0" class="form-control" placeholder="Pièces d'or" required>
                    <input type="hidden" name="tradeId" value="<?php echo htmlspecialchars($tradeId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input type="submit" class="btn btn-secondary btn-lg" name="addGoldEnd" value="Ajouter des pièces d'or">
                </form>
                
                <hr>
            
                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>
                
                <?php
            }
            // Si la demande d'échange n'existe pas ou n'est pas attribuée au joueur
            else
            {
                echo "Erreur : Cette demande d'échange n'existe pas ou ne vous est pas attribuée";
            }
            $tradeQuery->closeCursor(); 
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
