# Phase II: User Requirements and Application Specifications

Project: **Blood Donation System**

## 1. Chosen Development Model
### 1.1 Selected Model
**Agile (Scrum-inspired iterative model)**

### 1.2 Justification
The team selected Agile because:
- Requirements changed during development (for example adding time slots, donor ID/address, search features).
- The project has multiple user roles (donor, bank, hospital), so frequent feedback is important.
- Iterative delivery allows small, testable improvements instead of one large release.
- Risk is reduced because each sprint produces a working increment.

## 2. User Requirements

### 2.1 Stakeholders (WHO is involved)
1. **Donors (End Users)**
   - Role: Register, manage profile, book/cancel appointments.
   - Interest: Fast booking, clear eligibility rules, secure personal data.
2. **Blood Bank Staff (End Users / Operational Users)**
   - Role: Manage appointments, donor info, inventory, and hospital requests.
   - Interest: Reliable workflow, real-time stock visibility, safe actions.
3. **Hospital Staff (End Users / Clients)**
   - Role: Request blood units and monitor request status.
   - Interest: Quick processing and transparent request tracking.
4. **System Administrator (Project Owner / Maintainer)**
   - Role: Maintain app configuration, user access, and system health.
   - Interest: Stability, security, maintainability.
5. **Development Team**
   - Role: Design, implement, test, and document the system.
   - Interest: Clean architecture, reusable code, test coverage.
6. **Course Instructor / Evaluator**
   - Role: Reviews project completeness and quality.
   - Interest: Correct application of software engineering practices.

### 2.2 User Stories (WHAT they need and WHY)
1. **As a donor, I want to register with my blood group so that the system can match my donation type correctly.**
2. **As a donor, I want to add my phone, donor ID, and address so that blood bank staff can contact and verify me.**
3. **As a donor, I want to view my eligibility status so that I know whether I can donate now or must wait.**
4. **As a donor, I want to book an appointment with date and time slot so that I can plan my visit.**
5. **As a donor, I want to see appointment history so that I can track my donation activity.**
6. **As a donor, I want to cancel pending appointments so that I can handle schedule conflicts.**
7. **As a blood bank staff member, I want to review and update appointment status so that donor flow is controlled.**
8. **As a blood bank staff member, I want to search donor records quickly so that I can find the right person without delay.**
9. **As a blood bank staff member, I want inventory to auto-increase after completed donation so that stock remains accurate.**
10. **As hospital staff, I want to submit blood requests to a bank so that urgent patient needs can be addressed.**
11. **As hospital staff, I want to track request status so that I can coordinate treatment planning.**
12. **As an administrator, I want role-based access control so that each user sees only permitted pages and actions.**

## 3. Functional Requirements (What the system must do)

### 3.1 Functional Requirements List (22)
**FR-01** The system shall allow user registration for roles: donor, bank, hospital.

**FR-02** The system shall validate required fields during registration.

**FR-03** The system shall hash passwords before storing them.

**FR-04** The system shall allow login using email and password.

**FR-05** The system shall redirect authenticated users to role-specific dashboards.

**FR-06** The system shall block unauthorized role access to protected pages.

**FR-07** The donor profile shall store blood group, phone, city, donor ID, and address.

**FR-08** The donor dashboard shall display eligibility based on last donation date.

**FR-09** The donor shall be able to search blood banks.

**FR-10** The donor shall be able to book an appointment with date and time slot.

**FR-11** The system shall prevent booking in the past.

**FR-12** The system shall prevent conflicting active appointments for the same donor date/slot.

**FR-13** The donor shall be able to view appointment history.

**FR-14** The donor shall be able to cancel pending appointments.

**FR-15** Bank staff shall view all assigned appointments with donor details.

**FR-16** Bank staff shall search/filter appointments by donor info, date, status, blood group, and time slot.

**FR-17** Bank staff shall update appointment status to approved, completed, or cancelled.

**FR-18** On completed appointment, the system shall update donor last donation date.

**FR-19** On completed appointment, the system shall increment bank inventory for donor blood group.

**FR-20** Hospital staff shall create blood requests specifying bank, blood group, and units.

**FR-21** Bank staff shall approve/reject/fulfill blood requests.

**FR-22** On request fulfillment, the system shall verify stock and decrement inventory atomically.

### 3.2 Functional Acceptance Criteria (5 key features)

#### Feature A: User Login
Acceptance Criteria:
- User enters valid email and password.
- System verifies credentials against stored account.
- System starts authenticated session.
- User is redirected to correct dashboard by role.
- Invalid credentials show an error message without logging in.

#### Feature B: Donor Appointment Booking
Acceptance Criteria:
- Donor selects bank, date, and time slot.
- Date must be today or future.
- No conflicting active appointment exists for same donor/date/slot.
- Appointment is stored with status `pending`.
- Donor sees success confirmation and can view it in history.

#### Feature C: Bank Appointment Management
Acceptance Criteria:
- Bank sees appointments for its own bank only.
- Bank can perform allowed transitions: pending->approved, pending/approved->cancelled, pending/approved->completed.
- Completing appointment updates donor last donation date.
- Completing appointment increases matching inventory by 1 unit.
- Invalid transition attempt does not corrupt data.

#### Feature D: Hospital Blood Request
Acceptance Criteria:
- Hospital selects target bank, blood group, and units.
- Units must be a positive integer.
- New request is saved with status `pending`.
- Hospital can view request in history with timestamp.
- Request status updates are visible after bank action.

#### Feature E: Request Fulfillment with Stock Check
Acceptance Criteria:
- Bank can fulfill only pending/approved requests.
- System checks available inventory for requested blood group.
- If stock is insufficient, fulfillment is rejected with message.
- If stock is sufficient, request status changes to `fulfilled`.
- Inventory is reduced by requested units in one transaction.

## 4. Non-Functional Requirements (How well the system should work)

### 4.1 Non-Functional Requirements List (22)
**NFR-01 (Performance):** Standard page load should be under 2 seconds on local network.

**NFR-02 (Performance):** Search/filter operations should return results under 2 seconds for typical dataset sizes.

**NFR-03 (Performance):** Appointment status updates should complete under 3 seconds.

**NFR-04 (Reliability):** Core workflows (login, booking, request handling) should complete without unhandled errors.

**NFR-05 (Reliability):** Transactional operations shall rollback on failure.

**NFR-06 (Availability):** System should be available during planned lab/demo hours.

**NFR-07 (Usability):** Navigation should be role-oriented and understandable for first-time users.

**NFR-08 (Usability):** Forms should provide clear error messages for invalid input.

**NFR-09 (Usability):** Interface should be responsive for desktop and mobile widths.

**NFR-10 (Security):** Passwords shall never be stored in plain text.

**NFR-11 (Security):** Access to role pages shall require authentication.

**NFR-12 (Security):** SQL operations shall use prepared statements.

**NFR-13 (Maintainability):** DB CRUD logic shall be centralized in service-layer functions.

**NFR-14 (Maintainability):** Code changes for one module should minimize side effects in others.

**NFR-15 (Scalability):** Schema shall support growing records for users, appointments, inventory, requests.

**NFR-16 (Data Integrity):** Foreign keys must enforce valid cross-table references.

**NFR-17 (Data Integrity):** Enumerated status fields shall restrict invalid states.

**NFR-18 (Compatibility):** System shall run on PHP + MySQL in XAMPP environment.

**NFR-19 (Portability):** Configuration should allow deployment on another LAMP-like host with minimal changes.

**NFR-20 (Testability):** Service-layer behavior should be testable independently of UI.

**NFR-21 (Auditability):** Important actions should be traceable through timestamps/status history.

**NFR-22 (Documentation):** Project shall include updated README and engineering documents.

### 4.2 Non-Functional Acceptance Criteria (5 key quality attributes)

#### Quality A: Performance
Acceptance Criteria:
- 90% of standard pages load in <= 2 seconds in local environment.
- Search/filter actions return results in <= 2 seconds for normal test data.
- Status update actions complete in <= 3 seconds.

#### Quality B: Reliability and Consistency
Acceptance Criteria:
- No unhandled PHP fatal error occurs during core user flows in test run.
- Transactional operations preserve consistency after simulated failure.
- Duplicate or invalid transitions do not produce corrupted status records.

#### Quality C: Security
Acceptance Criteria:
- Password column stores hashed values only.
- Unauthorized users are redirected from protected pages.
- SQL queries in services use prepared statements (no string-concatenated raw user input).

#### Quality D: Usability
Acceptance Criteria:
- New user can complete register->login->main task flow without external help.
- Required field validation appears near form controls.
- Layout remains readable and usable on common mobile width (>= 360px).

#### Quality E: Maintainability
Acceptance Criteria:
- New CRUD logic is added in `services/*_service.php`, not page files.
- Page files primarily orchestrate request/response and rendering.
- Team can modify one module without breaking unrelated modules in smoke tests.

## 5. Application Specifications (How the system is built technically)

### 5.1 Architecture
The system follows a layered web architecture:
- **Presentation layer:** Role pages in `auth/`, `donor/`, `bank/`, `hospital/`
- **Application layer:** Bootstrap/auth/helpers in `includes/`
- **Service layer:** Business logic + database CRUD in `services/`
- **Data layer:** MySQL database defined in `sql/schema.sql`

Interaction flow:
1. User sends request from browser.
2. Role page validates session/role and input.
3. Role page calls service functions.
4. Service layer executes PDO queries/transactions.
5. Result returns to page and is rendered.

### 5.2 Database Model
Main tables:
- `users(id, name, email, password, role, phone, city, created_at)`
- `donors(id, blood_group, donor_id_number, address, last_donation_date)`
- `banks(id, address)`
- `hospitals(id, address)`
- `appointments(id, donor_id, bank_id, appointment_date, time_slot, status, created_at)`
- `inventory(id, bank_id, blood_group, units)`
- `requests(id, hospital_id, bank_id, blood_group, units_requested, status, requested_at)`

Relationships and constraints:
- `users` has 1:1 extension to donor/bank/hospital profile tables.
- `appointments` references donor and bank.
- `inventory` references bank and has unique `(bank_id, blood_group)`.
- `requests` references hospital and bank.
- ENUM constraints enforce valid roles, statuses, blood groups, and time slots.
- Foreign keys enforce referential integrity.

### 5.3 Technologies Used
- **PHP (Backend):** Simple server-side processing and broad compatibility with XAMPP.
- **MySQL (Database):** Relational model with constraints and transactional support.
- **PDO (DB Access):** Prepared statements and consistent DB interaction.
- **HTML/CSS/Bootstrap (Frontend):** Responsive and fast UI development.
- **JavaScript (minimal):** Basic client-side behavior when needed.
- **XAMPP (Environment):** Easy local deployment for development and testing.

### 5.4 User Interface Design
Current UI includes:
- Landing page with role navigation.
- Authentication pages (register/login).
- Donor dashboard + booking + appointment history.
- Bank dashboard + manage appointments + inventory + request actions.
- Hospital dashboard + create request + request history.

Design characteristics:
- Simple navigation with role-specific menus.
- Form-driven workflows with clear action buttons.
- Table-based management views for operational tasks.
- Responsive layout for desktop and mobile.

### 5.5 Security Measures
Implemented:
- Session-based authentication.
- Role-based authorization checks for page access.
- Password hashing for stored credentials.
- Prepared statements to reduce SQL injection risk.
- Output escaping helper for safe HTML rendering.

Recommended improvements (next phase):
- CSRF protection on all POST forms.
- Rate limiting for login attempts.
- Stronger password policy and account lockout.
- Centralized audit logging for critical actions.
- Encryption-at-rest strategy for sensitive fields if required by policy.

## 6. Scope Summary
In-scope:
- Multi-role authentication and dashboards.
- Donation appointments with time slots.
- Bank inventory management.
- Hospital blood request lifecycle.

Out-of-scope for this phase:
- Payment integration.
- SMS/email notification services.
- Advanced analytics and ML forecasting.

## 7. Traceability Summary
- User Stories -> mapped by FR-01..FR-22.
- Quality expectations -> mapped by NFR-01..NFR-22.
- Acceptance criteria -> defined for 5 functional and 5 non-functional key areas.

This document satisfies Phase II structure and quantity expectations:
- User stories: **12**
- Functional requirements: **22**
- Non-functional requirements: **22**
- Acceptance criteria sets: **5 functional + 5 non-functional**
