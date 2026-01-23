# Learner Progression Report - Design Document

**Version:** 1.0
**Date:** 2026-01-23
**Status:** Design Complete
**Target Plugin:** `wecoza-learners-plugin`

---

## 1. Overview

### 1.1 Purpose
Standalone reporting page tracking learner level progression across time with three reporting levels:
- Individual learner journey
- Employer/company report
- Aggregate statistics dashboard

### 1.2 Key Features
- Search by learner name/ID
- Filter by employer/company
- Timeline visualization: Level + Class Name + Date
- Statistics: progression rates, level distribution, company comparisons
- Export to PDF and Excel

### 1.3 Access
Shortcode: `[wecoza_learner_progression_report]`

### 1.4 Plugin Location
This feature belongs in **wecoza-learners-plugin** (not classes plugin) because:
- Learner-centric reporting
- Lives alongside learner management features
- Cross-references class data but reports on learners

---

## 2. Database Schema

### 2.1 Tables Used

| Table | Source | Purpose |
|-------|--------|---------|
| `learner_progressions` | classes plugin | Core progression records |
| `learners` | learners plugin | Learner details |
| `classes` | classes plugin | Class information |
| `employers` | clients plugin | Company data |

### 2.2 Key Queries

```sql
-- Individual learner progression
SELECT lp.level, lp.date_achieved, c.class_name, c.subject
FROM learner_progressions lp
JOIN classes c ON lp.class_id = c.id
WHERE lp.learner_id = ?
ORDER BY lp.date_achieved ASC;

-- Employer progression (all learners)
SELECT l.id, l.name, lp.level, lp.date_achieved, c.class_name
FROM learners l
JOIN learner_progressions lp ON l.id = lp.learner_id
JOIN classes c ON lp.class_id = c.id
WHERE l.employer_id = ?
ORDER BY l.name, lp.date_achieved ASC;

-- Aggregate statistics
SELECT level, COUNT(*) as count
FROM learner_progressions
GROUP BY level;
```

---

## 3. Technical Architecture

### 3.1 New Files

| File | Location | Purpose |
|------|----------|---------|
| `LearnerProgressionController.php` | `app/Controllers/` | AJAX handlers, data aggregation |
| `LearnerProgressionModel.php` | `app/Models/` | Database queries |
| `progression-report.php` | `app/Views/components/` | Main page template |
| `learner-progression-report.js` | `assets/js/` | Timeline, filters, charts |

### 3.2 AJAX Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `get_learner_progression` | POST | Individual timeline data |
| `get_employer_progression` | POST | All learners for employer |
| `get_progression_statistics` | POST | Aggregate stats |
| `export_progression_pdf` | POST | Generate PDF |
| `export_progression_excel` | POST | Generate Excel |

### 3.3 Dependencies

| Library | Purpose | Status |
|---------|---------|--------|
| Chart.js | Statistics visualizations | Already in plugin |
| TCPDF/DOMPDF | PDF generation | Needs installation |
| PhpSpreadsheet | Excel generation | Needs installation |

---

## 4. UI Design

### 4.1 Page Layout

```
+------------------------------------------------------------------+
| Learner Progression Report                                        |
+------------------------------------------------------------------+
| [Search Learner: ___________] [Employer: ▼ Select ___]  [Search] |
+------------------------------------------------------------------+

+------------------------------------------------------------------+
| STATISTICS DASHBOARD (collapsible)                                |
+------------------------------------------------------------------+
| +------------------+ +------------------+ +------------------+    |
| | Progression Rate | | Level Distribution| | Company Compare  |   |
| | Chart.js Gauge   | | Bar Chart        | | Comparison Table |   |
| +------------------+ +------------------+ +------------------+    |
+------------------------------------------------------------------+

+------------------------------------------------------------------+
| TIMELINE VIEW                                                     |
+------------------------------------------------------------------+
| Learner: John Smith (ABC Corp)                    [PDF] [Excel]  |
+------------------------------------------------------------------+
|  ●━━━━━●━━━━━━━━●━━━━━━━━━━━●                                    |
|  L1    L2       L3          L4                                   |
|                                                                   |
| Level 1 │ Welding Basics - Class A │ 15 Jan 2025                 |
| Level 2 │ Welding Advanced - Class B │ 28 Mar 2025               |
| Level 3 │ Welding Expert - Class C │ 12 Jul 2025                 |
+------------------------------------------------------------------+
```

### 4.2 UI Components

| Component | Description |
|-----------|-------------|
| Search Bar | Learner name/ID autocomplete search |
| Employer Dropdown | Select2 dropdown with company list |
| Statistics Cards | Three Chart.js visualizations |
| Timeline Graphic | Visual progress indicator with dots |
| Detail Table | Level, Class, Date columns |
| Export Buttons | PDF and Excel download buttons |

---

## 5. Statistics Dashboard

### 5.1 Progression Rate
- Gauge/percentage showing learners advancing on schedule
- Formula: (learners advancing within X months) / (total learners) × 100

### 5.2 Level Distribution
- Bar chart showing count of learners at each level
- X-axis: Level 1, Level 2, Level 3, etc.
- Y-axis: Number of learners

### 5.3 Company Comparison
- Table comparing progression rates across employers
- Columns: Company Name, Total Learners, Avg Progression Rate, Top Level Reached

---

## 6. Export Specifications

### 6.1 PDF Export
- Branded header with WeCoza logo
- Learner/employer details
- Timeline visualization (rendered as image)
- Statistics charts (rendered as images)
- Generated via TCPDF or DOMPDF

### 6.2 Excel Export
- Sheet 1: Progression Data
  - Columns: Learner ID, Name, Employer, Level, Class Name, Date Achieved
- Sheet 2: Statistics Summary
  - Level distribution counts
  - Progression rates by employer

---

## 7. Implementation Phases

### Phase 1: Core Infrastructure
- [ ] Create LearnerProgressionController
- [ ] Create LearnerProgressionModel
- [ ] Register shortcode
- [ ] Set up AJAX endpoints

### Phase 2: Individual Learner View
- [ ] Create progression-report.php view
- [ ] Implement learner search
- [ ] Build timeline visualization
- [ ] Create detail table

### Phase 3: Employer Filter
- [ ] Add employer dropdown
- [ ] Implement multi-learner view
- [ ] Group timelines by learner

### Phase 4: Statistics Dashboard
- [ ] Add Chart.js progression rate gauge
- [ ] Add level distribution bar chart
- [ ] Add company comparison table
- [ ] Make dashboard collapsible

### Phase 5: Export Functionality
- [ ] Install TCPDF/DOMPDF
- [ ] Install PhpSpreadsheet
- [ ] Implement PDF export
- [ ] Implement Excel export

---

## 8. Security Considerations

- All AJAX endpoints require `current_user_can()` check
- Input sanitization on learner_id and employer_id
- XSS protection via jQuery `.text()` method
- SQL injection prevention via prepared statements

---

## 9. Testing Checklist

- [ ] Search returns correct learner
- [ ] Employer filter shows all company learners
- [ ] Timeline displays in chronological order
- [ ] Statistics calculate correctly
- [ ] PDF export generates valid file
- [ ] Excel export contains correct data
- [ ] Works on create and update class pages
