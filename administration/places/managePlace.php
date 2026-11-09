<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminPlaceId'])
&& isset($_POST['token'])
&& isset($_POST['manage']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminPlaceId'])
        && $_POST['adminPlaceId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $adminPlaceId = $_POST['adminPlaceId'];

            // On fait une requête pour vérifier si l'objet choisi existe
            $placeQuery = $bdd->prepare("SELECT * FROM car_places 
            WHERE placeId = ?");
            $placeQuery->execute([$adminPlaceId]);
            $placeRow = $placeQuery->rowCount();

            // Si le lieu existe
            if ($placeRow == 1) 
            {
                // On fait une recherche dans la base de données de tous les lieux
                while ($place = $placeQuery->fetch())
                {
                    $adminplaceName = $place['placeName'];
                }
                ?>
                
                Que souhaitez-vous faire du lieu <em><?php echo htmlspecialchars($adminplaceName)?></em> ?

                <hr>
                    
                <form method="POST" action="editPlace.php">
                    <input type="hidden" name="adminPlaceId" value="<?php echo htmlspecialchars($adminPlaceId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input type="submit" class="btn btn-secondary btn-lg" name="edit" value="Afficher/Modifier le lieu">
                </form>
                <form method="POST" action="../placesShops/managePlaceShop.php">
                    <input type="hidden" name="adminplaceShopPlaceId" value="<?php echo htmlspecialchars($adminPlaceId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input type="submit" class="btn btn-secondary btn-lg" name="manage" value="Magasins du lieu">
                </form>
                <form method="POST" action="../placesMonsters/managePlaceMonster.php">
                    <input type="hidden" name="adminplaceMonsterPlaceId" value="<?php echo htmlspecialchars($adminPlaceId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input type="submit" class="btn btn-secondary btn-lg" name="manage" value="Monstres du lieu">
                </form>
                
                <hr>

                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>
                
                <?php
            }
            // Si le lieu n'existe pas
            else
            {
                echo "Erreur : Cette lieu n'existe pas";
            }
            $placeQuery->closeCursor();
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
