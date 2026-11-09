<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");
?>
    
<form method="POST" action="manageMonstersPicture.php">
    Images de monstres : <select name="pictureFile" class="form-control">
            
        <?php
        $dir = '../../img/monsters/';
        // On ouvre le dossier
        if ($dh = opendir($dir)) 
        {
            // On fait une boucle sur chaque fichier
            while (($file = readdir($dh)) !== false) 
            {
                // On affiche chaque fichier dans un menu option
                if($file != '.' && $file != '..') 
                {
                    ?>
                    <option value="<?php echo htmlspecialchars($file)?>"><?php echo htmlspecialchars($file); ?></option>
                    <?php
                }
            }
            //  on ferme la connexion
            closedir($dh);
        }
        ?>

    </select>
    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
    <input type="submit" name="viewPicture" class="btn btn-secondary btn-lg" value="Afficher l'image">
    <input type="submit" name="deletePicture" class="btn btn-secondary btn-lg" value="Supprimer l'image">
</form>

<hr>

<form method="POST" action="addMonstersPicture.php" enctype="multipart/form-data">
    <!-- On limite le fichier à 1000Ko -->
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
    <input type="file" name="picture">
    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
    <input name="upload" class="btn btn-secondary btn-lg" type="submit" value="Envoyer l'image">
</form>

<?php require_once("../html/footer.php");
