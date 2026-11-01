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

// ==========================
// PASSWORD PROCESSING MODAL
// ==========================

document.addEventListener("DOMContentLoaded", function () {

    const passwordForm = document.querySelector(".password-form");

    if (!passwordForm) return;

    passwordForm.addEventListener("submit", function () {

        const processingModal = document.getElementById("passwordProcessingModal");
        const saveButton = passwordForm.querySelector(".btn-save-profile");

        if (!processingModal) return;

        processingModal.classList.remove("hidden");

        if (saveButton) {
            saveButton.disabled = true;
            saveButton.textContent = "Updating Password...";
        }

    });

});