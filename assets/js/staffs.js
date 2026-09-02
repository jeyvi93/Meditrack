document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("staffSearch");
    const departmentFilter = document.getElementById("department");
    const positionFilter = document.getElementById("position");

    // =========================
    // LIVE SERVER-SIDE SEARCH
    // =========================

    if (searchInput) {

        let searchTimeout;

        searchInput.addEventListener("input", function () {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {

                const search = searchInput.value.trim();

                const params = new URLSearchParams();

                if (search !== "") {
                    params.set("search", search);
                }

                // Preserve department
                if (departmentFilter && departmentFilter.value !== "all") {
                    params.set("department", departmentFilter.value);
                }

                // Preserve position
                if (positionFilter && positionFilter.value !== "all") {
                    params.set("position", positionFilter.value);
                }

                // ALWAYS start search from page 1
                params.set("page", "1");

                window.location.href =
                    "staffs.php?" + params.toString();

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
    document.getElementById("m_fullName").textContent = data.name;
    document.getElementById("m_dob").textContent = data.dob;
    document.getElementById("m_age").textContent = data.age;
    document.getElementById("m_sex").textContent = data.sex;
    document.getElementById("m_position").textContent = data.position;
    document.getElementById("m_designation").textContent = data.designation;
    document.getElementById("m_department").textContent = data.department;
    document.getElementById("m_contact").textContent = data.contact;
    document.getElementById("m_address").textContent = data.address;

    document.getElementById("m_blood").textContent = data.blood;
    document.getElementById("m_height").textContent = data.height;
    document.getElementById("m_weight").textContent = data.weight;
    document.getElementById("m_allergies").textContent = data.allergies;
    document.getElementById("m_history").textContent = data.history;
    document.getElementById("m_medications").textContent = data.medications;
    document.getElementById("m_vaccines").textContent = data.vaccines;

    document.getElementById("m_philhealth").textContent = data.philhealth;
    document.getElementById("m_insuranceNumber").textContent = data.insuranceNumber;
    document.getElementById("m_insuranceCompany").textContent = data.insuranceCompany;

    document.getElementById("m_emergencyName").textContent = data.emergencyName;
    document.getElementById("m_relationship").textContent = data.relationship;
    document.getElementById("m_emergencyNumber").textContent = data.emergencyNumber;

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

    document.getElementById("e_fullName").value = data.name;

    document.getElementById("e_dob").value = data.dob;
    document.getElementById("e_age").value = data.age;
    document.getElementById("e_sex").value = data.sex;
    document.getElementById("e_position").value = data.position;
    document.getElementById("e_designation").value = data.designation;
    document.getElementById("e_department").value = data.department;
    document.getElementById("e_contact").value = data.contact;
    document.getElementById("e_address").value = data.address;

    document.getElementById("e_blood").value = data.blood;
    document.getElementById("e_height").value = data.height;
    document.getElementById("e_weight").value = data.weight;
    document.getElementById("e_allergies").value = data.allergies;
    document.getElementById("e_history").value = data.history;
    document.getElementById("e_medications").value = data.medications;
    document.getElementById("e_vaccines").value = data.vaccines;

    document.getElementById("e_philhealth").value = data.philhealth;
    document.getElementById("e_insuranceNumber").value = data.insuranceNumber;
    document.getElementById("e_insuranceCompany").value = data.insuranceCompany;

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
    document.getElementById("d_staffName").textContent = data.name;

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