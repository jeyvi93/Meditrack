const params = new URLSearchParams(window.location.search);

const mode = params.get("mode");
const medicine = params.get("medicine");

const pageTitle = document.getElementById("pageTitle");
const pageDesc = document.getElementById("pageDesc");
const medicineInput = document.getElementById("medicine_name");

if (mode === "restock") {

    pageTitle.textContent = "Restock Medicine";
    pageDesc.textContent = "Add a new batch for existing medicine.";

    if (medicineInput) {
        medicineInput.value = medicine;
        medicineInput.readOnly = true;
    }
}