$(document).ready(function () { // Lorsque le document est prêt
    const selectedFilters = document.getElementById('selected-filters'); // Déclare la constante pour le conteneur des filtres sélectionnés
    const selectedFiltersP = document.getElementById('selected-filters-head'); // Déclare la constante pour le titre des filtres sélectionnés
    const resetFilters = document.getElementById('reset-filters'); // Déclare la constante pour le bouton de réinitialisation des filtres

    function updateSelectedFilters() { // Fonction pour mettre à jour l'affichage des filtres sélectionnés
        var selectedGenres = $('input:checkbox[name="genre"]:checked').map(function () { // Récupère les genres sélectionnés
            return this.value; // Retourne la valeur du genre sélectionné
        }).get(); // Construit un tableau des genres sélectionnés

        var selectedPlatforms = $('input:checkbox[name="platform"]:checked').map(function () { // Récupère les plateformes sélectionnées
            return this.value; // Retourne la valeur de la plateforme sélectionnée
        }).get(); // Construit un tableau des plateformes sélectionnées

        var selectedDisabilities = $('input:checkbox[name="disability"]:checked').map(function () { // Récupère les handicaps sélectionnés
            return this.value; // Retourne la valeur du handicap sélectionné
        }).get(); // Construit un tableau des handicaps sélectionnés

        if (selectedGenres.length === 0 && selectedPlatforms.length === 0 && selectedDisabilities.length === 0) { // Si aucun filtre n'est sélectionné
            selectedFiltersP.classList.add('hidden'); // Masque le titre des filtres sélectionnés
            selectedFilters.innerHTML = ''; // Vide le conteneur des filtres
        } else { // Si au moins un filtre est sélectionné
            selectedFiltersP.classList.remove('hidden'); // Affiche le titre des filtres sélectionnés

            // Affiche les genres sous forme de badges
            selectedFilters.innerHTML = selectedGenres.map(genre =>
                `<div class="inline-block bg-indigo-800 dark:bg-indigo-200 text-white dark:text-black rounded-full px-3 py-1 text-xs font-semibold mr-2 mb-2">
                    <div class="flex content-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 512 512" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M192 104.8c0-9.2-5.8-17.3-13.2-22.8C167.2 73.3 160 61.3 160 48c0-26.5 28.7-48 64-48s64 21.5 64 48c0 13.3-7.2 25.3-18.8 34c-7.4 5.5-13.2 13.6-13.2 22.8c0 12.8 10.4 23.2 23.2 23.2H336c26.5 0 48 21.5 48 48v56.8c0 12.8 10.4 23.2 23.2 23.2c9.2 0 17.3-5.8 22.8-13.2c8.7-11.6 20.7-18.8 34-18.8c26.5 0 48 28.7 48 64s-21.5 64-48 64c-13.3 0-25.3-7.2-34-18.8c-5.5-7.4-13.6-13.2-22.8-13.2c-12.8 0-23.2 10.4-23.2 23.2V464c0 26.5-21.5 48-48 48H279.2c-12.8 0-23.2-10.4-23.2-23.2c0-9.2 5.8-17.3 13.2-22.8c11.6-8.7 18.8-20.7 18.8-34c0-26.5-28.7-48-64-48s-64 21.5-64 48c0 13.3 7.2 25.3 18.8 34c7.4 5.5 13.2 13.6 13.2 22.8c0 12.8-10.4 23.2-23.2 23.2H48c-26.5 0-48-21.5-48-48V343.2C0 330.4 10.4 320 23.2 320c9.2 0 17.3 5.8 22.8 13.2C54.7 344.8 66.7 352 80 352c26.5 0 48-28.7 48-64s-21.5-64-48-64c-13.3 0-25.3 7.2-34 18.8C40.5 250.2 32.4 256 23.2 256C10.4 256 0 245.6 0 232.8V176c0-26.5 21.5-48 48-48H168.8c12.8 0 23.2-10.4 23.2-23.2z"/>
                        </svg>
                        <span>${genre}</span>
                    </div>
                </div>`
            ).join(''); // Concatène les badges des genres sélectionnés

            // Affiche les plateformes sous forme de badges
            selectedFilters.innerHTML += selectedPlatforms.map(platform =>
                `<div class="inline-block bg-indigo-800 dark:bg-indigo-200 text-white dark:text-black rounded-full px-3 py-1 text-xs font-semibold mr-2 mb-2">
                    <div class="flex content-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg">
                            <path d="M192 64C86 64 0 150 0 256S86 448 192 448H448c106 0 192-86 192-192s-86-192-192-192H192zM496 168a40 40 0 1 1 0 80 40 40 0 1 1 0-80zM392 304a40 40 0 1 1 80 0 40 40 0 1 1 -80 0zM168 200c0-13.3 10.7-24 24-24s24 10.7 24 24v32h32c13.3 0 24 10.7 24 24s-10.7 24-24 24H216v32c0 13.3-10.7 24-24 24s-24-10.7-24-24V280H136c-13.3 0-24-10.7-24-24s10.7-24 24-24h32V200z"/>
                        </svg>
                        <span>${platform}</span>
                    </div>
                </div>`
            ).join(''); // Concatène les badges des plateformes sélectionnées

            // Affiche les handicaps sous forme de badges
            selectedFilters.innerHTML += selectedDisabilities.map(disability =>
                `<div class="inline-block bg-indigo-800 dark:bg-indigo-200 text-white dark:text-black rounded-full px-3 py-1 text-xs font-semibold mr-2 mb-2">
                    <div class="flex content-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 448 512" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M320 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zM204.5 121.3c-5.4-2.5-11.7-1.9-16.4 1.7l-40.9 30.7c-14.1 10.6-34.2 7.7-44.8-6.4s-7.7-34.2 6.4-44.8l40.9-30.7c23.7-17.8 55.3-21 82.1-8.4l90.4 42.5c29.1 13.7 36.8 51.6 15.2 75.5L299.1 224h97.4c30.3 0 53 27.7 47.1 57.4L415.4 422.3c-3.5 17.3-20.3 28.6-37.7 25.1s-28.6-20.3-25.1-37.7L377 288H306.7c8.6 19.6 13.3 41.2 13.3 64c0 88.4-71.6 160-160 160S0 440.4 0 352s71.6-160 160-160c11.1 0 22 1.1 32.4 3.3l54.2-54.2-42.1-19.8zM160 448a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/>
                        </svg>
                        <span>${disability}</span>
                    </div>
                </div>`
            ).join(''); // Concatène les badges des handicaps sélectionnés
        }
    }

    // Ajoute un écouteur d'événements sur les checkboxes des genres, plateformes et handicaps
    $('input:checkbox[name="genre"], input:checkbox[name="platform"], input:checkbox[name="disability"]').on('change', function () { 
        updateSelectedFilters(); // Met à jour les filtres affichés
        table.draw(); // Redessine la table pour appliquer les filtres
    });

    // Fonction de recherche personnalisée pour les genres
    $.fn.dataTable.ext.search.push(
        function (settings, searchData, index, rowData, counter) { // Fonction de filtrage
            var selectedGenres = $('input:checkbox[name="genre"]:checked').map(function () { // Récupère les genres sélectionnés
                return this.value; // Retourne la valeur du genre sélectionné
            }).get(); // Construit un tableau des genres sélectionnés

            if (selectedGenres.length === 0) { // Si aucun genre n'est sélectionné
                return true; // Affiche toutes les lignes
            }

            var gameGenres = searchData[2]; // Récupère les genres du jeu dans le tableau de données
            return selectedGenres.every(selectedGenre => gameGenres.includes(selectedGenre)); // Vérifie si tous les genres sélectionnés sont présents
        }
    );

    // Fonction de recherche personnalisée pour les plateformes
    $.fn.dataTable.ext.search.push(
        function (settings, searchData, index, rowData, counter) { // Fonction de filtrage
            var selectedPlatforms = $('input:checkbox[name="platform"]:checked').map(function () { // Récupère les plateformes sélectionnées
                return this.value; // Retourne la valeur de la plateforme sélectionnée
            }).get(); // Construit un tableau des plateformes sélectionnées

            if (selectedPlatforms.length === 0) { // Si aucune plateforme n'est sélectionnée
                return true; // Affiche toutes les lignes
            }

            var gamePlatforms = searchData[3]; // Récupère les plateformes du jeu dans le tableau de données
            return selectedPlatforms.every(selectedPlatform => gamePlatforms.includes(selectedPlatform)); // Vérifie si toutes les plateformes sélectionnées sont présentes
        }
    );

    // Fonction de recherche personnalisée pour les handicaps
    $.fn.dataTable.ext.search.push(
        function (settings, searchData, index, rowData, counter) { // Fonction de filtrage
            var selectedDisabilities = $('input:checkbox[name="disability"]:checked').map(function () { // Récupère les handicaps sélectionnés
                return this.value; // Retourne la valeur du handicap sélectionné
            }).get(); // Construit un tableau des handicaps sélectionnés

            if (selectedDisabilities.length === 0) { // Si aucun handicap n'est sélectionné
                return true; // Affiche toutes les lignes
            }

            var $cell = $(table.cell(index, 4).node()); // Récupère la cellule contenant les handicaps du jeu
            var gameDisabilities = $cell.find('img').map(function () { // Récupère les handicaps du jeu à partir des attributs data des images
                return $(this).data('disability'); // Retourne la valeur du handicap
            }).get(); // Construit un tableau des handicaps du jeu

            return selectedDisabilities.every(selectedDisability => gameDisabilities.includes(selectedDisability)); // Vérifie si tous les handicaps sélectionnés sont présents
        }
    );

    // Ajoute un écouteur d'événements sur le bouton de réinitialisation des filtres
    resetFilters.addEventListener('click', function () { 
        $('input:checkbox').prop('checked', false); // Décoche toutes les cases à cocher
        updateSelectedFilters(); // Met à jour l'affichage des filtres
        table.draw(); // Redessine la table pour appliquer les filtres
    });

    // Initialise la table DataTables avec l'option responsive
    var table = $('#dataTable1').DataTable({
        responsive: true // Active la réactivité de la table
    });

    updateSelectedFilters(); // Appelle la fonction pour afficher correctement les filtres au chargement initial
});
