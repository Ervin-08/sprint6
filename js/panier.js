/**
 * panier.js — Suppression d'articles du panier via Axios (Sprint 5)
 */

/**
 * Met à jour le badge compteur dans le header.
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
 * Supprime un article du panier via AJAX.
 * Retire le <tr> correspondant du DOM après confirmation.
 *
 * @param {number} idProduit
 */
function suppr(idProduit) {
    if (!confirm('Supprimer cet article du panier ?')) return;

    axios.postForm('supprimer_article.php', { product_id: idProduit })
        .then(function (response) {
            // Retirer la ligne du tableau
            const ligne = document.querySelector('.tr-' + idProduit);
            if (ligne) ligne.remove();

            // Mettre à jour le badge
            mettreAJourCompteur(response.data);

            // Afficher un message si le panier est vide
            if (parseInt(response.data, 10) === 0) {
                afficherPanierVide();
            }
        })
        .catch(function (error) {
            const message = error.response ? error.response.data : 'Une erreur est survenue.';
            alert('Erreur : ' + message);
        });
}

/**
 * Affiche un message "panier vide" en remplaçant le tableau.
 */
function afficherPanierVide() {
    const section = document.querySelector('.panier-table');
    const totaux  = document.querySelector('.panier-totaux');

    if (section) section.remove();
    if (totaux)  totaux.remove();

    const main = document.querySelector('main');
    const msg  = document.createElement('p');
    msg.className   = 'message message--erreur';
    msg.textContent = 'Votre panier est vide.';
    main.appendChild(msg);
}
