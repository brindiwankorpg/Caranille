<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['tradeId'])
&& isset($_POST['tradeItemId'])
&& isset($_POST['tradeItemQuantity'])
&& isset($_POST['token'])
&& isset($_POST['addTradeItemEnd']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;
		
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['tradeId'])
        && ctype_digit($_POST['tradeItemId'])
        && ctype_digit($_POST['tradeItemQuantity'])
        && $_POST['tradeId'] >= 0
        && $_POST['tradeItemId'] >= 1
        && $_POST['tradeItemQuantity'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $tradeId = $_POST['tradeId'];
            $tradeItemId = $_POST['tradeItemId'];
            $tradeItemQuantity = $_POST['tradeItemQuantity'];
            
            // On fait une requête pour vérifier si cette demande existe et est bien attribuée au joueur
            $tradeQuery = $bdd->prepare("SELECT * FROM car_trades
            WHERE (tradeCharacterOneId = ?
            OR tradeCharacterTwoId = ?)
            AND tradeId = ?");
            $tradeQuery->execute([$characterId, $characterId, $tradeId]);
            $tradeRow = $tradeQuery->rowCount();
            
            // Si cet échange existe et est attribué au joueur
            if ($tradeRow > 0) 
            {
                // On fait une requête pour vérifier si l'objet ou équipement choisi existe
                $itemQuery = $bdd->prepare("SELECT * FROM car_items 
                WHERE itemId = ?");
                $itemQuery->execute([$tradeItemId]);
                $itemRow = $itemQuery->rowCount();
        
                // Si l'objet ou l'équipement existe
                if ($itemRow == 1) 
                {
                    // On vérifie si le joueur possède bien cet objet ou équipement
                    $inventoryQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory 
                    WHERE itemId = inventoryItemId
                    AND inventoryCharacterId = ?
                    AND itemId = ?");
                    $inventoryQuery->execute([$characterId, $tradeItemId]);
                    $inventoryRow = $inventoryQuery->rowCount();
        
                    // Si l'objet ou équipement est bien dans l'inventaire du joueur
                    if ($inventoryRow == 1) 
                    {
                        // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                        while ($inventory = $inventoryQuery->fetch())
                        {
                            // On récupère les informations de l'objet
                            $inventoryId = $inventory['inventoryId'];
                            $itemQuantity = $inventory['inventoryQuantity'];
                            $itemName = $inventory['itemName'];
                            $itemSalePrice = $inventory['itemSalePrice'];
                            $inventoryEquipped = $inventory['inventoryEquipped'];
                        }
                        
                        // Si la quantité d'objets dans l'inventaire du joueur est supérieure ou égale au nombre à mettre en échange
                        if ($itemQuantity >= $tradeItemQuantity)
                        {
                            // Si le joueur possède plusieurs exemplaire de cet équipement/objet
                            if ($itemQuantity > $tradeItemQuantity)
                            {
                                // On met l'inventaire à jour
                                $updateInventory = $bdd->prepare("UPDATE car_inventory SET
                                inventoryQuantity = inventoryQuantity - :itemQuantity
                                WHERE inventoryId = :inventoryId");
                                $updateInventory->execute(array(
                                'itemQuantity' => $tradeItemQuantity,
                                'inventoryId' => $inventoryId));
                                $updateInventory->closeCursor();                
                            }
                            // Si le joueur ne possède cet équipement/objet dans le même nombre d'exemplaire que ce qu'il souhaite mettre en échange
                            else if ($itemQuantity == $tradeItemQuantity)
                            {
                                // Si l'équipement est équipé et que c'est le seul exemplaire du joueur
                                if ($inventoryEquipped == 1)
                                {
                                    // On va donc rendre l'objet non équipé afin de le mettre en vente
                                    $updateInventory = $bdd->prepare("UPDATE car_inventory SET
                                    inventoryEquipped = 0
                                    WHERE inventoryId = :inventoryId");
                                    $updateInventory->execute(array(
                                    'inventoryId' => $inventoryId));
                                    $updateInventory->closeCursor();
            
                                    // On remet les stats du joueur à zéro pour recalculer ensuite le bonus de tous les équipements équipés
                                    $updateCharacter = $bdd->prepare("UPDATE car_characters SET
                                    characterHpEquipments = 0,
                                    characterMpEquipments = 0, 
                                    characterStrengthEquipments = 0, 
                                    characterMagicEquipments = 0, 
                                    characterAgilityEquipments = 0, 
                                    characterDefenseEquipments = 0, 
                                    characterDefenseMagicEquipments = 0, 
                                    characterWisdomEquipments = 0,
                                    characterProspectingEquipments = 0
                                    WHERE characterId = :characterId");
                                    $updateCharacter->execute(array(
                                    'characterId' => $characterId));
                                    $updateCharacter->closeCursor();
            
                                    // Initialisation des variables qui vont contenir les bonus de tous les équipements équipés
                                    $hpBonus = 0;
                                    $mpBonus = 0;
                                    $strengthBonus = 0;
                                    $magicBonus = 0;
                                    $agilityBonus = 0;
                                    $defenseBonus = 0;
                                    $defenseMagicBonus = 0;
                                    $wisdomBonus = 0;
                                    $prospectingBonus = 0;
            
                                    // On va maintenant faire une requête sur tous les équipements que possède le joueur pour rajouter les bonus
                                    $equipmentEquipedQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory 
                                    WHERE itemId = inventoryItemId
                                    AND inventoryEquipped = 1
                                    AND inventoryCharacterId = ?");
                                    $equipmentEquipedQuery->execute([$characterId]);
            
                                    // On fait une boucle sur les résultats et on additionne les bonus de tous les équipements équipés
                                    while ($equipment = $equipmentEquipedQuery->fetch())
                                    {
                                        // On récupère les informations de l'équipement
                                        $hpBonus = $hpBonus + $equipment['itemHpEffect'];
                                        $mpBonus = $mpBonus + $equipment['itemMpEffect'];
                                        $strengthBonus = $strengthBonus + $equipment['itemStrengthEffect'];
                                        $magicBonus = $magicBonus + $equipment['itemMagicEffect'];
                                        $agilityBonus = $agilityBonus + $equipment['itemAgilityEffect'];
                                        $defenseBonus = $defenseBonus + $equipment['itemDefenseEffect'];
                                        $defenseMagicBonus = $defenseMagicBonus + $equipment['itemDefenseMagicEffect'];
                                        $wisdomBonus = $wisdomBonus + $equipment['itemWisdomEffect'];
                                        $prospectingBonus = $prospectingBonus + $equipment['itemProspectingEffect'];
                                    }
                                    $equipmentEquipedQuery->closeCursor();
            
                                    // On ajoute les bonus des stats au joueur
                                    $updateCharacter = $bdd->prepare("UPDATE car_characters SET
                                    characterHpEquipments = :hpBonus,
                                    characterMpEquipments = :mpBonus, 
                                    characterStrengthEquipments = :strengthBonus, 
                                    characterMagicEquipments = :magicBonus, 
                                    characterAgilityEquipments = :agilityBonus, 
                                    characterDefenseEquipments = :defenseBonus, 
                                    characterDefenseMagicEquipments = :defenseMagicBonus, 
                                    characterWisdomEquipments = :wisdomBonus,
                                    characterProspectingEquipments = :prospectingBonus
                                    WHERE characterId = :characterId");
                                    $updateCharacter->execute(array(
                                    'hpBonus' => $hpBonus,
                                    'mpBonus' => $mpBonus,
                                    'strengthBonus' => $strengthBonus,
                                    'magicBonus' => $magicBonus,
                                    'agilityBonus' => $agilityBonus,
                                    'defenseBonus' => $defenseBonus,
                                    'defenseMagicBonus' => $defenseMagicBonus,
                                    'wisdomBonus' => $wisdomBonus,
                                    'prospectingBonus' => $prospectingBonus,
                                    'characterId' => $characterId));
                                    $updateCharacter->closeCursor();
            
                                    // On va maintenant finir par actualiser tout le personnage
                                    $updateCharacter = $bdd->prepare("UPDATE car_characters
                                    SET characterHpTotal = characterHpMax + characterHpSkillPoints + characterHpBonus + characterHpEquipments + characterHpGuild,
                                    characterMpTotal = characterMpMax + characterMpSkillPoints + characterMpBonus + characterMpEquipments + characterMpGuild,
                                    characterStrengthTotal = characterStrength + characterStrengthSkillPoints + characterStrengthBonus + characterStrengthEquipments + characterStrengthGuild,
                                    characterMagicTotal = characterMagic + characterMagicSkillPoints + characterMagicBonus + characterMagicEquipments + characterMagicGuild,
                                    characterAgilityTotal = characterAgility + characterAgilitySkillPoints + characterAgilityBonus + characterAgilityEquipments + characterAgilityGuild,
                                    characterDefenseTotal = characterDefense + characterDefenseSkillPoints + characterDefenseBonus + characterDefenseEquipments + characterDefenseGuild,
                                    characterDefenseMagicTotal = characterDefenseMagic + characterDefenseMagicSkillPoints + characterDefenseMagicBonus + characterDefenseMagicEquipments + characterDefenseMagicGuild,
                                    characterWisdomTotal = characterWisdom + characterWisdomSkillPoints + characterWisdomBonus + characterWisdomEquipments + characterWisdomGuild,
                                    characterProspectingTotal = characterProspecting + characterProspectingSkillPoints + characterProspectingBonus + characterProspectingEquipments + characterProspectingGuild
                                    WHERE characterId = :characterId");
                                    $updateCharacter->execute(['characterId' => $characterId]);
                                    $updateCharacter->closeCursor();
                                }

                                // On supprime l'objet de l'inventaire
                                $updateInventory = $bdd->prepare("DELETE FROM car_inventory
                                WHERE inventoryId = :inventoryId");
                                $updateInventory->execute(array(
                                'inventoryId' => $inventoryId));
                                $updateInventory->closeCursor();
                            }
                            
                            // On vérifie si le joueur a déjà mis cet objet dans l'échange
                            $tradeItemQuery = $bdd->prepare("SELECT * FROM car_trades_items
                            WHERE tradeItemTradeId = ?
                            AND tradeItemItemId = ?
                            AND tradeItemCharacterId = ?");
                            $tradeItemQuery->execute([$tradeId, $tradeItemId, $characterId]);
                            $tradeItemRow = $tradeItemQuery->rowCount();
                            $tradeItemQuery->closeCursor();

                            // Si le joueur a déjà mis cet objet dans l'échange on augmente simplement sa quantité
                            if ($tradeItemRow > 0)
                            {
                                // On met la quantité de l'objet dans l'échange à jour (LIMIT 1 pour ne modifier qu'une seule ligne)
                                $updateTradeItem = $bdd->prepare("UPDATE car_trades_items SET
                                tradeItemItemQuantity = tradeItemItemQuantity + :tradeItemQuantity
                                WHERE tradeItemTradeId = :tradeId
                                AND tradeItemItemId = :tradeItemId
                                AND tradeItemCharacterId = :tradeCharacterId
                                LIMIT 1");
                                $updateTradeItem->execute(array(
                                'tradeItemQuantity' => $tradeItemQuantity,
                                'tradeId' => $tradeId,
                                'tradeItemId' => $tradeItemId,
                                'tradeCharacterId' => $characterId));
                                $updateTradeItem->closeCursor();
                            }
                            // Si le joueur n'a pas encore mis cet objet dans l'échange on l'ajoute
                            else
                            {
                                // On ajoute l'objet dans l'échange
                                $addTradeItem = $bdd->prepare("INSERT INTO car_trades_items VALUES(
                                NULL,
                                :tradeId,
                                :tradeItemId,
                                :tradeCharacterId,
                                :tradeItemQuantity)");
                                $addTradeItem->execute(array(
                                'tradeId' => $tradeId,
                                'tradeItemId' => $tradeItemId,
                                'tradeCharacterId' => $characterId,
                                'tradeItemQuantity' => $tradeItemQuantity));
                                $addTradeItem->closeCursor();
                            }
                            
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
                            ?>
                            
                            L'objet/équipement <?php echo htmlspecialchars($itemName)?> a bien été ajouté à l'échange en <?php echo htmlspecialchars($tradeItemQuantity)?> exemplaire(s).
                            
                            <hr>
                
                            <form method="POST" action="index.php">
                                <input type="submit" class="btn btn-secondary btn-lg" name="manage" value="Retour">
                            </form>
                            
                            <?php
                        }
                        else
                        {
                            ?>

                                <?php echo "Vous n'avez pas assez d'exemplaire de cet équipement/objet pour l'ajouter"; ?>

                                <hr>
            
                                <form method="POST" action="index.php">
                                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                                </form>

                            <?php
                        }
                    }
                    // Si le joueur ne possède pas cet objet ou équipement
                    else
                    {
                        echo "Erreur : Vous ne pouvez pas mettre en échange un objet ou équipement que vous ne possédez pas";
                    }
                    $inventoryQuery->closeCursor();
                }
                // Si l'objet ou équipement n'existe pas
                else
                {
                    echo "Erreur : Cet objet ou équipement n'existe pas";
                }
                $itemQuery->closeCursor();
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
