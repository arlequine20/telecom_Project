# Telecom System Report Generation Guide

## 📊 Overview

Your telecom system now has a complete **Report Generation System** integrated into the admin panel! This feature allows admins to generate, view, and export detailed system reports in multiple formats.

## 🚀 Quick Start

### 1. **Run Database Migration**
```bash
php artisan migrate
```

This creates the `reports` table to store all generated reports.

### 2. **Access Reports**
- Navigate to: `http://yourapp.com/admin/reports`
- Or click the **Reports** link in the admin sidebar

### 3. **Generate Your First Report**
1. Click **"Generate New Report"** button
2. Select a report type
3. Choose date range (start and end dates)
4. Add optional filters (varies by report type)
5. Click **"Generate Report"**
6. View, download, or delete the report

---

## 📋 Report Types

### 1. **Summary Report** 📈
**Complete system overview for your selected period**

Includes:
- Total transactions and their status breakdown
- Total amount transacted and fees collected
- Customer statistics (new, active)
- SIM card inventory (total, active, inactive)
- Total balances and data balances

**Best For:** Executive summaries, performance reviews

---

### 2. **Transaction Report** 💰
**Detailed analysis of all transactions**

Includes:
- Transaction count by status (approved, pending, cancelled, reversed)
- Total amount and fees
- Average transaction value
- Complete transaction table with:
  - Transaction ID and reference
  - Sender and receiver
  - Amount and fees
  - Status and timestamp

**Filters:**
- Status (Approved, Pending, Cancelled, Failed, Reversed)

**Best For:** Auditing transactions, reconciliation

---

### 3. **Customer Report** 👥
**Comprehensive customer data**

Includes:
- Total customers registered in period
- Active vs inactive customers
- Customer details table with:
  - Full name, email, phone
  - Number of SIMs and active SIMs
  - Total wallet balance
  - Registration date

**Best For:** Customer analytics, business development

---

### 4. **SIM Card Report** 📱
**Complete SIM inventory status**

Includes:
- Total SIMs created
- Active vs inactive count
- Distribution by tariff plan (prepaid/postpaid)
- Total balance and data balance
- Detailed SIM table with:
  - SIM number and phone number
  - Customer assignment
  - Current balance and data
  - Status and registration date

**Filters:**
- Status (Active, Inactive)
- Tariff Plan (Prepaid, Postpaid)

**Best For:** Inventory management, capacity planning

---

### 5. **Revenue Report** 💵
**Financial analysis and revenue metrics**

Includes:
- Total revenue (from approved transactions)
- Total fees collected
- Net revenue (revenue - fees)
- Average transaction value
- Daily revenue breakdown showing:
  - Daily revenue
  - Daily fees
  - Daily net revenue
  - Daily transaction count

**Best For:** Financial reporting, profit analysis

---

## 📤 Export Options

### **CSV Export**
- Format: Standard comma-separated values
- Opens in: Excel, Google Sheets, CSV readers
- Contains: Full report data in spreadsheet format

**How to:**
1. View a report
2. Click "Export CSV" button
3. File downloads as `report_[type]_[date].csv`

### **PDF Export**
- Format: Professional PDF document
- Contains: Formatted report with headers and tables
- Quality: Print-ready

**How to:**
1. View a report
2. Click "Export PDF" button
3. File downloads as `report_[type]_[date].pdf`

---

## ⚡ Quick Presets

When creating a report, use these quick shortcuts to set date ranges:

- **Today** - Report for today only
- **This Week** - Report for the past 7 days
- **This Month** - Report for the past 30 days
- **This Year** - Report for the past 365 days

---

## 🗂️ Report Management

### **View All Reports**
1. Go to `/admin/reports`
2. See all previously generated reports
3. Most recent reports appear first

### **View Report Details**
1. Click "View" button on any report
2. See formatted data with statistics
3. Export or delete from this page

### **Delete Reports**
1. Click "Delete" button on report list or detail page
2. Confirm deletion
3. Report is removed from database

### **Report Information**
Each report displays:
- Title with type and date range
- Generation timestamp
- Who generated it
- Current status (Generated, Archived)

---

## 📊 Data in Reports

### Summary Report Data
```
- Period: Y-m-d to Y-m-d
- Transaction Stats (approved, pending, cancelled, reversed)
- Total Revenue & Fees
- Customer Count
- SIM Card Inventory
```

### Transaction Report Data
```
- Total Transactions: X
- Total Amount: ₦X
- Total Fees: ₦X
- Average Transaction: ₦X
- Status Breakdown: {status: count}
- Full Transaction Table
```

### Customer Report Data
```
- Total Customers: X
- Active: X
- Inactive: X
- Customer Details Table
```

### SIM Card Report Data
```
- Total SIMs: X
- Active: X
- Inactive: X
- By Tariff Plan: {prepaid: X, postpaid: X}
- Full SIM Details Table
```

### Revenue Report Data
```
- Total Revenue: ₦X
- Total Fees: ₦X
- Net Revenue: ₦X
- Average Per Transaction: ₦X
- Daily Breakdown Table
```

---

## 🔐 Access Control

- Only **Admin users** can access reports
- Non-admin users will get a 403 error
- Authentication is required (`auth:sanctum` middleware)

---

## 📁 System Files

### New Files Created:

1. **Database**
   - `database/migrations/2026_06_10_000000_create_reports_table.php`

2. **Models**
   - `app/Models/Report.php`

3. **Services**
   - `app/Services/ReportService.php`

4. **Controllers**
   - `app/Http/Controllers/Web/ReportController.php`

5. **Views**
   - `resources/views/admin/reports/index.blade.php`
   - `resources/views/admin/reports/create.blade.php`
   - `resources/views/admin/reports/show.blade.php`
   - `resources/views/admin/reports/partials/summary.blade.php`
   - `resources/views/admin/reports/partials/transaction.blade.php`
   - `resources/views/admin/reports/partials/customer.blade.php`
   - `resources/views/admin/reports/partials/sim-card.blade.php`
   - `resources/views/admin/reports/partials/revenue.blade.php`

6. **Routes**
   - Updated: `routes/web.php` with report routes

7. **Navigation**
   - Updated: `resources/views/admin/partials/sidebar.blade.php`

---

## 🛠️ Technical Details

### Routes
```php
// All routes are prefixed with /admin/
GET     /reports                    - List all reports
GET     /reports/create             - Create report form
POST    /reports                    - Store new report
GET     /reports/{report}           - View report details
GET     /reports/{report}/export-csv - Download as CSV
GET     /reports/{report}/export-pdf - Download as PDF
DELETE  /reports/{report}           - Delete report
```

### Database Schema
```sql
Table: reports
- id (Primary Key)
- title (string)
- type (string: transaction, customer, sim_card, revenue, summary)
- start_date (datetime)
- end_date (datetime)
- filters (json, optional)
- data (json - contains report data)
- status (string: generated, archived)
- generated_by (foreign key to users)
- timestamps (created_at, updated_at)
```

### Report Service Methods
```php
ReportService::generateTransactionReport()
ReportService::generateCustomerReport()
ReportService::generateSimCardReport()
ReportService::generateRevenueReport()
ReportService::generateSummaryReport()
```

---

## 💡 Usage Examples

### Example 1: Monthly Revenue Report
1. Go to `/admin/reports/create`
2. Select "Revenue Report"
3. Set start_date to first day of month
4. Set end_date to last day of month
5. Click "Generate Report" or use "This Month" preset
6. View or export revenue data

### Example 2: Quarterly Customer Growth
1. Create "Customer Report"
2. Set date range for quarter (3 months)
3. No filters needed
4. View customer statistics and breakdown
5. Export as CSV for spreadsheet analysis

### Example 3: Daily Transaction Audit
1. Create "Transaction Report"
2. Use "Today" preset
3. Filter by status (e.g., "Pending" to find unapproved)
4. Export as CSV for detailed review

---

## 📝 Notes

- Reports are stored in database - they persist across sessions
- Each report includes metadata (who generated it, when)
- Date ranges are inclusive (includes both start and end dates)
- Filters are optional - leave blank to include all data
- CSV exports open in any spreadsheet application
- PDF exports require DomPDF (see optional setup below)

---

## 🔧 Optional: Enable PDF Export (Advanced)

To enable PDF download functionality:

```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

Then update [ReportController.php](app/Http/Controllers/Web/ReportController.php) line 87:

```php
public function exportPdf(Report $report)
{
    $pdf = PDF::loadView('admin.reports.pdf', compact('report'));
    return $pdf->download('report_' . $report->type . '.pdf');
}
```

---

## 🆘 Troubleshooting

### "Reports table not found" error
**Solution:** Run `php artisan migrate`

### "Route not found" error
**Solution:** Clear route cache with `php artisan route:clear`

### Report not showing any data
**Solution:** Check date range - ensure data exists for selected period

### Export button not working
**Solution:** Check file permissions on storage directory

---

## 🎓 Next Steps

1. ✅ Run migration: `php artisan migrate`
2. ✅ Test report generation
3. ✅ Try exporting to CSV
4. ✅ Setup PDF export (optional)
5. ✅ Share reports with team
6. ✅ Use reports for business decisions

---

## 📞 Support

For issues or questions:
1. Check the [Reports Model](app/Models/Report.php)
2. Review [Report Service](app/Services/ReportService.php)
3. Check [Report Controller](app/Http/Controllers/Web/ReportController.php)
4. Review generated report views

---

**Last Updated:** June 10, 2026
**System:** Telecom Management System
**Version:** 1.0
