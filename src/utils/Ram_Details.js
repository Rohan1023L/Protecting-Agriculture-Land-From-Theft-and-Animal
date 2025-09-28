async function fetchRAM() {
  try {
    const response = await fetch(`http://${RASPI_IP}:5000/ram`); // Replace with your Pi's IP
    const data = await response.json();

    document.getElementById("ram-total").innerText = data.total_GB;
    document.getElementById("ram-free").innerText = data.free_GB + " GB";
    document.getElementById("ram-percent").innerText = data.percent_used + " %";
  } catch (error) {
    console.error("Error fetching RAM usage:", error);
  }
}

// Fetch every 5 seconds
setInterval(fetchRAM, 5000);
fetchRAM();
