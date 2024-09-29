document.addEventListener("DOMContentLoaded", () => {
    // Modal
    const deleteAccountModal = document.querySelector("#deleteAccountModal") ?? null;

    // Boutons d'ouverture et de fermeture des modals
    const openModal = document.querySelector("#open-button") ?? null;
    const closeModalButtons = document.querySelectorAll(".close-button") ?? null;

    // Body
    const body = document.querySelector("body") ?? null;

    if (openModal && deleteAccountModal) {
        openModal.addEventListener("click", () => {
            deleteAccountModal.showModal();
            body.classList.add("blur-sm");
        });

        closeModalButtons.forEach(button => {
            button.addEventListener("click", () => {
                if (deleteAccountModal) {
                    deleteAccountModal.close();
                    body.classList.remove("blur-sm");
                }
            });
        });
    }
});