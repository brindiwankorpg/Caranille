<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session, c'est que le joueur n'est pas connecté, alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminCharacterLevel'])
&& isset($_POST['token'])
&& isset($_POST['finalEdit']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminCharacterLevel']) 
        && $_POST['adminCharacterLevel'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $adminCharacterLevel = $_POST['adminCharacterLevel'];
            
            // On va calculer les statistiques du joueur pour son nouveau niveau
            $hpLevel = 100 + $hPByLevel * ($adminCharacterLevel - 1);
            $mpLevel = 10 + $mPByLevel * ($adminCharacterLevel - 1);
            $strengthLevel = 1 + $strengthByLevel * ($adminCharacterLevel - 1);
            $magicLevel = 1 + $magicByLevel * ($adminCharacterLevel - 1);
            $agilityLevel = $agilityByLevel * ($adminCharacterLevel - 1);
            $defenseLevel = $defenseByLevel * ($adminCharacterLevel - 1);
            $defenseMagicLevel = $defenseMagicByLevel * ($adminCharacterLevel - 1);
            $wisdomLevel = $wisdomByLevel * ($adminCharacterLevel - 1);
            $prospectingLevel = $prospectingByLevel * ($adminCharacterLevel - 1);
            $skillPointLevel = $skillPointsByLevel * ($adminCharacterLevel - 1);
            
            // On met le personnage à jour
            $updateCharacter = $bdd->prepare("UPDATE car_characters SET
            characterLevel = :adminCharacterLevel,
            characterHpMin = :hpLevel, 
            characterHpMax = :hpLevel, 
            characterHpEquipments = 0,
            characterHpTotal = :hpLevel, 
            characterMpMin = :mpLevel, 
            characterMpMax = :mpLevel, 
            characterMpEquipments = 0,
            characterMpTotal = :mpLevel, 
            characterStrength = :strengthLevel, 
            characterStrengthEquipments = 0,
            characterStrengthTotal = :strengthLevel, 
            characterMagic = :magicLevel, 
            characterMagicEquipments = 0,
            characterMagicTotal = :magicLevel, 
            characterAgility = :agilityLevel, 
            characterAgilityEquipments = 0,
            characterAgilityTotal = :agilityLevel, 
            characterDefense = :defenseLevel, 
            characterDefenseEquipments = 0,
            characterDefenseTotal = :defenseLevel, 
            characterDefenseMagic = :defenseMagicLevel, 
            characterDefenseMagicEquipments = 0,
            characterDefenseMagicTotal = :defenseMagicLevel, 
            characterWisdom = :wisdomLevel, 
            characterWisdomEquipments = 0,
            characterWisdomTotal = :wisdomLevel,
            characterProspecting = :prospectingLevel, 
            characterProspectingEquipments = 0,
            characterProspectingTotal = :prospectingLevel,
            characterSkillPoints = :skillPointsLevel
            WHERE characterId = :characterId");
            $updateCharacter->execute(array(
            'adminCharacterLevel' => $adminCharacterLevel,  
            'hpLevel' => $hpLevel, 
            'mpLevel' => $mpLevel, 
            'strengthLevel' => $strengthLevel, 
            'magicLevel' => $magicLevel, 
            'agilityLevel' => $agilityLevel, 
            'defenseLevel' => $defenseLevel, 
            'defenseMagicLevel' => $defenseMagicLevel,
            'wisdomLevel' => $wisdomLevel,
            'prospectingLevel' => $prospectingLevel,
            'skillPointsLevel' => $skillPointLevel, 
            'characterId' => $characterId));
            $updateCharacter->closeCursor();
            
            // On fait une recherche de tous les équipements qui sont équipés pour les déséquiper
            $inventoryQuery = $bdd->prepare("SELECT * FROM car_inventory 
            WHERE inventoryEquipped = '1'
            AND inventoryCharacterId = ?");
            $inventoryQuery->execute([$characterId]);

            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
            while ($inventory = $inventoryQuery->fetch())
            {
                // On récupère les informations de l'inventaire
                $adminInventoryId = $inventory['inventoryId'];

                // On va rendre l'équipement non équipé
                $updateInventory = $bdd->prepare("UPDATE car_inventory SET
                inventoryEquipped = 0
                WHERE inventoryId = :adminInventoryId");
                $updateInventory->execute(array(
                'adminInventoryId' => $adminInventoryId));
                $updateInventory->closeCursor();
            }
            $inventoryQuery->closeCursor();
        ?>
        
        Le niveau a bien été mis à jour
        
        <hr>
            
        <form method="POST" action="index.php">
            <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
        </form>
        
        <?php
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
