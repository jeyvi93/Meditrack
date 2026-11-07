document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("personnelForm");

    if (!form) return;

    const fields = {
        staff_id_number: document.getElementById("staff_id_number"),
        role: document.getElementById("role"),
        last_name: document.getElementById("last_name"),
        first_name: document.getElementById("first_name"),
        middle_name: document.getElementById("middle_name"),
        email: document.getElementById("email"),
        password: document.getElementById("password"),
        confirm_password: document.getElementById("confirm_password")
    };

    const emojiPattern = /\p{Extended_Pictographic}/u;

    function showError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}_error`);
        const field = fields[fieldName];

        if (errorElement) {
            errorElement.textContent = message;
        }

        if (field) {
            field.classList.toggle("input-invalid", message !== "");
            field.setAttribute("aria-invalid", message ? "true" : "false");
        }
    }

    function clearError(fieldName) {
        showError(fieldName, "");
    }

    function validateField(fieldName) {
        const field = fields[fieldName];

        if (!field) return true;

        const value = field.value.trim();

        if (fieldName !== "middle_name" && !value) {
            showError(fieldName, "This field is required.");
            return false;
        }

        if (emojiPattern.test(value)) {
            showError(fieldName, "Emojis are not allowed.");
            return false;
        }

        if (fieldName === "email" && value && !field.validity.valid) {
            showError(fieldName, "Please enter a valid email address.");
            return false;
        }

        if (fieldName === "password" && value.length < 8) {
            showError(fieldName, "Password must be at least 8 characters.");
            return false;
        }

        if (
            fieldName === "confirm_password" &&
            value !== fields.password.value
        ) {
            showError(fieldName, "Passwords do not match.");
            return false;
        }

        clearError(fieldName);
        return true;
    }

    Object.entries(fields).forEach(([fieldName, field]) => {
        if (!field) return;

        field.addEventListener("input", () => {
            validateField(fieldName);

            if (fieldName === "password" && fields.confirm_password.value) {
                validateField("confirm_password");
            }
        });

        field.addEventListener("blur", () => {
            validateField(fieldName);
        });
    });

    form.addEventListener("submit", (event) => {
        let isValid = true;

        Object.keys(fields).forEach((fieldName) => {
            if (!validateField(fieldName)) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault();
        }
    });
});