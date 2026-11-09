<?php
require_once("../../kernel/kernel.php");
require_once("../../html/header.php");
?>

<form method="POST" action="completeRegistration.php" autocomplete="off">
    Pseudo : <input type="text" class="form-control" name="accountPseudo" autocomplete="off" required>
    Mot de passe : <input type="password" class="form-control" name="accountPassword" autocomplete="new-password" required>
    Confirmez : <input type="password" class="form-control" name="accountPasswordConfirm" autocomplete="new-password" required>
    Email : <input type="email" class="form-control" name="accountEmail" autocomplete="off" required>
    Confirmez l'email : <input type="email" class="form-control" name="accountEmailConfirm" autocomplete="off" required>
    Classe <select class="form-control" name="characterclasseId">

        <?php
        // On remplit le menu déroulant avec la liste des classes disponibles
        $classeListQuery = $bdd->query("SELECT * FROM car_classes");

        $classeList = $classeListQuery->rowCount();
        
        // S'il y a au moins une classe de disponible on les affiche dans le menu déroulant
        if ($classeList >= 1)
        {
            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
            while ($classeList = $classeListQuery->fetch())
            {
                // on récupère les valeurs de chaque classe qu'on va ensuite mettre dans le menu déroulant
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
    Nom du personnage : <input type="text" class="form-control" name="characterName" required><br />
    <iframe src="../../CGU.txt" width="100%" height="250"></iframe>
    En vous inscrivant vous acceptez le présent règlement !<br />
    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
    <input type="submit" class="btn btn-secondary btn-lg" name="register" value="Je crée mon compte">
</form>

<?php require_once("../../html/footer.php"); ?>
