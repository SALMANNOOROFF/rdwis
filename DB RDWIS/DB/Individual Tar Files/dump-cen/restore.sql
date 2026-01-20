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
-- Name: cen; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA cen;


ALTER SCHEMA cen OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: accounts; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.accounts (
    acc_id integer NOT NULL,
    acc_unt_id integer NOT NULL,
    acc_level smallint NOT NULL,
    acc_type character varying(255) NOT NULL,
    acc_reportlevel integer,
    acc_username character varying(255) NOT NULL,
    acc_pass character varying(255) NOT NULL,
    acc_startdt timestamp with time zone NOT NULL,
    acc_status character varying(255) NOT NULL,
    acc_enddt timestamp with time zone,
    acc_name character varying(255) NOT NULL,
    acc_title character varying(255),
    acc_rank character varying(255),
    acc_desig character varying(255) NOT NULL,
    acc_desigshort character varying(255) NOT NULL,
    acc_desigtype character varying(10) NOT NULL,
    acc_lowerm integer NOT NULL,
    acc_upperm integer NOT NULL,
    acc_access character varying NOT NULL,
    acc_lowers integer NOT NULL,
    acc_uppers integer NOT NULL,
    acc_untname character varying NOT NULL,
    acc_untnamesh character varying NOT NULL,
    acc_unttype character varying NOT NULL,
    acc_auth character varying NOT NULL,
    acc_untarea character varying NOT NULL
);


ALTER TABLE cen.accounts OWNER TO postgres;

--
-- Name: globalvars; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.globalvars (
    gvar_name character varying(255) NOT NULL,
    gvar_value character varying(255),
    gvar_type character varying,
    gvar_remarks character varying
);


ALTER TABLE cen.globalvars OWNER TO postgres;

--
-- Name: heads; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.heads (
    hed_id integer NOT NULL,
    hed_name character varying NOT NULL,
    hed_type character varying NOT NULL,
    hed_code character varying NOT NULL,
    hed_opendt date,
    hed_closedt date,
    hed_transtype smallint,
    hed_unt_id integer NOT NULL,
    hed_prj_id integer
);


ALTER TABLE cen.heads OWNER TO postgres;

--
-- Name: levels; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.levels (
    lvl_id smallint NOT NULL,
    lvl_cat character varying(255)
);


ALTER TABLE cen.levels OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.roles (
    rol_xunt_id integer NOT NULL,
    rol_level smallint NOT NULL,
    rol_desig character varying(255) NOT NULL,
    rol_desigshort character varying(255) NOT NULL,
    rol_type character varying(255) NOT NULL,
    rol_reportlevel smallint,
    rol_access character varying,
    rol_authprj character varying
);


ALTER TABLE cen.roles OWNER TO postgres;

--
-- Name: routes; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.routes (
    rte_doc character varying(255) NOT NULL,
    rte_steps character varying(255) NOT NULL
);


ALTER TABLE cen.routes OWNER TO postgres;

--
-- Name: units; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.units (
    unt_id integer NOT NULL,
    unt_type character varying(255) NOT NULL,
    unt_name character varying(255) NOT NULL,
    unt_namesh character varying(255) NOT NULL,
    unt_alowerlimit integer NOT NULL,
    unt_aupperlimit integer NOT NULL,
    unt_nlowerlimit integer NOT NULL,
    unt_nupperlimit integer NOT NULL,
    unt_area character varying,
    unt_leadname character varying,
    unt_leadtitle character varying,
    unt_leadrank character varying,
    unt_leaddesig character varying,
    unt_leaddesigshort character varying
);


ALTER TABLE cen.units OWNER TO postgres;

--
-- Name: version; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.version (
    ver_version numeric NOT NULL,
    ver_compat numeric
);


ALTER TABLE cen.version OWNER TO postgres;

--
-- Data for Name: accounts; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.accounts (acc_id, acc_unt_id, acc_level, acc_type, acc_reportlevel, acc_username, acc_pass, acc_startdt, acc_status, acc_enddt, acc_name, acc_title, acc_rank, acc_desig, acc_desigshort, acc_desigtype, acc_lowerm, acc_upperm, acc_access, acc_lowers, acc_uppers, acc_untname, acc_untnamesh, acc_unttype, acc_auth, acc_untarea) FROM stdin;
\.
COPY cen.accounts (acc_id, acc_unt_id, acc_level, acc_type, acc_reportlevel, acc_username, acc_pass, acc_startdt, acc_status, acc_enddt, acc_name, acc_title, acc_rank, acc_desig, acc_desigshort, acc_desigtype, acc_lowerm, acc_upperm, acc_access, acc_lowers, acc_uppers, acc_untname, acc_untnamesh, acc_unttype, acc_auth, acc_untarea) FROM '$$PATH$$/5269.dat';

--
-- Data for Name: globalvars; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.globalvars (gvar_name, gvar_value, gvar_type, gvar_remarks) FROM stdin;
\.
COPY cen.globalvars (gvar_name, gvar_value, gvar_type, gvar_remarks) FROM '$$PATH$$/5270.dat';

--
-- Data for Name: heads; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.heads (hed_id, hed_name, hed_type, hed_code, hed_opendt, hed_closedt, hed_transtype, hed_unt_id, hed_prj_id) FROM stdin;
\.
COPY cen.heads (hed_id, hed_name, hed_type, hed_code, hed_opendt, hed_closedt, hed_transtype, hed_unt_id, hed_prj_id) FROM '$$PATH$$/5271.dat';

--
-- Data for Name: levels; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.levels (lvl_id, lvl_cat) FROM stdin;
\.
COPY cen.levels (lvl_id, lvl_cat) FROM '$$PATH$$/5272.dat';

--
-- Data for Name: roles; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.roles (rol_xunt_id, rol_level, rol_desig, rol_desigshort, rol_type, rol_reportlevel, rol_access, rol_authprj) FROM stdin;
\.
COPY cen.roles (rol_xunt_id, rol_level, rol_desig, rol_desigshort, rol_type, rol_reportlevel, rol_access, rol_authprj) FROM '$$PATH$$/5273.dat';

--
-- Data for Name: routes; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.routes (rte_doc, rte_steps) FROM stdin;
\.
COPY cen.routes (rte_doc, rte_steps) FROM '$$PATH$$/5274.dat';

--
-- Data for Name: units; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.units (unt_id, unt_type, unt_name, unt_namesh, unt_alowerlimit, unt_aupperlimit, unt_nlowerlimit, unt_nupperlimit, unt_area, unt_leadname, unt_leadtitle, unt_leadrank, unt_leaddesig, unt_leaddesigshort) FROM stdin;
\.
COPY cen.units (unt_id, unt_type, unt_name, unt_namesh, unt_alowerlimit, unt_aupperlimit, unt_nlowerlimit, unt_nupperlimit, unt_area, unt_leadname, unt_leadtitle, unt_leadrank, unt_leaddesig, unt_leaddesigshort) FROM '$$PATH$$/5275.dat';

--
-- Data for Name: version; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.version (ver_version, ver_compat) FROM stdin;
\.
COPY cen.version (ver_version, ver_compat) FROM '$$PATH$$/5276.dat';

--
-- Name: accounts accounts_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_pk PRIMARY KEY (acc_id);


--
-- Name: accounts accounts_un; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_un UNIQUE (acc_username);


--
-- Name: roles desigs_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.roles
    ADD CONSTRAINT desigs_pk PRIMARY KEY (rol_desig);


--
-- Name: globalvars globalvars_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.globalvars
    ADD CONSTRAINT globalvars_pk PRIMARY KEY (gvar_name);


--
-- Name: heads heads_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.heads
    ADD CONSTRAINT heads_pk PRIMARY KEY (hed_id);


--
-- Name: heads heads_un; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.heads
    ADD CONSTRAINT heads_un UNIQUE (hed_code);


--
-- Name: levels levels_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.levels
    ADD CONSTRAINT levels_pk PRIMARY KEY (lvl_id);


--
-- Name: routes routes_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.routes
    ADD CONSTRAINT routes_pk PRIMARY KEY (rte_doc);


--
-- Name: units units_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.units
    ADD CONSTRAINT units_pk PRIMARY KEY (unt_id);


--
-- Name: version version_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.version
    ADD CONSTRAINT version_pk PRIMARY KEY (ver_version);


--
-- Name: accounts accounts_fk; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_fk FOREIGN KEY (acc_desig) REFERENCES cen.roles(rol_desig);


--
-- Name: accounts accounts_fk_1; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_fk_1 FOREIGN KEY (acc_unt_id) REFERENCES cen.units(unt_id);


--
-- Name: accounts accounts_fk_2; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_fk_2 FOREIGN KEY (acc_level) REFERENCES cen.levels(lvl_id);


--
-- Name: roles roles_fk; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.roles
    ADD CONSTRAINT roles_fk FOREIGN KEY (rol_xunt_id) REFERENCES cen.units(unt_id);


--
-- PostgreSQL database dump complete
--

