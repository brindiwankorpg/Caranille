<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminCharacterId'])
&& isset($_POST['adminOfferGold'])
&& isset($_POST['token'])
&& isset($_POST['offerGold']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminCharacterId'])
        && ctype_digit($_POST['adminOfferGold'])
        && $_POST['adminCharacterId'] >= 0
        && $_POST['adminOfferGold'] >= 0)
        {
            // On récupère les informations du formulaire précédent
            $adminCharacterId = $_POST['adminCharacterId'];
            $adminOfferGold = $_POST['adminOfferGold'];
            
            // Si l'experience à offrir est pour tous les joueurs
            if ($adminCharacterId == 0)
            {
                ?>
                
                <p>ATTENTION</p> 
                Vous êtes sur le point d'offrir <em><?php echo htmlspecialchars($adminOfferGold)?></em> pièce(s) d'or à <em>tous les joueurs</em>.<br />
                Confirmez-vous  ?

                <hr>
                    
                <form method="POST" action="offerGoldEnd.php">
                    <input type="hidden" name="adminCharacterId" value="<?php echo htmlspecialchars($adminCharacterId)?>">
                    <input type="hidden" name="adminOfferGold" value="<?php echo htmlspecialchars($adminOfferGold)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input type="submit" class="btn btn-secondary btn-lg" name="finalDelete" value="Je confirme">
                </form>
                
                <hr>

                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>
                
                <?php
            }
            // Si l'experience à offrir est pour un seul joueur
            else 
            {
                // On fait une requête pour vérifier si le personnage existe
                $characterQuery = $bdd->prepare("SELECT * FROM car_characters 
                WHERE characterId = ?");
                $characterQuery->execute([$adminCharacterId]);
                $characterRow = $characterQuery->rowCount();
        
                // Si le personnage existe
                if ($characterRow == 1)
                {
                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($character = $characterQuery->fetch())
                    {
                        $adminCharacterName = $character['characterName'];
                    }
                    ?>
                    
                    <p>ATTENTION</p>

                    Vous êtes sur le point d'offrir <em><?php echo htmlspecialchars($adminOfferGold)?></em> pièce(s) d'or à <em><?php echo htmlspecialchars($adminCharacterName)?></em>.<br />
                    Confirmez-vous ?
        
                    <hr>
                        
                    <form method="POST" action="offerGoldEnd.php">
                        <input type="hidden" name="adminCharacterId" value="<?php echo htmlspecialchars($adminCharacterId)?>">
                        <input type="hidden" name="adminOfferGold" value="<?php echo htmlspecialchars($adminOfferGold)?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                        <input type="submit" class="btn btn-secondary btn-lg" name="finalDelete" value="Je confirme">
                    </form>
                    
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
                $characterQuery->closeCursor();
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
