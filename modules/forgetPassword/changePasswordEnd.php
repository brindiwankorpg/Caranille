<?php
require_once("../../kernel/kernel.php");
require_once("../../kernel/security/passwordManager.php");
require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['accountCode'])
&& isset($_POST['accountPassword'])
&& isset($_POST['accountPasswordConfirm'])
&& isset($_POST['token'])
&& isset($_POST['changePasswordEnd']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On récupère les valeurs du formulaire dans une variable
        $accountCode = $_POST['accountCode'];
        $accountPassword = $_POST['accountPassword'];
        $accountPasswordConfirm = $_POST['accountPasswordConfirm'];

        // On vérifie si les deux mots de passe sont identiques
        if ($accountPassword == $accountPasswordConfirm)
        {
            // On définit la date à partir de laquelle un code est expiré (un code est valable 1 heure)
            $dateLimit = date('Y-m-d H:i:s', time() - 3600);

            // On fait une requête pour vérifier si une demande de réinitialisation de moins d'une heure correspond à ce code
            $accountForgetPasswordQuery = $bdd->prepare("SELECT * FROM car_forgets_passwords
            WHERE accountForgetPasswordEmailCode = ?
            AND accountForgetPasswordDateTime >= ?");
            $accountForgetPasswordQuery->execute([$accountCode, $dateLimit]);
            $accountForgetPasswordRow = $accountForgetPasswordQuery->rowCount();

            // Si une demande de réinitialisation valide est en cours
            if ($accountForgetPasswordRow == 1)
            {
                // Dans ce cas on boucle pour récupérer le tableau retourné par la base de données pour récupérer les informations du compte
                while ($accountForgetPassword = $accountForgetPasswordQuery->fetch())
                {
                    // On récupère les informations de la demande de vérification
                    $accountForgetPasswordId = $accountForgetPassword['accountForgetPasswordId'];
                    $accountForgetPasswordAccountId = $accountForgetPassword['accountForgetPasswordAccountId'];
                    $accountForgetPasswordEmailAdress = $accountForgetPassword['accountForgetPasswordEmailAdress'];
                }

                // On hash le mot de passe soumis
                $accountPasswordHash = PasswordManager::hashPassword($accountPassword);

                // On supprime toutes les demandes de réinitialisation du compte pour qu'aucun code ne puisse resservir
                $deleteForgetPasswordQuery = $bdd->prepare("DELETE FROM car_forgets_passwords
                WHERE accountForgetPasswordAccountId = :accountForgetPasswordAccountId");
                $deleteForgetPasswordQuery->execute(array(
                'accountForgetPasswordAccountId' => $accountForgetPasswordAccountId));
                $deleteForgetPasswordQuery->closeCursor();

                // On met à jour le mot de passe dans la base de données
                $updateAccount = $bdd->prepare("UPDATE car_accounts
                SET accountPassword = :accountPassword
                WHERE accountId = :accountForgetPasswordAccountId");
                $updateAccount->execute(array(
                'accountPassword' => $accountPasswordHash,
                'accountForgetPasswordAccountId' => $accountForgetPasswordAccountId));
                $updateAccount->closeCursor();
                ?>

                Votre mot de passe a bien été changé

                <hr>

                <form method="POST" action="../../modules/login/index.php">
                    <input type="submit" name="continue" class="btn btn-secondary btn-lg" value="Se connecter">
                </form>

                <?php
            }
            // Si le code ne correspond à aucune demande ou qu'il a expiré
            else
            {
                ?>

                Erreur : Ce code ne correspond à aucune demande de réinitialisation de mot de passe ou il a expiré

                <hr>

                <form method="POST" action="../../modules/forgetPassword/enterCode.php">
                    <input type="submit" name="continue" class="btn btn-secondary btn-lg" value="Recommencer">
                </form>

                <?php
            }
            $accountForgetPasswordQuery->closeCursor();
        }
        // Si les deux mots de passe ne sont pas identiques
        else
        {
            echo "Les deux mots de passe ne sont pas identiques";
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
