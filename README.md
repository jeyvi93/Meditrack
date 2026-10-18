# MediTrack Clinic Management System

A web-based clinic management system developed as a capstone project for **Mandaue City College**. MediTrack streamlines school clinic operations by centralizing patient records, appointment management, medicine inventory, consultations, reports, and referrals while providing students and school staff with convenient access to clinic services.

## Overview

MediTrack provides two primary interfaces:

- **Clinic Side** — Enables authorized clinic personnel to manage clinic operations, patient records, appointments, medicine inventory, consultations, reports, and referrals.
- **Patient Side** — Allows students and school staff to book appointments, track appointment status, receive notifications, and manage their profiles.

The system includes account authentication, email verification, password recovery, role-based access, and Progressive Web App (PWA) support for convenient access across compatible devices.

## System Modules

### Clinic Side

#### Dashboard
- Displays summarized clinic activities and key statistics.
- Presents visual information to help clinic personnel monitor operations.

#### Student Profiles
- View and manage student information and patient records.
- Access relevant profile details for clinic services.

#### School Staff Profiles
- Manage teaching and non-teaching staff information.
- Maintain staff records used by the clinic.

#### Appointment Management
- Review incoming appointment requests.
- Approve or reject requests.
- Reschedule or cancel appointments.
- Update appointment statuses and manage appointment records.

#### Medicine Inventory
- Manage medicine records and inventory information.
- Monitor stock levels and medicine availability.
- Maintain medicine-related details.

#### Consultations
- Record and manage patient consultations.
- Document complaints, vital signs, and other relevant clinical information.

#### Monthly Reports
- Generate summarized clinic records and statistics for a selected month.
- Support the review of clinic activities and appointment records.

#### Referrals
- Create and manage patient referral records.
- Review referral information and maintain referral history.
- Support referral document viewing and printing where available.

### Patient Side

#### Appointment Booking
- Submit appointment requests by specifying the purpose of the visit, preferred date, and time.
- Receive updates as appointment requests are processed.

#### Appointment Tracking
- View appointment records and their current status.
- Track Pending, Approved, Completed, Rejected, and Cancelled appointments.

#### Notifications
- Receive clinic-related updates, including appointment notifications.
- Support push notifications on compatible devices and browsers.

#### Patient Profile
- Access personal account and profile information.
- Manage supported profile details.

## Authentication and Security

MediTrack includes an authentication system designed for students, teaching staff, non-teaching staff, and authorized clinic personnel.

- **Registration:** Supports account creation using school identification information and registered email addresses.
- **Login:** Authenticates users using their email addresses and passwords.
- **Email Verification:** Uses verification links sent through email to verify registered accounts.
- **Forgot Password:** Provides password recovery through a time-limited email reset link.
- **Password Security:** Stores passwords using PHP's password-hashing functionality.
- **Role-Based Access Control:** Restricts access to system functions according to assigned user roles.
- **Database Security:** Uses prepared statements for supported database operations.

## Progressive Web App (PWA)

MediTrack supports installation as a Progressive Web App on compatible devices and browsers.

- Add MediTrack to a device's home screen.
- Access the system through an app-like interface.
- Use a web app manifest and service worker.
- Support push notifications on compatible platforms and browsers.

Installation availability depends on the device, operating system, browser, and PWA compatibility.

## Technologies Used

| Technology | Purpose |
|---|---|
| HTML5 | Page structure |
| CSS3 | Styling and responsive layouts |
| JavaScript | Client-side interactions and functionality |
| PHP | Server-side processing and application logic |
| MySQL | Database management |
| XAMPP | Local development environment |
| PHPMailer | Email delivery |
| Firebase Cloud Messaging | Push notification delivery |
| Git | Version control |
| GitHub | Source code hosting |

## System Requirements

- A modern web browser.
- PHP-compatible server environment.
- MySQL or MariaDB database server.
- XAMPP or an equivalent PHP development environment.
- Internet access for email delivery, Firebase services, and other externally hosted resources.

PWA installation and push notification functionality require a supported environment and appropriate HTTPS configuration, except for browser-recognized development origins such as localhost.

## Installation and Setup

1. Install and start Apache and MySQL using XAMPP.
2. Clone or copy the MediTrack project into the server's web directory, such as `htdocs`.
3. Create the required MySQL database.
4. Import the project's database schema and initial data.
5. Configure the database connection in `config/database.php`.
6. Configure SMTP settings in `config/mail.php`.
7. Install PHP dependencies using Composer if they are not already installed.
8. Configure the required Firebase project settings for push notifications.
9. Start the application and open it through the configured local development URL.
10. Test registration, email verification, login, appointment workflows, password recovery, and role-based access.

**Important:** Keep database passwords, SMTP credentials, Firebase service-account private keys, and other secrets out of public repositories. Use dummy records for demonstrations and testing.

## Project Structure

```text
MediTrack/
├── actions/
├── api/
│   └── auth/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── clinic/
├── config/
├── patient/
├── templates/
├── vendor/
├── manifest.json
├── service-worker.js
├── login.php
└── index.php
```

*The structure above highlights the main application directories; the actual repository may contain additional files and subdirectories.*

## Project Objectives

MediTrack aims to:

- Digitize and centralize school clinic records.
- Improve appointment handling and status tracking.
- Simplify medicine inventory management.
- Support organized consultation and referral documentation.
- Improve communication between clinic personnel and patients.
- Provide secure, role-appropriate access to clinic services.
- Make clinic services more accessible through a responsive web application and PWA support.

## Project Status

MediTrack is the completed implementation of a school clinic management system, with its modules and integrations subject to final verification and acceptance testing.

Final validation should cover appointment workflows, medicine inventory operations, authentication, email verification, password recovery, notification delivery, role permissions, reporting, referrals, and PWA installation on supported devices.

## Project Information

**Project:** MediTrack: Clinic Management System  
**Institution:** Mandaue City College  
**Project Type:** Capstone Project  
**Platform:** Web Application / Progressive Web App  
**Database:** MySQL

---

*Developed to support more organized, accessible, and efficient school clinic services.*