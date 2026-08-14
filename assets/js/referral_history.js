// ===============================
// REFERRAL MODAL
// ===============================

function openModal() {
    document.getElementById("referralModal").style.display = "flex";
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
    const searchValue = searchInput.value.toLowerCase();
    const tableRows = document.querySelectorAll(".logs-table tbody tr");

    tableRows.forEach(row => {
        const rowText = row.innerText.toLowerCase();

        row.style.display =
            rowText.includes(searchValue) ? "" : "none";
    });
}