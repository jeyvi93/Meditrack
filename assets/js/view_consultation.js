const searchInput = document.getElementById("searchInput");
const patientFilter = document.getElementById("patientFilter");
const tableRows = document.querySelectorAll(".logs-table tbody tr");

// run filter whenever user types or changes dropdown
searchInput.addEventListener("input", filterTable);
patientFilter.addEventListener("change", filterTable);

function filterTable() {
    const searchValue = searchInput.value.toLowerCase();
    const filterValue = patientFilter.value.toLowerCase();

    tableRows.forEach(row => {
        const name = row.children[1].innerText.toLowerCase();
        const type = row.children[2].innerText.toLowerCase();
        const department = row.children[3].innerText.toLowerCase();
        const details = row.children[4].innerText.toLowerCase();

        // SEARCH MATCH
        const matchesSearch =
            name.includes(searchValue) ||
            type.includes(searchValue) ||
            department.includes(searchValue) ||
            details.includes(searchValue);

        // FILTER MATCH
        let matchesFilter = true;

        if (filterValue === "student") {
            matchesFilter = type.includes("student");
        } 
        else if (filterValue === "staff") {
            matchesFilter = type.includes("staff");
        }

        // FINAL SHOW/HIDE
        if (matchesSearch && matchesFilter) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });

    updateCount();
}

function openModal() {
    document.getElementById("viewLogModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("viewLogModal").style.display = "none";
}

// Click outside
const modal = document.getElementById("viewLogModal");
const modalContainer = document.querySelector(".modal-container");

// close function
function closeModal() {
    modal.style.display = "none";
}

// open function (if you already have this)
function openModal() {
    modal.style.display = "flex";
}

// detect outside click
modal.addEventListener("click", function (e) {
    if (!modalContainer.contains(e.target)) {
        closeModal();
    }
});