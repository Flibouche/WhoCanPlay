// Menu Burger
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const closeMenu = document.getElementById('closeMenu');
    const mobileMenuContent = document.getElementById('mobileMenuContent');

    menuToggle.addEventListener('click', function() {
        mobileMenuContent.classList.remove('translate-x-full');
    });

    closeMenu.addEventListener('click', function() {
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