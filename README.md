# MediTrack

## Clinic Management System

MediTrack is a clinic management system developed as a capstone project for **Mandaue City College**. The system is designed to help streamline school clinic operations while providing students and school staff with convenient access to basic clinic services.

The system is divided into two main sides:

* **Clinic Side** – used by clinic personnel to manage patient information, appointments, medicines, consultations, reports, and referrals.
* **Patient Side** – used by students and school staff to book appointments, view appointment status, receive notifications, and access their profile.

## System Modules

### 🏥 Clinic Side

The clinic side provides the main tools for managing school clinic operations.

#### Dashboard

Provides an overview of clinic activities through summarized information, key statistics, and visual data.

#### Student Profiles

Allows clinic personnel to manage and view student information and patient records.

#### School Staff Profiles

Allows clinic personnel to manage information for teaching and non-teaching staff.

#### Appointments

Allows clinic personnel to review, approve, reject, reschedule, and cancel patient appointment requests.

#### Medicine Inventory

Allows clinic personnel to manage medicines available in the clinic, including inventory information, stock status, and medicine-related details.

#### Consultations

Allows clinic personnel to record and manage patient consultation information, including complaints, vital signs, and other relevant medical details.

#### Monthly Reports

Provides summarized clinic records and statistics for a specific month.

#### Referrals

Allows clinic personnel to manage patient referral records.

> 🚧 Consultation records, Monthly Reports, Referrals, and PDF report generation are still under development.

---

### 👤 Patient Side

The patient side provides students and school staff with access to basic clinic services.

#### Book Appointment

Allows patients to submit appointment requests by specifying their purpose of visit, preferred date, and preferred time.

#### View Appointments

Allows patients to view their appointment records and track their current appointment status, including Pending, Approved, Completed, Rejected, and Cancelled appointments.

#### Notifications

Provides patients with notifications regarding appointment updates and other clinic-related information.

> 🚧 In-app notification backend integration is still under development.

#### Profile

Allows patients to view and manage their personal information.

> 🚧 Profile backend integration is still under development.

---

## Authentication

MediTrack includes an account registration and login system for students and school staff.

### Registration

Users can register using their school identification information and create an account using their email address and password.

### Login

Users can log in using their registered email address and password.

### Forgot Password

A password recovery feature is planned to allow users to securely reset their password through a dedicated link sent to their registered email address.

> 🚧 Forgot Password / Email Password Reset is still under development.

---

## Technologies Used

* HTML
* CSS
* JavaScript
* PHP
* MySQL
* XAMPP
* Git
* GitHub

## Project Status

🚧 **MediTrack is currently under development.**

The core clinic management modules and appointment workflow are currently functional. The patient-side appointment booking and appointment tracking features have also been integrated with the database.

Current development priorities include:

* In-app notifications
* Patient profile integration
* Forgot password and email-based password reset
* Further consultation, reporting, and referral functionality
* PDF report generation
* Final testing and system-wide UI improvements
* Role-based access control and authorization

## Project Structure

```text
MediTrack
│
├── Clinic Side
│   ├── Dashboard
│   ├── Student Profiles
│   ├── School Staff Profiles
│   ├── Appointments
│   ├── Medicine Inventory
│   ├── Consultations
│   ├── Monthly Reports
│   └── Referrals
│
└── Patient Side
    ├── Book Appointment
    ├── View Appointments
    ├── Notifications
    └── Profile
```

## Development Progress

MediTrack is currently approximately **75% complete**. The core system structure, clinic-side management features, authentication, and appointment workflow are already established, while several supporting features and final integrations remain in development.
