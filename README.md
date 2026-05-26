# Green & Waste Collect App

A full-featured PHP web application for local waste management, featuring:

1. **Waste Disposal Planning** — Monthly collection calendar with zone filtering
2. **Real-Time Truck Tracking** — Live truck positions on an interactive map
3. **Crowdsourced Reporting** — Report overflowing bins with GPS + photo upload
4. **Recycling Economy** — Marketplace to buy and sell recyclable materials

---

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.4+
- A web server: Apache (with mod_rewrite) or Nginx
- PHP extensions: `pdo_mysql`, `gd` or `imagick`, `fileinfo`

---

## Installation

### 1. Set up the database

```sql
-- In MySQL / phpMyAdmin:
SOURCE /path/to/green-waste-app/db/schema.sql;
```

Or via CLI:
```bash
mysql -u root -p < db/schema.sql
```

### 2. Configure database connection

Edit `includes/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'green_waste');
```

### 3. Deploy files

Copy all files to your web server's document root (e.g. `htdocs/`, `www/`, `/var/www/html/`).

### 4. Set permissions

```bash
chmod 755 uploads/
chmod 755 uploads/reports/
```

### 5. Access the app

Open `http://localhost/green-waste-app/` in your browser.

---

## Demo Accounts

| Role     | Email                    | Password   |
|----------|--------------------------|------------|
| Admin    | admin@greenwaste.com     | password   |
| Resident | koaung@example.com       | password   |
| Resident | mahnin@example.com       | password   |

---

## File Structure

```
green-waste-app/
├── index.php           → Redirect to login
├── login.php           → Login page
├── register.php        → Registration
├── logout.php          → Session destroy
├── dashboard.php       → Main dashboard
├── schedule.php        → Collection calendar (Feature 1)
├── tracking.php        → Live truck tracker (Feature 2)
├── report.php          → Waste reporting (Feature 3)
├── market.php          → Recycling market (Feature 4)
├── admin/
│   └── index.php       → Admin panel (reports, trucks, users)
├── api/
│   ├── schedule.php    → Schedule CRUD API
│   ├── tracking.php    → Truck position API (JSON)
│   ├── report.php      → Reports API (JSON)
│   └── market.php      → Market listings API
├── includes/
│   ├── db.php          → PDO database connection
│   ├── auth.php        → Session auth helpers
│   ├── header.php      → Shared navbar
│   └── footer.php      → Shared footer + scripts
├── assets/
│   ├── css/style.css   → Custom styles
│   └── js/app.js       → Leaflet maps, UI helpers
├── db/
│   └── schema.sql      → Full DB schema + seed data
└── uploads/
    └── reports/        → User-uploaded photos (writable)
```

---

## Tech Stack

- **Backend**: PHP 8, PDO (MySQL)
- **Frontend**: Bootstrap 5.3, Font Awesome 6
- **Maps**: Leaflet.js + OpenStreetMap (free, no API key needed)
- **Auth**: PHP sessions + password_hash / password_verify

---

## For Production

- Set `session.cookie_httponly = 1` and `session.cookie_secure = 1` in `php.ini`
- Use HTTPS
- Restrict database user to minimum necessary privileges
- Consider adding CSRF tokens to all forms
- Set upload limits in `php.ini` (`upload_max_filesize`, `post_max_size`)
