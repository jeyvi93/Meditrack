document.addEventListener("DOMContentLoaded", function () {

    // =====================
    // AJAX LIVE SERVER-SIDE SEARCH
    // =====================

    const searchInput = document.getElementById("studentSearch");

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
        const fullName = [
            data.last_name,
            [
                data.first_name,
                data.middle_name
            ].filter(name => name && name.trim() !== "").join(" ")
        ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("m_fullName").textContent = fullName;
    document.getElementById("m_dob").textContent = data.dob;
    document.getElementById("m_age").textContent = data.age;
    document.getElementById("m_sex").textContent = data.sex;
    document.getElementById("m_course").textContent = data.course;
    document.getElementById("m_year").textContent = data.year;
    document.getElementById("m_department").textContent = data.department;
    document.getElementById("m_contact").textContent = data.contact;
    document.getElementById("m_address").textContent = data.address;

    document.getElementById("m_blood").textContent = data.blood || "Not Specified";
    document.getElementById("m_height").textContent = data.height ?? "N/A";
    document.getElementById("m_weight").textContent = data.weight ?? "N/A";
    document.getElementById("m_allergies").textContent = data.allergies || "N/A";
    document.getElementById("m_history").textContent = data.history || "N/A";
    document.getElementById("m_medications").textContent = data.medications || "N/A";
    document.getElementById("m_vaccines").textContent = data.vaccines || "N/A";

    document.getElementById("m_philhealth").textContent = data.philhealth || "N/A";
    document.getElementById("m_insuranceNumber").textContent = data.insuranceNumber || "N/A";
    document.getElementById("m_insuranceCompany").textContent = data.insuranceCompany || "N/A";

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

    document.getElementById("e_lastName").value = data.last_name;
    document.getElementById("e_firstName").value = data.first_name;
    document.getElementById("e_middleName").value = data.middle_name ?? "";
    document.getElementById("e_dob").value = data.dob;
    document.getElementById("e_age").value = data.age;
    document.getElementById("e_sex").value = data.sex;

    document.getElementById("e_course").value = data.course;
    document.getElementById("e_year").value = data.year;
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

function closeEditStudentModal() {

    document.getElementById("editStudentModal").classList.remove("active");

}


// =====================
// DELETE MODAL
// =====================

function openDeleteStudentModal(student) {

    const middleName = student.middle_name
        ? student.middle_name.trim()
        : "";

    const fullName = [
        student.last_name,
        [
            student.first_name,
            student.middle_name
        ].filter(name => name && name.trim() !== "").join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    document.getElementById("d_studentId").value = student.id;
    document.getElementById("d_studentName").textContent = fullName;

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

    if (e.target === document.getElementById("editStudentModal")) {
        closeEditStudentModal();
    }

    if (e.target === document.getElementById("deleteStudentModal")) {
        closeDeleteStudentModal();
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
