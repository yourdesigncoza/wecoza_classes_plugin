# Learner Integration Overview

This document describes how learners are managed within the WeCoza Classes Plugin, intended as context for cross-plugin integration with the separate Learners Plugin.

---

## 1. Data Storage Structure

**Database:** PostgreSQL `classes` table with two JSONB fields:

```sql
learner_ids    JSONB DEFAULT '[]'::jsonb  -- Regular class learners
exam_learners  JSONB DEFAULT '[]'::jsonb  -- Learners taking exams (subset)
```

**Learner Object Format:**
```json
{
  "id": "1234",
  "name": "John Smith",
  "level": "BA2LP1",
  "status": "CIC - Currently in Class"
}
```

**Note:** Learner data is **denormalized** — the name is stored at assignment time and won't auto-update if the learner changes their name in the Learners Plugin.

---

## 2. Data Source

Learners originate from **PostgreSQL `public.learners` table** and are retrieved via:

| File | Method | Cache |
|------|--------|-------|
| `app/Repositories/ClassRepository.php:88-164` | `getLearners()` | 12-hour WordPress transient |

**Learner Table Fields Used:**
- `id`, `first_name`, `second_name`, `initials`, `surname`
- `sa_id_no`, `passport_number`
- `city_town_id`, `province_region_id`, `postal_code`

---

## 3. Assignment Flow

| Step | File | Description |
|------|------|-------------|
| 1. Display | `app/Views/components/class-capture-partials/create-class.php:630-760` | Learner selection table with search/pagination |
| 2. JavaScript | `assets/js/learner-selection-table.js` | `LearnerSelectionTable` class handles selection, level/status assignment |
| 3. Hidden field | `#class_learners_data` | JSON array stored in form |
| 4. AJAX | `app/Controllers/ClassAjaxController.php` | `save_class` endpoint |
| 5. Processing | `app/Services/FormDataProcessor.php:87-95` | Decodes JSON, validates data |
| 6. Model | `app/Models/ClassModel.php:392-393` | `setLearnerIds()` stores to DB |

---

## 4. Learner Status Options

| Status | Description |
|--------|-------------|
| `CIC - Currently in Class` | Active enrollment |
| `RBE - Removed by Employer` | Employer withdrawal |
| `DRO - Drop Out` | Learner dropout |

---

## 5. Learner Level Options (60+)

Defined in `assets/js/learner-level-utils.js`:

| Category | Examples |
|----------|----------|
| Basic Literacy | `COMM`, `NUM`, `COMM_NUM` |
| NQF Levels | `CL4`, `NL4`, `LO4`, `HSS4`, `EMS4`, `NS4`, `SMME4` |
| Readiness | `RLC`, `RLN`, `RLF` |
| Bachelor Progression | `BA2LP1-10`, `BA3LP1-11`, `BA4LP1-7` |
| Packages | `WALK`, `HEXA`, `RUN`, `IPC`, `EQ`, `TM`, `SS` |
| Advanced | `EEPDL`, `EEPPF`, `EEPWI`, `EEPEI`, `EEPBI` |

---

## 6. Display Components

| Component | File | Purpose |
|-----------|------|---------|
| Staff Summary | `app/Views/components/single-class/details-staff.php:31-72` | Shows first 3 learners with count badge |
| Learner Modal | `app/Views/components/single-class/modal-learners.php:35-149` | Full learner table with status badges |

---

## 7. Exam Learners

Separate JSONB field for learners designated as exam candidates:

```json
{
  "id": "1234",
  "name": "John Smith",
  "exam_status": "Exam Candidate"
}
```

- Only displayed when `class['exam_class'] = true`
- Subset of regular learners
- Synced via `classes_sync_exam_learner_options()` in JavaScript

---

## 8. Cross-Plugin Access

### Existing WordPress Filters

```php
// Get all class types
$types = apply_filters('wecoza_classes_get_class_types', []);

// Get subjects for a class type
$subjects = apply_filters('wecoza_classes_get_subjects', [], 'AET');
```

### Direct Model Access (Recommended)

```php
use WeCozaClasses\Models\ClassModel;

// Get class with learners
$class = ClassModel::getById($classId);

// Full learner objects (id, name, level, status)
$learners = $class->getLearnerIds();

// Just the ID array
$ids = $class->getLearnerIdsOnly();

// Iterate
foreach ($learners as $learner) {
    echo $learner['id'];
    echo $learner['name'];
    echo $learner['status'];
    echo $learner['level'];
}
```

### Backward Compatibility

The `ClassModel` handles two data formats:

| Format | Example | Handler |
|--------|---------|---------|
| Legacy (ID array) | `[1234, 5678]` | `getLearnerData()` normalizes |
| Current (object array) | `[{id:"1234", name:"John", ...}]` | Direct access |

---

## 9. Key Files Reference

| Component | File |
|-----------|------|
| Model | `app/Models/ClassModel.php` (lines 34-35, 392-521) |
| Repository | `app/Repositories/ClassRepository.php` (lines 88-164) |
| Form Processing | `app/Services/FormDataProcessor.php` (lines 87-105, 641-642) |
| AJAX Handler | `app/Controllers/ClassAjaxController.php` (lines 66-150) |
| Form View | `app/Views/components/class-capture-partials/create-class.php` (lines 630-783) |
| Display View | `app/Views/components/single-class/details-staff.php` (lines 31-72) |
| Modal View | `app/Views/components/single-class/modal-learners.php` (lines 35-149) |
| JS Selection | `assets/js/learner-selection-table.js` (700 lines) |
| JS Levels | `assets/js/learner-level-utils.js` (101 lines) |

---

## 10. Missing Hooks (Enhancement Opportunities)

Currently no events fired when learners are assigned/removed. Recommended additions:

```php
// Before/after learner save
do_action('wecoza_classes_before_save_learners', $class_id, $learner_ids);
do_action('wecoza_classes_after_save_learners', $class_id, $learner_ids);

// Individual learner events
do_action('wecoza_classes_learner_assigned', $class_id, $learner_id, $learner_data);
do_action('wecoza_classes_learner_removed', $class_id, $learner_id);

// Filter learner data
$learners = apply_filters('wecoza_classes_get_class_learners', $learners, $class_id);

// After class save (includes learner data)
do_action('wecoza_classes_class_saved', $class_id, $learner_ids);
```

---

## 11. Security Considerations

| Concern | Implementation |
|---------|----------------|
| XSS Prevention | `esc_html()` in PHP, `escapeHtml()` in JS |
| CSRF Protection | `wecoza_class_nonce` verified in AJAX handlers |
| Access Control | `manage_options` capability for assignment |
| SQL Injection | PDO prepared statements throughout |
| Data Validation | IDs cast to int, names sanitized, status/level whitelist validated |

---

## 12. Data Flow Diagram

```
┌─────────────────────────────┐
│ PostgreSQL learners table   │
│ (source of truth)           │
└─────────────┬───────────────┘
              │ ClassRepository::getLearners()
              ▼
┌─────────────────────────────┐
│ Form View (create-class.php)│
│ - Learner selection table   │
│ - Search/sort/pagination    │
└─────────────┬───────────────┘
              │ JavaScript (LearnerSelectionTable)
              ▼
┌─────────────────────────────┐
│ Hidden field                │
│ #class_learners_data (JSON) │
└─────────────┬───────────────┘
              │ AJAX POST save_class
              ▼
┌─────────────────────────────┐
│ FormDataProcessor           │
│ - Decode JSON               │
│ - Validate/sanitize         │
└─────────────┬───────────────┘
              │ ClassModel::setLearnerIds()
              ▼
┌─────────────────────────────┐
│ PostgreSQL classes table    │
│ learner_ids JSONB column    │
└─────────────────────────────┘
```

---

*Last updated: January 2026*
