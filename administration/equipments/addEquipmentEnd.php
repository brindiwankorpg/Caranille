<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminItemItemTypeId'])
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
&& isset($_POST['finalAdd']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminItemItemTypeId'])
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
            // On récupère les informations du formulaire
            $adminItemItemTypeId = $_POST['adminItemItemTypeId'];
            $adminItemclasseId = $_POST['adminItemclasseId'];
            
            // On fait une requête pour vérifier si le type d'équipement choisi existe
            $itemTypeQuery = $bdd->prepare("SELECT * FROM car_items_types
            WHERE itemTypeId = ?");
            $itemTypeQuery->execute([$adminItemItemTypeId]);
            $itemTypeRow = $itemTypeQuery->rowCount();
            $itemTypeQuery->closeCursor();

            // Si le type d'équipement existe
            if ($itemTypeRow == 1) 
            {
                // Si la classe choisie est supérieure à zéro c'est que l'équipement est dédié à une classe
                if ($adminItemclasseId > 0)
                {
                    // On fait une requête pour vérifier si la classe choisi existe
                    $classeQuery = $bdd->prepare("SELECT * FROM car_classes 
                    WHERE classeId = ?");
                    $classeQuery->execute([$adminItemclasseId]);
                    $classeRow = $classeQuery->rowCount();
                    $classeQuery->closeCursor();
                }
                // Si la classe choisie est égale à zéro c'est qu'il s'agit d'un équipement pour toutes les classes
                else
                {
                    // On met $classeRow à 1 pour passer à la suite
                    $classeRow = 1;
                }

                // Si la classe est disponible ou que la classe est à zéro
                if ($classeRow == 1) 
                {
                    // On récupère les informations du formulaire
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
            
                    // On ajoute l'équipement dans la base de données
                    $addItem = $bdd->prepare("INSERT INTO car_items VALUES(
                    NULL,
                    :adminItemItemTypeId,
                    :adminItemclasseId,
                    :adminItemPicture,
                    :adminItemName,
                    :adminItemDescription,
                    :adminItemLevel,
                    :adminItemLevelRequired,
                    :adminItemHpEffects,
                    :adminItemMpEffect,
                    :adminItemStrengthEffect,
                    :adminItemMagicEffect,
                    :adminItemAgilityEffect,
                    :adminItemDefenseEffect,
                    :adminItemDefenseMagicEffect,
                    :adminItemWisdomEffect,
                    :adminItemProspectingEffect,
                    :adminItemPurchasePrice,
                    :adminItemSalePrice)");
                    $addItem->execute([
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
                    'adminItemSalePrice' => $adminItemSalePrice]);
                    $addItem->closeCursor();
                    ?>
            
                    L'équipement a bien été créé
            
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
