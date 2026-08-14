document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("studentSearch");
    const filterPills = document.querySelectorAll(".filter-pill");
    const rows = document.querySelectorAll("tbody tr");

    let activeDepartment = "All";

    const normalize = (text) => (text || "").toString().trim().toLowerCase();

    // =====================
    // SEARCH & FILTER
    // =====================

    function filterTable() {

        const searchValue = normalize(searchInput.value);

        rows.forEach(row => {

            const id = normalize(row.cells[0].textContent);
            const name = normalize(row.cells[1].textContent);
            const sex = normalize(row.cells[2].textContent);
            const course = normalize(row.cells[3].textContent);
            const year = normalize(row.cells[4].textContent);
            const department = normalize(row.cells[5].textContent);

            const matchesSearch =
                id.includes(searchValue) ||
                name.includes(searchValue) ||
                sex.includes(searchValue) ||
                course.includes(searchValue) ||
                year.includes(searchValue) ||
                department.includes(searchValue);

            const matchesDepartment =
                activeDepartment === "All" ||
                department === normalize(activeDepartment);

            row.style.display = (matchesSearch && matchesDepartment) ? "" : "none";

        });

    }

    if (searchInput) {
        searchInput.addEventListener("input", filterTable);
    }

    filterPills.forEach(pill => {

        pill.addEventListener("click", function () {

            filterPills.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");

            activeDepartment = this.textContent.trim();

            filterTable();

        });

    });

    // =====================
    // AUTO AGE
    // =====================

    const dobInput = document.getElementById("e_dob");

    if (dobInput) {

        dobInput.addEventListener("change", function () {

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

    }

});


// =====================
// VIEW MODAL
// =====================

function openStudentModal(data) {

    document.getElementById("studentModal").style.display = "flex";

    document.getElementById("m_studentId").textContent = data.id;
    document.getElementById("m_fullName").textContent = data.name;
    document.getElementById("m_dob").textContent = data.dob;
    document.getElementById("m_age").textContent = data.age;
    document.getElementById("m_sex").textContent = data.sex;
    document.getElementById("m_course").textContent = data.course;
    document.getElementById("m_year").textContent = data.year;
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

function closeStudentModal() {

    document.getElementById("studentModal").style.display = "none";

}


// =====================
// EDIT MODAL
// =====================

function openEditStudentModal(data) {

    document.getElementById("editStudentModal").classList.add("active");

    document.getElementById("e_studentId").value = data.id;
    document.getElementById("e_studentIdDisplay").value = data.id;

    document.getElementById("e_fullName").value = data.name;
    document.getElementById("e_dob").value = data.dob;
    document.getElementById("e_age").value = data.age;
    document.getElementById("e_sex").value = data.sex;

    document.getElementById("e_course").value = data.course;
    document.getElementById("e_year").value = data.year;
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

function closeEditStudentModal() {

    document.getElementById("editStudentModal").classList.remove("active");

}


// =====================
// DELETE MODAL
// =====================

function openDeleteStudentModal(student) {

    document.getElementById("d_studentId").value = student.id;
    document.getElementById("d_studentName").textContent = student.name;

    document.getElementById("deleteStudentModal").classList.add("active");

}

function closeDeleteStudentModal() {

    document.getElementById("deleteStudentModal").classList.remove("active");

}

function confirmDeleteStudent() {

    const id = document.getElementById("d_studentId").value;

    window.location.href =
        "../actions/delete_student.php?id=" + encodeURIComponent(id);

}


// =====================
// CLICK OUTSIDE MODALS
// =====================

window.addEventListener("click", function (e) {

    if (e.target === document.getElementById("studentModal")) {
        closeStudentModal();
    }

    if (e.target === document.getElementById("editStudentModal")) {
        closeEditStudentModal();
    }

    if (e.target === document.getElementById("deleteStudentModal")) {
        closeDeleteStudentModal();
    }

});

function closeMessageModal() {

    document
        .getElementById("messageModal")
        .classList.remove("active");

}
