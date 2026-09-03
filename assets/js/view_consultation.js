document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchInput");
    const patientFilter = document.getElementById("patientFilter");


    // =========================
    // AJAX LIVE SERVER-SIDE SEARCH
    // =========================

    if (searchInput) {

        let searchTimeout;
        let searchController;

        searchInput.addEventListener("input", function () {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(async () => {

                const search = searchInput.value.trim();

                // Cancel previous request if still running
                if (searchController) {
                    searchController.abort();
                }

                searchController = new AbortController();

                const params = new URLSearchParams();

                if (search !== "") {
                    params.set("search", search);
                }

                // Preserve patient type filter
                if (
                    patientFilter &&
                    patientFilter.value !== "all"
                ) {
                    params.set(
                        "patient_type",
                        patientFilter.value
                    );
                }

                // ALWAYS RESET SEARCH TO PAGE 1
                params.set("page", "1");

                try {

                    const response = await fetch(
                        "view_consultation.php?" + params.toString(),
                        {
                            signal: searchController.signal
                        }
                    );

                    const html = await response.text();

                    const parser = new DOMParser();

                    const doc = parser.parseFromString(
                        html,
                        "text/html"
                    );


                    // =========================
                    // UPDATE TABLE
                    // =========================

                    const newTable =
                        doc.querySelector(".logs-table");

                    const currentTable =
                        document.querySelector(".logs-table");

                    if (newTable && currentTable) {

                        currentTable.innerHTML =
                            newTable.innerHTML;

                    }


                    // =========================
                    // UPDATE FOOTER
                    // =========================

                    const newFooter =
                        doc.querySelector(".table-footer");

                    const currentFooter =
                        document.querySelector(".table-footer");

                    if (newFooter && currentFooter) {

                        currentFooter.innerHTML =
                            newFooter.innerHTML;

                    }


                    // =========================
                    // KEEP SEARCH FOCUSED
                    // =========================

                    searchInput.focus();

                    searchInput.setSelectionRange(
                        searchInput.value.length,
                        searchInput.value.length
                    );


                } catch (error) {

                    if (error.name !== "AbortError") {

                        console.error(
                            "Consultation search error:",
                            error
                        );

                    }

                }

            }, 300);

        });



    }


    // =========================
    // PATIENT TYPE FILTER
    // =========================

    if (patientFilter) {

        patientFilter.addEventListener("change", function () {

            const params = new URLSearchParams();

            const search = searchInput
                ? searchInput.value.trim()
                : "";

            const patientType =
                patientFilter.value;


            if (search !== "") {
                params.set("search", search);
            }

            if (patientType !== "all") {
                params.set(
                    "patient_type",
                    patientType
                );
            }

            // ALWAYS reset filter to page 1
            params.set("page", "1");

            window.location.href =
                "view_consultation.php?" +
                params.toString();

        });

    }

});

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