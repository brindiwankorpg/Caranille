<?php
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['pictureFile'])
&& isset($_POST['token'])
&& isset($_POST['finalDelete']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On récupère uniquement le nom de l'image du formulaire précédent (basename empêche de remonter dans les dossiers avec ../)
        $adminFile = basename($_POST['pictureFile']);

        // Si l'image n'est pas l'image par défaut
        if ($adminFile != "default.png")
        {
            // Si l'image existe bien dans le dossier on la supprime
            if (file_exists("../../img/parchments/" . $adminFile))
            {
                unlink("../../img/parchments/" . $adminFile);
                ?>

                L'image a bien été supprimée

                <hr>

                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>

                <?php
            }
            // Si l'image n'existe pas dans le dossier
            else
            {
                ?>

                Erreur : Cette image n'existe pas

                <hr>

                <form method="POST" action="index.php">
                    <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
                </form>

                <?php
            }
        }
        // Si l'image est l'image par défaut
        else
        {
            ?>

            Erreur : Il est impossible de supprimer l'image par défaut

            <hr>

            <form method="POST" action="index.php">
                <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
            </form>

            <?php
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
