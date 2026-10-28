document.addEventListener("DOMContentLoaded", function () {

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


});
