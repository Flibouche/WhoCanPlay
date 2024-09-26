document.addEventListener("DOMContentLoaded", () => {
    let time = 5;
    const timerElement = document.getElementById('timer');
    const redirectData = document.getElementById('redirectData');
    const redirectUrl = redirectData.dataset.redirectUrl;

    if (!timerElement) {
        console.error("Element with ID 'timer' not found.");
        return;
    }

    if (!redirectUrl) {
        console.error("Redirect URL not found in 'redirectData' element.");
        return;
    }

    function countdown() {
        if (time >= 0) {
            timerElement.innerText = time;
            time--;
        } else {
            clearInterval(interval);
        }
    }

    countdown();

    const interval = setInterval(countdown, 1000);

    setTimeout(function () {
        window.location.href = redirectUrl;
    }, 5000);
});