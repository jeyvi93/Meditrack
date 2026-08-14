function openTab(evt, tabName) {
    const panes = document.querySelectorAll(".tab-pane");
    const tabs = document.querySelectorAll(".tab-link");

    panes.forEach(p => p.classList.remove("active"));
    tabs.forEach(t => t.classList.remove("active"));

    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");

    const filter = document.getElementById("filterGroup");

    // 👇 ONLY show filter in "all"
    if (tabName === "all") {
        filter.style.display = "flex";
    } else {
        filter.style.display = "none";
    }
}

// Filter

const statusFilter = document.getElementById("statusFilter");

statusFilter.addEventListener("change", function () {
    const selectedStatus = this.value;

    const allRows = document.querySelectorAll("#all tbody tr");

    allRows.forEach(row => {
        const status = row.dataset.status;

        if (!selectedStatus || status === selectedStatus) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});


function openEditModal() {
    document.getElementById("editModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

// DISPOSE

const disposeModal =
    document.getElementById('disposeModal');

function closeDisposeModal() {
    disposeModal.style.display = 'none';
}

const reasonGroup =
    document.getElementById('disposeReasonGroup');

const fixedReason =
    document.getElementById('disposeFixedReasonContainer');

function openNearExpiryDispose() {

    reasonGroup.style.display = 'none';

    fixedReason.style.display = 'block';

    document.getElementById(
        'disposeModalTitle'
    ).textContent = 'Dispose Near-Expiry Batch';

    disposeModal.style.display = 'block';
}

function openQuarantineDispose() {

    reasonGroup.style.display = 'block';

    fixedReason.style.display = 'none';

    document.getElementById(
        'disposeModalTitle'
    ).textContent = 'Dispose Quarantined Batch';

    disposeModal.style.display = 'block';
}

function openExpiredDispose() {

    reasonGroup.style.display = 'none';

    fixedReason.style.display = 'block';

    document.getElementById(
        'disposeFixedReason'
    ).textContent = 'Expired';

    document.getElementById(
        'disposeModalTitle'
    ).textContent = 'Dispose Expired Batch';

    disposeModal.style.display = 'block';
}

//ALERTS
const alertContainer = document.getElementById("alertContainer");

function showInventoryAlert(name, type) {
    const alert = document.createElement("div");
    alert.classList.add("inv-alert", type);

    let label = "";

    if (type === "expired") label = "EXPIRED";
    if (type === "low") label = "LOW STOCK";
    if (type === "near") label = "NEAR EXPIRY";

    alert.innerText = `${name} is ${label}`;

    alertContainer.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 4000);
}

function scanInventoryAlerts() {

    const rows = document.querySelectorAll("#all tbody tr");

    rows.forEach(row => {

        const name =
            row.querySelector("td strong")?.innerText;

        const status =
            row.dataset.status;

        if (status === "Expired") {
            showInventoryAlert(name, "expired");
        }

        if (status === "Low Stock") {
            showInventoryAlert(name, "low");
        }

        if (status === "Near Expiry") {
            showInventoryAlert(name, "near");
        }
    });
}

// run on load
window.addEventListener("load", scanInventoryAlerts);

//REPORT MODAL

function openReportModal() {
    generateInventoryReport();
    document.getElementById("reportModal").style.display = "block";
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