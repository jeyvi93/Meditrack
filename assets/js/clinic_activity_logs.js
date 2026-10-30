document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("activitySearch");
    const roleFilter = document.getElementById("roleFilter");
    const dateFilter = document.getElementById("dateFilter");

    let searchTimeout;
    let searchController;

    // =========================
    // AJAX LIVE SEARCH + FILTERS
    // =========================

    async function refreshActivityTable() {

        // Cancel previous request if still running
        if (searchController) {
            searchController.abort();
        }

        searchController = new AbortController();

        const params = new URLSearchParams();

        const search = searchInput
            ? searchInput.value.trim()
            : "";

        const role = roleFilter
            ? roleFilter.value
            : "";

        const date = dateFilter
            ? dateFilter.value
            : "";

        if (search !== "") {
            params.set("search", search);
        }

        if (role !== "") {
            params.set("role", role);
        }

        if (date !== "") {
            params.set("date", date);
        }

        // Reset to first page whenever search or filters change
        params.set("page", "1");

        try {

            const response = await fetch(
                "clinic_activity_logs.php?" + params.toString(),
                {
                    signal: searchController.signal
                }
            );

            if (!response.ok) {
                throw new Error("Failed to fetch clinic activity logs.");
            }

            const html = await response.text();

            const parser = new DOMParser();

            const doc = parser.parseFromString(
                html,
                "text/html"
            );

            // =========================
            // UPDATE TABLE
            // =========================

            const newTable = doc.querySelector(".logs-table");
            const currentTable = document.querySelector(".logs-table");

            if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
            }

            // =========================
            // UPDATE PAGINATION
            // =========================

            const newFooter = doc.querySelector(".table-footer");
            const currentFooter = document.querySelector(".table-footer");

            if (newFooter && currentFooter) {
                currentFooter.innerHTML = newFooter.innerHTML;
            }

            // Keep filtered URL in the address bar
            window.history.replaceState(
                {},
                "",
                "clinic_activity_logs.php?" + params.toString()
            );

            // Keep search focused
            if (searchInput) {
                searchInput.focus();

                searchInput.setSelectionRange(
                    searchInput.value.length,
                    searchInput.value.length
                );
            }

        } catch (error) {

            if (error.name !== "AbortError") {
                console.error(
                    "Clinic activity search error:",
                    error
                );
            }

        }
    }

    // =========================
    // SEARCH INPUT
    // =========================

    if (searchInput) {

        searchInput.addEventListener("input", function () {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function () {
                refreshActivityTable();
            }, 300);

        });

    }

    // =========================
    // PERSONNEL FILTER
    // =========================

    if (roleFilter) {

        roleFilter.addEventListener("change", function () {
            refreshActivityTable();
        });

    }

    // =========================
    // DATE FILTER
    // =========================

    if (dateFilter) {

        dateFilter.addEventListener("change", function () {
            refreshActivityTable();
        });

    }

});