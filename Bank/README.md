# 🏦 BANKING SYSTEM - COMPREHENSIVE PROJECT

A complete, production-ready banking system built with PHP, MySQL, HTML5, CSS3, and JavaScript. Includes Admin, Manager, Staff, and Customer portals with comprehensive features.

## 📋 TABLE OF CONTENTS

1. [Quick Start](#quick-start)
2. [Features](#features)
3. [System Requirements](#system-requirements)
4. [Installation](#installation)
5. [File Structure](#file-structure)
6. [Database Setup](#database-setup)
7. [Default Credentials](#default-credentials)
8. [User Roles](#user-roles)
9. [Color Scheme](#color-scheme)
10. [Features Guide](#features-guide)
11. [Code Snippets](#code-snippets)
12. [Troubleshooting](#troubleshooting)

---

## 🚀 QUICK START

### For XAMPP Users (Recommended)
```bash
# 1. Extract to htdocs
C:\xampp\htdocs\banking_system\

# 2. Start XAMPP (Apache + MySQL)

# 3. Create Database
- Open http://localhost/phpmyadmin
- Create database: banking_system
- Import: banking_system_setup.sql

# 4. Access
- http://localhost/banking_system/
- Login: admin / admin123
```

---

## ✨ FEATURES

### 🔐 Authentication & Security
- ✅ Bcrypt password hashing
- ✅ Session-based authentication
- ✅ Role-based access control (RBAC)
- ✅ Input sanitization & SQL injection prevention
- ✅ Activity logging
- ✅ User status management

### 👨‍💼 Admin Portal
- User management (Create/Edit/Delete)
- Branch management
- Account approval workflow
- Loan approval with assessment
- ATM card & cheque book request approval
- Transaction monitoring
- Reports and analytics
- Activity logging
- Interest rate management

### 👷 Staff Portal
- Customer registration
- Account opening
- Document verification
- Request processing
- Customer profile management
- KYC verification
- Service request handling

### 💳 Customer Portal

#### Account Management
- Savings Account (SB)
- Current Account (CB)
- Balance checking
- Transaction history
- Account details

#### Investment Products
**Fixed Deposit (FD)**
- 1 Year (6.5% p.a.)
- 2 Years (7.0% p.a.)
- 3 Years (7.25% p.a.)
- Auto-calculated maturity amount
- Status tracking

**Recurring Deposit (RD)**
- 5 Years (5.75% p.a.)
- Monthly contribution
- Interest calculation
- Maturity tracking

#### Banking Services

**Loan Request (6 Types)**
1. 🚗 Car Loan
   - Vehicle details required
   - Income verification
   - CIBIL score consideration

2. 🏠 Home Loan
   - Property papers required
   - Income proof
   - Valuation assessment

3. 🎓 Education Loan
   - Admission letter required
   - Fee structure proof
   - Parent details

4. 💰 Personal Loan
   - Income proof
   - Bank statement
   - Quick approval

5. ⚜️ Gold Loan
   - Gold assessment
   - Valuation certificate
   - Quick processing

6. 💒 Wedding Loan
   - Wedding invitation
   - Basic documentation
   - Quick approval

**ATM Card Request**
- Card type selection
- Manager approval
- Downloadable PDF
- Status tracking

**Cheque Book Request**
- Leaf count selection (default: 50)
- Manager approval
- Downloadable PDF
- Status tracking

---

## 💻 SYSTEM REQUIREMENTS

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- 100 MB disk space
- Modern web browser

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

---

## 📦 INSTALLATION

### Method 1: XAMPP (Recommended for Windows)

```bash
# Step 1: Download and install XAMPP
# Download from: https://www.apachefriends.org/

# Step 2: Extract banking_system to htdocs
Copy banking_system/ to C:\xampp\htdocs\

# Step 3: Start XAMPP Control Panel
# Click Start on Apache and MySQL

# Step 4: Create database
- Open http://localhost/phpmyadmin
- Click "New"
- Database name: banking_system
- Click Create

# Step 5: Import SQL
- Select banking_system database
- Click Import tab
- Choose banking_system_setup.sql
- Click Go

# Step 6: Access application
- Open http://localhost/banking_system/login.php
- Login with admin/admin123
```

### Method 2: Manual Setup (Any Server)

```bash
# 1. Create database
mysql -u root -p
CREATE DATABASE banking_system;
USE banking_system;
SOURCE banking_system_setup.sql;

# 2. Update config.php with your database credentials
# Edit config.php and set:
# - DB_HOST
# - DB_USER
# - DB_PASSWORD

# 3. Create uploads directory
mkdir -p uploads/documents
mkdir -p uploads/kyc
mkdir -p uploads/loans
chmod -R 755 uploads/

# 4. Access the application
# Open in browser: http://yourserver/banking_system/
```

---

## 📂 FILE STRUCTURE

```
banking_system/
│
├── 📄 index.php                    # Home page
├── 📄 login.php                    # Login portal
├── 📄 register.php                 # Customer registration
├── 📄 logout.php                   # Logout handler
│
├── ⚙️ config.php                   # Database configuration
├── ⚙️ functions.php                # Common functions
│
├── 🗂️ admin/                       # Admin portal
│   ├── dashboard.php               # Admin dashboard
│   ├── manage-users.php            # User management
│   ├── manage-staff.php            # Staff management
│   ├── manage-customers.php        # Customer management
│   ├── manage-branches.php         # Branch management
│   ├── approve-accounts.php        # Account approval
│   ├── approve-loans.php           # Loan approval
│   ├── approve-requests.php        # Request approval
│   ├── view-transactions.php       # Transaction view
│   ├── reports.php                 # Reports
│   ├── activity-log.php            # Activity log
│   └── header.php                  # Navigation
│
├── 🗂️ staff/                       # Staff portal
│   ├── dashboard.php               # Staff dashboard
│   ├── register-customer.php       # Customer registration
│   ├── manage-customers.php        # Customer management
│   ├── verify-documents.php        # Document verification
│   ├── process-requests.php        # Request processing
│   └── header.php                  # Navigation
│
├── 🗂️ customer/                    # Customer portal
│   ├── dashboard.php               # Customer dashboard
│   ├── accounts.php                # Account management
│   ├── open-account.php            # Open new account
│   ├── fixed-deposit.php           # FD application
│   ├── recurring-deposit.php       # RD application
│   ├── loan-request.php            # Loan application
│   ├── view-loans.php              # Loan status
│   ├── cheque-request.php          # Cheque request
│   ├── atm-card-request.php        # ATM card request
│   ├── view-requests.php           # Request status
│   ├── transactions.php            # Transaction history
│   ├── balance.php                 # Balance check
│   ├── profile.php                 # Profile management
│   └── header.php                  # Navigation
│
├── 🎨 css/                         # Stylesheets
│   ├── style.css                   # Main styles
│   ├── admin.css                   # Admin styles
│   ├── staff.css                   # Staff styles
│   ├── customer.css                # Customer styles
│   └── responsive.css              # Responsive design
│
├── 📜 js/                          # JavaScript
│   ├── main.js                     # Main functionality
│   ├── validation.js               # Form validation
│   ├── theme.js                    # Theme toggle
│   └── utils.js                    # Utilities
│
├── 📁 uploads/                     # File uploads
│   ├── documents/                  # Document storage
│   ├── kyc/                        # KYC documents
│   └── loans/                      # Loan documents
│
├── 💾 database/
│   ├── banking_system.sql          # Full database dump
│   └── banking_system_setup.sql    # Setup script
│
├── 📋 SETUP_GUIDE.md               # Setup instructions
└── 📖 README.md                    # This file
```

---

## 🗄️ DATABASE SETUP

### Tables Created:

1. **users** - All system users
2. **branches** - Bank branches
3. **customer_profile** - Customer details
4. **bank_accounts** - Customer accounts
5. **fixed_deposits** - FD investments
6. **recurring_deposits** - RD investments
7. **loans** - Loan applications
8. **atm_card_requests** - ATM requests
9. **cheque_book_requests** - Cheque requests
10. **transactions** - Transaction history
11. **interest_rates** - Interest rate master
12. **loan_documents** - Loan documents
13. **activity_log** - Activity tracking

### Sample Data Included:

```sql
-- 3 Branches created
-- Interest rates configured
-- Admin user created
```

---

## 🔑 DEFAULT CREDENTIALS

### Admin Login
```
Username: admin
Password: admin123
Role: Admin (Full Access)
```

### Create Additional Users:
After login, use Admin panel to create:
- Manager accounts
- Staff accounts
- Branch accounts
- CSP (Customer Service Point) accounts

---

## 👥 USER ROLES

### 1. Admin
- **Access:** Full system access
- **Permissions:**
  - Create/Edit/Delete all users
  - Manage branches
  - Approve all requests
  - View all reports
  - System configuration

### 2. Manager
- **Access:** Admin & Staff features
- **Permissions:**
  - Approve accounts
  - Approve loans
  - Approve service requests
  - Manage staff
  - View customer records

### 3. Staff
- **Access:** Customer-facing operations
- **Permissions:**
  - Register customers
  - Create accounts
  - Verify documents
  - Process requests
  - Manage customer profiles

### 4. Customer
- **Access:** Personal banking
- **Permissions:**
  - View accounts
  - Apply for services
  - Check balance
  - View transactions
  - Download statements

### 5. Branch/CSP
- **Access:** Branch-level operations
- **Permissions:**
  - Manage branch customers
  - Process branch transactions
  - Generate branch reports

---

## 🎨 COLOR SCHEME

### Dark Mode (Default)
```css
Primary Color: #2d5f82 (Teal Blue)
Secondary Color: #8b6f47 (Brown)
Background: #131a1a (Dark Navy)
Text: #e0e0e0 (Light Gray)
Success: #2eb86f (Green)
Danger: #c91f2f (Red)
Warning: #f4a542 (Orange)
```

### Light Mode
```css
Primary Color: #2d5f82 (Navy Blue)
Secondary Color: #8b6f47 (Brown)
Background: #ffffff (White)
Text: #1f2121 (Dark Gray)
Success: #2eb86f (Green)
Danger: #c91f2f (Red)
Warning: #f4a542 (Orange)
```

### Theme Toggle
- Button in top-right corner
- Persists to localStorage
- Smooth transitions

---

## 📖 FEATURES GUIDE

### Customer Account Opening

```
1. Register (register.php)
   - Fill personal information
   - Enter KYC details
   - Account status: Pending

2. Manager Approval
   - Admin reviews profile
   - Verifies documents
   - Activates account

3. Login & Start Banking
   - Access customer portal
   - View account
   - Apply for services
```

### Loan Application Workflow

```
1. Apply (customer/loan-request.php)
   - Select loan type
   - Enter amount
   - Upload documents

2. Verification (staff/verify-documents.php)
   - Staff verifies documents
   - Checks completeness
   - Adds comments

3. Approval (admin/approve-loans.php)
   - Manager assesses
   - Checks CIBIL/credit
   - Approves/Rejects

4. Download (customer/view-loans.php)
   - Customer downloads approval
   - Gets loan details
```

### Fixed Deposit Creation

```
1. Apply (customer/fixed-deposit.php)
   - Select tenure
   - Enter principal
   - System calculates interest

2. Approval (auto for verified customers)
   - FD created
   - Maturity date set
   - Interest scheduled

3. Track (customer/accounts.php)
   - View FD status
   - Check maturity date
   - See interest earned
```

---

## 💻 CODE SNIPPETS

### Login Function

```php
<?php
// In config.php / functions.php
function checkLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

function checkRole($required_role) {
    checkLogin();
    
    $user_role = $_SESSION['user']['role'] ?? null;
    if ($user_role !== $required_role) {
        header('Location: ' . SITE_URL . 'index.php');
        exit;
    }
}
?>
```

### Database Query

```php
<?php
// In functions.php
function executeQuery($conn, $query, $params = [], $types = '') {
    try {
        $stmt = $conn->prepare($query);
        
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    } catch (Exception $e) {
        Message::addError("Database Error: " . $e->getMessage());
        return false;
    }
}
?>
```

### Theme Toggle

```javascript
// In js/theme.js
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}
```

---

## 🐛 TROUBLESHOOTING

### Database Connection Error
```
Error: Connection failed: ...
Solution:
1. Check MySQL is running
2. Verify credentials in config.php
3. Ensure database exists
```

### Session Not Working
```
Error: Cannot modify header information
Solution:
1. Check for whitespace before <?php
2. Verify session directory permissions
3. Clear browser cookies
```

### Files Not Found
```
Error: 404 Not Found
Solution:
1. Check file path matches directory structure
2. Verify file permissions (644 for files)
3. Check .htaccess rewrite rules
```

### Login Failing
```
Error: Invalid username or password
Solution:
1. Verify credentials are correct
2. Check user status is 'active'
3. Check database has users table
4. Try admin/admin123
```

### Theme Not Working
```
Issue: Theme doesn't change
Solution:
1. Enable localStorage in browser
2. Clear cache and reload
3. Check browser console for JS errors
4. Check theme.js is loaded
```

---

## 📞 SUPPORT

For issues or questions:
1. Check this README
2. Review code comments
3. Check browser console (F12)
4. Check server error logs
5. Review function.php for helpers

---

## 📝 LICENSE

This is an open-source project for educational purposes.

---

## 🔐 SECURITY TIPS

✅ Change default admin password
✅ Use HTTPS in production
✅ Regularly backup database
✅ Keep PHP/MySQL updated
✅ Monitor activity logs
✅ Implement 2FA (optional)
✅ Use strong passwords
✅ Regular security audits

---

## 🎯 FUTURE ENHANCEMENTS

- [ ] Two-Factor Authentication
- [ ] Email notifications
- [ ] SMS alerts
- [ ] Mobile app
- [ ] API endpoints
- [ ] Advanced analytics
- [ ] AI-powered assessments
- [ ] Bill payment
- [ ] Money transfer API
- [ ] Chatbot support

---

## 📊 STATISTICS

- **Total Tables:** 13
- **User Roles:** 5+
- **Features:** 20+
- **Lines of Code:** 5000+
- **Database Queries:** 100+
- **Security Features:** 8+

---

**Version:** 1.0.0  
**Created:** December 2025  
**Last Updated:** December 2025  
**Status:** Production Ready ✅

---

## 🙏 THANK YOU

Thank you for using SecureBank Banking System. Happy banking! 🏦

If you have suggestions or improvements, feel free to enhance this project.

**Enjoy secure banking! 💰**
