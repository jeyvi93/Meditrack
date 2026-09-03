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
function openRescheduleModal(name,id){

    document.getElementById("rescheduleModal").style.display="flex";

    document.getElementById("resPatientName").value = name;

    document.getElementById("resAppointmentId").value = id;

}


function closeRescheduleModal(){

    document.getElementById("rescheduleModal").style.display="none";

}



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



function closeCancelModal(){

    document.getElementById("cancelModal").style.display="none";

}



// ===============================
// CLICK OUTSIDE TO CLOSE
// ===============================
window.addEventListener("click",function(e){


    const modals=[
        "rejectModal",
        "rescheduleModal",
        "cancelModal"
    ];


    modals.forEach(id=>{


        let modal=document.getElementById(id);


        if(e.target===modal){

            modal.style.display="none";

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