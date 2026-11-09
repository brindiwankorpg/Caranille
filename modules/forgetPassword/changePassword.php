<?php
require_once("../../kernel/kernel.php");
require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['accountCode'])
&& isset($_POST['token'])
&& isset($_POST['resetPassword']))
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
            ?>

            Veuillez saisir un nouveau mot de passe et le confirmer

            <hr>

            <form method="POST" action="../../modules/forgetPassword/changePasswordEnd.php" autocomplete="off">
                Mot de passe : <input type="password" class="form-control" name="accountPassword" autocomplete="new-password" required>
                Confirmez : <input type="password" class="form-control" name="accountPasswordConfirm" autocomplete="new-password" required>
                <input type="hidden" name="accountCode" value="<?php echo htmlspecialchars($accountCode)?>">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
                <input type="submit" name="changePasswordEnd" class="btn btn-secondary btn-lg" value="Changer le mot de passe">
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
