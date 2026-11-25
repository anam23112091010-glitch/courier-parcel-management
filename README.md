# Courier & Parcel Management System

PHP (PDO) + MySQL + Bootstrap 5 project. Public website + Customer panel + Admin panel + Rider panel.

## Setup (XAMPP / WAMP / Laragon)

1. Copy the `courier` folder into `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** from XAMPP Control Panel
3. Open `http://localhost/phpmyadmin` -> **Import** -> choose `database.sql` -> Go
   (this creates the database `courier_db` with demo data)
4. Open `http://localhost/courier/`

If your MySQL has a password, edit `config.php` (DB_USER / DB_PASS).

## Demo accounts

| Role  | Email               | Password |
|-------|---------------------|----------|
| Admin | admin@courier.com   | admin123 |
| User  | user@courier.com    | user123  |
| Rider | rider@courier.com   | rider123 |

Demo tracking numbers: `CPM-DEMO-0001`, `CPM-DEMO-0002`, `CPM-DEMO-0003`

## Features

**Public site:** Home, Services, Pricing + live calculator, Track parcel, About + branches, Contact form, Login, Register

**Customer panel:** dashboard stats, book parcel (live charge), my parcels (search/filter/pagination), parcel details + timeline, cancel booking, printable invoice, profile + change password

**Admin panel:** dashboard with charts, all parcels (filters), manage parcel (status, rider, branch, payment, tracking history), customers (block/delete), riders (add/block/delete), branches (CRUD), contact messages, reports (date range, CSV export, print), settings (company info, pricing rates, admin password)

**Rider panel:** assigned deliveries, status updates (Picked Up / In Transit / Out for Delivery / Delivered / Returned), COD summary, delivery history, profile

## Security

PDO prepared statements, password_hash, CSRF tokens on all forms, output escaping, role-based access, session regeneration on login.

## Folder structure

```
courier/
  config.php, database.sql, index.php, services.php, price.php, track.php,
  about.php, contact.php, login.php, register.php, logout.php
  includes/  (functions, header, footer, panel_header, panel_footer)
  assets/    (css/style.css, js/main.js)
  user/      (dashboard, book, parcels, view, invoice, cancel, profile)
  admin/     (dashboard, parcels, parcel_view, users, riders, branches, messages, reports, settings)
  rider/     (dashboard, update, history, profile)
```
