$(document).ready(function () {
    var searchTimeout;
    let searchApiUrl = $('.search-container').data('search-url');

    $('#search').on('input', function () {
        var query = $(this).val();
        clearTimeout(searchTimeout);

        if (query.length > 2) {
            searchTimeout = setTimeout(function () {
                $('#spinner').removeClass('hidden');
                $.ajax({
                    url: searchApiUrl,
                    type: 'GET',
                    data: { game: query },
                    success: function (data) {
                        var resultDiv = $('#result');
                        console.log(data);
                        resultDiv.empty();

                        if (data.length > 0) {
                            var list = $('<ul class="absolute w-[100%] z-50 p-0 m-0 bg-gray-200"></ul>');
                            data.forEach(function (item) {
                                console.log(item);
                                $('#spinner').addClass('hidden');
                                list.append(
                                    '<li class="result-item flex items-center px-10 justify-between cursor-pointer hover:bg-indigo-200 my-2" data-id="' + item.id + '">' +
                                    '<div class="flex">' + item.name + ' (' + item.date + ')' + '</div>' +
                                    `
                                        <div class="flex-shrink-0 ml-4">${item.cover && item.cover.image_id
                                        ? `<img loading="lazy" src="https://images.igdb.com/igdb/image/upload/t_thumb/${item.cover.image_id}.webp" alt="${item.name} cover" class="w-16 h-20 object-cover rounded">`
                                        : '<div class="w-16 h-20 bg-gray-300 rounded flex items-center justify-center text-gray-500">No image</div>'}
                                        </div>
                                    </li>
                                    `
                                );
                            });
                            resultDiv.append(list);
                        } else {
                            $('#spinner').addClass('hidden');
                            resultDiv.append('<div>No results found</div>');
                        }
                    },
                    error: function () {
                        $('#spinner').addClass('hidden');
                        $('#result').html('<div>An error occurred</div>');
                    }
                });
            }, 300);  // Délai de 300ms pour éviter trop de requêtes
        } else {
            $('#result').empty();
        }
    });

    $(document).ready(function () {
        // Je récupère les valeurs stockées dans le sessionStorage
        var storedGame = sessionStorage.getItem('selectedGame');
        var storedGameId = sessionStorage.getItem('gameId');

        // Ajoute le jeu sélectionné dans le champ de recherche
        $(document).on('click', '.result-item', function () {
            var selectedGame = $(this).text();
            var gameId = $(this).data('id');
            $('#search').val(selectedGame);
            $('#result').empty();
            if (selectedGame) {
                $('#form').slideDown('slow');
                $('#form').addClass(' flex flex-col items-center justify-center');
                $('input[name="feature[id_game_api]"]').val(gameId);
                $('#clearSelection').removeClass('hidden');

                // Je stocke les valeurs dans le sessionStorage
                sessionStorage.setItem('selectedGame', selectedGame);
                sessionStorage.setItem('gameId', gameId);
                console.log("Selected game ID:", gameId);
            }
        });

        // Si il y a une erreur, j'affiche le jeu stocké dans le sessionStorage
        if ($('#form').find('.formErrorMessage').children().length > 0) {
            if (storedGame && storedGameId) {
                $('#search').val(storedGame);
                $('input[name="feature[id_game_api]"]').val(storedGameId);
                $('#form').slideDown();
                $('#form').addClass('flex flex-col items-center justify-center');
                $('#clearSelection').removeClass('hidden');
            }
        }
    });

    // Gestionnaire pour le bouton de suppression
    $('#clearSelection').on('click', function () {
        $('#search').val('');
        $('input[name="feature[id_game_api]"]').val('');
        $('#form').slideUp('slow');
        $('#clearSelection').addClass('hidden'); // Cacher le bouton de suppression
    });

    // Fermer les résultats si on clique en dehors ou les remontre si on reclique sur la barre de recherche
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.search-container').length) {
            $('#result').hide();
        }
        if ($(event.target).closest('.search-container').length) {
            $('#result').show();
        }
    });
});