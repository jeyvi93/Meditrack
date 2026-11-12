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
    if (!activePane || activePane.id === "disposed") return;

    const tbody = activePane.querySelector("tbody");
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll("tr")).filter(row =>
        !row.classList.contains("js-search-empty") &&
        row.querySelector("td")
    );

    let visibleCount = 0;

    rows.forEach(row => {
        // Keep the original PHP empty-state row separate from medicine rows
        const isOriginalEmptyRow =
            row.innerText.toLowerCase().includes("no medicines found") ||
            row.innerText.toLowerCase().includes("no expired medicines found") ||
            row.innerText.toLowerCase().includes("no quarantined medicines found");

        if (isOriginalEmptyRow) {
            row.style.display = "none";
            return;
        }

        const rowText = row.innerText.toLowerCase();

        const status = (
            row.dataset.status ||
            row.querySelector(".status-pill")?.innerText ||
            ""
        ).toLowerCase().trim();

        const matchesSearch = !searchText || rowText.includes(searchText);
        const matchesStatus =
            activePane.id !== "all" ||
            !selectedStatus ||
            status === selectedStatus;

        row.style.display = matchesSearch && matchesStatus ? "" : "none";

        if (matchesSearch && matchesStatus) {
            visibleCount++;
        }
    });

    // Remove a previous JavaScript-generated empty row
    tbody.querySelector(".js-search-empty")?.remove();

    // Show the original empty state if there are no medicine records
    if (rows.length === 1 && (
        rows[0].innerText.toLowerCase().includes("no medicines found") ||
        rows[0].innerText.toLowerCase().includes("no expired medicines found") ||
        rows[0].innerText.toLowerCase().includes("no quarantined medicines found")
    )) {
        rows[0].style.display = "";
        return;
    }

    // Show a message when search/filter returns no matches
    if (visibleCount === 0) {
        const emptyRow = document.createElement("tr");
        emptyRow.className = "js-search-empty";

        const emptyCell = document.createElement("td");
        emptyCell.colSpan = activePane.querySelectorAll("thead th").length || 1;
        emptyCell.style.textAlign = "center";

        if (activePane.id === "expired") {
            emptyCell.textContent = "No matching expired medicines found.";
        } else if (activePane.id === "quarantine") {
            emptyCell.textContent = "No matching quarantined medicines found.";
        } else {
            emptyCell.textContent = "No matching medicines found.";
        }

        emptyRow.appendChild(emptyCell);
        tbody.appendChild(emptyRow);
    }
}

// ==========================
// SERVER-SIDE DISPOSED SEARCH
// ==========================

let disposedSearchTimer;
let disposedSearchController = null;

medicineSearch.addEventListener("input", function () {

    // Only use server-side search when Disposed Records is active
    const disposedPane = document.getElementById("disposed");

    if (!disposedPane.classList.contains("active")) {
        filterMedicines();
        return;
    }

    clearTimeout(disposedSearchTimer);

    disposedSearchTimer = setTimeout(() => {

        const search = medicineSearch.value.trim();

        // Cancel previous request
        if (disposedSearchController) {
            disposedSearchController.abort();
        }

        disposedSearchController = new AbortController();

        const url =
            "medicine.php?disposed_page=1" +
            "&disposed_search=" +
            encodeURIComponent(search) +
            "#disposed";

        fetch(url, {
            signal: disposedSearchController.signal
        })
        .then(response => response.text())
        .then(html => {

            const parser = new DOMParser();
            const doc = parser.parseFromString(
                html,
                "text/html"
            );

            const newTable =
                doc.querySelector("#disposed .inventory-table");

            const newFooter =
                doc.querySelector("#disposed .table-footer");

            const currentTable =
                document.querySelector("#disposed .inventory-table");

            const currentFooter =
                document.querySelector("#disposed .table-footer");

            if (newTable && currentTable) {
                currentTable.innerHTML =
                    newTable.innerHTML;
            }

            if (newFooter && currentFooter) {
                currentFooter.innerHTML =
                    newFooter.innerHTML;
            }

            // Update browser URL without reloading
            window.history.replaceState(
                {},
                "",
                url
            );

            // Keep search box focused
            medicineSearch.focus();

            // Put cursor at the end
            medicineSearch.setSelectionRange(
                medicineSearch.value.length,
                medicineSearch.value.length
            );

        })
        .catch(error => {

            if (error.name !== "AbortError") {
                console.error(
                    "Disposed search error:",
                    error
                );
            }

        });

    }, 300);

});

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

            openSuccessModal(
                "Medicine Disposed!",
                data.message || "Medicine disposed successfully."
            );

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
    const allRows = document.querySelectorAll("#all tbody tr");
    const expiredRows = document.querySelectorAll("#expired tbody tr");

    let total = 0;
    let expired = 0;
    let low = 0;
    let near = 0;

    const medicineStocks = new Map();
    const list = document.getElementById("reportList");

    list.innerHTML = "";

    // ALL MEDICINES
    allRows.forEach(row => {
        const name = row.querySelector("td strong")?.innerText.trim();

        if (!name || row.classList.contains("js-search-empty")) return;

        const cells = row.querySelectorAll("td");

        // Column 1 = Total Quantity
        const quantity = parseInt(cells[1]?.innerText.trim(), 10) || 0;

        const status = row.dataset.status || "";

        // Count actual stock units
        total += quantity;

        if (status === "Low Stock") low += quantity;
        if (status === "Near Expiry") near += quantity;

        medicineStocks.set(name, quantity);
    });

    // EXPIRED MEDICINES
    expiredRows.forEach(row => {
        const name = row.querySelector("td strong")?.innerText.trim();

        if (!name || row.classList.contains("js-search-empty")) return;

        const cells = row.querySelectorAll("td");

        // Column 2 = Batch Quantity
        const quantity = parseInt(cells[2]?.innerText.trim(), 10) || 0;

        expired += quantity;
    });

    // MEDICINE STOCK BREAKDOWN
    medicineStocks.forEach((stock, name) => {
        const li = document.createElement("li");
        li.innerText = `${name} — ${stock} units`;
        list.appendChild(li);
    });

    document.getElementById("repTotal").innerText = total;
    document.getElementById("repExpired").innerText = expired;
    document.getElementById("repLow").innerText = low;
    document.getElementById("repNear").innerText = near;
}

// ===============================
// VIEW MEDICINE BATCHES
// ===============================

function openMedicineBatches(medicineName) {

    const modal = document.getElementById("medicineBatchesModal");
    const title = document.getElementById("batchesMedicineName");
    const tableBody = document.getElementById("medicineBatchesTableBody");

    title.textContent = medicineName;
    tableBody.innerHTML = `
        <tr>
            <td colspan="7" style="text-align:center;">
                Loading batches...
            </td>
        </tr>
    `;

    modal.style.display = "flex";

    fetch("../actions/get_medicine_batches.php?medicine_name=" + encodeURIComponent(medicineName))
        .then(response => response.json())
        .then(data => {

            tableBody.innerHTML = "";

            if (!data.success) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align:center;">
                            ${data.message || "Unable to load batches."}
                        </td>
                    </tr>
                `;
                return;
            }

            if (data.batches.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align:center;">
                            No batches found.
                        </td>
                    </tr>
                `;
                return;
            }

            data.batches.forEach(batch => {

                const row = document.createElement("tr");

                const values = [
                    batch.batch_number || "N/A",
                    batch.quantity,
                    batch.date_acquired || "N/A",
                    batch.expiry_date || "N/A",
                    batch.supplier || "N/A"
                ];

                values.forEach(value => {
                    const cell = document.createElement("td");
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                const statusCell = document.createElement("td");
                const statusPill = document.createElement("span");

                statusPill.classList.add("status-pill");

                if (batch.status === "In Stock") {
                    statusPill.classList.add("stat-in-stock");
                } else if (batch.status === "Low Stock") {
                    statusPill.classList.add("stat-low-stock");
                } else if (batch.status === "Near Expiry") {
                    statusPill.classList.add("stat-near-expiry");
                }

                statusPill.textContent = batch.status;
                statusCell.appendChild(statusPill);
                row.appendChild(statusCell);

                
                const actionCell = document.createElement("td");
                actionCell.classList.add("action-cell");

                // EDIT BUTTON
                const editButton = document.createElement("button");
                editButton.type = "button";
                editButton.className = "btn-action primary";
                editButton.title = "Edit Medicine";
                editButton.innerHTML = '<i class="bx bx-edit"></i>';

                editButton.addEventListener("click", function () {
                    closeMedicineBatchesModal();
                    openEditModal(batch.id);
                });

                actionCell.appendChild(editButton);


                // STATUS-BASED ACTIONS

                if (batch.status === "Near Expiry") {

                    // DISPOSE NEAR EXPIRY
                    const disposeButton = document.createElement("button");
                    disposeButton.type = "button";
                    disposeButton.className = "btn-dispose";
                    disposeButton.title = "Dispose Near Expiry Medicine";
                    disposeButton.innerHTML = '<i class="bx bx-trash"></i>';

                    disposeButton.addEventListener("click", function () {
                        closeMedicineBatchesModal();
                        openNearExpiryDispose(batch.id);
                    });

                    actionCell.appendChild(disposeButton);

                } else if (batch.status === "Low Stock") {

                    // RESTOCK
                    const restockButton = document.createElement("button");
                    restockButton.type = "button";
                    restockButton.className = "btn-action info";
                    restockButton.title = "Restock Medicine";
                    restockButton.innerHTML = '<i class="bx bx-package"></i>';

                    restockButton.addEventListener("click", function () {
                        openRestock(batch.id, batch.medicine_name || document.getElementById("batchesMedicineName").textContent);
                    });

                    actionCell.appendChild(restockButton);

                } else {

                    // QUARANTINE
                    const quarantineButton = document.createElement("button");
                    quarantineButton.type = "button";
                    quarantineButton.className = "btn-action warning";
                    quarantineButton.title = "Quarantine Medicine";
                    quarantineButton.innerHTML = '<i class="bx bx-shield-quarter"></i>';

                    quarantineButton.addEventListener("click", function () {
                        closeMedicineBatchesModal();
                        openQuarantineModal(batch.id);
                    });

                    actionCell.appendChild(quarantineButton);
                }

                row.appendChild(actionCell);


                tableBody.appendChild(row);
            });

        })
        .catch(error => {
            console.error("Error loading medicine batches:", error);

            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center;">
                        An error occurred while loading batches.
                    </td>
                </tr>
            `;
        });
}


// ===============================
// CLOSE MEDICINE BATCHES MODAL
// ===============================

function closeMedicineBatchesModal() {
    document.getElementById("medicineBatchesModal").style.display = "none";
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

        openSuccessModal(
            "Medicine Updated!",
            data.message || "Medicine updated successfully."
        );

    })
    .catch(error => {

        console.error(error);

        alert("Unable to update medicine.");

    });

});

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

    openSuccessModal(
        "Medicine Quarantined!",
        data.message || "Medicine moved to quarantine successfully."
            );

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

            openSuccessModal(
                "Medicine Quarantined!",
                data.message || "Medicine moved to quarantine successfully."
            );

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

            openSuccessModal(
                "Medicine Returned!",
                data.message || "Medicine returned to stock successfully."
            );
        } else {
            alert(data.message || "Unable to return medicine to stock.");
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
// REUSABLE SUCCESS MODAL
// ==========================

function openSuccessModal(title, message) {
    document.getElementById("successModalTitle").textContent = title;
    document.getElementById("successModalMessage").textContent = message;
    document.getElementById("successModal").style.display = "flex";
}

function closeSuccessModal() {
    document.getElementById("successModal").style.display = "none";
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

// ==========================
// DISPOSED RECORDS PAGINATION
// ==========================

document.addEventListener("DOMContentLoaded", function () {

    // If URL contains #disposed,
    // automatically open the Disposed Records tab
    if (window.location.hash === "#disposed") {

        const disposedTab = document.querySelector(
            '.tab-link[onclick*="disposed"]'
        );

        if (disposedTab) {

            disposedTab.click();

        }
    }

});

// REMOVE EMOJIS FROM EDIT MEDICINE MODAL
document.addEventListener("input", function (event) {

    const input = event.target;

    if (!input.closest("#editModal")) {
        return;
    }

    if (!input.matches('input[type="text"], textarea')) {
        return;
    }

    input.value = input.value.replace(
        /[\p{Extended_Pictographic}\p{Emoji_Presentation}\p{Emoji_Modifier}\p{Regional_Indicator}\uFE0F\u200D\u20E3]/gu,
        ""
    );

});