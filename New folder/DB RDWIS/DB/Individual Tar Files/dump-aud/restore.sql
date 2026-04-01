--
-- NOTE:
--
-- File paths need to be edited. Search for $$PATH$$ and
-- replace it with the path to the directory containing
-- the extracted data files.
--
--
-- PostgreSQL database dump
--

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

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

DROP DATABASE newdev;
--
-- Name: newdev; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE newdev WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'English_United States.1252';


ALTER DATABASE newdev OWNER TO postgres;

\unrestrict (null)
\connect newdev
\restrict (null)

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
-- Name: aud; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA aud;


ALTER SCHEMA aud OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: audattachments; Type: TABLE; Schema: aud; Owner: postgres
--

CREATE TABLE aud.audattachments (
    aat_objtype character varying NOT NULL,
    aat_objid integer NOT NULL,
    aat_type character varying NOT NULL,
    aat_path character varying,
    aat_id integer NOT NULL
);


ALTER TABLE aud.audattachments OWNER TO postgres;

--
-- Name: audattachments_aat_id_seq; Type: SEQUENCE; Schema: aud; Owner: postgres
--

ALTER TABLE aud.audattachments ALTER COLUMN aat_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME aud.audattachments_aat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: busdata; Type: TABLE; Schema: aud; Owner: postgres
--

CREATE TABLE aud.busdata (
    bdt_id integer NOT NULL,
    bdt_username character varying NOT NULL,
    bdt_dtg timestamp(0) without time zone NOT NULL,
    bdt_action character varying NOT NULL,
    bdt_formname character varying NOT NULL,
    bdt_controlname character varying NOT NULL,
    bdt_fieldname character varying NOT NULL,
    bdt_recordid character varying NOT NULL,
    btd_oldvalue character varying NOT NULL,
    btd_newvalue character varying NOT NULL,
    bdt_empname character varying NOT NULL,
    bdt_empdesig character varying NOT NULL
);


ALTER TABLE aud.busdata OWNER TO postgres;

--
-- Name: busdata_bdt_id_seq; Type: SEQUENCE; Schema: aud; Owner: postgres
--

ALTER TABLE aud.busdata ALTER COLUMN bdt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME aud.busdata_bdt_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: revcomps; Type: TABLE; Schema: aud; Owner: postgres
--

CREATE TABLE aud.revcomps (
    rvc_id integer NOT NULL,
    rvc_rev_id integer NOT NULL,
    rvc_table character varying NOT NULL,
    rvc_detail text NOT NULL,
    rvc_rowid character varying NOT NULL,
    rvc_action character varying NOT NULL,
    rvc_type smallint
);


ALTER TABLE aud.revcomps OWNER TO postgres;

--
-- Name: revcomps_rvc_id_seq; Type: SEQUENCE; Schema: aud; Owner: postgres
--

ALTER TABLE aud.revcomps ALTER COLUMN rvc_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME aud.revcomps_rvc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: revdata; Type: TABLE; Schema: aud; Owner: postgres
--

CREATE TABLE aud.revdata (
    rvd_id integer NOT NULL,
    rvd_rev_id integer NOT NULL,
    rvd_table character varying NOT NULL,
    rvd_rowid character varying NOT NULL,
    rvd_attrib character varying NOT NULL,
    rvd_oldvalue character varying,
    rvd_newvalue character varying,
    rvd_datatype character varying NOT NULL,
    rvd_type smallint NOT NULL,
    rvd_conversion character(1),
    rvd_colname character varying,
    rvd_alias character varying
);


ALTER TABLE aud.revdata OWNER TO postgres;

--
-- Name: revdata_rvd_id_seq; Type: SEQUENCE; Schema: aud; Owner: postgres
--

ALTER TABLE aud.revdata ALTER COLUMN rvd_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME aud.revdata_rvd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: revs; Type: TABLE; Schema: aud; Owner: postgres
--

CREATE TABLE aud.revs (
    rev_id integer NOT NULL,
    rev_obj character varying NOT NULL,
    rev_releasedtg timestamp without time zone,
    rev_closedtg timestamp without time zone,
    rev_objid character varying NOT NULL,
    rev_reason character varying,
    rev_status character varying DEFAULT 'Draft'::character varying NOT NULL,
    rev_unt_id integer NOT NULL,
    rev_date date NOT NULL,
    rev_type smallint NOT NULL,
    rev_intunt_id integer NOT NULL,
    rev_ref character varying
);


ALTER TABLE aud.revs OWNER TO postgres;

--
-- Name: revs_rev_id_seq; Type: SEQUENCE; Schema: aud; Owner: postgres
--

ALTER TABLE aud.revs ALTER COLUMN rev_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME aud.revs_rev_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: audattachments; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.audattachments (aat_objtype, aat_objid, aat_type, aat_path, aat_id) FROM stdin;
\.
COPY aud.audattachments (aat_objtype, aat_objid, aat_type, aat_path, aat_id) FROM '$$PATH$$/5253.dat';

--
-- Data for Name: busdata; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.busdata (bdt_id, bdt_username, bdt_dtg, bdt_action, bdt_formname, bdt_controlname, bdt_fieldname, bdt_recordid, btd_oldvalue, btd_newvalue, bdt_empname, bdt_empdesig) FROM stdin;
\.
COPY aud.busdata (bdt_id, bdt_username, bdt_dtg, bdt_action, bdt_formname, bdt_controlname, bdt_fieldname, bdt_recordid, btd_oldvalue, btd_newvalue, bdt_empname, bdt_empdesig) FROM '$$PATH$$/5255.dat';

--
-- Data for Name: revcomps; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.revcomps (rvc_id, rvc_rev_id, rvc_table, rvc_detail, rvc_rowid, rvc_action, rvc_type) FROM stdin;
\.
COPY aud.revcomps (rvc_id, rvc_rev_id, rvc_table, rvc_detail, rvc_rowid, rvc_action, rvc_type) FROM '$$PATH$$/5257.dat';

--
-- Data for Name: revdata; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.revdata (rvd_id, rvd_rev_id, rvd_table, rvd_rowid, rvd_attrib, rvd_oldvalue, rvd_newvalue, rvd_datatype, rvd_type, rvd_conversion, rvd_colname, rvd_alias) FROM stdin;
\.
COPY aud.revdata (rvd_id, rvd_rev_id, rvd_table, rvd_rowid, rvd_attrib, rvd_oldvalue, rvd_newvalue, rvd_datatype, rvd_type, rvd_conversion, rvd_colname, rvd_alias) FROM '$$PATH$$/5259.dat';

--
-- Data for Name: revs; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.revs (rev_id, rev_obj, rev_releasedtg, rev_closedtg, rev_objid, rev_reason, rev_status, rev_unt_id, rev_date, rev_type, rev_intunt_id, rev_ref) FROM stdin;
\.
COPY aud.revs (rev_id, rev_obj, rev_releasedtg, rev_closedtg, rev_objid, rev_reason, rev_status, rev_unt_id, rev_date, rev_type, rev_intunt_id, rev_ref) FROM '$$PATH$$/5261.dat';

--
-- Name: audattachments_aat_id_seq; Type: SEQUENCE SET; Schema: aud; Owner: postgres
--

SELECT pg_catalog.setval('aud.audattachments_aat_id_seq', 150, true);


--
-- Name: busdata_bdt_id_seq; Type: SEQUENCE SET; Schema: aud; Owner: postgres
--

SELECT pg_catalog.setval('aud.busdata_bdt_id_seq', 1097, true);


--
-- Name: revcomps_rvc_id_seq; Type: SEQUENCE SET; Schema: aud; Owner: postgres
--

SELECT pg_catalog.setval('aud.revcomps_rvc_id_seq', 349, true);


--
-- Name: revdata_rvd_id_seq; Type: SEQUENCE SET; Schema: aud; Owner: postgres
--

SELECT pg_catalog.setval('aud.revdata_rvd_id_seq', 362, true);


--
-- Name: revs_rev_id_seq; Type: SEQUENCE SET; Schema: aud; Owner: postgres
--

SELECT pg_catalog.setval('aud.revs_rev_id_seq', 249, true);


--
-- Name: busdata busdata_pk; Type: CONSTRAINT; Schema: aud; Owner: postgres
--

ALTER TABLE ONLY aud.busdata
    ADD CONSTRAINT busdata_pk PRIMARY KEY (bdt_id);


--
-- Name: audattachments empattachments_pk; Type: CONSTRAINT; Schema: aud; Owner: postgres
--

ALTER TABLE ONLY aud.audattachments
    ADD CONSTRAINT empattachments_pk PRIMARY KEY (aat_id);


--
-- Name: revcomps revcomps_pk; Type: CONSTRAINT; Schema: aud; Owner: postgres
--

ALTER TABLE ONLY aud.revcomps
    ADD CONSTRAINT revcomps_pk PRIMARY KEY (rvc_id);


--
-- Name: revdata revdata_pk; Type: CONSTRAINT; Schema: aud; Owner: postgres
--

ALTER TABLE ONLY aud.revdata
    ADD CONSTRAINT revdata_pk PRIMARY KEY (rvd_id);


--
-- Name: revs revs_pk; Type: CONSTRAINT; Schema: aud; Owner: postgres
--

ALTER TABLE ONLY aud.revs
    ADD CONSTRAINT revs_pk PRIMARY KEY (rev_id);


--
-- PostgreSQL database dump complete
--

