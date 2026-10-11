
let deferredPrompt;
let installModal;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
});

document.addEventListener("DOMContentLoaded", () => {
    installModal = document.getElementById("installModal");
});

function openInstallModal() {
    installModal = document.getElementById("installModal");

    if (!installModal) {
        console.error("Install modal not found in index.php.");
        return;
    }

    const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

    installModal.style.display = "flex";

    document.getElementById("androidInstructions").style.display =
        isIOS ? "none" : "block";

    document.getElementById("iosInstructions").style.display =
        isIOS ? "block" : "none";
}

function closeInstallModal() {
    if (installModal) {
        installModal.style.display = "none";
    }
}

async function installApp() {
    const message = document.getElementById("androidInstallMessage");

    if (!deferredPrompt) {
        message.textContent =
            "Installation is not currently available. Try Chrome's menu and look for Install app or Add to Home screen.";
        return;
    }

    const promptEvent = deferredPrompt;
    deferredPrompt = null;

    await promptEvent.prompt();

    const choiceResult = await promptEvent.userChoice;

    if (choiceResult.outcome === "accepted") {
        closeInstallModal();
    } else {
        message.textContent =
            "Installation was cancelled. You can try again later.";
    }
}

window.addEventListener("appinstalled", () => {
    deferredPrompt = null;
    closeInstallModal();
});

window.addEventListener("click", (event) => {
    if (event.target === installModal) {
        closeInstallModal();
    }
});


if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("./service-worker.js")
            .then(() => {
                console.log("MediTrack service worker registered.");
            })
            .catch((error) => {
                console.error(
                    "Service worker registration failed:",
                    error
                );
            });
    });
}
