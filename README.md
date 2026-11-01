# SoloLedger - Invoicing & Financial Accounting for Freelancers & Small Businesses

SoloLedger is a focused financial management, billing, and bookkeeping application engineered for freelancers, solo entrepreneurs, and boutique agencies. It provides an intuitive suite for client billing, sequential invoicing, expense tracking, and instant tax-compliant reporting.

---

## Key Modules & Capabilities

### 1. Invoicing & Billing Lifecycle
* Professional quotation and invoice generation with sequential number series.
* Itemized line items: billable hours, project milestones, unit-priced services, and products.
* Due date tracking, overdue status flags, and automated payment status progressions (Draft, Sent, Paid, Overdue).
* Multi-currency and custom payment terms support.

### 2. Payment Recording & Settlement
* Manual and automatic settlement recording across multiple financial channels.
* Partial payment support with running balance calculation.
* Payment history tracking for complete client account reconciliation.

### 3. Expense Tracking & Overhead Categorization
* Comprehensive expense ledger with category classification (Software, Travel, Subcontractors, Office, Utilities).
* Tax-deductible flag marking for simplified fiscal year reporting.
* Vendor assignment, receipt attachment metadata, and notes.

### 4. Client Directory
* Centralized client database storing corporate identities, registration numbers, billing addresses, and primary contacts.
* Individual client billing history showing lifetime value, open invoices, and payment punctuality.

### 5. Configurable Tax Rates
* Multi-tier sales tax and value added tax configuration.
* Inclusive and exclusive tax calculation rules per invoice line item.

### 6. Streaming Document Export
* High-performance export pipeline generating formatted documents without memory spikes:
  * PDF Invoices and Statements via Dompdf.
  * Excel spreadsheets (XLSX) and CSV reports via OpenSpout.
* Filterable export ranges for monthly, quarterly, and annual accounting periods.

---

## Technical Stack

* Framework: Laravel 13
* Runtime: PHP 8.5
* Database: MySQL 8.0
* Reporting Engines: OpenSpout, Dompdf
* Frontend: Tailwind CSS, Blade Templating Engine
* Containerization: Docker, Nginx

---

## Project Structure

```
app/
├── Http/Controllers/     Invoice, Payment, Expense, Customer, and Export controllers
├── Models/               Eloquent models (Invoice, InvoiceItem, Payment, Expense, Customer, TaxRate)
resources/
├── views/                Blade views (Invoice templates, Expense ledgers, Reports, Customer profiles)
routes/
├── web.php               Application endpoints and invoice export routes
database/
├── migrations/           Database schema definitions
├── seeders/              Standard expense categories, default tax rates, and demo clients
```

---

## Installation & Deployment

### Prerequisites
* Docker and Docker Compose
* Git

### Setup Instructions
1. Clone the repository into your local development environment:
   ```bash
   git clone <repository_url>
   cd <repository_folder>
   ```

2. Copy the sample environment configuration:
   ```bash
   cp .env.example .env
   ```

3. Update the database configuration in `.env`:
   ```env
   APP_NAME=SoloLedger
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel13_ledger
   DB_USERNAME=usr_ledger
   DB_PASSWORD=your_secure_password
   ```

4. Launch the application container:
   ```bash
   docker compose up -d
   ```

5. Install application dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

6. Generate the encryption key:
   ```bash
   php artisan key:generate
   ```

7. Execute migrations and seed default categories:
   ```bash
   php artisan migrate --seed
   ```

8. Establish storage symbolic link for report storage:
   ```bash
   php artisan storage:link
   ```

9. Cache configuration for production:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## License

This software is distributed under the proprietary license of the author. All rights reserved.
