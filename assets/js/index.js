
let deferredPrompt;
let installModal;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
});

document.addEventListener("DOMContentLoaded", () => {
    installModal = document.getElementById("installModal");
    updateInstallButtonVisibility();

    // Detect when display mode changes.
    const standaloneMode = window.matchMedia(
        "(display-mode: standalone)"
    );

    if (standaloneMode.addEventListener) {
        standaloneMode.addEventListener(
            "change",
            updateInstallButtonVisibility
        );
    }
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
    updateInstallButtonVisibility();
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


function updateInstallButtonVisibility() {
    const installButton = document.getElementById("installAppButton");
    const statusBadge = document.getElementById("installStatusBadge");

    const isStandalone =
        window.matchMedia("(display-mode: standalone)").matches ||
        navigator.standalone === true;

    if (installButton) {
        installButton.style.display = isStandalone ? "none" : "";
    }

    if (statusBadge) {
        statusBadge.style.display = isStandalone ? "none" : "";
    }

    if (isStandalone) {
        closeInstallModal();
    }
}
