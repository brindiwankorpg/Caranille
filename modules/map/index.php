<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur est déjà dans un lieu on le redirige vers le lieu
if ($characterPlaceId >= 1) { exit(header("Location: ../../modules/place/index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// On recherche la liste des lieux disponibles par rapport au chapitre du joueur
$placeQuery = $bdd->prepare("SELECT * FROM car_places
WHERE placeChapter <= ?
ORDER BY placeChapter");
$placeQuery->execute([$characterChapter]);
$placeRow = $placeQuery->rowCount();

// S'il y a au moins un lieu disponible on affiche le formulaire
if ($placeRow >= 1)
{
    ?>
    
    <form method="POST" action="choosePlace.php"><div class="form-group">
        Liste des lieux disponibles <select name="placeId" class="form-control">
            
            <?php
            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
            while ($placeList = $placeQuery->fetch())
            {
                // on récupère les valeurs de chaque lieu qu'on va ensuite mettre dans le menu déroulant
                $placeId = $placeList['placeId']; 
                $placeName = $placeList['placeName'];
                $placeChapter = $placeList['placeChapter'];
                $placeAccess = $placeList['placeAccess'];
                
                if ($placeAccess == "Yes")
                {
                	$placeAccessValue = "Accessible";
                }
                else
                {
                	$placeAccessValue = "Inaccessible";
                }
                ?>
                <option value="<?php echo htmlspecialchars($placeId)?>"><?php echo "Chapitre " . htmlspecialchars($placeChapter) . " - " . htmlspecialchars($placeName) . " (" . htmlspecialchars($placeAccessValue) . ")"?></option>
                <?php
            }
            ?>
            
        </select>
        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
        <input type="submit" name="enter" class="btn btn-secondary btn-lg" value="Entrer dans le lieu">
    </div></form>
    
    <?php
}
// S'il n'y a aucun lieu disponible on affiche un message
else
{
    echo "Aucun lieu disponible";
}
$placeQuery->closeCursor();
?>

<?php require_once("../../html/footer.php"); ?>
