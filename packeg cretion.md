# Customer Packages System Implementation Plan

This document outlines the architecture, database changes, and UI updates required to implement the advanced Customer Packages System (Monthly/Weekly Subscriptions).

## 1. Goal Description
The objective is to allow the laundry to sell pre-paid packages (e.g., "Family Pack - 20KG/Month") to customers. The system must handle expirations, balance rollovers, centralized/decentralized modes, and automatically integrate with the POS to split bills when usage exceeds the package balance.

## 2. Centralized vs Decentralized Architecture
* **Centralized Mode (Global):** Packages are created by the Super Admin and are available across all branches.
* **Decentralized Mode:** Each branch manager creates and manages their own packages.
* The system will use the existing `App\Models\Setting::get('centralized_management')` logic to filter queries appropriately.

## 3. Database Schema Changes

### Modify `packages` Table
Add fields to handle expiration and rollover logic:
* `validity_days` (integer): How long the package is valid (e.g., 30 for monthly, 7 for weekly).
* `is_rollover_allowed` (boolean): If true, remaining balance rolls over when a new package is purchased before expiry.

### Create `package_categories` Table
To link packages to main service categories (as requested: "main category search and add to package"):
* `id`, `package_id`, `service_category_id` (foreign keys)

### Modify `customer_packages` Table (or create if not exists)
To track issued packages for a customer:
* `id`, `customer_id`, `package_id`, `branch_id`, `remaining_balance` (decimal)
* `purchased_at` (datetime)
* `expires_at` (datetime)
* `status` (enum: active, expired, exhausted)

## 4. Proposed Changes: UI & Views

### Sidebar Navigation
#### [MODIFY] `resources/views/layouts/app.blade.php`
* Add a new main sidebar menu item: **Packages**
* Add two sub-menus:
  1. **Issue Packages** (UI for staff to sell a package to a customer)
  2. **Packages** (UI for Admins to create/edit package templates)

### Package Management UI (Admin)
#### [NEW] `resources/views/packages/index.blade.php`
* A premium UI to create packages.
* Features a search bar to find and add **Main Service Categories** to the package.
* Toggles for Rollover and Validity Periods.

### Issue Packages UI (Staff)
#### [NEW] `resources/views/packages/issue.blade.php`
* A screen to select a customer, select a package, take payment (Cash/Card/Transfer), and issue the package.
* Money collected here hits the daily cash register as "Package Sales".

### Customer View Page (Admin Side)
#### [MODIFY] `resources/views/customers/show.blade.php`
* Add visually appealing tabs/sections for:
  * **Active Packages:** Showing progress bars for remaining balances.
  * **Package History:** List of previously bought and expired packages.

### Public Customer Dashboard
#### [MODIFY] `resources/views/customer/dashboard.blade.php`
* Add a premium, modern "My Packages" widget.
* Displays current active packages, remaining balances, and expiry dates cleanly.

## 5. POS Integration & Split Billing Logic

### POS Controller & UI
#### [MODIFY] `resources/views/pos/index.blade.php`
#### [MODIFY] `app/Http/Controllers/POSController.php`
* **Alert System:** When a customer is selected, if they have an active package, show a prominent banner (e.g., "Active Family Pack: 5KG Remaining").
* **Split Billing Logic:** 
  * When adding items, the system checks the package balance.
  * E.g., Customer brings 7KG. Balance is 5KG.
  * The system creates 2 line items:
    1. Wash & Fold (Package) - 5KG @ Rs. 0.00
    2. Wash & Fold (Normal) - 2KG @ Standard Rate
* **Receipt Printing:** The thermal bill will explicitly show the Rs. 0.00 package usage and print a footer note: *"Remaining Package Balance: X KG"*.

## 6. Open Questions for User
> [!IMPORTANT]
> 1. Should customers be able to buy packages online through the public website, or is it strictly issued by staff at the shop/POS?
> 2. Are packages strictly measured in KG, or do we need Item-based packages (e.g., "10 Shirts per month") as well?
> 3. Does a package apply to *all* services in a category, or only specific services? (You mentioned Main Category, so currently it assumes all services under that category).

## 7. Verification Plan
* Ensure DB migrations run cleanly.
* Test Centralized vs Decentralized package creation.
* Issue a package and verify cash registers update correctly.
* Run a split-bill POS transaction and verify receipt formatting and balance deduction.
