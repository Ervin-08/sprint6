<?php
/* ============================================================
   supprimer_article.php — Suppression d'un article du panier (AJAX)
   Reçoit : $_POST['product_id']
   Retourne : array_sum() du panier (total articles) — texte brut
   Erreur    : HTTP 400 + message texte
   ============================================================ */

require_once 'connexion.php';   // démarre la session

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo 'Méthode non autorisée.';
    exit;
}

$idProduit = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($idProduit <= 0) {
    http_response_code(400);
    echo 'Identifiant invalide.';
    exit;
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

unset($_SESSION['panier'][$idProduit]);

echo array_sum($_SESSION['panier']);
