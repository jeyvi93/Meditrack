document.addEventListener("DOMContentLoaded", function () {

    const referralForm = document.getElementById("referralForm");

    // ======================
    // REAL-TIME FORM VALIDATION
    // ======================

    function validateField(input) {
        const group = input.closest(".input-group");

        if (!group) return true;

        // Skip hidden fields
        if (input.disabled || input.offsetParent === null) {
            input.classList.remove("invalid");

            const oldError = group.querySelector(".field-error");
            if (oldError) oldError.remove();

            return true;
        }

        let error = "";

        if (!input.checkValidity()) {
            if (input.validity.valueMissing) {
                error = "This field is required.";
            } else if (input.validity.patternMismatch) {
                error = input.title || "Please enter a valid value.";
            } else if (input.validity.rangeUnderflow) {
                error = `Value must be at least ${input.min}.`;
            } else if (input.validity.typeMismatch) {
                error = "Please enter a valid value.";
            } else {
                error = "Please check this field.";
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

    // Validate while typing, changing, or leaving a field
    referralForm.addEventListener("input", function (e) {
        if (e.target.matches("input, select, textarea")) {
            validateField(e.target);
        }
    });

    referralForm.addEventListener("change", function (e) {
        if (e.target.matches("input, select, textarea")) {
            validateField(e.target);
        }
    });

    referralForm.addEventListener("focusout", function (e) {
        if (e.target.matches("input, select, textarea")) {
            validateField(e.target);
        }
    });

    // Prevent submission when any visible field is invalid
    referralForm.addEventListener("submit", function (e) {
        let formIsValid = true;
        let firstInvalidInput = null;

        referralForm.querySelectorAll("input, select, textarea").forEach(input => {
            if (!validateField(input)) {
                formIsValid = false;

                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
            }
        });

        if (!formIsValid) {
            e.preventDefault();
            firstInvalidInput.focus();
        }
    });

    // ======================
    // AGE CALCULATION
    // ======================
    document.getElementById("dobInput").addEventListener("change", function () {
        let birth = new Date(this.value);
        let today = new Date();

        let age = today.getFullYear() - birth.getFullYear();
        let m = today.getMonth() - birth.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }

        document.getElementById("ageDisplay").value = age;
    });


    // ======================
    // PHILHEALTH TOGGLE (FIXED)
    // ======================
    const phYes = document.querySelector('input[name="philhealth"][value="yes"]');
    const phNo = document.querySelector('input[name="philhealth"][value="no"]');
    const philhealthBox = document.getElementById("philhealthSpec");

    function togglePhilHealth() {
        if (!philhealthBox) return;

        if (phYes && phYes.checked) {
            philhealthBox.style.display = "block";
        } else {
            philhealthBox.style.display = "none";
        }
    }

    if (phYes && phNo) {
        phYes.addEventListener("change", togglePhilHealth);
        phNo.addEventListener("change", togglePhilHealth);
    }

    togglePhilHealth();

    // ======================
    // NAME INPUT VALIDATION
    // ======================
    
    const nameFields = [
        document.getElementById("DoctorName"),
        document.getElementById("lastName"),
        document.getElementById("firstName"),
        document.getElementById("middleName"),
        document.getElementById("parent_name"),
        document.getElementById("spouse_name")
    ];

    nameFields.forEach(function (field) {
        if (!field) return;

        field.addEventListener("input", function () {
            this.value = this.value.replace(/[^A-Za-zÀ-ÿ .'-]/g, "");
        });
    });

    // ======================
    // CONTACT NUMBER VALIDATION
    // ======================
    const contactField = document.getElementById("contact");

    if (contactField) {
        contactField.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 11);
        });
    }

    // ======================
    // RESET FORM AFTER SUBMISSION
    // ======================

    

    if (referralForm) {
        referralForm.addEventListener("submit", function () {

            // Allow the browser to submit the original form first
            setTimeout(function () {
                referralForm.reset();

                // Restore the correct PhilHealth field visibility
                togglePhilHealth();

                // Clear the calculated age
                document.getElementById("ageDisplay").value = "";

            }, 0);

        });
    }


});