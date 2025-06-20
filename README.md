# 🚀 Fullstack App — Laravel 8 (Backend) + React with Node.js 20 (Frontend)

This project combines a Laravel 8 backend with a React frontend running on Node.js 20.

## 📂 Project Structure

project-root/
├── server/ # Laravel backend
└── client/ # React frontend

---

## 🖥️ Laravel 8 Backend Setup

**Requirements**

- PHP 7.3+ or 8.x
- Composer
- MySQL (or compatible DB)
- Laravel CLI

**Setup**
Clone this repo and navigate to the server directory:
`cd server`

Install dependencies:
`composer install`

Copy the `.env` file:
`cp .env.example .env`

Generate app key:
`php artisan key:generate`

Set up your `.env` database credentials:

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=your_db

DB_USERNAME=your_user

DB_PASSWORD=your_pass

(Optional) Run migrations:
`php artisan migrate`

Start the Laravel dev server:
`php artisan serve`

The server will run on `http://127.0.0.1:8000`

---

## 🌐 React Frontend Setup (Node.js 20)

**Requirements**

- Node.js 20+
- npm or yarn

Navigate to the client directory:
`cd client`

Install dependencies:
`npm install`
or
`yarn`

Create a `.env` file and set your API base URL:

Start the React development server:
`npm run dev`
or
`yarn dev`

The app will run at `http://localhost:5173`

---

## 🔄 Connecting Frontend to Backend

Ensure CORS is configured in `server/config/cors.php`:

'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_origins' => ['http://localhost:5173'],

In your Laravel `.env`, verify this:
`APP_URL=http://127.0.0.1:8000`

---

## ✅ Common Commands

**Laravel**
`composer install` – install dependencies
`php artisan serve` – start development server
`php artisan migrate` – run database migrations

**React**
`npm install` – install packages
`npm run dev` – start frontend dev server
`npm run build` – build for production

---

## 📬 API Testing

Use tools like Postman or curl to test endpoints.

Example:
`curl http://127.0.0.1:8000/api/test`

---

## 📝 License

MIT License. Feel free to use and modify.
