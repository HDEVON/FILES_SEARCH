<?php
session_start();
// Redirect to login if the user is not logged in
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

// --- Helper function to generate search results HTML ---
function generate_search_results_html($results, $error = null, $search_name = '', $is_ajax_request = false) {
    ob_start(); // Start output buffering

    if ($error) {
        // Use the .error class for styling
        echo '<p class="error">' . htmlspecialchars($error) . '</p>';
    } elseif (!empty($results)) {
        echo '<h3>نتائج البحث:</h3>';
        // Add dir="rtl" to the table for right-to-left layout
        echo '<table dir="rtl">';
        echo '<thead><tr><th>كود الموظف</th><th>الاسم</th><th>الرقم القومى</th><th>تاريخ الميلاد</th><th>تاريخ المعاش</th><th>نوع الملف</th><th>رقم الملف</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['ID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['NME']) . '</td>';
            echo '<td>' . htmlspecialchars($row['NID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['BD']) . '</td>';
            echo '<td>' . htmlspecialchars($row['ENDWORK']) . '</td>';
            // Combine FILEGNDR and FILE_T for display
            echo '<td>' . htmlspecialchars($row['FILEGNDR'] . ' - ' . $row['FILE_T']) . '</td>';
            echo '<td>' . htmlspecialchars($row['FILENO']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } elseif ($is_ajax_request || ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search_name']) && !empty($search_name))) {
        // Show "no results" only if a search was actually performed (either via AJAX or initial load with search_name)
         echo '<p style="text-align: center; margin-top: 15px;">لم يتم العثور على نتائج.</p>';
    }
    // If it's not an AJAX request and no search was done, output nothing or a default message
    // else { echo '<p style="text-align: center; margin-top: 15px;">Please enter a name to search.</p>'; }


    return ob_get_clean(); // Return buffered content
}
// --- End Helper Function ---


// --- Helper function to generate report results HTML ---
// Add $start_date and $end_date parameters
function generate_report_results_html($results, $error = null, $start_date = '', $end_date = '') {
    ob_start(); // Start output buffering

    if ($error) {
        echo '<p class="error">' . htmlspecialchars($error) . '</p>';
    } elseif (!empty($results)) {
        // Use the dates in the title if they are available
        if (!empty($start_date) && !empty($end_date)) {
            echo '<h3>End life between ' . htmlspecialchars($start_date) . ' and ' . htmlspecialchars($end_date) . '</h3>';
        } else {
            echo '<h3>نتائج التقرير:</h3>'; // Fallback title
        }
        // Add dir="rtl" to the table for right-to-left layout
        // Adjust columns as needed for the report
        echo '<table dir="rtl">';
        echo '<thead><tr><th>كود الموظف</th><th>الاسم</th><th>الرقم القومى</th><th>تاريخ الميلاد</th><th>تاريخ المعاش</th><th>نوع الملف</th><th>رقم الملف</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['ID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['NME']) . '</td>';
            echo '<td>' . htmlspecialchars($row['NID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['BD']) . '</td>';
            echo '<td>' . htmlspecialchars($row['ENDWORK']) . '</td>';
            echo '<td>' . htmlspecialchars($row['FILEGNDR'] . ' - ' . $row['FILE_T']) . '</td>';
            echo '<td>' . htmlspecialchars($row['FILENO']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        // Show "no results" if a search was performed but yielded no results
         echo '<p style="text-align: center; margin-top: 15px;">لا توجد سجلات تطابق فترة التاريخ المحددة.</p>';
    }

    return ob_get_clean(); // Return buffered content
}
// --- End Report Helper Function ---


// Establish database connection
$conn = new mysqli('localhost', 'root', '', 'if0_35716346_may');
if ($conn->connect_error) {
    // For AJAX requests, maybe return an error message instead of dying
    if (isset($_GET['ajax'])) {
        echo '<p class="error">Database connection failed.</p>';
        exit();
    }
    die("Connection failed: " . $conn->connect_error);
}
// Set character set for the connection
$conn->set_charset("utf8");


$search_results = [];
$search_error = null;
$search_name = '';
$report_results = []; // Added for report
$report_error = null; // Added for report
$start_date = ''; // Added for report
$end_date = ''; // Added for report

// Determine request type (search ajax, report ajax, or normal page load)
$request_type = 'load'; // Default
if (isset($_GET['ajax'])) {
    if ($_GET['ajax'] == '1') {
        $request_type = 'search_ajax';
    } elseif ($_GET['ajax'] == 'report') {
        $request_type = 'report_ajax';
    }
}


// Process based on request type
if ($request_type === 'search_ajax' || ($request_type === 'load' && isset($_GET['search_name']))) {
    // --- Existing Search Logic ---
    if (isset($_GET['search_name'])) {
        $search_name = trim($_GET['search_name']);
        if (!empty($search_name)) {
            $stmt = $conn->prepare("SELECT ID, NME, NID, BD, ENDWORK, FILEGNDR, FILENO, FILE_T FROM emp WHERE NME LIKE ?");
            if ($stmt) {
                $search_term = "%" . $search_name . "%";
                $stmt->bind_param("s", $search_term);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $search_results = $result->fetch_all(MYSQLI_ASSOC);
                } else {
                    $search_error = "لم يتم العثور على موظف بهذا الاسم: " . htmlspecialchars($search_name);
                }
                $stmt->close();
            } else {
                 $search_error = "Error preparing database query.";
            }
        } else {
             $search_error = "من فضلك ادخل اسم للبحث.";
        }
    }
    // --- End Existing Search Logic ---

    // If it's an AJAX search request, output only the results HTML and exit
    if ($request_type === 'search_ajax') {
        echo generate_search_results_html($search_results, $search_error, $search_name, true);
        $conn->close();
        exit();
    }

} elseif ($request_type === 'report_ajax') {
    // --- New Report Logic ---
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = trim($_GET['start_date']);
        $end_date = trim($_GET['end_date']);

        // Basic validation (more robust validation recommended)
        if (!empty($start_date) && !empty($end_date)) {
            // Ensure dates are in YYYY-MM-DD format if needed by DB
            // You might need date format conversion depending on input and DB column type

            $stmt = $conn->prepare("SELECT ID, NME, NID, BD, ENDWORK, FILEGNDR, FILENO, FILE_T FROM emp WHERE ENDWORK BETWEEN ? AND ? ORDER BY ENDWORK ASC");
            if ($stmt) {
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $report_results = $result->fetch_all(MYSQLI_ASSOC);
                }
                // No specific error message if no results found, handled by generate_report_results_html
                $stmt->close();
            } else {
                $report_error = "Error preparing report query.";
            }
        } else {
            $report_error = "من فضلك ادخل تاريخ البداية والنهاية.";
        }
    } else {
        $report_error = "لم يتم توفير تواريخ البدء والانتهاء.";
    }
    // --- End New Report Logic ---

    // Output report results HTML and exit
    echo generate_report_results_html($report_results, $report_error);
    $conn->close();
    exit();
}


// Close connection only if it's a normal page load request
if ($request_type === 'load') {
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Application Page</title>
    <!-- Add CSS links or styles here -->
    <style>
        /* Basic Styling */
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

        .container {
             max-width: 900px; /* Adjust as needed */
             margin: 20px auto;
             background-color: #fff;
             padding: 30px;
             border-radius: 10px;
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
             position: relative; /* Needed for logout link positioning */
        }

        h1, h2, h3 {
            color: #4a4a4a;
            margin-bottom: 15px;
            text-align: center;
        }
         .container > h1 { /* Style for the main page title if needed */
             color: #5b2c82;
             margin-bottom: 25px;
         }

        /* Logout Link Style */
        .logout-link {
             position: absolute; /* Position relative to container */
             top: 20px;
             right: 20px;
             background-color: #dc3545;
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
             background-color: #c82333;
             transform: translateY(-1px);
             text-decoration: none;
             color: white;
        }

        /* Tab Styles */
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #ccc;
            margin-bottom: 20px;
        }
        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background-color: transparent;
            font-size: 1.1em;
            font-weight: 500;
            color: #555;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: color 0.3s ease, border-color 0.3s ease;
            flex-grow: 1; /* Make buttons share space */
            text-align: center;
        }
        .tab-button:hover {
            color: #5b2c82;
        }
        .tab-button.active {
            color: #5b2c82;
            border-bottom-color: #5b2c82;
        }

        .tab-content .tab-pane {
            display: none; /* Hide all panes by default */
            padding: 15px 0; /* Add some padding */
            animation: fadeInTab 0.4s ease forwards; /* Use forwards to keep end state */
            border-radius: 0 0 8px 8px; /* Round bottom corners for all panes */
        }
        .tab-content .tab-pane.active {
            display: block; /* Show active pane */
        }
        .tab-content h1 { /* Style for the H1 inside tabs */
            font-size: 1.8em; /* Example size */
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Specific styles for the Report Tab Pane */
        #report-tab-content {
            background: linear-gradient(to bottom left, rgba(255, 255, 255, 0.5), rgba(220, 235, 250, 0.5)); /* Subtle light blue gradient */
            border-right: 3px solid #6c757d; /* Accent border (matches report button color) */
            padding: 20px 15px; /* Adjust padding */
            /* Keep the default fadeInTab animation unless specified otherwise */
        }


        /* Specific styles for the Search Tab Pane */
        #search-tab-content {
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.5), rgba(230, 230, 250, 0.5)); /* Subtle lavender gradient */
            border-left: 3px solid #8a4ef4; /* Accent border */
            padding: 20px 15px; /* Adjust padding */
            /* border-radius: 0 0 8px 8px; /* Moved to general .tab-pane */
            animation: slideInSearchTab 0.5s ease-out forwards;
        }


        @keyframes fadeInTab {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* New animation specific to the search tab */
        @keyframes slideInSearchTab {
            from {
                opacity: 0;
                transform: translateX(-20px); /* Slide in from left */
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
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
            text-align: center;
            color: #5b2c82;
            font-size: 20px;
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

        .report-form-container {
            margin-bottom: 30px; /* Increased margin */
            padding: 25px; /* Increased padding */
            background-color: #eef2f7; /* Slightly different background */
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0, 0.07); /* Slightly stronger shadow */
            text-align: center;
            border: 1px solid #d6dde5; /* Subtle border */
        }
        .report-form-container h2 { /* Style the heading inside the container */
            color: #4a6a8a; /* A calmer blue/grey */
            margin-bottom: 20px;
            font-weight: 500;
        }
        .report-form-container form {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            justify-content: center;
            align-items: center;
            gap: 15px 20px; /* Row and column gap */
        }
        .report-form-container label {
            font-weight: 500;
            margin-right: 5px;
            color: #555;
        }
        .report-form-container input[type="date"] {
            padding: 9px 14px; /* Adjusted padding */
            border: 1px solid #ccc;
            border-radius: 20px; /* Rounded corners */
            font-size: 0.95em;
            background-color: #fff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .report-form-container input[type="date"]:focus {
             border-color: #6c757d; /* Match button color on focus */
             box-shadow: 0 0 5px rgba(108, 117, 125, 0.3);
             outline: none;
        }
        .report-form-container button {
            padding: 10px 25px; /* Slightly wider button */
            background-color: #6c757d; /* Grey button for report */
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0, 0.1); /* Add subtle shadow to button */
        }
        .report-form-container button:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

       

     
        



    </style>
</head>
<body>

<div class="container">
    <a href="logout.php" class="logout-link">Logout</a>

    <h1>مرحبا بكم فى قسم الملفات</h1>

    <br>

    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
    <p style="text-align: center; margin-bottom: 25px;">This is the main application area.</p>

    <!-- Tab Buttons -->
    <div class="tab-buttons">
        <button class="tab-button active" data-tab="report">Report</button>
        <button class="tab-button" data-tab="search">Search for file</button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Report Tab Pane -->
        <div id="report-tab-content" class="tab-pane active">
            <h1>This is report page</h1>

            <!-- Report Date Range Form -->
            <div class="report-form-container">
                <h2>بحث حسب تاريخ المعاش</h2>
                <form id="report-form" action="" method="GET" dir="rtl">
                    <div>
                        <label for="start_date">من تاريخ:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    <div>
                        <label for="end_date">الى تاريخ:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                    <button type="submit">عرض التقرير</button>
                </form>
            </div>

            <!-- Report Results Area -->
            <div class="results-container" id="report-results-area">
                <!-- Report results will be loaded here via AJAX -->
            </div>

        </div>

        <!-- Search Tab Pane -->
        <div id="search-tab-content" class="tab-pane">
            <div class="search-container">
                <h2>ابحث على اى موظف بالاسم</h2>
                <!-- Add id="search-form" to the form -->
                <form method="GET" action="" id="search-form" dir="rtl">
                    <input type="text" name="search_name" placeholder="ادخل اسم الموظف" value="<?php echo htmlspecialchars($search_name); ?>" required>
                    <button type="submit">بحث</button>
                </form>
            </div>

            <!-- Results container - Add id="search-results-area" -->
            <!-- Initial content generated by PHP on page load -->
            <div class="results-container" id="search-results-area">
               
            </div>
        </div>
    </div> <!-- End Tab Content -->

</div> <!-- Close container -->

<!-- Add JavaScript for Tab Switching AND AJAX Form Submission -->
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // --- Tab Switching Logic (existing) ---
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-content .tab-pane');

        if (tabButtons.length > 0 && tabPanes.length > 0) {
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.getAttribute('data-tab');
                    const targetPane = document.getElementById(targetTab + '-tab-content');

                    if (targetPane) {
                        tabButtons.forEach(btn => btn.classList.remove('active'));
                        tabPanes.forEach(pane => pane.classList.remove('active'));
                        button.classList.add('active');
                        targetPane.classList.add('active');
                    }
                    resetTimer(); // Reset inactivity timer on tab switch
                });
            });
        }

        // --- AJAX Search Form Submission ---
        const searchForm = document.getElementById('search-form');
        const searchInput = searchForm.querySelector('input[name="search_name"]');
        const resultsArea = document.getElementById('search-results-area');

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default page refresh

                const searchTerm = searchInput.value.trim();
                if (!searchTerm) {
                    resultsArea.innerHTML = '<p class="error">من فضلك ادخل اسم للبحث.</p>';
                    return; // Don't search if input is empty
                }

                // Add a loading indicator (optional)
                resultsArea.innerHTML = ''; // Clear previous results
                resultsArea.classList.add('loading');

                // Construct the URL for the AJAX request
                const url = `newfile.php?ajax=1&search_name=${encodeURIComponent(searchTerm)}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text(); // Get the HTML response
                    })
                    .then(html => {
                        resultsArea.classList.remove('loading'); // Remove loading indicator
                        resultsArea.innerHTML = html; // Update results area with the HTML from PHP
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                        resultsArea.classList.remove('loading'); // Remove loading indicator
                        resultsArea.innerHTML = '<p class="error">An error occurred while fetching results. Please try again.</p>';
                    });

                resetTimer(); // Reset inactivity timer on search
            });
        }


        // --- NEW: AJAX Report Form Submission ---
        const reportForm = document.getElementById('report-form');
        const startDateInput = reportForm?.querySelector('#start_date');
        const endDateInput = reportForm?.querySelector('#end_date');
        const reportResultsArea = document.getElementById('report-results-area');

        if (reportForm && startDateInput && endDateInput && reportResultsArea) { // Check all exist
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default page refresh

                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                if (!startDate || !endDate) {
                    reportResultsArea.innerHTML = '<p class="error">من فضلك ادخل تاريخ البداية والنهاية.</p>';
                    return;
                }
                // Optional: Add validation to ensure end date is not before start date
                if (new Date(endDate) < new Date(startDate)) {
                     reportResultsArea.innerHTML = '<p class="error">تاريخ النهاية لا يمكن ان يكون قبل تاريخ البداية.</p>';
                     return;
                }


                reportResultsArea.innerHTML = ''; // Clear previous results
                reportResultsArea.classList.add('loading');

                // Construct the URL for the AJAX request
                const url = `newfile.php?ajax=report&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

                fetch(url)
                    .then(response => response.ok ? response.text() : Promise.reject(`HTTP error! status: ${response.status}`))
                    .then(html => {
                        reportResultsArea.classList.remove('loading'); // Remove loading indicator
                        reportResultsArea.innerHTML = html; // Update results area with the HTML from PHP
                    })
                    .catch(error => {
                        console.error('Error fetching report results:', error);
                        reportResultsArea.classList.remove('loading'); // Remove loading indicator
                        reportResultsArea.innerHTML = '<p class="error">An error occurred while fetching report results. Please try again.</p>';
                    });

                resetTimer(); // Reset inactivity timer on report generation
            });
        }


        // --- Inactivity Timer Code (existing) ---
        let inactivityTimer;
        const timeoutDuration = 5 * 60 * 1000; // 5 minutes

        function logoutUser() {
            window.location.href = 'logout.php';
        }

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(logoutUser, timeoutDuration);
        }

        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;
        document.ontouchstart = resetTimer;

        resetTimer(); // Initial timer start
    });
</script>

</body>
</html>



    