{/* <h2>🌡 Raspberry Pi CPU Temperature:</h2>
<p id="temp">Loading...</p> */}


async function fetchTemp() {
  let response = await fetch(`http://${RASPI_IP}:5000/temperature`); // Pi's IP
  let data = await response.json();
  document.getElementById("temp").innerText = data.temperature.replace("'C", " ℃");
}

setInterval(fetchTemp, 5000);
fetchTemp();

