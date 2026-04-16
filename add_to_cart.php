<?php
/* ============================================================
   add_to_cart.php — Ajout au panier (AJAX via Axios)
   Reçoit : $_POST['product_id'], $_POST['quantite']
   Retourne : array_sum() du panier (total articles) — texte brut
   Erreur    : HTTP 400 + message texte (capturé par .catch())
   ============================================================ */

require_once 'connexion.php';   // démarre la session + connexion PDO

/* ── Fonctions ───────────────────────────────────────────── */

function getProductById(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare('SELECT * FROM PRODUIT WHERE id_produit = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function isProductAvailable(array $produit): bool
{
    $enStock   = (int) $produit['stock_produit'] > 0;
    $dispVente = !isset($produit['statut_produit']) || (int) $produit['statut_produit'] === 1;
    return $enStock && $dispVente;
}

function erreur(string $message): void
{
    http_response_code(400);
    echo $message;
    exit;
}

/* ── Vérification méthode ────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erreur('Méthode non autorisée.');
}

/* ── Lecture et validation des données POST ─────────────── */
$idProduit = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantite  = isset($_POST['quantite'])   ? (int) $_POST['quantite']   : 1;

if ($idProduit <= 0) erreur('Identifiant invalide.');
if ($quantite  <= 0) erreur('La quantité doit être supérieure à zéro.');

/* ── Validation en base de données ─────────────────────── */
try {
    $pdo     = getConnexion();
    $produit = getProductById($pdo, $idProduit);
} catch (PDOException $e) {
    erreur('Erreur de connexion à la base de données.');
}

if (empty($produit))          erreur('Ce produit n\'existe pas.');
if (!isProductAvailable($produit)) erreur('Ce produit n\'est pas disponible à la vente.');

$stock = (int) $produit['stock_produit'];

/* ── Mise à jour de la session ──────────────────────────── */
// Structure : $_SESSION['panier'][$id] = quantité (entier simple)
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$dejaEnPanier = isset($_SESSION['panier'][$idProduit])
    ? (int) $_SESSION['panier'][$idProduit]
    : 0;

$qteTotale = $dejaEnPanier + $quantite;

if ($qteTotale > $stock) {
    erreur("Stock insuffisant ({$stock} dispo, {$dejaEnPanier} déjà dans le panier).");
}

$_SESSION['panier'][$idProduit] = $qteTotale;

/* ── Réponse : total d'articles pour le badge ───────────── */
echo array_sum($_SESSION['panier']);
