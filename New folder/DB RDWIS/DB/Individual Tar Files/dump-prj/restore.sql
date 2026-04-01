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
-- Name: prj; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA prj;


ALTER SCHEMA prj OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: comments; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.comments (
    cmt_xpgh_id integer NOT NULL,
    cmt_id integer NOT NULL,
    cmt_dtg timestamp without time zone,
    cmt_comment character varying(255),
    cmt_author character varying(15),
    cmt_status character varying(10)
);


ALTER TABLE prj.comments OWNER TO postgres;

--
-- Name: comm_cmt_id_seq; Type: SEQUENCE; Schema: prj; Owner: postgres
--

ALTER TABLE prj.comments ALTER COLUMN cmt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME prj.comm_cmt_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: events; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.events (
    evt_id integer NOT NULL,
    evt_name character varying(255),
    evt_doer character varying(15),
    evt_effectee character varying(15),
    evt_dtg timestamp without time zone,
    evt_xprj_id integer,
    evt_xpgh_id integer,
    evt_xcmt_id integer
);


ALTER TABLE prj.events OWNER TO postgres;

--
-- Name: events_evt_id_seq; Type: SEQUENCE; Schema: prj; Owner: postgres
--

ALTER TABLE prj.events ALTER COLUMN evt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME prj.events_evt_id_seq
    START WITH 1152
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: milestones; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.milestones (
    msn_id integer NOT NULL,
    msn_xprj_id integer NOT NULL,
    msn_type character varying(255),
    msn_desc character varying(255),
    msn_cost integer,
    msn_startdt date,
    msn_targetdt date,
    msn_achvdt date,
    msn_comp integer,
    msn_pay integer,
    msn_paydt date,
    msn_status character varying(255),
    msn_rem character varying(255),
    msn_idd integer NOT NULL
);


ALTER TABLE prj.milestones OWNER TO postgres;

--
-- Name: milestones_new_idd_seq; Type: SEQUENCE; Schema: prj; Owner: postgres
--

ALTER TABLE prj.milestones ALTER COLUMN msn_idd ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME prj.milestones_new_idd_seq
    START WITH 311
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: mprgroup; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.mprgroup (
    mgp_id integer NOT NULL,
    mgp_name character varying NOT NULL,
    mgp_dtg timestamp without time zone NOT NULL,
    mgp_status character varying NOT NULL
);


ALTER TABLE prj.mprgroup OWNER TO postgres;

--
-- Name: mprgroup_mgp_id_seq; Type: SEQUENCE; Schema: prj; Owner: postgres
--

ALTER TABLE prj.mprgroup ALTER COLUMN mgp_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME prj.mprgroup_mgp_id_seq
    START WITH 26
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: prghistory; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.prghistory (
    pgh_xprj_id integer NOT NULL,
    pgh_id integer NOT NULL,
    pgh_dtg timestamp without time zone,
    pgh_progress text,
    pgh_author character varying(255) NOT NULL,
    pgh_status character varying(255) NOT NULL,
    pgh_closedtg timestamp without time zone,
    pgh_level integer NOT NULL,
    pgh_group character varying,
    pgh_trail character varying(255),
    pgh_trailinherited character varying(255),
    pgh_underedit boolean DEFAULT true NOT NULL,
    pgh_path character varying
);


ALTER TABLE prj.prghistory OWNER TO postgres;

--
-- Name: prghistory_pgh_id_seq; Type: SEQUENCE; Schema: prj; Owner: postgres
--

ALTER TABLE prj.prghistory ALTER COLUMN pgh_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME prj.prghistory_pgh_id_seq
    START WITH 670
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: prjattachments; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.prjattachments (
    jat_objtype character varying NOT NULL,
    jat_objid integer NOT NULL,
    jat_type character varying NOT NULL,
    jat_path character varying,
    jat_id integer NOT NULL
);


ALTER TABLE prj.prjattachments OWNER TO postgres;

--
-- Name: prjattachments_jat_id_seq; Type: SEQUENCE; Schema: prj; Owner: postgres
--

ALTER TABLE prj.prjattachments ALTER COLUMN jat_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME prj.prjattachments_jat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: projects; Type: TABLE; Schema: prj; Owner: postgres
--

CREATE TABLE prj.projects (
    prj_title character varying NOT NULL,
    prj_id integer NOT NULL,
    prj_startdt date,
    prj_id_old smallint,
    prj_scope character varying(255),
    prj_sponsor character varying(255),
    prj_rcptdt date,
    prj_assigndt date,
    prj_propdt date,
    prj_propcost integer,
    prj_aprvdt date,
    prj_aprvcost integer,
    prj_estenddt date,
    prj_cfycost integer,
    prj_status character varying(255) NOT NULL,
    prj_enddt date,
    prj_rem text,
    prj_notes text,
    prj_notes1 character varying(255),
    prj_unt_id integer NOT NULL,
    prj_reporting boolean DEFAULT true NOT NULL,
    prj_code character varying(9) NOT NULL
);


ALTER TABLE prj.projects OWNER TO postgres;

--
-- Data for Name: comments; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.comments (cmt_xpgh_id, cmt_id, cmt_dtg, cmt_comment, cmt_author, cmt_status) FROM stdin;
\.
COPY prj.comments (cmt_xpgh_id, cmt_id, cmt_dtg, cmt_comment, cmt_author, cmt_status) FROM '$$PATH$$/5267.dat';

--
-- Data for Name: events; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.events (evt_id, evt_name, evt_doer, evt_effectee, evt_dtg, evt_xprj_id, evt_xpgh_id, evt_xcmt_id) FROM stdin;
\.
COPY prj.events (evt_id, evt_name, evt_doer, evt_effectee, evt_dtg, evt_xprj_id, evt_xpgh_id, evt_xcmt_id) FROM '$$PATH$$/5269.dat';

--
-- Data for Name: milestones; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.milestones (msn_id, msn_xprj_id, msn_type, msn_desc, msn_cost, msn_startdt, msn_targetdt, msn_achvdt, msn_comp, msn_pay, msn_paydt, msn_status, msn_rem, msn_idd) FROM stdin;
\.
COPY prj.milestones (msn_id, msn_xprj_id, msn_type, msn_desc, msn_cost, msn_startdt, msn_targetdt, msn_achvdt, msn_comp, msn_pay, msn_paydt, msn_status, msn_rem, msn_idd) FROM '$$PATH$$/5271.dat';

--
-- Data for Name: mprgroup; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.mprgroup (mgp_id, mgp_name, mgp_dtg, mgp_status) FROM stdin;
\.
COPY prj.mprgroup (mgp_id, mgp_name, mgp_dtg, mgp_status) FROM '$$PATH$$/5273.dat';

--
-- Data for Name: prghistory; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.prghistory (pgh_xprj_id, pgh_id, pgh_dtg, pgh_progress, pgh_author, pgh_status, pgh_closedtg, pgh_level, pgh_group, pgh_trail, pgh_trailinherited, pgh_underedit, pgh_path) FROM stdin;
\.
COPY prj.prghistory (pgh_xprj_id, pgh_id, pgh_dtg, pgh_progress, pgh_author, pgh_status, pgh_closedtg, pgh_level, pgh_group, pgh_trail, pgh_trailinherited, pgh_underedit, pgh_path) FROM '$$PATH$$/5275.dat';

--
-- Data for Name: prjattachments; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.prjattachments (jat_objtype, jat_objid, jat_type, jat_path, jat_id) FROM stdin;
\.
COPY prj.prjattachments (jat_objtype, jat_objid, jat_type, jat_path, jat_id) FROM '$$PATH$$/5277.dat';

--
-- Data for Name: projects; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.projects (prj_title, prj_id, prj_startdt, prj_id_old, prj_scope, prj_sponsor, prj_rcptdt, prj_assigndt, prj_propdt, prj_propcost, prj_aprvdt, prj_aprvcost, prj_estenddt, prj_cfycost, prj_status, prj_enddt, prj_rem, prj_notes, prj_notes1, prj_unt_id, prj_reporting, prj_code) FROM stdin;
\.
COPY prj.projects (prj_title, prj_id, prj_startdt, prj_id_old, prj_scope, prj_sponsor, prj_rcptdt, prj_assigndt, prj_propdt, prj_propcost, prj_aprvdt, prj_aprvcost, prj_estenddt, prj_cfycost, prj_status, prj_enddt, prj_rem, prj_notes, prj_notes1, prj_unt_id, prj_reporting, prj_code) FROM '$$PATH$$/5279.dat';

--
-- Name: comm_cmt_id_seq; Type: SEQUENCE SET; Schema: prj; Owner: postgres
--

SELECT pg_catalog.setval('prj.comm_cmt_id_seq', 35, true);


--
-- Name: events_evt_id_seq; Type: SEQUENCE SET; Schema: prj; Owner: postgres
--

SELECT pg_catalog.setval('prj.events_evt_id_seq', 2061, true);


--
-- Name: milestones_new_idd_seq; Type: SEQUENCE SET; Schema: prj; Owner: postgres
--

SELECT pg_catalog.setval('prj.milestones_new_idd_seq', 434, true);


--
-- Name: mprgroup_mgp_id_seq; Type: SEQUENCE SET; Schema: prj; Owner: postgres
--

SELECT pg_catalog.setval('prj.mprgroup_mgp_id_seq', 57, true);


--
-- Name: prghistory_pgh_id_seq; Type: SEQUENCE SET; Schema: prj; Owner: postgres
--

SELECT pg_catalog.setval('prj.prghistory_pgh_id_seq', 1257, true);


--
-- Name: prjattachments_jat_id_seq; Type: SEQUENCE SET; Schema: prj; Owner: postgres
--

SELECT pg_catalog.setval('prj.prjattachments_jat_id_seq', 310, true);


--
-- Name: comments comments_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.comments
    ADD CONSTRAINT comments_pk PRIMARY KEY (cmt_id);


--
-- Name: events events_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.events
    ADD CONSTRAINT events_pk PRIMARY KEY (evt_id);


--
-- Name: milestones milestones_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.milestones
    ADD CONSTRAINT milestones_pk PRIMARY KEY (msn_idd);


--
-- Name: milestones milestones_un; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.milestones
    ADD CONSTRAINT milestones_un UNIQUE (msn_id, msn_xprj_id);


--
-- Name: mprgroup mprgroup_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.mprgroup
    ADD CONSTRAINT mprgroup_pk PRIMARY KEY (mgp_id);


--
-- Name: prghistory prghistory_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.prghistory
    ADD CONSTRAINT prghistory_pk PRIMARY KEY (pgh_id);


--
-- Name: projects projects_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.projects
    ADD CONSTRAINT projects_pk PRIMARY KEY (prj_id);


--
-- Name: projects projects_un; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.projects
    ADD CONSTRAINT projects_un UNIQUE (prj_code);


--
-- Name: prjattachments purattachments_pk; Type: CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.prjattachments
    ADD CONSTRAINT purattachments_pk PRIMARY KEY (jat_id);


--
-- Name: comments comments_fk; Type: FK CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.comments
    ADD CONSTRAINT comments_fk FOREIGN KEY (cmt_xpgh_id) REFERENCES prj.prghistory(pgh_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: milestones milestones_fk; Type: FK CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.milestones
    ADD CONSTRAINT milestones_fk FOREIGN KEY (msn_xprj_id) REFERENCES prj.projects(prj_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: prghistory prghistory_fk; Type: FK CONSTRAINT; Schema: prj; Owner: postgres
--

ALTER TABLE ONLY prj.prghistory
    ADD CONSTRAINT prghistory_fk FOREIGN KEY (pgh_xprj_id) REFERENCES prj.projects(prj_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

