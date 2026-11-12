document.addEventListener("DOMContentLoaded", function () {

    const referralForm = document.getElementById("referralForm");

    // REMOVE EMOJIS FROM ALL TEXT FIELDS
    referralForm.addEventListener("input", function (event) {

        const input = event.target;

        if (!input.matches('input[type="text"], input[type="tel"], textarea')) {
            return;
        }

        input.value = input.value.replace(
            /[\p{Extended_Pictographic}\p{Emoji_Presentation}\p{Emoji_Modifier}\p{Regional_Indicator}\uFE0F\u200D\u20E3]/gu,
            ""
        );

    });

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

    // PATIENT ID LOOKUP
    const patientIdInput = document.getElementById("patientIdNumber");
    const retrievePatientBtn = document.getElementById("retrievePatientBtn");
    const patientLookupMessage = document.getElementById("patientLookupMessage");

    if (patientIdInput && retrievePatientBtn) {

        retrievePatientBtn.addEventListener("click", function () {

            const idNumber = patientIdInput.value.trim();

            if (!idNumber) {
                patientLookupMessage.textContent = "Please enter a Student / Staff ID.";
                patientLookupMessage.style.color = "red";
                return;
            }

            retrievePatientBtn.disabled = true;
            retrievePatientBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Retrieving...';

            patientLookupMessage.textContent = "";

            fetch("../actions/get_referral_patient.php?id_number=" + encodeURIComponent(idNumber))
                .then(response => response.json())
                .then(data => {

                    if (!data.success) {
                        patientLookupMessage.textContent = data.message;
                        patientLookupMessage.style.color = "red";
                        return;
                    }

                    const patient = data.patient;

                    document.getElementById("lastName").value = patient.last_name || "";
                    document.getElementById("firstName").value = patient.first_name || "";
                    document.getElementById("middleName").value = patient.middle_name || "";
                    document.getElementById("dobInput").value = patient.date_of_birth || "";
                    document.getElementById("ageDisplay").value = patient.age ?? "";
                    document.getElementById("sex").value = patient.sex || "";
                    document.getElementById("contact").value = patient.contact_number || "";
                    document.getElementById("address").value = patient.address || "";

                    // PHILHEALTH INFORMATION
                    const philhealthNumber = patient.philhealth_number || "";

                    if (philhealthNumber.trim() !== "") {
                        phYes.checked = true;
                        document.getElementById("philhealthInput").value = philhealthNumber;
                    } else {
                        phNo.checked = true;
                        document.getElementById("philhealthInput").value = "";
                    }

                    togglePhilHealth();

                    patientLookupMessage.textContent = "Patient information retrieved successfully.";
                    patientLookupMessage.style.color = "green";

                })
                .catch(error => {
                    console.error("Patient lookup error:", error);
                    patientLookupMessage.textContent = "Unable to retrieve patient information. Please try again.";
                    patientLookupMessage.style.color = "red";
                })
                .finally(() => {
                    retrievePatientBtn.disabled = false;
                    retrievePatientBtn.innerHTML = '<i class="fas fa-search"></i> Retrieve';
                });
        });
    }

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
            let value = this.value.replace(/[^\p{L}\p{M}\s.'-]/gu, "");

            this.value = value.toLowerCase().replace(
                /(^|[\s'-])(\p{L})/gu,
                (match, separator, letter) => separator + letter.toUpperCase()
            );
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