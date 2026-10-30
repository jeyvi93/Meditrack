document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("archiveSearch");

    if (!searchInput) return;

    let searchTimeout;
    let searchController;

    searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(async function () {
            const search = searchInput.value.trim();

            if (searchController) {
                searchController.abort();
            }

            searchController = new AbortController();

            const params = new URLSearchParams();

            params.set(
                "tab",
                new URLSearchParams(window.location.search).get("tab") || "students"
            );

            params.set("search", search);
            params.set("page", "1");

            try {
                const response = await fetch(
                    "archived_records.php?" + params.toString(),
                    { signal: searchController.signal }
                );

                if (!response.ok) {
                    throw new Error("Search request failed.");
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                const newTable = doc.querySelector(".table-wrapper table");
                const currentTable = document.querySelector(".table-wrapper table");

                const newFooter = doc.querySelector(".table-footer");
                const currentFooter = document.querySelector(".table-footer");

                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                }

                if (newFooter && currentFooter) {
                    currentFooter.innerHTML = newFooter.innerHTML;
                }

                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set(
                    "tab",
                    params.get("tab")
                );
                newUrl.searchParams.set("search", search);
                newUrl.searchParams.set("page", "1");

                window.history.replaceState({}, "", newUrl);

            } catch (error) {
                if (error.name !== "AbortError") {
                    console.error("Archived records search error:", error);
                }
            }
        }, 300);
    });
});

function closeMessageModal() {
    const modal = document.getElementById("messageModal");

    if (modal) {
        modal.classList.remove("active");
    }
}

// =====================
// VIEW ARCHIVED STUDENT MODAL
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

function closeStudentModal() {
    document.getElementById("studentModal").style.display = "none";
}

// =====================
// VIEW ARCHIVED STAFF MODAL
// =====================

function openArchivedStaffModal(data) {

    const modal = document.getElementById("archivedStaffModal");

    const fullName = [
        data.last_name,
        [
            data.first_name,
            data.middle_name
        ].filter(name => name && name.trim() !== "").join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    const setText = (id, value, fallback = "Not Recorded") => {
        document.getElementById(id).textContent =
            value !== null && value !== undefined && String(value).trim() !== ""
                ? value
                : fallback;
    };

    setText("as_fullName", fullName);
    setText("as_staffId", data.id);
    setText("as_dob", data.dob);
    setText("as_age", data.age);
    setText("as_sex", data.sex);
    setText("as_position", data.position);
    setText("as_designation", data.designation);
    setText("as_department", data.department);
    setText("as_contact", data.contact, "Not Provided");
    setText("as_address", data.address, "Not Provided");

    // HEALTH INFORMATION
    setText("as_blood", data.blood, "Not Specified");

    setText(
        "as_height",
        data.height !== null && data.height !== ""
            ? data.height + " cm"
            : "",
        "Not Recorded"
    );

    setText(
        "as_weight",
        data.weight !== null && data.weight !== ""
            ? data.weight + " kg"
            : "",
        "Not Recorded"
    );

    setText("as_allergies", data.allergies);
    setText("as_history", data.history);
    setText("as_medications", data.medications);
    setText("as_vaccines", data.vaccines, "No Records Available");

    // INSURANCE INFORMATION
    setText("as_philhealth", data.philhealth, "Not Provided");
    setText("as_insuranceNumber", data.insuranceNumber, "Not Provided");
    setText("as_insuranceCompany", data.insuranceCompany, "Not Provided");

    // EMERGENCY CONTACT
    setText("as_emergencyName", data.emergencyName, "Not Provided");
    setText("as_relationship", data.relationship, "Not Provided");
    setText("as_emergencyNumber", data.emergencyNumber, "Not Provided");

    modal.style.display = "flex";
}

function closeArchivedStaffModal() {
    document.getElementById("archivedStaffModal").style.display = "none";
}

// CLOSE WHEN CLICKING OUTSIDE
window.addEventListener("click", function (e) {

    const modal = document.getElementById("archivedStaffModal");

    if (modal && e.target === modal) {
        closeArchivedStaffModal();
    }

});

// =====================
// CLOSE VIEW MODAL WHEN CLICKING OUTSIDE
// =====================

window.addEventListener("click", function (e) {

    const studentModal = document.getElementById("studentModal");

    if (studentModal && e.target === studentModal) {
        closeStudentModal();
    }

});