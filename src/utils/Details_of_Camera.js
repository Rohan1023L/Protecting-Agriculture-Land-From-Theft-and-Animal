document.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch(`http://${window.RASPI_IP}:5000/camera_status`);
    const data = await res.json();
    document.getElementById("camera-status").innerHTML = data.connected ? `<i class="fa-solid fa-wifi" style="font-size:14px;color:#a8e6a3;"></i>` : `<i class="bi bi-wifi-off" style="font-size:16px;color:#ff7b7b;"></i>`;
  } catch (e) {
    document.getElementById("camera-status").innerText = "Not Connected";
    console.error("Error fetching camera status:", e);
  }
});