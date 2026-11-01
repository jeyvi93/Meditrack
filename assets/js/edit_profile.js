
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

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }

    function closeErrorModal() {
        document.getElementById('errorModal').classList.add('hidden');
    }

    // ==========================
    // PROFILE UPDATE PROCESSING
    // ==========================

    document.addEventListener("DOMContentLoaded", function () {
        const editForm = document.querySelector(".edit-form");

        if (!editForm) return;

        editForm.addEventListener("submit", function () {
            const processingModal = document.getElementById("processingModal");
            const saveButton = editForm.querySelector(".btn-save-profile");

            if (!processingModal) return;

            processingModal.classList.remove("hidden");

            if (saveButton) {
                saveButton.disabled = true;
                saveButton.textContent = "Saving Changes...";
            }
        });
    });