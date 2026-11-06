
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

        // REMOVE EMOJIS FROM EMAIL FIELD
        const emailInput = editForm.querySelector('input[name="email"]');

        if (emailInput) {
            emailInput.addEventListener("input", function () {
                this.value = this.value.replace(
                    /[\p{Extended_Pictographic}\p{Emoji_Presentation}\p{Emoji_Modifier}\p{Regional_Indicator}\uFE0F\u200D\u20E3]/gu,
                    ""
                );
            });
        }

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