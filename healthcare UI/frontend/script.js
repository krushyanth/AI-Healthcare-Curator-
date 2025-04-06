// Sign Up Form Submission
document.getElementById("signUpForm")?.addEventListener("submit", function (e) {
  e.preventDefault();

  // Capture user input
  const fullName = document.getElementById("fullName").value.trim();
  const email = document.getElementById("email").value.trim();
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value;

  // Basic validation to ensure no empty fields
  if (!fullName || !email || !username || !password) {
    alert("All fields are required!");
    return;
  }

  // Save user data to localStorage (simulating a database)
  const user = { fullName, email, username, password };
  localStorage.setItem(username, JSON.stringify(user));
  alert("Sign Up Successful! Please Sign In.");
  window.location.href = "index.html"; // Redirect to sign-in page after successful signup
});

// Sign In Form Submission
document.getElementById("signInForm")?.addEventListener("submit", function (e) {
  e.preventDefault();

  // Capture user input
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value;

  // Check for empty fields
  if (!username || !password) {
    alert("Please enter both username and password!");
    return;
  }

  // Retrieve user data from localStorage
  const user = JSON.parse(localStorage.getItem(username));

  if (user && user.password === password) {
    alert("Sign In Successful!");
    localStorage.setItem('loggedIn', 'true'); // Mark user as logged in
    window.location.href = "predict.html"; // Redirect to prediction page
  } else {
    alert("Invalid username or password");
  }
});

// Symptom Form Submission (Prediction Page)
document.getElementById("symptomForm")?.addEventListener("submit", function (e) {
  e.preventDefault();

  // Capture selected symptoms
  const symptom1 = document.getElementById("symptom1").value;
  const symptom2 = document.getElementById("symptom2").value;
  const symptom3 = document.getElementById("symptom3").value;

  // Ensure at least one symptom is selected
  if (!symptom1 && !symptom2 && !symptom3) {
    alert("Please select at least one symptom!");
    return;
  }

  // Simulate diagnosis (API call should replace this in a real app)
  const diagnosis = "Common Cold"; // Placeholder diagnosis
  localStorage.setItem("diagnosis", diagnosis); // Save diagnosis to localStorage
  window.location.href = "diagnosis.html"; // Redirect to diagnosis result page
});

// Display Diagnosis Result (Diagnosis Page)
window.onload = function () {
  const diagnosisResult = document.getElementById("diagnosisResult");

  if (diagnosisResult) {
    const diagnosis = localStorage.getItem("diagnosis");
    if (diagnosis) {
      diagnosisResult.innerHTML = `<p>Your diagnosis: <strong>${diagnosis}</strong></p>`;
    } else {
      diagnosisResult.innerHTML = `<p>No diagnosis available. Please return to the symptom page.</p>`;
    }
  }
};
