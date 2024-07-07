// Dark Mode
document.addEventListener("DOMContentLoaded", () => {
  const button = document.querySelector("#toggle");

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
});

const modal = document.querySelector("#modal") ?? null;
const openModal = document.querySelector("#open-button") ?? null;
const closeModal = document.querySelector("#close-button") ?? null;
const body = document.querySelector("body");

if (openModal && closeModal) {
  openModal.addEventListener("click", () => {
    modal.showModal();
    body.classList.add("blur-sm");
  });

  closeModal.addEventListener("click", () => {
    modal.close();
    body.classList.remove("blur-sm");
  });
}
