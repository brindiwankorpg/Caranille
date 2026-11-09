<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session, c'est que le joueur n'est pas connecté, alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminItemId']) 
&& isset($_POST['adminItemPicture'])
&& isset($_POST['adminItemName'])
&& isset($_POST['adminItemDescription'])
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
            
            // On fait une requête pour vérifier si le parchemin choisi existe
            $itemQuery = $bdd->prepare("SELECT * FROM car_items 
            WHERE itemId = ?");
            $itemQuery->execute([$adminItemId]);
            $itemRow = $itemQuery->rowCount();

            // Si le parchemin existe
            if ($itemRow == 1) 
            {
                // On récupère les informations du formulaire
                $adminItemId = $_POST['adminItemId'];
                $adminItemPicture = $_POST['adminItemPicture'];
                $adminItemName = $_POST['adminItemName'];
                $adminItemDescription = $_POST['adminItemDescription'];
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
        
                // On met à jour le parchemin dans la base de données
                $updateItems = $bdd->prepare("UPDATE car_items 
                SET itemPicture = :adminItemPicture,
                itemName = :adminItemName,
                itemDescription = :adminItemDescription,
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
                'adminItemPicture' => $adminItemPicture,
                'adminItemName' => $adminItemName,
                'adminItemDescription' => $adminItemDescription,
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
                ?>

                Le parchemin a bien été mis à jour

                <hr>
                    
                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>
                
                <?php
            }
            // Si le parchemin n'existe pas
            else
            {
                echo "Erreur : Parchemin indisponible";
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
    echo "Erreur : Tous les champs n'ont pas été remplis";
}

require_once("../html/footer.php");
