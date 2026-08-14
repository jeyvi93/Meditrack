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
// TABLE SEARCH
// ===============================
document.querySelectorAll(".table-search").forEach(search => {


    search.addEventListener("keyup", function(){


        let keyword = this.value.toLowerCase();


        let table = this.closest(".tab-content")
                        .querySelector("tbody");


        let rows = table.querySelectorAll("tr");


        rows.forEach(row => {


            let text = row.textContent.toLowerCase();


            if(text.includes(keyword)){

                row.style.display = "";

            }else{

                row.style.display = "none";

            }


        });


    });


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