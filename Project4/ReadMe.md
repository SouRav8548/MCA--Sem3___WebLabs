                                                                                                                                                                                                                                                                                                                                                                                        https://gemini.google.com/share/49e8add86e13

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

---

---

---

Generate a complete website for a MARKSHEET DOWNLOAD PORTAL using HTML, CSS (based on glass blue and black theme), and PHP on XAMPP. The website should include the following features:

1. **Admin Portal:**

   - Secure login for admin users.

   - A web interface displaying a table structure where admins can enter, edit, and manage student marks.

   - Ability to add new student records with fields like Student ID, Name, Subject-wise marks, and overall grade.

   - Validation for data input to ensure accuracy.

   - Option to export or backup marks data.

2. **User/Student Portal:**

   - Student in their profilr section edit all personal information including photo, also these personal information entry in their registration.

   - When student in their dashboard section then also view their picture with stylish(unique) view.

   - Secure login for students to access their individual marksheet.

   - Display the marksheet in a clear, readable tabuler format with all subjects and total marks, table also edit with unique stylish css.

   - Provide options to download the marksheet in multiple formats, such as PDF(recommended) and Excel(optional).

   - Responsive design for accessibility on various devices.

Ensure proper session management and security measures throughout the site. Use clean, well-commented code with modular structure separating HTML, CSS, and PHP files.

This web by default in dark mode in navbar have a toogle button.

At the end, provide all final files organized in appropriate folders ready for deployment.
