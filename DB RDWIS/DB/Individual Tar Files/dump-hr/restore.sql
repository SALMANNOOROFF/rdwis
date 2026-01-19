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
-- Name: hr; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA hr;


ALTER SCHEMA hr OWNER TO postgres;

--
-- Name: SCHEMA hr; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA hr IS 'standard public schema';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: applicjobs; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.applicjobs (
    apj_company character varying NOT NULL,
    apj_jobtitle character varying NOT NULL,
    apj_repto character varying NOT NULL,
    apj_team smallint,
    apj_from date NOT NULL,
    apj_to date,
    apj_resp character varying NOT NULL,
    apj_ach character varying,
    apj_id integer NOT NULL,
    apj_apl_id integer NOT NULL,
    apj_city character varying NOT NULL
);


ALTER TABLE hr.applicjobs OWNER TO postgres;

--
-- Name: appjobs_apj_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.applicjobs ALTER COLUMN apj_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.appjobs_apj_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: applicants; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.applicants (
    apl_id integer NOT NULL,
    apl_cnic character varying(15) NOT NULL,
    apl_name character varying NOT NULL,
    apl_discip character varying NOT NULL,
    apl_qualif smallint NOT NULL,
    apl_spec character varying,
    apl_paddress character varying NOT NULL,
    apl_dob date NOT NULL,
    apl_dtg timestamp(0) without time zone NOT NULL,
    apl_marital character varying NOT NULL,
    apl_ntnlty character varying NOT NULL,
    apl_pob character varying NOT NULL,
    apl_taddress character varying NOT NULL,
    apl_mobile character varying(13) NOT NULL,
    apl_landline character varying(13),
    apl_gender character varying NOT NULL,
    apl_mobile2 character varying,
    apl_rank character varying,
    apl_status character varying NOT NULL,
    apl_remarks character varying,
    apl_appliedfor character varying,
    apl_currentsal integer,
    apl_expectedsal integer,
    apl_experience numeric(5,0) NOT NULL,
    apl_expjoindt date,
    apl_email character varying NOT NULL,
    apl_father character varying NOT NULL,
    apl_unt_id integer
);


ALTER TABLE hr.applicants OWNER TO postgres;

--
-- Name: applicants_column1_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.applicants ALTER COLUMN apl_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.applicants_column1_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: applicqualifs; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.applicqualifs (
    apq_id integer NOT NULL,
    apq_type character varying NOT NULL,
    apq_level smallint NOT NULL,
    apq_name character varying NOT NULL,
    apq_inst character varying NOT NULL,
    apq_duration numeric NOT NULL,
    apq_unit character varying NOT NULL,
    apq_enddt date NOT NULL,
    apq_apl_id integer NOT NULL,
    apq_grade character varying,
    apq_license character varying,
    apq_spec character varying
);


ALTER TABLE hr.applicqualifs OWNER TO postgres;

--
-- Name: attendance; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.attendance (
    att_id bigint NOT NULL,
    att_emp_id character varying(13) NOT NULL,
    att_empnamecomp character varying NOT NULL,
    att_unt_id integer NOT NULL,
    att_startdt date NOT NULL,
    att_enddt date NOT NULL,
    att_1 character(1),
    att_2 character(1),
    att_3 character(1),
    att_4 character(1),
    att_5 character(1),
    att_6 character(1),
    att_7 character(1),
    att_8 character(1),
    att_9 character(1),
    att_10 character(1),
    att_11 character(1),
    att_12 character(1),
    att_13 character(1),
    att_14 character(1),
    att_15 character(1),
    att_16 character(1),
    att_17 character(1),
    att_18 character(1),
    att_19 character(1),
    att_20 character(1),
    att_21 character(1),
    att_22 character(1),
    att_23 character(1),
    att_24 character(1),
    att_25 character(1),
    att_26 character(1),
    att_27 character(1),
    att_28 character(1),
    att_29 character(1),
    att_30 character(1),
    att_31 character(1),
    att_locked1 boolean DEFAULT false NOT NULL,
    att_locked2 boolean DEFAULT false NOT NULL,
    att_eahreplace boolean DEFAULT false
);


ALTER TABLE hr.attendance OWNER TO postgres;

--
-- Name: attendance_att_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.attendance ALTER COLUMN att_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.attendance_att_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: bdapps; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.bdapps (
    bda_brd_id integer NOT NULL,
    bda_apl_id integer NOT NULL
);


ALTER TABLE hr.bdapps OWNER TO postgres;

--
-- Name: bdmems; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.bdmems (
    bdm_id smallint NOT NULL,
    bdm_brd_id integer NOT NULL,
    bdm_name character varying NOT NULL,
    bdm_rank character varying NOT NULL
);


ALTER TABLE hr.bdmems OWNER TO postgres;

--
-- Name: bnkaccounts; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.bnkaccounts (
    bac_id integer NOT NULL,
    bac_emp_id character varying(13) NOT NULL,
    bac_bnkname character varying NOT NULL,
    bac_bchname character varying NOT NULL,
    bac_bchcode character varying,
    bac_acctitle character varying NOT NULL,
    bac_accnum character varying NOT NULL,
    bac_bchcity character varying NOT NULL,
    bac_selforpay boolean
);


ALTER TABLE hr.bnkaccounts OWNER TO postgres;

--
-- Name: bnkaccounts_bac_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.bnkaccounts ALTER COLUMN bac_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.bnkaccounts_bac_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: boards; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.boards (
    brd_id integer NOT NULL,
    brd_date date NOT NULL,
    brd_status character varying,
    brd_hir_id integer NOT NULL
);


ALTER TABLE hr.boards OWNER TO postgres;

--
-- Name: boards_brd_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.boards ALTER COLUMN brd_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.boards_brd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: contractplans; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.contractplans (
    cpn_id integer NOT NULL,
    cpn_ctr_id integer NOT NULL,
    cpn_startdt date NOT NULL,
    cpn_enddt date NOT NULL,
    cpn_hed_id integer
);


ALTER TABLE hr.contractplans OWNER TO postgres;

--
-- Name: contractplans_cpn_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.contractplans ALTER COLUMN cpn_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.contractplans_cpn_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: contracts; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.contracts (
    ctr_unt_id integer NOT NULL,
    ctr_num character varying(13) NOT NULL,
    ctr_startdt date NOT NULL,
    ctr_enddt date NOT NULL,
    ctr_date date NOT NULL,
    ctr_jobtitle character varying NOT NULL,
    ctr_grade character varying NOT NULL,
    ctr_salary integer NOT NULL,
    ctr_hed_id integer,
    ctr_id integer NOT NULL,
    ctr_prob smallint,
    ctr_probsal integer,
    ctr_termindt date,
    ctr_remarks character varying,
    ctr_path character varying,
    ctr_type smallint NOT NULL,
    ctr_path2 character varying
);


ALTER TABLE hr.contracts OWNER TO postgres;

--
-- Name: contracts_ctr_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.contracts ALTER COLUMN ctr_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.contracts_ctr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: empattachments; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.empattachments (
    eat_objtype character varying NOT NULL,
    eat_objid character varying NOT NULL,
    eat_type character varying NOT NULL,
    eat_path character varying,
    eat_id integer NOT NULL
);


ALTER TABLE hr.empattachments OWNER TO postgres;

--
-- Name: empattachments_eat_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.empattachments ALTER COLUMN eat_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.empattachments_eat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: qualifs; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.qualifs (
    qlf_id integer NOT NULL,
    qlf_type character varying NOT NULL,
    qlf_level smallint NOT NULL,
    qlf_name character varying,
    qlf_inst character varying NOT NULL,
    qlf_duration numeric NOT NULL,
    qlf_unit character varying NOT NULL,
    qlf_enddt date NOT NULL,
    qlf_emp_id character varying(13) NOT NULL,
    qlf_grade character varying,
    qlf_license character varying,
    qlf_spec character varying
);


ALTER TABLE hr.qualifs OWNER TO postgres;

--
-- Name: empqualifs_qlf_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.qualifs ALTER COLUMN qlf_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.empqualifs_qlf_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: emps; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.emps (
    emp_id character varying(13) NOT NULL,
    emp_cnic character varying(15) NOT NULL,
    emp_name character varying NOT NULL,
    emp_joindt date NOT NULL,
    emp_locked boolean DEFAULT false NOT NULL,
    emp_rank character varying,
    emp_status character varying NOT NULL,
    emp_remarks character varying,
    emp_unt_id integer NOT NULL,
    emp_hed_id integer,
    emp_lastdt date,
    emp_title character varying,
    emp_photodest character varying
);


ALTER TABLE hr.emps OWNER TO postgres;

--
-- Name: empsexta; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.empsexta (
    empexta_emp_id character varying(13) NOT NULL,
    emp_discip character varying NOT NULL,
    emp_qualif smallint NOT NULL,
    emp_spec character varying,
    emp_paddress character varying NOT NULL,
    emp_dob date NOT NULL,
    emp_marital character varying NOT NULL,
    emp_ntnlty character varying NOT NULL,
    emp_pob character varying NOT NULL,
    emp_taddress character varying NOT NULL,
    emp_mobile character varying(13) NOT NULL,
    emp_landline character varying(13),
    emp_gender character varying NOT NULL,
    emp_mobile2 character varying,
    emp_email character varying NOT NULL,
    emp_father character varying NOT NULL
);


ALTER TABLE hr.empsexta OWNER TO postgres;

--
-- Name: empsextb; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.empsextb (
    empextb_emp_id character varying(13) NOT NULL,
    emp_nokname character varying NOT NULL,
    emp_nokrelation character varying NOT NULL,
    emp_nokcnic character varying(15) NOT NULL,
    emp_emername character varying NOT NULL,
    emp_emerrelation character varying NOT NULL,
    emp_emermobile character varying NOT NULL,
    emp_idmark character varying NOT NULL,
    emp_height numeric NOT NULL,
    emp_caste character varying NOT NULL,
    emp_religion character varying NOT NULL,
    emp_sect character varying NOT NULL,
    emp_police character varying NOT NULL,
    emp_political character varying NOT NULL
);


ALTER TABLE hr.empsextb OWNER TO postgres;

--
-- Name: empsextc; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.empsextc (
    empextc_emp_id character varying(13) NOT NULL,
    emp_cnum character varying,
    emp_cissuedt date,
    emp_cexpdt date,
    emp_secclear character varying
);


ALTER TABLE hr.empsextc OWNER TO postgres;

--
-- Name: hirings; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.hirings (
    hir_id integer NOT NULL,
    hir_hhd_id smallint NOT NULL,
    hir_reference character varying NOT NULL,
    hir_date date NOT NULL,
    hir_jobtitle character varying NOT NULL,
    hir_grade character varying NOT NULL,
    hir_salarymin integer NOT NULL,
    hir_salarymax integer NOT NULL
);


ALTER TABLE hr.hirings OWNER TO postgres;

--
-- Name: hrheads; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.hrheads (
    hhd_id smallint NOT NULL,
    hhd_dpt_id smallint NOT NULL,
    hhd_name character varying NOT NULL
);


ALTER TABLE hr.hrheads OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.jobs (
    job_company character varying NOT NULL,
    job_jobtitle character varying NOT NULL,
    job_repto character varying,
    job_team smallint,
    job_from date NOT NULL,
    job_to date,
    job_resp character varying,
    job_ach character varying,
    job_emp_id character varying(13) NOT NULL,
    job_id integer NOT NULL,
    job_city character varying NOT NULL
);


ALTER TABLE hr.jobs OWNER TO postgres;

--
-- Name: jobs_job_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.jobs ALTER COLUMN job_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.jobs_job_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: qualifs_qlf_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.applicqualifs ALTER COLUMN apq_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.qualifs_qlf_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: request_req_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.hirings ALTER COLUMN hir_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.request_req_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: salreqs; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.salreqs (
    srq_id integer NOT NULL,
    srq_emp_id character varying NOT NULL,
    srq_empnamecomp character varying NOT NULL,
    srq_unt_id integer NOT NULL,
    srq_hed_id integer,
    srq_effunt_id integer NOT NULL,
    srq_effhed_id integer NOT NULL,
    srq_month date NOT NULL,
    srq_unpaiddays smallint NOT NULL,
    srq_salary integer NOT NULL,
    srq_status character varying NOT NULL,
    srq_remarks character varying,
    srq_releasedtg timestamp(0) without time zone,
    srq_closedtg timestamp(0) without time zone,
    srq_fulfilment integer,
    srq_ctrsalary integer NOT NULL,
    srq_grosalary integer NOT NULL,
    srq_netsalary integer NOT NULL,
    srq_bnkaccdetail character varying NOT NULL,
    srq_bnkacctitle character varying NOT NULL,
    srq_contracts character varying NOT NULL,
    srq_checked boolean DEFAULT false NOT NULL,
    srq_arrears integer NOT NULL,
    srq_dues integer NOT NULL,
    srq_overwork integer NOT NULL,
    srq_underwork integer NOT NULL,
    srq_loaned integer NOT NULL,
    srq_withheld integer NOT NULL,
    srq_award integer NOT NULL,
    srq_penalty integer NOT NULL,
    srq_paidalready integer NOT NULL,
    srq_paidholidays smallint NOT NULL,
    srq_remarks2 character varying,
    srq_sudohed character varying(7)
);


ALTER TABLE hr.salreqs OWNER TO postgres;

--
-- Name: salreqs_srq_id_seq1; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.salreqs ALTER COLUMN srq_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.salreqs_srq_id_seq1
    START WITH 297
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: vehicles; Type: TABLE; Schema: hr; Owner: postgres
--

CREATE TABLE hr.vehicles (
    vcl_id integer NOT NULL,
    vcl_emp_id character varying(13) NOT NULL,
    vcl_type character varying NOT NULL,
    vcl_maker character varying NOT NULL,
    vcl_variant character varying NOT NULL,
    vcl_year smallint NOT NULL,
    vcl_regis character varying NOT NULL,
    vcl_color character varying NOT NULL
);


ALTER TABLE hr.vehicles OWNER TO postgres;

--
-- Name: vehicles_vcl_id_seq; Type: SEQUENCE; Schema: hr; Owner: postgres
--

ALTER TABLE hr.vehicles ALTER COLUMN vcl_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hr.vehicles_vcl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: applicants; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.applicants (apl_id, apl_cnic, apl_name, apl_discip, apl_qualif, apl_spec, apl_paddress, apl_dob, apl_dtg, apl_marital, apl_ntnlty, apl_pob, apl_taddress, apl_mobile, apl_landline, apl_gender, apl_mobile2, apl_rank, apl_status, apl_remarks, apl_appliedfor, apl_currentsal, apl_expectedsal, apl_experience, apl_expjoindt, apl_email, apl_father, apl_unt_id) FROM stdin;
\.
COPY hr.applicants (apl_id, apl_cnic, apl_name, apl_discip, apl_qualif, apl_spec, apl_paddress, apl_dob, apl_dtg, apl_marital, apl_ntnlty, apl_pob, apl_taddress, apl_mobile, apl_landline, apl_gender, apl_mobile2, apl_rank, apl_status, apl_remarks, apl_appliedfor, apl_currentsal, apl_expectedsal, apl_experience, apl_expjoindt, apl_email, apl_father, apl_unt_id) FROM '$$PATH$$/5329.dat';

--
-- Data for Name: applicjobs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.applicjobs (apj_company, apj_jobtitle, apj_repto, apj_team, apj_from, apj_to, apj_resp, apj_ach, apj_id, apj_apl_id, apj_city) FROM stdin;
\.
COPY hr.applicjobs (apj_company, apj_jobtitle, apj_repto, apj_team, apj_from, apj_to, apj_resp, apj_ach, apj_id, apj_apl_id, apj_city) FROM '$$PATH$$/5327.dat';

--
-- Data for Name: applicqualifs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.applicqualifs (apq_id, apq_type, apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_apl_id, apq_grade, apq_license, apq_spec) FROM stdin;
\.
COPY hr.applicqualifs (apq_id, apq_type, apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_apl_id, apq_grade, apq_license, apq_spec) FROM '$$PATH$$/5331.dat';

--
-- Data for Name: attendance; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.attendance (att_id, att_emp_id, att_empnamecomp, att_unt_id, att_startdt, att_enddt, att_1, att_2, att_3, att_4, att_5, att_6, att_7, att_8, att_9, att_10, att_11, att_12, att_13, att_14, att_15, att_16, att_17, att_18, att_19, att_20, att_21, att_22, att_23, att_24, att_25, att_26, att_27, att_28, att_29, att_30, att_31, att_locked1, att_locked2, att_eahreplace) FROM stdin;
\.
COPY hr.attendance (att_id, att_emp_id, att_empnamecomp, att_unt_id, att_startdt, att_enddt, att_1, att_2, att_3, att_4, att_5, att_6, att_7, att_8, att_9, att_10, att_11, att_12, att_13, att_14, att_15, att_16, att_17, att_18, att_19, att_20, att_21, att_22, att_23, att_24, att_25, att_26, att_27, att_28, att_29, att_30, att_31, att_locked1, att_locked2, att_eahreplace) FROM '$$PATH$$/5332.dat';

--
-- Data for Name: bdapps; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.bdapps (bda_brd_id, bda_apl_id) FROM stdin;
\.
COPY hr.bdapps (bda_brd_id, bda_apl_id) FROM '$$PATH$$/5334.dat';

--
-- Data for Name: bdmems; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.bdmems (bdm_id, bdm_brd_id, bdm_name, bdm_rank) FROM stdin;
\.
COPY hr.bdmems (bdm_id, bdm_brd_id, bdm_name, bdm_rank) FROM '$$PATH$$/5335.dat';

--
-- Data for Name: bnkaccounts; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.bnkaccounts (bac_id, bac_emp_id, bac_bnkname, bac_bchname, bac_bchcode, bac_acctitle, bac_accnum, bac_bchcity, bac_selforpay) FROM stdin;
\.
COPY hr.bnkaccounts (bac_id, bac_emp_id, bac_bnkname, bac_bchname, bac_bchcode, bac_acctitle, bac_accnum, bac_bchcity, bac_selforpay) FROM '$$PATH$$/5336.dat';

--
-- Data for Name: boards; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.boards (brd_id, brd_date, brd_status, brd_hir_id) FROM stdin;
\.
COPY hr.boards (brd_id, brd_date, brd_status, brd_hir_id) FROM '$$PATH$$/5338.dat';

--
-- Data for Name: contractplans; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.contractplans (cpn_id, cpn_ctr_id, cpn_startdt, cpn_enddt, cpn_hed_id) FROM stdin;
\.
COPY hr.contractplans (cpn_id, cpn_ctr_id, cpn_startdt, cpn_enddt, cpn_hed_id) FROM '$$PATH$$/5340.dat';

--
-- Data for Name: contracts; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.contracts (ctr_unt_id, ctr_num, ctr_startdt, ctr_enddt, ctr_date, ctr_jobtitle, ctr_grade, ctr_salary, ctr_hed_id, ctr_id, ctr_prob, ctr_probsal, ctr_termindt, ctr_remarks, ctr_path, ctr_type, ctr_path2) FROM stdin;
\.
COPY hr.contracts (ctr_unt_id, ctr_num, ctr_startdt, ctr_enddt, ctr_date, ctr_jobtitle, ctr_grade, ctr_salary, ctr_hed_id, ctr_id, ctr_prob, ctr_probsal, ctr_termindt, ctr_remarks, ctr_path, ctr_type, ctr_path2) FROM '$$PATH$$/5342.dat';

--
-- Data for Name: empattachments; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empattachments (eat_objtype, eat_objid, eat_type, eat_path, eat_id) FROM stdin;
\.
COPY hr.empattachments (eat_objtype, eat_objid, eat_type, eat_path, eat_id) FROM '$$PATH$$/5344.dat';

--
-- Data for Name: emps; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.emps (emp_id, emp_cnic, emp_name, emp_joindt, emp_locked, emp_rank, emp_status, emp_remarks, emp_unt_id, emp_hed_id, emp_lastdt, emp_title, emp_photodest) FROM stdin;
\.
COPY hr.emps (emp_id, emp_cnic, emp_name, emp_joindt, emp_locked, emp_rank, emp_status, emp_remarks, emp_unt_id, emp_hed_id, emp_lastdt, emp_title, emp_photodest) FROM '$$PATH$$/5348.dat';

--
-- Data for Name: empsexta; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empsexta (empexta_emp_id, emp_discip, emp_qualif, emp_spec, emp_paddress, emp_dob, emp_marital, emp_ntnlty, emp_pob, emp_taddress, emp_mobile, emp_landline, emp_gender, emp_mobile2, emp_email, emp_father) FROM stdin;
\.
COPY hr.empsexta (empexta_emp_id, emp_discip, emp_qualif, emp_spec, emp_paddress, emp_dob, emp_marital, emp_ntnlty, emp_pob, emp_taddress, emp_mobile, emp_landline, emp_gender, emp_mobile2, emp_email, emp_father) FROM '$$PATH$$/5349.dat';

--
-- Data for Name: empsextb; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empsextb (empextb_emp_id, emp_nokname, emp_nokrelation, emp_nokcnic, emp_emername, emp_emerrelation, emp_emermobile, emp_idmark, emp_height, emp_caste, emp_religion, emp_sect, emp_police, emp_political) FROM stdin;
\.
COPY hr.empsextb (empextb_emp_id, emp_nokname, emp_nokrelation, emp_nokcnic, emp_emername, emp_emerrelation, emp_emermobile, emp_idmark, emp_height, emp_caste, emp_religion, emp_sect, emp_police, emp_political) FROM '$$PATH$$/5350.dat';

--
-- Data for Name: empsextc; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empsextc (empextc_emp_id, emp_cnum, emp_cissuedt, emp_cexpdt, emp_secclear) FROM stdin;
\.
COPY hr.empsextc (empextc_emp_id, emp_cnum, emp_cissuedt, emp_cexpdt, emp_secclear) FROM '$$PATH$$/5351.dat';

--
-- Data for Name: hirings; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.hirings (hir_id, hir_hhd_id, hir_reference, hir_date, hir_jobtitle, hir_grade, hir_salarymin, hir_salarymax) FROM stdin;
\.
COPY hr.hirings (hir_id, hir_hhd_id, hir_reference, hir_date, hir_jobtitle, hir_grade, hir_salarymin, hir_salarymax) FROM '$$PATH$$/5352.dat';

--
-- Data for Name: hrheads; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.hrheads (hhd_id, hhd_dpt_id, hhd_name) FROM stdin;
\.
COPY hr.hrheads (hhd_id, hhd_dpt_id, hhd_name) FROM '$$PATH$$/5353.dat';

--
-- Data for Name: jobs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.jobs (job_company, job_jobtitle, job_repto, job_team, job_from, job_to, job_resp, job_ach, job_emp_id, job_id, job_city) FROM stdin;
\.
COPY hr.jobs (job_company, job_jobtitle, job_repto, job_team, job_from, job_to, job_resp, job_ach, job_emp_id, job_id, job_city) FROM '$$PATH$$/5354.dat';

--
-- Data for Name: qualifs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.qualifs (qlf_id, qlf_type, qlf_level, qlf_name, qlf_inst, qlf_duration, qlf_unit, qlf_enddt, qlf_emp_id, qlf_grade, qlf_license, qlf_spec) FROM stdin;
\.
COPY hr.qualifs (qlf_id, qlf_type, qlf_level, qlf_name, qlf_inst, qlf_duration, qlf_unit, qlf_enddt, qlf_emp_id, qlf_grade, qlf_license, qlf_spec) FROM '$$PATH$$/5346.dat';

--
-- Data for Name: salreqs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.salreqs (srq_id, srq_emp_id, srq_empnamecomp, srq_unt_id, srq_hed_id, srq_effunt_id, srq_effhed_id, srq_month, srq_unpaiddays, srq_salary, srq_status, srq_remarks, srq_releasedtg, srq_closedtg, srq_fulfilment, srq_ctrsalary, srq_grosalary, srq_netsalary, srq_bnkaccdetail, srq_bnkacctitle, srq_contracts, srq_checked, srq_arrears, srq_dues, srq_overwork, srq_underwork, srq_loaned, srq_withheld, srq_award, srq_penalty, srq_paidalready, srq_paidholidays, srq_remarks2, srq_sudohed) FROM stdin;
\.
COPY hr.salreqs (srq_id, srq_emp_id, srq_empnamecomp, srq_unt_id, srq_hed_id, srq_effunt_id, srq_effhed_id, srq_month, srq_unpaiddays, srq_salary, srq_status, srq_remarks, srq_releasedtg, srq_closedtg, srq_fulfilment, srq_ctrsalary, srq_grosalary, srq_netsalary, srq_bnkaccdetail, srq_bnkacctitle, srq_contracts, srq_checked, srq_arrears, srq_dues, srq_overwork, srq_underwork, srq_loaned, srq_withheld, srq_award, srq_penalty, srq_paidalready, srq_paidholidays, srq_remarks2, srq_sudohed) FROM '$$PATH$$/5358.dat';

--
-- Data for Name: vehicles; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.vehicles (vcl_id, vcl_emp_id, vcl_type, vcl_maker, vcl_variant, vcl_year, vcl_regis, vcl_color) FROM stdin;
\.
COPY hr.vehicles (vcl_id, vcl_emp_id, vcl_type, vcl_maker, vcl_variant, vcl_year, vcl_regis, vcl_color) FROM '$$PATH$$/5360.dat';

--
-- Name: appjobs_apj_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.appjobs_apj_id_seq', 8, true);


--
-- Name: applicants_column1_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.applicants_column1_seq', 95, true);


--
-- Name: attendance_att_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.attendance_att_id_seq', 4839, true);


--
-- Name: bnkaccounts_bac_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.bnkaccounts_bac_id_seq', 416, true);


--
-- Name: boards_brd_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.boards_brd_id_seq', 1, false);


--
-- Name: contractplans_cpn_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.contractplans_cpn_id_seq', 3757, true);


--
-- Name: contracts_ctr_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.contracts_ctr_id_seq', 690, true);


--
-- Name: empattachments_eat_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.empattachments_eat_id_seq', 396, true);


--
-- Name: empqualifs_qlf_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.empqualifs_qlf_id_seq', 1225, true);


--
-- Name: jobs_job_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.jobs_job_id_seq', 402, true);


--
-- Name: qualifs_qlf_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.qualifs_qlf_id_seq', 94, true);


--
-- Name: request_req_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.request_req_id_seq', 57, true);


--
-- Name: salreqs_srq_id_seq1; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.salreqs_srq_id_seq1', 3905, true);


--
-- Name: vehicles_vcl_id_seq; Type: SEQUENCE SET; Schema: hr; Owner: postgres
--

SELECT pg_catalog.setval('hr.vehicles_vcl_id_seq', 291, true);


--
-- Name: applicants applicants_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.applicants
    ADD CONSTRAINT applicants_pk PRIMARY KEY (apl_id);


--
-- Name: applicants applicants_un; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.applicants
    ADD CONSTRAINT applicants_un UNIQUE (apl_cnic, apl_dtg);


--
-- Name: applicjobs applicjobs_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.applicjobs
    ADD CONSTRAINT applicjobs_pk PRIMARY KEY (apj_id);


--
-- Name: applicqualifs applicqualifs_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.applicqualifs
    ADD CONSTRAINT applicqualifs_pk PRIMARY KEY (apq_id);


--
-- Name: attendance attendance_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.attendance
    ADD CONSTRAINT attendance_pk PRIMARY KEY (att_id);


--
-- Name: attendance attendance_un; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.attendance
    ADD CONSTRAINT attendance_un UNIQUE (att_emp_id, att_startdt);


--
-- Name: bdapps bdapps_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.bdapps
    ADD CONSTRAINT bdapps_pk PRIMARY KEY (bda_brd_id, bda_apl_id);


--
-- Name: bdmems bdmems_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.bdmems
    ADD CONSTRAINT bdmems_pk PRIMARY KEY (bdm_id, bdm_brd_id);


--
-- Name: bnkaccounts bnkaccounts_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.bnkaccounts
    ADD CONSTRAINT bnkaccounts_pk PRIMARY KEY (bac_id);


--
-- Name: boards boards_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.boards
    ADD CONSTRAINT boards_pk PRIMARY KEY (brd_id);


--
-- Name: contractplans contractplans_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.contractplans
    ADD CONSTRAINT contractplans_pk PRIMARY KEY (cpn_id);


--
-- Name: contracts contracts_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.contracts
    ADD CONSTRAINT contracts_pk PRIMARY KEY (ctr_id);


--
-- Name: contracts contracts_un; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.contracts
    ADD CONSTRAINT contracts_un UNIQUE (ctr_num, ctr_startdt);


--
-- Name: empattachments empattachments_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empattachments
    ADD CONSTRAINT empattachments_pk PRIMARY KEY (eat_id);


--
-- Name: emps emps_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.emps
    ADD CONSTRAINT emps_pk PRIMARY KEY (emp_id);


--
-- Name: emps emps_un; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.emps
    ADD CONSTRAINT emps_un UNIQUE (emp_cnic, emp_joindt);


--
-- Name: empsexta empsexta_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empsexta
    ADD CONSTRAINT empsexta_pk PRIMARY KEY (empexta_emp_id);


--
-- Name: empsextb empsextb_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empsextb
    ADD CONSTRAINT empsextb_pk PRIMARY KEY (empextb_emp_id);


--
-- Name: empsextc empsextc_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empsextc
    ADD CONSTRAINT empsextc_pk PRIMARY KEY (empextc_emp_id);


--
-- Name: hirings hirings_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.hirings
    ADD CONSTRAINT hirings_pk PRIMARY KEY (hir_id);


--
-- Name: hrheads hrheads_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.hrheads
    ADD CONSTRAINT hrheads_pk PRIMARY KEY (hhd_id);


--
-- Name: jobs jobs_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.jobs
    ADD CONSTRAINT jobs_pk PRIMARY KEY (job_id);


--
-- Name: qualifs qualifs_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.qualifs
    ADD CONSTRAINT qualifs_pk PRIMARY KEY (qlf_id);


--
-- Name: salreqs salreqs_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.salreqs
    ADD CONSTRAINT salreqs_pk PRIMARY KEY (srq_id);


--
-- Name: vehicles vehicles_pk; Type: CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.vehicles
    ADD CONSTRAINT vehicles_pk PRIMARY KEY (vcl_id);


--
-- Name: applicjobs applicjobs_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.applicjobs
    ADD CONSTRAINT applicjobs_fk FOREIGN KEY (apj_apl_id) REFERENCES hr.applicants(apl_id);


--
-- Name: applicqualifs applicqualifs_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.applicqualifs
    ADD CONSTRAINT applicqualifs_fk FOREIGN KEY (apq_apl_id) REFERENCES hr.applicants(apl_id);


--
-- Name: attendance attendance_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.attendance
    ADD CONSTRAINT attendance_fk FOREIGN KEY (att_emp_id) REFERENCES hr.emps(emp_id);


--
-- Name: bnkaccounts bnkaccounts_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.bnkaccounts
    ADD CONSTRAINT bnkaccounts_fk FOREIGN KEY (bac_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: contractplans contractplans_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.contractplans
    ADD CONSTRAINT contractplans_fk FOREIGN KEY (cpn_ctr_id) REFERENCES hr.contracts(ctr_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: contracts contracts_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.contracts
    ADD CONSTRAINT contracts_fk FOREIGN KEY (ctr_num) REFERENCES hr.emps(emp_id);


--
-- Name: empattachments empattachments_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empattachments
    ADD CONSTRAINT empattachments_fk FOREIGN KEY (eat_objid) REFERENCES hr.emps(emp_id);


--
-- Name: empsexta empsexta_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empsexta
    ADD CONSTRAINT empsexta_fk FOREIGN KEY (empexta_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: empsextb empsextb_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empsextb
    ADD CONSTRAINT empsextb_fk FOREIGN KEY (empextb_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: empsextc empsextc_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.empsextc
    ADD CONSTRAINT empsextc_fk FOREIGN KEY (empextc_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: jobs jobs_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.jobs
    ADD CONSTRAINT jobs_fk FOREIGN KEY (job_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: qualifs qualifs_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.qualifs
    ADD CONSTRAINT qualifs_fk FOREIGN KEY (qlf_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: salreqs salreqs_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.salreqs
    ADD CONSTRAINT salreqs_fk FOREIGN KEY (srq_emp_id) REFERENCES hr.emps(emp_id);


--
-- Name: vehicles vehicles_fk; Type: FK CONSTRAINT; Schema: hr; Owner: postgres
--

ALTER TABLE ONLY hr.vehicles
    ADD CONSTRAINT vehicles_fk FOREIGN KEY (vcl_emp_id) REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

