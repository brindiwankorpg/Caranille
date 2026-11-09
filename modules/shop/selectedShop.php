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
if (isset($_POST['token'])
&& isset($_POST['shopId']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;
        
        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['shopId'])
        && $_POST['shopId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $shopId = $_POST['shopId'];

            // On fait une requête pour vérifier si le magasin est bien disponible dans le lieu du joueur
            $shopQueryList = $bdd->prepare("SELECT * FROM car_shops, car_places, car_places_shops
            WHERE placeShopShopId = shopId
            AND placeShopPlaceId = placeId
            AND shopId = ?
            AND placeId = ?");
            $shopQueryList->execute([$shopId, $placeId]);
            $shopRow = $shopQueryList->rowCount();

            // Si plusieurs magasins ont été trouvé
            if ($shopRow > 0)
            {
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($shop = $shopQueryList->fetch())
                {
                    // On récupère les informations du magasin
                    $shopPicture = $shop['shopPicture'];
                    $shopName = $shop['shopName'];
                    $shopDescription = $shop['shopDescription'];
                }

                // On cherche à savoir S'il y a un ou plusieurs objets en vente
                $placeShopQuery = $bdd->prepare("SELECT * FROM car_shops, car_items, car_items_types, car_shops_items
                WHERE itemItemTypeId = itemTypeId
                AND shopItemShopId = shopId
                AND shopItemItemId = itemId
                AND shopId = ?
                ORDER BY itemItemTypeId, itemName");
                $placeShopQuery->execute([$shopId]);
                $placeShopRow = $placeShopQuery->rowCount();

                // S'il existe un ou plusieurs objet dans le magasin on affiche le menu déroulant
                if ($placeShopRow > 0) 
                {
                    ?>
                    
                    <p><img src="<?php echo htmlspecialchars($shopPicture)?>" height="100" width="100"></p>
                    
                    <h4><?php echo htmlspecialchars($shopName); ?></h4><br />
                    <?php echo htmlspecialchars($shopDescription); ?>

                    <hr>

                    <form method="POST" action="viewItem.php">
                        Article(s) en vente : <select name="itemId" class="form-control">
                        
                        <?php
                        // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                        while ($placeShop = $placeShopQuery->fetch())
                        {
                            // On récupère les informations de l'objet
                            $itemId = $placeShop['itemId'];
                            $itemName = $placeShop['itemName'];
                            $itemPurchasePrice = $placeShop['itemPurchasePrice'];
                            $itemDiscount = $placeShop['shopItemDiscount'];
                            $itemTypeName = $placeShop['itemTypeName'];
                            $itemTypeNameShow = $placeShop['itemTypeNameShow'];

                            // On calcule la réduction de l'équipement/objet
                            $discount = $itemPurchasePrice * $itemDiscount / 100;
                            // On applique la réduction
                            $itemPurchasePrice = $itemPurchasePrice - $discount
                            ?>
                            <option value="<?php echo htmlspecialchars($itemId)?>"><?php echo "[" . htmlspecialchars($itemTypeNameShow) . "] - " . htmlspecialchars($itemName) . " (" . htmlspecialchars($itemPurchasePrice) . " Pièce d'or)"; ?></option>
                            <?php
                        }
                        ?>
                        
                        </select>
                        <input type="hidden" name="shopId" value="<?php echo htmlspecialchars($shopId)?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                        <input type="submit" name="view" class="btn btn-secondary btn-lg" value="Détail/Achat">
                    </form>
                    
                    <hr>

                    <form method="POST" action="index.php">
                        <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                    </form>
                    
                    <?php
                }
                else
                {
                    echo "Il n'y a aucun article dans ce magasin";
                }
                $placeShopQuery->closeCursor();
            }
            // Si le magasin n'existe pas ou qu'il n'est pas disponible dans le lieu du joueur
            else
            {
                echo "Erreur : Ce magasin n'existe pas ou n'est pas disponible dans ce lieu";
            }
            $shopQueryList->closeCursor(); 
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
