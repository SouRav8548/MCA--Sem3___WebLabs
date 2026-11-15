# 🌐 MCA Semester 3 Web Projects

A collection of seven web-development exercises for the **MCA third semester at the University of Kalyani**, covering frontend interactions, PHP forms and sessions, database-backed applications, MVC organization, and text-file handling.

**Maintainer:** Sourav Maity · **Personal brand:** xCODER

> These are educational prototypes for local development. The banking and marksheet applications are demonstrations, not real financial or institutional services. This README is based on source inspection of `Web-Project-main.zip`; the applications have not been run or integration-tested as part of this documentation work.

## 🎯 Learning goals

- Build interfaces with HTML, CSS, JavaScript, and jQuery.
- Handle form submissions, authentication, and sessions with PHP.
- Connect PHP applications to MySQL/MariaDB using PDO and MySQLi.
- Practice CRUD operations, role-based workflows, MVC, and file I/O.
- Explore charts, browser-generated PDFs, and theme switching.

## 📂 Projects at a glance

Paths below are relative to the repository root.

| Folder | Project | Implemented features visible in source | Entry point |
| --- | --- | --- | --- |
| Project1 | University event registration demo | Client-side form validation, random background colors, rotating banner using jQuery | `Project1/index.html` |
| Project2 | Scientific calculator and converters | Registration/login, profile, calculator, currency/weight/length/number conversion | `Project2/public/index.php` |
| Project3 | Online quiz application | User/admin registration, question management, quizzes, stored attempts, profile charts | `Project3/index.php` |
| Project4 | Marksheet portal | Student accounts and photos, admin marks management, marksheet view and browser PDF export | `Project4/index.php` |
| Project5 | MVC factorial calculator | Separate controller, model, and view; server-side factorial calculation | `Project5/index.php` |
| Project6 | Banking simulation | Manager/staff/consumer workflows, account status, transfers, deposits, loans, service requests | `Project6/index.php` |
| Project7 | Text-file editor | Read, append, and overwrite `data.txt`; editor view and theme toggle | `Project7/index.php` |

Use **`RootIndex.html`** as the main launcher: its links include Project2's `public` directory. `RootIndex.php` is an alternative directory-scanning launcher; its Project2 link goes to the parent folder rather than the application's entry page.

## 🛠️ Tech stack and prerequisites

| Layer | Technologies |
| --- | --- |
| Frontend | HTML5, CSS3, JavaScript; jQuery 3.6.0 in Project1 |
| Backend | PHP; sessions; PDO MySQL and MySQLi |
| Database | MySQL-compatible database, including MariaDB bundled with XAMPP |
| Local environment | XAMPP with Apache and database service, or a PHP CLI development server plus a separate database service |
| Browser dependencies | Chart.js 4.4.0, html2pdf.js 0.10.1, Font Awesome 6.4.0, Google Fonts, Picsum images on relevant pages |

Use a modern browser and a PHP environment with `pdo_mysql` and `mysqli` enabled. No PHP version is pinned in the archive; verify compatibility with your installed version. Internet access is needed for externally loaded scripts, styles, fonts, and banner images unless you vendor them locally.

There is **no `package.json`, `composer.json`, dependency lockfile, or build pipeline** in the supplied archive. You do not need `npm install` or `composer install` for this version.

**Assignment documentation:** LaTeX is used for the assignment papers according to the project author's description, but no `.tex` sources or compiled assignment papers are present in this ZIP. Add those separately if they should be part of the repository.

## 🚀 Setup with XAMPP on Windows

### 1. Place the project in the web root

Extract the ZIP and copy its inner `Web-Project-main` folder to:

```text
C:\xampp\htdocs\Web-Project-main\
```

Make sure `RootIndex.html` is directly inside that folder, rather than inside a second nested copy. If your XAMPP installation is elsewhere, use its `htdocs` directory instead.

### 2. Start the services

Open the XAMPP Control Panel and start **Apache** and **MySQL**. Open `http://localhost/phpmyadmin/` to configure the databases.

### 3. Import the database schemas

At the phpMyAdmin server level, select **Import** and import each required SQL file once into your local development environment. The scripts contain their own database creation and selection statements.

| Project | SQL file | Database | Connection settings |
| --- | --- | --- | --- |
| Project2 | `Project2/config/db.sql` | `Calculator` | `Project2/config/db.php` |
| Project3 | `Project3/db.sql` | `quiz_app` | `Project3/config.php` |
| Project4 | `Project4/database.sql` | `marks_portal` | `Project4/config/db_connect.php` |
| Project6 | `Project6/bank_db.sql` | `bank_db` | `Project6/db.php` |

**Important:** `Project6/bank_db.sql` starts with `DROP DATABASE IF EXISTS bank_db`. It deletes an existing database of that name. Use a disposable local database or back it up before importing. Do not re-import casually. Other scripts may also fail on repeated imports because databases, tables, or seed accounts already exist.

Projects 1, 5, and 7 do not require a database. Update the host, username, password, and database name in the listed connection files to match your environment. The supplied files use local hosts and the demonstration account `root` with an empty password.

### 4. Check writable files and folders

PHP needs write access to:

- `Project4/assets/uploads/` for student photos.
- `Project6/uploads/` for profile images.
- `Project7/data.txt` for file editing; its parent directory also needs write access if the file must be created.

The upload folders are already included. Avoid making the entire project globally writable. Project4 refers to `default.png`, but that default image is not included in its upload directory; add an appropriate placeholder if needed.

### 5. Launch

Open:

```text
http://localhost/Web-Project-main/RootIndex.html
```

For the calculator directly:

```text
http://localhost/Web-Project-main/Project2/public/index.php
```

Do not open PHP pages through a `file://` path; serve them through PHP. If Apache uses another port, include that port in the URL.

## ▶️ Alternative local server and CLI options

With PHP on your PATH, run this from the extracted repository root:

```bash
php -S 127.0.0.1:8000 -t .
```

Open `http://127.0.0.1:8000/RootIndex.html`. Stop the server with **Ctrl+C**. Database-backed projects still require their database service and imported schemas.

| Option | Purpose |
| --- | --- |
| `-S 127.0.0.1:8000` | Start PHP's development server on loopback port 8000 |
| `-t .` | Use the current directory as the document root |
| `-v` | Show the PHP version |
| `-m` | List enabled extensions |
| `-l filename.php` | Check one PHP file's syntax |

On Windows, if PHP is not on PATH, use `C:\xampp\php\php.exe` instead of `php`. This project has no custom command-line interface. PHP's built-in server is intended for development, not public hosting.

## 💡 Usage examples

- **Event page:** try an invalid email or a mobile number with fewer than ten digits. A valid form triggers an alert and reset; it does not save a registration to a server.
- **Calculator:** register with a password containing at least eight characters, an uppercase letter, a lowercase letter, and a digit; log in and try the calculator and converter tabs. Currency rates are fixed examples, not live market rates.
- **Quiz:** create an admin account through the demo registration form, add questions, then create a separate user account and take a quiz. Admin self-registration is a demo limitation to fix before deployment.
- **Marksheet:** register a student, then use the admin interface to enter marks. Log in as the student to view or export the sample marksheet.
- **Factorial:** enter `5` for `120`, or `0` for `1`. Negative values and values above 170 are rejected. Decimal handling still needs improvement.
- **Banking:** use the seeded local manager to explore the demonstration workflows. Use fictional names, identity fields, and balances only.
- **File editor:** back up `Project7/data.txt`, append a test line, then edit and save the full file. Saving a full edit overwrites the existing content.

### Local demonstration accounts

| Application | Login | Password | Source behavior |
| --- | --- | --- | --- |
| Project4 admin | `admin` | `admin123` | Hard-coded in `Project4/admin/login.php` |
| Project6 manager | `admin@bank.com` | `admin123` | Seeded by `Project6/bank_db.sql` |

These credentials are included in the source for local demonstrations. Project4's SQL contains a truncated password hash; the current admin login bypasses database password checking. Replace this behavior before any public deployment.

## 🧪 Build and testing

No compilation step or automated test suite is included. PHP and the frontend files are served directly. Example syntax check:

```bash
php -l Project5/index.php
```

This checks only that file's syntax, not its included files or runtime behavior. Repeat for modified PHP files.

Suggested manual checks:

- [ ] Launcher opens each project's correct entry page.
- [ ] Database connections succeed after fresh schema imports.
- [ ] Registration, duplicate-email handling, login, and logout behave as expected.
- [ ] Protected pages reject unauthenticated access.
- [ ] Quiz answers and attempts are recorded correctly.
- [ ] Mark changes appear in the student's view and PDF export.
- [ ] Calculator and converter outputs match known examples.
- [ ] File append and overwrite persist after reload.
- [ ] Theme toggles, keyboard focus, and narrow-screen layouts remain usable.
- [ ] Browser console and PHP logs contain no unexpected errors.

These are recommended checks, not a claim that they have passed.

## 🗂️ Repository structure

Selected files and folders from the supplied archive:

```text
Web-Project-main/
├── README.md
├── RootIndex.html
├── RootIndex.php
├── Project1/
│   ├── index.html
│   ├── script.js
│   ├── style.css
│   └── Img1.png, Img2.png, Img3.png, Img4.jpg
├── Project2/
│   ├── config/                 # db.php and db.sql
│   └── public/                 # Auth, calculator, converter, profile
│       ├── assets/             # CSS and JavaScript
│       └── partials/           # Header, footer, navigation
├── Project3/
│   ├── config.php
│   ├── db.sql
│   ├── questions.php
│   ├── quiz.php
│   ├── submit_quiz.php
│   └── ...                     # Auth, profiles, styles and scripts
├── Project4/
│   ├── admin/
│   ├── student/
│   ├── assets/                 # CSS, JavaScript and uploaded images
│   ├── config/db_connect.php
│   ├── database.sql
│   └── index.php
├── Project5/
│   ├── controllers/FactorialController.php
│   ├── models/FactorialModel.php
│   ├── views/home.php
│   ├── css/
│   ├── js/
│   └── index.php
├── Project6/
│   ├── bank_db.sql
│   ├── db.php
│   ├── actions.php
│   ├── manager_dashboard.php
│   ├── consumer_dashboard.php
│   ├── uploads/
│   ├── css/
│   ├── js/
│   └── ...
└── Project7/
    ├── index.php
    ├── good.php
    ├── data.txt
    └── style.css
```

## ⚠️ Known limitations

These observations come from the supplied code, not from a complete security audit:

- **Project6 stores passwords in plaintext.** Replace this with password hashing and verification before using real accounts.
- **Project3 allows users to select the admin role when registering.** Restrict administrator provisioning.
- **Project4 uses hard-coded admin credentials** and includes a debug page. Its SQL admin hash is incomplete.
- **Project2 registration sets `verified = 1` immediately.** The presence of `verify.php` does not mean working email verification is configured.
- **Project1 does not persist form submissions.** Its restrictive address regex rejects ordinary punctuation.
- **Project2 currency rates are static.** Number-base parsing uses `parseInt`, which can accept a valid prefix of otherwise invalid input.
- **Project5 does not enforce integer-only input.** Large factorials also exceed exact integer precision.
- **Project7 has no authentication check in its main editor page.** Keep it local unless access control is added.
- Several features depend on external CDNs. Uploaded sample images are included, and some are large.
- The original root README lists nine questions, but this ZIP contains seven project folders; the text-file exercise is implemented in `Project7`.

## 🛣️ Suggested roadmap

- [ ] Fix the authentication and authorization limitations above.
- [ ] Add CSRF protection, session hardening, and robust upload validation after reviewing each application.
- [ ] Move local credentials into environment-specific configuration.
- [ ] Add automated tests and repeatable database setup.
- [ ] Validate converter inputs and integer-only factorial input.
- [ ] Add the missing default profile image and review bundled uploads.
- [ ] Vendor essential frontend dependencies for offline lab use.
- [ ] Add assignment statements, LaTeX sources, screenshots, and verified test results.

These are proposed improvements, not committed delivery dates.

## 🤝 Contributing

1. Open an issue describing the bug or proposed change, if issues are enabled.
2. Fork the repository and create a focused branch.
3. Keep changes within the relevant project folder.
4. Run syntax checks and the relevant manual checks.
5. Submit a pull request with the affected project, setup changes, and test evidence.

Do not include personal records, credentials, or real financial information in fixtures or screenshots.

### Code of conduct

Be respectful and constructive. Discuss code and ideas without personal attacks; welcome learners, credit others' work, and respect privacy. No separate `CODE_OF_CONDUCT.md` or formal reporting process is included in the supplied archive.

## 📄 License

No `LICENSE` file was found in the ZIP. No open-source license is assigned by this README. Obtain the appropriate permissions before redistribution, and review third-party code and image terms separately. The maintainer can add a license after confirming ownership and intended reuse rights.

## 👤 Author and contact

**Sourav Maity — xCODER**  
MCA Semester 3 web-development coursework, University of Kalyani.

For project questions, use the repository's Issues or pull requests if enabled. No GitHub username or contact email was verified from the supplied archive, so none is invented here.

## References

Project descriptions and known issues are derived from the included source files and SQL schemas. Local setup references:

- [XAMPP Windows FAQ](https://www.apachefriends.org/faq_windows.html)
- [PHP built-in development server](https://www.php.net/manual/en/features.commandline.webserver.php)

The original project-level README files contain planning text; features above are based on implementation files rather than treating every planned feature as completed.
