<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si l'utilisateur a cliqué sur le bouton finalEdit
if (isset($_POST['adminGameName'])
&& isset($_POST['adminGameMaxLevel'])
&& isset($_POST['adminGamePresentation'])
&& isset($_POST['adminGameExperience'])
&& isset($_POST['adminGameSkillPoint'])
&& isset($_POST['adminGameExperienceBonus'])
&& isset($_POST['adminGameGoldBonus'])
&& isset($_POST['adminGameDropBonus'])
&& isset($_POST['adminGameAccess'])
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
        if (ctype_digit($_POST['adminGameMaxLevel'])
        && ctype_digit($_POST['adminGameExperience'])
        && ctype_digit($_POST['adminGameSkillPoint'])
        && ctype_digit($_POST['adminGameExperienceBonus'])
        && ctype_digit($_POST['adminGameGoldBonus'])
        && ctype_digit($_POST['adminGameDropBonus'])
        && $_POST['adminGameExperience'] >= 0
        && $_POST['adminGameSkillPoint'] >= 0
        && $_POST['adminGameExperienceBonus'] >= 0
        && $_POST['adminGameGoldBonus'] >= 0
        && $_POST['adminGameDropBonus'] >= 0)
        {
            // On récupère les informations du formulaire
            $adminGameName = $_POST['adminGameName'];
            $adminGamePresentation = $_POST['adminGamePresentation'];
            $adminGameMaxLevel = $_POST['adminGameMaxLevel'];
            $adminGameExperience = $_POST['adminGameExperience'];
            $adminGameSkillPoint = $_POST['adminGameSkillPoint'];
            $adminGameExperienceBonus = $_POST['adminGameExperienceBonus'];
            $adminGameGoldBonus = $_POST['adminGameGoldBonus'];
            $adminGameDropBonus = $_POST['adminGameDropBonus'];
            $adminGameAccess = $_POST['adminGameAccess'];

            // On fait une requête dans la base de données pour récupérer les informations du jeu
            $configurationQuery = $bdd->query("SELECT * FROM car_configuration");

            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
            while ($configuration = $configurationQuery->fetch())
            {
                // On récupère les informations du jeu
                $adminGameId = $configuration['configurationId'];
                $adminOldGameName = $configuration['configurationGameName'];
                $adminOldGamePresentation = $configuration['configurationPresentation'];   
                $adminOldGameExperience = $configuration['configurationExperience'];
                $adminOldGameSkillPoint = $configuration['configurationSkillPoint'];
                $adminOldGameExperienceBonus = $configuration['configurationExperienceBonus'];
                $adminOldGameGoldBonus = $configuration['configurationGoldBonus'];
                $adminOldGameDropBonus = $configuration['configurationDropBonus'];
                $adminOldGameAccess = $configuration['configurationAccess'];
            }
            $configurationQuery->closeCursor();
            ?>

            <p>Récapitulatif de la configuration :</p>

            <?php
            echo "Nom du jeu : " . htmlspecialchars($adminGameName) . "<br />";
            echo "Présentation : " . htmlspecialchars($adminGamePresentation) . "<br />";
            echo "Niveau Max : " . htmlspecialchars($adminGameMaxLevel) . "<br />";
            echo "Base d'expérience : " . htmlspecialchars($adminGameExperience) . "<br />";
            echo "PC par niveau : " . htmlspecialchars($adminGameSkillPoint) . "<br />";
            echo "Expérience bonus (%) : " . htmlspecialchars($adminGameExperienceBonus) . "<br />";
            echo "Argent bonus (%) : " . htmlspecialchars($adminGameGoldBonus) . "<br />";
            echo "Taux d'obtention bonus (%) : " . htmlspecialchars($adminGameDropBonus) . "<br />";
            echo "Accès du jeu : " . htmlspecialchars($adminGameAccess) . "<br /><br />";
            ?>

            <em>ATTENTION</em><br />
            Toute modification de la base d'expérience ou des PC par niveau entraînera la remise à zéro de tous les personnages du jeu en leur redistribuant leur expérience gagnée et en conservant leurs objets, équipements, avancée dans le jeu etc...<br />
        
            Confirmez-vous les modifications ?

            <hr>
                
            <form method="POST" action="editConfigurationEnd.php">
                <input type="hidden" name="adminGameName" class="form-control" placeholder="Nom du jeu" value="<?php echo htmlspecialchars($adminGameName)?>">
                <input type="hidden" name="adminGamePresentation" class="form-control" value="<?php echo htmlspecialchars($adminGamePresentation); ?>">
                <input type="hidden" name="adminGameMaxLevel" class="form-control" value="<?php echo htmlspecialchars($adminGameMaxLevel)?>">
                <input type="hidden" name="adminGameExperience" class="form-control" value="<?php echo htmlspecialchars($adminGameExperience)?>">
                <input type="hidden" name="adminGameSkillPoint" class="form-control" value="<?php echo htmlspecialchars($adminGameSkillPoint)?>">
                <input type="hidden" name="adminGameExperienceBonus" class="form-control" value="<?php echo htmlspecialchars($adminGameExperienceBonus)?>">
                <input type="hidden" name="adminGameGoldBonus" class="form-control" value="<?php echo htmlspecialchars($adminGameGoldBonus)?>">
                <input type="hidden" name="adminGameDropBonus" class="form-control" value="<?php echo htmlspecialchars($adminGameDropBonus)?>">
                <input type="hidden" name="adminGameAccess" class="form-control" value="<?php echo htmlspecialchars($adminGameAccess)?>">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                <input type="submit" class="btn btn-secondary btn-lg" name="editEnd" value="Oui">
            </form>

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
