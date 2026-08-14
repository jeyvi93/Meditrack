
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

});