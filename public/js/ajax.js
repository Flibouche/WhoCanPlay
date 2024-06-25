$(document).ready(function() {
    $('#search').on('input', function() {
        var query = $(this).val();

        if (query.length > 2) {
            $.ajax({
                url: '{{ path("search_api_game") }}',
                type: 'GET',
                data: { game: query },
                success: function(data) {
                    var resultDiv = $('#result');
                    resultDiv.empty();

                    if (data.length > 0) {
                        data.forEach(function(item) {
                            resultDiv.append('<div class="result-item">' + item.name + '</div>');
                        });
                    } else {
                        resultDiv.append('<div class="result-item">No results found</div>');
                    }
                },
                error: function() {
                    $('#result').html('<div class="result-item">An error occurred</div>');
                }
            });
        } else {
            $('#result').empty();
        }
    });
});