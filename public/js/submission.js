$(document).ready(function () {
    var searchTimeout; // Variable pour stocker le délai avant la recherche
    let searchApiUrl = $('.search-container').data('search-url'); // Récupération de l'URL de l'API de recherche à partir de l'attribut data

    $('#search').on('input', function () {
        var query = $(this).val(); // Récupère la valeur du champ de recherche
        clearTimeout(searchTimeout); // Annule le délai précédent si l'utilisateur tape rapidement

        if (query.length > 2) { // Si la requête a plus de 2 caractères
            searchTimeout = setTimeout(function () { // Lance une recherche après un délai de 300ms
                $('#spinner').removeClass('hidden'); // Affiche le spinner de chargement
                $.ajax({
                    url: searchApiUrl, // Utilise l'URL d'API récupérée
                    type: 'GET', // Utilise la méthode GET pour l'appel AJAX
                    data: { game: query }, // Envoie la requête avec la valeur de recherche
                    success: function (data) { // Si la requête réussit
                        var resultDiv = $('#result'); // Sélectionne l'élément où afficher les résultats
                        console.log(data); // Affiche les données reçues dans la console
                        resultDiv.empty(); // Vide les anciens résultats

                        if (data.length > 0) { // Si des résultats sont retournés
                            var list = $('<ul class="absolute w-[100%] z-50 mt-2"></ul>'); // Crée une liste pour afficher les résultats
                            data.forEach(function (item) { // Boucle sur chaque élément de la réponse
                                console.log(item); // Affiche l'élément dans la console
                                $('#spinner').addClass('hidden'); // Cache le spinner
                                list.append( // Ajoute chaque résultat sous forme de liste
                                    '<li class="result-item flex items-center px-10 py-2 bg-white dark:bg-neutral-800 border-b border-indigo-800 dark:border-indigo-200 justify-between cursor-pointer hover:bg-indigo-200 dark:hover:bg-indigo-800" data-id="' + item.id + '">' +
                                    '<div class="flex text-black dark:text-white">' + item.name + ' (' + item.date + ')' + '</div>' +
                                    `
                                        <div class="flex-shrink-0 ml-4">${item.cover && item.cover.image_id
                                        ? `<img loading="lazy" src="https://images.igdb.com/igdb/image/upload/t_thumb/${item.cover.image_id}.webp" alt="${item.name} cover" class="w-16 h-20 object-cover rounded">`
                                        : '<div class="w-16 h-20 bg-gray-300 rounded flex items-center justify-center text-gray-500">No image</div>'}
                                        </div>
                                    </li>
                                    `
                                );
                            });
                            resultDiv.append(list); // Ajoute la liste des résultats au conteneur
                        } else {
                            $('#spinner').addClass('hidden'); // Cache le spinner
                            resultDiv.append('<div>No result found</div>'); // Affiche "Aucun résultat trouvé" si la recherche est vide
                        }
                    },
                    error: function () { // Si une erreur se produit dans l'appel AJAX
                        $('#spinner').addClass('hidden'); // Cache le spinner
                        $('#result').html('<div>An error occurred</div>'); // Affiche un message d'erreur
                    }
                });
            }, 300);  // Délai de 300ms pour éviter trop de requêtes
        } else {
            $('#result').empty(); // Vide les résultats si la requête est trop courte
        }
    });

    $(document).ready(function () {
        var storedGame = sessionStorage.getItem('selectedGame'); // Récupère le jeu stocké dans le sessionStorage
        var storedGameId = sessionStorage.getItem('gameId'); // Récupère l'ID du jeu stocké dans le sessionStorage

        // Ajoute le jeu sélectionné dans le champ de recherche lorsqu'on clique sur un élément de résultat
        $(document).on('click', '.result-item', function () {
            var selectedGame = $(this).text(); // Récupère le texte du jeu sélectionné
            var gameId = $(this).data('id'); // Récupère l'ID du jeu sélectionné
            $('#search').val(selectedGame); // Met à jour le champ de recherche avec le jeu sélectionné
            $('#result').empty(); // Vide les résultats après la sélection
            if (selectedGame) {
                $('#form').slideDown('slow'); // Affiche lentement le formulaire
                $('#form').addClass(' flex flex-col items-center justify-center'); // Ajoute des classes de style au formulaire
                $('input[name="feature[id_game_api]"]').val(gameId); // Remplit le champ caché avec l'ID du jeu sélectionné
                $('#clearSelection').removeClass('hidden'); // Affiche le bouton de suppression

                sessionStorage.setItem('selectedGame', selectedGame); // Stocke le jeu sélectionné dans le sessionStorage
                sessionStorage.setItem('gameId', gameId); // Stocke l'ID du jeu dans le sessionStorage
                console.log("Selected game ID:", gameId); // Affiche l'ID du jeu sélectionné dans la console
            }
        });

        // Si une erreur est présente, affiche le jeu stocké dans le sessionStorage
        if ($('#form').find('.formErrorMessage').children().length > 0) {
            if (storedGame && storedGameId) { // Si des valeurs sont stockées
                $('#search').val(storedGame); // Remplit le champ de recherche avec le jeu stocké
                $('input[name="feature[id_game_api]"]').val(storedGameId); // Remplit le champ caché avec l'ID stocké
                $('#form').slideDown(); // Affiche le formulaire
                $('#form').addClass('flex flex-col items-center justify-center'); // Ajoute des classes de style au formulaire
                $('#clearSelection').removeClass('hidden'); // Affiche le bouton de suppression
            }
        }
    });

    // Gestionnaire pour le bouton de suppression
    $('#clearSelection').on('click', function () {
        $('#search').val(''); // Vide le champ de recherche
        $('input[name="feature[id_game_api]"]').val(''); // Vide le champ caché avec l'ID du jeu
        $('#form').slideUp('slow'); // Masque le formulaire
        $('#clearSelection').addClass('hidden'); // Cache le bouton de suppression
    });

    // Fermer les résultats si on clique en dehors ou les remontrer si on reclique sur la barre de recherche
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.search-container').length) { // Si on clique en dehors de la zone de recherche
            $('#result').hide(); // Masque les résultats
        }
        if ($(event.target).closest('.search-container').length) { // Si on clique sur la barre de recherche
            $('#result').show(); // Affiche les résultats
        }
    });
});