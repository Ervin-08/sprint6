<?php
/* ============================================================
   Connexion PDO partagée + démarrage de session
   Inclure ce fichier dans toutes les pages.
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('TVA', 0.21);   // Taux de TVA : 21 %

function getConnexion(): PDO
{
    return new PDO(
        'mysql:host=localhost;dbname=nveukxthqy_bdd_dwa;charset=utf8mb4',
        'nveukxthqy',
        'HqCkKDnacvP4gzGt6wXF35NU',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
