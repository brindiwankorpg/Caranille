<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../modules/login/index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['secretQuestion']) 
&& isset($_POST['secretAnswer'])
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

        // On récupère les valeurs du formulaire dans une variable
        $secretQuestion = $_POST['secretQuestion'];
        $secretAnswer = $_POST['secretAnswer'];

        // On vérifie si le joueur a jamais créé sa question secrète
        if (($accountSecretQuestion == "" || $accountSecretQuestion == "None") && ($accountSecretAnswer == "" || $accountSecretAnswer == "None"))
        {
            // On met à jour la question secrète dans la base de données
            $updateAccount = $bdd->prepare("UPDATE car_accounts SET 
            accountSecretQuestion = :secretQuestion,
            accountSecretAnswer = :secretAnswer
            WHERE accountId = :accountId");
            $updateAccount->execute(array(
            'secretQuestion' => $secretQuestion,
            'secretAnswer' => $secretAnswer,
            'accountId' => $accountId));
            $updateAccount->closeCursor();
            ?>
            
            La question secrète a bien été créée.

            <hr>

            <form method="POST" action="index.php">
                <input type="submit" name="continue" class="btn btn-secondary btn-lg" value="Continuer">
            </form>
                
            <?php
        }
        else
        {
            ?>
        
            Vous avez déjà une question secrète
        
            <hr>
        
            <form method="POST" action="index.php">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                <input type="submit" name="back" class="btn btn-secondary btn-lg" value="Retour"><br>
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
    echo "Tous les champs n'ont pas été remplis";
}

require_once("../../html/footer.php"); ?>
