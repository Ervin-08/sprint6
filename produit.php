<?php
/* ============================================================
   Sprint 5 — Fiche produit + ajout au panier via Axios
   MUST : axios.postForm() vers add_to_cart.php ($_POST)
          Badge compteur mis à jour via textContent (anti-XSS)
          Feedback succès/erreur sous le formulaire
          Lien "Voir mon panier" affiché après ajout
   ============================================================ */

require_once 'connexion.php';

/* ── Fonctions métier ────────────────────────────────────── */

function retrieveProductById(PDO $pdo, $id): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM PRODUIT WHERE id_produit = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    return $produit ?: [];
}

function isProductAvailable(array $produit): bool
{
    $enStock   = (int) $produit['stock_produit'] > 0;
    $dispVente = !isset($produit['statut_produit']) || (int) $produit['statut_produit'] === 1;
    return $enStock && $dispVente;
}

/* ── Sécurisation de l'identifiant reçu en URL ───────────── */
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

/* ── Récupération du produit ─────────────────────────────── */
$produit       = [];
$erreur        = null;
$avertissement = null;

if ($id <= 0) {
    $erreur = 'Identifiant de produit invalide.';
} else {
    try {
        $pdo     = getConnexion();
        $produit = retrieveProductById($pdo, $id);

        if (empty($produit)) {
            $erreur = 'Ce produit est introuvable.';
        } elseif (!isProductAvailable($produit)) {
            $avertissement = 'Ce produit n\'est actuellement pas disponible à la vente.';
        }
    } catch (PDOException $e) {
        $erreur = 'Impossible de charger le produit. Veuillez réessayer plus tard.';
    }
}

/* ── Calcul des prix ─────────────────────────────────────── */
$prixHtva = null;
$prixTvac = null;

if (!empty($produit)) {
    $prixHtva = (float) $produit['prix_produit'];
    $prixTvac = $prixHtva * (1 + TVA);
}

/* ── Helpers d'affichage ─────────────────────────────────── */
function formatPrix(float $prix): string
{
    return number_format($prix, 2, ',', ' ') . ' €';
}

function initiale(string $nom): string
{
    return mb_strtoupper(mb_substr($nom, 0, 1, 'UTF-8'), 'UTF-8');
}

/* ── Titre de la page ────────────────────────────────────── */
$titrePage = !empty($produit)
    ? htmlspecialchars($produit['nom_produit']) . ' — Boutique Parfums'
    : 'Produit introuvable — Boutique Parfums';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titrePage ?></title>
    <link rel="stylesheet" href="style.css">
    <?php if (!empty($produit)): ?>
    <meta name="description"
          content="<?= htmlspecialchars($produit['description_produit']) ?>">
    <?php endif; ?>
</head>
<body>

    <header>
        <h1>Boutique Parfums</h1>
        <nav aria-label="Navigation principale">
            <a href="produits.php">Produits</a>
        </nav>
        <nav aria-label="Navigation secondaire">
            <a href="basket.php" title="Mon panier" class="nav-panier">
                🛒 Panier
                <span id="compteur" class="panier-badge">
                    <?= array_sum($_SESSION['panier'] ?? []) ?>
                </span>
            </a>
        </nav>
    </header>

    <main>

        <a href="produits.php" class="lien-retour" title="Retour à la liste des produits">
            ← Retour aux produits
        </a>

        <?php if ($erreur): ?>

            <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>

        <?php else: ?>

            <?php if ($avertissement): ?>
                <p class="message message--avertissement">
                    ⚠ <?= htmlspecialchars($avertissement) ?>
                </p>
            <?php endif; ?>

            <?php
            $nom           = htmlspecialchars($produit['nom_produit']);
            $marque        = htmlspecialchars($produit['marque_produit']);
            $categorie     = htmlspecialchars($produit['categorie_produit']);
            $concentration = htmlspecialchars($produit['concentration_parfum']);
            $description   = htmlspecialchars($produit['description_produit']);
            $stock         = (int) $produit['stock_produit'];
            ?>

            <section id="fiche-produit" aria-label="Fiche du produit <?= $nom ?>">

                <h2><?= $nom ?></h2>

                <figure class="fiche-visuel" aria-hidden="true">
                    <?php
                    $imagesProduits = [
                            'bleu intense' => 'images/bleu_intense.png',
                            'rose élégante' => 'images/rose_elegante.png',
                            'citrus energy' => 'images/citrus_energy.png',
                            'nuit mystérieuse' => 'images/nuit_mysterieuse.png',
                            'océan sport' => 'images/ocean_sport.png',
                            'vanilla dream' => 'images/vanilla_dream.png'
                    ];
                    $nomCle    = mb_strtolower(trim($produit['nom_produit']), 'UTF-8');
                    $imagePath = $imagesProduits[$nomCle] ?? null;
                    ?>
                    <?php if ($imagePath): ?>
                        <img src="<?= htmlspecialchars($imagePath) ?>"
                             alt="<?= $nom ?>"
                             class="fiche-img">
                    <?php else: ?>
                        <div class="fiche-placeholder">
                            <span class="fiche-initiale"><?= initiale($nom) ?></span>
                            <span class="fiche-concentration"><?= $concentration ?></span>
                        </div>
                    <?php endif; ?>
                    <figcaption><?= $nom ?> — <?= $marque ?></figcaption>
                </figure>

                <div class="produit-infos">

                    <section aria-label="Identification">
                        <h3>Marque</h3>
                        <p><?= $marque ?></p>
                        <p class="fiche-categorie"><?= $categorie ?> · <?= $concentration ?></p>
                    </section>

                    <section aria-label="Description">
                        <h3>Description</h3>
                        <p><?= $description ?></p>
                    </section>

                    <section aria-label="Prix">
                        <h3>Prix</h3>
                        <dl>
                            <dt>Prix HTVA</dt>
                            <dd><?= formatPrix($prixHtva) ?></dd>

                            <dt>TVA (<?= (int)(TVA * 100) ?> %)</dt>
                            <dd><?= formatPrix($prixTvac - $prixHtva) ?></dd>

                            <dt>Prix TVAC</dt>
                            <dd class="prix-tvac"><?= formatPrix($prixTvac) ?></dd>
                        </dl>
                    </section>

                    <section aria-label="Disponibilité">
                        <h3>Disponibilité</h3>
                        <?php if ($stock > 0): ?>
                            <p>
                                <strong class="stock-ok">En stock</strong>
                                — <?= $stock ?> unité<?= $stock > 1 ? 's' : '' ?> disponible<?= $stock > 1 ? 's' : '' ?>
                            </p>
                        <?php else: ?>
                            <p>
                                <strong class="stock-vide">Rupture de stock</strong>
                                — Ce produit n'est pas disponible actuellement.
                            </p>
                        <?php endif; ?>
                    </section>

                    <!-- ── Formulaire d'ajout au panier (AJAX — Sprint 5) ── -->
                    <?php if (isProductAvailable($produit)): ?>

                        <?php $qteMax = min($stock, 5); ?>

                        <section aria-label="Ajouter au panier">
                            <h3>Ajouter au panier</h3>

                            <form id="form-ajout-panier"
                                  class="form-ajout-panier"
                                  data-id-produit="<?= $id ?>">

                                <label for="quantite">Quantité :</label>
                                <select id="quantite" name="quantite">
                                    <?php for ($i = 1; $i <= $qteMax; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>

                                <button type="submit" class="btn-ajout-panier">
                                    Ajouter au panier
                                </button>

                            </form>

                            <!-- Zone de feedback (remplie par JS) -->
                            <div id="ajout-feedback" role="status" aria-live="polite"></div>

                        </section>

                    <?php endif; ?>

                </div>

            </section>

        <?php endif; ?>

    </main>

    <footer>
        <p>&copy; 2026 Boutique Parfums. Tous droits réservés.</p>
    </footer>

    <!-- ── Axios CDN + script produit ── -->
    <?php if (!empty($produit) && isProductAvailable($produit)): ?>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/produit.js"></script>
    <?php endif; ?>

</body>
</html>
