<?php
// Database connection details (replace with your actual credentials)
$servername = "localhost";
$username = "medical";
$password = "123456";
$dbname = "healthcare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Registration logic
if (isset($_POST['register'])) {
    $reg_username = $_POST['reg_username'];
    $reg_password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT); // Hash the password!
    $reg_email = $_POST['reg_email'];

    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)"; // Use prepared statements!
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $reg_username, $reg_password, $reg_email); // bind parameters
    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();

}

// Login logic
if (isset($_POST['login'])) {
    $login_username = $_POST['login_username'];
    $login_password = $_POST['login_password'];

    $sql = "SELECT id, username, password FROM users WHERE username = ?"; // Prepared statement!
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login_username); // bind parameters
    $stmt->execute();
    $stmt->store_result(); //important for num_rows and bind_result

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();

        if (password_verify($login_password, $hashed_password)) {
            echo "Login successful!";
            // Start a session or set a cookie here
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }
    $stmt->close();
}

$conn->close();
?>
