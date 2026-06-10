# Club DanDana — Backend System

## Stack
- PHP 8+ (pure, no framework)
- MySQL / MariaDB
- PDO with prepared statements
- Sessions for auth
- QR codes via api.qrserver.com (no lib install needed)
- Camera scanner via html5-qrcode CDN

---

## Setup

### 1. Database
```sql
-- Import via phpMyAdmin or:
mysql -u root -p < database.sql
```

### 2. Config
Edit `config/database.php` — change DB_USER / DB_PASS if needed.

### 3. Place in XAMPP
Drop the `clubdandana/` folder in `htdocs/`:
```
C:\xampp\htdocs\clubdandana\
```

### 4. Access
- Portal:     http://localhost/clubdandana/portal/index.php
- Admin:      http://localhost/clubdandana/admin/login.php
  - user: `admin`  password: `admin123`

---

## File Structure
```
clubdandana/
├── config/
│   └── database.php          ← DB connection (PDO singleton)
├── includes/
│   ├── header.php            ← HTML head + CSS (portal)
│   ├── footer.php            ← HTML close tags
│   └── qr_helper.php        ← qrCodeImg() / qrCodeUrl()
├── portal/
│   ├── index.php             ← Public event listing
│   ├── buy_ticket.php        ← Ticket purchase form
│   ├── process_ticket.php    ← Core logic: member + ticket + notification
│   ├── my_tickets.php        ← User's tickets by phone number
│   └── verify.php            ← Ticket validation (page + JSON API)
├── admin/
│   ├── login.php             ← Admin authentication
│   ├── auth_guard.php        ← Session guard (include in every admin page)
│   ├── admin_nav.php         ← Shared nav + CSS
│   ├── dashboard.php         ← Stats + recent activity
│   ├── events.php            ← Full CRUD for events
│   ├── members.php           ← Member list + search
│   ├── tickets.php           ← All tickets, filterable
│   ├── scanner.php           ← Camera QR scanner + manual entry
│   ├── notifications.php     ← New non-member alerts
│   └── logout.php
├── database.sql              ← Schema + sample data
└── index.php                 ← Redirect to portal
```

---

## Flow

### Ticket purchase
1. User opens `portal/index.php` → selects event
2. Fills name + phone in `buy_ticket.php`
3. `process_ticket.php`:
   - Finds or creates member
   - Checks duplicate registration
   - Generates unique `TKT-XXXXXXXXXXXXXXXX` code
   - Decrements `places_disponibles` (transaction-safe)
   - Creates notification if new member
   - Redirects to ticket page with QR code

### QR Code
- QR encodes the URL: `portal/verify.php?code=TKT-XXXX`
- Rendered via `https://api.qrserver.com/v1/create-qr-code/`
- Downloadable as PNG

### Ticket validation
- `portal/verify.php` accepts GET (human page) or POST (JSON for scanner)
- Valid first time → marks `utilise = 1`
- Already used → rejected
- Not found → rejected

### Admin scanner
- Uses `html5-qrcode` library (CDN, no install)
- Camera scans QR → extracts code from URL → POSTs to `verify.php`
- Manual input also supported
- Session scan log shown in real-time

---

## Change Admin Password
```php
echo password_hash('your_new_password', PASSWORD_BCRYPT, ['cost' => 12]);
// Then UPDATE admins SET password = 'hash' WHERE username = 'admin';
```
