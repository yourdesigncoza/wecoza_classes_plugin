# WeCoza Class Capture Form Fields

_Fields marked *(required)* must be completed on the Create Class form._

## Core Info
- **`client_id`** *(required)* — Client picker; changing it filters the available sites (assets/js/class-capture.js:265, app/Views/components/class-capture-partials/create-class.php:19).
- **`site_id`** *(required)* — Site selector tied to the chosen client; triggers address updates (assets/js/class-capture.js:266, app/Views/components/class-capture-partials/create-class.php:32).
- **`site_address`** — Populated with the selected site’s address and shown/hidden automatically (assets/js/class-capture.js:311).
- **`class_subject`** *(required)* — Stores the class subject and feeds exam learner level defaults (assets/js/class-capture.js:516, app/Views/components/class-capture-partials/create-class.php:81).
- **`class_id`** / **`class-select`** — Hidden ID and optional selector that define which class’s data, notes, and QA records load (assets/js/class-capture.js:2541, app/Views/components/class-capture-partials/create-class.php:5).
- **`redirect_url`** — Hidden redirect value used if the server response omits a target URL after saving (assets/js/class-capture.js:1465).

## Key Dates
- **`class_start_date`** *(required)* — Primary start date; drives cascading updates across related date fields (assets/js/class-capture.js:140, app/Views/components/class-capture-partials/create-class.php:104).
- **`schedule_start_date`** *(required)* — Schedule baseline date; auto-filled from the class start when blank and validated before submit (assets/js/class-capture.js:145, app/Views/components/class-capture-partials/create-class.php:117).
- **`schedule_end_date`** *(required)* — Read-only estimated end date that must be present before submission (assets/js/class-capture.js:650, app/Views/components/class-capture-partials/create-class.php:401).
- **`initial_agent_start_date`** *(required)* — Defaults to the class start to seed agent scheduling (assets/js/class-capture.js:158, app/Views/components/class-capture-partials/create-class.php:669).
- **`delivery_date`** *(required)* — Auto-filled from the class start for delivery planning (assets/js/class-capture.js:167, app/Views/components/class-capture-partials/create-class.php:731).
- **`backup_agent_dates[]` / `exception_dates[]`** — Date arrays populated when the class start changes so follow-up rows stay in sync (assets/js/class-capture.js:175, assets/js/class-capture.js:185).

## Scheduling Controls
- **`schedule_day`** *(required – at least one day)* — Day-of-week selector that keeps the schedule start aligned with the chosen weekday (assets/js/class-capture.js:203, app/Views/components/class-capture-partials/create-class.php:143).
- **`schedule_pattern`** *(required)* — Recurrence pattern (weekly, biweekly, monthly) required for schedule validation (assets/js/class-capture.js:663, app/Views/components/class-capture-partials/create-class.php:124).
- **`schedule_day_of_month`** *(required when pattern = `monthly`)* — Monthly schedule anchor day; enforced via validation when needed (assets/js/class-capture.js:678, app/Views/components/class-capture-partials/create-class.php:181).
- **`class_duration`** — Must be a positive value before the form can submit (assets/js/class-capture.js:685).

## Funding & Exams
- **`seta_funded`** *(required)* — Toggles whether SETA details are required and visible (assets/js/class-capture.js:330, app/Views/components/class-capture-partials/create-class.php:506).
- **`seta_id`** *(required when `seta_funded` = Yes)* — SETA selection; shown and marked required when funding is “Yes” (assets/js/class-capture.js:334, app/Views/components/class-capture-partials/create-class.php:518).
- **`exam_class`** *(required)* — Yes/No control that exposes the exam type and learner capture UI (assets/js/class-capture.js:380, app/Views/components/class-capture-partials/create-class.php:530).
- **`exam_type`** *(required when `exam_class` = Yes)* — Required exam classification whenever exam mode is enabled (assets/js/class-capture.js:386, app/Views/components/class-capture-partials/create-class.php:543).
- **`class_learners_data`** — Hidden JSON snapshot of class learners used to seed exam options (assets/js/class-capture.js:427).
- **`exam_learner_select` / `exam_learners`** — Dropdown and hidden JSON that capture selected exam learners; the script also maintains the auto-created `exam_learner_count` field (assets/js/class-capture.js:369, assets/js/class-capture.js:374, assets/js/class-capture.js:448).

## Class Notes
- **`note_class_id`** — Associates notes with the active class when creating or editing (assets/js/class-capture.js:2300).
- **`note_id`** — Hidden identifier used while editing an existing class note (assets/js/class-capture.js:2244).
- **`note_content`** — Main note body with character counting and validation (assets/js/class-capture.js:2594).
- **`class_notes`** — Multi-select category field saved with each note (assets/js/class-capture.js:2623).
- **`note_priority`** — Priority selector stored with the note payload (assets/js/class-capture.js:2248).

## QA Visits
- **`qa_visits_data`** — Hidden JSON that mirrors the visible QA visit rows for submission (assets/js/class-capture.js:818).
- **`qa_visit_dates[]`** — Per-row visit date inputs generated from the QA template (assets/js/class-capture.js:735).
- **`qa_visit_types[]`** — Visit type selectors for each QA row (assets/js/class-capture.js:736).
- **`qa_officers[]`** — Officer name inputs tied to each QA visit (assets/js/class-capture.js:737).
- **`qa_reports[]`** — File inputs for uploading QA visit reports; metadata is tracked alongside (assets/js/class-capture.js:738).

## QA Questions
- **`qa_class_id`** — Links submitted QA questions back to the current class (assets/js/class-capture.js:2821).
- **`qa_question`** — Primary QA question text captured from the modal form (assets/js/class-capture.js:2881).
- **`qa_context`** — Supplemental context/notes stored with the QA question (assets/js/class-capture.js:2881).
- **`qa_attachment`** — Optional file upload attached to a QA question (assets/js/class-capture.js:2788).

## Assignments & Roles
- **`initial_class_agent`** *(required)* — Primary agent selection for the class (assets/js/class-capture.js:158, app/Views/components/class-capture-partials/create-class.php:658).
- **`project_supervisor`** *(required)* — Supervisor assigned to the class (app/Views/components/class-capture-partials/create-class.php:675).
