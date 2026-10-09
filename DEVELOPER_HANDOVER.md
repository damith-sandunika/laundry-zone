# Laundry Zone - Developer Handover Guide

Welcome to the **Laundry Zone** project. This document is a comprehensive guide to understanding the system architecture, workflows, database schema, and custom features implemented so far. It is designed to help a new developer take over and continue development seamlessly.

---

## 1. Tech Stack
* **Backend:** Laravel (PHP)
* **Frontend:** Blade Templates, TailwindCSS, Alpine.js (for reactive UI components, Modals, and DOM manipulation)
* **Database:** MySQL / MariaDB

---

## 2. Roles & Access Control
The system uses a role-based access control (RBAC) approach.
* **`super_admin`**: Full access to the entire system, all branches, settings, and bypasses all approval requirements.
* **`branch_admin`**: Manages a specific branch. Can perform most branch-level actions. Auto-approves order corrections/refunds for their branch.
* **`workshop_admin`**: Manages workshop operations (washing, ironing, etc.). Can accept dispatches, update statuses in bulk. Auto-approves corrections.
* **`cashier`**: Standard branch staff. Can create orders, accept payments. **Requires admin approval** for order corrections and refunds.

---

## 3. Core Business Workflows

### 3.1. Order Lifecycle (Status Flow)
Every order strictly follows this status pipeline:
1. **`received`**: Created at the POS by branch staff.
2. **`dispatched`**: Sent from the branch to the workshop.
3. **`processing`**: Accepted by the workshop and currently being washed/ironed.
4. **`returned_to_branch`**: Workshop has finished processing and sent the items back to the originating branch.
5. **`done`**: Branch has received the items and they are ready for the customer to collect.
6. **`delivered`**: Handed over to the customer (Payment is fully settled).

### 3.2. Workshop Dispatch System (Bulk Operations)
To handle hundreds of orders smoothly, workshop operations use **chunked processing** via dedicated Action classes:
* `App\Actions\Workshop\BulkDispatchToWorkshopAction`: Handles sending multiple orders from a branch to the workshop.
* `App\Actions\Workshop\WorkshopBulkStatusUpdateAction`: Handles updating order statuses in bulk at the workshop (e.g., marking as processing, returning to branch).
* `App\Actions\Order\BulkUpdateOrderStatusAction`: General bulk status updater.

*Note: These action classes process database records in chunks to prevent memory exhaustion and timeout errors.*

### 3.3. Payment & Refund Flow
* **Payments:** Tracked in the `payments` table. Supports Cash, Card, Bank Transfer.
* **Overpayments:** If a user modifies an order and the new total is LESS than what the customer already paid, it triggers an overpayment scenario.
* **Refund Logic:** 
    - Found in `OrderController@refundOverpayment`.
    - Creates an `OrderCorrectionRequest` with type `refund_excess`.
    - **Approval:** If the user is `super_admin`, `branch_admin`, or `workshop_admin`, the system *auto-approves* the refund via `OrderCorrectionController@approve`. If the user is a `cashier`, it remains `pending` until an admin approves it.

---

## 4. Key Features & Custom Implementations

### 4.1. POS (Point of Sale) System
* **Quick Customer Creation:** The POS includes a modal to add a new customer on the fly without leaving the page.
* **Customer Mark:** A special field `customer_mark` (e.g., "M1", "VIP") is tracked in the `customers` table to help physically tag bags. This mark prints on POS chits and displays in the profile.

### 4.2. UI & Frontend Specifics (Alpine.js & Tailwind)
* **Modal Stacking Context:** Modals heavily use Alpine.js. To prevent modals from being trapped under parent `z-index` containers, modals are implemented using `x-teleport="body"`. 
* **Z-Index Standards:** Modals typically use `z-[60]` or higher with `fixed inset-0` to guarantee they sit above everything else.
* **Inline Computations:** We use Alpine.js `x-text` to dynamically calculate Pending vs. Paid amounts on the frontend without requiring a page reload.

---

## 5. Important Database Tables
* `users`: System users and their roles.
* `branches`: Stores both standard branches and workshops (`type` = 'branch' | 'workshop').
* `customers`: Stores client details and their unique `customer_mark`.
* `orders`: The central table. Contains `total`, `paid_amount`, `status`.
* `order_items`: Line items for each order, linked to `service_types`.
* `payments`: Ledger of all transactions.
* `workshop_dispatches`: Tracks batches of orders moving between branch and workshop.
* `order_correction_requests`: Tracks refund requests, discount edits, and voids.

---

## 6. How to Test the Architecture
There is a custom backend script written to simulate the entire lifecycle of the application under heavy load to ensure transactions and logic are solid.
* **File:** `scratch/full_system_test_v3.php` (or similar scripts you may find in the project).
* **What it does:** Simulates creating 10 orders, chunk-dispatching them, processing them, returning them, and delivering them, while verifying DB integrity, and then rolls back the transaction.

## 7. Developer Rules / Best Practices Established
1. **Do not use Store Credit:** The system has moved away from "Store Credit". Excess payments must be refunded.
2. **Follow the existing Branch strategy:** Always check if a user is acting on behalf of their assigned branch before showing data.
3. **UI Consistency:** Always use the defined Tailwind color palette (e.g., Slate for backgrounds, Emerald for success/paid, Rose for pending/errors) and maintain the premium aesthetic.

---
*Document generated to ensure a smooth transition and continuous deployment.*
