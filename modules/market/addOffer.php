<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['marketItemId'])
&& isset($_POST['marketSalePrice'])
&& isset($_POST['token'])
&& isset($_POST['addOffer']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;
        
        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['marketItemId'])
        && ctype_digit($_POST['marketSalePrice'])
        && $_POST['marketItemId'] >= 1
        && $_POST['marketSalePrice'] >= 0)
        {
            // On récupère l'id du formulaire précédent
            $marketItemId = $_POST['marketItemId'];
            $marketSalePrice = $_POST['marketSalePrice'];

            // On fait une requête pour vérifier si l'objet ou équipement choisi existe
            $itemQuery = $bdd->prepare("SELECT * FROM car_items 
            WHERE itemId = ?");
            $itemQuery->execute([$marketItemId]);
            $itemRow = $itemQuery->rowCount();

            // Si l'objet ou équipement existe
            if ($itemRow == 1) 
            {
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($item = $itemQuery->fetch())
                {
                    // On récupère les informations de l'équipement
                    $marketItemName = $item['itemName'];
                }

                // On vérifie si le joueur possède bien cet objet ou équipement
                $inventoryQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory 
                WHERE itemId = inventoryItemId
                AND inventoryCharacterId = ?
                AND itemId = ?");
                $inventoryQuery->execute([$characterId, $marketItemId]);
                $inventoryRow = $inventoryQuery->rowCount();

                // Si l'objet ou équipement est bien dans l'inventaire du joueur
                if ($inventoryRow == 1) 
                {
                    ?>

                    <p>ATTENTION</p> 
                    
                    Vous êtes sur le point de mettre en ligne l'offre concernant l'article <em><?php echo htmlspecialchars($marketItemName)?></em> pour <em><?php echo htmlspecialchars($marketSalePrice)?> Pièce(s) d'or</em>.<br />
                    Une fois l'offre mise en ligne vous ne pourrez plus modifier le prix de vente ni annuler l'offre<br />
                    Confirmez-vous la mise en ligne ?

                    <hr>

                    <form method="POST" action="addOfferEnd.php">
                        <input type="hidden" name="marketItemId" value="<?php echo htmlspecialchars($marketItemId)?>">
                        <input type="hidden" name="marketSalePrice" value="<?php echo htmlspecialchars($marketSalePrice)?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                        <input type="submit" class="btn btn-secondary btn-lg" name="addOfferEnd" value="Je confirme">
                    </form>

                    <hr>

                    <form method="POST" action="index.php">
                        <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                    </form>
                    
                    <?php
                }
                // Si le joueur ne possède pas cet objet ou équipement
                else
                {
                    echo "Erreur : Vous ne pouvez pas mettre en vente un objet ou équipement que vous ne possédez pas";
                }
                $inventoryQuery->closeCursor();
            }
            // Si l'objet ou l'équipement n'existe pas
            else
            {
                echo "Erreur : Cet objet ou équipement n'existe pas";
            }
            $itemQuery->closeCursor();  
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
    echo "Tous les champs n'ont pas été remplis";
}

require_once("../../html/footer.php"); ?>
