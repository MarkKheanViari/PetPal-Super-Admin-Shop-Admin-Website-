document.addEventListener("DOMContentLoaded", function () {
  fetchAppointments();
});

function fetchAppointments() {
  fetch("http://192.168.1.3/backend/fetch_all_appointments.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayAppointments(data.appointments);
      } else {
        alert("No appointments found.");
      }
    })
    .catch((error) => console.error("Error fetching data:", error));
}

function displayAppointments(appointments) {
  const tableBody = document.getElementById("appointmentsTableBody");
  tableBody.innerHTML = ""; // Clear existing data

  appointments.forEach((appointment) => {
    if (appointment.service_type === "Grooming") {
      // For Grooming Page
      const row = `
                <tr>
                    <td>${appointment.name}</td>
                    <td>${appointment.pet_name} (${appointment.pet_breed})</td>
                    <td>${appointment.service_name}</td>
                    <td>${appointment.appointment_date}</td>
                    <td>₱${appointment.price}</td>
                    <td><span class="${getStatusClass(appointment.status)}">${
        appointment.status
      }</span></td>
                    <td>
                        <button onclick="updateStatus(${
                          appointment.id
                        }, 'Approved')">Approve</button>
                        <button onclick="updateStatus(${
                          appointment.id
                        }, 'Declined')">Decline</button>
                    </td>
                </tr>
            `;
      tableBody.innerHTML += row;
    }
  });
}

function getStatusClass(status) {
  switch (status) {
    case "Pending":
      return "status-pending";
    case "Approved":
      return "status-approved";
    case "Declined":
      return "status-declined";
    default:
      return "";
  }
}

function updateStatus(appointmentId, newStatus) {
  fetch("http://192.168.1.3/backend/update_appointment_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ appointment_id: appointmentId, status: newStatus }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Status updated successfully!");
        fetchAppointments(); // Refresh the table
      } else {
        alert("Error updating status.");
      }
    })
    .catch((error) => console.error("Error updating status:", error));
}
