# MAGERWA Vehicle Tracking Management System

A vehicle tracking management system for MAGERWA (Rwanda's public bonded warehouse), built with **native PHP (PDO)**, **MySQL**, and a responsive **Bootstrap 5** frontend.

## Features

- **Admin Authentication** — sign up (names, email, phone, national ID), log in, log out. All management pages and APIs are restricted to logged-in admins.
- **Staff Management** — list, add, edit, and delete admin staff in a paginated table. Editing keeps the current password when left blank; deleting is guarded so you cannot remove your own account or the last remaining staff account.
- **Client Management** — register, edit, and delete clients (names, national ID, telephone, address) with paginated listing. Deleting a client automatically unlinks (and frees the plate of) any vehicles assigned to them.
- **Vehicle Management** — register, edit, and delete vehicles (chassis number, manufacture company, year, price, model name) with paginated listing.
- **Linkage & Display** — link a vehicle to a client with a unique plate number, view all linked records in a paginated table, edit a link (reassign client / change plate), or unlink a vehicle while keeping it registered.
- **Data Integrity** — uniqueness checks on update (email, national ID, chassis, plate), POST-only deletes with confirm prompts, and validation with inline error feedback.
- **REST API** — token-protected JSON endpoints usable from Postman.

## Requirements

- XAMPP (Apache + MySQL) or any PHP 8+ and MySQL environment.

## Setup

1. Place the `magerwa-vts` folder inside `C:\xampp\htdocs` (already there if built in place).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Create the database by importing the schema:
   - Open `http://localhost/phpmyadmin`
   - Import `sql/schema.sql`, **or** run it from the SQL tab.
   - From CLI: `mysql -u root < sql/schema.sql`
4. Visit `http://localhost/magerwa-vts`.
5. Sign up an admin account, then log in.

Default DB credentials (edit in `config/database.php` if different): host `127.0.0.1`, db `magerwa_vts`, user `root`, no password.

## REST API (Postman)

Base URL: `http://localhost/magerwa-vts/api`

All requests/responses use JSON. Protected endpoints require an `Authorization: Bearer <token>` header.

| Method | Endpoint        | Auth   | Body fields |
|--------|-----------------|--------|-------------|
| POST   | `/signup.php`   | none   | names, email, phone, national_id, password |
| POST   | `/login.php`    | none   | email, password — returns `token` |
| GET    | `/clients.php?page=1` | Bearer | — |
| POST   | `/clients.php`  | Bearer | names, national_id, telephone, address |
| PUT    | `/clients.php?id={id}` | Bearer | names, national_id, telephone, address |
| DELETE | `/clients.php?id={id}` | Bearer | — (frees plate of any linked vehicles) |
| GET    | `/vehicles.php?page=1` | Bearer | — |
| POST   | `/vehicles.php` | Bearer | chassis_number, manufacture_company, manufacture_year, price, model_name |
| PUT    | `/vehicles.php?id={id}` | Bearer | chassis_number, manufacture_company, manufacture_year, price, model_name |
| DELETE | `/vehicles.php?id={id}` | Bearer | — |
| POST   | `/link.php`     | Bearer | vehicle_id, client_id, plate_number |
| DELETE | `/link.php?id={id}` | Bearer | — (unlinks vehicle, keeps it registered) |

The `id` for `PUT`/`DELETE` may be passed as a query parameter (`?id={id}`) or in the JSON body.

### Example flow

1. `POST /api/login.php` with `{ "email": "...", "password": "..." }` → copy the `token`.
2. Add header `Authorization: Bearer <token>` to subsequent requests.
3. Create clients/vehicles and link them.

## Project Structure

```
magerwa-vts/
├── admins/         staff list, create, edit, delete
├── api/            REST API endpoints (token auth)
├── assets/css/     Stylesheet
├── auth/           signup, login, logout
├── clients/        register, list, edit, delete
├── config/         app config + PDO connection
├── includes/       shared header, footer, helpers, pagination
├── sql/            database schema
├── vehicles/       register, list, edit, delete, link, edit link, unlink, linked records
├── dashboard.php   stats overview
└── index.php       entry redirect
```
