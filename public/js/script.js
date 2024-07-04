
const modal = document.querySelector('#modal') ?? null;
const openModal = document.querySelector('#open-button') ?? null;
const closeModal = document.querySelector('#close-button') ?? null;
const body = document.querySelector('body');

if(openModal && closeModal) {
    openModal.addEventListener('click', () => {
        modal.showModal();
        body.classList.add('blur-sm')
    })
    
    closeModal.addEventListener('click', () => {
        modal.close();
        body.classList.remove('blur-sm')
    })
}