        // Toggle Student/Staff Fields
        function togglePatientFields() {
            const type = document.getElementById('patientType').value;
            const studentFields = document.getElementById('studentFields');
            const staffFields = document.getElementById('staffFields');

            if (type === 'student') {
                studentFields.style.display = 'grid';
                staffFields.style.display = 'none';
            } else {
                studentFields.style.display = 'none';
                staffFields.style.display = 'block';
            }
        }

// Add Dynamic Medicine Rows
function addMedicineRow() {

    const container = document.getElementById('medicineRows');
    const firstSelect = document.querySelector('.med-select');

    const newRow = document.createElement('div');
    newRow.className = 'medicine-row';

    newRow.innerHTML = `
        <div class="input-group">
            <label>
                Medicine Name
            </label>

            <select
                class="med-select"
                name="medicine_names[]"
                required
            >
                ${firstSelect.innerHTML}
            </select>
        </div>

        <div class="input-group">
            <label>
                Batch Number
            </label>

            <select
                class="batch-select"
                name="medicines[]"
                required
                disabled
            >
                <option value="">
                    Select a medicine first
                </option>
            </select>
        </div>

        <div class="input-group qty-input">
            <label>
                Quantity
            </label>

            <input
                type="number"
                name="medicine_quantities[]"
                min="1"
                value="1"
                required
            >
        </div>

        <div class="row-action">
            <button
                type="button"
                class="btn-remove"
                onclick="this.parentElement.parentElement.remove()"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    container.appendChild(newRow);
}

document.getElementById('medicineRows').addEventListener('change', function (event) {

    if (!event.target.classList.contains('med-select')) {
        return;
    }

    const row = event.target.closest('.medicine-row');
    const batchSelect = row.querySelector('.batch-select');
    const selectedMedicine = event.target.value;

    // Reset the batch dropdown
    batchSelect.innerHTML = '';

    if (!selectedMedicine) {
        batchSelect.disabled = true;
        batchSelect.add(new Option('Select a medicine first', ''));
        return;
    }

    // Get batches supplied by consultation.php
    const batches = (window.availableMedicineBatches || [])
        .filter(batch => batch.medicine_name === selectedMedicine)
        .sort((a, b) => {
            return a.expiry_date.localeCompare(b.expiry_date);
        });

    if (batches.length === 0) {
        batchSelect.disabled = true;
        batchSelect.add(new Option('No available batches', ''));
        return;
    }

    batchSelect.add(new Option('Select a batch', ''));

    batches.forEach(batch => {
        const batchNumber = batch.batch_number || 'No batch number';

        const optionText =
            `${batchNumber} — Expires: ${batch.expiry_date} — Stock: ${batch.quantity}`;

        batchSelect.add(new Option(optionText, batch.id));
    });

    batchSelect.disabled = false;
});


// ==========================
// SAVE CONSULTATION
// ==========================

const consultationForm = document.getElementById("consultationForm");

consultationForm.addEventListener("submit", function (e) {

    e.preventDefault();

    const formData = new FormData(consultationForm);

    fetch("../actions/save_consultation.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            // Show success modal
            document.getElementById("successModal").style.display = "flex";

        } else {

            alert(data.message || "Failed to save consultation.");

        }

    })
    .catch(error => {

        console.error(error);

        alert("Something went wrong while saving the consultation.");

    });

});


// ==========================
// SUCCESS MODAL
// ==========================

function closeSuccessModal() {

    document.getElementById("successModal").style.display = "none";

}


function viewConsultationLogs() {

    window.location.href = "view_consultation.php";

}

// TOGGLE OTHER COMPLAINT FIELD
function toggleOtherComplaint() {

    const complaint = document.getElementById("complaint");
    const otherGroup = document.getElementById("otherComplaintGroup");

    if (complaint.value === "Other") {
        otherGroup.style.display = "block";
    } else {
        otherGroup.style.display = "none";
    }
}

// AUTO-SET DATE AND TIME
document.addEventListener("DOMContentLoaded", function () {

    const dateInput = document.getElementById("consultationDate");
    const timeInput = document.getElementById("consultationTime");

    const now = new Date();

    // Current date
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");

    // Current time
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");

    if (dateInput) {
        dateInput.value = `${year}-${month}-${day}`;
    }

    if (timeInput) {
        timeInput.value = `${hours}:${minutes}`;
    }

});