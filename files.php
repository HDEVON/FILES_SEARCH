<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

// Establish database connection
$conn = new mysqli('localhost', 'root', '', 'if0_35716346_may');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search_results = []; // Variable to hold search results (plural, as LIKE can return multiple)
$search_name = ''; // Variable to hold the searched name

// Check if the form has been submitted using the correct input name
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search_name'])) {
    $search_name = trim($_GET['search_name']);

    if (!empty($search_name)) {
        // Prepare statement to prevent SQL injection, search by NME using LIKE
        $stmt = $conn->prepare("SELECT ID, NME, NID, BD, ENDWORK, FILEGNDR, FILENO, FILE_T FROM emp WHERE NME LIKE ?");
        $search_term = "%" . $search_name . "%"; // Add wildcards for LIKE search
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch all results into an array
            $search_results = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            // Update error message to reflect searching by name
            $search_error = "لم يتم العثور على موظف بهذا الاسم: " . htmlspecialchars($search_name);
        }
        $stmt->close();
    } else {
         // Update error message
         $search_error = "من فضلك ادخل اسم للبحث.";
    }
}
$conn->close(); // Close the connection after processing

?>
<!DOCTYPE html>
<html>
<head>
    <title>Files Page - Search Employees</title>
    <style>
        /* Enhanced CSS with animations */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); /* Light gradient background */
            color: #333;
            padding: 20px;
            min-height: 100vh;
        }

        .container { /* Optional: Wrap content for better centering/max-width */
             max-width: 800px;
             margin: 20px auto;
             background-color: #fff;
             padding: 30px;
             border-radius: 10px;
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #4a4a4a; /* Darker heading color */
            margin-bottom: 15px;
            text-align: center; /* Center headings */
        }
         h1 {
             color: #5b2c82; /* Purple accent for main heading */
             margin-bottom: 25px;
         }


        .search-container {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0, 0.05);
            text-align: center; /* Center form elements */
        }

        .search-container form {
            display: flex; /* Align input and button */
            justify-content: center;
            gap: 10px; /* Space between input and button */
        }

        .search-container input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 20px; /* Rounded input */
            font-size: 1em;
            flex-grow: 1; /* Allow input to take available space */
            max-width: 300px; /* Limit input width */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .search-container input[type="text"]:focus {
             border-color: #8a4ef4; /* Highlight on focus */
             box-shadow: 0 0 5px rgba(138, 78, 244, 0.3);
             outline: none;
        }


        .search-container button {
            padding: 10px 20px;
            background-color: #8a4ef4; /* Purple button */
            color: white;
            border: none;
            border-radius: 20px; /* Rounded button */
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .search-container button:hover {
            background-color: #7035d8; /* Darker purple on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }


        .results-container {
            margin-bottom: 20px;
            opacity: 0; /* Start hidden for animation */
            transform: translateY(10px); /* Start slightly lower */
            animation: fadeInResults 0.5s ease forwards;
            animation-delay: 0.2s; /* Delay animation slightly */
        }

        /* Keyframe animation for fading in results */
        @keyframes fadeInResults {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0, 0.1);
            border-radius: 8px; /* Rounded table corners */
            overflow: hidden; /* Needed for border-radius on table */
            table-layout: auto; /* This ensures columns adjust to content width */
        }

        th, td {
            border-bottom: 1px solid #eee; /* Lighter borders */
            padding: 12px 15px; /* More padding */
            text-align: right; /* Align text to the right for RTL */
            /* word-break: break-word; */ /* Remove this line */
        }

        th {
            background-color: #e9ecef; /* Light grey header */
            color: #495057;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9em;
            white-space: nowrap; /* Prevent headers from wrapping */
        }

        tr:hover {
             background-color: #f1f1f1; /* Hover effect for rows */
        }

        tr:last-child td {
             border-bottom: none; /* Remove border for last row */
        }

        td {
            color: #555;
        }

        .error {
            color: #dc3545; /* Bootstrap danger color */
            background-color: #f8d7da; /* Light red background */
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }

        .logout-link {
             position: fixed; /* Keep it fixed on screen */
             top: 15px;
             right: 20px;
             background-color: #dc3545; /* Red background */
             color: white;
             padding: 8px 15px;
             border-radius: 20px;
             text-decoration: none;
             font-size: 0.9em;
             font-weight: 500;
             box-shadow: 0 2px 5px rgba(0,0,0, 0.2);
             transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .logout-link:hover {
             background-color: #c82333; /* Darker red on hover */
             transform: translateY(-1px);
             text-decoration: none;
             color: white;
        }
    </style>
</head>
<body>

<!-- Optional: Wrap main content in a container -->
<div class="container">

    <a href="logout.php" class="logout-link">Logout</a>

    <h1>مرحبا بكم فى قسم الملفات</h1>
    <p style="text-align: center; margin-bottom: 25px;">انت الان على يوزر بإسم : <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>

    <div class="search-container">
        <h2>ابحث على اى موظف بالاسم</h2>
        <form method="GET" action="">
             <!-- Change input name to search_name -->
            <input type="text" name="search_name" placeholder="ادخل اسم الموظف" value="<?php echo htmlspecialchars($search_name); ?>" required>
            <button type="submit">بحث</button>
        </form>
    </div>

    <!-- Results container - animation applied via CSS -->
    <div class="results-container">
        <?php if (isset($search_error)): ?>
            <p class="error"><?php echo $search_error; ?></p>
        <?php endif; ?>

        <?php if (!empty($search_results)): ?> <!-- Check if the results array is not empty -->
            <h3>نتائج البحث:</h3>
            <!-- Add dir="rtl" to the table tag -->
            <table dir="rtl">
                <thead>
                    <tr>
                        <!-- Update table headers to match selected columns -->
                        <th>كود الموظف</th>
                        <th>الاسم</th>
                        <th>الرقم القومى</th>
                        <th>تاريخ الميلاد</th>
                        <th>تاريخ المعاش</th>
                        <th>نوع الملف</th>
                        <th>رقم الملف</th>                    </tr>
                </thead>
                <tbody>
                    <!-- Loop through each result -->
                    <?php foreach ($search_results as $row): ?>
                    <tr>
                        <!-- Display data for each column -->
                        <td><?php echo htmlspecialchars($row['ID']); ?></td>
                        <td><?php echo htmlspecialchars($row['NME']); ?></td>
                        <td><?php echo htmlspecialchars($row['NID']); ?></td>
                        <td><?php echo htmlspecialchars($row['BD']); ?></td>
                        <td><?php echo htmlspecialchars($row['ENDWORK']); ?></td>
                        <td><?php echo htmlspecialchars($row['FILEGNDR'] . ' - ' . $row['FILE_T']); ?></td>
                        <td><?php echo htmlspecialchars($row['FILENO']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search_name']) && !isset($search_error) && empty($search_results)): ?>
             <!-- Show message only if a search was performed but yielded no results -->
             <p style="text-align: center; margin-top: 15px;">لم يتم العثور على نتائج.</p>
        <?php endif; ?>
    </div>

</div> <!-- Close container -->

<!-- Add JavaScript for inactivity timeout before closing body -->
<script>
    let inactivityTimer;
    const timeoutDuration = 5 * 60 * 1000; // 15 minutes in milliseconds

    // Function to redirect to login page
    function logoutUser() {
        window.location.href = 'login.php'; // Redirect to login page
    }

    // Function to reset the inactivity timer
    function resetTimer() {
        clearTimeout(inactivityTimer); // Clear the previous timer
        inactivityTimer = setTimeout(logoutUser, timeoutDuration); // Start a new timer
    }

    // Initial setup: Start the timer when the page loads
    window.onload = resetTimer;

    // Reset timer on user activity events
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.onclick = resetTimer;
    document.onscroll = resetTimer;
    document.ontouchstart = resetTimer; // For touch devices

</script>

</body>
</html>