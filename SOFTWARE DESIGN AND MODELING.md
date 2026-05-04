# Phase III: Software Design and Modeling

Project: **Blood Donation System**


## 1. Software Architecture

### 1.1 System Architecture
The Blood Donation System is a role-based web application with three main stakeholders: **Donor**, **Blood Bank Staff**, and **Hospital Staff**.

High-level request flow:
1. A stakeholder performs an action on the frontend (browser UI).
2. The request is sent to a role-specific PHP page (controller-like layer).
3. The page validates session/role and input.
4. The page calls service-layer functions (`services/*_service.php`).
5. Service layer reads/writes the MySQL database via PDO.
6. The result is returned to the page.
7. The frontend renders feedback (success/error/data table/dashboard cards).

Example architecture flow (project-specific):
- Donor books appointment (Frontend).
- Request goes to `donor/book_appointment.php`.
- `donor_service.php` validates date/slot/conflicts.
- Database inserts appointment with `pending` status.
- Donor receives confirmation in dashboard/history.

### 1.2 Component Diagram (UML) — Quantity: 1
![Component Diagram](./component_diagram.svg)



Component-side explanation (format requested):
- **Left side (Actors/System Entry):**
  - `Donor`, `Bank Staff`, `Hospital Staff`, `System Admin` interact with the `Blood Donation Web System`.
- **Middle side (Core Components):**
  - `Auth`: login, registration, role session checks.
  - `Donor Management`: donor profile and donor-facing operations.
  - `Appointment Management`: booking, approval, completion, cancellation.
  - `Inventory Management`: blood stock create/update/read.
  - `Request Management`: hospital blood request lifecycle.
  - Each core component calls a `Data Access` interface.
- **Right side (Technical Infrastructure):**
  - `Security`: authentication and access control concerns.
  - `Persistence`: database read/write handling.
  - `Database`: MySQL storage for users, donors, banks, hospitals, appointments, inventory, and requests.

## 2. Detailed Design

### 2.1 Class Diagram 
![Class Diagram](./class_diagram.svg)


### 2.2 Sequence Diagrams 

#### Sequence 1: Donor books appointment

![Sequence Diagram 1](./sequence_diagram_1.svg)

#### Sequence 2: Bank completes appointment

![Sequence Diagram 2](./sequence_diagram_2.svg)

#### Sequence 3: Hospital request fulfillment by bank
![Sequence Diagram 3](./sequence_diagram_3.svg)







## 3. Modeling

### 3.1 Use Case Diagram — (all users included)

![Use Case Diagram](./use_case_diagram.svg)


### 3.2 Activity Diagrams

![Activity Diagram 1](./activity_diagram_1.svg)

![Activity Diagram 2](./activity_diagram_2.svg)



### 3.3 State Diagrams 

![State Diagram 1](./state_diagram_1.svg)




#### State Diagram 2: Blood request lifecycle
![State Diagram 2](./state_diagram_2.svg)

