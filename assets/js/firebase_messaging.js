
/*
 * MediTrack - Firebase Cloud Messaging
 * Handles Firebase setup and notification permission.
 */

const firebaseConfig = {
    apiKey: "AIzaSyBqnixZLFsqfsL1-yDY872caOfRdgdDpeU",
    authDomain: "meditrack-clinic-2f6a0.firebaseapp.com",
    projectId: "meditrack-clinic-2f6a0",
    storageBucket: "meditrack-clinic-2f6a0.firebasestorage.app",
    messagingSenderId: "1031623849289",
    appId: "1:1031623849289:web:85359d01c7db2f3f1b1698",
    measurementId: "G-TB7PFY9PZ9"
};

if (typeof firebase !== "undefined") {
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
} else {
    console.error("Firebase SDK has not been loaded.");
}


async function enablePushNotifications() {
    const status = document.getElementById("notificationStatus");
    const button = document.getElementById("enableNotificationsButton");

    try {
        if (
            typeof firebase === "undefined" ||
            !(await firebase.messaging.isSupported())
        ) {
            throw new Error(
                "Push notifications are not supported in this browser."
            );
        }

        if (!("Notification" in window)) {
            throw new Error("Browser notifications are unavailable.");
        }

        if (!window.isSecureContext) {
            throw new Error(
                "Notifications require HTTPS or localhost."
            );
        }

        if (status) {
            status.textContent = "";
            status.classList.add("hidden");
        }

        if (button) {
            button.disabled = true;
        }

        // Register the root service worker.
        const registration = await navigator.serviceWorker.register(
            "../service-worker.js"
        );

        await navigator.serviceWorker.ready;

        // Ask permission only after the user clicks the button.
        const permission = await Notification.requestPermission();

        if (permission !== "granted") {
            throw new Error(
                "Notification permission was not granted. Check your browser settings."
            );
        }

        const messaging = firebase.messaging();

        // Keep your actual VAPID public key here.
        const token = await messaging.getToken({
            vapidKey: "BKh4i8xIX-PP2Bq6YeV_9gy3bA018BvkEUxVhFI275udo082LU34iaykZ-3spSHTuMCDk0qAFKvMcbQB9waXO6Q",
            serviceWorkerRegistration: registration
        });

        if (!token) {
            throw new Error("Firebase did not return a device token.");
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        if (!csrfToken) {
            throw new Error("Security token is missing. Refresh the page.");
        }

        const response = await fetch("../actions/save_fcm_token.php", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken
            },
            body: JSON.stringify({
                token,
                base_url: registration.scope
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || "Unable to save your notification settings."
            );
        }

        if (status) {
            status.textContent = "";
            status.classList.add("hidden");
        }

        if (button) {
            button.textContent = "Notifications Enabled";
        }

        console.log("FCM token saved successfully.");

        return token;

    } catch (error) {
        console.error("Notification setup failed:", error);

        if (status) {
            status.textContent = "";
            status.classList.add("hidden");
        }

        return null;

    } finally {
        if (button) {
            button.disabled = false;
        }
    }
}


document.addEventListener("DOMContentLoaded", async () => {
    // Listen for Firebase messages while the page is open.
    try {
        if (
            typeof firebase !== "undefined" &&
            await firebase.messaging.isSupported()
        ) {
            const messaging = firebase.messaging();

            messaging.onMessage(async (payload) => {
                console.log("Foreground FCM message received:", payload);

                const data = payload.data || {};
                const title =
                    data.title ||
                    payload.notification?.title ||
                    "MediTrack Notification";

                const body =
                    data.body ||
                    payload.notification?.body ||
                    "You have a new clinic update.";

                try {
                    const registration =
                        await navigator.serviceWorker.ready;

                    await registration.showNotification(title, {
                        body,
                        data: {
                            url: data.url ||
                                new URL(
                                    "patient/view_appointments.php",
                                    registration.scope
                                ).href
                        }
                    });
                } catch (error) {
                    console.error(
                        "Unable to display FCM notification:",
                        error
                    );
                }
            });
        }
    } catch (error) {
        console.error(
            "Unable to initialize foreground messaging:",
            error
        );
    }

    // Keep the existing Enable Notifications button.
    const button = document.getElementById(
        "enableNotificationsButton"
    );

    if (button) {
        button.addEventListener("click", enablePushNotifications);
    }

    
    // Notification permission modal
    const notificationModal = document.getElementById(
        "notificationPermissionModal"
    );

    const modalEnableButton = document.getElementById(
        "enableNotificationModalButton"
    );

    const maybeLaterButton = document.getElementById(
        "maybeLaterNotificationsButton"
    );

    const notificationPromptKey =
        "meditrackNotificationPromptDismissed";

    function closeNotificationModal() {
        if (notificationModal) {
            notificationModal.classList.add("hidden");
        }
    }
    
    // Show the modal only when permission has not been decided.
    // Remember when the user dismisses or acts on the prompt.
    if (
        notificationModal &&
        "Notification" in window &&
        Notification.permission === "default" &&
        localStorage.getItem(notificationPromptKey) !== "yes"
    ) {
        notificationModal.classList.remove("hidden");
    }

    if (maybeLaterButton) {
        maybeLaterButton.addEventListener("click", () => {
            localStorage.setItem(notificationPromptKey, "yes");
            closeNotificationModal();
        });
    }

    if (modalEnableButton) {
        modalEnableButton.addEventListener("click", async () => {
            localStorage.setItem(notificationPromptKey, "yes");
            closeNotificationModal();

            modalEnableButton.disabled = true;

            try {
                await enablePushNotifications();
            } finally {
                modalEnableButton.disabled = false;
            }
        });
    }

});
