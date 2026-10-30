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

function openStaffModal(data) {

    document.getElementById("staffModal").style.display = "flex";

    document.getElementById("m_staffId").textContent = data.id;
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
    document.getElementById("m_history").textContent = data.history || "Not Recorded";
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

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("staffModal")) {
        closeStaffModal();
    }

});


// =========================
// EDIT STAFF MODAL
// =========================

function openEditStaffModal(data) {

    document.getElementById("editStaffModal").classList.add("active");

    // Hidden input submitted to PHP
    document.getElementById("e_staffId").value = data.id;

    // Display only
    document.getElementById("e_staffIdDisplay").value = data.id;

    document.getElementById("e_lastName").value = data.last_name || "";
    document.getElementById("e_firstName").value = data.first_name || "";
    document.getElementById("e_middleName").value = data.middle_name || "";

    document.getElementById("e_dob").value = data.dob;
    document.getElementById("e_age").value = data.age;
    document.getElementById("e_sex").value = data.sex;
    document.getElementById("e_position").value = data.position;
    document.getElementById("e_designation").value = data.designation;
    document.getElementById("e_department").value = data.department;
    document.getElementById("e_contact").value = data.contact;
    document.getElementById("e_address").value = data.address;

    document.getElementById("e_blood").value = data.blood || "";
    document.getElementById("e_height").value = data.height ?? "";
    document.getElementById("e_weight").value = data.weight ?? "";
    document.getElementById("e_allergies").value = data.allergies ?? "";
    document.getElementById("e_history").value = data.history ?? "";
    document.getElementById("e_medications").value = data.medications ?? "";
    document.getElementById("e_vaccines").value = data.vaccines ?? "";

    document.getElementById("e_philhealth").value = data.philhealth ?? "";
    document.getElementById("e_insuranceNumber").value = data.insuranceNumber ?? "";
    document.getElementById("e_insuranceCompany").value = data.insuranceCompany ?? "";

    document.getElementById("e_emergencyName").value = data.emergencyName;
    document.getElementById("e_relationship").value = data.relationship;
    document.getElementById("e_emergencyNumber").value = data.emergencyNumber;

}

function closeEditStaffModal() {

    document.getElementById("editStaffModal").classList.remove("active");

}

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("editStaffModal")) {
        closeEditStaffModal();
    }

});


// =========================
// DELETE STAFF MODAL
// =========================

function openDeleteStaffModal(data) {

    document.getElementById("d_staffId").value = data.id;
        const fullName = [
            data.last_name,
            [
                data.first_name,
                data.middle_name
            ].filter(name => name && name.trim() !== "").join(" ")
        ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("d_staffName").textContent = fullName;

    document.getElementById("deleteStaffModal").classList.add("active");

}

function closeDeleteStaffModal() {

    document.getElementById("deleteStaffModal").classList.remove("active");

}

function confirmDeleteStaff() {

    const id = document.getElementById("d_staffId").value;

    window.location.href =
        "../actions/delete_staff.php?id=" + encodeURIComponent(id);

}

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("deleteStaffModal")) {
        closeDeleteStaffModal();
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


// =========================
// AUTO COMPUTE AGE
// =========================

document.getElementById("e_dob").addEventListener("change", function () {

    const dob = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - dob.getFullYear();

    const month = today.getMonth() - dob.getMonth();

    if (
        month < 0 ||
        (month === 0 && today.getDate() < dob.getDate())
    ) {
        age--;
    }

    document.getElementById("e_age").value = age;

});

function closeMessageModal() {

    document
        .getElementById("messageModal")
        .classList.remove("active");

}