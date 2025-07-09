-- PostgreSQL Database Schema Dump
-- Generated on: 2025-07-09 13:53:00
-- Database: defaultdb
-- PostgreSQL Version: 16.9

-- ===========================================
-- TABLE LIST (45 tables found)
-- ===========================================
-- agent_absences
-- agent_notes  
-- agent_orders
-- agent_products
-- agent_qa_visits
-- agent_replacements
-- agents
-- attendance_records
-- attendance_registers
-- class_agents
-- class_notes
-- class_schedules
-- class_subjects
-- classes
-- client_communications
-- client_contact_persons
-- clients
-- collections
-- deliveries
-- employers
-- exam_results
-- exams
-- files
-- history
-- learner_placement_level
-- learner_portfolios
-- learner_products
-- learner_progressions
-- learner_qualifications
-- learners
-- locations
-- products
-- progress_reports
-- qa_reports
-- sites
-- supervisors
-- user_permissions
-- user_roles
-- users
-- wecoza_class_backup_agents
-- wecoza_class_dates
-- wecoza_class_learners
-- wecoza_class_notes
-- wecoza_class_schedule
-- wecoza_classes

-- ===========================================
-- DETAILED SCHEMA FOLLOWS BELOW
-- ===========================================

-- ===========================================
-- QA RELATED TABLES SCHEMA
-- ===========================================

-- Table: agent_qa_visits
CREATE TABLE agent_qa_visits (
    visit_id INTEGER NOT NULL DEFAULT nextval('agent_qa_visits_visit_id_seq'::regclass),
    agent_id INTEGER,
    class_id INTEGER,
    visit_date DATE,
    qa_report_id INTEGER
);

-- Table: qa_reports
CREATE TABLE qa_reports (
    qa_report_id INTEGER NOT NULL DEFAULT nextval('qa_reports_qa_report_id_seq'::regclass),
    class_id INTEGER,
    agent_id INTEGER,
    report_date DATE,
    report_file VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

-- Table: class_notes
CREATE TABLE class_notes (
    note_id INTEGER NOT NULL DEFAULT nextval('class_notes_note_id_seq'::regclass),
    class_id INTEGER,
    note TEXT,
    note_date TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

-- Table: wecoza_class_notes
CREATE TABLE wecoza_class_notes (
    id INTEGER NOT NULL DEFAULT nextval('wecoza_class_notes_id_seq'::regclass),
    class_id INTEGER NOT NULL,
    note_type VARCHAR(50) NOT NULL,
    note_content TEXT NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: wecoza_classes
CREATE TABLE wecoza_classes (
    id INTEGER NOT NULL DEFAULT nextval('wecoza_classes_id_seq'::regclass),
    client_id INTEGER NOT NULL,
    site_id INTEGER NOT NULL,
    site_address TEXT NOT NULL,
    class_type VARCHAR(50) NOT NULL,
    class_subject VARCHAR(50),
    class_code VARCHAR(50),
    class_duration INTEGER,
    class_start_date DATE NOT NULL,
    seta_funded BOOLEAN NOT NULL DEFAULT false,
    seta_id INTEGER,
    exam_class BOOLEAN NOT NULL DEFAULT false,
    exam_type VARCHAR(50),
    qa_visit_dates TEXT,
    class_agent INTEGER NOT NULL,
    project_supervisor INTEGER,
    delivery_date DATE,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

-- ===========================================
-- DATABASE STATISTICS
-- ===========================================
-- Total Tables: 45
-- Schema Generated: 2025-07-09 13:53:00
-- Database Version: PostgreSQL 16.9
-- Database Name: defaultdb
-- Database User: doadmin

-- ===========================================
-- QA SYSTEM INTEGRATION NOTES
-- ===========================================
-- Key tables for QA integration:
-- - qa_reports: Main QA reporting table
-- - agent_qa_visits: QA visit tracking
-- - wecoza_class_notes: Class-specific notes
-- - wecoza_classes: Main class information
-- - class_notes: General class notes

-- Important relationships:
-- - qa_reports.class_id -> wecoza_classes.id
-- - agent_qa_visits.class_id -> wecoza_classes.id
-- - wecoza_class_notes.class_id -> wecoza_classes.id
-- - class_notes.class_id -> wecoza_classes.id (may reference different classes table)

-- ===========================================
-- END OF SCHEMA DUMP
-- ===========================================
