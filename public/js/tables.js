// Script qui permet de rendre toute une ligne cliquable d'un tableau

// J'attends que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function () {
    // Je récupère tous les éléments du tableau qui ont un attribut data-href
    const rows = document.querySelectorAll('tr[data-href]');
    // Pour chaque élément trouvé
    rows.forEach(row => {
        // J'ajoute un écouteur d'événement sur le click
        row.addEventListener('click', function () {
            // Je redirige vers l'URL contenue dans l'attribut data-href
            window.location.href = this.dataset.href;
        });
    });
});