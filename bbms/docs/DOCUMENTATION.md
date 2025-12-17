# Blood Bank Management System (BBMS)
## Complete DBMS Documentation

---

# Table of Contents

1. [Abstract](#1-abstract)
2. [Problem Statement](#2-problem-statement)
3. [Objectives](#3-objectives)
4. [System Architecture](#4-system-architecture)
5. [ER Diagram & Database Design](#5-er-diagram--database-design)
6. [Normalization](#6-normalization)
7. [Data Flow Diagrams](#7-data-flow-diagrams)
8. [Use Case Descriptions](#8-use-case-descriptions)
9. [Database Schema Reference](#9-database-schema-reference)
10. [Advanced DBMS Features](#10-advanced-dbms-features)
11. [Installation Guide](#11-installation-guide)
12. [Conclusion](#12-conclusion)

---

# 1. Abstract

The Blood Bank Management System (BBMS) is a comprehensive web-based application designed to streamline and automate the operations of blood banks. This project implements advanced Database Management System (DBMS) concepts including normalized database design (up to 3NF), triggers for automatic stock management, stored procedures for complex business logic, views for simplified data access, and transactions for data integrity.

The system manages the complete lifecycle of blood donation and distribution: from donor registration and eligibility tracking, through donation collection and testing, to blood stock management and request fulfillment. By implementing proper database normalization and utilizing MySQL's advanced features like triggers and stored procedures, the system ensures data consistency, eliminates redundancy, and provides real-time alerts for critical situations like low stock levels.

**Key DBMS Features Implemented:**
- Fully normalized database schema (1NF → 2NF → 3NF)
- 8 Triggers for automatic operations
- 10 Stored Procedures for business logic
- 10 Database Views for reporting
- Transaction management with COMMIT/ROLLBACK
- Comprehensive audit logging

---

# 2. Problem Statement

Traditional blood bank management faces several critical challenges:

### 2.1 Data Management Issues
- **Manual Record Keeping**: Paper-based systems lead to errors, data loss, and difficulty in retrieval
- **Data Redundancy**: Same information stored multiple times causing inconsistencies
- **No Real-time Updates**: Stock levels not updated immediately after donations or issues

### 2.2 Operational Challenges
- **Donor Tracking**: Difficulty in tracking donor eligibility (90-day waiting period)
- **Stock Management**: No automatic alerts for low stock or expiring blood units
- **Request Processing**: Manual matching of blood requests with available stock

### 2.3 Critical Risks
- **Blood Expiry**: Blood units have limited shelf life (42 days for whole blood)
- **Emergency Response**: Delays in critical/urgent blood requests
- **Audit Trail**: No proper tracking of who did what and when

### 2.4 Need for Automation
- Automatic stock updates when blood is donated or issued
- Automatic eligibility updates for donors
- Automatic notifications for critical situations
- Automatic expiry tracking and alerts

---

# 3. Objectives

### 3.1 Primary Objectives

1. **Donor Management**
   - Register and maintain donor profiles
   - Track donation history
   - Automatic eligibility management (90-day rule)
   - Search donors by blood group and location

2. **Blood Stock Management**
   - Real-time stock tracking for all blood groups
   - Automatic stock updates via triggers
   - Low stock alerts
   - Expiry tracking and notifications

3. **Request Fulfillment**
   - Process blood requests from hospitals/patients
   - Priority-based queue (Critical > Urgent > Normal)
   - Stock reservation on approval
   - Complete issuance tracking

### 3.2 DBMS-Specific Objectives

1. **Data Integrity**
   - Implement proper foreign key constraints
   - Use transactions for complex operations
   - Prevent orphan records

2. **Normalization**
   - Achieve Third Normal Form (3NF)
   - Eliminate data redundancy
   - Ensure data consistency

3. **Automation**
   - Triggers for automatic stock management
   - Stored procedures for business logic
   - Views for simplified reporting

4. **Audit & Compliance**
   - Complete activity logging
   - Notification system
   - Data change tracking

---

# 4. System Architecture

## 4.1 Three-Tier Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │  Dashboard   │  │  Donor Mgmt  │  │  Stock Mgmt  │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │ Request Mgmt │  │  Reports     │  │  Settings    │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                    HTML / CSS / JavaScript                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    PHP Application                        │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐      │  │
│  │  │  Database   │  │  Helpers    │  │  Session    │      │  │
│  │  │   Class     │  │  Functions  │  │  Manager    │      │  │
│  │  └─────────────┘  └─────────────┘  └─────────────┘      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                     XAMPP / Apache Server                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       DATA LAYER                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    MySQL 8+ Database                      │  │
│  │  ┌───────────────────────────────────────────────────┐   │  │
│  │  │  TABLES (12)  │  VIEWS (10)  │  TRIGGERS (8)      │   │  │
│  │  │               │              │                     │   │  │
│  │  │  - admins     │  - view_     │  - trg_after_      │   │  │
│  │  │  - donors     │    available │    donation_       │   │  │
│  │  │  - patients   │    _blood    │    available       │   │  │
│  │  │  - donations  │  - view_     │  - trg_low_        │   │  │
│  │  │  - blood_     │    pending_  │    stock_alert     │   │  │
│  │  │    stock      │    requests  │  - trg_urgent_     │   │  │
│  │  │  - blood_     │  - view_     │    request_        │   │  │
│  │  │    requests   │    donation_ │    notification    │   │  │
│  │  │  - etc...     │    summary   │  - etc...          │   │  │
│  │  └───────────────────────────────────────────────────┘   │  │
│  │  ┌───────────────────────────────────────────────────┐   │  │
│  │  │  STORED PROCEDURES (10)                            │   │  │
│  │  │  - sp_RegisterDonor()      - sp_AddDonation()     │   │  │
│  │  │  - sp_ApproveBloodRequest() - sp_IssueBlood()     │   │  │
│  │  │  - sp_GenerateStockSummary() - etc...             │   │  │
│  │  └───────────────────────────────────────────────────┘   │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## 4.2 Component Description

| Layer | Component | Technology | Purpose |
|-------|-----------|------------|---------|
| Presentation | UI Pages | HTML5, CSS3, Bootstrap 5 | User interface |
| Presentation | Interactivity | JavaScript | Form validation, AJAX |
| Application | Web Server | Apache (XAMPP) | HTTP request handling |
| Application | Backend | PHP 8+ | Business logic |
| Application | Database Class | PHP mysqli | Database abstraction |
| Data | RDBMS | MySQL 8+ | Data storage |
| Data | Triggers | MySQL | Automatic operations |
| Data | Procedures | MySQL | Complex business logic |
| Data | Views | MySQL | Simplified queries |

---

# 5. ER Diagram & Database Design

## 5.1 Entity-Relationship Diagram (Text UML Style)

```
┌─────────────────┐         ┌─────────────────┐
│     ADMINS      │         │  BLOOD_GROUPS   │
├─────────────────┤         ├─────────────────┤
│ *admin_id (PK)  │         │ *group_code(PK) │
│  username       │         │  group_name     │
│  email          │         │  can_donate_to  │
│  password_hash  │         │  can_receive_   │
│  full_name      │         │    from         │
│  phone          │         │  description    │
│  is_active      │         └────────┬────────┘
│  last_login     │                  │
└────────┬────────┘                  │
         │                           │
         │ 1                         │ 1
         │                           │
         ▼ N                         ▼ N
┌─────────────────┐         ┌─────────────────┐
│    DONORS       │◄────────│  BLOOD_STOCK    │
├─────────────────┤         ├─────────────────┤
│ *donor_id (PK)  │         │ *stock_id (PK)  │
│  first_name     │         │  group_code(FK) │
│  last_name      │         │  units_         │
│  date_of_birth  │         │    available    │
│  gender         │         │  units_reserved │
│  blood_group    │◄─────┐  │  min_threshold  │
│    (FK)         │      │  │  max_capacity   │
│  phone          │      │  └─────────────────┘
│  email          │      │
│  address        │      │
│  city           │      │
│  is_eligible    │      │
│  total_         │      │
│   donations     │      │
│  registered_by  │      │
│    (FK)         │      │
└────────┬────────┘      │
         │               │
         │ 1             │
         ▼ N             │
┌─────────────────┐      │
│   DONATIONS     │──────┘
├─────────────────┤
│ *donation_id    │
│   (PK)          │
│  donor_id (FK)  │
│  blood_group    │
│   (FK)          │
│  units_donated  │
│  donation_date  │
│  hemoglobin_    │
│   level         │
│  bag_number     │
│  expiry_date    │
│  test_result    │
│  status         │
└─────────────────┘

┌─────────────────┐         ┌─────────────────┐
│   HOSPITALS     │         │    PATIENTS     │
├─────────────────┤         ├─────────────────┤
│ *hospital_id    │◄────────│ *patient_id(PK) │
│   (PK)          │    1  N │  first_name     │
│  hospital_name  │         │  last_name      │
│  hospital_type  │         │  blood_group_   │
│  contact_person │         │   needed (FK)   │
│  phone          │         │  hospital_id    │
│  email          │         │   (FK)          │
│  address        │         │  ward_number    │
│  city           │         │  doctor_name    │
│  is_verified    │         │  status         │
└────────┬────────┘         └────────┬────────┘
         │                           │
         │ 1                         │ 1
         ▼ N                         ▼ N
┌─────────────────────────────────────────────┐
│              BLOOD_REQUESTS                  │
├─────────────────────────────────────────────┤
│ *request_id (PK)                            │
│  patient_id (FK)                            │
│  hospital_id (FK)                           │
│  requester_name                             │
│  blood_group_requested (FK)                 │
│  units_requested                            │
│  units_approved                             │
│  urgency_level                              │
│  required_date                              │
│  approved_by (FK to admins)                 │
│  issued_by (FK to admins)                   │
│  status                                     │
└─────────────────┬───────────────────────────┘
                  │
                  │ 1
                  ▼ N
┌─────────────────────────────────────────────┐
│              BLOOD_ISSUES                    │
├─────────────────────────────────────────────┤
│ *issue_id (PK)                              │
│  request_id (FK)                            │
│  donation_id (FK)                           │
│  patient_id (FK)                            │
│  blood_group (FK)                           │
│  units_issued                               │
│  issued_to                                  │
│  issued_by (FK to admins)                   │
│  receiver_name                              │
│  receiver_phone                             │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│           NOTIFICATION_LOGS                  │
├─────────────────────────────────────────────┤
│ *notification_id (PK)                       │
│  notification_type                          │
│  title                                      │
│  message                                    │
│  severity                                   │
│  related_table                              │
│  related_id                                 │
│  is_read                                    │
│  read_by (FK to admins)                     │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│             ACTIVITY_LOGS                    │
├─────────────────────────────────────────────┤
│ *log_id (PK)                                │
│  user_id (FK to admins)                     │
│  user_type                                  │
│  action                                     │
│  table_name                                 │
│  record_id                                  │
│  old_values (JSON)                          │
│  new_values (JSON)                          │
│  ip_address                                 │
│  description                                │
└─────────────────────────────────────────────┘
```

## 5.2 Relationships Summary

| Entity 1 | Relationship | Entity 2 | Cardinality |
|----------|--------------|----------|-------------|
| blood_groups | has | donors | 1:N |
| blood_groups | has | blood_stock | 1:1 |
| blood_groups | has | donations | 1:N |
| blood_groups | has | blood_requests | 1:N |
| admins | registers | donors | 1:N |
| admins | approves | blood_requests | 1:N |
| admins | issues | blood_issues | 1:N |
| donors | makes | donations | 1:N |
| hospitals | admits | patients | 1:N |
| hospitals | makes | blood_requests | 1:N |
| patients | has | blood_requests | 1:N |
| blood_requests | results_in | blood_issues | 1:N |

---

# 6. Normalization

## 6.1 First Normal Form (1NF)

**Definition**: A relation is in 1NF if:
- All attributes contain only atomic (indivisible) values
- Each column contains values of a single type
- Each row is unique (has a primary key)

**Application in BBMS:**

❌ **Unnormalized Example:**
```
DONOR(donor_id, name, phone_numbers, address_full)
-- phone_numbers: "9876543210, 9876543211" (multi-valued)
-- address_full: "123 Main St, Mumbai, Maharashtra, 400001" (composite)
```

✅ **1NF Applied:**
```
DONOR(donor_id, first_name, last_name, phone, address, city, state, pincode)
-- Each attribute is atomic
-- Phone stored separately; address split into components
```

## 6.2 Second Normal Form (2NF)

**Definition**: A relation is in 2NF if:
- It is in 1NF
- All non-key attributes are fully functionally dependent on the primary key
- No partial dependencies exist

**Application in BBMS:**

❌ **Before 2NF:**
```
DONATION(donation_id, donor_id, donor_name, donor_phone, blood_group, blood_group_name, units)
-- donor_name, donor_phone → depend only on donor_id (partial dependency)
-- blood_group_name → depends only on blood_group (partial dependency)
```

✅ **2NF Applied:**
```
DONOR(donor_id, first_name, last_name, phone, blood_group)
BLOOD_GROUP(group_code, group_name, can_donate_to, can_receive_from)
DONATION(donation_id, donor_id, blood_group, units, donation_date)
-- Foreign keys: donor_id → DONOR, blood_group → BLOOD_GROUP
```

## 6.3 Third Normal Form (3NF)

**Definition**: A relation is in 3NF if:
- It is in 2NF
- No transitive dependencies exist
- Non-key attributes depend only on the primary key

**Application in BBMS:**

❌ **Before 3NF:**
```
BLOOD_REQUEST(request_id, hospital_id, hospital_name, hospital_city, blood_group, units)
-- hospital_name, hospital_city → depend on hospital_id (transitive dependency)
```

✅ **3NF Applied:**
```
HOSPITAL(hospital_id, hospital_name, city, phone, is_verified)
BLOOD_REQUEST(request_id, hospital_id, blood_group, units, urgency_level)
-- Foreign key: hospital_id → HOSPITAL
```

## 6.4 Functional Dependencies

### DONORS Table
```
donor_id → first_name, last_name, date_of_birth, gender, blood_group, phone, 
           email, address, city, state, pincode, weight, is_eligible, 
           total_donations, last_donation_date, status, registered_by
```

### DONATIONS Table
```
donation_id → donor_id, blood_group, units_donated, donation_date, 
              hemoglobin_level, blood_pressure, bag_number, expiry_date, 
              test_result, status
```

### BLOOD_REQUESTS Table
```
request_id → patient_id, hospital_id, requester_name, blood_group_requested,
             units_requested, units_approved, urgency_level, required_date,
             approved_by, issued_by, status
```

### BLOOD_STOCK Table
```
group_code → units_available, units_reserved, minimum_threshold, maximum_capacity
```

---

# 7. Data Flow Diagrams

## 7.1 Level 0 DFD (Context Diagram)

```
                    ┌──────────────┐
     Donor Info     │              │     Stock Info
    ────────────────►              ├─────────────────►
                    │              │
     Donation       │     BBMS     │     Notifications
    ────────────────►              ├─────────────────►
                    │              │
     Blood Request  │              │     Blood Issue
    ────────────────►              ├─────────────────►
                    │              │
                    └──────────────┘
                          ▲
                          │
                          │ Login/Commands
                          │
                    ┌─────┴─────┐
                    │   ADMIN   │
                    └───────────┘
```

## 7.2 Level 1 DFD

```
┌─────────────────────────────────────────────────────────────────────┐
│                           BLOOD BANK MANAGEMENT SYSTEM              │
│                                                                     │
│    ┌─────────┐                                      ┌─────────┐    │
│    │  DONOR  │                                      │  ADMIN  │    │
│    └────┬────┘                                      └────┬────┘    │
│         │                                                │         │
│         │ Donor Registration                             │         │
│         ▼                                                ▼         │
│    ┌─────────────┐    ┌─────────────┐    ┌─────────────────────┐  │
│    │   1.0       │    │    2.0      │    │        3.0          │  │
│    │  Register   │───►│   Manage    │◄───│     Authenticate    │  │
│    │   Donor     │    │   Donors    │    │       Admin         │  │
│    └─────────────┘    └──────┬──────┘    └─────────────────────┘  │
│                              │                                     │
│                              ▼                                     │
│    ┌─────────────┐    ┌─────────────┐    ┌─────────────────────┐  │
│    │    4.0      │    │    5.0      │    │        6.0          │  │
│    │   Record    │───►│   Manage    │───►│     Generate        │  │
│    │  Donation   │    │   Stock     │    │     Reports         │  │
│    └─────────────┘    └──────┬──────┘    └─────────────────────┘  │
│                              │                                     │
│                              ▼                                     │
│    ┌─────────────┐    ┌─────────────┐    ┌─────────────────────┐  │
│    │    7.0      │    │    8.0      │    │        9.0          │  │
│    │   Process   │───►│   Issue     │───►│      Log            │  │
│    │   Request   │    │   Blood     │    │    Activity         │  │
│    └─────────────┘    └─────────────┘    └─────────────────────┘  │
│                                                     │              │
│                                                     ▼              │
│                                          ┌─────────────────────┐  │
│                                          │     DATABASE        │  │
│                                          │   ┌───────────┐     │  │
│                                          │   │  Tables   │     │  │
│                                          │   │  Views    │     │  │
│                                          │   │ Triggers  │     │  │
│                                          │   │ Procedures│     │  │
│                                          │   └───────────┘     │  │
│                                          └─────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

## 7.3 Process Descriptions

| Process | Input | Output | Description |
|---------|-------|--------|-------------|
| 1.0 Register Donor | Donor info | Donor record | Validates and stores donor data |
| 2.0 Manage Donors | Donor queries | Donor data | Search, update, manage donors |
| 3.0 Authenticate | Credentials | Session | Validates admin login |
| 4.0 Record Donation | Donation info | Donation record | Records blood donation |
| 5.0 Manage Stock | Stock changes | Updated stock | Auto-updates via triggers |
| 6.0 Generate Reports | Report request | Report data | Uses views for reports |
| 7.0 Process Request | Blood request | Approval status | Validates and approves requests |
| 8.0 Issue Blood | Issue request | Issue record | Tracks blood issuance |
| 9.0 Log Activity | User action | Log entry | Audit trail |

---

# 8. Use Case Descriptions

## 8.1 Use Case Diagram (Text)

```
                        ┌─────────────────────────────────────┐
                        │       BLOOD BANK MANAGEMENT         │
                        │             SYSTEM                   │
                        │                                     │
        ┌───────┐       │   ┌─────────────────────────┐      │
        │ ADMIN │───────┼───│ UC1: Login to System    │      │
        └───┬───┘       │   └─────────────────────────┘      │
            │           │                                     │
            │           │   ┌─────────────────────────┐      │
            ├───────────┼───│ UC2: Register Donor     │      │
            │           │   └─────────────────────────┘      │
            │           │                                     │
            │           │   ┌─────────────────────────┐      │
            ├───────────┼───│ UC3: Record Donation    │      │
            │           │   └─────────────────────────┘      │
            │           │                                     │
            │           │   ┌─────────────────────────┐      │
            ├───────────┼───│ UC4: View Blood Stock   │      │
            │           │   └─────────────────────────┘      │
            │           │                                     │
            │           │   ┌─────────────────────────┐      │
            ├───────────┼───│ UC5: Process Request    │      │
            │           │   └─────────────────────────┘      │
            │           │                                     │
            │           │   ┌─────────────────────────┐      │
            ├───────────┼───│ UC6: Issue Blood        │      │
            │           │   └─────────────────────────┘      │
            │           │                                     │
            │           │   ┌─────────────────────────┐      │
            └───────────┼───│ UC7: Generate Reports   │      │
                        │   └─────────────────────────┘      │
                        │                                     │
                        └─────────────────────────────────────┘
```

## 8.2 Use Case: Register Donor (UC2)

| Field | Description |
|-------|-------------|
| **Use Case ID** | UC2 |
| **Name** | Register Donor |
| **Actor** | Admin |
| **Description** | Register a new blood donor in the system |
| **Preconditions** | Admin is logged in |
| **Trigger** | Admin clicks "Add Donor" |

**Main Flow:**
1. Admin enters donor personal details
2. System validates blood group exists
3. System checks for duplicate phone number
4. System validates age (18-65) and weight (≥45kg)
5. System calls sp_RegisterDonor stored procedure
6. Procedure creates donor record
7. Activity is logged
8. Success message displayed

**Alternative Flow:**
- 3a. Phone number already exists → Error message
- 4a. Age/weight invalid → Error message

**Postconditions:** Donor record created, activity logged

## 8.3 Use Case: Record Donation (UC3)

| Field | Description |
|-------|-------------|
| **Use Case ID** | UC3 |
| **Name** | Record Donation |
| **Actor** | Admin |
| **Description** | Record a blood donation from an eligible donor |
| **Preconditions** | Donor is registered and eligible |
| **Trigger** | Donor arrives for donation |

**Main Flow:**
1. Admin selects donor
2. System verifies eligibility
3. Admin enters health parameters (hemoglobin, BP, etc.)
4. Admin generates bag number
5. System calls sp_AddDonation procedure
6. **Trigger fires**: Updates donor's total donations
7. **Trigger fires**: Sets donor ineligible for 90 days
8. Success message with expiry date displayed

**Alternative Flow:**
- 2a. Donor not eligible → Show last donation date
- 3a. Hemoglobin < 12.5 → Reject donation

## 8.4 Use Case: Approve Blood Request (UC5)

| Field | Description |
|-------|-------------|
| **Use Case ID** | UC5 |
| **Name** | Approve Blood Request |
| **Actor** | Admin |
| **Description** | Approve or reject a blood request |
| **Preconditions** | Request exists with status 'Pending' |

**Main Flow:**
1. Admin views pending requests (via view_pending_requests)
2. Admin selects a request
3. System shows available stock
4. Admin enters units to approve
5. System calls sp_ApproveBloodRequest
6. **Transaction**: Stock reserved
7. **Trigger fires**: Notification created
8. Request status updated

**Alternative Flow:**
- 4a. Insufficient stock → Partial approval or rejection

---

# 9. Database Schema Reference

## 9.1 Tables Summary

| Table | Primary Key | Foreign Keys | Description |
|-------|-------------|--------------|-------------|
| blood_groups | group_code | - | Blood type master data |
| admins | admin_id | - | System administrators |
| donors | donor_id | blood_group, registered_by | Blood donors |
| hospitals | hospital_id | - | Partner hospitals |
| patients | patient_id | blood_group_needed, hospital_id | Patients needing blood |
| blood_stock | stock_id | group_code | Current stock levels |
| donations | donation_id | donor_id, blood_group | Donation records |
| blood_requests | request_id | patient_id, hospital_id, blood_group_requested | Blood requests |
| blood_issues | issue_id | request_id, donation_id, patient_id | Issue records |
| notification_logs | notification_id | read_by | System notifications |
| activity_logs | log_id | user_id | Audit trail |
| donor_eligibility_logs | log_id | donor_id, checked_by | Eligibility history |

## 9.2 Key Constraints

```sql
-- Foreign Key Examples
FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) 
    ON DELETE RESTRICT ON UPDATE CASCADE

FOREIGN KEY (registered_by) REFERENCES admins(admin_id) 
    ON DELETE SET NULL ON UPDATE CASCADE

FOREIGN KEY (donor_id) REFERENCES donors(donor_id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
```

---

# 10. Advanced DBMS Features

## 10.1 Triggers

| Trigger | Event | Purpose |
|---------|-------|---------|
| trg_after_donation_available | AFTER UPDATE on donations | Updates stock when donation marked available |
| trg_after_donation_insert | AFTER INSERT on donations | Updates donor statistics |
| trg_low_stock_alert | AFTER UPDATE on blood_stock | Creates notification when stock low |
| trg_donation_expiry_check | BEFORE UPDATE on donations | Auto-marks expired units |
| trg_after_donation_expiry | AFTER UPDATE on donations | Creates expiry notification |
| trg_after_request_approved | AFTER UPDATE on blood_requests | Reserves stock on approval |
| trg_urgent_request_notification | AFTER INSERT on blood_requests | Alerts for urgent requests |
| trg_donor_eligibility_after_donation | AFTER INSERT on donations | Sets 90-day ineligibility |

## 10.2 Stored Procedures

| Procedure | Parameters | Purpose |
|-----------|------------|---------|
| sp_RegisterDonor | IN: donor data, OUT: status | Register new donor with validation |
| sp_AddDonation | IN: donation data, OUT: status | Record donation with health checks |
| sp_ApproveBloodRequest | IN: request_id, units, OUT: status | Approve request with stock check |
| sp_RejectBloodRequest | IN: request_id, reason, OUT: status | Reject request with reason |
| sp_IssueBlood | IN: issue data, OUT: status | Complete blood issuance |
| sp_GenerateStockSummary | - | Generate stock report |
| sp_UpdateDonorEligibility | - | Batch update eligibilities |
| sp_CheckExpiringBlood | IN: days_threshold | Find expiring units |
| sp_GetDashboardStats | - | Dashboard statistics |
| sp_SearchDonors | IN: filters | Search donors |

## 10.3 Views

| View | Purpose |
|------|---------|
| view_available_blood | Current stock with availability status |
| view_pending_requests | Pending requests sorted by urgency |
| view_donation_summary | All donations with donor info |
| view_blood_group_statistics | Statistics per blood group |
| view_donor_directory | Active donor listing |
| view_patient_records | Patient information |
| view_recent_activity | Recent system activity |
| view_unread_notifications | Unread alerts |
| view_blood_issue_history | Issue records |
| view_expiring_blood | Units expiring soon |

---

# 11. Installation Guide

## 11.1 Prerequisites

- XAMPP (Apache + MySQL 8+)
- PHP 8.0+
- Web browser

## 11.2 Installation Steps

1. **Start XAMPP**
   ```
   Start Apache and MySQL services
   ```

2. **Access phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

3. **Run SQL Scripts in Order**
   ```sql
   -- In MySQL/phpMyAdmin, run these in sequence:
   SOURCE database/schema.sql;      -- Creates tables
   SOURCE database/views.sql;       -- Creates views
   SOURCE database/triggers.sql;    -- Creates triggers
   SOURCE database/stored_procedures.sql;  -- Creates procedures
   SOURCE database/sample_data.sql; -- Inserts sample data
   ```

4. **Access Application**
   ```
   http://localhost/bbms
   ```

5. **Default Login**
   ```
   Username: admin
   Password: admin123
   ```

---

# 12. Conclusion

The Blood Bank Management System successfully implements comprehensive DBMS concepts to create a robust, efficient, and maintainable application:

## 12.1 Achievements

✅ **Complete Database Normalization**
- Achieved 3NF eliminating all redundancy
- Proper entity separation with foreign key relationships
- Data integrity maintained through constraints

✅ **Automated Operations via Triggers**
- Stock automatically updates on donations and issues
- Low stock alerts generated automatically
- Donor eligibility managed automatically
- Expiry tracking and notifications

✅ **Business Logic via Stored Procedures**
- Complex operations encapsulated
- Transaction management for data consistency
- Validation at database level
- Reusable across applications

✅ **Simplified Reporting via Views**
- Pre-built queries for common operations
- Real-time dashboards
- Security through abstraction

✅ **Complete Audit Trail**
- All activities logged
- JSON storage for before/after values
- IP address tracking

## 12.2 Future Enhancements

1. **Blood Compatibility Matrix**: Automatic compatible blood group suggestions
2. **Donor Communication**: SMS/Email integration for donation reminders
3. **Mobile Application**: Donor app for scheduling donations
4. **Analytics Dashboard**: Predictive analysis for demand forecasting
5. **Multi-branch Support**: Support for multiple blood bank locations

## 12.3 Learning Outcomes

This project demonstrates practical application of:
- Database design principles
- Normalization theory (1NF → 3NF)
- SQL programming (DDL, DML, DCL)
- Stored procedures and functions
- Trigger programming
- View creation and usage
- Transaction management
- PHP-MySQL integration

---

**Document Version**: 2.0  
**Last Updated**: November 2024  
**Author**: BBMS Development Team
