const addMedicineForm = document.getElementById("addMedicineForm");

// FILTER INVALID CHARACTERS AND EMOJIS
const textFields = [
    document.getElementById("medName"),
    document.getElementById("batchNo"),
    document.getElementById("supplier")
];

const allowedTextPattern = /[^\p{L}\p{M}\p{N}\s.,()\/#&+'%\-]/gu;

textFields.forEach(input => {
    if (!input) return;

    input.addEventListener("input", function () {
        this.value = this.value.replace(allowedTextPattern, "");
    });
});

// REAL-TIME VALIDATION
if (addMedicineForm) {

    const inputs = addMedicineForm.querySelectorAll("input");

    function validateField(input) {

        const group = input.closest(".input-group");
        let error = "";

        if (!input.checkValidity()) {

            if (input.validity.valueMissing) {
                error = "This field is required.";
            } else if (input.validity.rangeUnderflow) {
                error = "Value must be at least 1.";
            } else {
                error = "Please enter a valid value.";
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
        input.addEventListener("input", () => validateField(input));
        input.addEventListener("change", () => validateField(input));
        input.addEventListener("blur", () => validateField(input));
    });
}

addMedicineForm.addEventListener("submit", function (e) {

    e.preventDefault();

    let formIsValid = true;
    let firstInvalidInput = null;

    addMedicineForm.querySelectorAll("input").forEach(input => {

        if (!validateField(input)) {
            formIsValid = false;

            if (!firstInvalidInput) {
                firstInvalidInput = input;
            }
        }

    });

    if (!formIsValid) {
        firstInvalidInput.focus();
        return;
    }

    const formData = new FormData();

    formData.append(
        "medicine_name",
        document.getElementById("medName").value.trim()
    );

    formData.append(
        "batch_number",
        document.getElementById("batchNo").value.trim()
    );

    formData.append(
        "supplier",
        document.getElementById("supplier").value.trim()
    );

    formData.append(
        "quantity",
        document.getElementById("quantity").value
    );

    formData.append(
        "low_stock_threshold",
        document.getElementById("lowStockThreshold").value
    );

    formData.append(
        "date_acquired",
        document.getElementById("dateAcquired").value
    );

    formData.append(
        "expiry_date",
        document.getElementById("expiryDate").value
    );


    fetch("../actions/add_medicine.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

    if (data.success) {

        sessionStorage.setItem(
            "medicineSuccessMessage",
            data.message
        );

        window.location.href = "medicine.php";

    } else {

        alert(
            data.message ||
            "Unable to add medicine."
        );

    }

})
    .catch(error => {

        console.error(error);

        alert(
            "Something went wrong while adding the medicine."
        );

    });

});
