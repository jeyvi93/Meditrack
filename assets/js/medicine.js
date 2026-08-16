function openTab(evt, tabName) {
    const panes = document.querySelectorAll(".tab-pane");
    const tabs = document.querySelectorAll(".tab-link");

    panes.forEach(p => p.classList.remove("active"));
    tabs.forEach(t => t.classList.remove("active"));

    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");

    const filter = document.getElementById("filterGroup");

    //  ONLY show filter in "all"
    if (tabName === "all") {
        filter.style.display = "flex";
    } else {
        filter.style.display = "none";
    }

    // Apply search to the newly selected tab
    filterMedicines();
}



// ==========================
// SEARCH + FILTER
// ==========================

const statusFilter = document.getElementById("statusFilter");
const medicineSearch = document.getElementById("medicineSearch");

function filterMedicines() {

    const searchText = medicineSearch.value.toLowerCase().trim();
    const selectedStatus = statusFilter.value.toLowerCase();

    // Find the currently active tab
    const activePane = document.querySelector(".tab-pane.active");

    if (!activePane) return;

    // Get rows from the active tab only
    const rows = activePane.querySelectorAll("tbody tr");

    rows.forEach(row => {

        // Ignore the "No medicines found" row
        if (!row.querySelector("td")) return;

        const rowText = row.innerText.toLowerCase();

        const status = (
            row.dataset.status ||
            row.querySelector(".status-pill")?.innerText ||
            ""
        ).toLowerCase().trim();

        const matchesSearch =
            !searchText || rowText.includes(searchText);

        const matchesStatus =
            !selectedStatus || status === selectedStatus;

        row.style.display =
            matchesSearch && matchesStatus
                ? ""
                : "none";
    });
}

// Search while typing
medicineSearch.addEventListener("input", filterMedicines);

// Filter when dropdown changes
statusFilter.addEventListener("change", filterMedicines);

// ==========================
// DISPOSE MEDICINE
// ==========================

const disposeModal = document.getElementById("disposeModal");
const reasonGroup = document.getElementById("disposeReasonGroup");
const fixedReason = document.getElementById("disposeFixedReasonContainer");

let disposeMedicineId = null;
let disposeType = null;


// ==========================
// CLOSE DISPOSE MODAL
// ==========================

function closeDisposeModal() {

    disposeModal.style.display = "none";

    disposeMedicineId = null;
    disposeType = null;

    // Reset reason dropdown
    document.getElementById("disposeReasonSelect").value = "Select reason";
}


// ==========================
// LOAD MEDICINE INFORMATION
// ==========================

function loadDisposeMedicine(id) {

    disposeMedicineId = id;

    fetch("../actions/get_medicine.php?id=" + id)
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                alert("Unable to retrieve medicine information.");
                return;
            }

            document.getElementById("disposeMedicine").textContent =
                data.medicine_name;

            document.getElementById("disposeBatch").textContent =
                data.batch_number;

            document.getElementById("disposeQty").textContent =
                data.quantity;

            disposeModal.style.display = "flex";

        })
        .catch(error => {

            console.error(error);

            alert("Unable to load medicine information.");

        });
}


// ==========================
// NEAR EXPIRY
// ==========================

function openNearExpiryDispose(id) {

    disposeType = "near_expiry";

    reasonGroup.style.display = "none";
    fixedReason.style.display = "block";

    document.getElementById("disposeFixedReason").textContent =
        "Will Not Be Consumed Before Expiry";

    document.getElementById("disposeModalTitle").textContent =
        "Dispose Near-Expiry Batch";

    loadDisposeMedicine(id);
}


// ==========================
// EXPIRED
// ==========================

function openExpiredDispose(id) {

    disposeType = "expired";

    reasonGroup.style.display = "none";
    fixedReason.style.display = "block";

    document.getElementById("disposeFixedReason").textContent =
        "Expired";

    document.getElementById("disposeModalTitle").textContent =
        "Dispose Expired Batch";

    loadDisposeMedicine(id);
}


// ==========================
// QUARANTINED
// ==========================

function openQuarantineDispose(id) {

    disposeType = "quarantine";

    reasonGroup.style.display = "block";
    fixedReason.style.display = "none";

    document.getElementById("disposeModalTitle").textContent =
        "Dispose Quarantined Batch";

    loadDisposeMedicine(id);
}


// ==========================
// CONFIRM DISPOSAL
// ==========================

document
    .querySelector(".btn-confirm-dispose")
    .addEventListener("click", function () {

        if (!disposeMedicineId || !disposeType) {
            alert("Invalid medicine.");
            return;
        }

        let disposalReason = "";

        // Near expiry
        if (disposeType === "near_expiry") {

            disposalReason =
                "Will Not Be Consumed Before Expiry";

        }

        // Expired
        else if (disposeType === "expired") {

            disposalReason = "Expired";

        }

        // Quarantine
        else if (disposeType === "quarantine") {

            disposalReason =
                document.getElementById("disposeReasonSelect").value;

            if (
                !disposalReason ||
                disposalReason === "Select reason"
            ) {
                alert("Please select a disposal reason.");
                return;
            }
        }

        const formData = new FormData();

        formData.append("id", disposeMedicineId);
        formData.append("disposal_reason", disposalReason);
        formData.append("dispose_type", disposeType);

        fetch("../actions/dispose_medicine.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if (data.success) {

                closeDisposeModal();

                location.reload();

            } else {

                alert(
                    data.message ||
                    "Unable to dispose medicine."
                );

            }

        })
        .catch(error => {

            console.error(error);

            alert("Something went wrong while disposing the medicine.");

        });

    });

//REPORT MODAL

function openReportModal() {
    generateInventoryReport();
    document.getElementById("reportModal").style.display = "flex";
}

function closeReportModal() {
    document.getElementById("reportModal").style.display = "none";
}

function generateInventoryReport() {

    const rows = document.querySelectorAll("#all tbody tr");

    let total = 0;
    let expired = 0;
    let low = 0;
    let near = 0;

    const list = document.getElementById("reportList");
    list.innerHTML = "";

    rows.forEach(row => {

        const name = row.querySelector("td strong")?.innerText;
        const status = row.dataset.status;

        total++;

        if (status === "Expired") expired++;
        if (status === "Low Stock") low++;
        if (status === "Near Expiry") near++;

        const li = document.createElement("li");
        li.innerText = `${name} — ${status}`;
        list.appendChild(li);
    });

    document.getElementById("repTotal").innerText = total;
    document.getElementById("repExpired").innerText = expired;
    document.getElementById("repLow").innerText = low;
    document.getElementById("repNear").innerText = near;
}

// ==========================
// EDIT MEDICINE
// ==========================

let editMedicineId = null;

function openEditModal(id) {

    editMedicineId = id;

    fetch("../actions/get_medicine.php?id=" + id)
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                alert("Medicine not found.");
                return;
            }

            document.getElementById("editMedicineName").value =
                data.medicine_name;

            document.getElementById("editBatchNumber").value =
                data.batch_number;

            document.getElementById("editQuantity").value =
                data.quantity;

            document.getElementById("editSupplier").value =
                data.supplier;

            document.getElementById("editDateAcquired").value =
                data.date_acquired;

            document.getElementById("editExpiryDate").value =
                data.expiry_date;

            document.getElementById("editModal").style.display = "flex";

        })
        .catch(error => {
            console.error(error);
            alert("Unable to load medicine information.");
        });
}


function closeEditModal() {

    document.getElementById("editModal").style.display = "none";

    editMedicineId = null;
}

document.getElementById("editMedicineForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const formData = new FormData();

    formData.append("id", editMedicineId);
    formData.append(
        "medicine_name",
        document.getElementById("editMedicineName").value
    );
    formData.append(
        "batch_number",
        document.getElementById("editBatchNumber").value
    );
    formData.append(
        "quantity",
        document.getElementById("editQuantity").value
    );
    formData.append(
        "supplier",
        document.getElementById("editSupplier").value
    );
    formData.append(
        "date_acquired",
        document.getElementById("editDateAcquired").value
    );
    formData.append(
        "expiry_date",
        document.getElementById("editExpiryDate").value
    );

    fetch("../actions/update_medicine.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        if (!data.success) {
            alert(data.message || "Failed to update medicine.");
            return;
        }

        closeEditModal();

        openSuccessModal();

    })
    .catch(error => {

        console.error(error);

        alert("Unable to update medicine.");

    });

});

// ==========================
// SUCCESS MODAL
// ==========================

function openSuccessModal() {
    document.getElementById("successModal").style.display = "flex";
}

function closeSuccessModal() {
    document.getElementById("successModal").style.display = "none";
    location.reload();
}

function quarantineMedicine(id) {

    if (!confirm("Are you sure you want to quarantine this medicine?")) {
        return;
    }

    fetch("../actions/quarantine_medicine.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {
            location.reload();
        } else {
            alert(data.message || "Unable to quarantine medicine.");
        }

    })
    .catch(error => {
        console.error(error);
        alert("Something went wrong.");
    });
}

// ==========================
// QUARANTINE MEDICINE
// ==========================

let quarantineMedicineId = null;

function openQuarantineModal(id) {

    quarantineMedicineId = id;

    fetch("../actions/get_medicine.php?id=" + id)
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                alert("Unable to retrieve medicine information.");
                return;
            }

            document.getElementById("quarantineMedicine").textContent =
                data.medicine_name;

            document.getElementById("quarantineBatch").textContent =
                data.batch_number;

            document.getElementById("quarantineQty").textContent =
                data.quantity;

            document.getElementById("quarantineModal").style.display = "flex";

        })
        .catch(error => {

            console.error(error);

            alert("Unable to load medicine information.");

        });
}


function closeQuarantineModal() {

    document.getElementById("quarantineModal").style.display = "none";

    quarantineMedicineId = null;
}

function confirmQuarantine() {

    if (!quarantineMedicineId) {
        return;
    }

    fetch("../actions/quarantine_medicine.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + encodeURIComponent(quarantineMedicineId)
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            closeQuarantineModal();

            location.reload();

        } else {

            alert(data.message || "Unable to quarantine medicine.");

        }

    })
    .catch(error => {

        console.error(error);

        alert("Something went wrong.");

    });
}

// ==========================
// RETURN TO STOCK
// ==========================

let returnStockMedicineId = null;


// ==========================
// OPEN RETURN TO STOCK MODAL
// ==========================

function returnToStock(id) {

    returnStockMedicineId = id;

    fetch("../actions/get_medicine.php?id=" + id)
        .then(response => response.json())
        .then(data => {

            if (!data.success) {

                alert("Unable to retrieve medicine information.");
                return;

            }

            document.getElementById("returnStockMedicine").textContent =
                data.medicine_name;

            document.getElementById("returnStockBatch").textContent =
                data.batch_number;

            document.getElementById("returnStockQty").textContent =
                data.quantity;

            document.getElementById("returnStockModal").style.display =
                "flex";

        })
        .catch(error => {

            console.error(error);

            alert("Unable to load medicine information.");

        });
}


// ==========================
// CLOSE RETURN TO STOCK MODAL
// ==========================

function closeReturnStockModal() {

    document.getElementById("returnStockModal").style.display =
        "none";

    returnStockMedicineId = null;
}


// ==========================
// CONFIRM RETURN TO STOCK
// ==========================

function confirmReturnToStock() {

    if (!returnStockMedicineId) {

        alert("Invalid medicine.");

        return;
    }

    fetch("../actions/return_to_stock.php", {
        method: "POST",

        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },

        body:
            "id=" +
            encodeURIComponent(returnStockMedicineId)
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            closeReturnStockModal();

            document.getElementById(
                "returnStockSuccessModal"
            ).style.display = "flex";

        } else {

            alert(
                data.message ||
                "Unable to return medicine to stock."
            );

        }

    })
    .catch(error => {

        console.error(error);

        alert(
            "Something went wrong while returning the medicine."
        );

    });
}


// ==========================
// CLOSE SUCCESS MODAL
// ==========================

function closeReturnStockSuccessModal() {

    document.getElementById(
        "returnStockSuccessModal"
    ).style.display = "none";

    location.reload();
}

// RESTOCK
function openRestock(id, medicineName) {

    const url =
        "add_medicine.php?mode=restock" +
        "&id=" + encodeURIComponent(id) +
        "&medicine=" + encodeURIComponent(medicineName);

    window.location.href = url;
}