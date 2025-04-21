<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'if0_35716346_may');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT NME, pass FROM login WHERE name=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        echo $id;
        // Changed from password_verify to direct comparison
        // assuming passwords are stored in plain text or using a different hashing method
        if ($password === $hashed_password) {
            // Remove the echo statement that might be preventing the redirect
            $_SESSION['name'] = $id;
            header("Location: files.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* Updated CSS to match the image style */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap'); /* Added Google Font */

        * { /* Basic reset */
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            /* Gradient background similar to the image */
            background: linear-gradient(to bottom right, #3a1c71, #d76d77, #ffaf7b); /* Example gradient */
            background: linear-gradient(135deg, #2c1a4d 0%, #4f2a7e 50%, #1e1035 100%); /* Closer purple gradient */
            /* Add background image/stars if desired */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Use min-height */
            color: #fff; /* Default text color */
        }

        .login-container {
            /* Frosted glass effect */
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px); /* Safari */
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px 30px; /* Increased padding */
            border-radius: 15px; /* More rounded corners */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            width: 380px; /* Slightly wider */
            text-align: center;
            /* Removed animation for now, can be added back if needed */
        }

        h2 {
            margin-bottom: 25px;
            color: #fff; /* White title */
            font-weight: 600;
            font-size: 2em;
        }

        .input-group { /* Wrapper for input */
            position: relative;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%; /* Full width */
            padding: 12px 15px 12px 15px; /* Adjusted padding (more space for potential icons) */
            margin-bottom: 0; /* Remove bottom margin from input itself */
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px; /* Highly rounded corners */
            background-color: rgba(255, 255, 255, 0.05); /* Slight transparency */
            color: #fff; /* White text */
            font-size: 1em;
        }

        input::placeholder { /* Style placeholder text */
            color: rgba(255, 255, 255, 0.7);
        }

        /* Add styles for icons if you use an icon library */
        /* .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }
        .input-group input { padding-left: 40px; } */


        .options { /* Flex container for remember me/forgot password */
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        .options label {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
        }

        .options input[type="checkbox"] {
            margin-right: 5px;
        }

        .options a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        .options a:hover {
            text-decoration: underline;
        }


        button {
            width: 100%;
            padding: 12px;
            background-color: #8a4ef4; /* Purple button */
            color: white;
            border: none;
            border-radius: 25px; /* Highly rounded corners */
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 500;
            transition: background-color 0.3s ease;
            margin-top: 10px; /* Add some space above button */
        }

        button:hover {
            background-color: #7035d8; /* Darker purple on hover */
        }

        .signup-link {
            margin-top: 25px;
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.8);
        }

        .signup-link a {
            color: #fff; /* White link */
            font-weight: 600;
            text-decoration: none;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }

        .error {
            color: #ffdddd; /* Lighter red for dark background */
            background-color: rgba(255, 0, 0, 0.2);
            padding: 8px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 0.9em;
            border: 1px solid rgba(255, 0, 0, 0.3);
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <form method="post">
        <!-- Input Group for Username -->
        <div class="input-group">
             <!-- Add icon here if desired: <i class="icon-user"></i> -->
            <input type="text" name="username" placeholder="Username" required />
        </div>
        <!-- Input Group for Password -->
        <div class="input-group">
             <!-- Add icon here if desired: <i class="icon-lock"></i> -->
            <input type="password" name="password" placeholder="Password" required />
             <!-- Add eye icon here if desired -->
        </div>

        <!-- Options Row -->
        <div class="options">
            <label>
                <input type="checkbox" name="remember_me"> Remember me
            </label>
            <a href="#">Forgot Password?</a>
        </div>

        <button type="submit">LOGIN</button>
    </form>

    <!-- Sign Up Link -->
    <div class="signup-link">
        Don't have an account? <a href="#">SIGN UP</a>
    </div>

    <!-- Error Message -->
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>

</body>
</html>