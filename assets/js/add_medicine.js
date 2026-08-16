const addMedicineForm = document.getElementById("addMedicineForm");

addMedicineForm.addEventListener("submit", function (e) {

    e.preventDefault();

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

        document.getElementById(
            "addMedicineSuccessModal"
        ).style.display = "flex";

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

function closeAddMedicineSuccessModal() {

    document.getElementById(
        "addMedicineSuccessModal"
    ).style.display = "none";

    window.location.href = "medicine.php";
}