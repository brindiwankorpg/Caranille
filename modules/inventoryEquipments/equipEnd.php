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
&& isset($_POST['finalEquip']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;

		// Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['itemId'])
        && $_POST['itemId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $itemId = $_POST['itemId'];

            // On cherche à savoir si l'équipement que l'on va équiper appartient bien au joueur et qu'il s'agit bien d'un équipement
            $itemQuery = $bdd->prepare("SELECT * FROM car_items, car_items_types, car_inventory
            WHERE itemItemTypeId = itemTypeId
            AND itemId = inventoryItemId
            AND (itemTypeName = 'Armor'
            OR itemTypeName = 'Boots'
            OR itemTypeName = 'Gloves'
            OR itemTypeName = 'Helmet'
            OR itemTypeName = 'Weapon')
            AND inventoryCharacterId = ?
            AND itemId = ?");
            $itemQuery->execute([$characterId, $itemId]);
            $itemRow = $itemQuery->rowCount();

            // Si le joueur possède cet équipement
            if ($itemRow == 1)
            {
                // On récupère les informations de l'équipement
                while ($item = $itemQuery->fetch())
                {
                    // On récupère les informations de l'équipement
                    $inventoryId = $item['inventoryId'];
                    $itemclasseId = $item['itemclasseId'];
                    $itemItemTypeId = $item['itemItemTypeId'];
                    $itemLevelRequired = $item['itemLevelRequired'];
                    $itemName = $item['itemName'];
                }

                // On vérifie si la classe du joueur lui permet de s'équiper de cet équipement, ou si celui-ci est pour toutes les classes
                if ($characterclasseId == $itemclasseId || $itemclasseId == 0)
                {
                    // Si le niveau du joueur est supérieur ou égal à celui du niveau requis
                    if ($characterLevel >= $itemLevelRequired)
                    {
                        // On va vérifier si ce type d'équipement est déjà équipé
                        $equipmentQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory
                        WHERE itemId = inventoryItemId
                        AND itemItemTypeId = ?
                        AND inventoryCharacterId = ?
                        AND inventoryEquipped = 1");
                        $equipmentQuery->execute([$itemItemTypeId, $characterId]);
                        $equipmentRow = $equipmentQuery->rowCount();

                        // Si un autre équipement de ce type est déjà équipé on va le rendre non équipé
                        if ($equipmentRow > 0)
                        {
                            while ($equipment = $equipmentQuery->fetch())
                            {
                                // On récupère les informations de l'inventaire
                                $inventoryId = $equipment['inventoryId'];

                                // On rend l'objet non équipé
                                $updateInventory = $bdd->prepare("UPDATE car_inventory SET
                                inventoryEquipped = 0
                                WHERE inventoryId = :inventoryId");
                                $updateInventory->execute(array(
                                'inventoryId' => $inventoryId));
                                $updateInventory->closeCursor();
                            }
                        }
                        $equipmentQuery->closeCursor();

                        // On équipe maintenant l'équipement choisi
                        $updateInventory = $bdd->prepare("UPDATE car_inventory SET
                        inventoryEquipped = 1
                        WHERE inventoryItemId = :itemId
                        AND inventoryCharacterId = :characterId");
                        $updateInventory->execute(array(
                        'itemId' => $itemId,
                        'characterId' => $characterId));
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

                        // Initialisation des variables qui vont contenir les bonus de tous les équipements actuellement équipés
                        $hpBonus = 0;
                        $mpBonus = 0;
                        $strengthBonus = 0;
                        $magicBonus = 0;
                        $agilityBonus = 0;
                        $defenseBonus = 0;
                        $defenseMagicBonus = 0;
                        $wisdomBonus = 0;
                        $prospectingBonus = 0;

                        // On va maintenant faire une requête sur tous les équipements que possède le joueur et qui sont équipés pour rajouter les bonus
                        $equipmentEquipedQuery = $bdd->prepare("SELECT * FROM car_items, car_inventory
                        WHERE itemId = inventoryItemId
                        AND inventoryEquipped = 1
                        AND inventoryCharacterId = ?");
                        $equipmentEquipedQuery->execute([$characterId]);

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
                        ?>

                        L'équipement <?php echo htmlspecialchars($itemName)?> est maintenant équipé

                        <hr>

                        <form method="POST" action="index.php">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                            <input type="submit" class="btn btn-secondary btn-lg" value="Retour">
                        </form>

                        <?php
                    }
                    // Si le niveau du joueur n'est pas supérieur ou égal à celui du niveau requis
                    else
                    {
                        ?>

                        Votre niveau est trop faible pour vous équiper de cet objet

                        <hr>

                        <form method="POST" action="index.php">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                            <input type="submit" class="btn btn-secondary btn-lg" value="Retour">
                        </form>

                        <?php
                    }
                }
                // Si la classe de l'objet est incompatible avec celle du joueur
                else
                {
                    ?>

                    Votre classe ne vous permet pas de vous équiper de cet équipement

                    <hr>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                        <input type="submit" class="btn btn-secondary btn-lg" value="Retour">
                    </form>

                    <?php
                }
            }
            // Si le joueur ne possède pas cet équipement
            else
            {
                echo "Erreur : Vous ne possédez pas cet équipement";
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
