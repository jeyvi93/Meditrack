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