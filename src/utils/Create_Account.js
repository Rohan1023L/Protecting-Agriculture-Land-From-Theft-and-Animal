// Import Firebase modules correctly (version 11.6.0)
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js";
import { getDatabase, ref, push, set } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-database.js";

// Firebase Configuration
const firebaseConfig = {
  apiKey: "AIzaSyCi6hy2gHVzVJ61jPO_jxHu2T8pCP8eou0",
  authDomain: "testdb-63b82.firebaseapp.com",
  databaseURL: "https://testdb-63b82-default-rtdb.firebaseio.com",
  projectId: "testdb-63b82",
  storageBucket: "testdb-63b82.appspot.com",
  messagingSenderId: "616616625117",
  appId: "1:616616625117:web:4c296b2fa04d33aa61ec34"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const database = getDatabase(app);
const usersRef = ref(database, "users/");

// Function to Get Input Values
const getElementVal = (id) => document.getElementById(id).value;

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("testDB").addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent page reload

    let name = getElementVal("name");
    let gmail = getElementVal("gmail");
    let password = getElementVal("password");

    if (name === "" || gmail === "" || password === "") {
      alert("All fields are required!");
      return;
    }

    // Push data to Firebase
    const newUserRef = push(usersRef);
    set(newUserRef, {
      name: name,
      email: gmail,
      password: password
    })
    .then(() => {
      alert("Account Created Successfully!");
      document.getElementById("testDB").reset();
    })
    .catch((error) => {
      console.error("Error saving data:", error);
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("testDB");

  form.addEventListener("submit", (event) => {
    event.preventDefault(); // Stop form from refreshing the page

    // Get form values
    const name = document.getElementById("name").value;
    const gmail = document.getElementById("gmail").value;
    const password = document.getElementById("password").value;

    // ✅ Save user name to localStorage
    localStorage.setItem("userName", name);

    // 👉 After saving, redirect to chatbot_details.html
    window.location.href = "user_details.html";
  });
});
