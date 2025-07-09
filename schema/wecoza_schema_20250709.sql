--
-- PostgreSQL database dump
--

-- Dumped from database version 16.9
-- Dumped by pg_dump version 17.5 (Ubuntu 17.5-1.pgdg22.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: doadmin
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO doadmin;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: doadmin
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: agent_absences; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agent_absences (
    absence_id integer NOT NULL,
    agent_id integer,
    class_id integer,
    absence_date date,
    reason text,
    reported_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.agent_absences OWNER TO doadmin;

--
-- Name: TABLE agent_absences; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agent_absences IS 'Records instances when agents are absent from classes';


--
-- Name: COLUMN agent_absences.absence_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_absences.absence_id IS 'Unique internal absence ID';


--
-- Name: COLUMN agent_absences.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_absences.agent_id IS 'Reference to the absent agent';


--
-- Name: COLUMN agent_absences.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_absences.class_id IS 'Reference to the class affected by the absence';


--
-- Name: COLUMN agent_absences.absence_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_absences.absence_date IS 'Date of the agent''s absence';


--
-- Name: COLUMN agent_absences.reason; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_absences.reason IS 'Reason for the agent''s absence';


--
-- Name: COLUMN agent_absences.reported_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_absences.reported_at IS 'Timestamp when the absence was reported';


--
-- Name: agent_absences_absence_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.agent_absences_absence_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agent_absences_absence_id_seq OWNER TO doadmin;

--
-- Name: agent_absences_absence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.agent_absences_absence_id_seq OWNED BY public.agent_absences.absence_id;


--
-- Name: agent_notes; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agent_notes (
    note_id integer NOT NULL,
    agent_id integer,
    note text,
    note_date timestamp without time zone DEFAULT now()
);


ALTER TABLE public.agent_notes OWNER TO doadmin;

--
-- Name: TABLE agent_notes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agent_notes IS 'Stores historical notes and remarks about agents';


--
-- Name: COLUMN agent_notes.note_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_notes.note_id IS 'Unique internal note ID';


--
-- Name: COLUMN agent_notes.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_notes.agent_id IS 'Reference to the agent';


--
-- Name: COLUMN agent_notes.note; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_notes.note IS 'Content of the note regarding the agent';


--
-- Name: COLUMN agent_notes.note_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_notes.note_date IS 'Timestamp when the note was created';


--
-- Name: agent_notes_note_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.agent_notes_note_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agent_notes_note_id_seq OWNER TO doadmin;

--
-- Name: agent_notes_note_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.agent_notes_note_id_seq OWNED BY public.agent_notes.note_id;


--
-- Name: agent_orders; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agent_orders (
    order_id integer NOT NULL,
    agent_id integer,
    class_id integer,
    order_number character varying(50),
    class_time time without time zone,
    class_days character varying(50),
    order_hours integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.agent_orders OWNER TO doadmin;

--
-- Name: TABLE agent_orders; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agent_orders IS 'Stores order information related to agents and classes';


--
-- Name: COLUMN agent_orders.order_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.order_id IS 'Unique internal order ID';


--
-- Name: COLUMN agent_orders.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.agent_id IS 'Reference to the agent';


--
-- Name: COLUMN agent_orders.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.class_id IS 'Reference to the class';


--
-- Name: COLUMN agent_orders.order_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.order_number IS 'Valid order number associated with the agent';


--
-- Name: COLUMN agent_orders.class_time; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.class_time IS 'Time when the class is scheduled';


--
-- Name: COLUMN agent_orders.class_days; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.class_days IS 'Days when the class is scheduled';


--
-- Name: COLUMN agent_orders.order_hours; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.order_hours IS 'Number of hours linked to the agent''s order for a specific class';


--
-- Name: COLUMN agent_orders.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.created_at IS 'Timestamp when the order record was created';


--
-- Name: COLUMN agent_orders.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_orders.updated_at IS 'Timestamp when the order record was last updated';


--
-- Name: agent_orders_order_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.agent_orders_order_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agent_orders_order_id_seq OWNER TO doadmin;

--
-- Name: agent_orders_order_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.agent_orders_order_id_seq OWNED BY public.agent_orders.order_id;


--
-- Name: agent_products; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agent_products (
    agent_id integer NOT NULL,
    product_id integer NOT NULL,
    trained_start_date date,
    trained_end_date date
);


ALTER TABLE public.agent_products OWNER TO doadmin;

--
-- Name: TABLE agent_products; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agent_products IS 'Associates agents with the products they are trained to teach';


--
-- Name: COLUMN agent_products.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_products.agent_id IS 'Reference to the agent';


--
-- Name: COLUMN agent_products.product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_products.product_id IS 'Reference to the product the agent is trained in';


--
-- Name: COLUMN agent_products.trained_start_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_products.trained_start_date IS 'Start date when the agent began training in the product';


--
-- Name: COLUMN agent_products.trained_end_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_products.trained_end_date IS 'End date when the agent finished training in the product';


--
-- Name: agent_qa_visits; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agent_qa_visits (
    visit_id integer NOT NULL,
    agent_id integer,
    class_id integer,
    visit_date date,
    qa_report_id integer
);


ALTER TABLE public.agent_qa_visits OWNER TO doadmin;

--
-- Name: TABLE agent_qa_visits; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agent_qa_visits IS 'Records QA visits involving agents and classes';


--
-- Name: COLUMN agent_qa_visits.visit_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_qa_visits.visit_id IS 'Unique internal QA visit ID';


--
-- Name: COLUMN agent_qa_visits.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_qa_visits.agent_id IS 'Reference to the agent';


--
-- Name: COLUMN agent_qa_visits.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_qa_visits.class_id IS 'Reference to the class';


--
-- Name: COLUMN agent_qa_visits.visit_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_qa_visits.visit_date IS 'Date of the QA visit';


--
-- Name: COLUMN agent_qa_visits.qa_report_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_qa_visits.qa_report_id IS 'Reference to the associated QA report';


--
-- Name: agent_qa_visits_visit_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.agent_qa_visits_visit_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agent_qa_visits_visit_id_seq OWNER TO doadmin;

--
-- Name: agent_qa_visits_visit_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.agent_qa_visits_visit_id_seq OWNED BY public.agent_qa_visits.visit_id;


--
-- Name: agent_replacements; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agent_replacements (
    replacement_id integer NOT NULL,
    class_id integer,
    original_agent_id integer,
    replacement_agent_id integer,
    start_date date,
    end_date date,
    reason text
);


ALTER TABLE public.agent_replacements OWNER TO doadmin;

--
-- Name: TABLE agent_replacements; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agent_replacements IS 'Records instances of agent replacements in classes';


--
-- Name: COLUMN agent_replacements.replacement_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.replacement_id IS 'Unique internal replacement ID';


--
-- Name: COLUMN agent_replacements.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.class_id IS 'Reference to the class';


--
-- Name: COLUMN agent_replacements.original_agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.original_agent_id IS 'Reference to the original agent';


--
-- Name: COLUMN agent_replacements.replacement_agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.replacement_agent_id IS 'Reference to the replacement agent';


--
-- Name: COLUMN agent_replacements.start_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.start_date IS 'Date when the replacement starts';


--
-- Name: COLUMN agent_replacements.end_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.end_date IS 'Date when the replacement ends';


--
-- Name: COLUMN agent_replacements.reason; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agent_replacements.reason IS 'Reason for the agent''s replacement';


--
-- Name: agent_replacements_replacement_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.agent_replacements_replacement_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agent_replacements_replacement_id_seq OWNER TO doadmin;

--
-- Name: agent_replacements_replacement_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.agent_replacements_replacement_id_seq OWNED BY public.agent_replacements.replacement_id;


--
-- Name: agents; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.agents (
    agent_id integer NOT NULL,
    first_name character varying(50),
    initials character varying(10),
    surname character varying(50),
    gender character varying(10),
    race character varying(20),
    sa_id_no character varying(20),
    passport_number character varying(20),
    tel_number character varying(20),
    email_address character varying(100),
    residential_address_line character varying(100),
    residential_suburb character varying(50),
    residential_town_id integer,
    residential_postal_code character varying(10),
    preferred_working_area_1 integer,
    preferred_working_area_2 integer,
    preferred_working_area_3 integer,
    highest_qualification character varying(100),
    sace_registration_number character varying(50),
    sace_registration_date date,
    sace_expiry_date date,
    quantum_assesment numeric(5,2),
    agent_training_date date,
    bank_name character varying(50),
    bank_branch_code character varying(20),
    bank_account_number character varying(30),
    signed_agreement boolean,
    signed_agreement_date date,
    agent_notes text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.agents OWNER TO doadmin;

--
-- Name: TABLE agents; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.agents IS 'Stores information about agents (instructors or facilitators)';


--
-- Name: COLUMN agents.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.agent_id IS 'Unique internal agent ID';


--
-- Name: COLUMN agents.first_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.first_name IS 'Agent''s first name';


--
-- Name: COLUMN agents.initials; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.initials IS 'Agent''s initials';


--
-- Name: COLUMN agents.surname; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.surname IS 'Agent''s surname';


--
-- Name: COLUMN agents.gender; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.gender IS 'Agent''s gender';


--
-- Name: COLUMN agents.race; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.race IS 'Agent''s race; options include ''African'', ''Coloured'', ''White'', ''Indian''';


--
-- Name: COLUMN agents.sa_id_no; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.sa_id_no IS 'Agent''s South African ID number';


--
-- Name: COLUMN agents.passport_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.passport_number IS 'Agent''s passport number if they are a foreigner';


--
-- Name: COLUMN agents.tel_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.tel_number IS 'Agent''s primary telephone number';


--
-- Name: COLUMN agents.email_address; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.email_address IS 'Agent''s email address';


--
-- Name: COLUMN agents.residential_address_line; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.residential_address_line IS 'Agent''s residential street address';


--
-- Name: COLUMN agents.residential_suburb; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.residential_suburb IS 'Agent''s residential suburb';


--
-- Name: COLUMN agents.residential_town_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.residential_town_id IS 'Reference to the town where the agent lives';


--
-- Name: COLUMN agents.residential_postal_code; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.residential_postal_code IS 'Postal code of the agent''s residential area';


--
-- Name: COLUMN agents.preferred_working_area_1; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.preferred_working_area_1 IS 'Agent''s first preferred working area';


--
-- Name: COLUMN agents.preferred_working_area_2; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.preferred_working_area_2 IS 'Agent''s second preferred working area';


--
-- Name: COLUMN agents.preferred_working_area_3; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.preferred_working_area_3 IS 'Agent''s third preferred working area';


--
-- Name: COLUMN agents.highest_qualification; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.highest_qualification IS 'Highest qualification the agent has achieved';


--
-- Name: COLUMN agents.sace_registration_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.sace_registration_number IS 'Agent''s SACE (South African Council for Educators) registration number';


--
-- Name: COLUMN agents.sace_registration_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.sace_registration_date IS 'Date when the agent''s SACE registration became effective';


--
-- Name: COLUMN agents.sace_expiry_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.sace_expiry_date IS 'Expiry date of the agent''s provisional SACE registration';


--
-- Name: COLUMN agents.quantum_assesment; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.quantum_assesment IS 'Agent''s competence score in Communications (percentage)';


--
-- Name: COLUMN agents.agent_training_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.agent_training_date IS 'Date when the agent received induction training';


--
-- Name: COLUMN agents.bank_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.bank_name IS 'Name of the agent''s bank';


--
-- Name: COLUMN agents.bank_branch_code; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.bank_branch_code IS 'Branch code of the agent''s bank';


--
-- Name: COLUMN agents.bank_account_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.bank_account_number IS 'Agent''s bank account number';


--
-- Name: COLUMN agents.signed_agreement; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.signed_agreement IS 'Indicates if the agent has a signed agreement (true) or not (false)';


--
-- Name: COLUMN agents.signed_agreement_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.signed_agreement_date IS 'Date when the agent signed the agreement';


--
-- Name: COLUMN agents.agent_notes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.agent_notes IS 'Notes regarding the agent''s performance, issues, or other relevant information';


--
-- Name: COLUMN agents.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.created_at IS 'Timestamp when the agent record was created';


--
-- Name: COLUMN agents.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.agents.updated_at IS 'Timestamp when the agent record was last updated';


--
-- Name: agents_agent_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.agents_agent_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agents_agent_id_seq OWNER TO doadmin;

--
-- Name: agents_agent_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.agents_agent_id_seq OWNED BY public.agents.agent_id;


--
-- Name: attendance_records; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.attendance_records (
    register_id integer NOT NULL,
    learner_id integer NOT NULL,
    status character varying(20)
);


ALTER TABLE public.attendance_records OWNER TO doadmin;

--
-- Name: TABLE attendance_records; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.attendance_records IS 'Associates learners with their attendance status on specific dates';


--
-- Name: COLUMN attendance_records.register_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_records.register_id IS 'Reference to the attendance register';


--
-- Name: COLUMN attendance_records.learner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_records.learner_id IS 'Reference to the learner';


--
-- Name: COLUMN attendance_records.status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_records.status IS 'Attendance status of the learner (e.g., ''Present'', ''Absent'')';


--
-- Name: attendance_registers; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.attendance_registers (
    register_id integer NOT NULL,
    class_id integer,
    date date,
    agent_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.attendance_registers OWNER TO doadmin;

--
-- Name: TABLE attendance_registers; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.attendance_registers IS 'Records attendance registers for classes';


--
-- Name: COLUMN attendance_registers.register_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_registers.register_id IS 'Unique internal attendance register ID';


--
-- Name: COLUMN attendance_registers.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_registers.class_id IS 'Reference to the class';


--
-- Name: COLUMN attendance_registers.date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_registers.date IS 'Date of the attendance';


--
-- Name: COLUMN attendance_registers.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_registers.agent_id IS 'Reference to the agent who conducted the attendance';


--
-- Name: COLUMN attendance_registers.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_registers.created_at IS 'Timestamp when the attendance register was created';


--
-- Name: COLUMN attendance_registers.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.attendance_registers.updated_at IS 'Timestamp when the attendance register was last updated';


--
-- Name: attendance_registers_register_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.attendance_registers_register_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attendance_registers_register_id_seq OWNER TO doadmin;

--
-- Name: attendance_registers_register_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.attendance_registers_register_id_seq OWNED BY public.attendance_registers.register_id;


--
-- Name: class_agents; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.class_agents (
    class_id integer NOT NULL,
    agent_id integer NOT NULL,
    start_date date NOT NULL,
    end_date date,
    role character varying(50)
);


ALTER TABLE public.class_agents OWNER TO doadmin;

--
-- Name: TABLE class_agents; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.class_agents IS 'Associates agents with classes they facilitate, including their roles and durations';


--
-- Name: COLUMN class_agents.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_agents.class_id IS 'Reference to the class';


--
-- Name: COLUMN class_agents.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_agents.agent_id IS 'Reference to the agent facilitating the class';


--
-- Name: COLUMN class_agents.start_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_agents.start_date IS 'Date when the agent started facilitating the class';


--
-- Name: COLUMN class_agents.end_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_agents.end_date IS 'Date when the agent stopped facilitating the class';


--
-- Name: COLUMN class_agents.role; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_agents.role IS 'Role of the agent in the class (e.g., ''Original'', ''Backup'', ''Replacement'')';


--
-- Name: class_notes; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.class_notes (
    note_id integer NOT NULL,
    class_id integer,
    note text,
    note_date timestamp without time zone DEFAULT now()
);


ALTER TABLE public.class_notes OWNER TO doadmin;

--
-- Name: TABLE class_notes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.class_notes IS 'Stores historical notes and remarks about classes';


--
-- Name: COLUMN class_notes.note_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_notes.note_id IS 'Unique internal note ID';


--
-- Name: COLUMN class_notes.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_notes.class_id IS 'Reference to the class';


--
-- Name: COLUMN class_notes.note; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_notes.note IS 'Content of the note regarding the class';


--
-- Name: COLUMN class_notes.note_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_notes.note_date IS 'Timestamp when the note was created';


--
-- Name: class_notes_note_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.class_notes_note_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.class_notes_note_id_seq OWNER TO doadmin;

--
-- Name: class_notes_note_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.class_notes_note_id_seq OWNED BY public.class_notes.note_id;


--
-- Name: class_schedules; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.class_schedules (
    schedule_id integer NOT NULL,
    class_id integer,
    day_of_week character varying(10),
    start_time time without time zone,
    end_time time without time zone
);


ALTER TABLE public.class_schedules OWNER TO doadmin;

--
-- Name: TABLE class_schedules; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.class_schedules IS 'Stores scheduling information for classes';


--
-- Name: COLUMN class_schedules.schedule_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_schedules.schedule_id IS 'Unique internal schedule ID';


--
-- Name: COLUMN class_schedules.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_schedules.class_id IS 'Reference to the class';


--
-- Name: COLUMN class_schedules.day_of_week; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_schedules.day_of_week IS 'Day of the week when the class occurs (e.g., ''Monday'')';


--
-- Name: COLUMN class_schedules.start_time; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_schedules.start_time IS 'Class start time';


--
-- Name: COLUMN class_schedules.end_time; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_schedules.end_time IS 'Class end time';


--
-- Name: class_schedules_schedule_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.class_schedules_schedule_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.class_schedules_schedule_id_seq OWNER TO doadmin;

--
-- Name: class_schedules_schedule_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.class_schedules_schedule_id_seq OWNED BY public.class_schedules.schedule_id;


--
-- Name: class_subjects; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.class_subjects (
    class_id integer NOT NULL,
    product_id integer NOT NULL
);


ALTER TABLE public.class_subjects OWNER TO doadmin;

--
-- Name: TABLE class_subjects; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.class_subjects IS 'Associates classes with the subjects or products being taught';


--
-- Name: COLUMN class_subjects.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_subjects.class_id IS 'Reference to the class';


--
-- Name: COLUMN class_subjects.product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.class_subjects.product_id IS 'Reference to the subject or product taught in the class';


--
-- Name: classes; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.classes (
    class_id integer NOT NULL,
    client_id integer,
    class_address_line character varying(100),
    class_type character varying(50),
    original_start_date date,
    seta_funded boolean,
    seta character varying(100),
    exam_class boolean,
    exam_type character varying(50),
    project_supervisor_id integer,
    delivery_date date,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    site_id integer,
    class_subject character varying(100),
    class_code character varying(50),
    class_duration integer,
    qa_visit_dates text,
    class_agent integer,
    learner_ids jsonb DEFAULT '[]'::jsonb,
    backup_agent_ids jsonb DEFAULT '[]'::jsonb,
    schedule_data jsonb DEFAULT '[]'::jsonb,
    stop_restart_dates jsonb DEFAULT '[]'::jsonb,
    class_notes_data jsonb DEFAULT '[]'::jsonb,
    initial_class_agent integer,
    initial_agent_start_date date,
    qa_reports jsonb DEFAULT '[]'::jsonb,
    exam_learners jsonb DEFAULT '[]'::jsonb
);


ALTER TABLE public.classes OWNER TO doadmin;

--
-- Name: TABLE classes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.classes IS 'Stores information about classes, including scheduling and associations';


--
-- Name: COLUMN classes.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.class_id IS 'Unique internal class ID';


--
-- Name: COLUMN classes.client_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.client_id IS 'Reference to the client associated with the class';


--
-- Name: COLUMN classes.class_address_line; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.class_address_line IS 'Street address where the class takes place';


--
-- Name: COLUMN classes.class_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.class_type IS 'Type of class; determines the ''rules'' (e.g., ''Employed'', ''Community'')';


--
-- Name: COLUMN classes.original_start_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.original_start_date IS 'Original start date of the class';


--
-- Name: COLUMN classes.seta_funded; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.seta_funded IS 'Indicates if the project is SETA funded (true) or not (false)';


--
-- Name: COLUMN classes.seta; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.seta IS 'Name of the SETA (Sector Education and Training Authority) the client belongs to';


--
-- Name: COLUMN classes.exam_class; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.exam_class IS 'Indicates if this is an exam project (true) or not (false)';


--
-- Name: COLUMN classes.exam_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.exam_type IS 'Type of exam associated with the class';


--
-- Name: COLUMN classes.project_supervisor_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.project_supervisor_id IS 'Reference to the project supervisor managing the class';


--
-- Name: COLUMN classes.delivery_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.delivery_date IS 'Date when materials or resources must be delivered to the class';


--
-- Name: COLUMN classes.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.created_at IS 'Timestamp when the class record was created';


--
-- Name: COLUMN classes.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.updated_at IS 'Timestamp when the class record was last updated';


--
-- Name: COLUMN classes.qa_reports; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.qa_reports IS 'JSON array storing QA report file paths and metadata';


--
-- Name: COLUMN classes.exam_learners; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.classes.exam_learners IS 'JSON array storing exam learner IDs and 
  metadata for learners taking exams';


--
-- Name: classes_class_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.classes_class_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.classes_class_id_seq OWNER TO doadmin;

--
-- Name: classes_class_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.classes_class_id_seq OWNED BY public.classes.class_id;


--
-- Name: client_communications; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.client_communications (
    communication_id integer NOT NULL,
    client_id integer,
    communication_type character varying(50),
    subject character varying(100),
    content text,
    communication_date timestamp without time zone DEFAULT now(),
    user_id integer
);


ALTER TABLE public.client_communications OWNER TO doadmin;

--
-- Name: TABLE client_communications; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.client_communications IS 'Stores records of communications with clients';


--
-- Name: COLUMN client_communications.communication_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.communication_id IS 'Unique internal communication ID';


--
-- Name: COLUMN client_communications.client_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.client_id IS 'Reference to the client';


--
-- Name: COLUMN client_communications.communication_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.communication_type IS 'Type of communication (e.g., ''Email'', ''Phone Call'')';


--
-- Name: COLUMN client_communications.subject; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.subject IS 'Subject of the communication';


--
-- Name: COLUMN client_communications.content; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.content IS 'Content or summary of the communication';


--
-- Name: COLUMN client_communications.communication_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.communication_date IS 'Date and time when the communication occurred';


--
-- Name: COLUMN client_communications.user_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_communications.user_id IS 'Reference to the user who communicated with the client';


--
-- Name: client_communications_communication_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.client_communications_communication_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.client_communications_communication_id_seq OWNER TO doadmin;

--
-- Name: client_communications_communication_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.client_communications_communication_id_seq OWNED BY public.client_communications.communication_id;


--
-- Name: client_contact_persons; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.client_contact_persons (
    contact_id integer NOT NULL,
    client_id integer,
    first_name character varying(50),
    surname character varying(50),
    email character varying(100),
    cellphone_number character varying(20),
    tel_number character varying(20),
    "position" character varying(50)
);


ALTER TABLE public.client_contact_persons OWNER TO doadmin;

--
-- Name: TABLE client_contact_persons; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.client_contact_persons IS 'Stores contact person information for clients';


--
-- Name: COLUMN client_contact_persons.contact_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.contact_id IS 'Unique internal contact person ID';


--
-- Name: COLUMN client_contact_persons.client_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.client_id IS 'Reference to the client';


--
-- Name: COLUMN client_contact_persons.first_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.first_name IS 'First name of the contact person';


--
-- Name: COLUMN client_contact_persons.surname; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.surname IS 'Surname of the contact person';


--
-- Name: COLUMN client_contact_persons.email; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.email IS 'Email address of the contact person';


--
-- Name: COLUMN client_contact_persons.cellphone_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.cellphone_number IS 'Cellphone number of the contact person';


--
-- Name: COLUMN client_contact_persons.tel_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons.tel_number IS 'Landline number of the contact person';


--
-- Name: COLUMN client_contact_persons."position"; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.client_contact_persons."position" IS 'Position or role of the contact person at the client company';


--
-- Name: client_contact_persons_contact_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.client_contact_persons_contact_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.client_contact_persons_contact_id_seq OWNER TO doadmin;

--
-- Name: client_contact_persons_contact_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.client_contact_persons_contact_id_seq OWNED BY public.client_contact_persons.contact_id;


--
-- Name: clients; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.clients (
    client_id integer NOT NULL,
    client_name character varying(100),
    branch_of integer,
    company_registration_number character varying(50),
    address_line character varying(100),
    suburb character varying(50),
    town_id integer,
    postal_code character varying(10),
    seta character varying(100),
    client_status character varying(50),
    financial_year_end date,
    bbbee_verification_date date,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.clients OWNER TO doadmin;

--
-- Name: TABLE clients; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.clients IS 'Stores information about clients (companies or organizations)';


--
-- Name: COLUMN clients.client_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.client_id IS 'Unique internal client ID';


--
-- Name: COLUMN clients.client_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.client_name IS 'Name of the client company or organization';


--
-- Name: COLUMN clients.branch_of; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.branch_of IS 'Reference to the parent client if this client is a branch';


--
-- Name: COLUMN clients.company_registration_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.company_registration_number IS 'Company registration number of the client';


--
-- Name: COLUMN clients.address_line; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.address_line IS 'Client''s street address';


--
-- Name: COLUMN clients.suburb; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.suburb IS 'Suburb where the client is located';


--
-- Name: COLUMN clients.town_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.town_id IS 'Reference to the town where the client is located';


--
-- Name: COLUMN clients.postal_code; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.postal_code IS 'Postal code of the client''s location';


--
-- Name: COLUMN clients.seta; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.seta IS 'SETA the client belongs to';


--
-- Name: COLUMN clients.client_status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.client_status IS 'Current status of the client (e.g., ''Active Client'', ''Lost Client'')';


--
-- Name: COLUMN clients.financial_year_end; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.financial_year_end IS 'Date of the client''s financial year-end';


--
-- Name: COLUMN clients.bbbee_verification_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.bbbee_verification_date IS 'Date of the client''s BBBEE verification';


--
-- Name: COLUMN clients.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.created_at IS 'Timestamp when the client record was created';


--
-- Name: COLUMN clients.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.clients.updated_at IS 'Timestamp when the client record was last updated';


--
-- Name: clients_client_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.clients_client_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clients_client_id_seq OWNER TO doadmin;

--
-- Name: clients_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.clients_client_id_seq OWNED BY public.clients.client_id;


--
-- Name: collections; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.collections (
    collection_id integer NOT NULL,
    class_id integer,
    collection_date date,
    items text,
    status character varying(20),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.collections OWNER TO doadmin;

--
-- Name: TABLE collections; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.collections IS 'Records collections made from classes';


--
-- Name: COLUMN collections.collection_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.collection_id IS 'Unique internal collection ID';


--
-- Name: COLUMN collections.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.class_id IS 'Reference to the class';


--
-- Name: COLUMN collections.collection_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.collection_date IS 'Date when the collection is scheduled or occurred';


--
-- Name: COLUMN collections.items; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.items IS 'Items collected from the class';


--
-- Name: COLUMN collections.status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.status IS 'Collection status (e.g., ''Pending'', ''Collected'')';


--
-- Name: COLUMN collections.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.created_at IS 'Timestamp when the collection record was created';


--
-- Name: COLUMN collections.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.collections.updated_at IS 'Timestamp when the collection record was last updated';


--
-- Name: collections_collection_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.collections_collection_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.collections_collection_id_seq OWNER TO doadmin;

--
-- Name: collections_collection_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.collections_collection_id_seq OWNED BY public.collections.collection_id;


--
-- Name: deliveries; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.deliveries (
    delivery_id integer NOT NULL,
    class_id integer,
    delivery_date date,
    items text,
    status character varying(20),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.deliveries OWNER TO doadmin;

--
-- Name: TABLE deliveries; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.deliveries IS 'Records deliveries made to classes';


--
-- Name: COLUMN deliveries.delivery_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.delivery_id IS 'Unique internal delivery ID';


--
-- Name: COLUMN deliveries.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.class_id IS 'Reference to the class';


--
-- Name: COLUMN deliveries.delivery_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.delivery_date IS 'Date when the delivery is scheduled or occurred';


--
-- Name: COLUMN deliveries.items; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.items IS 'Items included in the delivery';


--
-- Name: COLUMN deliveries.status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.status IS 'Delivery status (e.g., ''Pending'', ''Delivered'')';


--
-- Name: COLUMN deliveries.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.created_at IS 'Timestamp when the delivery record was created';


--
-- Name: COLUMN deliveries.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.deliveries.updated_at IS 'Timestamp when the delivery record was last updated';


--
-- Name: deliveries_delivery_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.deliveries_delivery_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.deliveries_delivery_id_seq OWNER TO doadmin;

--
-- Name: deliveries_delivery_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.deliveries_delivery_id_seq OWNED BY public.deliveries.delivery_id;


--
-- Name: employers; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.employers (
    employer_id integer NOT NULL,
    employer_name character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.employers OWNER TO doadmin;

--
-- Name: TABLE employers; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.employers IS 'Stores information about employers or sponsors of learners';


--
-- Name: COLUMN employers.employer_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.employers.employer_id IS 'Unique internal employer ID';


--
-- Name: COLUMN employers.employer_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.employers.employer_name IS 'Name of the employer or sponsoring organization';


--
-- Name: COLUMN employers.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.employers.created_at IS 'Timestamp when the employer record was created';


--
-- Name: COLUMN employers.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.employers.updated_at IS 'Timestamp when the employer record was last updated';


--
-- Name: employers_employer_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.employers_employer_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employers_employer_id_seq OWNER TO doadmin;

--
-- Name: employers_employer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.employers_employer_id_seq OWNED BY public.employers.employer_id;


--
-- Name: exam_results; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.exam_results (
    result_id integer NOT NULL,
    exam_id integer,
    learner_id integer,
    subject character varying(100),
    mock_exam_number integer,
    score numeric(5,2),
    result character varying(20),
    exam_date date,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.exam_results OWNER TO doadmin;

--
-- Name: TABLE exam_results; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.exam_results IS 'Stores detailed exam results for learners';


--
-- Name: COLUMN exam_results.result_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.result_id IS 'Unique internal exam result ID';


--
-- Name: COLUMN exam_results.exam_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.exam_id IS 'Reference to the exam';


--
-- Name: COLUMN exam_results.learner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.learner_id IS 'Reference to the learner';


--
-- Name: COLUMN exam_results.subject; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.subject IS 'Subject of the exam';


--
-- Name: COLUMN exam_results.mock_exam_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.mock_exam_number IS 'Number of the mock exam (e.g., 1, 2, 3)';


--
-- Name: COLUMN exam_results.score; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.score IS 'Learner''s score in the exam';


--
-- Name: COLUMN exam_results.result; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.result IS 'Exam result (e.g., ''Pass'', ''Fail'')';


--
-- Name: COLUMN exam_results.exam_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.exam_date IS 'Date when the exam was taken';


--
-- Name: COLUMN exam_results.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.created_at IS 'Timestamp when the exam result was created';


--
-- Name: COLUMN exam_results.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exam_results.updated_at IS 'Timestamp when the exam result was last updated';


--
-- Name: exam_results_result_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.exam_results_result_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.exam_results_result_id_seq OWNER TO doadmin;

--
-- Name: exam_results_result_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.exam_results_result_id_seq OWNED BY public.exam_results.result_id;


--
-- Name: exams; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.exams (
    exam_id integer NOT NULL,
    learner_id integer,
    product_id integer,
    exam_date date,
    exam_type character varying(50),
    score numeric(5,2),
    result character varying(20),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.exams OWNER TO doadmin;

--
-- Name: TABLE exams; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.exams IS 'Stores exam results for learners';


--
-- Name: COLUMN exams.exam_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.exam_id IS 'Unique internal exam ID';


--
-- Name: COLUMN exams.learner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.learner_id IS 'Reference to the learner';


--
-- Name: COLUMN exams.product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.product_id IS 'Reference to the product or subject';


--
-- Name: COLUMN exams.exam_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.exam_date IS 'Date when the exam was taken';


--
-- Name: COLUMN exams.exam_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.exam_type IS 'Type of exam (e.g., ''Mock'', ''Final'')';


--
-- Name: COLUMN exams.score; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.score IS 'Learner''s score in the exam';


--
-- Name: COLUMN exams.result; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.result IS 'Exam result (e.g., ''Pass'', ''Fail'')';


--
-- Name: COLUMN exams.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.created_at IS 'Timestamp when the exam record was created';


--
-- Name: COLUMN exams.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.exams.updated_at IS 'Timestamp when the exam record was last updated';


--
-- Name: exams_exam_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.exams_exam_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.exams_exam_id_seq OWNER TO doadmin;

--
-- Name: exams_exam_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.exams_exam_id_seq OWNED BY public.exams.exam_id;


--
-- Name: files; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.files (
    file_id integer NOT NULL,
    owner_type character varying(50),
    owner_id integer,
    file_path character varying(255),
    file_type character varying(50),
    uploaded_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.files OWNER TO doadmin;

--
-- Name: TABLE files; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.files IS 'Stores references to files associated with various entities';


--
-- Name: COLUMN files.file_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.files.file_id IS 'Unique internal file ID';


--
-- Name: COLUMN files.owner_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.files.owner_type IS 'Type of entity that owns the file (e.g., ''Learner'', ''Class'', ''Agent'')';


--
-- Name: COLUMN files.owner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.files.owner_id IS 'ID of the owner entity';


--
-- Name: COLUMN files.file_path; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.files.file_path IS 'File path or URL to the stored file';


--
-- Name: COLUMN files.file_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.files.file_type IS 'Type of file (e.g., ''Scanned Portfolio'', ''QA Report'')';


--
-- Name: COLUMN files.uploaded_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.files.uploaded_at IS 'Timestamp when the file was uploaded';


--
-- Name: files_file_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.files_file_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.files_file_id_seq OWNER TO doadmin;

--
-- Name: files_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.files_file_id_seq OWNED BY public.files.file_id;


--
-- Name: history; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.history (
    history_id integer NOT NULL,
    entity_type character varying(50),
    entity_id integer,
    action character varying(50),
    changes jsonb,
    action_date timestamp without time zone DEFAULT now(),
    user_id integer
);


ALTER TABLE public.history OWNER TO doadmin;

--
-- Name: TABLE history; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.history IS 'Records historical changes and actions performed on entities';


--
-- Name: COLUMN history.history_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.history_id IS 'Unique internal history ID';


--
-- Name: COLUMN history.entity_type; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.entity_type IS 'Type of entity the history record refers to (e.g., ''Learner'', ''Agent'', ''Class'')';


--
-- Name: COLUMN history.entity_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.entity_id IS 'ID of the entity';


--
-- Name: COLUMN history.action; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.action IS 'Type of action performed (e.g., ''Created'', ''Updated'', ''Deleted'')';


--
-- Name: COLUMN history.changes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.changes IS 'Details of the changes made, stored in JSON format';


--
-- Name: COLUMN history.action_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.action_date IS 'Timestamp when the action occurred';


--
-- Name: COLUMN history.user_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.history.user_id IS 'Reference to the user who performed the action';


--
-- Name: history_history_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.history_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.history_history_id_seq OWNER TO doadmin;

--
-- Name: history_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.history_history_id_seq OWNED BY public.history.history_id;


--
-- Name: learner_placement_level; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.learner_placement_level (
    placement_level_id integer NOT NULL,
    level character varying(255) NOT NULL,
    level_desc character varying(255)
);


ALTER TABLE public.learner_placement_level OWNER TO doadmin;

--
-- Name: TABLE learner_placement_level; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.learner_placement_level IS 'Stores Learners Placement Levels';


--
-- Name: learner_portfolios; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.learner_portfolios (
    portfolio_id integer NOT NULL,
    learner_id integer NOT NULL,
    file_path character varying(255) NOT NULL,
    upload_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.learner_portfolios OWNER TO doadmin;

--
-- Name: learner_portfolios_portfolio_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.learner_portfolios_portfolio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learner_portfolios_portfolio_id_seq OWNER TO doadmin;

--
-- Name: learner_portfolios_portfolio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.learner_portfolios_portfolio_id_seq OWNED BY public.learner_portfolios.portfolio_id;


--
-- Name: learner_products; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.learner_products (
    learner_id integer NOT NULL,
    product_id integer NOT NULL,
    start_date date,
    end_date date
);


ALTER TABLE public.learner_products OWNER TO doadmin;

--
-- Name: TABLE learner_products; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.learner_products IS 'Associates learners with the products they are enrolled in';


--
-- Name: COLUMN learner_products.learner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_products.learner_id IS 'Reference to the learner';


--
-- Name: COLUMN learner_products.product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_products.product_id IS 'Reference to the product the learner is enrolled in';


--
-- Name: COLUMN learner_products.start_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_products.start_date IS 'Start date of the learner''s enrollment in the product';


--
-- Name: COLUMN learner_products.end_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_products.end_date IS 'End date of the learner''s enrollment in the product';


--
-- Name: learner_progressions; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.learner_progressions (
    progression_id integer NOT NULL,
    learner_id integer,
    from_product_id integer,
    to_product_id integer,
    progression_date date,
    notes text
);


ALTER TABLE public.learner_progressions OWNER TO doadmin;

--
-- Name: TABLE learner_progressions; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.learner_progressions IS 'Tracks the progression of learners between products';


--
-- Name: COLUMN learner_progressions.progression_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_progressions.progression_id IS 'Unique internal progression ID';


--
-- Name: COLUMN learner_progressions.learner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_progressions.learner_id IS 'Reference to the learner';


--
-- Name: COLUMN learner_progressions.from_product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_progressions.from_product_id IS 'Reference to the initial product';


--
-- Name: COLUMN learner_progressions.to_product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_progressions.to_product_id IS 'Reference to the new product after progression';


--
-- Name: COLUMN learner_progressions.progression_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_progressions.progression_date IS 'Date when the learner progressed to the new product';


--
-- Name: COLUMN learner_progressions.notes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_progressions.notes IS 'Additional notes regarding the progression';


--
-- Name: learner_progressions_progression_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.learner_progressions_progression_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learner_progressions_progression_id_seq OWNER TO doadmin;

--
-- Name: learner_progressions_progression_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.learner_progressions_progression_id_seq OWNED BY public.learner_progressions.progression_id;


--
-- Name: learner_qualifications; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.learner_qualifications (
    id integer NOT NULL,
    qualification character varying(255)
);


ALTER TABLE public.learner_qualifications OWNER TO doadmin;

--
-- Name: TABLE learner_qualifications; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.learner_qualifications IS 'Table containing a list of possible qualifications that learners can attain.';


--
-- Name: COLUMN learner_qualifications.qualification; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learner_qualifications.qualification IS 'Name of the qualification.';


--
-- Name: learner_qualifications_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.learner_qualifications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learner_qualifications_id_seq OWNER TO doadmin;

--
-- Name: learner_qualifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.learner_qualifications_id_seq OWNED BY public.learner_qualifications.id;


--
-- Name: learners; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.learners (
    id integer NOT NULL,
    first_name character varying(50),
    initials character varying(10),
    surname character varying(50),
    gender character varying(10),
    race character varying(20),
    sa_id_no character varying(20),
    passport_number character varying(20),
    tel_number character varying(20),
    alternative_tel_number character varying(20),
    email_address character varying(100),
    address_line_1 character varying(100),
    address_line_2 character varying(100),
    city_town_id integer,
    province_region_id integer,
    postal_code character varying(10),
    assessment_status character varying(20),
    placement_assessment_date date,
    numeracy_level integer,
    employment_status boolean,
    employer_id integer,
    disability_status boolean,
    scanned_portfolio character varying(255),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    highest_qualification integer,
    communication_level integer
);


ALTER TABLE public.learners OWNER TO doadmin;

--
-- Name: TABLE learners; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.learners IS 'Stores personal, educational, and assessment information about learners';


--
-- Name: COLUMN learners.id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.id IS 'Unique internal learner ID';


--
-- Name: COLUMN learners.first_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.first_name IS 'Learner''s first name';


--
-- Name: COLUMN learners.initials; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.initials IS 'Learner''s initials';


--
-- Name: COLUMN learners.surname; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.surname IS 'Learner''s surname';


--
-- Name: COLUMN learners.gender; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.gender IS 'Learner''s gender';


--
-- Name: COLUMN learners.race; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.race IS 'Learner''s race; options include ''African'', ''Coloured'', ''White'', ''Indian''';


--
-- Name: COLUMN learners.sa_id_no; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.sa_id_no IS 'Learner''s South African ID number';


--
-- Name: COLUMN learners.passport_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.passport_number IS 'Learner''s passport number if they are a foreigner';


--
-- Name: COLUMN learners.tel_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.tel_number IS 'Learner''s primary telephone number';


--
-- Name: COLUMN learners.alternative_tel_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.alternative_tel_number IS 'Learner''s alternative contact number';


--
-- Name: COLUMN learners.email_address; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.email_address IS 'Learner''s email address';


--
-- Name: COLUMN learners.address_line_1; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.address_line_1 IS 'First line of learner''s physical address';


--
-- Name: COLUMN learners.address_line_2; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.address_line_2 IS 'Second line of learner''s physical address';


--
-- Name: COLUMN learners.city_town_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.city_town_id IS 'Reference to the city or town where the learner lives';


--
-- Name: COLUMN learners.province_region_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.province_region_id IS 'Reference to the province/region where the learner lives';


--
-- Name: COLUMN learners.postal_code; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.postal_code IS 'Postal code of the learner''s area';


--
-- Name: COLUMN learners.assessment_status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.assessment_status IS 'Assessment status; indicates if the learner was assessed (''Assessed'', ''Not Assessed'')';


--
-- Name: COLUMN learners.placement_assessment_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.placement_assessment_date IS 'Date when the learner took the placement assessment';


--
-- Name: COLUMN learners.numeracy_level; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.numeracy_level IS 'Learner''s initial placement level in Communications (e.g., ''CL1b'', ''CL1'', ''CL2'')';


--
-- Name: COLUMN learners.employment_status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.employment_status IS 'Indicates if the learner is employed (true) or not (false)';


--
-- Name: COLUMN learners.employer_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.employer_id IS 'Reference to the learner''s employer or sponsor';


--
-- Name: COLUMN learners.disability_status; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.disability_status IS 'Indicates if the learner has a disability (true) or not (false)';


--
-- Name: COLUMN learners.scanned_portfolio; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.scanned_portfolio IS 'File path or URL to the learner''s scanned portfolio in PDF format';


--
-- Name: COLUMN learners.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.created_at IS 'Timestamp when the learner record was created';


--
-- Name: COLUMN learners.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.updated_at IS 'Timestamp when the learner record was last updated';


--
-- Name: COLUMN learners.highest_qualification; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.learners.highest_qualification IS 'Foreign key referencing learner_qualifications.id; indicates the learner''s highest qualification.';


--
-- Name: learners_learner_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.learners_learner_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learners_learner_id_seq OWNER TO doadmin;

--
-- Name: learners_learner_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.learners_learner_id_seq OWNED BY public.learners.id;


--
-- Name: locations; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.locations (
    location_id integer NOT NULL,
    suburb character varying(50),
    town character varying(50),
    province character varying(50),
    postal_code character varying(10),
    longitude numeric(9,6),
    latitude numeric(9,6),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.locations OWNER TO doadmin;

--
-- Name: TABLE locations; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.locations IS 'Stores geographical location data for addresses';


--
-- Name: COLUMN locations.location_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.location_id IS 'Unique internal location ID';


--
-- Name: COLUMN locations.suburb; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.suburb IS 'Suburb name';


--
-- Name: COLUMN locations.town; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.town IS 'Town name';


--
-- Name: COLUMN locations.province; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.province IS 'Province name';


--
-- Name: COLUMN locations.postal_code; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.postal_code IS 'Postal code';


--
-- Name: COLUMN locations.longitude; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.longitude IS 'Geographical longitude coordinate';


--
-- Name: COLUMN locations.latitude; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.latitude IS 'Geographical latitude coordinate';


--
-- Name: COLUMN locations.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.created_at IS 'Timestamp when the location record was created';


--
-- Name: COLUMN locations.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.locations.updated_at IS 'Timestamp when the location record was last updated';


--
-- Name: locations_location_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.locations_location_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.locations_location_id_seq OWNER TO doadmin;

--
-- Name: locations_location_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.locations_location_id_seq OWNED BY public.locations.location_id;


--
-- Name: products; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.products (
    product_id integer NOT NULL,
    product_name character varying(100),
    product_duration integer,
    learning_area character varying(100),
    learning_area_duration integer,
    reporting_structure text,
    product_notes text,
    product_rules text,
    product_flags text,
    parent_product_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.products OWNER TO doadmin;

--
-- Name: TABLE products; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.products IS 'Stores information about educational products or courses';


--
-- Name: COLUMN products.product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.product_id IS 'Unique internal product ID';


--
-- Name: COLUMN products.product_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.product_name IS 'Name of the product or course';


--
-- Name: COLUMN products.product_duration; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.product_duration IS 'Total duration of the product in hours';


--
-- Name: COLUMN products.learning_area; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.learning_area IS 'Learning areas covered by the product (e.g., ''Communication'', ''Numeracy'')';


--
-- Name: COLUMN products.learning_area_duration; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.learning_area_duration IS 'Duration of each learning area in hours';


--
-- Name: COLUMN products.reporting_structure; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.reporting_structure IS 'Structure of progress reports for the product';


--
-- Name: COLUMN products.product_notes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.product_notes IS 'Notes or additional information about the product';


--
-- Name: COLUMN products.product_rules; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.product_rules IS 'Rules or guidelines associated with the product';


--
-- Name: COLUMN products.product_flags; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.product_flags IS 'Flags or alerts for the product (e.g., attendance thresholds)';


--
-- Name: COLUMN products.parent_product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.parent_product_id IS 'Reference to a parent product for hierarchical structuring';


--
-- Name: COLUMN products.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.created_at IS 'Timestamp when the product record was created';


--
-- Name: COLUMN products.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.products.updated_at IS 'Timestamp when the product record was last updated';


--
-- Name: products_product_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.products_product_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_product_id_seq OWNER TO doadmin;

--
-- Name: products_product_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.products_product_id_seq OWNED BY public.products.product_id;


--
-- Name: progress_reports; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.progress_reports (
    report_id integer NOT NULL,
    class_id integer,
    learner_id integer,
    product_id integer,
    progress_percentage numeric(5,2),
    report_date date,
    remarks text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.progress_reports OWNER TO doadmin;

--
-- Name: TABLE progress_reports; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.progress_reports IS 'Stores progress reports for learners in specific classes and products';


--
-- Name: COLUMN progress_reports.report_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.report_id IS 'Unique internal progress report ID';


--
-- Name: COLUMN progress_reports.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.class_id IS 'Reference to the class';


--
-- Name: COLUMN progress_reports.learner_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.learner_id IS 'Reference to the learner';


--
-- Name: COLUMN progress_reports.product_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.product_id IS 'Reference to the product or subject';


--
-- Name: COLUMN progress_reports.progress_percentage; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.progress_percentage IS 'Learner''s progress percentage in the product';


--
-- Name: COLUMN progress_reports.report_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.report_date IS 'Date when the progress report was generated';


--
-- Name: COLUMN progress_reports.remarks; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.remarks IS 'Additional remarks or comments';


--
-- Name: COLUMN progress_reports.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.created_at IS 'Timestamp when the progress report was created';


--
-- Name: COLUMN progress_reports.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.progress_reports.updated_at IS 'Timestamp when the progress report was last updated';


--
-- Name: progress_reports_report_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.progress_reports_report_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.progress_reports_report_id_seq OWNER TO doadmin;

--
-- Name: progress_reports_report_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.progress_reports_report_id_seq OWNED BY public.progress_reports.report_id;


--
-- Name: qa_reports; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.qa_reports (
    qa_report_id integer NOT NULL,
    class_id integer,
    agent_id integer,
    report_date date,
    report_file character varying(255),
    notes text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.qa_reports OWNER TO doadmin;

--
-- Name: TABLE qa_reports; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.qa_reports IS 'Stores QA (Quality Assurance) reports for classes and agents';


--
-- Name: COLUMN qa_reports.qa_report_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.qa_report_id IS 'Unique internal QA report ID';


--
-- Name: COLUMN qa_reports.class_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.class_id IS 'Reference to the class';


--
-- Name: COLUMN qa_reports.agent_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.agent_id IS 'Reference to the agent';


--
-- Name: COLUMN qa_reports.report_date; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.report_date IS 'Date when the QA report was created';


--
-- Name: COLUMN qa_reports.report_file; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.report_file IS 'File path or URL to the QA report';


--
-- Name: COLUMN qa_reports.notes; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.notes IS 'Additional notes or observations from the QA report';


--
-- Name: COLUMN qa_reports.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.created_at IS 'Timestamp when the QA report was created';


--
-- Name: COLUMN qa_reports.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.qa_reports.updated_at IS 'Timestamp when the QA report was last updated';


--
-- Name: qa_reports_qa_report_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.qa_reports_qa_report_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.qa_reports_qa_report_id_seq OWNER TO doadmin;

--
-- Name: qa_reports_qa_report_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.qa_reports_qa_report_id_seq OWNED BY public.qa_reports.qa_report_id;


--
-- Name: sites; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.sites (
    site_id integer NOT NULL,
    client_id integer NOT NULL,
    site_name character varying(100) NOT NULL,
    address text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.sites OWNER TO doadmin;

--
-- Name: TABLE sites; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.sites IS 'Stores information about client sites';


--
-- Name: COLUMN sites.site_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.sites.site_id IS 'Unique site ID';


--
-- Name: COLUMN sites.client_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.sites.client_id IS 'Reference to the client this site belongs to';


--
-- Name: COLUMN sites.site_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.sites.site_name IS 'Name of the site';


--
-- Name: COLUMN sites.address; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.sites.address IS 'Full address of the site';


--
-- Name: sites_site_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.sites_site_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sites_site_id_seq OWNER TO doadmin;

--
-- Name: sites_site_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.sites_site_id_seq OWNED BY public.sites.site_id;


--
-- Name: supervisors; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.supervisors (
    supervisor_id integer NOT NULL,
    first_name character varying(50) NOT NULL,
    last_name character varying(50) NOT NULL,
    email character varying(100),
    phone character varying(20),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.supervisors OWNER TO doadmin;

--
-- Name: supervisors_supervisor_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.supervisors_supervisor_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supervisors_supervisor_id_seq OWNER TO doadmin;

--
-- Name: supervisors_supervisor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.supervisors_supervisor_id_seq OWNED BY public.supervisors.supervisor_id;


--
-- Name: user_permissions; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.user_permissions (
    permission_id integer NOT NULL,
    user_id integer,
    permission character varying(100)
);


ALTER TABLE public.user_permissions OWNER TO doadmin;

--
-- Name: TABLE user_permissions; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.user_permissions IS 'Grants specific permissions to users';


--
-- Name: COLUMN user_permissions.permission_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.user_permissions.permission_id IS 'Unique internal permission ID';


--
-- Name: COLUMN user_permissions.user_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.user_permissions.user_id IS 'Reference to the user';


--
-- Name: COLUMN user_permissions.permission; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.user_permissions.permission IS 'Specific permission granted to the user';


--
-- Name: user_permissions_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.user_permissions_permission_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_permissions_permission_id_seq OWNER TO doadmin;

--
-- Name: user_permissions_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.user_permissions_permission_id_seq OWNED BY public.user_permissions.permission_id;


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.user_roles (
    role_id integer NOT NULL,
    role_name character varying(50),
    permissions jsonb
);


ALTER TABLE public.user_roles OWNER TO doadmin;

--
-- Name: TABLE user_roles; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.user_roles IS 'Defines roles and associated permissions for users';


--
-- Name: COLUMN user_roles.role_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.user_roles.role_id IS 'Unique internal role ID';


--
-- Name: COLUMN user_roles.role_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.user_roles.role_name IS 'Name of the role (e.g., ''Admin'', ''Project Supervisor'')';


--
-- Name: COLUMN user_roles.permissions; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.user_roles.permissions IS 'Permissions associated with the role, stored in JSON format';


--
-- Name: user_roles_role_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.user_roles_role_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_roles_role_id_seq OWNER TO doadmin;

--
-- Name: user_roles_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.user_roles_role_id_seq OWNED BY public.user_roles.role_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.users (
    user_id integer NOT NULL,
    first_name character varying(50),
    surname character varying(50),
    email character varying(100) NOT NULL,
    cellphone_number character varying(20),
    role character varying(50),
    password_hash character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.users OWNER TO doadmin;

--
-- Name: TABLE users; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON TABLE public.users IS 'Stores system user information';


--
-- Name: COLUMN users.user_id; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.user_id IS 'Unique internal user ID';


--
-- Name: COLUMN users.first_name; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.first_name IS 'User''s first name';


--
-- Name: COLUMN users.surname; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.surname IS 'User''s surname';


--
-- Name: COLUMN users.email; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.email IS 'User''s email address';


--
-- Name: COLUMN users.cellphone_number; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.cellphone_number IS 'User''s cellphone number';


--
-- Name: COLUMN users.role; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.role IS 'User''s role in the system, e.g., ''Admin'', ''Project Supervisor''';


--
-- Name: COLUMN users.password_hash; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.password_hash IS 'Hashed password for user authentication';


--
-- Name: COLUMN users.created_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.created_at IS 'Timestamp when the user record was created';


--
-- Name: COLUMN users.updated_at; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON COLUMN public.users.updated_at IS 'Timestamp when the user record was last updated';


--
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.users_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_user_id_seq OWNER TO doadmin;

--
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- Name: wecoza_class_backup_agents; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.wecoza_class_backup_agents (
    id integer NOT NULL,
    class_id integer NOT NULL,
    agent_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.wecoza_class_backup_agents OWNER TO doadmin;

--
-- Name: wecoza_class_backup_agents_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.wecoza_class_backup_agents_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wecoza_class_backup_agents_id_seq OWNER TO doadmin;

--
-- Name: wecoza_class_backup_agents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.wecoza_class_backup_agents_id_seq OWNED BY public.wecoza_class_backup_agents.id;


--
-- Name: wecoza_class_dates; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.wecoza_class_dates (
    id integer NOT NULL,
    class_id integer NOT NULL,
    stop_date date NOT NULL,
    restart_date date NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.wecoza_class_dates OWNER TO doadmin;

--
-- Name: wecoza_class_dates_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.wecoza_class_dates_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wecoza_class_dates_id_seq OWNER TO doadmin;

--
-- Name: wecoza_class_dates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.wecoza_class_dates_id_seq OWNED BY public.wecoza_class_dates.id;


--
-- Name: wecoza_class_learners; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.wecoza_class_learners (
    id integer NOT NULL,
    class_id integer NOT NULL,
    learner_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.wecoza_class_learners OWNER TO doadmin;

--
-- Name: wecoza_class_learners_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.wecoza_class_learners_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wecoza_class_learners_id_seq OWNER TO doadmin;

--
-- Name: wecoza_class_learners_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.wecoza_class_learners_id_seq OWNED BY public.wecoza_class_learners.id;


--
-- Name: wecoza_class_notes; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.wecoza_class_notes (
    id integer NOT NULL,
    class_id integer NOT NULL,
    note_type character varying(50) NOT NULL,
    note_content text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.wecoza_class_notes OWNER TO doadmin;

--
-- Name: wecoza_class_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.wecoza_class_notes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wecoza_class_notes_id_seq OWNER TO doadmin;

--
-- Name: wecoza_class_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.wecoza_class_notes_id_seq OWNED BY public.wecoza_class_notes.id;


--
-- Name: wecoza_class_schedule; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.wecoza_class_schedule (
    id integer NOT NULL,
    class_id integer NOT NULL,
    schedule_pattern character varying(20) NOT NULL,
    schedule_days character varying(50) NOT NULL,
    start_time time without time zone NOT NULL,
    end_time time without time zone NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    exception_dates text,
    holiday_overrides text,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);


ALTER TABLE public.wecoza_class_schedule OWNER TO doadmin;

--
-- Name: wecoza_class_schedule_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.wecoza_class_schedule_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wecoza_class_schedule_id_seq OWNER TO doadmin;

--
-- Name: wecoza_class_schedule_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.wecoza_class_schedule_id_seq OWNED BY public.wecoza_class_schedule.id;


--
-- Name: wecoza_classes; Type: TABLE; Schema: public; Owner: doadmin
--

CREATE TABLE public.wecoza_classes (
    id integer NOT NULL,
    client_id integer NOT NULL,
    site_id integer NOT NULL,
    site_address text NOT NULL,
    class_type character varying(50) NOT NULL,
    class_subject character varying(50),
    class_code character varying(50),
    class_duration integer,
    class_start_date date NOT NULL,
    seta_funded boolean DEFAULT false NOT NULL,
    seta_id integer,
    exam_class boolean DEFAULT false NOT NULL,
    exam_type character varying(50),
    qa_visit_dates text,
    class_agent integer NOT NULL,
    project_supervisor integer,
    delivery_date date,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);


ALTER TABLE public.wecoza_classes OWNER TO doadmin;

--
-- Name: wecoza_classes_id_seq; Type: SEQUENCE; Schema: public; Owner: doadmin
--

CREATE SEQUENCE public.wecoza_classes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wecoza_classes_id_seq OWNER TO doadmin;

--
-- Name: wecoza_classes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: doadmin
--

ALTER SEQUENCE public.wecoza_classes_id_seq OWNED BY public.wecoza_classes.id;


--
-- Name: agent_absences absence_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_absences ALTER COLUMN absence_id SET DEFAULT nextval('public.agent_absences_absence_id_seq'::regclass);


--
-- Name: agent_notes note_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_notes ALTER COLUMN note_id SET DEFAULT nextval('public.agent_notes_note_id_seq'::regclass);


--
-- Name: agent_orders order_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_orders ALTER COLUMN order_id SET DEFAULT nextval('public.agent_orders_order_id_seq'::regclass);


--
-- Name: agent_qa_visits visit_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_qa_visits ALTER COLUMN visit_id SET DEFAULT nextval('public.agent_qa_visits_visit_id_seq'::regclass);


--
-- Name: agent_replacements replacement_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_replacements ALTER COLUMN replacement_id SET DEFAULT nextval('public.agent_replacements_replacement_id_seq'::regclass);


--
-- Name: agents agent_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agents ALTER COLUMN agent_id SET DEFAULT nextval('public.agents_agent_id_seq'::regclass);


--
-- Name: attendance_registers register_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_registers ALTER COLUMN register_id SET DEFAULT nextval('public.attendance_registers_register_id_seq'::regclass);


--
-- Name: class_notes note_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_notes ALTER COLUMN note_id SET DEFAULT nextval('public.class_notes_note_id_seq'::regclass);


--
-- Name: class_schedules schedule_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_schedules ALTER COLUMN schedule_id SET DEFAULT nextval('public.class_schedules_schedule_id_seq'::regclass);


--
-- Name: classes class_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.classes ALTER COLUMN class_id SET DEFAULT nextval('public.classes_class_id_seq'::regclass);


--
-- Name: client_communications communication_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_communications ALTER COLUMN communication_id SET DEFAULT nextval('public.client_communications_communication_id_seq'::regclass);


--
-- Name: client_contact_persons contact_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_contact_persons ALTER COLUMN contact_id SET DEFAULT nextval('public.client_contact_persons_contact_id_seq'::regclass);


--
-- Name: clients client_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.clients ALTER COLUMN client_id SET DEFAULT nextval('public.clients_client_id_seq'::regclass);


--
-- Name: collections collection_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.collections ALTER COLUMN collection_id SET DEFAULT nextval('public.collections_collection_id_seq'::regclass);


--
-- Name: deliveries delivery_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.deliveries ALTER COLUMN delivery_id SET DEFAULT nextval('public.deliveries_delivery_id_seq'::regclass);


--
-- Name: employers employer_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.employers ALTER COLUMN employer_id SET DEFAULT nextval('public.employers_employer_id_seq'::regclass);


--
-- Name: exam_results result_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exam_results ALTER COLUMN result_id SET DEFAULT nextval('public.exam_results_result_id_seq'::regclass);


--
-- Name: exams exam_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exams ALTER COLUMN exam_id SET DEFAULT nextval('public.exams_exam_id_seq'::regclass);


--
-- Name: files file_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.files ALTER COLUMN file_id SET DEFAULT nextval('public.files_file_id_seq'::regclass);


--
-- Name: history history_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.history ALTER COLUMN history_id SET DEFAULT nextval('public.history_history_id_seq'::regclass);


--
-- Name: learner_portfolios portfolio_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_portfolios ALTER COLUMN portfolio_id SET DEFAULT nextval('public.learner_portfolios_portfolio_id_seq'::regclass);


--
-- Name: learner_progressions progression_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_progressions ALTER COLUMN progression_id SET DEFAULT nextval('public.learner_progressions_progression_id_seq'::regclass);


--
-- Name: learner_qualifications id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_qualifications ALTER COLUMN id SET DEFAULT nextval('public.learner_qualifications_id_seq'::regclass);


--
-- Name: learners id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners ALTER COLUMN id SET DEFAULT nextval('public.learners_learner_id_seq'::regclass);


--
-- Name: locations location_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.locations ALTER COLUMN location_id SET DEFAULT nextval('public.locations_location_id_seq'::regclass);


--
-- Name: products product_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.products ALTER COLUMN product_id SET DEFAULT nextval('public.products_product_id_seq'::regclass);


--
-- Name: progress_reports report_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.progress_reports ALTER COLUMN report_id SET DEFAULT nextval('public.progress_reports_report_id_seq'::regclass);


--
-- Name: qa_reports qa_report_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.qa_reports ALTER COLUMN qa_report_id SET DEFAULT nextval('public.qa_reports_qa_report_id_seq'::regclass);


--
-- Name: sites site_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.sites ALTER COLUMN site_id SET DEFAULT nextval('public.sites_site_id_seq'::regclass);


--
-- Name: supervisors supervisor_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.supervisors ALTER COLUMN supervisor_id SET DEFAULT nextval('public.supervisors_supervisor_id_seq'::regclass);


--
-- Name: user_permissions permission_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.user_permissions ALTER COLUMN permission_id SET DEFAULT nextval('public.user_permissions_permission_id_seq'::regclass);


--
-- Name: user_roles role_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.user_roles ALTER COLUMN role_id SET DEFAULT nextval('public.user_roles_role_id_seq'::regclass);


--
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- Name: wecoza_class_backup_agents id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_backup_agents ALTER COLUMN id SET DEFAULT nextval('public.wecoza_class_backup_agents_id_seq'::regclass);


--
-- Name: wecoza_class_dates id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_dates ALTER COLUMN id SET DEFAULT nextval('public.wecoza_class_dates_id_seq'::regclass);


--
-- Name: wecoza_class_learners id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_learners ALTER COLUMN id SET DEFAULT nextval('public.wecoza_class_learners_id_seq'::regclass);


--
-- Name: wecoza_class_notes id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_notes ALTER COLUMN id SET DEFAULT nextval('public.wecoza_class_notes_id_seq'::regclass);


--
-- Name: wecoza_class_schedule id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_schedule ALTER COLUMN id SET DEFAULT nextval('public.wecoza_class_schedule_id_seq'::regclass);


--
-- Name: wecoza_classes id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_classes ALTER COLUMN id SET DEFAULT nextval('public.wecoza_classes_id_seq'::regclass);


--
-- Name: agent_absences agent_absences_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_absences
    ADD CONSTRAINT agent_absences_pkey PRIMARY KEY (absence_id);


--
-- Name: agent_notes agent_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_notes
    ADD CONSTRAINT agent_notes_pkey PRIMARY KEY (note_id);


--
-- Name: agent_orders agent_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_orders
    ADD CONSTRAINT agent_orders_pkey PRIMARY KEY (order_id);


--
-- Name: agent_products agent_products_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_products
    ADD CONSTRAINT agent_products_pkey PRIMARY KEY (agent_id, product_id);


--
-- Name: agent_qa_visits agent_qa_visits_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_qa_visits
    ADD CONSTRAINT agent_qa_visits_pkey PRIMARY KEY (visit_id);


--
-- Name: agent_replacements agent_replacements_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_replacements
    ADD CONSTRAINT agent_replacements_pkey PRIMARY KEY (replacement_id);


--
-- Name: agents agents_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agents
    ADD CONSTRAINT agents_pkey PRIMARY KEY (agent_id);


--
-- Name: attendance_records attendance_records_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_pkey PRIMARY KEY (register_id, learner_id);


--
-- Name: attendance_registers attendance_registers_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_registers
    ADD CONSTRAINT attendance_registers_pkey PRIMARY KEY (register_id);


--
-- Name: class_agents class_agents_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_agents
    ADD CONSTRAINT class_agents_pkey PRIMARY KEY (class_id, agent_id, start_date);


--
-- Name: class_notes class_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_notes
    ADD CONSTRAINT class_notes_pkey PRIMARY KEY (note_id);


--
-- Name: class_schedules class_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_schedules
    ADD CONSTRAINT class_schedules_pkey PRIMARY KEY (schedule_id);


--
-- Name: class_subjects class_subjects_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_subjects
    ADD CONSTRAINT class_subjects_pkey PRIMARY KEY (class_id, product_id);


--
-- Name: classes classes_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_pkey PRIMARY KEY (class_id);


--
-- Name: client_communications client_communications_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_communications
    ADD CONSTRAINT client_communications_pkey PRIMARY KEY (communication_id);


--
-- Name: client_contact_persons client_contact_persons_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_contact_persons
    ADD CONSTRAINT client_contact_persons_pkey PRIMARY KEY (contact_id);


--
-- Name: clients clients_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (client_id);


--
-- Name: collections collections_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.collections
    ADD CONSTRAINT collections_pkey PRIMARY KEY (collection_id);


--
-- Name: deliveries deliveries_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_pkey PRIMARY KEY (delivery_id);


--
-- Name: employers employers_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.employers
    ADD CONSTRAINT employers_pkey PRIMARY KEY (employer_id);


--
-- Name: exam_results exam_results_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exam_results
    ADD CONSTRAINT exam_results_pkey PRIMARY KEY (result_id);


--
-- Name: exams exams_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exams
    ADD CONSTRAINT exams_pkey PRIMARY KEY (exam_id);


--
-- Name: files files_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_pkey PRIMARY KEY (file_id);


--
-- Name: history history_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.history
    ADD CONSTRAINT history_pkey PRIMARY KEY (history_id);


--
-- Name: learner_placement_level learner_placement_level_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_placement_level
    ADD CONSTRAINT learner_placement_level_pkey PRIMARY KEY (placement_level_id);


--
-- Name: learner_portfolios learner_portfolios_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_portfolios
    ADD CONSTRAINT learner_portfolios_pkey PRIMARY KEY (portfolio_id);


--
-- Name: learner_products learner_products_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_products
    ADD CONSTRAINT learner_products_pkey PRIMARY KEY (learner_id, product_id);


--
-- Name: learner_progressions learner_progressions_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_progressions
    ADD CONSTRAINT learner_progressions_pkey PRIMARY KEY (progression_id);


--
-- Name: learner_qualifications learner_qualifications_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_qualifications
    ADD CONSTRAINT learner_qualifications_pkey PRIMARY KEY (id);


--
-- Name: learners learners_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners
    ADD CONSTRAINT learners_pkey PRIMARY KEY (id);


--
-- Name: locations locations_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_pkey PRIMARY KEY (location_id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (product_id);


--
-- Name: progress_reports progress_reports_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.progress_reports
    ADD CONSTRAINT progress_reports_pkey PRIMARY KEY (report_id);


--
-- Name: qa_reports qa_reports_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.qa_reports
    ADD CONSTRAINT qa_reports_pkey PRIMARY KEY (qa_report_id);


--
-- Name: sites sites_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_pkey PRIMARY KEY (site_id);


--
-- Name: supervisors supervisors_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.supervisors
    ADD CONSTRAINT supervisors_pkey PRIMARY KEY (supervisor_id);


--
-- Name: user_permissions user_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.user_permissions
    ADD CONSTRAINT user_permissions_pkey PRIMARY KEY (permission_id);


--
-- Name: user_roles user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (role_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: wecoza_class_backup_agents wecoza_class_backup_agents_class_id_agent_id_key; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_backup_agents
    ADD CONSTRAINT wecoza_class_backup_agents_class_id_agent_id_key UNIQUE (class_id, agent_id);


--
-- Name: wecoza_class_backup_agents wecoza_class_backup_agents_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_backup_agents
    ADD CONSTRAINT wecoza_class_backup_agents_pkey PRIMARY KEY (id);


--
-- Name: wecoza_class_dates wecoza_class_dates_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_dates
    ADD CONSTRAINT wecoza_class_dates_pkey PRIMARY KEY (id);


--
-- Name: wecoza_class_learners wecoza_class_learners_class_id_learner_id_key; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_learners
    ADD CONSTRAINT wecoza_class_learners_class_id_learner_id_key UNIQUE (class_id, learner_id);


--
-- Name: wecoza_class_learners wecoza_class_learners_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_learners
    ADD CONSTRAINT wecoza_class_learners_pkey PRIMARY KEY (id);


--
-- Name: wecoza_class_notes wecoza_class_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_notes
    ADD CONSTRAINT wecoza_class_notes_pkey PRIMARY KEY (id);


--
-- Name: wecoza_class_schedule wecoza_class_schedule_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_schedule
    ADD CONSTRAINT wecoza_class_schedule_pkey PRIMARY KEY (id);


--
-- Name: wecoza_classes wecoza_classes_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_classes
    ADD CONSTRAINT wecoza_classes_pkey PRIMARY KEY (id);


--
-- Name: client_contact_persons_client_id_email_idx; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE UNIQUE INDEX client_contact_persons_client_id_email_idx ON public.client_contact_persons USING btree (client_id, email);


--
-- Name: idx_classes_backup_agent_ids; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_backup_agent_ids ON public.classes USING gin (backup_agent_ids);


--
-- Name: idx_classes_class_agent; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_class_agent ON public.classes USING btree (class_agent);


--
-- Name: idx_classes_class_code; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_class_code ON public.classes USING btree (class_code);


--
-- Name: idx_classes_class_subject; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_class_subject ON public.classes USING btree (class_subject);


--
-- Name: idx_classes_exam_learners; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_exam_learners ON public.classes USING gin (exam_learners);


--
-- Name: idx_classes_learner_ids; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_learner_ids ON public.classes USING gin (learner_ids);


--
-- Name: idx_classes_qa_reports; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_qa_reports ON public.classes USING gin (qa_reports);


--
-- Name: idx_classes_schedule_data; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_schedule_data ON public.classes USING gin (schedule_data);


--
-- Name: idx_classes_site_id; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_classes_site_id ON public.classes USING btree (site_id);


--
-- Name: idx_clients_client_name; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_clients_client_name ON public.clients USING btree (client_name);


--
-- Name: idx_sites_address; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_sites_address ON public.sites USING btree (address);


--
-- Name: idx_sites_client_id; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_sites_client_id ON public.sites USING btree (client_id);


--
-- Name: idx_sites_created_at; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_sites_created_at ON public.sites USING btree (created_at);


--
-- Name: idx_sites_search_text; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_sites_search_text ON public.sites USING btree (site_name, address);


--
-- Name: idx_sites_site_name; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_sites_site_name ON public.sites USING btree (site_name);


--
-- Name: agent_absences agent_absences_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_absences
    ADD CONSTRAINT agent_absences_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agent_absences agent_absences_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_absences
    ADD CONSTRAINT agent_absences_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: agent_notes agent_notes_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_notes
    ADD CONSTRAINT agent_notes_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agent_orders agent_orders_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_orders
    ADD CONSTRAINT agent_orders_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agent_orders agent_orders_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_orders
    ADD CONSTRAINT agent_orders_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: agent_products agent_products_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_products
    ADD CONSTRAINT agent_products_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agent_products agent_products_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_products
    ADD CONSTRAINT agent_products_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: agent_qa_visits agent_qa_visits_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_qa_visits
    ADD CONSTRAINT agent_qa_visits_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agent_qa_visits agent_qa_visits_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_qa_visits
    ADD CONSTRAINT agent_qa_visits_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: agent_qa_visits agent_qa_visits_qa_report_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_qa_visits
    ADD CONSTRAINT agent_qa_visits_qa_report_id_fkey FOREIGN KEY (qa_report_id) REFERENCES public.qa_reports(qa_report_id);


--
-- Name: agent_replacements agent_replacements_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_replacements
    ADD CONSTRAINT agent_replacements_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: agent_replacements agent_replacements_original_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_replacements
    ADD CONSTRAINT agent_replacements_original_agent_id_fkey FOREIGN KEY (original_agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agent_replacements agent_replacements_replacement_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agent_replacements
    ADD CONSTRAINT agent_replacements_replacement_agent_id_fkey FOREIGN KEY (replacement_agent_id) REFERENCES public.agents(agent_id);


--
-- Name: agents agents_preferred_working_area_1_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agents
    ADD CONSTRAINT agents_preferred_working_area_1_fkey FOREIGN KEY (preferred_working_area_1) REFERENCES public.locations(location_id);


--
-- Name: agents agents_preferred_working_area_2_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agents
    ADD CONSTRAINT agents_preferred_working_area_2_fkey FOREIGN KEY (preferred_working_area_2) REFERENCES public.locations(location_id);


--
-- Name: agents agents_preferred_working_area_3_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agents
    ADD CONSTRAINT agents_preferred_working_area_3_fkey FOREIGN KEY (preferred_working_area_3) REFERENCES public.locations(location_id);


--
-- Name: agents agents_residential_town_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.agents
    ADD CONSTRAINT agents_residential_town_id_fkey FOREIGN KEY (residential_town_id) REFERENCES public.locations(location_id);


--
-- Name: attendance_records attendance_records_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id);


--
-- Name: attendance_records attendance_records_register_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_register_id_fkey FOREIGN KEY (register_id) REFERENCES public.attendance_registers(register_id);


--
-- Name: attendance_registers attendance_registers_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_registers
    ADD CONSTRAINT attendance_registers_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: attendance_registers attendance_registers_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.attendance_registers
    ADD CONSTRAINT attendance_registers_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: class_agents class_agents_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_agents
    ADD CONSTRAINT class_agents_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: class_agents class_agents_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_agents
    ADD CONSTRAINT class_agents_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: class_notes class_notes_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_notes
    ADD CONSTRAINT class_notes_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: class_schedules class_schedules_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_schedules
    ADD CONSTRAINT class_schedules_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: class_subjects class_subjects_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_subjects
    ADD CONSTRAINT class_subjects_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: class_subjects class_subjects_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.class_subjects
    ADD CONSTRAINT class_subjects_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: classes classes_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(client_id);


--
-- Name: classes classes_project_supervisor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_project_supervisor_id_fkey FOREIGN KEY (project_supervisor_id) REFERENCES public.users(user_id);


--
-- Name: client_communications client_communications_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_communications
    ADD CONSTRAINT client_communications_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(client_id);


--
-- Name: client_communications client_communications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_communications
    ADD CONSTRAINT client_communications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: client_contact_persons client_contact_persons_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.client_contact_persons
    ADD CONSTRAINT client_contact_persons_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.clients(client_id);


--
-- Name: clients clients_branch_of_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_branch_of_fkey FOREIGN KEY (branch_of) REFERENCES public.clients(client_id);


--
-- Name: clients clients_town_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_town_id_fkey FOREIGN KEY (town_id) REFERENCES public.locations(location_id);


--
-- Name: collections collections_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.collections
    ADD CONSTRAINT collections_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: deliveries deliveries_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: exam_results exam_results_exam_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exam_results
    ADD CONSTRAINT exam_results_exam_id_fkey FOREIGN KEY (exam_id) REFERENCES public.exams(exam_id);


--
-- Name: exam_results exam_results_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exam_results
    ADD CONSTRAINT exam_results_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id);


--
-- Name: exams exams_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exams
    ADD CONSTRAINT exams_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id);


--
-- Name: exams exams_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.exams
    ADD CONSTRAINT exams_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: classes fk_classes_agent; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT fk_classes_agent FOREIGN KEY (class_agent) REFERENCES public.agents(agent_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: classes fk_classes_site; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT fk_classes_site FOREIGN KEY (site_id) REFERENCES public.sites(site_id) ON DELETE SET NULL;


--
-- Name: learners fk_highest_qualification; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners
    ADD CONSTRAINT fk_highest_qualification FOREIGN KEY (highest_qualification) REFERENCES public.learner_qualifications(id);


--
-- Name: CONSTRAINT fk_highest_qualification ON learners; Type: COMMENT; Schema: public; Owner: doadmin
--

COMMENT ON CONSTRAINT fk_highest_qualification ON public.learners IS 'Ensures that highest_qualification in learners references a valid id in learner_qualifications.';


--
-- Name: learners fk_placement_level; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners
    ADD CONSTRAINT fk_placement_level FOREIGN KEY (numeracy_level) REFERENCES public.learner_placement_level(placement_level_id) ON UPDATE CASCADE;


--
-- Name: sites fk_sites_client; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT fk_sites_client FOREIGN KEY (client_id) REFERENCES public.clients(client_id) ON DELETE CASCADE;


--
-- Name: history history_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.history
    ADD CONSTRAINT history_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: learner_portfolios learner_portfolios_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_portfolios
    ADD CONSTRAINT learner_portfolios_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: learner_products learner_products_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_products
    ADD CONSTRAINT learner_products_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id);


--
-- Name: learner_products learner_products_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_products
    ADD CONSTRAINT learner_products_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: learner_progressions learner_progressions_from_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_progressions
    ADD CONSTRAINT learner_progressions_from_product_id_fkey FOREIGN KEY (from_product_id) REFERENCES public.products(product_id);


--
-- Name: learner_progressions learner_progressions_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_progressions
    ADD CONSTRAINT learner_progressions_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id);


--
-- Name: learner_progressions learner_progressions_to_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learner_progressions
    ADD CONSTRAINT learner_progressions_to_product_id_fkey FOREIGN KEY (to_product_id) REFERENCES public.products(product_id);


--
-- Name: learners learners_city_town_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners
    ADD CONSTRAINT learners_city_town_id_fkey FOREIGN KEY (city_town_id) REFERENCES public.locations(location_id);


--
-- Name: learners learners_employer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners
    ADD CONSTRAINT learners_employer_id_fkey FOREIGN KEY (employer_id) REFERENCES public.employers(employer_id);


--
-- Name: learners learners_province_region_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.learners
    ADD CONSTRAINT learners_province_region_id_fkey FOREIGN KEY (province_region_id) REFERENCES public.locations(location_id);


--
-- Name: products products_parent_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_parent_product_id_fkey FOREIGN KEY (parent_product_id) REFERENCES public.products(product_id);


--
-- Name: progress_reports progress_reports_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.progress_reports
    ADD CONSTRAINT progress_reports_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: progress_reports progress_reports_learner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.progress_reports
    ADD CONSTRAINT progress_reports_learner_id_fkey FOREIGN KEY (learner_id) REFERENCES public.learners(id);


--
-- Name: progress_reports progress_reports_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.progress_reports
    ADD CONSTRAINT progress_reports_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: qa_reports qa_reports_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.qa_reports
    ADD CONSTRAINT qa_reports_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.agents(agent_id);


--
-- Name: qa_reports qa_reports_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.qa_reports
    ADD CONSTRAINT qa_reports_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(class_id);


--
-- Name: user_permissions user_permissions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.user_permissions
    ADD CONSTRAINT user_permissions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: wecoza_class_backup_agents wecoza_class_backup_agents_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_backup_agents
    ADD CONSTRAINT wecoza_class_backup_agents_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.wecoza_classes(id) ON DELETE CASCADE;


--
-- Name: wecoza_class_dates wecoza_class_dates_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_dates
    ADD CONSTRAINT wecoza_class_dates_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.wecoza_classes(id) ON DELETE CASCADE;


--
-- Name: wecoza_class_learners wecoza_class_learners_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_learners
    ADD CONSTRAINT wecoza_class_learners_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.wecoza_classes(id) ON DELETE CASCADE;


--
-- Name: wecoza_class_notes wecoza_class_notes_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_notes
    ADD CONSTRAINT wecoza_class_notes_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.wecoza_classes(id) ON DELETE CASCADE;


--
-- Name: wecoza_class_schedule wecoza_class_schedule_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.wecoza_class_schedule
    ADD CONSTRAINT wecoza_class_schedule_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.wecoza_classes(id) ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: doadmin
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


--
-- PostgreSQL database dump complete
--

