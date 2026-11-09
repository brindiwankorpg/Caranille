<?php
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['tradeId'])
&& isset($_POST['tradeGold'])
&& isset($_POST['token'])
&& isset($_POST['addGoldEnd']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['tradeId'])
        && ctype_digit($_POST['tradeGold'])
        && $_POST['tradeId'] >= 0)
        {
            // On récupère l'id du formulaire précédent
            $tradeId = $_POST['tradeId'];
            $tradeGold = $_POST['tradeGold'];

            // On fait une requête pour vérifier si cette demande existe et est bien attribué au joueur
            $tradeQuery = $bdd->prepare("SELECT * FROM car_trades
            WHERE (tradeCharacterOneId = ?
            OR tradeCharacterTwoId = ?)
            AND tradeId = ?");
            $tradeQuery->execute([$characterId, $characterId, $tradeId]);
            $tradeRow = $tradeQuery->rowCount();

            // Si cet échange existe et est attribué au joueur
            if ($tradeRow > 0)
            {
                // On fait une requête pour récupérer le montant de l'argent que le joueur a déjà mis dans l'échange
                $tradeGoldQuery = $bdd->prepare("SELECT * FROM car_trades_golds
                WHERE tradeGoldCharacterId = ?
                AND tradeGoldTradeId = ?");
                $tradeGoldQuery->execute([$characterId, $tradeId]);
                $tradeGoldRow = $tradeGoldQuery->rowCount();

                // Si le montant de l'argent du joueur existe bien dans l'échange
                if ($tradeGoldRow == 1)
                {
                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($tradeGoldList = $tradeGoldQuery->fetch())
                    {
                        // On récupère le nombre de pièce d'or que le joueur a déjà mis dans l'échange
                        $tradeGoldQuantity = $tradeGoldList['tradeGoldQuantity'];
                    }

                    // Si le joueur a suffisamment d'argent en comptant l'argent qu'il a déjà mis dans l'échange
                    if ($characterGold + $tradeGoldQuantity >= $tradeGold)
                    {
                        // On met l'argent de l'échange à jour
                        $updateGoldTrade = $bdd->prepare("UPDATE car_trades_golds SET
                        tradeGoldQuantity = :tradeGold
                        WHERE tradeGoldTradeId = :tradeId
                        AND tradeGoldCharacterId = :characterId");
                        $updateGoldTrade->execute(array(
                        'tradeGold' => $tradeGold,
                        'tradeId' => $tradeId,
                        'characterId' => $characterId));
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

                        // On rend au joueur l'argent qu'il avait déjà mis dans l'échange et on lui retire le nouveau montant
                        $updateGoldTrade = $bdd->prepare("UPDATE car_characters SET
                        characterGold = characterGold + :tradeGoldQuantity - :tradeGold
                        WHERE characterId = :characterId");
                        $updateGoldTrade->execute(array(
                        'tradeGoldQuantity' => $tradeGoldQuantity,
                        'tradeGold' => $tradeGold,
                        'characterId' => $characterId));
                        $updateGoldTrade->closeCursor();
                        ?>

                        Vous avez mis <?php echo htmlspecialchars($tradeGold)?> pièce(s) d'or dans l'échange

                        <hr>

                        <form method="POST" action="index.php">
                            <input type="submit" class="btn btn-secondary btn-lg" name="manage" value="Retour">
                        </form>

                        <?php
                    }
                    // Si le joueur n'a pas suffisamment d'argent
                    else
                    {
                        ?>

                        <?php echo "Vous n'avez pas assez d'argent"; ?>

                        <hr>

                        <form method="POST" action="index.php">
                            <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                        </form>

                        <?php
                    }
                }
                // Si le montant de l'argent du joueur n'existe pas dans l'échange
                else
                {
                    echo "Erreur : Impossible de trouver l'argent de cet échange";
                }
                $tradeGoldQuery->closeCursor();
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
