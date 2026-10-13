
importScripts(
    "https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js"
);
importScripts(
    "https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js"
);

const firebaseConfig = {
    apiKey: "AIzaSyBqnixZLFsqfsL1-yDY872caOfRdgdDpeU",
    authDomain: "meditrack-clinic-2f6a0.firebaseapp.com",
    projectId: "meditrack-clinic-2f6a0",
    storageBucket: "meditrack-clinic-2f6a0.firebasestorage.app",
    messagingSenderId: "1031623849289",
    appId: "1:1031623849289:web:85359d01c7db2f3f1b1698"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    // Let Firebase/browser handle notification payloads automatically.
    // Display a custom notification only for data-only messages.
    if (payload.notification) {
        return;
    }

    const title = payload.data?.title || "MediTrack Notification";
    const options = {
        body: payload.data?.body || "You have a new clinic update.",
        icon: "/assets/images/icon-192.png",
        data: payload.data || {}
    };

    return self.registration.showNotification(title, options);
});

self.addEventListener("install", (event) => {
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    event.waitUntil((async () => {
        const scopeUrl = new URL(self.registration.scope);

        const requestedUrl = new URL(
            event.notification.data?.url ||
                "patient/view_appointments.php",
            scopeUrl
        );

        const targetUrl =
            requestedUrl.origin === scopeUrl.origin &&
            requestedUrl.pathname.startsWith(scopeUrl.pathname)
                ? requestedUrl.href
                : new URL(
                    "patient/view_appointments.php",
                    scopeUrl
                ).href;

        try {
            const windowClients = await self.clients.matchAll({
                type: "window",
                includeUncontrolled: true
            });

            for (const client of windowClients) {
                if (client.url.startsWith(scopeUrl.href)) {
                    try {
                        if (client.url !== targetUrl) {
                            await client.navigate(targetUrl);
                        }

                        return await client.focus();
                    } catch (error) {
                        console.warn(
                            "Could not reuse existing tab:",
                            error
                        );
                    }
                }
            }
        } catch (error) {
            console.warn("Could not find existing tabs:", error);
        }

        return self.clients.openWindow(targetUrl);
    })());
});