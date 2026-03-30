# National Blood Donation Platform (PHP + MySQL)

A role-based blood donation prototype built with plain PHP and MySQL.

The platform connects three actor types:
- `donor`: books donation appointments
- `bank`: manages appointments, inventory, and hospital requests
- `hospital`: submits and tracks blood requests

## Team Information

### Team Name
- `TeamC2`

### Team Leader
- **`Evi Shedula`** (`@evii123`) - **Team Leader**

### Team Members
- **`Evi Shedula`** (`@evii123`) - `eshedula23@epoka.edu.al` - **Team Leader**
- **`Alma Hado`** (`@Alma140666`) - `ahado23@epoka.edu.al` - **Team Member**
- **`Elda Cami`** (`@ecami23epoka>`) - `ecami23@epoka.edu.al` - **Team Member**
- **`Samed Kokici`** (`@samedkokicii>`) - `skokici23@epoka.edu.al` - **Team Member**
- **`Frenklin Mehmeti`** (`@FM12344>`) - `fmehmeti23@epoka.edu.al` - **Team Member**
- **`Donald Zekaj`** (`@donaldzekaj23>`) - `dzekaj23@epoka.edu.al` - **Team Member**



## Project Details

### Problem Statement
Blood donation ecosystems often face coordination problems between donors, blood banks, and hospitals. Donors may not know where urgent need exists, hospitals struggle to request and track blood units efficiently, and banks need a simple way to manage appointments and inventory status in one place.

### Proposed Solution
This project provides a role-based web platform where:
- donors can register and book donation appointments
- banks can manage appointments and blood inventory
- hospitals can submit and track blood requests

The system centralizes workflows and status tracking to reduce delays and improve visibility across all actors.

### Project Scope
Aim:
- Build a functional prototype that streamlines blood donation coordination among donors, banks, and hospitals.

Objectives:
- enable role-based authentication and dashboards
- support donor appointment booking and cancellation
- support bank inventory and appointment processing
- support hospital blood request submission and tracking
- provide clear status transitions for appointments and requests

### Application Description
Application:
- A PHP + MySQL web application running on XAMPP.

Users:
- Donors
- Blood Banks
- Hospitals

Key features:
- role-based login and dashboard routing
- donor eligibility checks and appointment booking with time slots
- blood bank inventory CRUD and request fulfillment workflows
- hospital request submission, history, and inventory overview
- service-layer architecture for centralized business/data logic

## Roles and Task Distribution

### Assigned Roles
- **`Evi Shedula`** (`@evii123`) – `eshedula23@epoka.edu.al`  
  **Team Leader / Project Coordinator**
  - Repository creation and management  
  - Task planning and coordination  
  - Final documentation merging and submission  
  - Oversight of system integration
  - Database Creation
  - Create Project Structure

---

- **`Alma Hado`** (`@Alma1406`) – `hado.alma14@gmail.com`  
  **Hospital Module Developer**
  - Blood request submission system  
  - Request tracking and status updates  
  - Viewing bank inventory  
  - Integration with bank workflows  

---

- **`Elda Cami`** (`@ecami23epoka`) – `ecami23@epoka.edu.al`  
  **Authentication & User Management**
  - Login and registration system  
  - Role-based authentication and routing  
  - Session handling and access control  

---

- **`Samed Kokici`** (`@samedkokicii`) – `skokici23@epoka.edu.al`  
  **Donor Module Developer**
  - Donor features implementation  
  - Appointment booking and cancellation  
  - Eligibility checks (90-day rule)  
  - Donor history and dashboard  

---

- **`Frenklin Mehmeti`** (`@FM12344`) – `fmehmeti23@epoka.edu.al`  
  **Blood Bank Module Developer**
  - Inventory management (CRUD operations)  
  - Appointment processing (approve/complete/cancel)  
  - Dashboard metrics and low-stock alerts  
  - Blood stock updates after donations  

---

- **`Donald Zekaj`** (`@donaldzekaj23`) – `dzekaj23@epoka.edu.al`  
  **Frontend Developer**
  - UI design using Bootstrap 5  
  - Donor, hospital, and bank dashboards  
  - Form validation and user experience improvements  

### Shared Roles
- Documentation: shared by all members
- Code Review: shared by all members
- Feature Validation: shared by all members

