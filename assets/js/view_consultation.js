const searchInput = document.getElementById("searchInput");
const patientFilter = document.getElementById("patientFilter");
const tableRows = document.querySelectorAll(".logs-table tbody tr");

// Run filter whenever user types or changes dropdown
searchInput.addEventListener("input", filterTable);
patientFilter.addEventListener("change", filterTable);

function filterTable() {

    const searchValue = searchInput.value.toLowerCase().trim();
    const filterValue = patientFilter.value.toLowerCase();

    tableRows.forEach(row => {

        const name = row.children[1].innerText.toLowerCase();
        const type = row.children[2].innerText.toLowerCase();
        const department = row.children[3].innerText.toLowerCase();
        const details = row.children[4].innerText.toLowerCase();

        // SEARCH MATCH
        const matchesSearch =
            name.includes(searchValue) ||
            type.includes(searchValue) ||
            department.includes(searchValue) ||
            details.includes(searchValue);

        // FILTER MATCH
        let matchesFilter = true;

        if (filterValue === "student") {

            matchesFilter = type.includes("student");

        } else if (filterValue === "tstaff") {

            matchesFilter =
                type.includes("teaching") &&
                !type.includes("non");

        } else if (filterValue === "nstaff") {

            matchesFilter =
                type.includes("non-teaching") ||
                type.includes("non teaching");

        }

        // FINAL SHOW / HIDE
        if (matchesSearch && matchesFilter) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

    updateCount();
}

// ==========================
// VIEW CONSULTATION MODAL
// ==========================

const modal = document.getElementById("viewLogModal");
const modalContainer = document.querySelector(".modal-container");

function openModal(id) {

    // Show modal
    modal.style.display = "flex";

    // Optional loading state
    document.getElementById("modalDate").textContent = "Loading...";
    document.getElementById("modalTime").textContent = "Loading...";

    fetch("../actions/get_consultation.php?id=" + encodeURIComponent(id))
        .then(response => response.json())
        .then(data => {

            if (!data.success) {

                alert(data.message || "Unable to load consultation.");

                closeModal();

                return;
            }

            const consultation = data.consultation;

            // ==========================
            // RECORD INFORMATION
            // ==========================

            document.getElementById("modalDate").textContent =
                consultation.date;

            document.getElementById("modalTime").textContent =
                consultation.time;


            // ==========================
            // PATIENT INFORMATION
            // ==========================

            let patientType = consultation.patient_type;

                if (patientType === "student") {
                    patientType = "Student";
                } 
                else if (patientType === "tstaff") {
                    patientType = "Teaching Staff";
                } 
                else if (patientType === "nstaff") {
                    patientType = "Non-Teaching Staff";
                }

            document.getElementById("modalPatientType").textContent =
                patientType;

            document.getElementById("modalFullName").textContent =
                consultation.full_name;

            document.getElementById("modalAgeSex").textContent =
                `${consultation.age || "—"} / ${consultation.sex || "—"}`;

            document.getElementById("modalContact").textContent =
                consultation.contact_number || "—";

            document.getElementById("modalDepartment").textContent =
                consultation.department || "—";

            // ==========================
            // STUDENT / STAFF FIELDS
            // ==========================

            const courseYearBox =
                document.getElementById("modalCourseYearBox");

            const positionBox =
                document.getElementById("modalPositionBox");

            if (consultation.patient_type === "student") {

                // Show Course & Year
                courseYearBox.style.display = "";

                let courseYear = consultation.course || "—";

                if (consultation.year_level) {
                    courseYear += " - " + consultation.year_level;
                }

            document.getElementById("modalCourseYear").textContent =
            courseYear;

            // Hide Position
            positionBox.style.display = "none";

        } else {

            // Hide Course & Year
            courseYearBox.style.display = "none";

            // Show Position
            positionBox.style.display = "";

            document.getElementById("modalPosition").textContent =
                consultation.position || "—";
        }

            // ==========================
            // CLINICAL ASSESSMENT
            // ==========================

            document.getElementById("modalComplaint").textContent =
                consultation.complaint || "—";

            document.getElementById("modalTreatment").textContent =
                consultation.treatment_given || "—";

            document.getElementById("modalRemarks").textContent =
                consultation.remarks || "—";


            // ==========================
            // MEDICINES
            // ==========================

            const medicineContainer =
                document.getElementById("modalMedicines");

            // Keep table header
            medicineContainer.innerHTML = `
                <div class="table-row header">
                    <span>Medicine Name</span>
                    <span>Quantity</span>
                </div>
            `;

            if (
                data.medicines &&
                data.medicines.length > 0
            ) {

                data.medicines.forEach(medicine => {

                    const row = document.createElement("div");

                    row.className = "table-row";

                    row.innerHTML = `
                        <span>${medicine.medicine_name}</span>
                        <span>${medicine.quantity}</span>
                    `;

                    medicineContainer.appendChild(row);

                });

            } else {

                const row = document.createElement("div");

                row.className = "table-row";

                row.innerHTML = `
                    <span>No medicine dispensed</span>
                    <span>—</span>
                `;

                medicineContainer.appendChild(row);

            }

        })
        .catch(error => {

            console.error(error);

            alert("Something went wrong while loading the consultation.");

            closeModal();

        });
}


// ==========================
// CLOSE MODAL
// ==========================

function closeModal() {

    modal.style.display = "none";

}


// ==========================
// CLICK OUTSIDE MODAL
// ==========================

modal.addEventListener("click", function (e) {

    if (!modalContainer.contains(e.target)) {

        closeModal();

    }

});