// ==========================
// LOGOUT MODAL
// ==========================

function openLogoutModal() {
    document.getElementById('logoutModal').classList.remove('hidden');
}

function closeLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
}

function confirmLogout() {
    window.location.href = "../auth/logout.php";
}


// ==========================
// PASSWORD SUCCESS MODAL
// ==========================

function closePasswordSuccessModal() {
    document.getElementById('passwordSuccessModal').classList.add('hidden');
}


// ==========================
// PASSWORD ERROR MODAL
// ==========================

function closePasswordErrorModal() {
    document.getElementById('passwordErrorModal').classList.add('hidden');
}

function togglePassword(inputId, icon) {

    const input = document.getElementById(inputId);

    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("bx-show");
        icon.classList.add("bx-hide");

    } else {

        input.type = "password";

        icon.classList.remove("bx-hide");
        icon.classList.add("bx-show");

    }

}