// Menu Burger
document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menuToggle');
    const closeMenu = document.getElementById('closeMenu');
    const mobileMenuContent = document.getElementById('mobileMenuContent');

    menuToggle.addEventListener('click', function () {
        mobileMenuContent.classList.remove('translate-x-full');
    });

    closeMenu.addEventListener('click', function () {
        mobileMenuContent.classList.add('translate-x-full')
    })
});

// Dark Mode
document.addEventListener("DOMContentLoaded", () => {
    const button = document.querySelector("#toggle"); // Élément normal
    const buttonMobileMenu = document.querySelector("#toggleMobileMenu"); // Élément mobile

    // Je check le thème initial du localStorage
    const darkMode = localStorage.getItem("dark-mode");
    if (darkMode === "enabled") {
        document.documentElement.classList.add("dark");
    }

    // J'ajoute le darkMode et je sauvegarde les préférences dans le localStorage
    button.addEventListener("click", () => {
        document.documentElement.classList.toggle("dark");
        if (document.documentElement.classList.contains("dark")) {
            localStorage.setItem("dark-mode", "enabled");
        } else {
            localStorage.setItem("dark-mode", "disabled");
        }
    });

    // Pareil qu'en haut mais en vue mobile
    buttonMobileMenu.addEventListener("click", () => {
        document.documentElement.classList.toggle("dark");
        if (document.documentElement.classList.contains("dark")) {
            localStorage.setItem("dark-mode", "enabled");
        } else {
            localStorage.setItem("dark-mode", "disabled");
        }
    });
});

// Search bar
let routeTemplate = "{{ path('show_game', { 'id': 'ID_PLACEHOLDER', 'slug': 'SLUG_PLACEHOLDER' }) }}";
$(document).ready(function () {
    var searchTimeout;
    $('#search').on('input', function () {
        var query = $(this).val();
        clearTimeout(searchTimeout);
        if (query.length > 2) {
            searchTimeout = setTimeout(function () {
                $.ajax({
                    url: '/searchGame',
                    type: 'GET',
                    data: { search: query },
                    success: function (data) {
                        var resultDiv = $('#result');
                        resultDiv.empty();
                        if (data.length > 0) {
                            var list = $('<ul class="absolute w-[100%] z-50 p-0 m-0 bg-gray-700"></ul>');
                            data.forEach(function (item) {
                                let itemUrl = routeTemplate.replace('ID_PLACEHOLDER', item.id).replace('SLUG_PLACEHOLDER', item.slug);
                                list.append(
                                    '<li class="result-item flex items-center px-2 justify-between cursor-pointer hover:bg-indigo-200 my-2" data-id="' + item.id + '">' +
                                    '<a href="' + itemUrl + '" class="flex items-center justify-between w-full">' +
                                    '<span class="name-date">' + item.name + '</span>' +
                                    `<img loading="lazy" src='https://images.igdb.com/igdb/image/upload/t_thumb/${item.imageUrl}.jpg' alt='${item.name} cover' class="w-20 h-10 object-cover">` +
                                    '</a>' +
                                    '</li>'

                                );
                            });
                            resultDiv.append(list);
                        } else {
                            $('#result').html('<div>No result found</div>');
                        }
                    }
                });
            }, 300);
        } else {
            $('#result').empty();
        }
    });
});