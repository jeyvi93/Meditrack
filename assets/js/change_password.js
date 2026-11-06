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

    // ==========================
    // REAL-TIME PASSWORD VALIDATION
    // ==========================

    const passwordInputs = {
        current: document.getElementById("current_password"),
        new: document.getElementById("new_password"),
        confirm: document.getElementById("confirm_password")
    };

    function validatePasswordField(input) {
        const box = input.closest(".input-box");
        const group = input.closest(".info-group");

        if (!box || !group) return true;

        let error = "";

        if (input.value.trim() === "") {
            error = "This field is required.";
        } else if (input.id === "new_password" && input.value.length < 8) {
            error = "Password must be at least 8 characters long.";
        } else if (
            input.id === "confirm_password" &&
            input.value !== passwordInputs.new.value
        ) {
            error = "Passwords do not match.";
        }

        let errorElement = group.querySelector(".password-field-error");

        if (!errorElement) {
            errorElement = document.createElement("span");
            errorElement.className = "password-field-error";
            box.insertAdjacentElement("afterend", errorElement);
        }

        errorElement.textContent = error;
        errorElement.hidden = !error;

        box.classList.toggle("invalid", error !== "");

        return error === "";
    }

    Object.values(passwordInputs).forEach(input => {
        input.addEventListener("input", function () {
            validatePasswordField(input);

            // Recheck confirmation when the new password changes
            if (input.id === "new_password" && passwordInputs.confirm.value !== "") {
                validatePasswordField(passwordInputs.confirm);
            }
        });

        input.addEventListener("blur", function () {
            validatePasswordField(input);
        });
    });

    if (!passwordForm) return;

    passwordForm.addEventListener("submit", function (e) {

        let formIsValid = true;
        let firstInvalidInput = null;

        Object.values(passwordInputs).forEach(input => {

            if (!validatePasswordField(input)) {
                formIsValid = false;

                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
            }

        });

        if (!formIsValid) {
            e.preventDefault();
            firstInvalidInput.focus();
            return;
        }

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