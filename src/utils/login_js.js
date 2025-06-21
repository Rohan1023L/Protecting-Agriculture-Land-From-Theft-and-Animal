import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js";
import {
  getDatabase,
  ref,
  get,
} from "https://www.gstatic.com/firebasejs/11.6.0/firebase-database.js";

const firebaseConfig = {
  apiKey: "AIzaSyCi6hy2gHVzVJ61jPO_jxHu2T8pCP8eou0",
  authDomain: "testdb-63b82.firebaseapp.com",
  databaseURL: "https://testdb-63b82-default-rtdb.firebaseio.com",
  projectId: "testdb-63b82",
  storageBucket: "testdb-63b82.appspot.com",
  messagingSenderId: "616616625117",
  appId: "1:616616625117:web:4c296b2fa04d33aa61ec34",
};

const app = initializeApp(firebaseConfig);
const database = getDatabase(app);
const usersRef = ref(database, "users");

const getElementVal = (id) => document.getElementById(id).value;

document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("loginForm")
    .addEventListener("submit", async function (event) {
      event.preventDefault();

      let email = getElementVal("email");
      let password = getElementVal("password");
      let messagePrint = document.getElementById("massage_print");

      if (email === "" || password === "") {
        alert("Please enter both Email and Password.");
        return;
      }

      try {
        const snapshot = await get(usersRef);
        if (snapshot.exists()) {
          let users = snapshot.val();
          let userFound = false;
          let userName = "";

          Object.values(users).forEach((user) => {
            if (user.email === email && user.password === password) {
              userFound = true;
              userName = user.name; // Get correct user name
            }
          });

          if (userFound) {
            localStorage.setItem("userName", userName); // Store correct user name
            messagePrint.innerHTML = `<article>Login Successful!</article>`;

            setTimeout(() => {
              location.href = "user_details.html";
            }, 2000); 
          } else {
            messagePrint.innerHTML = "<p>Invalid Email or Password. Please try again!</p>";
          }
        } else {
          messagePrint.innerHTML = "<i>No user found in the database.</i>";
        }
      } catch (error) {
        console.error("Error fetching data:", error);
        alert("Something went wrong. Please try again later.");
      }
    });
});
