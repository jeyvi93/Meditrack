document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("staffSearch");
    const departmentFilter = document.getElementById("department");
    const positionFilter = document.getElementById("position");

    function normalize(text) {
        return (text || "").trim().toLowerCase();
    }

    function filterTable() {

        const search = normalize(searchInput.value);

        const selectedDepartment = normalize(
            departmentFilter.options[departmentFilter.selectedIndex].text
        );

        const selectedPosition = normalize(
            positionFilter.options[positionFilter.selectedIndex].text
        );

        document.querySelectorAll("tbody tr").forEach(row => {

            const id = normalize(row.cells[0].textContent);
            const name = normalize(row.cells[1].textContent);
            const sex = normalize(row.cells[2].textContent);
            const position = normalize(row.cells[3].textContent);
            const designation = normalize(row.cells[4].textContent);
            const department = normalize(row.cells[5].textContent);

            const matchesSearch =
                id.includes(search) ||
                name.includes(search) ||
                sex.includes(search) ||
                position.includes(search) ||
                designation.includes(search) ||
                department.includes(search);

            const matchesDepartment =
                selectedDepartment === "all departments" ||
                department === selectedDepartment;

            const matchesPosition =
                selectedPosition === "all positions" ||
                position === selectedPosition;

            row.style.display =
                matchesSearch &&
                matchesDepartment &&
                matchesPosition
                    ? ""
                    : "none";
        });

    }

    searchInput.addEventListener("input", filterTable);
    departmentFilter.addEventListener("change", filterTable);
    positionFilter.addEventListener("change", filterTable);

    filterTable();

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