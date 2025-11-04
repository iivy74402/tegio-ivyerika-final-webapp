# Farmers Production & Sales Management System (FPSMS)

A comprehensive web-based system for managing farmer records, production data, expenses, and sales tracking. Built with PHP, MySQL, and Tailwind CSS.

## Features Implemented

### Admin Module
- **Dashboard Analytics** - View statistics on farmers, technicians, production, and income with interactive charts
- **Manage Technicians** - Add, edit, delete, and assign technicians to barangays
- **Manage Farmers** - View all farmer records across all barangays
- **View Production Records** - Monitor production data from all barangays
- **View Expense Records** - Track expenses with filtering by barangay and season
- **Barangay Management** - Manage barangay assignments for technicians

### Technician Module
- **Dashboard** - View assigned barangay statistics and recent activities
- **Manage Farmers** - Add, edit, delete farmer records (restricted to assigned barangays)
- **Farmers Records** - View detailed farmer information and farm details
- **Record Production** - Add, edit, delete production records with yield calculations
- **Record Expenses** - Track 13 expense categories (Seeds, Fertilizer, Pesticide, Labor, Irrigation, Fuel, Machinery, Herbicide, Insecticide, Molluscicide, Rodenticide, Fungicide, Others)
- **Generate Reports** - Export production and expense data

### Authentication & Authorization
- Role-based access control (Admin, Technician)
- Session management
- Barangay-level data restriction for technicians

## Setup Instructions

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Laragon/XAMPP/WAMP (recommended for local development)

### Installation Steps

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd fpsms
   ```

2. **Database Setup**
   - Create a new MySQL database named `agri_db`
   - Import the database schema (see Database Structure section)
   - Update database credentials in `dbconnection.php`:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $database = "agri_db";
     ```

3. **Configure Web Server**
   - Place project in web server root directory
   - For Laragon: `C:\laragon\www\fpsms`
   - For XAMPP: `C:\xampp\htdocs\fpsms`

4. **Access the Application**
   - Navigate to `http://localhost/fpsms`
   - Default login credentials:
     - Username: `username`
     - Password: `password`

## Database Structure

### Core Tables

**users**
- `user_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `username` (VARCHAR)
- `password_hash` (VARCHAR)
- `email` (VARCHAR)
- `first_name` (VARCHAR)
- `middle_initial` (VARCHAR)
- `last_name` (VARCHAR)
- `role` (ENUM: 'admin', 'technician')

**farmers**
- `farmer_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `id_number` (VARCHAR, UNIQUE)
- `last_name` (VARCHAR)
- `first_name` (VARCHAR)
- `middle_initial` (VARCHAR)
- `birthdate` (DATE)
- `place_of_birth` (VARCHAR)
- `occupation` (VARCHAR)
- `civil_status` (VARCHAR)
- `citizenship` (VARCHAR)
- `sex` (ENUM: 'Male', 'Female')
- `cellphone` (VARCHAR)
- `address_id` (INT, FOREIGN KEY)

**farms**
- `farm_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `farmer_id` (INT, FOREIGN KEY)
- `address_id` (INT, FOREIGN KEY)
- `farm_area` (DECIMAL)
- `tenurial_status` (VARCHAR)
- `farm_owner_name` (VARCHAR)
- `farm_owner_cell` (VARCHAR)

**production_records**
- `production_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `farm_id` (INT, FOREIGN KEY)
- `season_id` (INT, FOREIGN KEY)
- `crop_type` (VARCHAR)
- `planting_date` (DATE)
- `harvest_date` (DATE)
- `sacks_harvested` (INT)
- `weight_per_sack` (DECIMAL)
- `yield_kg` (DECIMAL)
- `selling_price` (DECIMAL)
- `total_expense` (DECIMAL)
- `planting_method` (VARCHAR)
- `irrigation_method` (VARCHAR)
- `notes` (TEXT)

**production_expense**
- `expense_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `production_id` (INT, FOREIGN KEY)
- `category_id` (INT)
- `expense_item` (VARCHAR)
- `amount` (DECIMAL)
- `remarks` (TEXT)

**sales**
- `sale_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `production_id` (INT, FOREIGN KEY)
- `sale_date` (DATE)
- `quantity_sold` (DECIMAL)

**addresses**
- `address_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `street` (VARCHAR)
- `barangay_id` (INT, FOREIGN KEY)

**barangays**
- `barangay_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `barangay_name` (VARCHAR)

**seasons**
- `season_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `season_name` (VARCHAR)
- `year` (INT)

**technician_barangays**
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `technician_id` (INT, FOREIGN KEY)
- `barangay_id` (INT, FOREIGN KEY)

## PHP Functions

### Authentication Functions
- `session_start()` - Initialize user session
- `password_verify()` - Verify hashed passwords
- `password_hash()` - Hash passwords for storage

### Database Functions
- `PDO::prepare()` - Prepare SQL statements
- `PDO::execute()` - Execute prepared statements
- `PDO::fetch()` - Fetch single record
- `PDO::fetchAll()` - Fetch multiple records
- `PDO::lastInsertId()` - Get last inserted ID
- `PDO::beginTransaction()` - Start database transaction
- `PDO::commit()` - Commit transaction
- `PDO::rollBack()` - Rollback transaction

### Utility Functions
- `htmlspecialchars()` - Escape HTML special characters
- `number_format()` - Format numbers with decimals
- `json_encode()` - Encode data to JSON
- `intval()` - Convert to integer
- `floatval()` - Convert to float
- `trim()` - Remove whitespace
- `header()` - Send HTTP headers for redirects

### Custom Calculations
- Yield calculation: `sacks_harvested * weight_per_sack`
- Gross income: `yield_kg * selling_price`
- Net income: `gross_income - total_expense`
- Age calculation: `DateTime::diff()`

## API Endpoints

### Admin Endpoints

**POST** `/admin/actions/add_technician.php`
- Parameters: `first_name`, `middle_initial`, `last_name`, `username`, `email`, `password`, `barangay_id`
- Returns: Redirect with success/error message

**POST** `/admin/actions/edit_technician.php`
- Parameters: `user_id`, `first_name`, `middle_initial`, `last_name`, `username`, `email`, `barangay_id`
- Returns: Redirect with success/error message

**GET** `/admin/actions/delete_technician.php?id={user_id}`
- Parameters: `id` (user_id)
- Returns: Redirect with success/error message

**POST** `/admin/actions/assign_technician.php`
- Parameters: `technician_id`, `barangay_id`
- Returns: Redirect with success/error message

**GET** `/admin/actions/unassign_technician.php?id={user_id}`
- Parameters: `id` (user_id)
- Returns: Redirect with success/error message

### Technician Endpoints

**POST** `/technician/actions/add_farmer.php`
- Parameters: Farmer personal info, home address, farm details
- Returns: Redirect with success/error message

**POST** `/technician/actions/update_farmer.php`
- Parameters: `farmer_id` + all farmer fields
- Returns: Redirect with success/error message

**GET** `/technician/actions/delete_farmer.php?delete={farmer_id}`
- Parameters: `delete` (farmer_id)
- Returns: Redirect with success/error message

**POST** `/technician/actions/add_production.php`
- Parameters: `farmer_id`, `farm_id`, `season_id`, `crop_type`, `sacks_harvested`, `weight_per_sack`, `selling_price`, `planting_date`, `harvest_date`, etc.
- Returns: Redirect with success/error message

**POST** `/technician/actions/update_production.php`
- Parameters: `production_id` + all production fields
- Returns: Redirect with success/error message

**GET** `/technician/actions/delete_production.php?delete={production_id}`
- Parameters: `delete` (production_id)
- Returns: Redirect with success/error message

**POST** `/technician/record_expense.php` (Add Expense)
- Parameters: `production_id`, `add_expense`, expense category amounts
- Returns: Page reload with success/error message

**POST** `/technician/record_expense.php` (Edit Expense)
- Parameters: `production_id`, `edit_expense`, expense category amounts
- Returns: Page reload with success/error message

**GET** `/technician/record_expense.php?delete={production_id}`
- Parameters: `delete` (production_id)
- Returns: Page reload with success/error message

**GET** `/technician/get_farms.php?farmer_id={farmer_id}`
- Parameters: `farmer_id`
- Returns: JSON array of farms

### Authentication Endpoints

**POST** `/login.php`
- Parameters: `username`, `password`
- Returns: Redirect to dashboard or error message

**GET** `/logout.php`
- Returns: Redirect to login page

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, Tailwind CSS 2.2+
- **JavaScript**: Vanilla JS, Chart.js
- **Server**: Apache/Nginx

## Project Structure

```
fpsms/
├── admin/
│   ├── actions/
│   ├── includes/
│   ├── dashboard.php
│   ├── manage_technicians.php
│   ├── manage_farmers.php
│   ├── view_production.php
│   └── view_expenses.php
├── technician/
│   ├── actions/
│   ├── includes/
│   ├── dashboard.php
│   ├── manage_farmers.php
│   ├── farmers_records.php
│   ├── record_production.php
│   └── record_expense.php
├── assets/
├── includes/
├── dbconnection.php
├── login.php
├── logout.php
└── index.php
```

## License

This project is developed for agricultural management purposes.

## Support

For issues or questions, contact the system administrator.
