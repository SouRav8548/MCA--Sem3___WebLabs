htdocs/
└── marksheet_portal/
│
├── admin/ <-- Admin Module
│ ├── dashboard.php (Admin interface to manage marks)
│ ├── login.php (Admin login page)
│ └── logout.php (Admin logout script)
│
├── assets/ <-- Static Assets
│ ├── css/
│ │ └── style.css (Glass theme styles)
│ ├── js/
│ │ └── script.js (Theme toggle & interactions)
│ └── uploads/ <-- Create this empty folder manually
│ └── default.png (Optional: Place a default user image here)
│
├── config/ <-- Database Configuration
│ └── db_connect.php (Database connection file)
│
├── student/ <-- Student Module
│ ├── dashboard.php (View marks & download PDF)
│ ├── login.php (Student login page)
│ ├── logout.php (Student logout script)
│ ├── profile.php (Edit profile info & photo)
│ └── register.php (Student registration page)
│
├── database.sql (Import this into phpMyAdmin)
└── index.php (Main landing page)

---

---

Based on predict error is:

                        Fatal error: Uncaught Error: Call to a member function bind_param() on bool

What This Means--- This error happens when your database query fails before it can even add the data.

        The code: $conn->prepare(...) tried to setup the SQL command.

It failed, so instead of giving back a "Command Object", it returned FALSE (which is a boolean).

The next line tried to run bind_param() on that FALSE, causing the crash.

Common Causes
Missing Table: You haven't imported the database.sql file, so the table students doesn't exist.

        Wrong Column Names: The PHP code is trying to save to a column (like photo) that isn't in your database table.

        Typo: There is a spelling mistake in your SQL query string.
