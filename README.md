<<<<<<< HEAD
# FILES_SEARCH Application

This is a simple PHP web application for managing and searching employee file records.

## Features

*   **User Authentication:** Secure login system using PHP sessions. Users are redirected to `login.php` if not logged in.
*   **Employee Search:**
    *   Search for employees by name using an AJAX-powered search form.
    *   Results are displayed dynamically without page reloads.
*   **End of Work Report:**
    *   Generate reports of employees based on their end-of-work date (`ENDWORK`).
    *   Select a date range using date input fields.
    *   Report results are fetched and displayed using AJAX.
    *   The report title dynamically updates to show the selected date range.
*   **Tabbed Interface:** Search and Report functionalities are separated into distinct tabs for better user experience.
*   **Inactivity Logout:** Users are automatically logged out after a period of inactivity (currently set to 5 minutes).
*   **Responsive Styling:** Basic CSS styling for layout and user interface elements.

## Setup

1.  **Prerequisites:**
    *   A web server with PHP support (like XAMPP, WAMP, MAMP).
    *   A MySQL database server.
2.  **Database:**
    *   Create a database (e.g., `if0_35716346_may`).
    *   Import the necessary table structure (e.g., the `emp` table with columns like `ID`, `NME`, `NID`, `BD`, `ENDWORK`, `FILEGNDR`, `FILENO`, `FILE_T`).
    *   Ensure you have a `users` table for login credentials (used by `login.php`).
3.  **Configuration:**
    *   Update the database connection details in `newfile.php` and `login.php` if they differ from `localhost`, `root`, ``, `if0_35716346_may`.
    *   Ensure the database character set is correctly handled (currently set to `utf8`).
4.  **Deployment:**
    *   Place the project files (`newfile.php`, `login.php`, `logout.php`, etc.) in your web server's document root (e.g., `htdocs` for XAMPP).
    *   Access the application through your web browser (e.g., `http://localhost/FILES_SEARCH/login.php`).

## Files

*   `newfile.php`: Main application page displaying search and report tabs after login.
*   `login.php`: Handles user login.
*   `logout.php`: Handles user logout.
*   `README.md`: This file.
=======
in this script :
1- login by mysql database
2- go to files page and search by name
3- get data from database
4- logout if wait 5 minuts without any move
>>>>>>> d59d45b6b769f57702c6a4251f418f92f013357c
