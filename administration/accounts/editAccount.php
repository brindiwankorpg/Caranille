<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminAccountId'])
&& isset($_POST['token'])
&& isset($_POST['edit']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminAccountId'])
        && $_POST['adminAccountId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $adminAccountId = $_POST['adminAccountId'];

            // On fait une requête pour vérifier si le compte choisi existe
            $accountQuery = $bdd->prepare("SELECT * FROM car_accounts 
            WHERE accountId = ?");
            $accountQuery->execute([$adminAccountId]);
            $accountRow = $accountQuery->rowCount();

            // Si le compte existe
            if ($accountRow == 1) 
            {
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($account = $accountQuery->fetch())
                {
                    // On récupère les informations du compte
                    $adminAccountId = $account['accountId'];
                    $adminAccountPseudo = $account['accountPseudo'];
                    $adminAccountEmail = $account['accountEmail'];
                    $adminAccountAccess = $account['accountAccess'];
                }

                // On récupère le personnage pour l'afficher dans le menu d'information du personnage
                $characterQuery = $bdd->prepare("SELECT * FROM car_characters
                WHERE characterAccountId = ?");
                $characterQuery->execute([$adminAccountId]);
                
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($character = $characterQuery->fetch())
                {
                    // On récupère toutes les informations du personnage
                    $adminCharacterId = $character['characterId'];
                    $adminCharacterAccountId = $character['characterAccountId'];
                    $adminCharacterGuildId = $character['characterGuildId'];
                    $adminCharacterclasseId = $character['characterclasseId'];
                    $adminCharacterPlaceId = $character['characterPlaceId'];
                    $adminCharacterPicture = $character['characterPicture'];
                    $adminCharacterName = $character['characterName'];
                    $adminCharacterLevel = $character['characterLevel'];
                    $adminCharacterSex = $character['characterSex'];
                    $adminCharacterHpMin = $character['characterHpMin'];
                    $adminCharacterHpMax = $character['characterHpMax'];
                    $adminCharacterHpSkillPoints = $character['characterHpSkillPoints'];
                    $adminCharacterHpBonus = $character['characterHpBonus'];
                    $adminCharacterHpEquipments = $character['characterHpEquipments'];
                    $adminCharacterHpGuild = $character['characterHpGuild'];
                    $adminCharacterHpTotal = $character['characterHpTotal'];
                    $adminCharacterMpMin = $character['characterMpMin'];
                    $adminCharacterMpMax = $character['characterMpMax'];
                    $adminCharacterMpSkillPoints = $character['characterMpSkillPoints'];
                    $adminCharacterMpBonus = $character['characterMpBonus'];
                    $adminCharacterMpEquipments = $character['characterMpEquipments'];
                    $adminCharacterMpGuild = $character['characterMpGuild'];
                    $adminCharacterMpTotal = $character['characterMpTotal'];
                    $adminCharacterStrength = $character['characterStrength'];
                    $adminCharacterStrengthSkillPoints = $character['characterStrengthSkillPoints'];
                    $adminCharacterStrengthBonus = $character['characterStrengthBonus'];
                    $adminCharacterStrengthEquipments = $character['characterStrengthEquipments'];
                    $adminCharacterStrengthGuild = $character['characterStrengthGuild'];
                    $adminCharacterStrengthTotal = $character['characterStrengthTotal'];
                    $adminCharacterMagic = $character['characterMagic'];
                    $adminCharacterMagicSkillPoints = $character['characterMagicSkillPoints'];
                    $adminCharacterMagicBonus = $character['characterMagicBonus'];
                    $adminCharacterMagicEquipments = $character['characterMagicEquipments'];
                    $adminCharacterMagicGuild = $character['characterMagicGuild'];
                    $adminCharacterMagicTotal = $character['characterMagicTotal'];
                    $adminCharacterAgility = $character['characterAgility'];
                    $adminCharacterAgilitySkillPoints = $character['characterAgilitySkillPoints'];
                    $adminCharacterAgilityBonus = $character['characterAgilityBonus'];
                    $adminCharacterAgilityEquipments = $character['characterAgilityEquipments'];
                    $adminCharacterAgilityGuild = $character['characterAgilityGuild'];
                    $adminCharacterAgilityTotal = $character['characterAgilityTotal'];
                    $adminCharacterDefense = $character['characterDefense'];
                    $adminCharacterDefenseSkillPoints = $character['characterDefenseSkillPoints'];
                    $adminCharacterDefenseBonus = $character['characterDefenseBonus'];
                    $adminCharacterDefenseEquipment = $character['characterDefenseEquipments'];
                    $adminCharacterDefenseGuild = $character['characterDefenseGuild'];
                    $adminCharacterDefenseTotal = $character['characterDefenseTotal'];
                    $adminCharacterDefenseMagic = $character['characterDefenseMagic'];
                    $adminCharacterDefenseMagicSkillPoints = $character['characterDefenseMagicSkillPoints'];
                    $adminCharacterDefenseMagicBonus = $character['characterDefenseMagicBonus'];
                    $adminCharacterDefenseMagicEquipments = $character['characterDefenseMagicEquipments'];
                    $adminCharacterDefenseMagicGuild = $character['characterDefenseMagicGuild'];
                    $adminCharacterDefenseMagicTotal = $character['characterDefenseMagicTotal'];
                    $adminCharacterWisdom = $character['characterWisdom'];
                    $adminCharacterWisdomSkillPoints = $character['characterWisdomSkillPoints'];
                    $adminCharacterWisdomBonus = $character['characterWisdomBonus'];
                    $adminCharacterWisdomEquipments = $character['characterWisdomEquipments'];
                    $adminCharacterProspecting = $character['characterProspecting'];
                    $adminCharacterProspectingSkillPoints = $character['characterProspectingSkillPoints'];
                    $adminCharacterProspectingBonus = $character['characterProspectingBonus'];
                    $adminCharacterProspectingEquipments = $character['characterProspectingEquipments'];
                    $adminCharacterProspectingGuild = $character['characterProspectingGuild'];
                    $adminCharacterProspectingTotal = $character['characterProspectingTotal'];
                    $adminCharacterWisdomGuild = $character['characterWisdomGuild'];
                    $adminCharacterWisdomTotal = $character['characterWisdomTotal'];
                    $adminCharacterDefeate = $character['characterArenaDefeate'];
                    $adminCharacterVictory = $character['characterArenaVictory'];
                    $adminCharacterExperience = $character['characterExperience'];
                    $adminCharacterExperienceTotal = $character['characterExperienceTotal'];
                    $adminCharacterSkillPoints = $character['characterSkillPoints'];
                    $adminCharacterGold = $character['characterGold'];
                    $adminCharacterChapter = $character['characterChapter'];
                    $adminCharacterOnBattle = $character['characterOnBattle'];
                    $adminCharacterEnable = $character['characterEnable'];
                }
                $characterQuery->closeCursor();

                // On récupère la classe du personnage pour l'afficher dans le menu d'information du personnage
                $classeQuery = $bdd->prepare("SELECT * FROM car_classes
                WHERE classeId = ?");
                $classeQuery->execute([$adminCharacterclasseId]);

                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($classe = $classeQuery->fetch())
                {
                    // On récupère le nom de la classe du personnage
                    $adminclasseName = $classe['classeName'];
                }
                $classeQuery->closeCursor();

                // Si adminCharacterPlaceId à un Id supérieur à zéro c'est que le joueur est dans un lieu
                if ($adminCharacterPlaceId > 0)
                {
                    // On récupère le lieu du personnage pour l'afficher dans le menu d'information du personnage
                    $placeQuery = $bdd->prepare("SELECT * FROM car_places
                    WHERE placeId = ?");
                    $placeQuery->execute([$adminCharacterPlaceId]);

                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($place = $placeQuery->fetch())
                    {
                        // On récupère le nom du lieu où se situe le personnage
                        $adminplaceName = $place['placeName'];
                    }
                    $placeQuery->closeCursor();
                }
                // Si adminCharacterPlaceId à un Id à zéro c'est que le joueur est sur la carte du monde
                else
                {
                    // On met Carte du monde comme nom de lieu au personnage
                    $adminplaceName = "Carte du monde";
                }

                // On récupère les équipements équipé du personnage pour les afficher dans le menu d'information du personnage
                $equipmentEquipedQuery = $bdd->prepare("SELECT * FROM car_items, car_items_types, car_inventory 
                WHERE itemItemTypeId = itemTypeId
                AND itemId = inventoryItemId
                AND inventoryEquipped = 1
                AND inventoryCharacterId = ?");
                $equipmentEquipedQuery->execute([$adminCharacterId]);

                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations et on vérifie le type d'équipement
                while ($equipment = $equipmentEquipedQuery->fetch())
                {
                    switch ($equipment['itemTypeName'])
                    {
                        // S'il s'agit d'une armure
                        case "Armor":
                            $adminEquipmentArmorId = $equipment['itemId'];
                            $adminEquipmentArmorName = $equipment['itemName'];
                            $adminEquipmentArmorDescription = $equipment['itemDescription'];
                        break;

                        // S'il s'agit de bottes
                        case "Boots":
                            $adminEquipmentBootsId = $equipment['itemId'];
                            $adminEquipmentBootsName = $equipment['itemName'];
                            $adminEquipmentBootsDescription = $equipment['itemDescription'];
                        break;

                        // S'il s'agit de gants
                        case "Gloves":
                            $adminEquipmentGlovesId = $equipment['itemId'];
                            $adminEquipmentGlovesName = $equipment['itemName'];
                            $adminEquipmentGlovesDescription = $equipment['itemDescription'];
                        break;

                        // S'il s'agit d'un casque
                        case "Helmet":
                            $adminEquipmentHelmetId = $equipment['itemId'];
                            $adminEquipmentHelmetName = $equipment['itemName'];
                            $adminEquipmentHelmetDescription = $equipment['itemDescription'];
                        break;

                        // S'il s'agit d'une arme
                        case "Weapon":
                            $adminEquipmentWeaponId = $equipment['itemId'];
                            $adminEquipmentWeaponName = $equipment['itemName'];
                            $adminEquipmentWeaponDescription = $equipment['itemDescription'];
                        break;
                    }
                }
                $equipmentEquipedQuery->closeCursor();

                // Si la variable $equipmentArmorId existe pas c'est que le personnage n'en est pas équipé
                if (!isset($adminEquipmentArmorId))
                {
                    $adminEquipmentArmorId = 0;
                    $adminEquipmentArmorName = "Vide";
                    $adminEquipmentArmorDescription = "";
                }

                // Si la variable $equipmentBootsId existe pas c'est que le personnage n'en est pas équipé
                if (!isset($adminEquipmentBootsId))
                {
                    $adminEquipmentBootsId = 0;
                    $adminEquipmentBootsName = "Vide";
                    $adminEquipmentBootsDescription = "";
                }

                // Si la variable $equipmentGlovesId existe pas c'est que le personnage n'en est pas équipé
                if (!isset($adminEquipmentGlovesId))
                {
                    $adminEquipmentGlovesId = 0;
                    $adminEquipmentGlovesName = "Vide";
                    $adminEquipmentGlovesDescription = "";
                }

                // Si la variable $equipmentHelmetId existe pas c'est que le personnage n'en est pas équipé
                if (!isset($adminEquipmentHelmetId))
                {
                    $adminEquipmentHelmetId = 0;
                    $adminEquipmentHelmetName = "Vide";
                    $adminEquipmentHelmetDescription = "";
                }

                // Si la variable $equipmentWeaponId existe pas c'est que le personnage n'en est pas équipé
                if (!isset($adminEquipmentWeaponId))
                {
                    $adminEquipmentWeaponId = 0;
                    $adminEquipmentWeaponName = "Vide";
                    $adminEquipmentWeaponDescription = "";
                }

                // On va déterminer le sexe du personnage
                switch ($adminCharacterSex)
                {
                    // Si le sexe du personnage est 0 c'est sexe féminin
                    case 0:
                        $adminCharacterSexName = "Féminin";
                    break;

                    // Si le sexe du personnage est 1 c'est sexe masculin
                    case 1:
                        $adminCharacterSexName = "Masculin";
                    break;
                }

                // On va déterminer si le personnage est en combat ou pas
                switch ($adminCharacterOnBattle)
                {
                    // Si OnBattle du personnage est 0 il est hors combat
                    case 0:
                        $adminCharacterOnBattleName = "Non";
                    break;

                    // Si OnBattle du personnage est 1 il est en combat
                    case 1:
                        $adminCharacterOnBattleName = "Oui";
                    break;
                }
                ?>

                <p>Informations du compte</p>
                
                <form method="POST" action="editAccountEnd.php">
                    Pseudo : <?php echo htmlspecialchars($adminAccountPseudo)?> <br />
                    Email : <?php echo htmlspecialchars($adminAccountEmail)?> <br />
                    Accès : <select name="adminAccountAccess" class="form-control">

                    <?php
                    switch ($adminAccountAccess)
                    {
                        case 0:
                            ?>
                            
                            <option selected="selected" value="0">Joueur</option>
                            <option value="1">Modérateur</option>
                            <option value="2">Administrateur</option>
                            
                            <?php
                        break;

                        case 1:
                            ?>
                            
                            <option value="0">Joueur</option>
                            <option selected="selected" value="1">Modérateur</option>
                            <option value="2">Administrateur</option>
                            
                            <?php
                        break;

                        case 2:
                            ?>
                            
                            <option value="0">Joueur</option>
                            <option value="1">Modérateur</option>
                            <option selected="selected" value="2">Administrateur</option>
                            
                            <?php
                        break;
                    }
                    ?>
                    
                    </select>
                    <input type="hidden" name="adminAccountId" value="<?php echo htmlspecialchars($adminAccountId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input name="finalEdit" class="btn btn-secondary btn-lg" type="submit" value="Modifier">
                </form>

                <hr>

                <p><img src="<?php echo htmlspecialchars($adminCharacterPicture)?>" height="100" width="100"></p>

                <p>Informations du personnage</p>
                
                Classe : <?php echo htmlspecialchars($adminclasseName)?><br />
                Nom du personnage : <?php echo htmlspecialchars($adminCharacterName)?><br />
                Niveau du personnage : <?php echo htmlspecialchars($adminCharacterLevel)?><br />
                <?php echo htmlspecialchars($itemArmorNameShow)?> : <?php echo htmlspecialchars($adminEquipmentArmorName)?><br />
                <?php echo htmlspecialchars($itemBootsNameShow)?> : <?php echo htmlspecialchars($adminEquipmentBootsName)?><br />
                <?php echo htmlspecialchars($itemGlovesNameShow)?> : <?php echo htmlspecialchars($adminEquipmentGlovesName)?><br />
                <?php echo htmlspecialchars($itemHelmetNameShow)?> : <?php echo htmlspecialchars($adminEquipmentHelmetName)?><br />
                <?php echo htmlspecialchars($itemWeaponNameShow)?> : <?php echo htmlspecialchars($adminEquipmentWeaponName)?><br />
                Sexe du personnage : <?php echo htmlspecialchars($adminCharacterSexName)?><br />
                HP actuel : <?php echo htmlspecialchars($adminCharacterHpMin)?><br />
                HP maximum : <?php echo htmlspecialchars($adminCharacterHpMax)?><br />
                HP points de compétences : <?php echo htmlspecialchars($adminCharacterHpSkillPoints)?><br />
                HP bonus : <?php echo htmlspecialchars($adminCharacterHpBonus)?><br />
                HP équipement : <?php echo htmlspecialchars($adminCharacterHpEquipments)?><br />
                HP bonus guilde : <?php echo htmlspecialchars($adminCharacterHpGuild)?><br />
                HP total : <?php echo htmlspecialchars($adminCharacterHpTotal)?><br />
                MP actuel : <?php echo htmlspecialchars($adminCharacterMpMin)?><br />
                MP maximum : <?php echo htmlspecialchars($adminCharacterMpMax)?><br />
                MP points de compétences : <?php echo htmlspecialchars($adminCharacterMpSkillPoints)?><br />
                MP bonus : <?php echo htmlspecialchars($adminCharacterMpBonus)?><br />
                MP équipements : <?php echo htmlspecialchars($adminCharacterMpEquipments)?><br />
                MP bonus guilde : <?php echo htmlspecialchars($adminCharacterMpGuild)?><br />
                MP total : <?php echo htmlspecialchars($adminCharacterMpTotal)?><br />
                Force actuelle : <?php echo htmlspecialchars($adminCharacterStrength)?><br />
                Force points de compétences : <?php echo htmlspecialchars($adminCharacterStrengthSkillPoints)?><br />
                Force bonus : <?php echo htmlspecialchars($adminCharacterStrengthBonus)?><br />
                Force équipement : <?php echo htmlspecialchars($adminCharacterStrengthEquipments)?><br />
                Force bonus guilde : <?php echo htmlspecialchars($adminCharacterStrengthGuild)?><br />
                Force Total : <?php echo htmlspecialchars($adminCharacterStrengthTotal)?><br />
                Magie actuelle : <?php echo htmlspecialchars($adminCharacterMagic)?><br />
                Magie points de compétences : <?php echo htmlspecialchars($adminCharacterMagicSkillPoints)?><br />
                Magie bonus : <?php echo htmlspecialchars($adminCharacterMagicBonus)?><br />
                Magie équipement : <?php echo htmlspecialchars($adminCharacterMagicEquipments)?><br />
                Magie bonus guilde : <?php echo htmlspecialchars($adminCharacterMagicGuild)?><br />
                Magie Total : <?php echo htmlspecialchars($adminCharacterMagicTotal)?><br />
                Agilité actuelle : <?php echo htmlspecialchars($adminCharacterAgility)?><br />
                Agilité points de compétences : <?php echo htmlspecialchars($adminCharacterAgilitySkillPoints)?><br />
                Agilité bonus : <?php echo htmlspecialchars($adminCharacterAgilityBonus)?><br />
                Agilité équipement : <?php echo htmlspecialchars($adminCharacterAgilityEquipments)?><br />
                Agilité bonus guilde : <?php echo htmlspecialchars($adminCharacterAgilityGuild)?><br />
                Agilité Total : <?php echo htmlspecialchars($adminCharacterAgilityTotal)?><br />
                Défense actuelle : <?php echo htmlspecialchars($adminCharacterDefense)?><br />
                Défense points de compétences : <?php echo htmlspecialchars($adminCharacterDefenseSkillPoints)?><br />
                Défense bonus : <?php echo htmlspecialchars($adminCharacterDefenseBonus)?><br />
                Défense équipement : <?php echo htmlspecialchars($adminCharacterDefenseEquipment)?><br />
                Défense bonus guilde : <?php echo htmlspecialchars($adminCharacterDefenseGuild)?><br />
                Défense Total : <?php echo htmlspecialchars($adminCharacterDefenseTotal)?><br />
                Défense magique actuelle : <?php echo htmlspecialchars($adminCharacterDefenseMagic)?><br />
                Défense magique points de compétences : <?php echo htmlspecialchars($adminCharacterDefenseMagicSkillPoints)?><br />
                Défense magique bonus : <?php echo htmlspecialchars($adminCharacterDefenseMagicBonus)?><br />
                Défense magique équipement : <?php echo htmlspecialchars($adminCharacterDefenseMagicEquipments)?><br />
                Défense magique bonus guilde : <?php echo htmlspecialchars($adminCharacterDefenseMagicGuild)?><br />
                Défense magique Total : <?php echo htmlspecialchars($adminCharacterDefenseMagicTotal)?><br />
                Sagesse actuelle : <?php echo htmlspecialchars($adminCharacterWisdom)?><br />
                Sagesse points de compétences : <?php echo htmlspecialchars($adminCharacterWisdomSkillPoints)?><br />
                Sagesse bonus : <?php echo htmlspecialchars($adminCharacterWisdomBonus)?><br />
                Sagesse équipement : <?php echo htmlspecialchars($adminCharacterWisdomEquipments)?><br />
                Sagesse bonus guilde : <?php echo htmlspecialchars($adminCharacterWisdomGuild)?><br />
                Sagesse Total : <?php echo htmlspecialchars($adminCharacterWisdomTotal)?><br />
                Prospection actuelle : <?php echo htmlspecialchars($adminCharacterProspecting)?><br />
                Prospection points de compétences : <?php echo htmlspecialchars($adminCharacterProspectingSkillPoints)?><br />
                Prospection bonus : <?php echo htmlspecialchars($adminCharacterProspectingBonus)?><br />
                Prospection équipement : <?php echo htmlspecialchars($adminCharacterProspectingEquipments)?><br />
                Prospection bonus guilde : <?php echo htmlspecialchars($adminCharacterProspectingGuild)?><br />
                Prospection Total : <?php echo htmlspecialchars($adminCharacterProspectingTotal)?><br />
                Défaite(s) : <?php echo htmlspecialchars($adminCharacterDefeate)?><br />
                Victoire(s) : <?php echo htmlspecialchars($adminCharacterVictory)?><br />
                Expérience : <?php echo htmlspecialchars($adminCharacterExperience)?><br />
                Expérience totale : <?php echo htmlspecialchars($adminCharacterExperienceTotal)?><br />
                Points de compétence : <?php echo htmlspecialchars($adminCharacterSkillPoints)?><br />
                Argent : <?php echo htmlspecialchars($adminCharacterGold)?><br />
                Lieu : <?php echo htmlspecialchars($adminplaceName)?><br />
                Chapitre : <?php echo htmlspecialchars($adminCharacterChapter)?><br />
                En combat : <?php echo htmlspecialchars($adminCharacterOnBattleName)?><br />
                Activé : <?php echo htmlspecialchars($adminCharacterEnable)?><br />

                <hr>

                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>
                
                <?php
            }
            // Si le compte n'existe pas
            else
            {
                echo "Erreur : Ce compte n'existe pas";
            }
            $accountQuery->closeCursor();
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
