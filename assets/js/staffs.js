document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("staffSearch");
    const departmentFilter = document.getElementById("department");
    const positionFilter = document.getElementById("position");

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

                // Preserve department filter
                if (
                    departmentFilter &&
                    departmentFilter.value !== "all"
                ) {
                    params.set(
                        "department",
                        departmentFilter.value
                    );
                }

                // Preserve position filter
                if (
                    positionFilter &&
                    positionFilter.value !== "all"
                ) {
                    params.set(
                        "position",
                        positionFilter.value
                    );
                }

                // ALWAYS RESET SEARCH TO PAGE 1
                params.set("page", "1");

                try {

                    const response = await fetch(
                        "staffs.php?" + params.toString(),
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
                        doc.querySelector(".table-wrapper table");

                    const currentTable =
                        document.querySelector(
                            ".table-wrapper table"
                        );

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
                            "Staff search error:",
                            error
                        );

                    }

                }

            }, 300);

        });

    }


    // =========================
    // LIVE SERVER-SIDE FILTER
    // =========================

    function applyFilters() {

        const params = new URLSearchParams();

        const search = searchInput
            ? searchInput.value.trim()
            : "";

        const department = departmentFilter
            ? departmentFilter.value
            : "all";

        const position = positionFilter
            ? positionFilter.value
            : "all";


        if (search !== "") {
            params.set("search", search);
        }

        if (department !== "all") {
            params.set("department", department);
        }

        if (position !== "all") {
            params.set("position", position);
        }

        // Always reset to page 1
        params.set("page", "1");

        window.location.href =
            "staffs.php?" + params.toString();

    }


    if (departmentFilter) {
        departmentFilter.addEventListener("change", applyFilters);
    }

    if (positionFilter) {
        positionFilter.addEventListener("change", applyFilters);
    }

});


// =========================
// VIEW STAFF MODAL
// =========================

let selectedStaffData = null;

function openStaffModal(data) {

    selectedStaffData = data;

    document.getElementById("staffModal").style.display = "flex";

    document.getElementById("m_staffId").textContent = data.staff_id_number;
        const fullName = [
            data.last_name,
            [
                data.first_name,
                data.middle_name
            ].filter(name => name && name.trim() !== "").join(" ")
        ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("m_fullName").textContent = fullName;
    document.getElementById("m_dob").textContent = data.dob || "Not Recorded";
    document.getElementById("m_age").textContent = data.age ?? "Not Recorded";
    document.getElementById("m_sex").textContent = data.sex || "Not Recorded";
    document.getElementById("m_position").textContent = data.position || "Not Recorded";
    document.getElementById("m_designation").textContent = data.designation || "Not Recorded";
    document.getElementById("m_department").textContent = data.department || "Not Recorded";
    document.getElementById("m_contact").textContent = data.contact || "Not Provided";
    document.getElementById("m_address").textContent = data.address || "Not Provided";

    // HEALTH INFORMATION
    document.getElementById("m_blood").textContent =
        data.blood && data.blood.trim() !== ""
            ? data.blood
            : "Not Specified";

    document.getElementById("m_height").textContent =
        data.height != null && data.height !== ""
            ? data.height + " cm"
            : "Not Recorded";

    document.getElementById("m_weight").textContent =
        data.weight != null && data.weight !== ""
            ? data.weight + " kg"
            : "Not Recorded";

    document.getElementById("m_allergies").textContent = data.allergies || "Not Recorded";
    document.getElementById("m_medications").textContent = data.medications || "Not Recorded";
    document.getElementById("m_vaccines").textContent = data.vaccines || "No Records Available";

    // INSURANCE INFORMATION
    document.getElementById("m_philhealth").textContent = data.philhealth || "Not Provided";
    document.getElementById("m_insuranceNumber").textContent = data.insuranceNumber || "Not Provided";
    document.getElementById("m_insuranceCompany").textContent = data.insuranceCompany || "Not Provided";

    // EMERGENCY CONTACT
    document.getElementById("m_emergencyName").textContent = data.emergencyName || "Not Provided";
    document.getElementById("m_relationship").textContent = data.relationship || "Not Provided";
    document.getElementById("m_emergencyNumber").textContent = data.emergencyNumber || "Not Provided";

}

function closeStaffModal() {

    document.getElementById("staffModal").style.display = "none";

}

// =========================
// STAFF MEDICAL HISTORY
// =========================

function formatStaffHistoryDate(date) {

    if (!date) return "Not Recorded";

    const parsedDate = new Date(date + "T00:00:00");

    return parsedDate.toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric"
    });

}

function createStaffHistoryEntry(title, value) {

    const entry = document.createElement("div");
    entry.className = "history-entry";

    const heading = document.createElement("h4");
    heading.textContent = title;

    const content = document.createElement("p");
    content.textContent = value || "Not Recorded";

    entry.appendChild(heading);
    entry.appendChild(content);

    return entry;

}

function displayStaffHistoryRecords(records) {

    const container = document.getElementById(
        "staffConsultationHistoryList"
    );

    container.replaceChildren();

    if (!records || records.length === 0) {

        const empty = document.createElement("p");
        empty.textContent = "No clinic history records found.";

        container.appendChild(empty);
        return;

    }

    records.forEach(record => {

        const card = document.createElement("div");
        card.className = "history-record";

        card.appendChild(
            createStaffHistoryEntry(
                "Consultation Date",
                formatStaffHistoryDate(record.date)
            )
        );

        card.appendChild(
            createStaffHistoryEntry(
                "Time",
                record.time || "Not Recorded"
            )
        );

        card.appendChild(
            createStaffHistoryEntry(
                "Type",
                record.appointment_id ? "Appointment" : "Walk-in"
            )
        );

        card.appendChild(
            createStaffHistoryEntry(
                "Complaint",
                record.complaint
            )
        );

        card.appendChild(
            createStaffHistoryEntry(
                "Treatment Given",
                record.treatment_given
            )
        );

        card.appendChild(
            createStaffHistoryEntry(
                "Medicine Dispensed",
                record.medicines_dispensed || "No medicine dispensed"
            )
        );

        card.appendChild(
            createStaffHistoryEntry(
                "Remarks",
                record.remarks
            )
        );

        container.appendChild(card);

    });

}

async function openStaffMedicalHistoryModal() {

    if (!selectedStaffData) return;

    const modal = document.getElementById(
        "staffMedicalHistoryModal"
    );

    const fullName = [
        selectedStaffData.last_name,
        [
            selectedStaffData.first_name,
            selectedStaffData.middle_name
        ].filter(name => name && name.trim() !== "").join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("historyStaffName").textContent =
        fullName;

    document.getElementById("pastStaffMedicalHistory").textContent =
        selectedStaffData.history || "No past medical history recorded.";

    const container = document.getElementById(
        "staffConsultationHistoryList"
    );

    container.replaceChildren();

    const loading = document.createElement("p");
    loading.textContent = "Loading clinic history...";

    container.appendChild(loading);

    modal.style.display = "flex";

    try {

        const response = await fetch(
            "../actions/get_staff_medical_history.php?staff_id=" +
            encodeURIComponent(selectedStaffData.id)
        );

        if (!response.ok) {
            throw new Error("Unable to retrieve medical history.");
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(
                data.message || "Unable to retrieve medical history."
            );
        }

        displayStaffHistoryRecords(data.consultations);

    } catch (error) {

        console.error("Staff medical history error:", error);

        container.replaceChildren();

        const errorMessage = document.createElement("p");
        errorMessage.textContent = "Unable to load clinic history. Please try again.";

        container.appendChild(errorMessage);

    }

}

function closeStaffMedicalHistoryModal() {

    document.getElementById(
        "staffMedicalHistoryModal"
    ).style.display = "none";

}

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("staffModal")) {
        closeStaffModal();
    }

});

// =========================
// ARCHIVE STAFF MODAL
// =========================

function openArchiveStaffModal(data) {

    const fullName = [
        data.last_name,
        [
            data.first_name,
            data.middle_name
        ].filter(name => name && name.trim() !== "").join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("archiveStaffId").value = data.id;
    document.getElementById("archiveStaffName").textContent = fullName;

    document.getElementById("archiveStaffModal").classList.add("active");
}

function closeArchiveStaffModal() {

    document.getElementById("archiveStaffModal").classList.remove("active");

}

function confirmArchiveStaff() {

    const id = document.getElementById("archiveStaffId").value;

    if (!id) return;

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../actions/archive_staff.php";

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "id";
    input.value = id;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();

}

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("archiveStaffModal")) {
        closeArchiveStaffModal();
    }

});


function closeMessageModal() {

    document
        .getElementById("messageModal")
        .classList.remove("active");

}
