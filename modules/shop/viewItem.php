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
if (isset($_POST['shopId'])
&& isset($_POST['itemId'])
&& isset($_POST['token'])
&& isset($_POST['view']))
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
        && ctype_digit($_POST['itemId'])
        && $_POST['shopId'] >= 1
        && $_POST['itemId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
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
                $itemQuery = $bdd->prepare("SELECT * FROM car_items, car_items_types, car_shops_items
                WHERE itemItemTypeId = itemTypeId
                AND shopItemItemId = itemId
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
                        $itemclasseId = $item['itemclasseId'];
                        $itemTypeName = $item['itemTypeName'];
                        $itemTypeNameShow = $item['itemTypeNameShow'];
                        $itemPicture = $item['itemPicture'];
                        $itemLevel = $item['itemLevel'];
                        $itemLevelRequired = $item['itemLevelRequired'];
                        $itemName = $item['itemName'];
                        $itemDescription = $item['itemDescription'];
                        $itemHpEffect = $item['itemHpEffect'];
                        $itemMpEffect = $item['itemMpEffect'];
                        $itemStrengthEffect = $item['itemStrengthEffect'];
                        $itemMagicEffect = $item['itemMagicEffect'];
                        $itemAgilityEffect = $item['itemAgilityEffect'];
                        $itemDefenseEffect = $item['itemDefenseEffect'];
                        $itemDefenseMagicEffect = $item['itemDefenseMagicEffect'];
                        $itemWisdomEffect = $item['itemWisdomEffect'];
                        $itemProspectingEffect = $item['itemProspectingEffect'];
                        $itemSalePrice = $item['itemSalePrice'];
                        $itemPurchasePrice = $item['itemPurchasePrice'];
                        $itemDiscount = $item['shopItemDiscount'];
                    }
                    // Si la classe de l'équipement est supérieure à 1 c'est qu'il est attitré à une classe
                    if ($itemclasseId >= 1)
                    {
                        // On récupère la classe de l'équipement
                        $classeQuery = $bdd->prepare("SELECT * FROM car_classes
                        WHERE classeId = ?");
                        $classeQuery->execute([$itemclasseId]);
                        
                        while ($classe = $classeQuery->fetch())
                        {
                            // On récupère le nom de la classe
                            $itemclasseName = $classe['classeName'];
                        }
                        $classeQuery->closeCursor(); 
                    }
                    // Si la classe de l'équipement est égale à 0 c'est qu'il est disponible pour toutes les classes
                    else
                    {
                        $itemclasseName = "Toutes les classes";
                    }

                    // On calcule le prix final de l'objet par rapport à la réduction
                    $discount = $itemPurchasePrice * $itemDiscount / 100;
                    $itemPurchasePrice = $itemPurchasePrice - $discount; 
                    ?>
                    
                    <p><img src="<?php echo htmlspecialchars($itemPicture)?>" height="100" width="100"></p>
                    
                    <table class="table">
                        
                        <tr>
                            <td>
                                Type
                            </td>
                            
                            <td>
                                <?php echo htmlspecialchars($itemTypeNameShow); ?>
                            </td>
                        </tr>
                            
                        <tr>
                            <td>
                                Nom
                            </td>
                            
                            <td>
                                <?php echo htmlspecialchars($itemName)?>
                            </td>
                        </tr>
                            
                        <tr>
                            <td>
                                Description
                            </td>
                            
                            <td>
                                <?php echo nl2br(htmlspecialchars($itemDescription))?>
                            </td>
                        </tr>
                        
                        <?php
                        // S'il s'agit d'un équipement on affiche la classe de celui-ci ainsi que son niveau requis
                        if ($itemTypeName != "Item")
                        {
                            ?>
                            <tr>
                                <td>
                                    Classe requise
                                </td>
                            
                                <td>
                                    <?php echo htmlspecialchars($itemclasseName)?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    Niveau de l'objet
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($itemLevel)?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    Niveau requis
                                </td>
                                
                                <td>
                                    <?php echo htmlspecialchars($itemLevelRequired)?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                            
                        <tr>
                            <td>
                                Effet(s)
                            </td>
                            
                            <td>
                                <?php
                                // S'il s'agit d'un équipement on affiche toutes les stats concernées
                                if ($itemTypeName != "Item")
                                {
                                    // Si l'équipement augmente les HP on l'affiche
                                    if ($itemHpEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemHpEffect) . " HP<br />";
                                    }
                                    
                                    // Si l'équipement augmente les MP on l'affiche
                                    if ($itemMpEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemMpEffect) . " MP<br />";
                                    }
                                    
                                    // Si l'équipement augmente la force on l'affiche
                                    if ($itemStrengthEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemStrengthEffect) . " Force<br />";
                                    }
                                    
                                    // Si l'équipement augmente la magie on l'affiche
                                    if ($itemMagicEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemMagicEffect) . " Magie<br />";
                                    }
                                    
                                    // Si l'équipement augmente l'agilité on l'affiche
                                    if ($itemAgilityEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemAgilityEffect) . " Agilité<br />";
                                    }
                                    
                                    // Si l'équipement augmente la défense on l'affiche
                                    if ($itemDefenseEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemDefenseEffect) . " Défense<br />";
                                    }
                                    
                                    // Si l'équipement augmente la défense magique on l'affiche
                                    if ($itemDefenseMagicEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemDefenseMagicEffect) . " Défense Magic<br />";
                                    }
                                    
                                    // Si l'équipement augmente la sagesse on l'affiche
                                    if ($itemWisdomEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemWisdomEffect) . " Sagesse<br />";
                                    }
                                    
                                    // Si l'équipement augmente la prospection on l'affiche
                                    if ($itemProspectingEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemProspectingEffect) . " Prospection<br />";
                                    }
                                }
                                // S'il s'agit d'un objet on affiche que les stats HP et MP qui sont concernée
                                else
                                {
                                    // Si l'objet augmente les HP on l'affiche
                                    if ($itemHpEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemHpEffect) . " HP<br />";
                                    }
                                    
                                    // Si l'objet augmente les MP on l'affiche
                                    if ($itemMpEffect > 0)
                                    {
                                        echo "+ " . htmlspecialchars($itemMpEffect) . " MP<br />";
                                    }
                                }
                                ?>                            
                            </td>
                        </tr>
                            
                        <tr>
                            <td>
                                Prix d'achat
                            </td>
                            
                            <td>
                                <?php echo htmlspecialchars($itemPurchasePrice)?>
                            </td>
                        </tr>
                            
                        <tr>
                            <td>
                                Actions
                            </td>
                            
                            <td>
                                <form method="POST" action="buyItem.php">
                                    Quantité à acheter : <input type="number" name="buyQuantity" value="1" class="form-control" required>
                                    <input type="hidden" name="shopId" value="<?php echo htmlspecialchars($shopId)?>">
                                    <input type="hidden" name="itemId" value="<?php echo htmlspecialchars($itemId)?>">
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                                    <input type="submit" class="btn btn-secondary btn-lg" name="buy" value="Acheter">
                                </form>
                            </td>
                        </tr>
                    </table>
        
                    <hr>

                    <form method="POST" action="selectedShop.php">
                        <input type="hidden" name="shopId" value="<?php echo htmlspecialchars($shopId)?>">   
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
