<?php 
require_once("../../kernel/kernel.php");

// On détruit la session complète
session_destroy();

// On redirige le joueur vers la page d'accueil
exit(header("Location: ../../index.php")); ?>
