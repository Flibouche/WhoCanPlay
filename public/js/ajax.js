// Méthode pour éditer un post
// Je crée une méthode asynchrone pour editer le Post en récupérant l'ID et le formData (un objet qui contient le content)
async function fetchEditPost(id, formData, child) {
  // J'execute la méthode via fetch en asynchrone
  const response = await fetch(`/api/topic/${id}/edit`, {
    method: "POST",
    body: JSON.stringify(formData),
  });

  // J'attends la réponse de la méthode que je viens d'appeler
  const data = await response.json();

  // Si la promesse a bien été validé, je fais un console.log qui me retourne les données du controlleur
  if (data.success) {
    console.log("data: ", data.message);
    child.innerText = formData.content;
  }
}
// J'attends que la page soit complètement chargée
document.addEventListener("DOMContentLoaded", function () {
  // Je récupère tous les posts en les sélectionnant par la class "postToEdit"
  const posts = document.getElementsByClassName("postToEdit");
  const editPosts = document.getElementsByClassName("edit-post");
  const postContents = document.getElementsByClassName("post-content");

  // Je crée une boucle for qui parcours le tableau de Post, et je crée un eventListener pour chaque Post individuel
  for (i = 0; i < posts.length; i++) {
    // Le post individuel récupéré
    const openPost = posts[i];
    const btn = editPosts[i];
    const postContent = postContents[i];
    btn.addEventListener("click", () => {
      postContent.removeAttribute("disabled");
    });
    // A ce post récupéré, je lui ajoute un eventListener, c'est un onSubmit, c'est une action qui se trigger quand je clique sur le bouton "submit" d'un formulaire
    openPost.addEventListener("submit", function (event) {
      // Le preventDefault empêche de recharger la page
      event.preventDefault();
      // Je récupère l'ID de mon Post via le formulaire
      const id = event.target.id;
      // Je récupère le Content de mon Post via le formulaire
      const content = event.target.children[0].value;
      // Je crée un objet qui contient l'ID du Post et le Content du Post
      const data = {
        id,
        content,
      };
      // J'appelle ma méthode asynchrone pour editer le Post en ajax (sans rafraîchissement)
      fetchEditPost(id, data, event.target.children[0]);
      postContent.setAttribute("disabled", true);
    });
  }
});

// Recherche de jeu de l'API
// $(document).ready(function() {
//     $('#search').on('input', function() {
//         var query = $(this).val();

//         if (query.length > 2) {
//             $.ajax({
//                 url: '{{ path("search_api_game") }}',
//                 type: 'GET',
//                 data: { game: query },
//                 success: function(data) {
//                     var resultDiv = $('#result');
//                     resultDiv.empty();

//                     if (data.length > 0) {
//                         data.forEach(function(item) {
//                             resultDiv.append('<div class="result-item">' + item.name + '</div>');
//                         });
//                     } else {
//                         resultDiv.append('<div class="result-item">No results found</div>');
//                     }
//                 },
//                 error: function() {
//                     $('#result').html('<div class="result-item">An error occurred</div>');
//                 }
//             });
//         } else {
//             $('#result').empty();
//         }
//     });
// });
