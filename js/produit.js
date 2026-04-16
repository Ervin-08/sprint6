/**
 * produit.js — Ajout au panier via Axios (Sprint 5)
 */

/**
 * Met à jour le badge compteur dans le header.
 * Utilise textContent pour éviter les failles XSS.
 *
 * @param {string|number} total
 */
function mettreAJourCompteur(total) {
    const compteur = document.getElementById('compteur');
    if (compteur) {
        compteur.textContent = total;
    }
}

/**
 * Affiche un message de feedback sous le formulaire.
 *
 * @param {boolean} succes
 * @param {string}  message
 */
function afficherFeedback(succes, message) {
    const zone = document.getElementById('ajout-feedback');
    if (!zone) return;

    zone.className = 'ajout-feedback ' + (succes ? 'ajout-feedback--succes' : 'ajout-feedback--erreur');

    if (succes) {
        zone.innerHTML = '✓ ' + message +
            ' <a href="basket.php" class="lien-panier-feedback">Voir mon panier →</a>';
    } else {
        zone.textContent = '✗ ' + message;
    }
}

/**
 * Envoie une requête POST via axios.postForm pour ajouter un produit au panier.
 * Données envoyées en x-www-form-urlencoded → accessibles via $_POST en PHP.
 *
 * @param {number} idProduit
 * @param {number} quantite
 */
function ajouterAuPanier(idProduit, quantite) {
    const btn = document.querySelector('#form-ajout-panier button[type="submit"]');

    btn.disabled = true;
    btn.textContent = 'Ajout en cours…';

    axios.postForm('add_to_cart.php', {
        product_id: idProduit,
        quantite:   quantite,
    })
    .then(function (response) {
        mettreAJourCompteur(response.data);
        afficherFeedback(true, 'Produit ajouté au panier.');
    })
    .catch(function (error) {
        const message = error.response ? error.response.data : 'Une erreur est survenue.';
        afficherFeedback(false, message);
    })
    .finally(function () {
        btn.disabled = false;
        btn.textContent = 'Ajouter au panier';
    });
}

/**
 * Initialise le formulaire d'ajout au panier.
 */
function initFormAjoutPanier() {
    const form = document.getElementById('form-ajout-panier');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const idProduit = parseInt(form.dataset.idProduit, 10);
        const quantite  = parseInt(form.querySelector('#quantite').value, 10);

        ajouterAuPanier(idProduit, quantite);
    });
}

document.addEventListener('DOMContentLoaded', initFormAjoutPanier);
