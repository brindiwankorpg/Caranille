<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'est pas dans un lieu on le redirige vers la carte du monde
if ($characterPlaceId == 0) { exit(header("Location: ../../modules/map/index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['buyQuantity'])
&& isset($_POST['shopId'])
&& isset($_POST['itemId'])
&& isset($_POST['token'])
&& isset($_POST['buy']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;
        
        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));
        
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['buyQuantity'])
        && ctype_digit($_POST['shopId'])
        && ctype_digit($_POST['itemId'])
        && $_POST['buyQuantity'] >= 1
        && $_POST['shopId'] >= 1
        && $_POST['itemId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $buyQuantity = $_POST['buyQuantity'];
            $shopId = $_POST['shopId'];
            $itemId = $_POST['itemId'];

            // On fait une requête pour vérifier si le magasin choisi existe et qu'il est bien disponible dans le lieu du joueur
            $shopQuery = $bdd->prepare("SELECT * FROM car_shops, car_places_shops
            WHERE placeShopShopId = shopId
            AND shopId = ?
            AND placeShopPlaceId = ?");
            $shopQuery->execute([$shopId, $placeId]);
            $shopRow = $shopQuery->rowCount();

            // Si le magasin existe dans le lieu du joueur
            if ($shopRow == 1)
            {
                // On fait une requête pour vérifier si l'objet choisi existe et qu'il est bien en vente dans ce magasin
                $itemQuery = $bdd->prepare("SELECT * FROM car_items, car_shops_items
                WHERE shopItemItemId = itemId
                AND shopItemShopId = ?
                AND itemId = ?");
                $itemQuery->execute([$shopId, $itemId]);
                $itemRow = $itemQuery->rowCount();

                // Si l'objet existe et qu'il est en vente dans ce magasin
                if ($itemRow == 1)
                {
                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($item = $itemQuery->fetch())
                    {
                        // On récupère les informations de l'objet
                        $itemName = $item['itemName'];
                        $itemPurchasePrice = $item['itemPurchasePrice'];
                        $itemDiscount = $item['shopItemDiscount'];
                    }

                    // On calcule le prix final de l'objet par rapport à la réduction
                    $discount = $itemPurchasePrice * $itemDiscount / 100;
                    $itemPurchasePrice = $itemPurchasePrice - $discount; 

                    // On calcule le prix d'achat total en prenant en compte les quantités
                    $itemPurchasePrice = $itemPurchasePrice * $buyQuantity;
                    ?>

                    <p>ATTENTION</p> 
                    
                    Vous êtes sur le point d'acheter l'article <em><?php echo htmlspecialchars($itemName)?></em> en <em><?php echo htmlspecialchars($buyQuantity)?> quantité</em> pour <em><?php echo htmlspecialchars($itemPurchasePrice)?> Pièce(s) d'or</em>.<br />
                    Confirmez-vous l'achat ?
                    
                    <hr>
                    
                    <form method="POST" action="buyItemEnd.php">
                        <input type="hidden" name="buyQuantity" value="<?php echo htmlspecialchars($buyQuantity)?>">
                        <input type="hidden" name="shopId" value="<?php echo htmlspecialchars($shopId)?>">
                        <input type="hidden" name="itemId" value="<?php echo htmlspecialchars($itemId)?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                        <input type="submit" class="btn btn-secondary btn-lg" name="finalBuy" value="Je confirme">
                    </form>
        
                    <hr>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                        <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                    </form>
                    
                    <?php
                }
                // Si l'article n'existe pas ou qu'il n'est pas en vente dans ce magasin
                else
                {
                    echo "Erreur : Cet article n'existe pas ou n'est pas en vente dans ce magasin";
                }
                $itemQuery->closeCursor();
            }
            // Si le magasin n'existe pas ou qu'il n'est pas disponible dans le lieu du joueur
            else
            {
                echo "Erreur : Ce magasin n'existe pas ou n'est pas disponible dans ce lieu";
            }
            $shopQuery->closeCursor();
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
