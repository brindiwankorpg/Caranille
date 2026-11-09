			</div>
		</main>

        <?php
            // On récupère une seconde fois le temps Unix actuel
            $timeEnd = microtime(true);
            // On soustrait le temps Unix obtenu dans le header de celui obtenu juste au-dessus afin d'obtenir le temps d'exécution de la page
            $time = $timeEnd - $timeStart;
            // On formate le résultat en secondes
            $pageLoadTime = number_format($time, 3);
            // On affiche le résultat en bas de la page
            echo "<center>Temps d'exécution de la page : " . htmlspecialchars($pageLoadTime) . " secondes<br /></center>";
            echo "<center>Caranille : " . htmlspecialchars($version) . "</center>";
        ?>

            <script src="../../js/bootstrap.bundle.min.js"></script>
    </body>
</html>

<?php
// On ferme la connexion à la base de données
$bdd = null;
?>
