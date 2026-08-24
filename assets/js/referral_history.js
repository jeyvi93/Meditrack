// ===============================
// REFERRAL MODAL
// ===============================

function openModal(id) {

    fetch(`../actions/get_referral.php?id=${id}`)
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                alert(data.message || "Failed to load referral.");
                return;
            }

            const referral = data.referral;

            document.getElementById("modalReferredDate").textContent =
                referral.referred_date || "—";

            document.getElementById("modalDoctor").textContent =
                referral.doctor_name || "—";

            document.getElementById("modalReason").textContent =
                referral.reason_for_referral || "—";

            document.getElementById("modalFullName").textContent =
                referral.full_name || "—";

            document.getElementById("modalSex").textContent =
                referral.sex || "—";

            document.getElementById("modalDob").textContent =
                referral.date_of_birth || "—";

            document.getElementById("modalContact").textContent =
                referral.contact_number || "—";

            document.getElementById("modalAddress").textContent =
                referral.address || "—";

            document.getElementById("modalParentName").textContent =
                referral.parent_name || "—";

            document.getElementById("modalParentDob").textContent =
                referral.parent_dob || "—";

            document.getElementById("modalSpouseName").textContent =
                referral.spouse_name || "—";

            document.getElementById("modalSpouseDob").textContent =
                referral.spouse_dob || "—";

            document.getElementById("modalPhilhealthMember").textContent =
                Number(referral.is_philhealth_member) === 1 ? "Yes" : "No";

            document.getElementById("modalPhilhealthNumber").textContent =
                referral.philhealth_number || "—";

            document.getElementById("modalBP").textContent =
                referral.blood_pressure || "—";

            document.getElementById("modalWeight").textContent =
                referral.weight ? referral.weight + " kg" : "—";

            document.getElementById("modalTemperature").textContent =
                referral.temperature ? referral.temperature + " °C" : "—";

            document.getElementById("modalNurseNotes").textContent =
                referral.nurse_notes || "—";

            document.getElementById("referralModal").style.display = "flex";

        })
        .catch(error => {

            console.error(error);
            alert("Something went wrong while loading the referral.");

        });
}

function closeModal() {
    document.getElementById("referralModal").style.display = "none";
}

const modal = document.getElementById("referralModal");

if (modal) {
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
}

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        closeModal();
    }
});

// ===============================
// SEARCH FUNCTION
// ===============================

const searchInput = document.getElementById("searchInput");

if (searchInput) {
    searchInput.addEventListener("input", filterTable);
}

function filterTable() {

    const searchValue = searchInput.value
        .trim()
        .toLowerCase();

    const tableRows = document.querySelectorAll(
        ".logs-table tbody tr"
    );

    tableRows.forEach(row => {

        const rowText = row.innerText.toLowerCase();

        if (rowText.includes(searchValue)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });
}