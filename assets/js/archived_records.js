
// ==========================
// ARCHIVED RECORDS SEARCH
// ==========================

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("archiveSearch");

    if (searchInput) {
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
                    const doc = new DOMParser().parseFromString(html, "text/html");

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
                    newUrl.searchParams.set("tab", params.get("tab"));
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
    }
});


// ==========================
// RESTORE CONFIRMATION MODAL
// ==========================

function openRestoreModal(id, type, fullName) {
    const modal = document.getElementById("restoreConfirmModal");

    if (!modal) {
        console.error("Restore confirmation modal was not found.");
        return;
    }

    const recordId = document.getElementById("restoreRecordId");
    const recordType = document.getElementById("restoreRecordTypeInput");
    const recordName = document.getElementById("restoreRecordName");
    const title = document.getElementById("restoreConfirmTitle");

    if (!recordId || !recordType || !recordName || !title) {
        console.error("One or more restore modal elements are missing.");
        return;
    }

    recordId.value = id;
    recordType.value = type;
    recordName.textContent = fullName;

    title.textContent =
        type === "students" ? "Restore Student?" : "Restore Staff?";

    modal.style.display = "flex";
}

function closeRestoreModal() {
    const modal = document.getElementById("restoreConfirmModal");

    if (modal) {
        modal.style.display = "none";
    }
}


// ==========================
// VIEW ARCHIVED STUDENT MODAL
// ==========================

function openStudentModal(data) {
    const modal = document.getElementById("studentModal");

    if (!modal) return;

    modal.style.display = "flex";

    const setText = (id, value, fallback = "Not Recorded") => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent =
                value !== null && value !== undefined && String(value).trim() !== ""
                    ? value
                    : fallback;
        }
    };

    const fullName = [
        data.last_name,
        [data.first_name, data.middle_name]
            .filter(name => name && name.trim() !== "")
            .join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

    setText("m_studentId", data.id);
    setText("m_fullName", fullName);
    setText("m_dob", data.dob);
    setText("m_age", data.age);
    setText("m_sex", data.sex);
    setText("m_course", data.course);
    setText("m_year", data.year);
    setText("m_department", data.department);
    setText("m_contact", data.contact, "Not Provided");
    setText("m_address", data.address, "Not Provided");

    setText("m_blood", data.blood, "Not Specified");
    setText("m_height", data.height);
    setText("m_weight", data.weight);
    setText("m_allergies", data.allergies);
    setText("m_history", data.history);
    setText("m_medications", data.medications);
    setText("m_vaccines", data.vaccines, "No Records Available");

    setText("m_philhealth", data.philhealth, "Not Provided");
    setText("m_insuranceNumber", data.insuranceNumber, "Not Provided");
    setText("m_insuranceCompany", data.insuranceCompany, "Not Provided");

    setText("m_emergencyName", data.emergencyName, "Not Provided");
    setText("m_relationship", data.relationship, "Not Provided");
    setText("m_emergencyNumber", data.emergencyNumber, "Not Provided");
}

function closeStudentModal() {
    const modal = document.getElementById("studentModal");

    if (modal) {
        modal.style.display = "none";
    }
}


// ==========================
// VIEW ARCHIVED STAFF MODAL
// ==========================

function openArchivedStaffModal(data) {
    const modal = document.getElementById("archivedStaffModal");

    if (!modal) return;

    const setText = (id, value, fallback = "Not Recorded") => {
        const element = document.getElementById(id);

        if (element) {
            element.textContent =
                value !== null && value !== undefined && String(value).trim() !== ""
                    ? value
                    : fallback;
        }
    };

    const fullName = [
        data.last_name,
        [data.first_name, data.middle_name]
            .filter(name => name && name.trim() !== "")
            .join(" ")
    ].filter(name => name && name.trim() !== "").join(", ");

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

    setText("as_philhealth", data.philhealth, "Not Provided");
    setText("as_insuranceNumber", data.insuranceNumber, "Not Provided");
    setText("as_insuranceCompany", data.insuranceCompany, "Not Provided");

    setText("as_emergencyName", data.emergencyName, "Not Provided");
    setText("as_relationship", data.relationship, "Not Provided");
    setText("as_emergencyNumber", data.emergencyNumber, "Not Provided");

    modal.style.display = "flex";
}

function closeArchivedStaffModal() {
    const modal = document.getElementById("archivedStaffModal");

    if (modal) {
        modal.style.display = "none";
    }
}


// ==========================
// CLOSE SUCCESS / ERROR MODAL
// ==========================

function closeMessageModal() {
    const modal = document.getElementById("messageModal");

    if (modal) {
        modal.classList.remove("active");
        modal.style.display = "none";
    }
}


// ==========================
// CLOSE MODALS WHEN CLICKING OUTSIDE
// ==========================

window.addEventListener("click", function (event) {
    const restoreModal = document.getElementById("restoreConfirmModal");
    const studentModal = document.getElementById("studentModal");
    const staffModal = document.getElementById("archivedStaffModal");
    const messageModal = document.getElementById("messageModal");

    if (event.target === restoreModal) {
        closeRestoreModal();
    }

    if (event.target === studentModal) {
        closeStudentModal();
    }

    if (event.target === staffModal) {
        closeArchivedStaffModal();
    }

    if (event.target === messageModal) {
        closeMessageModal();
    }
});
