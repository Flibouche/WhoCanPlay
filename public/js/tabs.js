// J'attends que le DOM soit chargé
document.addEventListener('DOMContentLoaded', () => {
    // Je sélectionne tous les éléments avec l'attribut 'data-tab-target'
    const tabs = document.querySelectorAll('[data-tab-target]');
    // Je définis les classes CSS pour l'état actif d'un onglet
    const activeClasses = ['border-b-2', 'text-indigo-800', 'border-indigo-800', 'dark:text-indigo-200', 'dark:border-indigo-200'];
    // Je définis les classes CSS pour l'état inactif d'un onglet
    const inactiveClasses = ['text-black', 'dark:text-white'];

    // J'active le premier onglet par défaut
    tabs[0].classList.remove(...inactiveClasses); // Je retire les classes inactives
    tabs[0].classList.add(...activeClasses); // J'ajoute les classes actives
    document.querySelector('#tab1').classList.remove('hidden'); // J'affiche le contenu du premier onglet

    // J'ajoute un écouteur d'événements à chaque onglet
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Je trouve le contenu associé à l'onglet cliqué
            const targetContent = document.querySelector(tab.dataset.tabTarget);
            // Je cache tous les contenus d'onglets
            document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
            // J'affiche le contenu de l'onglet cliqué
            targetContent.classList.remove('hidden');
            // Je réinitialise tous les onglets à l'état inactif
            tabs.forEach(t => {
                t.classList.remove(...activeClasses);
                t.classList.add(...inactiveClasses);
            });
            // J'active l'onglet cliqué
            tab.classList.remove(...inactiveClasses);
            tab.classList.add(...activeClasses);
        });
    });
});