# 🏛️ Enterprise SaaS Architecture Hardening & Zero-Fail Master Plan
**Project:** 360-Laundry Zone Enterprise Multi-Tenant Platform  
**Target Workload:** 100+ Tenant Companies | 10,000+ Orders/Day | 365 Days 24/7 Zero-Downtime  
**Author:** Principal Enterprise Systems Architect  
**Status:** Approved for Implementation

---

## 1. විධායක සාරාංශය (Executive Summary & Defect Inventory)

පද්ධතිය සමාගම් 100ක්, දිනකට ඇණවුම් 10,000ක් (වසරකට ඇණවුම් මිලියන 3.65ක්) සහිතව දින 365 පුරා නොනැවතී ධාවනය කිරීමේදී හඳුනාගත් බරපතල ගැටලු 7 සහ ඒවා නිරාකරණය කරන ස්ථිර ඉංජිනේරු විසඳුම් මෙහි අන්තර්ගත වේ.

### හඳුනාගත් බරපතල දෝෂ (Audit Defect Registry)

| # | කාණ්ඩය (Category) | හඳුනාගත් දෝෂය (Identified Defect) | සැබෑ අවදානම (Risk Level) | විසඳුම් සැලැස්ම (Fix Strategy) |
|---|---|---|---|---|
| **1** | **Architecture** | සෑම HTTP Request එකකදීම `CentralSchemaManager::ensureCentralDatabaseAndTables()` ධාවනය වීම. | 🔴 **CRITICAL** (Metadata locks, 1,500ms latency spike) | Middleware එකෙන් ඉවත් කර CLI / Provisioning අවස්ථාවට පමණක් සීමා කිරීම. |
| **2** | **Background Queues** | Queue Jobs (`SendOrderNotificationJob` ආදිය) වලට අදාළ Tenant Database එක අමතක වීම. | 🔴 **CRITICAL** (Cross-tenant data leak / notifications) | `TenantAwareJob` Trait / Middleware මඟින් Queue Worker එක තුළ Dynamic Tenant Connection enforce කිරීම. |
| **3** | **Performance** | Subdomain එක සෑම Request එකකදීම Landlord MySQL එකෙන් Direct Query කිරීම. | 🟠 **HIGH** (500,000 queries/day redundant load) | Redis / In-Memory Subdomain Caching (`Cache::rememberForever`) යෙදීම (0ms latency). |
| **4** | **Database Design** | `orders` වගුවේ Compound B-Tree Indexes නොමැති වීම. | 🟠 **HIGH** (Full Table Scans on 3.6M rows) | `(branch_id, status, created_at)` සහ `(customer_id, created_at)` composite indexes එක් කිරීම. |
| **5** | **Action Architecture** | `BulkDispatchToWorkshopAction` තුළ Chunk foreach loop එකක් තුළ $O(2N)$ queries ධාවනය වීම. | 🟠 **HIGH** (400 SQL queries for 200 orders) | Bulk Vectorization: `insert()` සහ `whereIn()->update()` මඟින් තනි Queries 2කට සීමා කිරීම. |
| **6** | **Database Concurrency** | MySQL Connection Pool එක සමාගම් 100කදී හිස් වීම (`ERROR 1040`). | 🔴 **CRITICAL** (Total DB crash under load) | Request අවසානයේ `DB::disconnect('tenant')` කිරීම සහ Deadlock retry `DB::transaction(..., 5)`. |
| **7** | **Observability** | සමාගම් 100කම දෝෂ එකම `laravel.log` ගොනුවේ කලවම් වීම. | 🟡 **MEDIUM** (Debugging paralysis) | `Log::withContext(['tenant_id' => ...])` මඟින් Structured Logging සක්‍රීය කිරීම. |

---

## 2. පියවරෙන් පියවර ක්‍රියාත්මක කිරීමේ සැලැස්ම (Step-by-Step Hardening Plan)

### පියවර 1: `IdentifyTenant` Middleware එක Hardening කිරීම
* **ගොනුව:** `app/Http/Middleware/IdentifyTenant.php`
* **ක්‍රියාවලිය:**
  1. `CentralSchemaManager::ensureCentralDatabaseAndTables()` නිරන්තරයෙන් ධාවනය වීම සම්පූර්ණයෙන්ම ඉවත් කිරීම.
  2. Subdomain lookup එක `Cache::remember("tenant_domain:{$host}", 3600, ...)` මඟින් මතකයේ රඳවා තැබීම.
  3. Tenant එක load වූ වහාම `Log::withContext()` මඟින් ලොග් සටහන් සඳහා Tenant සන්දර්භය (Context) එක් කිරීම.
  4. Response එක අවසන් වන අවස්ථාවේදී (terminate lifecycle) Connection leaks වැළැක්වීම.

### පියවර 2: Queue Workers සඳහා Tenancy Context Injection
* **ගොනුව:** `app/Traits/TenantAwareJob.php`
* **ක්‍රියාවලිය:**
  1. සෑම Job එකක්ම dispatch වන විට ක්‍රියාකාරී `tenant_id` එක Serialized payload එකට ලබාගැනීම.
  2. Background Worker එක Job එක ක්‍රියාත්මක කිරීමට (handle) ප්‍රථම, අදාළ සමාගමේ ඩේටාබේස් එකට Connection එක switch කරගැනීම.
  3. Job එක අවසන් වූ පසු Landlord DB එකට reset කිරීම.

### පියවර 3: High-Performance Composite Indexes Migration එක සෑදීම
* **ගොනුව:** `database/migrations/2026_10_29_000002_add_enterprise_indexes_to_orders_and_dispatches.php`
* **ක්‍රියාවලිය:**
  1. `orders` වගුව සඳහා:
     - `['branch_id', 'status', 'created_at']`
     - `['customer_id', 'created_at']`
     - `['payment_status', 'created_at']`
  2. `workshop_dispatches` වගුව සඳහා:
     - `['order_id', 'status']`
     - `['workshop_branch_id', 'status']`
  3. පවතින සියලුම Tenant Databases වලට මෙම migration එක ධාවනය කිරීම.

### පියවර 4: `BulkDispatchToWorkshopAction` Action එක Bulk Vectorization කිරීම
* **ගොනුව:** `app/Actions/Workshop/BulkDispatchToWorkshopAction.php`
* **ක්‍රියාවලිය:**
  1. $O(2N)$ තනි queries loop එක ඉවත් කිරීම.
  2. Chunk එක තුළ ඇති සියලුම records එකවර සකසා `WorkshopDispatch::insert($records)` කිරීම.
  3. සියලුම අදාළ orders තනි `Order::whereIn('id', $chunkIds)->update(...)` query එකකින් dispatch තත්ත්වයට පත් කිරීම.
  4. Deadlocks වැළැක්වීමට `DB::transaction(..., 5)` (automatic 5-retries) එක් කිරීම.

### පියවර 5: පූර්ණ පරීක්ෂණ (End-to-End Stress & Verification Test)
* **ගොනුව:** `scratch/verify_enterprise_hardening.php`
* **ක්‍රියාවලිය:**
  1. Subdomain Cache එක නිවැරදිව වැඩ කරන බව තහවුරු කිරීම.
  2. Middleware එකෙන් DDL queries නොයන බව තහවුරු කිරීම.
  3. Bulk Dispatch Action එක තනි Queries 2කින් දුවන බව සහ Execution time එක 10ms පමණ වන බව තහවුරු කිරීම.
  4. Composite Indexes MySQL EXPLAIN query මඟින් Index Scan වන බව ඔප්පු කිරීම.
