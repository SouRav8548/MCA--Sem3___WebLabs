# BANKING SYSTEM - COMPLETE PROJECT DOCUMENTATION

## Project Overview
A comprehensive Web-based Banking System with role-based access control for Admin, Manager, Staff, and Customer portals. Built with HTML5, CSS3, JavaScript, and PHP with MySQL database.

---

## FEATURES INCLUDED

### 1. Admin Portal (Admin/Manager/Branch/Head Branch/CSP)
- User management (Create, Edit, Delete staff and customers)
- Branch management
- Account approval and activation
- Loan approval with assessment
- Cheque book request approval
- ATM card request approval
- View all transactions
- Generate reports
- Activity logging

### 2. Staff Portal (Staff/Manager)
- Customer registration
- Account creation
- Processing customer requests
- Loan document verification
- Service request handling
- Customer profile management
- Transaction processing

### 3. Customer Portal
- **Account Management:**
  - Savings Account (SB)
  - Current Account (CB)
  - Check balance
  - View transactions
  
- **Investment Products:**
  - Fixed Deposit (FD)
    * 1 Year (6.5% interest)
    * 2 Years (7.0% interest)
    * 3 Years (7.25% interest)
  
  - Recurring Deposit (RD)
    * 5 Years (5.75% interest)
  
- **Banking Services:**
  - Cheque Book Request (Downloadable PDF after approval)
  - ATM Card Request (Downloadable PDF after approval)
  - Loan Request (6 Types)
  
- **Loan Types with Assessment:**
  1. Car Loan (requires vehicle details)
  2. Home Loan (requires property papers)
  3. Education Loan (requires admission letter)
  4. Personal Loan (basic assessment)
  5. Gold Loan (gold assessment)
  6. Wedding Loan (basic assessment)

### 4. Registration & Authentication
- New Customer Registration (Requires Manager Approval)
- New Staff Registration (Admin approval)
- Login for All Roles
- Session Management
- Password Reset

---

## DIRECTORY STRUCTURE

```
banking_system/
│
├── index.php                      # Main home page
├── login.php                      # Login page
├── register.php                   # Customer registration
├── logout.php                     # Logout script
├── config.php                     # Database configuration
├── functions.php                  # Common functions
│
├── admin/
│   ├── dashboard.php              # Admin/Manager dashboard
│   ├── manage-users.php           # User management
│   ├── manage-staff.php           # Staff management
│   ├── manage-customers.php       # Customer management
│   ├── manage-branches.php        # Branch management
│   ├── approve-accounts.php       # Account approval
│   ├── approve-loans.php          # Loan approval
│   ├── approve-requests.php       # Request approval (ATM, Cheque)
│   ├── view-transactions.php      # View all transactions
│   ├── reports.php                # Reports and analytics
│   ├── activity-log.php           # Activity logging
│   └── header.php                 # Admin header/navigation
│
├── staff/
│   ├── dashboard.php              # Staff dashboard
│   ├── register-customer.php      # Customer registration form
│   ├── manage-customers.php       # Customer management
│   ├── verify-documents.php       # Document verification
│   ├── process-requests.php       # Process customer requests
│   └── header.php                 # Staff header/navigation
│
├── customer/
│   ├── dashboard.php              # Customer dashboard
│   ├── accounts.php               # View all accounts
│   ├── open-account.php           # Open new account
│   ├── fixed-deposit.php          # Fixed deposit application
│   ├── recurring-deposit.php       # Recurring deposit application
│   ├── loan-request.php           # Loan request form
│   ├── view-loans.php             # View loan status
│   ├── cheque-request.php         # Cheque book request
│   ├── atm-card-request.php       # ATM card request
│   ├── view-requests.php          # View request status
│   ├── transactions.php           # View transactions
│   ├── balance.php                # Check balance
│   ├── profile.php                # Customer profile
│   └── header.php                 # Customer header/navigation
│
├── css/
│   ├── style.css                  # Main stylesheet
│   ├── admin.css                  # Admin portal styles
│   ├── staff.css                  # Staff portal styles
│   ├── customer.css               # Customer portal styles
│   └── responsive.css             # Responsive design
│
├── js/
│   ├── main.js                    # Main JavaScript
│   ├── validation.js              # Form validation
│   ├── theme.js                   # Theme toggle
│   └── utils.js                   # Utility functions
│
├── uploads/
│   ├── documents/                 # Document uploads
│   ├── kyc/                       # KYC documents
│   └── loans/                     # Loan documents
│
└── database/
    └── banking_system.sql         # Database dump/setup
```

---

## DATABASE TABLES

### Core Tables:
1. **users** - All users (admin, manager, staff, customer)
2. **branches** - Branch information
3. **customer_profile** - Detailed customer information
4. **bank_accounts** - Bank accounts (savings/current)
5. **fixed_deposits** - Fixed deposit investments
6. **recurring_deposits** - Recurring deposit investments
7. **loans** - Loan applications and records
8. **atm_card_requests** - ATM card requests
9. **cheque_book_requests** - Cheque book requests
10. **transactions** - All transactions
11. **interest_rates** - Master data for interest rates
12. **loan_documents** - Loan supporting documents
13. **activity_log** - User activity tracking

---

## SETUP INSTRUCTIONS

### Step 1: Extract Files
1. Extract the banking_system folder to: `C:\xampp\htdocs\`

### Step 2: Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create new database named: `banking_system`
3. Import the SQL file: `database/banking_system.sql`
4. Or paste the SQL commands from `banking_system_setup.sql`

### Step 3: Start XAMPP
1. Start Apache and MySQL services

### Step 4: Access Application
- Home: http://localhost/banking_system/
- Login: http://localhost/banking_system/login.php

### Step 5: Default Credentials
- **Admin Login:**
  - Username: `admin`
  - Password: `admin123`

---

## USER ROLES & PERMISSIONS

### 1. Admin
- Full system access
- Create/manage all users
- Approve/reject all requests
- View reports and analytics
- Manage branches

### 2. Manager
- Approve accounts and services
- View all customer records
- Approve loan applications
- Manage staff members
- Process requests

### 3. Staff
- Register new customers
- Create customer profiles
- Verify documents
- Process basic requests
- Handle customer inquiries

### 4. Customer
- View personal accounts
- Apply for services
- Download statements/documents
- Check balance
- View transaction history

---

## FEATURES DETAILS

### Authentication & Security
- Bcrypt password hashing
- Session-based authentication
- Role-based access control (RBAC)
- Input sanitization
- SQL injection prevention (Prepared statements)
- CSRF token support (can be added)
- Activity logging

### Account Management
- Account opening process with approval
- Balance inquiry
- Multiple account support per customer
- Account status tracking (active/inactive/closed)

### Investment Products
- **Fixed Deposit:**
  - Multiple tenure options (1, 2, 3 years)
  - Auto-calculated maturity amount
  - Status tracking
  - Withdrawal management

- **Recurring Deposit:**
  - Monthly contribution option
  - 5-year tenure
  - Interest calculation
  - Maturity tracking

### Loan Management
- 6 types of loans with specific requirements
- AI-assisted assessment (CIBIL score consideration)
- Document upload and verification
- Manager approval workflow
- Loan status tracking
- Downloadable loan approval documents

### Service Requests
- **ATM Card:**
  - Request submission
  - Manager approval
  - Card generation with PDF
  - Status tracking
  - Downloadable card document

- **Cheque Book:**
  - Request with leaf count selection
  - Manager approval
  - PDF generation
  - Downloadable cheque book
  - Status tracking

### Reports & Analytics
- Customer dashboard with key metrics
- Transaction reports
- Loan portfolio analysis
- Account statistics
- Activity logs

### Dark/Light Theme
- System-wide theme toggle
- Persistent theme preference
- Color scheme:
  - **Dark Mode:** Black (#1f2121) & Brown (#8b6f47)
  - **Light Mode:** White (#ffffff) & Navy Blue (#2d5f82)
- Smooth theme transitions

---

## FILE DESCRIPTIONS

### Core Files
- **config.php**: Database connection and configuration
- **functions.php**: Reusable PHP functions
- **login.php**: User authentication
- **register.php**: New user registration
- **logout.php**: Session termination

### Admin Portal Files
- **admin/dashboard.php**: Admin overview and statistics
- **admin/manage-users.php**: User CRUD operations
- **admin/approve-loans.php**: Loan approval interface
- **admin/approve-requests.php**: Approve ATM/Cheque requests

### Staff Portal Files
- **staff/dashboard.php**: Staff overview
- **staff/register-customer.php**: Customer onboarding
- **staff/process-requests.php**: Process customer requests

### Customer Portal Files
- **customer/dashboard.php**: Customer overview
- **customer/accounts.php**: Account listing and details
- **customer/fixed-deposit.php**: FD application
- **customer/loan-request.php**: Loan application form
- **customer/cheque-request.php**: Cheque book request
- **customer/atm-card-request.php**: ATM card request

### Styling Files
- **css/style.css**: Main responsive stylesheet
- **css/admin.css**: Admin-specific styles
- **css/staff.css**: Staff-specific styles
- **css/customer.css**: Customer-specific styles

### JavaScript Files
- **js/main.js**: Main functionality
- **js/validation.js**: Form validation
- **js/theme.js**: Theme management

---

## COLOR SCHEME

### Dark Mode (Default)
- Background: #131a1a (Dark Navy)
- Text: #e0e0e0 (Light Gray)
- Primary: #2d5f82 (Teal Blue)
- Secondary: #8b6f47 (Brown)
- Success: #2eb86f (Green)
- Danger: #c91f2f (Red)
- Warning: #f4a542 (Orange)

### Light Mode
- Background: #ffffff (White)
- Text: #1f2121 (Dark Gray)
- Primary: #2d5f82 (Navy Blue)
- Secondary: #8b6f47 (Brown)
- Success: #2eb86f (Green)
- Danger: #c91f2f (Red)
- Warning: #f4a542 (Orange)

---

## FUTURE ENHANCEMENTS

1. Two-Factor Authentication (2FA)
2. Email notifications
3. SMS alerts for transactions
4. Mobile banking app
5. API integration for external services
6. Advanced analytics and reporting
7. Automated interest calculation
8. Scheduled deposits
9. Money transfer between accounts
10. Bill payment integration

---

## TECHNICAL STACK

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP)
- **Authentication:** bcrypt password hashing
- **Architecture:** Modular, Role-based access

---

## TROUBLESHOOTING

### Database Connection Error
1. Check MySQL is running in XAMPP
2. Verify database credentials in config.php
3. Ensure banking_system database exists

### Session Issues
1. Check PHP session.save_path in php.ini
2. Verify sessions folder has write permissions
3. Clear browser cookies if needed

### File Upload Issues
1. Check uploads/ folder exists and has 755 permissions
2. Verify file size limits in php.ini
3. Check ALLOWED_EXTENSIONS in config.php

### Theme Not Persisting
1. Check localStorage is enabled in browser
2. Clear browser cache and reload
3. Check browser console for JavaScript errors

---

## SECURITY RECOMMENDATIONS

1. ✅ Use HTTPS in production
2. ✅ Set strong database passwords
3. ✅ Implement CSRF tokens
4. ✅ Use prepared statements (already implemented)
5. ✅ Sanitize all user inputs
6. ✅ Implement rate limiting
7. ✅ Regular security audits
8. ✅ Keep PHP and MySQL updated

---

## SUPPORT & DOCUMENTATION

For additional help:
1. Check the comments in each PHP file
2. Review the database schema in banking_system.sql
3. Test with provided admin credentials
4. Check browser console for JavaScript errors
5. Review error logs in XAMPP\logs\

---

**Version:** 1.0.0
**Created:** December 2025
**Last Updated:** December 2025
**Developed for:** Banking System Project
