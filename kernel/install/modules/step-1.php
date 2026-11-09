<?php require_once("../html/header.php"); ?>

<p><h4>Étape 1/4 - Licence d'utilisation</h4></p>

<p>Avant de procéder à l'installation de Caranille vous devez lire et accepter la licence d'utilisation du logiciel</p>

<form method="POST" action="step-2.php">
    <iframe src="../../../LICENCE.txt" width="100%" height="100%"></iframe>
    En cliquant sur "Installer le logiciel" vous acceptez la licence d'utilisation !<br />
    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
    <input type="submit" class="btn btn-secondary btn-lg" name="install" value="Installer le logiciel">
</form>

<?php require_once("../html/footer.php"); ?>
