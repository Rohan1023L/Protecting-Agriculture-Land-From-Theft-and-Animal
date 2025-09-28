async function fetchStorage() {
  try {
    const response = await fetch(`http://${RASPI_IP}:5000/storage`); // Pi's IP and Flask port
    const data = await response.json();

    document.getElementById("total").innerText = data.total_GB;
    document.getElementById("sd-free").innerText = data.free_GB;
  } catch (error) {
    console.error("Error fetching storage info:", error);
  }
}

// Fetch every 5 seconds
setInterval(fetchStorage, 5000);
fetchStorage();