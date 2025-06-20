# SMS Monitoring & Admin Dashboard (Laravel 8 + React)

This project is a full-stack SMS monitoring and admin system built using **Laravel 8 (PHP)** for the backend and **React (Create React App)** for the frontend. It is designed for telco operators or fintech teams who want detailed visibility into SMS delivery, telco breakdowns, user activity, and wallet balances.

---

## 🎯 Purpose

This dashboard helps internal teams manage and monitor SMS traffic and administrative tasks such as:

- **Tracking SMS statuses (delivered, undelivered, expired, rejected, pending)**
- **Viewing distribution by telecom providers (MTN, Airtel, Glo, 9mobile)**
- **Managing platform users with role-based access**
- **Monitoring wallet balance and transaction history**
- **Auditing all user actions**
- **Accessing daily message logs**

---

## 🧩 Features

### 📊 Dashboard Overview

- Total SMS sent today
- Total SMS pages consumed
- Delivery statistics per telco
- Status distribution per telco (in percentages)
- Wallet balance displayed prominently

### 👤 User Management

- Create and delete users
- Assign roles (`super_admin`, `admin`, `user`)
- Only `super_admin` can manage users
- Secure JWT-based authentication

### 🛡️ Token-Based Authorization

- All API endpoints are protected with **JWT (JSON Web Token)**
- Token is issued at login and must be included in `Authorization: Bearer <token>` header
- Payload contains `id`, `email`, and `role`
- Middleware verifies token on each request and injects user context

### 📨 Messages Tab

- Displays all messages sent today
- Real-time updates using polling or refresh
- Includes delivery status, pages, sender ID, and timestamp

### 🧾 Logs Download

- Access logs generated for system activity
- Download full logs for auditing or debugging

### 🕵️ Audit Trail

- Every user action is recorded: login, creation, deletion, API calls, edits
- `super_admin` users can view audit logs in a dedicated tab
- Useful for compliance or forensic analysis

### 💰 Wallet History

- Track all wallet top-ups and deductions
- Exportable CSV of wallet transactions
- Current wallet balance shown on dashboard

---

## ⚙️ Tech Stack

- **Backend:** Laravel 8, MySQL, JWT Auth, Cloudinary (for uploads)
- **Frontend:** React (CRA), TailwindCSS, Axios, React Router, React Toastify, Chart.js
- **Server:** Apache with PHP 8.3 (locally), PHP 7.4 on production

---

## 🚀 Setup Instructions

### Laravel Backend Setup

```bash
git clone https://github.com/RingoSupport/RingoSms.git
cd your-project/backend

composer install
cp .env.example .env
php artisan key:generate

# Set up DB in .env:
# DB_DATABASE=uba
# DB_USERNAME=root
# DB_PASSWORD=your_password

php artisan migrate
php artisan serve
```
