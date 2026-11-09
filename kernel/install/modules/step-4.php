<?php
require_once("../html/header.php"); 
include("../../config.php"); 

if (isset($_POST['installationType'])
&& isset($_POST['token']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        $installationType = $_POST['installationType'];

        // Si le type d'installation est complète ou minimale
        if ($installationType == "fullInstall" || $installationType == "minInstall" || $installationType == "retry")
        {
            // Si le type d'installation est complète
            if ($installationType == "fullInstall")
            {
                $installSqlQuery = $bdd->query(file_get_contents('../sql/fullInstall.sql'));
                $installSqlQuery->closeCursor();
            }
            
            // Si le type d'installation est minimale
            if ($installationType == "minInstall")
            {
                $installSqlQuery = $bdd->query(file_get_contents('../sql/minInstall.sql'));
                $installSqlQuery->closeCursor();
            }
            ?>

            <p><h4>Étape 4/4 - Création d'un compte administrateur</h4></p>
        
            <form method="POST" action="step-5.php" autocomplete="off">
                Pseudo : <input type="text" class="form-control" name="accountPseudo" autocomplete="off" required>
                Mot de passe : <input type="password" class="form-control" name="accountPassword" autocomplete="new-password" required>
                Confirmez : <input type="password" class="form-control" name="accountPasswordConfirm" autocomplete="new-password" required>
                Email : <input type="email" class="form-control" name="accountEmail" autocomplete="off" required>
                Confirmez l'email : <input type="email" class="form-control" name="accountEmailConfirm" autocomplete="off" required>
                Classe <select class="form-control" name="characterclasseId">
                    
                    <?php
                    // On remplit le menu déroulant avec la liste des classes disponibles
                    $classeListQuery = $bdd->query("SELECT * FROM car_classes");
                    // On recherche combien il y a de classes disponibles
                    $classeList = $classeListQuery->rowCount();
                    // S'il y a au moins une classe disponible, on les affiche dans le menu déroulant
                    if ($classeList >= 1)
                    {
                        // On fait une boucle sur tous les résultats
                        while ($classeList = $classeListQuery->fetch())
                        {
                            // On récupère les informations de la classe
                            $classeId = $classeList['classeId']; 
                            $classeName = $classeList['classeName'];
                            ?>
                            <option value="<?php echo htmlspecialchars($classeId)?>"><?php echo htmlspecialchars($classeName)?></option>
                            <?php
                        }
                    }
                    $classeListQuery->closeCursor();
                    ?>
                
                </select>
                Sexe : <select class="form-control" name="characterSex">
                    <option value="1">Homme</option>
                    <option value="0">Femme</option>
                </select>
                Nom du personnage : <input type="text" class="form-control" name="characterName" required>
                <iframe src="../../../CGU.txt" width="100%" height="250"></iframe>
                En vous inscrivant vous acceptez le présent règlement !<br />
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                <input type="submit" name="register" class="btn btn-secondary btn-lg" value="Je crée mon compte">
            </form>

            <?php
        }
        // Si le type d'installation n'est ni complète ni minimale
        else
        {
            echo "Erreur : Type d'installation non défini";
        }
    }
    // Si le token de sécurité n'est pas correct
    else
    {
        echo "Erreur : La session a expiré, veuillez réessayer";
    }
}
// Si tous les champs n'ont pas été remplis
else 
{
    echo "Tous les champs n'ont pas été remplis";
}

require_once("../html/footer.php"); ?>
