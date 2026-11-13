let selectedStudentData = null;

document.addEventListener("DOMContentLoaded", function () {

    // =====================
    // AJAX LIVE SERVER-SIDE SEARCH
    // =====================

    const searchInput = document.getElementById("studentSearch");

    // REMOVE EMOJIS FROM EDIT STUDENT TEXT FIELDS
    document.addEventListener("input", function (event) {

        const input = event.target;

        if (!input.closest("#editStudentForm")) {
            return;
        }

        if (!input.matches('input[type="text"], input[type="tel"], textarea')) {
            return;
        }

        input.value = input.value.replace(
            /[\p{Extended_Pictographic}\p{Emoji_Presentation}\p{Emoji_Modifier}\p{Regional_Indicator}\uFE0F\u200D\u20E3]/gu,
            ""
        );

    });

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

                // Preserve current department filter
                const departmentInput =
                    document.querySelector(
                        'input[name="department"]'
                    );

                if (
                    departmentInput &&
                    departmentInput.value !== ""
                ) {
                    params.set(
                        "department",
                        departmentInput.value
                    );
                }

                // ALWAYS RESET SEARCH TO PAGE 1
                params.set("page", "1");

                try {

                    const response = await fetch(
                        "students.php?" + params.toString(),
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


                    // =====================
                    // UPDATE TABLE
                    // =====================

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


                    // =====================
                    // UPDATE FOOTER
                    // =====================

                    const newFooter =
                        doc.querySelector(".table-footer");

                    const currentFooter =
                        document.querySelector(".table-footer");

                    if (newFooter && currentFooter) {

                        currentFooter.innerHTML =
                            newFooter.innerHTML;

                    }


                    // =====================
                    // KEEP SEARCH FOCUSED
                    // =====================

                    searchInput.focus();

                    searchInput.setSelectionRange(
                        searchInput.value.length,
                        searchInput.value.length
                    );


                } catch (error) {

                    if (error.name !== "AbortError") {

                        console.error(
                            "Student search error:",
                            error
                        );

                    }

                }

            }, 300);

        });



    }

});

// =====================
// VIEW MODAL
// =====================

function openStudentModal(data) {

    selectedStudentData = data;

    document.getElementById("studentModal").style.display = "flex";

    document.getElementById("m_studentId").textContent = data.student_id_number;
        const fullName = [
            data.last_name,
            [
                data.first_name,
                data.middle_name
            ].filter(name => name && name.trim() !== "").join(" ")
        ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("m_fullName").textContent = fullName;
    document.getElementById("m_dob").textContent = data.dob || "Not Recorded";
    document.getElementById("m_age").textContent = data.age || "Not Recorded";
    document.getElementById("m_sex").textContent = data.sex || "Not Recorded";
    document.getElementById("m_course").textContent = data.course || "Not Recorded";
    document.getElementById("m_year").textContent = data.year || "Not Recorded";
    document.getElementById("m_department").textContent = data.department || "Not Recorded";
    document.getElementById("m_contact").textContent = data.contact || "Not Provided";
    document.getElementById("m_address").textContent = data.address || "Not Provided";

    // HEALTH INFORMATION
    document.getElementById("m_blood").textContent = data.blood || "Not Specified";
    document.getElementById("m_height").textContent = data.height ?? "Not Recorded";
    document.getElementById("m_weight").textContent = data.weight ?? "Not Recorded";
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

function closeStudentModal() {

    document.getElementById("studentModal").style.display = "none";

}

// =====================
// VIEW ALL MEDICAL HISTORY MODAL
// =====================

function formatHistoryDate(date) {

    if (!date) return "Date not recorded";

    const parsedDate = new Date(date + "T00:00:00");

    if (isNaN(parsedDate.getTime())) return date;

    return parsedDate.toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric"
    });
}

function createHistoryEntry(title, value) {

    const entry = document.createElement("div");
    entry.className = "history-entry";

    const heading = document.createElement("h4");
    heading.textContent = title;

    const content = document.createElement("p");
    content.textContent = value || "Not recorded";

    entry.appendChild(heading);
    entry.appendChild(content);

    return entry;
}

function displayHistoryRecords(containerId, records, type) {

    const container = document.getElementById(containerId);

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

        card.appendChild(createHistoryEntry(
            "Consultation Date",
            formatHistoryDate(record.date)
        ));

        card.appendChild(createHistoryEntry(
            "Time",
            record.time || "Not recorded"
        ));

        card.appendChild(createHistoryEntry(
            "Type",
            record.appointment_id
                ? "Appointment"
                : "Walk-in"
        ));

        card.appendChild(createHistoryEntry(
            "Complaint",
            record.complaint
        ));

        card.appendChild(createHistoryEntry(
            "Treatment Given",
            record.treatment_given
        ));

        card.appendChild(createHistoryEntry(
            "Medicine Dispensed",
            record.medicines_dispensed || "No medicine dispensed"
        ));

        card.appendChild(createHistoryEntry(
            "Remarks",
            record.remarks
        ));

        container.appendChild(card);
    });
}

async function openMedicalHistoryModal() {

    if (!selectedStudentData) {
        return;
    }

    const fullName = [
        selectedStudentData.last_name,
        [
            selectedStudentData.first_name,
            selectedStudentData.middle_name
        ].filter(name => name && name.trim() !== "").join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("historyStudentName").textContent = fullName;

    document.getElementById("pastMedicalHistory").textContent =
        selectedStudentData.history || "No past medical history recorded.";

    const clinicHistoryContainer =
        document.getElementById("consultationHistoryList");

    clinicHistoryContainer.textContent = "Loading clinic history...";

    document.getElementById("medicalHistoryModal").style.display = "flex";

    try {

        const response = await fetch(
            "../actions/get_student_medical_history.php?student_id=" +
            encodeURIComponent(selectedStudentData.id)
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to load medical history.");
        }

        displayHistoryRecords(
            "consultationHistoryList",
            data.consultations,
            "consultation"
        );

    } catch (error) {

        console.error("Medical history error:", error);

        clinicHistoryContainer.textContent =
            "Unable to load clinic history. Please try again.";
    }
}

function closeMedicalHistoryModal() {

    document.getElementById("medicalHistoryModal").style.display = "none";

}


// =====================
// ARCHIVE STUDENT MODAL
// =====================

function openArchiveStudentModal(student) {

    const fullName = [
        student.last_name,
        [
            student.first_name,
            student.middle_name
        ].filter(name => name && name.trim() !== "").join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("archiveStudentId").value = student.id;
    document.getElementById("archiveStudentName").textContent = fullName;

    document.getElementById("archiveStudentModal").classList.add("active");
}

function closeArchiveStudentModal() {

    document.getElementById("archiveStudentModal").classList.remove("active");
}

function confirmArchiveStudent() {

    const id = document.getElementById("archiveStudentId").value;

    if (!id) {
        return;
    }

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../actions/archive_student.php";

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "id";
    input.value = id;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}


// =====================
// CLICK OUTSIDE MODALS
// =====================

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("studentModal")) {
        closeStudentModal();
    }

    if (e.target === document.getElementById("archiveStudentModal")) {
    closeArchiveStudentModal();
}

});

function closeMessageModal() {

    document
        .getElementById("messageModal")
        .classList.remove("active");

}
