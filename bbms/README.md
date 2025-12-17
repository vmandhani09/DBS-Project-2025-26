# Blood Bank Management System (BBMS)

A comprehensive PHP + MySQL blood bank management system with advanced DBMS features including triggers, stored procedures, views, and normalized database design.

## ✨ Features

- 🩸 **Donor Management** - Register, search, edit and track blood donors
- 💉 **Donation Tracking** - Record donations with automatic stock updates
- 🏥 **Hospital Management** - Manage partner hospitals
- 👤 **Patient Management** - Manage patients requiring blood
- 📦 **Blood Stock Management** - Real-time inventory with low-stock alerts
- 📋 **Blood Request Processing** - Handle, approve and fulfill blood requests
- 🔔 **Automatic Notifications** - Low stock and urgent request alerts
- 📊 **Dashboard Analytics** - Visual statistics and KPI cards
- 🔐 **Admin Authentication** - Secure login with bcrypt hashing
- 📝 **Complete Audit Trail** - Activity logging for all operations
- ⚠️ **Error Handling** - User-friendly error messages throughout

## 🛠 Technology Stack

- **Backend:** PHP 8+
- **Database:** MySQL 8+
- **Frontend:** HTML5, CSS3, Bootstrap 5.3.2, Bootstrap Icons
- **Server:** XAMPP (Apache)
- **UI Theme:** Pink Minimalistic Design

## 🚀 Quick Start

### 1. Setup Files
Place the `bbms` folder inside your webroot:
```
C:\xampp\htdocs\bbms
```

### 2. Import Database

**Recommended:** Import the simplified schema
```
database/bbms_simple.sql
```

Or for advanced features:
```
database/bbms_complete.sql
```

Import via phpMyAdmin or MySQL command line:
```bash
mysql -u root -p < database/bbms_simple.sql
```

### 3. Configure Database (if needed)
Edit `db.php` if your MySQL credentials differ:
```php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'bbms';
```

### 4. Access Application
```
http://localhost/bbms/login.php
```

### 🔑 Default Credentials
| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `admin123` |

---

## 📁 Project Structure

```
bbms/
├── database/
│   ├── bbms_simple.sql        # ⭐ Recommended - Simple compatible schema
│   ├── bbms_complete.sql      # Full advanced schema
│   ├── schema.sql             # Tables only (advanced)
│   ├── views.sql              # Database views
│   ├── triggers.sql           # Database triggers
│   └── stored_procedures.sql  # Stored procedures
├── docs/
│   ├── DOCUMENTATION.md       # Full DBMS documentation
│   └── QUICK_REFERENCE.md     # Quick reference guide
├── includes/
│   ├── Database.php           # Advanced database class
│   └── helpers.php            # PHP helper functions
├── assets/
│   ├── style.css              # Styles (pink minimalistic theme)
│   └── app.js                 # JavaScript (form validation)
├── donors/                    # Donor CRUD pages
├── donations/                 # Donation management
├── patients/                  # Patient CRUD pages
├── hospitals/                 # Hospital CRUD pages
├── requests/                  # Blood request management
├── stock/                     # Stock management
├── issue/                     # Blood issue pages
├── db.php                     # Database connection + helpers
├── auth.php                   # Authentication middleware
├── header.php                 # Sidebar + navigation
├── footer.php                 # Common footer
├── index.php                  # Dashboard
├── login.php                  # Login page
├── logout.php                 # Logout handler
└── README.md
```

---

## 📊 Database Schema

### Core Tables
| Table | Description |
|-------|-------------|
| `blood_groups` | Blood type master data (A+, A-, B+, etc.) |
| `admins` | System administrators |
| `donors` | Blood donors |
| `donations` | Donation records |
| `patients` | Patients |
| `blood_requests` | Blood requests |
| `blood_stock` | Blood inventory |
| `blood_issue` | Blood issue records |
| `hospitals` | Partner hospitals |
| `notification_logs` | System notifications |
| `activity_logs` | Audit trail |

### Key Views
- `view_available_blood` - Stock with availability status
- `view_pending_requests` - Pending requests by priority

### Triggers
- Auto-update stock on donation (insert)
- Auto-decrease stock on issue (insert)
- Low stock alert notifications

---

## 🔄 Workflow

1. **Add Donors** → Register donors with blood group
2. **Record Donations** → Automatically updates stock
3. **Add Patients** → Register patients needing blood
4. **Create Request** → Submit blood request (Normal/High/Critical)
5. **Approve Request** → Admin approves after stock check
6. **Issue Blood** → Fulfill request, stock decreases

---

## 🎨 UI Features

- Responsive design (works on mobile/tablet/desktop)
- Pink minimalistic color theme
- KPI cards on dashboard
- Filter and search on list pages
- Form validation (client & server side)
- Toast notifications for success/error
- Collapsible sidebar navigation

---

## 📚 Documentation

For detailed DBMS documentation:
- **Full docs:** `docs/DOCUMENTATION.md`
- **Quick reference:** `docs/QUICK_REFERENCE.md`

---

## 📝 License

MIT License

---

## 👥 Credits

BBMS Development Team

