<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminMonsterId'])
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
        if (ctype_digit($_POST['adminMonsterId'])
        && $_POST['adminMonsterId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $adminMonsterId = $_POST['adminMonsterId'];

            // On fait une requête pour vérifier si le monstre choisi existe
            $monsterQuery = $bdd->prepare("SELECT * FROM car_monsters, car_monsters_categories
            WHERE monsterCategory = monsterCategoryId
            AND monsterId = ?");
            $monsterQuery->execute([$adminMonsterId]);
            $monsterRow = $monsterQuery->rowCount();

            // Si le monstre existe
            if ($monsterRow == 1) 
            {
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($monster = $monsterQuery->fetch())
                {
                    // On récupère les informations du monstre
                    $adminMonsterId = $monster['monsterId'];
                    $adminMonsterCategoryId = $monster['monsterCategoryId'];
                    $adminMonsterCategoryName = $monster['monsterCategoryName'];
                    $adminMonsterCategoryNameShow = $monster['monsterCategoryNameShow'];
                    $adminMonsterPicture = $monster['monsterPicture'];
                    $adminMonsterName = $monster['monsterName'];
                    $adminMonsterLevel = $monster['monsterLevel'];
                    $adminMonsterDescription = $monster['monsterDescription'];
                    $adminMonsterHp = $monster['monsterHp'];
                    $adminMonsterMp = $monster['monsterMp'];
                    $adminMonsterStrength = $monster['monsterStrength'];
                    $adminMonsterMagic = $monster['monsterMagic'];
                    $adminMonsterAgility = $monster['monsterAgility'];
                    $adminMonsterDefense = $monster['monsterDefense'];
                    $adminMonsterDefenseMagic = $monster['monsterDefenseMagic'];
                    $adminMonsterExperience = $monster['monsterExperience'];              
                    $adminMonsterGold = $monster['monsterGold'];
                    $adminMonsterLimited = $monster['monsterLimited'];
                    $adminMonsterQuantity = $monster['monsterQuantity'];
                    $adminMonsterQuantityBattle = $monster['monsterQuantityBattle'];
                    $adminMonsterQuantityEscaped = $monster['monsterQuantityEscaped'];
                    $adminMonsterQuantityVictory = $monster['monsterQuantityVictory'];
                    $adminMonsterQuantityDefeated = $monster['monsterQuantityDefeated'];
                    $adminMonsterQuantityDraw = $monster['monsterQuantityDraw'];
                }
                ?>

                <p><img src="<?php echo htmlspecialchars($adminMonsterPicture)?>" height="100" width="100"></p>

                <p>Informations du monstre</p>

                Numéro : <?php echo htmlspecialchars($adminMonsterId)?><br />

                <form method="POST" action="editMonsterEnd.php">
                    Image : <input type="text" name="adminMonsterPicture" class="form-control" placeholder="Image" value="<?php echo htmlspecialchars($adminMonsterPicture)?>" required>
                    Catégorie : <select name="adminMonsterCategoryCategoryId" class="form-control">
                        
                        <?php
                        // On rempli le menu déroulant avec la liste des classes disponible
                        $monsterCategoryQuery = $bdd->query("SELECT * FROM car_monsters_categories");
                        
                        // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                        while ($monsterCategory = $monsterCategoryQuery->fetch())
                        {
                            // On récupère les informations de la classe
                            $adminMonsterCategoryIdSql = $monsterCategory['monsterCategoryId'];
                            $adminMonsterCategoryName = $monsterCategory['monsterCategoryName'];
                            $adminMonsterCategoryNameShow = $monsterCategory['monsterCategoryNameShow'];
                            
                            if ($adminMonsterCategoryIdSql == $adminMonsterCategoryId)
                            {
                                ?>
                                <option selected="selected" value="<?php echo htmlspecialchars($adminMonsterCategoryIdSql)?>"><?php echo htmlspecialchars($adminMonsterCategoryNameShow)?></option>
                                <?php
                            }
                            else
                            {
                                ?>
                                <option value="<?php echo htmlspecialchars($adminMonsterCategoryIdSql)?>"><?php echo htmlspecialchars($adminMonsterCategoryNameShow)?></option>
                                <?php
                            }
                            
                        }
                        $monsterCategoryQuery->closeCursor();
                        ?>
                        
                    </select>    
                    Nom : <input type="text" name="adminMonsterName" class="form-control" placeholder="Nom" value="<?php echo htmlspecialchars($adminMonsterName)?>" required>
                    Niveau : <input type="number" name="adminMonsterLevel" class="form-control" placeholder="Niveau" value="<?php echo htmlspecialchars($adminMonsterLevel)?>" required>
                    Description : <br> <textarea class="form-control" name="adminMonsterDescription" id="adminMonsterDescription" rows="3" required><?php echo htmlspecialchars($adminMonsterDescription); ?></textarea>
                    HP : <input type="number" name="adminMonsterHp" class="form-control" placeholder="HP" value="<?php echo htmlspecialchars($adminMonsterHp)?>" required>
                    MP : <input type="number" name="adminMonsterMp" class="form-control" placeholder="MP" value="<?php echo htmlspecialchars($adminMonsterMp)?>" required>
                    Force : <input type="number" name="adminMonsterStrength" class="form-control" placeholder="Force" value="<?php echo htmlspecialchars($adminMonsterStrength)?>" required>
                    Magie : <input type="number" name="adminMonsterMagic" class="form-control" placeholder="Magie" value="<?php echo htmlspecialchars($adminMonsterMagic)?>" required>
                    Agilité : <input type="number" name="adminMonsterAgility" class="form-control" placeholder="Agilité" value="<?php echo htmlspecialchars($adminMonsterAgility)?>" required>
                    Défense : <input type="number" name="adminMonsterDefense" class="form-control" placeholder="Défense" value="<?php echo htmlspecialchars($adminMonsterDefense)?>" required>
                    Défense Magique : <input type="number" name="adminMonsterDefenseMagic" class="form-control" placeholder="Défense Magique" value="<?php echo htmlspecialchars($adminMonsterDefenseMagic)?>" required>
                    Expérience : <input type="number" name="adminMonsterExperience" class="form-control" placeholder="Expérience" value="<?php echo htmlspecialchars($adminMonsterExperience)?>" required>
                    Argent : <input type="number" name="adminMonsterGold" class="form-control" placeholder="Argent" value="<?php echo htmlspecialchars($adminMonsterGold)?>" required>
                    Monstre limité : <select name="adminMonsterLimited" class="form-control">

                    <?php
                    // Si le monstre n'est pas limité
                    if ($adminMonsterLimited == "No")
                    {
                        ?>
                        <option selected="selected" value="No">Non</option>
                        <option value="Yes">Oui</option>
                        <?php
                    }
                    // Si le monstre est limité
                    else
                    {
                        ?>
                        <option selected="selected" value="Yes">Oui</option>
                        <option value="No">Non</option>
                        <?php
                    }
                    ?>

                    </select>
                    Quantité restante (Si monstre limité) : <input type="number" name="adminMonsterQuantity" class="form-control" placeholder="Quantité du monstre" value="<?php echo htmlspecialchars($adminMonsterQuantity)?>" required>
                    <input type="hidden" name="adminMonsterId" value="<?php echo htmlspecialchars($adminMonsterId)?>">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                    <input name="finalEdit" class="btn btn-secondary btn-lg" type="submit" value="Modifier">
                </form>

                <hr>

                <p>STATISTIQUES DE COMBAT</p>

                Nombre de combat lancé contre ce monstre : <?php echo htmlspecialchars($adminMonsterQuantityBattle)?><br />
                Nombre de combat fuit contre ce monstre : <?php echo htmlspecialchars($adminMonsterQuantityEscaped)?><br />
                Nombre de combat gagné contre ce monstre : <?php echo htmlspecialchars($adminMonsterQuantityVictory)?><br />
                Nombre de combat perdu contre ce monstre : <?php echo htmlspecialchars($adminMonsterQuantityDefeated)?><br />
                Nombre de combat match nul contre ce monstre : <?php echo htmlspecialchars($adminMonsterQuantityDraw)?><br />

                <hr>

                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>
                <?php
            }
            // Si le monstre n'existe pas
            else
            {
                echo "Erreur : Ce monstre n'existe pas";
            }
            $monsterQuery->closeCursor();
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
