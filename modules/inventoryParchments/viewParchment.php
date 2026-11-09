<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['itemId'])
&& isset($_POST['token'])
&& isset($_POST['viewItem']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;
        
        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));
        
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if(ctype_digit($_POST['itemId'])
        && $_POST['itemId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $itemId = $_POST['itemId'];
                    
            // On fait une requête pour vérifier si l'objet choisi existe
            $itemQuery = $bdd->prepare("SELECT * FROM car_items 
            WHERE itemId = ?");
            $itemQuery->execute([$itemId]);
            $itemRow = $itemQuery->rowCount();
    
            // Si l'objet existe
            if ($itemRow == 1) 
            {
                // On fait une requête pour avoir la liste des objets du personnage
                $itemInventoryQuery = $bdd->prepare("SELECT * FROM  car_items, car_items_types, car_inventory 
                WHERE itemItemTypeId = itemTypeId
                AND itemId = inventoryItemId
                AND itemTypeName = 'Parchment' 
                AND inventoryCharacterId = ?
                AND inventoryItemId = ?");
                $itemInventoryQuery->execute([$characterId, $itemId]);
                $itemInventoryRow = $itemInventoryQuery->rowCount();
                
                // Si le personnage possède cet objet
                if ($itemInventoryRow == 1) 
                {
                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($itemInventory = $itemInventoryQuery->fetch())
                    {
                        // On récupère les informations de l'objet
                        $itemId = $itemInventory['itemId'];
                        $itemTypeName = $itemInventory['itemTypeName'];
                        $itemTypeNameShow = $itemInventory['itemTypeNameShow'];
                        $itemPicture = $itemInventory['itemPicture'];
                        $itemName = $itemInventory['itemName'];
                        $itemDescription = $itemInventory['itemDescription'];
                        $itemQuantity = $itemInventory['inventoryQuantity'];
                        $itemHpEffect = $itemInventory['itemHpEffect'];
                        $itemMpEffect = $itemInventory['itemMpEffect'];
                        $itemStrengthEffect = $itemInventory['itemStrengthEffect'];
                        $itemMagicEffect = $itemInventory['itemMagicEffect'];
                        $itemAgilityEffect = $itemInventory['itemAgilityEffect'];
                        $itemDefenseEffect = $itemInventory['itemDefenseEffect'];
                        $itemDefenseMagicEffect = $itemInventory['itemDefenseMagicEffect'];
                        $itemWisdomEffect = $itemInventory['itemWisdomEffect'];
                        $itemProspectingEffect = $itemInventory['itemProspectingEffect'];
                        $itemSalePrice = $itemInventory['itemSalePrice'];
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
                                    <?php echo htmlspecialchars($itemName); ?>
                                </td>
                            </tr>
                                
                            <tr>
                                <td>
                                    Description
                                </td>
                                
                                <td>
                                    <?php echo nl2br(htmlspecialchars($itemDescription)); ?>
                                </td>
                            </tr>
                                
                            <tr>
                                <td>
                                    Quantité
                                </td>
                                
                                <td>
                                    <?php echo htmlspecialchars($itemQuantity); ?>
                                </td>
                            </tr>
                                
                            <tr>
                                <td>
                                    Effet(s)
                                </td>
                                
                                <td>
                                <?php
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
                                        echo "+ " . htmlspecialchars($itemDefenseMagicEffect) . " Défense Magique<br />";
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
                                    ?>
                                </td>
                            </tr>
                                
                            <tr>
                                <td>
                                    Prix de vente
                                </td>
                                
                                <td>
                                    <?php echo htmlspecialchars($itemSalePrice); ?>
                                </td>
                            </tr>
                                
                            <tr>
                                <td>
                                    Actions
                                </td>
                                
                                <td>
                                    <form method="POST" action="useParchment.php">
                                        <input type="hidden" name="itemId" value="<?php echo htmlspecialchars($itemId)?>">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                                        <input type="submit" class="btn btn-secondary btn-lg" name="use" value="Utiliser">
                                    </form>
                                    <form method="POST" action="../../modules/inventory/sale.php">
                                        Quantité à vendre : <input type="number" name="saleQuantity" value="1" class="form-control" required>
                                        <input type="hidden" name="itemId" value="<?php echo htmlspecialchars($itemId)?>">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                                        <input type="submit" class="btn btn-secondary btn-lg" name="sale" value="Vendre">
                                    </form>
                                </td>
                            </tr>
                        </table>
                                    
                        <hr>
            
                        <form method="POST" action="index.php">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                            <input type="submit" class="btn btn-secondary btn-lg" value="Retour">
                        </form>
                        
                        <?php
                    }
                }
                // Si le joueur ne possède pas ce parchemin
                else
                {
                    echo "Erreur : Impossible de visualiser un parchemin que vous ne possédez pas.";
                }
                $itemInventoryQuery->closeCursor();
            }
            // Si le parchemin n'existe pas
            else
            {
                echo "Erreur : Ce parchemin n'existe pas";
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
