// Preview images before uploading
document.addEventListener("DOMContentLoaded", () => {
    document.querySelector('#feature_images').addEventListener('change', checkFiles);

    function checkFiles() {
        let previewContainer = document.querySelector('.preview');
        previewContainer.innerText = '';
        const types = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        let files = this.files;

        Array.from(files).forEach(file => {
            if (types.includes(file.type)) {
                let reader = new FileReader();
                reader.onloadend = function () {
                    let img = document.createElement('img');
                    img.src = reader.result;
                    previewContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });

        if (files.length > 0) {
            previewContainer.style.display = 'block';
        } else {
            previewContainer.style.display = 'none';
        }
    }
});