<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminChapterMonsterId'])
&& isset($_POST['adminChapterTitle'])
&& isset($_POST['adminChapterOpening'])
&& isset($_POST['adminChapterEnding'])
&& isset($_POST['token'])
&& isset($_POST['finalAdd']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminChapterMonsterId'])
        && $_POST['adminChapterMonsterId'] >= 1)
        {
            // On récupère les informations du formulaire
            $adminChapterMonsterId = $_POST['adminChapterMonsterId'];
            $adminChapterTitle = $_POST['adminChapterTitle'];
            $adminChapterOpening = $_POST['adminChapterOpening'];
            $adminChapterEnding = $_POST['adminChapterEnding'];
            
            // On ajoute le chapitre dans la base de données
            $addChapter = $bdd->prepare("INSERT INTO car_chapters VALUES(
            NULL,
            :adminChapterMonsterId,
            :adminChapterTitle,
            :adminChapterOpening,
            :adminChapterEnding)");
            $addChapter->execute([
            'adminChapterMonsterId' => $adminChapterMonsterId,
            'adminChapterTitle' => $adminChapterTitle,
            'adminChapterOpening' => $adminChapterOpening,
            'adminChapterEnding' => $adminChapterEnding]);
            $addChapter->closeCursor();
            ?>

            Le chapitre a bien été créé

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
// Si Tous les champs n'ont pas été remplis
else
{
    echo "Erreur : Tous les champs n'ont pas été remplis";
}

require_once("../html/footer.php");
