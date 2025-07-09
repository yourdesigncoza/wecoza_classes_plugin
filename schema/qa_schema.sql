--
-- QA Integration Schema Updates for WeCoza Classes Plugin
-- PostgreSQL database schema for QA visits and analytics
--

-- Create qa_visits table for detailed QA visit tracking
CREATE TABLE IF NOT EXISTS public.qa_visits (
    visit_id SERIAL PRIMARY KEY,
    class_id INTEGER NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME,
    visit_type VARCHAR(50) DEFAULT 'routine',
    qa_officer_id INTEGER,
    visit_duration INTEGER, -- in minutes
    overall_rating INTEGER CHECK (overall_rating >= 1 AND overall_rating <= 5),
    attendance_count INTEGER,
    instructor_present BOOLEAN DEFAULT TRUE,
    equipment_status VARCHAR(50),
    venue_condition VARCHAR(50),
    safety_compliance BOOLEAN DEFAULT TRUE,
    findings JSONB DEFAULT '[]'::jsonb,
    recommendations JSONB DEFAULT '[]'::jsonb,
    action_items JSONB DEFAULT '[]'::jsonb,
    visit_notes TEXT,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE,
    report_file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    created_by INTEGER,
    
    -- Foreign key constraints
    CONSTRAINT fk_qa_visits_class FOREIGN KEY (class_id) REFERENCES public.classes(class_id) ON DELETE CASCADE
    -- Note: qa_officer_id and created_by reference user IDs but no FK constraint until user table structure is confirmed
);

-- Create qa_metrics table for analytics aggregation
CREATE TABLE IF NOT EXISTS public.qa_metrics (
    metric_id SERIAL PRIMARY KEY,
    metric_period VARCHAR(20) NOT NULL, -- 'weekly', 'monthly', 'quarterly', 'yearly'
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    class_id INTEGER,
    total_visits INTEGER DEFAULT 0,
    average_rating DECIMAL(3,2),
    completion_rate DECIMAL(5,2),
    safety_violations INTEGER DEFAULT 0,
    follow_up_actions INTEGER DEFAULT 0,
    resolved_actions INTEGER DEFAULT 0,
    pending_actions INTEGER DEFAULT 0,
    top_issues JSONB DEFAULT '[]'::jsonb,
    improvement_trends JSONB DEFAULT '[]'::jsonb,
    department_stats JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    -- Foreign key constraints
    CONSTRAINT fk_qa_metrics_class FOREIGN KEY (class_id) REFERENCES public.classes(class_id) ON DELETE CASCADE
);

-- Create qa_findings table for detailed issue tracking
CREATE TABLE IF NOT EXISTS public.qa_findings (
    finding_id SERIAL PRIMARY KEY,
    visit_id INTEGER NOT NULL,
    finding_type VARCHAR(50) NOT NULL, -- 'safety', 'equipment', 'attendance', 'instructor', 'venue'
    severity VARCHAR(20) NOT NULL, -- 'low', 'medium', 'high', 'critical'
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    photo_evidence JSONB DEFAULT '[]'::jsonb,
    corrective_action TEXT,
    deadline DATE,
    status VARCHAR(20) DEFAULT 'open', -- 'open', 'in_progress', 'resolved', 'closed'
    assigned_to INTEGER,
    resolved_date DATE,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    -- Foreign key constraints
    CONSTRAINT fk_qa_findings_visit FOREIGN KEY (visit_id) REFERENCES public.qa_visits(visit_id) ON DELETE CASCADE
    -- Note: assigned_to references user ID but no FK constraint until user table structure is confirmed
);

-- Create indexes for performance optimization
CREATE INDEX IF NOT EXISTS idx_qa_visits_class_id ON public.qa_visits(class_id);
CREATE INDEX IF NOT EXISTS idx_qa_visits_date ON public.qa_visits(visit_date);
CREATE INDEX IF NOT EXISTS idx_qa_visits_officer ON public.qa_visits(qa_officer_id);
CREATE INDEX IF NOT EXISTS idx_qa_visits_rating ON public.qa_visits(overall_rating);
CREATE INDEX IF NOT EXISTS idx_qa_visits_findings ON public.qa_visits USING gin(findings);
CREATE INDEX IF NOT EXISTS idx_qa_visits_recommendations ON public.qa_visits USING gin(recommendations);
CREATE INDEX IF NOT EXISTS idx_qa_visits_action_items ON public.qa_visits USING gin(action_items);

CREATE INDEX IF NOT EXISTS idx_qa_metrics_period ON public.qa_metrics(metric_period, period_start, period_end);
CREATE INDEX IF NOT EXISTS idx_qa_metrics_class_id ON public.qa_metrics(class_id);
CREATE INDEX IF NOT EXISTS idx_qa_metrics_rating ON public.qa_metrics(average_rating);

CREATE INDEX IF NOT EXISTS idx_qa_findings_visit_id ON public.qa_findings(visit_id);
CREATE INDEX IF NOT EXISTS idx_qa_findings_type ON public.qa_findings(finding_type);
CREATE INDEX IF NOT EXISTS idx_qa_findings_severity ON public.qa_findings(severity);
CREATE INDEX IF NOT EXISTS idx_qa_findings_status ON public.qa_findings(status);
CREATE INDEX IF NOT EXISTS idx_qa_findings_assignee ON public.qa_findings(assigned_to);
CREATE INDEX IF NOT EXISTS idx_qa_findings_deadline ON public.qa_findings(deadline);

-- Add comments for documentation
COMMENT ON TABLE public.qa_visits IS 'Detailed QA visit records for class quality assurance tracking';
COMMENT ON TABLE public.qa_metrics IS 'Aggregated QA metrics for analytics and reporting';
COMMENT ON TABLE public.qa_findings IS 'Individual QA findings and corrective actions from visits';

COMMENT ON COLUMN public.qa_visits.findings IS 'JSON array of structured finding objects';
COMMENT ON COLUMN public.qa_visits.recommendations IS 'JSON array of QA officer recommendations';
COMMENT ON COLUMN public.qa_visits.action_items IS 'JSON array of action items with deadlines and assignments';
COMMENT ON COLUMN public.qa_metrics.top_issues IS 'JSON array of most common issues for the period';
COMMENT ON COLUMN public.qa_metrics.improvement_trends IS 'JSON array of improvement metrics over time';
COMMENT ON COLUMN public.qa_metrics.department_stats IS 'JSON object with department-specific statistics';
COMMENT ON COLUMN public.qa_findings.photo_evidence IS 'JSON array of photo file paths and metadata';