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

SET default_tablespace = '';

SET default_table_access_method = heap;

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
-- Name: sites site_id; Type: DEFAULT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.sites ALTER COLUMN site_id SET DEFAULT nextval('public.sites_site_id_seq'::regclass);


--
-- Name: sites sites_pkey; Type: CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT sites_pkey PRIMARY KEY (site_id);


--
-- Name: idx_sites_client_id; Type: INDEX; Schema: public; Owner: doadmin
--

CREATE INDEX idx_sites_client_id ON public.sites USING btree (client_id);


--
-- Name: sites fk_sites_client; Type: FK CONSTRAINT; Schema: public; Owner: doadmin
--

ALTER TABLE ONLY public.sites
    ADD CONSTRAINT fk_sites_client FOREIGN KEY (client_id) REFERENCES public.clients(client_id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

