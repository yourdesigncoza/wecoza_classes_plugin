-- Add exam_learners field to classes table
-- This field will store which learners are taking exams as a JSONB array

ALTER TABLE public.classes 
ADD COLUMN IF NOT EXISTS exam_learners jsonb DEFAULT '[]'::jsonb;

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_classes_exam_learners 
ON public.classes USING gin (exam_learners);

-- Add comment to document the field
COMMENT ON COLUMN public.classes.exam_learners IS 'JSON array storing exam learner IDs and metadata for learners taking exams';