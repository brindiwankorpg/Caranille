<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session, c'est que le joueur n'est pas connecté, alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminItemId']) 
&& isset($_POST['adminItemItemTypeId'])
&& isset($_POST['adminItemclasseId'])
&& isset($_POST['adminItemPicture'])
&& isset($_POST['adminItemName'])
&& isset($_POST['adminItemDescription'])
&& isset($_POST['adminItemLevel'])
&& isset($_POST['adminItemLevelRequired'])
&& isset($_POST['adminItemHpEffects'])
&& isset($_POST['adminItemMpEffect'])
&& isset($_POST['adminItemStrengthEffect'])
&& isset($_POST['adminItemMagicEffect'])
&& isset($_POST['adminItemAgilityEffect'])
&& isset($_POST['adminItemDefenseEffect'])
&& isset($_POST['adminItemDefenseMagicEffect'])
&& isset($_POST['adminItemWisdomEffect'])
&& isset($_POST['adminItemProspectingEffect'])
&& isset($_POST['adminItemPurchasePrice'])
&& isset($_POST['adminItemSalePrice'])
&& isset($_POST['token'])
&& isset($_POST['finalEdit']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminItemId']) 
        && ctype_digit($_POST['adminItemItemTypeId'])
        && ctype_digit($_POST['adminItemclasseId'])
        && ctype_digit($_POST['adminItemLevel'])
        && ctype_digit($_POST['adminItemLevelRequired'])
        && ctype_digit($_POST['adminItemHpEffects'])
        && ctype_digit($_POST['adminItemMpEffect'])
        && ctype_digit($_POST['adminItemStrengthEffect'])
        && ctype_digit($_POST['adminItemMagicEffect'])
        && ctype_digit($_POST['adminItemAgilityEffect'])
        && ctype_digit($_POST['adminItemDefenseEffect'])
        && ctype_digit($_POST['adminItemDefenseMagicEffect'])
        && ctype_digit($_POST['adminItemWisdomEffect'])
        && ctype_digit($_POST['adminItemProspectingEffect'])
        && ctype_digit($_POST['adminItemPurchasePrice'])
        && ctype_digit($_POST['adminItemSalePrice'])
        && $_POST['adminItemId'] >= 0
        && $_POST['adminItemItemTypeId'] >= 0
        && $_POST['adminItemclasseId'] >= 0
        && $_POST['adminItemLevel'] >= 0
        && $_POST['adminItemLevelRequired'] >= 0
        && $_POST['adminItemHpEffects'] >= 0
        && $_POST['adminItemMpEffect'] >= 0
        && $_POST['adminItemStrengthEffect'] >= 0
        && $_POST['adminItemMagicEffect'] >= 0
        && $_POST['adminItemAgilityEffect'] >= 0
        && $_POST['adminItemDefenseEffect'] >= 0
        && $_POST['adminItemDefenseMagicEffect'] >= 0
        && $_POST['adminItemWisdomEffect'] >= 0
        && $_POST['adminItemProspectingEffect'] >= 0
        && $_POST['adminItemPurchasePrice'] >= 0
        && $_POST['adminItemSalePrice'] >= 0)
        {
            // On récupère l'id du formulaire précédent
            $adminItemId = $_POST['adminItemId'];
            $adminItemItemTypeId = $_POST['adminItemItemTypeId'];
            $adminItemclasseId = $_POST['adminItemclasseId'];
            
            // On fait une requête pour vérifier si l'équipement choisi existe
            $itemQuery = $bdd->prepare("SELECT * FROM car_items 
            WHERE itemId = ?");
            $itemQuery->execute([$adminItemId]);
            $itemRow = $itemQuery->rowCount();
            $itemQuery->closeCursor();

            // Si l'équipement existe
            if ($itemRow == 1) 
            {
                // On fait une requête pour vérifier si le type d'équipement choisi existe
                $itemTypeQuery = $bdd->prepare("SELECT * FROM car_items_types
                WHERE itemTypeId = ?");
                $itemTypeQuery->execute([$adminItemItemTypeId]);
                $itemTypeRow = $itemTypeQuery->rowCount();
                $itemTypeQuery->closeCursor();
        
                // Si le type d'équipement existe
                if ($itemTypeRow == 1) 
                {
                    // Si la classe choisie est supérieure à zéro, c'est que l'équipement est dédié à une classe
                    if ($adminItemclasseId > 0)
                    {
                        // On fait une requête pour vérifier si la classe choisie existe
                        $classeQuery = $bdd->prepare("SELECT * FROM car_classes 
                        WHERE classeId = ?");
                        $classeQuery->execute([$adminItemclasseId]);
                        $classeRow = $classeQuery->rowCount();
                        $classeQuery->closeCursor();
                    }
                    // Si la classe choisie est égale à zéro, c'est qu'il s'agit d'un équipement pour toutes les classes
                    else
                    {
                        // On met $classeRow à 1 pour passer à la suite
                        $classeRow = 1;
                    }
        
                    // Si la classe est disponible ou que la classe est à zéro
                    if ($classeRow == 1) 
                    {
                        // On récupère les informations du formulaire
                        $adminItemId = $_POST['adminItemId'];
                        $adminItemItemTypeId = $_POST['adminItemItemTypeId'];
                        $adminItemclasseId = $_POST['adminItemclasseId'];
                        $adminItemPicture = $_POST['adminItemPicture'];
                        $adminItemName = $_POST['adminItemName'];
                        $adminItemDescription = $_POST['adminItemDescription'];
                        $adminItemLevel = $_POST['adminItemLevel'];
                        $adminItemLevelRequired = $_POST['adminItemLevelRequired'];
                        $adminItemHpEffects = $_POST['adminItemHpEffects'];
                        $adminItemMpEffect = $_POST['adminItemMpEffect'];
                        $adminItemStrengthEffect = $_POST['adminItemStrengthEffect'];
                        $adminItemMagicEffect = $_POST['adminItemMagicEffect'];
                        $adminItemAgilityEffect = $_POST['adminItemAgilityEffect'];
                        $adminItemDefenseEffect = $_POST['adminItemDefenseEffect'];
                        $adminItemDefenseMagicEffect = $_POST['adminItemDefenseMagicEffect'];
                        $adminItemWisdomEffect = $_POST['adminItemWisdomEffect'];
                        $adminItemProspectingEffect = $_POST['adminItemProspectingEffect'];
                        $adminItemPurchasePrice = $_POST['adminItemPurchasePrice'];
                        $adminItemSalePrice = $_POST['adminItemSalePrice'];
        
                        // On met à jour l'équipement dans la base de données
                        $updateItems = $bdd->prepare("UPDATE car_items 
                        SET itemItemTypeId = :adminItemItemTypeId, 
                        itemclasseId = :adminItemclasseId, 
                        itemPicture = :adminItemPicture,
                        itemName = :adminItemName,
                        itemDescription = :adminItemDescription,
                        itemLevel = :adminItemLevel,
                        itemLevelRequired = :adminItemLevelRequired,
                        itemHpEffect = :adminItemHpEffects,
                        itemMpEffect = :adminItemMpEffect,
                        itemStrengthEffect = :adminItemStrengthEffect,
                        itemMagicEffect = :adminItemMagicEffect,
                        itemAgilityEffect = :adminItemAgilityEffect,
                        itemDefenseEffect = :adminItemDefenseEffect,
                        itemDefenseMagicEffect = :adminItemDefenseMagicEffect,
                        itemWisdomEffect = :adminItemWisdomEffect,
                        itemProspectingEffect = :adminItemProspectingEffect,
                        itemPurchasePrice = :adminItemPurchasePrice,
                        itemSalePrice = :adminItemSalePrice
                        WHERE itemId = :adminItemId");
                        $updateItems->execute([
                        'adminItemItemTypeId' => $adminItemItemTypeId,
                        'adminItemclasseId' => $adminItemclasseId,
                        'adminItemPicture' => $adminItemPicture,
                        'adminItemName' => $adminItemName,
                        'adminItemDescription' => $adminItemDescription,
                        'adminItemLevel' => $adminItemLevel,
                        'adminItemLevelRequired' => $adminItemLevelRequired,
                        'adminItemHpEffects' => $adminItemHpEffects,
                        'adminItemMpEffect' => $adminItemMpEffect,
                        'adminItemStrengthEffect' => $adminItemStrengthEffect,
                        'adminItemMagicEffect' => $adminItemMagicEffect,
                        'adminItemAgilityEffect' => $adminItemAgilityEffect,
                        'adminItemDefenseEffect' => $adminItemDefenseEffect,
                        'adminItemDefenseMagicEffect' => $adminItemDefenseMagicEffect,
                        'adminItemWisdomEffect' => $adminItemWisdomEffect,
                        'adminItemProspectingEffect' => $adminItemProspectingEffect,
                        'adminItemPurchasePrice' => $adminItemPurchasePrice,
                        'adminItemSalePrice' => $adminItemSalePrice,
                        'adminItemId' => $adminItemId]);
                        $updateItems->closeCursor();
        
                        // On cherche à savoir quels sont les joueurs qui ont cet équipement pour leur modifier les bonus de cet équipement
                        $itemQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory 
                        WHERE itemId = inventoryItemId
                        AND inventoryEquipped = 1
                        AND itemId = ?");
                        $itemQuery->execute([$adminItemId]);
                        $itemRow = $itemQuery->rowCount();
        
                        // Si des joueurs en sont équipés
                        if ($itemRow > 0) 
                        {
                            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                            while ($item = $itemQuery->fetch())
                            {   
                                // On récupère les informations du personnage
                                $adminCharacterId = $item['inventoryCharacterId'];
                                
                                // On remet les statistiques du joueur à zéro pour recalculer ensuite le bonus de tous les équipements équipés
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
                                WHERE characterId = :adminCharacterId");
                                $updateCharacter->execute(array(
                                'adminCharacterId' => $adminCharacterId));
                                $updateCharacter->closeCursor();
                
                                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations et on additionne les bonus de tous les équipements actuellement équipés
                                $equipmentEquipedQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory 
                                WHERE itemId = inventoryItemId
                                AND inventoryEquipped = 1
                                AND inventoryCharacterId = ?");
                                $equipmentEquipedQuery->execute([$adminCharacterId]);

                                $hpBonus = 0;
                                $mpBonus = 0;
                                $strengthBonus = 0;
                                $magicBonus = 0;
                                $agilityBonus = 0;
                                $defenseBonus = 0;
                                $defenseMagicBonus = 0;
                                $wisdomBonus = 0;
                                $prospectingBonus = 0;
                
                                // On fait une boucle sur les résultats et on additionne les bonus de tous les équipements actuellement équipés
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
                
                                // On ajoute les bonus des statistiques au joueur
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
                                WHERE characterId = :adminCharacterId");
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
                                'adminCharacterId' => $adminCharacterId));
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
                                WHERE characterId = :adminCharacterId");
                                $updateCharacter->execute(['adminCharacterId' => $adminCharacterId]);
                                $updateCharacter->closeCursor();
                            }
                        }
                        $itemQuery->closeCursor();
                        ?>
        
                        L'équipement a bien été mis à jour
        
                        <hr>
                            
                        <form method="POST" action="index.php">
                            <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                        </form>
                        
                        <?php
                    }
                    // Si la classe choisie n'existe pas
                    else
                    {
                        echo "Erreur : La classe choisie n'existe pas";
                    }
                }
                else 
                {
                    echo "Erreur : Ce type d'objet n'existe pas";
                }
            }
            // Si l'équipement n'existe pas
            else
            {
                echo "Erreur : Équipement indisponible";
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

require_once("../html/footer.php");
