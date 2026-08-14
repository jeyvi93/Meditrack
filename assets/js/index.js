
// ==========================
// OPEN MODALS
// ==========================

function openLogin() {
    document.getElementById("loginModal").style.display = "block";
}


// ==========================
// CLOSE MODALS
// ==========================

function closeLogin() {
    document.getElementById("loginModal").style.display = "none";
}


// ==========================
// CLOSE WHEN CLICKING OUTSIDE ANY MODAL
// ==========================
window.addEventListener("click", function (event) {

    const modals = document.querySelectorAll(".modal");

    modals.forEach(function (modal) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

});

window.addEventListener("DOMContentLoaded", () => {
    if (window.location.hash === "#login") {
        openLogin();
    }
});

let deferredPrompt;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
});

function installApp() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            deferredPrompt = null;
        });
    } else {
        alert("Install not available on this device/browser.");
    }
}