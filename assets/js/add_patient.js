
document.addEventListener("DOMContentLoaded", function () {

    const dobInput = document.getElementById("dobInput");
    const ageDisplay = document.getElementById("ageDisplay");

    if (dobInput && ageDisplay) {

        dobInput.addEventListener("change", function () {

            const birthDate = new Date(this.value);
            const today = new Date();

            if (isNaN(birthDate)) {
                ageDisplay.value = "";
                return;
            }

            let age = today.getFullYear() - birthDate.getFullYear();

            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            ageDisplay.value = age;

        });

    }
    // REAL-TIME FORM VALIDATION
    const studentForm = document.getElementById("studentForm");

    if (studentForm) {

        const inputs = studentForm.querySelectorAll(
            "input:not([readonly]), select, textarea"
        );

        function validateField(input) {

            const group = input.closest(".input-group");
            if (!group) return true;

            let error = "";

            if (!input.checkValidity()) {
                if (input.validity.valueMissing) {
                    error = "This field is required.";
                } else {
                    error = input.title || "Please enter a valid value.";
                }
            }

            let errorElement = group.querySelector(".field-error");

            if (!errorElement) {
                errorElement = document.createElement("span");
                errorElement.className = "field-error";
                input.insertAdjacentElement("afterend", errorElement);
            }

            errorElement.textContent = error;
            errorElement.hidden = !error;

            input.classList.toggle("invalid", error !== "");

            return error === "";
        }

        inputs.forEach(input => {

            input.addEventListener("input", function () {
                validateField(input);
            });

            input.addEventListener("change", function () {
                validateField(input);
            });

            input.addEventListener("blur", function () {
                validateField(input);
            });

        });

        studentForm.addEventListener("submit", function (event) {

            let formIsValid = true;
            let firstInvalidInput = null;

            inputs.forEach(input => {

                if (!validateField(input)) {
                    formIsValid = false;

                    if (!firstInvalidInput) {
                        firstInvalidInput = input;
                    }
                }

            });

            if (!formIsValid) {
                event.preventDefault();
                firstInvalidInput.focus();
            }

        });

    }
});