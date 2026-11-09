<?php require_once("../html/header.php");

if (isset($_POST['databaseName'])
&& isset($_POST['databaseHost'])
&& isset($_POST['databaseUser'])
&& isset($_POST['databasePassword'])
&& isset($_POST['databasePort'])
&& isset($_POST['token']))
{
	// Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
		$_SESSION['token'] = NULL;
		
		// Comme il y a un nouveau formulaire on régénère un nouveau token
        $_SESSION['token'] = bin2hex(random_bytes(32));
        
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['databasePort'])
        && $_POST['databasePort'] >= 0)
        {
			// On récupère les valeurs du formulaire dans une variable
		    $databaseName = $_POST['databaseName'];
		    $databaseHost = $_POST['databaseHost'];
		    $databaseUser = $_POST['databaseUser'];
		    $databasePassword = $_POST['databasePassword'];
		    $databasePort = $_POST['databasePort'];

		    // On tente de créer la base de données automatiquement si elle n'existe pas encore
		    try
		    {
		        $initCommandAttr = defined('Pdo\\Mysql::ATTR_INIT_COMMAND')
		            ? Pdo\Mysql::ATTR_INIT_COMMAND
		            : PDO::MYSQL_ATTR_INIT_COMMAND;
		        $pdoServer = new PDO("mysql:host=$databaseHost;port=$databasePort", $databaseUser, $databasePassword, array($initCommandAttr => 'SET NAMES utf8'));
		        $pdoServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		        $safeDatabaseName = str_replace('`', '``', $databaseName);
		        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$safeDatabaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
		        $pdoServer = null;
		    }
		    catch (PDOException $e)
		    {
		        // Si l'utilisateur SQL n'a pas le droit CREATE DATABASE mais que la base existe déjà, la connexion suivante prendra le relais
		    }

		    // On protège les valeurs avant de les écrire dans le fichier config.php pour empêcher l'injection de code PHP
		    $databaseDsn = var_export("mysql:dbname=$databaseName;host=$databaseHost;port=$databasePort", true);
		    $databaseUser = var_export($databaseUser, true);
		    $databasePassword = var_export($databasePassword, true);

		    // On crée le fichier config.php et on y écrit les informations de connexion SQL
		    $openSql = fopen('../../config.php', 'w');
		    fwrite($openSql, "<?php
		    // Version of Caranille
		    \$version = \"2.0.0\";
		    \$dsn = $databaseDsn;
		    \$user = $databaseUser;
		    \$password = $databasePassword;
		
		    // LAUNCH THE CONNECTION
		    try 
		    {
				\$initCommandAttr = defined('Pdo\\\\Mysql::ATTR_INIT_COMMAND')
					? Pdo\\Mysql::ATTR_INIT_COMMAND
					: PDO::MYSQL_ATTR_INIT_COMMAND;
				\$bdd = new PDO(\$dsn, \$user, \$password, array(\$initCommandAttr => 'SET NAMES utf8'));
		        \$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    } 
		    catch (PDOException \$e) 
		    {		
				?>	

				<h3>Les informations de connexion à la base de données sont incorrectes (vérifiez également que la base de données existe bien sur votre serveur MySQL) !</h3><br />

				<b>Caranille était en cours d'installation ?</b><br /><br />
				
				Supprimez le fichier \"config.php\" dans le dossier \"kernel\" du site et retournez sur la page d'accueil du site

				<hr>

				<b>Caranille est déjà installé ?</b><br /><br />

				Vérifiez le fichier \"config.php\" dans le dossier \"kernel\" du site et retournez sur la page d'accueil du site

				<?php

				exit();
		    }
		    ?>");
		    fclose($openSql);

		    // On indique que l'installation est en cours pour autoriser l'accès aux étapes suivantes
		    $_SESSION['install'] = 1;

		    // On inclut le fichier précédemment créé
		    include("../../config.php");
		
		    $installSqlQuery = $bdd->query(file_get_contents('../sql/ddb.sql'));
		    $installSqlQuery->closeCursor();
		    ?>

			<p><h4>Étape 3/4 - Choix d'installation</h4></p>

		    <form method="POST" action="step-4.php">
				Type d'installation : <select class="form-control" name="installationType">
					<option value="fullInstall">Complète (avec exemples)</option>
					<option value="minInstall">Minimale (sans exemples)</option>
				</select>
				<input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
		        <input type="submit" class="btn btn-secondary btn-lg" name="continuer" value="Continuer">
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
    echo "Tous les champs n'ont pas été remplis";
}

require_once("../html/footer.php"); ?>
