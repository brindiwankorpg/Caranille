<?php
require_once("../../kernel/kernel.php");
require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['accountPseudo'])
&& isset($_POST['accountEmail'])
&& isset($_POST['token'])
&& isset($_POST['continue']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));

        // On récupère les valeurs du formulaire dans une variable
        $accountPseudo = $_POST['accountPseudo'];
        $accountEmail = $_POST['accountEmail'];

        // On fait une requête pour vérifier si la combinaison pseudo et adresse Email est bonne
        $accountQuery = $bdd->prepare("SELECT * FROM car_accounts
        WHERE accountPseudo = ?
        AND accountEmail = ?");
        $accountQuery->execute([$accountPseudo, $accountEmail]);
        $accountRow = $accountQuery->rowCount();

        // Si la combinaison pseudo et adresse Email est bonne
        if ($accountRow == 1)
        {
            // On fait une boucle pour récupérer les résultats
            while ($account = $accountQuery->fetch())
            {
                // On récupère les informations du compte
                $accountId = $account['accountId'];
                $accountEmail = $account['accountEmail'];
            }

            // On définit la date à partir de laquelle une nouvelle demande est autorisée (une demande toutes les 5 minutes)
            $dateLimit = date('Y-m-d H:i:s', time() - 300);

            // On fait une requête pour vérifier si une demande a déjà été faite il y a moins de 5 minutes
            $forgetPasswordQuery = $bdd->prepare("SELECT * FROM car_forgets_passwords
            WHERE accountForgetPasswordAccountId = ?
            AND accountForgetPasswordDateTime > ?");
            $forgetPasswordQuery->execute([$accountId, $dateLimit]);
            $forgetPasswordRow = $forgetPasswordQuery->rowCount();
            $forgetPasswordQuery->closeCursor();

            // Si aucune demande n'a été faite il y a moins de 5 minutes on envoie un nouveau code
            if ($forgetPasswordRow == 0)
            {
                // On supprime les anciennes demandes du compte pour qu'un seul code soit valable
                $deleteForgetPassword = $bdd->prepare("DELETE FROM car_forgets_passwords
                WHERE accountForgetPasswordAccountId = :accountId");
                $deleteForgetPassword->execute(['accountId' => $accountId]);
                $deleteForgetPassword->closeCursor();

                // On génère un code aléatoire cryptographiquement sécurisé (32 caractères)
                $codeForgetPassword = bin2hex(random_bytes(16));

                // On définit la date de la demande
                $date = date('Y-m-d H:i:s');

                $addForgetPassword = $bdd->prepare("INSERT INTO car_forgets_passwords VALUES(
                NULL,
                :accountId,
                :accountEmail,
                :codeForgetPassword,
                :date)");
                $addForgetPassword->execute([
                'accountId' => $accountId,
                'accountEmail' => $accountEmail,
                'codeForgetPassword' => $codeForgetPassword,
                'date' => $date]);
                $addForgetPassword->closeCursor();

                $from = "noreply@caranille.com";

                $to = $accountEmail;

                // On encode le sujet pour que les accents s'affichent correctement dans toutes les messageries
                $subject = "=?UTF-8?B?" . base64_encode("Mot de passe oublié") . "?=";

                $message = "Voici votre code à saisir dans Mon compte -> Code pour réinitialiser votre mot de passe (valable 1 heure) : \n\n$codeForgetPassword\n\nSi vous n'êtes pas à l'origine de cette demande veuillez ne pas tenir compte de ce mail.";

                // On précise que le message est en UTF-8 pour que les accents s'affichent correctement
                $headers = "From: " . $from . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8";

                mail($to, $subject, $message, $headers);
            }
        }
        $accountQuery->closeCursor();

        // On affiche toujours le même message pour ne pas révéler si le pseudo et l'adresse email existent
        ?>

        Si ce pseudo et cette adresse email correspondent à un compte, un code valable 1 heure vous a été envoyé par email. Veuillez cliquer sur "Saisir le code" ci-dessous afin de continuer

        <hr>

        <form method="POST" action="enterCode.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
            <input type="submit" name="enterCode" class="btn btn-secondary btn-lg" value="Saisir le code">
        </form>

        <?php
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
