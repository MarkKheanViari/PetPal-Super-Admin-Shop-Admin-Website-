document.addEventListener("DOMContentLoaded", function () {
    fetch("http://192.168.137.239/backend/fetch_veterinary_appointments.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAppointments(data.appointments, "veterinaryAppointmentsContainer");
            } else {
                document.getElementById("veterinaryAppointmentsContainer").innerHTML = "<p>No veterinary appointments found.</p>";
            }
        })
        .catch(error => console.error("Error fetching veterinary appointments:", error));
});

function displayAppointments(appointments, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = ""; // Clear previous data

    appointments.forEach(appointment => {
        const appointmentDiv = document.createElement("div");
        appointmentDiv.classList.add("appointment-card");

        // Format date
        const formattedDate = appointment.appointment_date !== "N/A" ? appointment.appointment_date : "Not Set";

        // ✅ Add color-coded status badge
        let statusBadge = `<span class="status pending">Pending</span>`;
        if (appointment.status === "Approved") {
            statusBadge = `<span class="status approved">Approved</span>`;
        } else if (appointment.status === "Declined") {
            statusBadge = `<span class="status declined">Declined</span>`;
        }
        
        appointmentDiv.innerHTML = `
            <div class="appointment-item">
            <div class="appointment-image"></div>
            <div class="appointment-details">
                <h3>${appointment.checkup_type}</h3>
                <p><strong>Customer:</strong> ${appointment.name}</p>
                <p><strong>Pet:</strong> ${appointment.pet_name} (${appointment.pet_breed})</p>
                <p><strong>Date:</strong> ${formattedDate}</p>
            </div>
            ${statusBadge}
            <button class="view-details-btn" onclick='showVetDetails(${JSON.stringify(appointment)})'>View Details</button>
        
            </div>`;
        container.appendChild(appointmentDiv);
    });
}

// Function to show the popup (modal)
function showVetDetails(appointment) {
const updateField = (id, value) => {
const element = document.getElementById(id);
if (element) {
    element.innerText = value ? value : "N/A"; // If value is null, set "N/A"
} else {
    console.error(`❌ Element with ID '${id}' not found.`);
}
};

updateField("vetModalCustomerName", appointment.name);
updateField("vetModalCustomerAddress", appointment.address);
updateField("vetModalCustomerPhone", appointment.phone_number);
updateField("vetModalPetName", appointment.pet_name);
updateField("vetModalPetBreed", appointment.pet_breed);
updateField("vetModalCheckupType", appointment.checkup_type);
updateField("vetModalNotes", appointment.notes);
updateField("vetModalDate", appointment.appointment_date);
updateField("vetModalStatus", appointment.status);

document.getElementById("vetAppointmentModal").setAttribute("data-id", appointment.id);
document.getElementById("vetAppointmentModal").style.display = "block";
}

window.onload = function() {
    document.getElementById("vetAppointmentModal").style.display = "none";
};


// ✅ Function to approve a veterinary appointment
function approveVeterinaryAppointment() {
    const appointmentId = document.getElementById("vetAppointmentModal").getAttribute("data-id");

    fetch("http://192.168.137.239/backend/approve_veterinary_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ appointment_id: appointmentId, status: "Approved" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Appointment Approved!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

// ✅ Function to decline a veterinary appointment
function declineVeterinaryAppointment() {
    const appointmentId = document.getElementById("vetAppointmentModal").getAttribute("data-id");

    fetch("http://192.168.137.239/backend/approve_veterinary_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ appointment_id: appointmentId, status: "Declined" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Appointment Declined.");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

function closeVetModal() {
const modal = document.getElementById("vetAppointmentModal");
if (modal) {
modal.style.display = "none";
} else {
console.error("❌ Modal element with ID 'vetAppointmentModal' not found.");
}
}

const modal = document.querySelector(".modal");
const openModalBtns = document.querySelectorAll(".view-details-btn"); // Change this selector based on your buttons
const closeModalBtns = document.querySelectorAll(".cancel-btn, .decline-btn, .approve-btn"); // Include all possible close buttons

// Function to open the modal
function openModal() {
  modal.style.display = "flex";
  document.body.classList.add("modal-open"); // Prevent scrolling
}

// Function to close the modal
function closeModal() {
  modal.style.display = "none";
  document.body.classList.remove("modal-open"); // Allow scrolling again
}

// Attach event listeners to open modal buttons
openModalBtns.forEach(btn => {
  btn.addEventListener("click", openModal);
});

// Attach event listeners to close modal buttons
closeModalBtns.forEach(btn => {
  btn.addEventListener("click", closeModal);
});

// Close modal if clicking outside modal content
modal.addEventListener("click", (event) => {
  if (event.target === modal) {
    closeModal();
  }
});
