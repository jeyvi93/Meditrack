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

    // Get the medicine options from the first dropdown
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
                name="medicines[]"
            >
                ${firstSelect.innerHTML}
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