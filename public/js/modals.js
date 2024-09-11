// Modals utilisés dans le fichier showFeature.html.twig
document.addEventListener("DOMContentLoaded", () => {
    const validateModal = document.querySelector("#validateModal") ?? null;
    const denyModal = document.querySelector("#denyModal") ?? null;
    const openModal1 = document.querySelector("#open-button-1") ?? null;
    const openModal2 = document.querySelector("#open-button-2") ?? null;
    const closeModalButtons = document.querySelectorAll(".close-button") ?? null;
    const body = document.querySelector("body") ?? null;

    if (openModal1 && validateModal) {
        openModal1.addEventListener("click", () => {
            validateModal.showModal();
            body.classList.add("blur-sm");
        });

        // Ajoute un écouteur d'événement à chaque bouton de fermeture
        closeModalButtons.forEach(button => {
            button.addEventListener("click", () => {
                if (validateModal) {
                    validateModal.close();
                    body.classList.remove("blur-sm");
                }
            });
        });
    }

    if (openModal2 && denyModal) {
        openModal2.addEventListener("click", () => {
            denyModal.showModal();
            body.classList.add("blur-sm");
        });

        closeModalButtons.forEach(button => {
            button.addEventListener("click", () => {
                if (denyModal) {
                    denyModal.close();
                    body.classList.remove("blur-sm");
                }
            });
        });
    }
});