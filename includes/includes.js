// Run when the page is fully loaded
document.addEventListener("DOMContentLoaded", function () {
    setActiveLink();
});

// ==========================
// ACTIVE SIDEBAR LINK
// ==========================
function setActiveLink() {
    const currentPage = window.location.pathname.split("/").pop();

    document.querySelectorAll(".nav-menu a").forEach(link => {
        const linkPage = link.getAttribute("href").split("/").pop();

        if (linkPage === currentPage) {
            link.classList.add("active");
        }
    });
}

// ==========================
// TOGGLE LOGOUT MENU
// ==========================
function toggleLogoutMenu() {
    const menu = document.getElementById("logoutMenu");

    if (menu.style.display === "block") {
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
}

// ==========================
// CLOSE LOGOUT WHEN CLICKING OUTSIDE
// ==========================
document.addEventListener("click", function (e) {
    const footer = document.querySelector(".sidebar-footer");
    const menu = document.getElementById("logoutMenu");

    if (footer && menu && !footer.contains(e.target)) {
        menu.style.display = "none";
    }
});

// ==========================
// LOGOUT
// ==========================
function openLogoutModal() {

    document
        .getElementById("logoutModal")
        .classList.add("active");

}

function closeLogoutModal() {

    document
        .getElementById("logoutModal")
        .classList.remove("active");

}

function confirmLogout() {

    window.location.href = "../auth/logout.php";

}