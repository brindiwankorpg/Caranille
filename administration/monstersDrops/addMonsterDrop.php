<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminMonsterDropMonsterId'])
&& isset($_POST['adminMonsterDropItemId'])
&& isset($_POST['adminMonsterDropItemVisible'])
&& isset($_POST['adminMonsterDropRate'])
&& isset($_POST['adminMonsterDropRateVisible'])
&& isset($_POST['token'])
&& isset($_POST['add']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminMonsterDropMonsterId'])
        && ctype_digit($_POST['adminMonsterDropItemId'])
        && ctype_digit($_POST['adminMonsterDropRate'])
        && $_POST['adminMonsterDropMonsterId'] >= 1
        && $_POST['adminMonsterDropItemId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $adminMonsterDropMonsterId = $_POST['adminMonsterDropMonsterId'];
            $adminMonsterDropItemId = $_POST['adminMonsterDropItemId'];
            $adminMonsterDropItemVisible = $_POST['adminMonsterDropItemVisible'];
            $adminMonsterDropRate = $_POST['adminMonsterDropRate'];
            $adminMonsterDropRateVisible = $_POST['adminMonsterDropRateVisible'];

            // Si le taux d'obtention est entre 0 et 1000 on ajoute l'objet
            if ($adminMonsterDropRate >= 0 && $adminMonsterDropRate <= 1000)
            {
                // On fait une requête pour vérifier si le monstre choisi existe
                $monsterQuery = $bdd->prepare("SELECT * FROM car_monsters 
                WHERE monsterId = ?");
                $monsterQuery->execute([$adminMonsterDropMonsterId]);
                $monsterRow = $monsterQuery->rowCount();

                // Si le monstre existe
                if ($monsterRow == 1) 
                {
                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($monster = $monsterQuery->fetch())
                    {
                        // On récupère les informations du monstre
                        $adminMonsterDropMonsterPicture = $monster['monsterPicture'];
                        $adminMonsterDropMonsterName = $monster['monsterName'];
                    }

                    // On fait une requête pour vérifier si l'objet choisi existe
                    $itemQuery = $bdd->prepare("SELECT * FROM car_items 
                    WHERE itemId = ?");
                    $itemQuery->execute([$adminMonsterDropItemId]);
                    $itemRow = $itemQuery->rowCount();

                    // Si l'objet existe
                    if ($itemRow == 1) 
                    {
                        // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                        while ($item = $itemQuery->fetch())
                        {
                            // /On récupère les informations de l'objet
                            $adminMonsterDropItemPicture = $item['itemPicture'];
                            $adminMonsterDropItemName = $item['itemName'];
                        }

                        // On fait une requête pour vérifier si l'objet n'est pas déjà sur ce monstre
                        $monsterDropQuery = $bdd->prepare("SELECT * FROM car_monsters_drops 
                        WHERE monsterDropMonsterID = ?
                        AND monsterDropItemID = ?");
                        $monsterDropQuery->execute([$adminMonsterDropMonsterId, $adminMonsterDropItemId]);
                        $monsterDropRow = $monsterDropQuery->rowCount();

                        // Si cet objet n'est pas sur le monstre
                        if ($monsterDropRow == 0) 
                        {
                            ?>
                
                            <p>ATTENTION</p> 

                            Vous êtes sur le point d'ajouter l'objet <em><?php echo htmlspecialchars($adminMonsterDropItemName)?></em> sur le monstre <em><?php echo htmlspecialchars($adminMonsterDropMonsterName)?></em>.<br />
                            Confirmez-vous l'ajout ?

                            <hr>
                                
                            <form method="POST" action="addMonsterDropEnd.php">
                                <input type="hidden" name="adminMonsterDropMonsterId" value="<?php echo htmlspecialchars($adminMonsterDropMonsterId)?>">
                                <input type="hidden" name="adminMonsterDropItemId" value="<?php echo htmlspecialchars($adminMonsterDropItemId)?>">
                                <input type="hidden" name="adminMonsterDropItemVisible" value="<?php echo htmlspecialchars($adminMonsterDropItemVisible)?>">
                                <input type="hidden" name="adminMonsterDropRate" value="<?php echo htmlspecialchars($adminMonsterDropRate)?>">
                                <input type="hidden" name="adminMonsterDropRateVisible" value="<?php echo htmlspecialchars($adminMonsterDropRateVisible)?>">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                                <input type="submit" class="btn btn-secondary btn-lg" name="finalAdd" value="Je confirme">
                            </form>
                            
                            <hr>

                            <form method="POST" action="index.php">
                                <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                            </form>

                            <?php
                        }
                        // Si l'objet est déjà sur le monstre est déjà dans ce lieu
                        else
                        {
                            ?>
                            
                            Erreur : Cet objet est déjà sur ce monstre

                            <form method="POST" action="manageMonsterDrop.php">
                                <input type="hidden" name="adminMonsterDropMonsterId" value="<?php echo htmlspecialchars($adminMonsterDropMonsterId)?>">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                                <input type="submit" class="btn btn-secondary btn-lg" name="manage" value="Retour">
                            </form>
                            
                            <?php
                        }
                        $monsterDropQuery->closeCursor();
                    }
                    // Si l'objet existe pas
                    else
                    {
                        echo "Erreur : Objet indisponible";
                    }
                    $itemQuery->closeCursor();
                }
                // Si le monstre existe pas
                else
                {
                    echo "Erreur : Ce monstre n'existe pas";
                }
                $monsterQuery->closeCursor();
            }
            // Si le taux d'obtention de l'objet est inférieur à 0 ou est supérieur à 1000
            else
            {
                ?>
                
                Erreur : Le taux d'obtention doit être entre 0 et 1000/1000
                <form method="POST" action="manageMonsterDrop.php">
                    <input type="hidden" name="adminMonsterDropMonsterId" value="<?php echo htmlspecialchars($adminMonsterDropMonsterId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input type="submit" class="btn btn-secondary btn-lg" name="manage" value="Continuer">
                </form>
                
                <?php
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
