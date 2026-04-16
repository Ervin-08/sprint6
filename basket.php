<?php
/* ============================================================
   Sprint 5 — Panier dynamique
   Lit $_SESSION['panier'] (id => quantité)
   Récupère les détails des produits en DB via PDO
   Bouton 🗑️ par ligne → suppression AJAX via Axios (panier.js)
   Badge compteur mis à jour via textContent (anti-XSS)
   ============================================================ */

require_once 'connexion.php';   // session_start() + PDO

function formatPrix(float $prix): string
{
    return number_format($prix, 2, ',', ' ') . ' €';
}

/* ── Chargement du panier depuis la session + DB ─────────── */
$lignesPanier  = [];
$totalArticles = 0;
$totalHtva     = 0.0;
$erreur        = null;

$panier = $_SESSION['panier'] ?? [];

if (!empty($panier)) {
    try {
        $pdo = getConnexion();

        foreach ($panier as $id => $quantite) {
            $sql  = 'SELECT * FROM PRODUIT WHERE id_produit = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produit) continue;   // produit supprimé de la DB

            $prixHtva   = (float) $produit['prix_produit'];
            $totalLigne = $prixHtva * $quantite;

            $lignesPanier[] = [
                'id'         => (int) $id,
                'nom'        => $produit['nom_produit'],
                'image'      => null,   // à étendre si images disponibles
                'quantite'   => (int) $quantite,
                'prixHtva'   => $prixHtva,
                'totalLigne' => $totalLigne,
            ];

            $totalArticles += $quantite;
            $totalHtva     += $totalLigne;
        }
    } catch (PDOException $e) {
        $erreur = 'Impossible de charger le panier. Veuillez réessayer.';
    }
}

$totalTvac = $totalHtva * (1 + TVA);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier — Boutique Parfums</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Récapitulatif de votre panier d'achats.">
</head>
<body>

    <header>
        <h1>Boutique Parfums</h1>
        <nav aria-label="Navigation principale">
            <a href="produits.php">Produits</a>
        </nav>
        <nav aria-label="Navigation secondaire">
            <a href="basket.php" title="Mon panier" class="nav-panier" aria-current="page">
                🛒 Panier
                <span id="compteur" class="panier-badge"><?= $totalArticles ?></span>
            </a>
        </nav>
    </header>

    <main>
        <h2>Mon Panier</h2>

        <?php if ($erreur): ?>

            <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>

        <?php elseif (empty($lignesPanier)): ?>

            <p class="message message--erreur">Votre panier est vide.</p>

        <?php else: ?>

            <section aria-label="Produits dans le panier">
                <table class="panier-table">
                    <caption class="sr-only">Liste des produits dans votre panier</caption>
                    <thead>
                        <tr>
                            <th scope="col">Produit</th>
                            <th scope="col">Prix unitaire (HTVA)</th>
                            <th scope="col">Quantité</th>
                            <th scope="col">Total (HTVA)</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignesPanier as $ligne): ?>
                        <tr class="tr-<?= $ligne['id'] ?>">
                            <td class="panier-produit">
                                <div class="panier-placeholder" aria-hidden="true">
                                    <?= htmlspecialchars(mb_strtoupper(mb_substr($ligne['nom'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                                </div>
                                <span><?= htmlspecialchars($ligne['nom']) ?></span>
                            </td>
                            <td><?= formatPrix($ligne['prixHtva']) ?></td>
                            <td><?= $ligne['quantite'] ?></td>
                            <td><?= formatPrix($ligne['totalLigne']) ?></td>
                            <td>
                                <button class="btn-suppr"
                                        onclick="suppr(<?= $ligne['id'] ?>)"
                                        type="button"
                                        title="Supprimer <?= htmlspecialchars($ligne['nom']) ?>">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="panier-totaux" aria-label="Récapitulatif des totaux">
                <dl>
                    <dt>Nombre d'articles</dt>
                    <dd><?= $totalArticles ?></dd>

                    <dt>Total HTVA</dt>
                    <dd><?= formatPrix($totalHtva) ?></dd>

                    <dt>TVA (<?= (int)(TVA * 100) ?> %)</dt>
                    <dd><?= formatPrix($totalTvac - $totalHtva) ?></dd>

                    <dt>Total TVAC</dt>
                    <dd class="prix-tvac"><?= formatPrix($totalTvac) ?></dd>
                </dl>
            </section>

        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Boutique Parfums. Tous droits réservés.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/panier.js"></script>

</body>
</html>
