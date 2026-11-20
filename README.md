# shorturladminpanel

## Setup

1. Create the MySQL database:

```sql
CREATE DATABASE assignment_shorturl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Copy the environment file and set the MySQL connection:

```bash
cp .env.example .env
```
Default local values in `.env.example`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=assignment_shorturl
DB_USERNAME=root
DB_PASSWORD=
```

3. Install dependencies :

```bash
composer install

```

4. Run the migrations. This creates the tables in `assignment_shorturl`:

```bash
php artisan migrate
```

5. Run the seeder. This adds the SuperAdmin, Admin, and Member roles, their permissions, and the Super Admin login:

```bash
php artisan db:seed
```

6. Start the app:

```bash
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Seeded login

`php artisan db:seed` inserts one account.

| Email | Password | Role |
|---|---|---|
| superadmin@example.com | Admin@123 | SuperAdmin |


## Tools

Cursor chat was used in this project for the code and for simple code management.
