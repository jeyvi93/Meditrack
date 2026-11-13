// ===============================
// TAB SWITCHING
// ===============================
function switchTab(event, tabId) {

    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });


    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });


    document.getElementById(tabId).classList.add('active');


    if(event){
        event.currentTarget.classList.add('active');
    }

}


// ===============================
// OPEN TAB FROM URL HASH
// ===============================
window.addEventListener('DOMContentLoaded', () => {

    const hash = window.location.hash.substring(1);


    if(hash){

        document.querySelectorAll('.tab-content').forEach(tab=>{
            tab.classList.remove('active');
        });


        document.querySelectorAll('.tab-btn').forEach(btn=>{

            btn.classList.remove('active');


            if(btn.getAttribute('onclick')?.includes(hash)){
                btn.classList.add('active');
            }

        });


        const target = document.getElementById(hash);

        if(target){
            target.classList.add('active');
        }

    }


});



// ===============================
// AJAX TABLE SEARCH
// ===============================

document.addEventListener("DOMContentLoaded", () => {

    const completedSearch =
        document.getElementById("completedSearch");

    const rejectedSearch =
        document.getElementById("rejectedSearch");


    // ===============================
    // COMPLETED SEARCH
    // ===============================

    if (completedSearch) {

        let completedTimeout;
        let completedController;

        completedSearch.addEventListener("input", function () {

            clearTimeout(completedTimeout);

            completedTimeout = setTimeout(async () => {

                const search =
                    completedSearch.value.trim();

                if (completedController) {
                    completedController.abort();
                }

                completedController =
                    new AbortController();

                const params =
                    new URLSearchParams();

                if (search !== "") {
                    params.set(
                        "completed_search",
                        search
                    );
                }

                params.set(
                    "completed_page",
                    "1"
                );

                params.set(
                    "ajax",
                    "completed"
                );


                try {

                    const response =
                        await fetch(
                            "appointments.php?" +
                            params.toString(),
                            {
                                signal:
                                    completedController.signal
                            }
                        );

                    const html =
                        await response.text();


                    const parser =
                        new DOMParser();

                    const doc =
                        parser.parseFromString(
                            html,
                            "text/html"
                        );


                    const newTbody =
                        doc.querySelector(
                            "#completed .appointment-table tbody"
                        );

                    const currentTbody =
                        document.querySelector(
                            "#completed .appointment-table tbody"
                        );


                    const newFooter =
                        doc.querySelector(
                            "#completed .table-footer"
                        );

                    const currentFooter =
                        document.querySelector(
                            "#completed .table-footer"
                        );


                    if (newTbody && currentTbody) {

                        currentTbody.innerHTML =
                            newTbody.innerHTML;

                    }


                    if (newFooter && currentFooter) {

                        currentFooter.innerHTML =
                            newFooter.innerHTML;

                    }


                    // KEEP SEARCH BOX FOCUSED

                    completedSearch.focus();

                    completedSearch.setSelectionRange(
                        completedSearch.value.length,
                        completedSearch.value.length
                    );


                } catch (error) {

                    if (error.name !== "AbortError") {
                        console.error(
                            "Completed search error:",
                            error
                        );
                    }

                }

            }, 300);

        });

    }


    // ===============================
    // REJECTED / CANCELLED SEARCH
    // ===============================

    if (rejectedSearch) {

        let rejectedTimeout;
        let rejectedController;

        rejectedSearch.addEventListener("input", function () {

            clearTimeout(rejectedTimeout);

            rejectedTimeout = setTimeout(async () => {

                const search =
                    rejectedSearch.value.trim();

                if (rejectedController) {
                    rejectedController.abort();
                }

                rejectedController =
                    new AbortController();

                const params =
                    new URLSearchParams();

                if (search !== "") {
                    params.set(
                        "rejected_search",
                        search
                    );
                }

                params.set(
                    "rejected_page",
                    "1"
                );

                params.set(
                    "ajax",
                    "rejected"
                );


                try {

                    const response =
                        await fetch(
                            "appointments.php?" +
                            params.toString(),
                            {
                                signal:
                                    rejectedController.signal
                            }
                        );

                    const html =
                        await response.text();


                    const parser =
                        new DOMParser();

                    const doc =
                        parser.parseFromString(
                            html,
                            "text/html"
                        );


                    const newTbody =
                        doc.querySelector(
                            "#rejected .appointment-table tbody"
                        );

                    const currentTbody =
                        document.querySelector(
                            "#rejected .appointment-table tbody"
                        );


                    const newFooter =
                        doc.querySelector(
                            "#rejected .table-footer"
                        );

                    const currentFooter =
                        document.querySelector(
                            "#rejected .table-footer"
                        );


                    if (newTbody && currentTbody) {

                        currentTbody.innerHTML =
                            newTbody.innerHTML;

                    }


                    if (newFooter && currentFooter) {

                        currentFooter.innerHTML =
                            newFooter.innerHTML;

                    }


                    // KEEP SEARCH BOX FOCUSED

                    rejectedSearch.focus();

                    rejectedSearch.setSelectionRange(
                        rejectedSearch.value.length,
                        rejectedSearch.value.length
                    );


                } catch (error) {

                    if (error.name !== "AbortError") {
                        console.error(
                            "Rejected search error:",
                            error
                        );
                    }

                }

            }, 300);

        });

    }

});



// ===============================
// RESCHEDULE MODAL
// ===============================

const resDate = document.getElementById("resDate");
const resTime = document.getElementById("resTime");

if (resDate && typeof flatpickr !== "undefined") {

    flatpickr(resDate, {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        clickOpens: true,
        minDate: "today",

        disable: [
            function(date) {
                return date.getDay() === 0;
            }
        ],

        onReady: function(selectedDates, dateStr, instance) {
            instance.altInput.placeholder = "dd/mm/yyyy";
            instance.altInput.readOnly = true;

            const wrapper = instance.altInput.parentElement;
            const icon = wrapper.querySelector(".fa-calendar-alt");

            if (icon) {
                icon.style.pointerEvents = "auto";
                icon.style.cursor = "pointer";

                icon.addEventListener("click", function() {
                    instance.open();
                });
            }
        }
    });
}

function updateRescheduleTimeSlots() {

    if (!resDate || !resTime) return;

    const selectedDate = resDate.value;

    // Get current Philippine date and time
    const manilaNow = new Date().toLocaleString("en-US", {
        timeZone: "Asia/Manila"
    });

    const currentDateTime = new Date(manilaNow);

    const today = [
        currentDateTime.getFullYear(),
        String(currentDateTime.getMonth() + 1).padStart(2, "0"),
        String(currentDateTime.getDate()).padStart(2, "0")
    ].join("-");

    const currentTime = [
        String(currentDateTime.getHours()).padStart(2, "0"),
        String(currentDateTime.getMinutes()).padStart(2, "0"),
        "00"
    ].join(":");

    Array.from(resTime.options).forEach(option => {

        if (!option.value) return;

        const isPast =
            selectedDate === today &&
            option.value <= currentTime;

        option.disabled = isPast;

        if (isPast && option.selected) {
            resTime.value = "";
        }

    });

}

function openRescheduleModal(name, id) {

    document.getElementById("rescheduleModal").style.display = "flex";

    document.getElementById("resPatientName").value = name;

    document.getElementById("resAppointmentId").value = id;

    updateRescheduleTimeSlots();

}

function closeRescheduleModal() {

    document.getElementById("rescheduleModal").style.display = "none";

}

// Update whenever the selected date changes
if (resDate) {
    resDate.addEventListener("change", updateRescheduleTimeSlots);
}

// Recheck when returning to the page
window.addEventListener("focus", updateRescheduleTimeSlots);

// ===============================
// REJECT MODAL
// ===============================
function openRejectModal(name,id){


    document.getElementById("rejectModal").style.display="flex";


    document.getElementById("rejPatientName").value=name;


    document.getElementById("rejAppointmentId").value=id;


}



function closeRejectModal(){

    document.getElementById("rejectModal").style.display="none";

}



// ===============================
// CANCEL MODAL
// ===============================
function openCancelModal(name,id){


    document.getElementById("cancelModal").style.display="flex";


    document.getElementById("canPatientName").value=name;


    document.getElementById("canAppointmentId").value=id;


}

// ===============================
// CLICK OUTSIDE TO CLOSE
// ===============================
window.addEventListener("click",function(e){


    const modals = [
        "rejectModal",
        "rescheduleModal",
        "cancelModal",
        "appointmentDetailsModal"
    ];


    modals.forEach(id=>{


        let modal=document.getElementById(id);


        if(e.target===modal){

            if (id === "appointmentDetailsModal") {
                closeAppointmentDetails();
            } else {
                modal.style.display = "none";
            }

        }


    });


});



// ===============================
// MESSAGE MODAL
// ===============================
function closeMessageModal(){

    let modal=document.getElementById("messageModal");


    if(modal){

        modal.style.display="none";

    }

}


// ===============================
// CONSULTATION DETAILS MODAL
// ===============================

async function openAppointmentDetails(appointmentId) {

    const modal = document.getElementById("appointmentDetailsModal");
    const content = document.getElementById("appointmentDetailsContent");

    modal.classList.remove("hidden");
    content.replaceChildren();

    const loading = document.createElement("p");
    loading.textContent = "Loading consultation details...";
    content.appendChild(loading);

    try {

        const response = await fetch(
            "../actions/get_clinic_appointment_details.php?appointment_id=" +
            encodeURIComponent(appointmentId)
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to load consultation details.");
        }

        const consultation = data.consultation;

        content.replaceChildren();

        const details = [
            ["Consultation Date", formatAppointmentDetailDate(consultation.date)],
            ["Time", formatAppointmentDetailTime(consultation.time)],
            ["Complaint", consultation.complaint],
            ["Treatment Given", consultation.treatment_given],
            ["Medicine Dispensed", consultation.medicines_dispensed || "No medicine dispensed"],
            ["Remarks", consultation.remarks]
        ];

        details.forEach(([label, value]) => {

            const item = document.createElement("div");
            item.className = "appointment-detail-item";

            const heading = document.createElement("strong");
            heading.textContent = label;

            const text = document.createElement("p");
            text.textContent = value || "Not Recorded";

            item.append(heading, text);
            content.appendChild(item);

        });

    } catch (error) {

        console.error("Appointment details error:", error);

        content.replaceChildren();

        const errorMessage = document.createElement("p");
        errorMessage.textContent =
            error.message || "Unable to load consultation details. Please try again.";

        content.appendChild(errorMessage);

    }

}

function formatAppointmentDetailDate(date) {

    if (!date) return "Not Recorded";

    const parsedDate = new Date(date + "T00:00:00");

    return parsedDate.toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric"
    });

}

function formatAppointmentDetailTime(time) {

    if (!time) return "Not Recorded";

    const [hours, minutes] = time.split(":");
    const parsedTime = new Date();

    parsedTime.setHours(Number(hours), Number(minutes), 0, 0);

    return parsedTime.toLocaleTimeString("en-PH", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true
    });

}

function closeAppointmentDetails() {

    document.getElementById("appointmentDetailsModal")
        .classList.add("hidden");

}
