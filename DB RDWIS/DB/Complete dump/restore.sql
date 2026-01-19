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

--
-- Name: cen; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA cen;


ALTER SCHEMA cen OWNER TO postgres;

--
-- Name: fin; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA fin;


ALTER SCHEMA fin OWNER TO postgres;

--
-- Name: frm; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA frm;


ALTER SCHEMA frm OWNER TO postgres;

--
-- Name: hr; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA hr;


ALTER SCHEMA hr OWNER TO postgres;

--
-- Name: SCHEMA hr; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA hr IS 'standard public schema';


--
-- Name: ina; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA ina;


ALTER SCHEMA ina OWNER TO postgres;

--
-- Name: prj; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA prj;


ALTER SCHEMA prj OWNER TO postgres;

--
-- Name: pur; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA pur;


ALTER SCHEMA pur OWNER TO postgres;

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
-- Name: commitments; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.commitments (
    cmt_id integer NOT NULL,
    cmt_docid integer NOT NULL,
    cmt_type character varying(2) NOT NULL,
    cmt_date date NOT NULL,
    cmt_amount numeric NOT NULL,
    cmt_status character varying NOT NULL,
    cmt_effhed_id integer NOT NULL,
    cmt_effunt_id integer NOT NULL,
    cmt_hed_id integer,
    cmt_unt_id integer NOT NULL,
    cmt_sudohed character varying(7)
);


ALTER TABLE fin.commitments OWNER TO postgres;

--
-- Name: commitments_cmt_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.commitments ALTER COLUMN cmt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.commitments_cmt_id_seq
    START WITH 1374
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: contractsverif; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.contractsverif (
    cvf_ctr_id integer NOT NULL,
    cvf_verif boolean DEFAULT false NOT NULL,
    cvf_dtg timestamp(0) without time zone
);


ALTER TABLE fin.contractsverif OWNER TO postgres;

--
-- Name: TABLE contractsverif; Type: COMMENT; Schema: fin; Owner: postgres
--

COMMENT ON TABLE fin.contractsverif IS 'Verification of contractas by finance department';


--
-- Name: empeffheads; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.empeffheads (
    eeh_emp_id character varying NOT NULL,
    eeh_emphed_id integer,
    eeh_dtg timestamp(0) without time zone,
    eeh_status character varying DEFAULT 'Open'::character varying NOT NULL,
    eeh_remarks character varying,
    eeh_sudohed character varying(7)
);


ALTER TABLE fin.empeffheads OWNER TO postgres;

--
-- Name: loanadjustments; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.loanadjustments (
    lad_string character varying NOT NULL,
    lad_amount numeric NOT NULL,
    lad_remarks character varying NOT NULL,
    lad_id integer NOT NULL,
    lad_dtg timestamp without time zone NOT NULL,
    lad_from integer NOT NULL,
    lad_to integer NOT NULL
);


ALTER TABLE fin.loanadjustments OWNER TO postgres;

--
-- Name: loanadjustments_lad_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.loanadjustments ALTER COLUMN lad_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.loanadjustments_lad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: loanremarks; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.loanremarks (
    lrm_string character varying NOT NULL,
    lrm_remarks character varying
);


ALTER TABLE fin.loanremarks OWNER TO postgres;

--
-- Name: msncosts; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.msncosts (
    mct_prj_id integer NOT NULL,
    mct_hed_id integer NOT NULL,
    mct_msn_id integer NOT NULL,
    mct_cost integer DEFAULT 0 NOT NULL,
    mct_msn_idd integer NOT NULL
);


ALTER TABLE fin.msncosts OWNER TO postgres;

--
-- Name: salorders; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.salorders (
    sor_id integer NOT NULL,
    sor_hed_id integer,
    sor_releasedtg timestamp without time zone,
    sor_status character varying NOT NULL,
    sor_remarks character varying,
    sor_srq_id integer NOT NULL,
    sor_closedtg timestamp without time zone,
    sor_unt_id integer NOT NULL,
    sor_month date NOT NULL,
    sor_netsalary integer NOT NULL,
    sor_salary integer NOT NULL,
    sor_emp_id character varying NOT NULL,
    sor_effhed_id integer NOT NULL,
    sor_empnamecomp character varying NOT NULL,
    sor_bnkacctitle character varying NOT NULL,
    sor_effunt_id integer NOT NULL,
    sor_bnkaccdetail character varying NOT NULL,
    sor_ctrsalary integer NOT NULL,
    sor_checked boolean DEFAULT false NOT NULL,
    sor_contracts character varying NOT NULL,
    sor_noloan boolean DEFAULT false NOT NULL,
    sor_transtype smallint NOT NULL,
    sor_sudohed character varying(7),
    sor_remarks2 character varying,
    sor_type character varying(5) NOT NULL,
    sor_grosalary integer NOT NULL,
    sor_arrears integer NOT NULL,
    sor_dues integer NOT NULL,
    sor_overwork integer NOT NULL,
    sor_underwork integer NOT NULL,
    sor_loaned integer NOT NULL,
    sor_withheld integer NOT NULL,
    sor_award integer NOT NULL,
    sor_penalty integer NOT NULL,
    sor_paidalready integer NOT NULL
);


ALTER TABLE fin.salorders OWNER TO postgres;

--
-- Name: salorders_shd; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.salorders_shd (
    sod_sor_id integer NOT NULL,
    sod_type character varying(5),
    sod_subhead character varying NOT NULL,
    sod_ratio numeric(13,12) NOT NULL
);


ALTER TABLE fin.salorders_shd OWNER TO postgres;

--
-- Name: salorders_sor_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.salorders ALTER COLUMN sor_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.salorders_sor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sharesalloc; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.sharesalloc (
    sha_hed_id integer NOT NULL,
    sha_ficmt_id integer NOT NULL,
    sha_focmt_id integer,
    sha_transtype smallint,
    sha_id integer NOT NULL,
    sha_cf numeric,
    sha_pcc numeric,
    sha_prj numeric,
    sha_prj_sal numeric,
    sha_prj_pur numeric
);


ALTER TABLE fin.sharesalloc OWNER TO postgres;

--
-- Name: sharesalloc_sha_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.sharesalloc ALTER COLUMN sha_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.sharesalloc_sha_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sharesinstall; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.sharesinstall (
    shi_hed_id integer NOT NULL,
    shi_fitrn_id integer NOT NULL,
    shi_fotrn_id integer,
    shi_id integer NOT NULL,
    shi_cf numeric,
    shi_pcc numeric,
    shi_msn_idd integer,
    shi_prj numeric
);


ALTER TABLE fin.sharesinstall OWNER TO postgres;

--
-- Name: sharesinstall_shi_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.sharesinstall ALTER COLUMN shi_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.sharesinstall_shi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: subheads; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.subheads (
    sbh_hed_id integer NOT NULL,
    sbh_name character varying NOT NULL,
    sbh_alloc numeric NOT NULL,
    sbh_id integer NOT NULL
);


ALTER TABLE fin.subheads OWNER TO postgres;

--
-- Name: subheads_sbh_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.subheads ALTER COLUMN sbh_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.subheads_sbh_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: subheads_zzz; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.subheads_zzz (
    sbh_hed_id integer NOT NULL,
    sbh_name character varying NOT NULL,
    sbh_alloc numeric NOT NULL
);


ALTER TABLE fin.subheads_zzz OWNER TO postgres;

--
-- Name: transactions; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.transactions (
    trn_id integer NOT NULL,
    trn_cmt_id integer NOT NULL,
    trn_date date NOT NULL,
    trn_amount1 numeric NOT NULL,
    trn_balance numeric NOT NULL,
    trn_seq smallint NOT NULL,
    trn_tax1 numeric NOT NULL,
    trn_amount2 numeric NOT NULL,
    trn_transtype smallint NOT NULL,
    trn_noloan boolean DEFAULT false NOT NULL
);


ALTER TABLE fin.transactions OWNER TO postgres;

--
-- Name: transactions_trn_id_seq; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.transactions ALTER COLUMN trn_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.transactions_trn_id_seq
    START WITH 1378
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: transfers; Type: TABLE; Schema: fin; Owner: postgres
--

CREATE TABLE fin.transfers (
    trf_id integer NOT NULL,
    trf_date date NOT NULL,
    trf_type character varying(3) NOT NULL,
    trf_title character varying NOT NULL,
    trf_amount integer NOT NULL,
    trf_fromhed integer NOT NULL,
    trf_fromunt integer NOT NULL,
    trf_tohed integer NOT NULL,
    trf_tount integer NOT NULL,
    trf_status character varying NOT NULL
);


ALTER TABLE fin.transfers OWNER TO postgres;

--
-- Name: transfers_trf_id_seq1; Type: SEQUENCE; Schema: fin; Owner: postgres
--

ALTER TABLE fin.transfers ALTER COLUMN trf_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME fin.transfers_trf_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: facils; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.facils (
    fcl_xfrm_id integer NOT NULL,
    fcl_facil character varying(255) NOT NULL
);


ALTER TABLE frm.facils OWNER TO postgres;

--
-- Name: firmz; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.firmz (
    frm_id integer NOT NULL,
    frm_name character varying NOT NULL,
    frm_entity character varying(255),
    frm_type character varying(255),
    frm_group character varying(255),
    frm_emp integer,
    frm_points integer,
    frm_black boolean,
    frm_notes text,
    frm_id_old integer,
    frm_ntn character varying,
    frm_gst character varying
);


ALTER TABLE frm.firmz OWNER TO postgres;

--
-- Name: firmz_1_frm_id_seq; Type: SEQUENCE; Schema: frm; Owner: postgres
--

ALTER TABLE frm.firmz ALTER COLUMN frm_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME frm.firmz_1_frm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: info; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.info (
    inf_xmsc_id integer NOT NULL,
    inf_xmsc_entity character varying(255) NOT NULL,
    inf_type character varying(255) NOT NULL,
    inf_value character varying(255) NOT NULL
);


ALTER TABLE frm.info OWNER TO postgres;

--
-- Name: offices; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.offices (
    off_id integer NOT NULL,
    off_entity character varying(255),
    off_xfrm_id integer,
    off_name character varying(255),
    off_type character varying(255),
    off_address text,
    off_city character varying(255)
);


ALTER TABLE frm.offices OWNER TO postgres;

--
-- Name: offices_1_off_id_seq; Type: SEQUENCE; Schema: frm; Owner: postgres
--

ALTER TABLE frm.offices ALTER COLUMN off_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME frm.offices_1_off_id_seq
    START WITH 56
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: persons; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.persons (
    per_id integer NOT NULL,
    per_entity character varying(255),
    per_xfrm_id integer,
    per_title character varying(255),
    per_name character varying(50),
    per_desig character varying(255),
    per_dept character varying(255),
    per_exprt character varying(255)
);


ALTER TABLE frm.persons OWNER TO postgres;

--
-- Name: persons_1_per_id_seq; Type: SEQUENCE; Schema: frm; Owner: postgres
--

ALTER TABLE frm.persons ALTER COLUMN per_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME frm.persons_1_per_id_seq
    START WITH 27
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: projects; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.projects (
    prj_xfrm_id integer NOT NULL,
    prj_name character varying(255) NOT NULL,
    prj_scope character varying(255),
    prj_awarddt timestamp with time zone,
    prj_status character varying(255),
    prj_compdt timestamp with time zone,
    prj_tech character varying(255),
    prj_cost integer
);


ALTER TABLE frm.projects OWNER TO postgres;

--
-- Name: specs; Type: TABLE; Schema: frm; Owner: postgres
--

CREATE TABLE frm.specs (
    spc_xfrm_id integer NOT NULL,
    spc_spec character varying(255) NOT NULL
);


ALTER TABLE frm.specs OWNER TO postgres;

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
-- Name: inaattachments; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.inaattachments (
    iat_objtype character varying NOT NULL,
    iat_objid integer NOT NULL,
    iat_type character varying NOT NULL,
    iat_path character varying,
    iat_id integer NOT NULL
);


ALTER TABLE ina.inaattachments OWNER TO postgres;

--
-- Name: inaattachments_iat_id_seq; Type: SEQUENCE; Schema: ina; Owner: postgres
--

ALTER TABLE ina.inaattachments ALTER COLUMN iat_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME ina.inaattachments_iat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: invatcomps; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.invatcomps (
    iac_id integer NOT NULL,
    iac_ias_id integer NOT NULL,
    iac_qty numeric NOT NULL,
    iac_qtyunit character varying NOT NULL,
    iac_parent_id integer,
    iac_isparent boolean DEFAULT false NOT NULL,
    iac_isassembly boolean DEFAULT false NOT NULL,
    iac_location character varying,
    iac_group character varying,
    iac_person character varying,
    iac_dtg timestamp without time zone NOT NULL,
    iac_dispdate date,
    iac_dispdtg timestamp without time zone,
    iac_status character varying NOT NULL,
    iac_remarks character varying,
    iac_details text,
    iac_shared boolean DEFAULT false NOT NULL
);


ALTER TABLE ina.invatcomps OWNER TO postgres;

--
-- Name: invatcomps_iac_id_seq; Type: SEQUENCE; Schema: ina; Owner: postgres
--

ALTER TABLE ina.invatcomps ALTER COLUMN iac_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME ina.invatcomps_iac_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: invatlocs; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.invatlocs (
    ial_id integer NOT NULL,
    ial_ias_id integer NOT NULL,
    ial_date date NOT NULL,
    ial_unt_id integer NOT NULL,
    ial_location character varying NOT NULL,
    ial_custgroup character varying NOT NULL,
    ial_custperson character varying NOT NULL
);


ALTER TABLE ina.invatlocs OWNER TO postgres;

--
-- Name: invatlocs_ial_id_seq; Type: SEQUENCE; Schema: ina; Owner: postgres
--

ALTER TABLE ina.invatlocs ALTER COLUMN ial_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME ina.invatlocs_ial_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: invats; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.invats (
    ias_id integer NOT NULL,
    ias_pcs_id integer,
    ias_pci_id integer,
    ias_desc text NOT NULL,
    ias_details text,
    ias_spec_id integer,
    ias_qty numeric NOT NULL,
    ias_qtyunit character varying NOT NULL,
    ias_unt_id integer NOT NULL,
    ias_prj_id integer,
    ias_effhed_id integer,
    ias_chargedate date NOT NULL,
    ias_price numeric,
    ias_type smallint NOT NULL,
    ias_subtype character varying,
    ias_dtg timestamp without time zone,
    ias_type2 smallint
);


ALTER TABLE ina.invats OWNER TO postgres;

--
-- Name: invats_ias_id_seq; Type: SEQUENCE; Schema: ina; Owner: postgres
--

ALTER TABLE ina.invats ALTER COLUMN ias_id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME ina.invats_ias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: invatspecs; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.invatspecs (
    iap_ias_id integer NOT NULL,
    iap_specname character varying NOT NULL,
    iap_specvalue character varying NOT NULL
);


ALTER TABLE ina.invatspecs OWNER TO postgres;

--
-- Name: invenitems; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.invenitems (
    ini_id integer NOT NULL,
    ini_inv_id integer NOT NULL,
    ini_qty numeric NOT NULL,
    ini_qtyunit character varying NOT NULL,
    ini_parent_id integer,
    ini_isparent boolean NOT NULL,
    ini_isassembly boolean NOT NULL,
    ini_location character varying,
    ini_group character varying,
    ini_person character varying,
    ini_dtg timestamp without time zone NOT NULL,
    ini_dispdate date,
    ini_dispdtg timestamp without time zone,
    ini_status character varying NOT NULL,
    ini_remarks character varying
);


ALTER TABLE ina.invenitems OWNER TO postgres;

--
-- Name: inventory; Type: TABLE; Schema: ina; Owner: postgres
--

CREATE TABLE ina.inventory (
    inv_id integer NOT NULL,
    inv_pcs_id integer,
    inv_pci_id integer,
    inv_desc character varying NOT NULL,
    inv_details character varying,
    inv_spec_id integer,
    inv_qty numeric NOT NULL,
    inv_qtyunit character varying NOT NULL,
    inv_unt_id integer NOT NULL,
    inv_chargedate date NOT NULL,
    inv_price numeric,
    inv_type character varying NOT NULL,
    inv_subtype character varying NOT NULL,
    inv_dtg timestamp without time zone,
    inv_prj_id integer
);


ALTER TABLE ina.inventory OWNER TO postgres;

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
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: project_activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project_activities (
    pja_id bigint NOT NULL,
    pja_prj_id bigint NOT NULL,
    pja_action character varying(255) NOT NULL,
    pja_details text NOT NULL,
    pja_user character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.project_activities OWNER TO postgres;

--
-- Name: project_activities_pja_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_activities_pja_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_activities_pja_id_seq OWNER TO postgres;

--
-- Name: project_activities_pja_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_activities_pja_id_seq OWNED BY public.project_activities.pja_id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: purcases; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purcases (
    pcs_id integer NOT NULL,
    pcs_date date NOT NULL,
    pcs_remarks character varying,
    pcs_status character varying NOT NULL,
    pcs_unt_id integer NOT NULL,
    pcs_hed_id integer,
    pcs_purreqs character varying,
    pcs_releasedtg timestamp(0) without time zone,
    pcs_effhed_id integer NOT NULL,
    pcs_intprice numeric DEFAULT 0,
    pcs_inttax numeric DEFAULT 0,
    pcs_midprice numeric DEFAULT 0 NOT NULL,
    pcs_midtax numeric DEFAULT 0 NOT NULL,
    pcs_price numeric DEFAULT 0 NOT NULL,
    pcs_title character varying NOT NULL,
    pcs_frm_id integer,
    pcs_type character varying(5) NOT NULL,
    pcs_effunt_id integer NOT NULL,
    pcs_intunt_id integer NOT NULL,
    pcs_transtype smallint NOT NULL,
    pcs_noloan boolean DEFAULT false NOT NULL,
    pcs_sudohed character varying(7),
    pcs_minute smallint,
    pcs_recomm character varying,
    pcs_quotetype smallint DEFAULT 1 NOT NULL,
    pcs_approvedtg timestamp(0) without time zone,
    pcs_closedtg timestamp(0) without time zone
);


ALTER TABLE pur.purcases OWNER TO postgres;

--
-- Name: compstat_cst_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purcases ALTER COLUMN pcs_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.compstat_cst_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: noquotes; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.noquotes (
    nqt_id integer NOT NULL,
    nqt_pcs_id integer NOT NULL,
    nqt_frm_id integer NOT NULL
);


ALTER TABLE pur.noquotes OWNER TO postgres;

--
-- Name: noquotes_nqt_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.noquotes ALTER COLUMN nqt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.noquotes_nqt_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purattachments; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purattachments (
    pat_objtype character varying NOT NULL,
    pat_objid integer NOT NULL,
    pat_type character varying NOT NULL,
    pat_path character varying,
    pat_id integer NOT NULL
);


ALTER TABLE pur.purattachments OWNER TO postgres;

--
-- Name: purattachments_pat_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purattachments ALTER COLUMN pat_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.purattachments_pat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purcaseitems; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purcaseitems (
    pci_pri_id integer,
    pci_serial smallint NOT NULL,
    pci_desc text NOT NULL,
    pci_estprice numeric,
    pci_qty numeric NOT NULL,
    pci_qtyunit character varying NOT NULL,
    pci_id integer NOT NULL,
    pci_pcs_id integer NOT NULL,
    pci_price numeric,
    pci_fulfilment numeric,
    pci_type smallint NOT NULL,
    pci_subtype character varying NOT NULL,
    pci_category smallint,
    pci_type2 smallint,
    pci_subhead character varying
);


ALTER TABLE pur.purcaseitems OWNER TO postgres;

--
-- Name: purcaseitems_pci_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purcaseitems ALTER COLUMN pci_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.purcaseitems_pci_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purcaseminuterefs; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purcaseminuterefs (
    pmr_pcm_id integer NOT NULL,
    pmr_min smallint NOT NULL,
    pmr_title character varying NOT NULL,
    pmr_date date,
    pmr_from character varying,
    pmr_flag character varying,
    pmr_ref character varying,
    pmr_encl character varying
);


ALTER TABLE pur.purcaseminuterefs OWNER TO postgres;

--
-- Name: purcaseminutes; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purcaseminutes (
    pcm_id integer NOT NULL,
    pcm_texta text,
    pcm_textb text,
    pcm_textc text,
    pcm_textd text,
    pcm_texte text,
    pcm_lwoamount integer,
    pcm_minute smallint NOT NULL,
    pcm_purcases character varying NOT NULL,
    pcm_eqdue numeric DEFAULT 0,
    pcm_hrdue numeric DEFAULT 0,
    pcm_msdue numeric DEFAULT 0,
    pcm_date date
);


ALTER TABLE pur.purcaseminutes OWNER TO postgres;

--
-- Name: purcaseminutes_pcm_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purcaseminutes ALTER COLUMN pcm_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.purcaseminutes_pcm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purcases_shd; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purcases_shd (
    pcd_pcs_id integer NOT NULL,
    pcd_subhead character varying NOT NULL,
    pcd_ratio numeric(13,12) NOT NULL,
    pcd_type character varying(5)
);


ALTER TABLE pur.purcases_shd OWNER TO postgres;

--
-- Name: purreqitems; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purreqitems (
    pri_prq_id integer NOT NULL,
    pri_serial smallint NOT NULL,
    pri_desc text NOT NULL,
    pri_price numeric NOT NULL,
    pri_qty numeric NOT NULL,
    pri_qtyunit character varying NOT NULL,
    pri_type smallint NOT NULL,
    pri_category smallint NOT NULL,
    pri_id integer NOT NULL,
    pri_fulfilment numeric,
    pri_appqty numeric,
    pri_remarks character varying,
    pri_subtype character varying NOT NULL
);


ALTER TABLE pur.purreqitems OWNER TO postgres;

--
-- Name: puritems_pri_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purreqitems ALTER COLUMN pri_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.puritems_pri_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purreceipts; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purreceipts (
    prt_id integer NOT NULL,
    prt_date date,
    prt_unt_id integer NOT NULL,
    prt_prj_id integer,
    prt_status character varying NOT NULL,
    prt_pcs_id integer NOT NULL,
    prt_dtg timestamp(0) without time zone
);


ALTER TABLE pur.purreceipts OWNER TO postgres;

--
-- Name: purreceipt_prt_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purreceipts ALTER COLUMN prt_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.purreceipt_prt_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purreceiptitems; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purreceiptitems (
    pti_id integer NOT NULL,
    pti_prt_id integer NOT NULL,
    pti_desc text NOT NULL,
    pti_qty numeric NOT NULL,
    pti_qtyunit character varying NOT NULL,
    pti_pci_id integer NOT NULL,
    pti_pri_id integer,
    pti_serial smallint NOT NULL
);


ALTER TABLE pur.purreceiptitems OWNER TO postgres;

--
-- Name: purreceiptitem_pti_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purreceiptitems ALTER COLUMN pti_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.purreceiptitem_pti_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purreqs; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.purreqs (
    prq_id integer NOT NULL,
    prq_date date NOT NULL,
    prq_unt_id integer NOT NULL,
    prq_hed_id integer NOT NULL,
    prq_status character varying NOT NULL,
    prq_fulfilled boolean DEFAULT false NOT NULL,
    prq_dtg timestamp(0) without time zone,
    prq_effhed_id integer NOT NULL,
    prq_appeffhed_id integer,
    prq_intunt_id integer NOT NULL,
    prq_desc character varying NOT NULL,
    prq_minute smallint NOT NULL
);


ALTER TABLE pur.purreqs OWNER TO postgres;

--
-- Name: purreq_prq_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.purreqs ALTER COLUMN prq_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.purreq_prq_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: quoteitems; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.quoteitems (
    qti_qte_id integer NOT NULL,
    qti_serial smallint NOT NULL,
    qti_desc text NOT NULL,
    qti_price numeric DEFAULT 0 NOT NULL,
    qti_qty numeric NOT NULL,
    qti_qtyunit character varying NOT NULL,
    qti_id integer NOT NULL,
    qti_pcsdesc text NOT NULL,
    qti_pci_id integer
);


ALTER TABLE pur.quoteitems OWNER TO postgres;

--
-- Name: quoteitems_qti_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.quoteitems ALTER COLUMN qti_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.quoteitems_qti_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: quotes; Type: TABLE; Schema: pur; Owner: postgres
--

CREATE TABLE pur.quotes (
    qte_date date DEFAULT CURRENT_DATE NOT NULL,
    qte_firmname character varying,
    qte_pcs_id integer NOT NULL,
    qte_num character varying DEFAULT '<Add Ref>'::character varying NOT NULL,
    qte_id integer NOT NULL,
    qte_price numeric DEFAULT 0,
    qte_frm_id integer DEFAULT 0 NOT NULL,
    qte_techaccept boolean DEFAULT true NOT NULL,
    qte_recomm boolean DEFAULT false NOT NULL,
    qte_midprice numeric DEFAULT 0,
    qte_midtax numeric DEFAULT 0,
    qte_intprice numeric DEFAULT 0,
    qte_inttax numeric DEFAULT 0,
    qte_quotetype smallint
);


ALTER TABLE pur.quotes OWNER TO postgres;

--
-- Name: quotes_qte_id_seq; Type: SEQUENCE; Schema: pur; Owner: postgres
--

ALTER TABLE pur.quotes ALTER COLUMN qte_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME pur.quotes_qte_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: project_activities pja_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_activities ALTER COLUMN pja_id SET DEFAULT nextval('public.project_activities_pja_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: audattachments; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.audattachments (aat_objtype, aat_objid, aat_type, aat_path, aat_id) FROM stdin;
\.
COPY aud.audattachments (aat_objtype, aat_objid, aat_type, aat_path, aat_id) FROM '$$PATH$$/5642.dat';

--
-- Data for Name: busdata; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.busdata (bdt_id, bdt_username, bdt_dtg, bdt_action, bdt_formname, bdt_controlname, bdt_fieldname, bdt_recordid, btd_oldvalue, btd_newvalue, bdt_empname, bdt_empdesig) FROM stdin;
\.
COPY aud.busdata (bdt_id, bdt_username, bdt_dtg, bdt_action, bdt_formname, bdt_controlname, bdt_fieldname, bdt_recordid, btd_oldvalue, btd_newvalue, bdt_empname, bdt_empdesig) FROM '$$PATH$$/5644.dat';

--
-- Data for Name: revcomps; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.revcomps (rvc_id, rvc_rev_id, rvc_table, rvc_detail, rvc_rowid, rvc_action, rvc_type) FROM stdin;
\.
COPY aud.revcomps (rvc_id, rvc_rev_id, rvc_table, rvc_detail, rvc_rowid, rvc_action, rvc_type) FROM '$$PATH$$/5646.dat';

--
-- Data for Name: revdata; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.revdata (rvd_id, rvd_rev_id, rvd_table, rvd_rowid, rvd_attrib, rvd_oldvalue, rvd_newvalue, rvd_datatype, rvd_type, rvd_conversion, rvd_colname, rvd_alias) FROM stdin;
\.
COPY aud.revdata (rvd_id, rvd_rev_id, rvd_table, rvd_rowid, rvd_attrib, rvd_oldvalue, rvd_newvalue, rvd_datatype, rvd_type, rvd_conversion, rvd_colname, rvd_alias) FROM '$$PATH$$/5648.dat';

--
-- Data for Name: revs; Type: TABLE DATA; Schema: aud; Owner: postgres
--

COPY aud.revs (rev_id, rev_obj, rev_releasedtg, rev_closedtg, rev_objid, rev_reason, rev_status, rev_unt_id, rev_date, rev_type, rev_intunt_id, rev_ref) FROM stdin;
\.
COPY aud.revs (rev_id, rev_obj, rev_releasedtg, rev_closedtg, rev_objid, rev_reason, rev_status, rev_unt_id, rev_date, rev_type, rev_intunt_id, rev_ref) FROM '$$PATH$$/5650.dat';

--
-- Data for Name: accounts; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.accounts (acc_id, acc_unt_id, acc_level, acc_type, acc_reportlevel, acc_username, acc_pass, acc_startdt, acc_status, acc_enddt, acc_name, acc_title, acc_rank, acc_desig, acc_desigshort, acc_desigtype, acc_lowerm, acc_upperm, acc_access, acc_lowers, acc_uppers, acc_untname, acc_untnamesh, acc_unttype, acc_auth, acc_untarea) FROM stdin;
\.
COPY cen.accounts (acc_id, acc_unt_id, acc_level, acc_type, acc_reportlevel, acc_username, acc_pass, acc_startdt, acc_status, acc_enddt, acc_name, acc_title, acc_rank, acc_desig, acc_desigshort, acc_desigtype, acc_lowerm, acc_upperm, acc_access, acc_lowers, acc_uppers, acc_untname, acc_untnamesh, acc_unttype, acc_auth, acc_untarea) FROM '$$PATH$$/5652.dat';

--
-- Data for Name: globalvars; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.globalvars (gvar_name, gvar_value, gvar_type, gvar_remarks) FROM stdin;
\.
COPY cen.globalvars (gvar_name, gvar_value, gvar_type, gvar_remarks) FROM '$$PATH$$/5653.dat';

--
-- Data for Name: heads; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.heads (hed_id, hed_name, hed_type, hed_code, hed_opendt, hed_closedt, hed_transtype, hed_unt_id, hed_prj_id) FROM stdin;
\.
COPY cen.heads (hed_id, hed_name, hed_type, hed_code, hed_opendt, hed_closedt, hed_transtype, hed_unt_id, hed_prj_id) FROM '$$PATH$$/5654.dat';

--
-- Data for Name: levels; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.levels (lvl_id, lvl_cat) FROM stdin;
\.
COPY cen.levels (lvl_id, lvl_cat) FROM '$$PATH$$/5655.dat';

--
-- Data for Name: roles; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.roles (rol_xunt_id, rol_level, rol_desig, rol_desigshort, rol_type, rol_reportlevel, rol_access, rol_authprj) FROM stdin;
\.
COPY cen.roles (rol_xunt_id, rol_level, rol_desig, rol_desigshort, rol_type, rol_reportlevel, rol_access, rol_authprj) FROM '$$PATH$$/5656.dat';

--
-- Data for Name: routes; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.routes (rte_doc, rte_steps) FROM stdin;
\.
COPY cen.routes (rte_doc, rte_steps) FROM '$$PATH$$/5657.dat';

--
-- Data for Name: units; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.units (unt_id, unt_type, unt_name, unt_namesh, unt_alowerlimit, unt_aupperlimit, unt_nlowerlimit, unt_nupperlimit, unt_area, unt_leadname, unt_leadtitle, unt_leadrank, unt_leaddesig, unt_leaddesigshort) FROM stdin;
\.
COPY cen.units (unt_id, unt_type, unt_name, unt_namesh, unt_alowerlimit, unt_aupperlimit, unt_nlowerlimit, unt_nupperlimit, unt_area, unt_leadname, unt_leadtitle, unt_leadrank, unt_leaddesig, unt_leaddesigshort) FROM '$$PATH$$/5658.dat';

--
-- Data for Name: version; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.version (ver_version, ver_compat) FROM stdin;
\.
COPY cen.version (ver_version, ver_compat) FROM '$$PATH$$/5659.dat';

--
-- Data for Name: commitments; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.commitments (cmt_id, cmt_docid, cmt_type, cmt_date, cmt_amount, cmt_status, cmt_effhed_id, cmt_effunt_id, cmt_hed_id, cmt_unt_id, cmt_sudohed) FROM stdin;
\.
COPY fin.commitments (cmt_id, cmt_docid, cmt_type, cmt_date, cmt_amount, cmt_status, cmt_effhed_id, cmt_effunt_id, cmt_hed_id, cmt_unt_id, cmt_sudohed) FROM '$$PATH$$/5660.dat';

--
-- Data for Name: contractsverif; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.contractsverif (cvf_ctr_id, cvf_verif, cvf_dtg) FROM stdin;
\.
COPY fin.contractsverif (cvf_ctr_id, cvf_verif, cvf_dtg) FROM '$$PATH$$/5662.dat';

--
-- Data for Name: empeffheads; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.empeffheads (eeh_emp_id, eeh_emphed_id, eeh_dtg, eeh_status, eeh_remarks, eeh_sudohed) FROM stdin;
\.
COPY fin.empeffheads (eeh_emp_id, eeh_emphed_id, eeh_dtg, eeh_status, eeh_remarks, eeh_sudohed) FROM '$$PATH$$/5663.dat';

--
-- Data for Name: loanadjustments; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.loanadjustments (lad_string, lad_amount, lad_remarks, lad_id, lad_dtg, lad_from, lad_to) FROM stdin;
\.
COPY fin.loanadjustments (lad_string, lad_amount, lad_remarks, lad_id, lad_dtg, lad_from, lad_to) FROM '$$PATH$$/5664.dat';

--
-- Data for Name: loanremarks; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.loanremarks (lrm_string, lrm_remarks) FROM stdin;
\.
COPY fin.loanremarks (lrm_string, lrm_remarks) FROM '$$PATH$$/5666.dat';

--
-- Data for Name: msncosts; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.msncosts (mct_prj_id, mct_hed_id, mct_msn_id, mct_cost, mct_msn_idd) FROM stdin;
\.
COPY fin.msncosts (mct_prj_id, mct_hed_id, mct_msn_id, mct_cost, mct_msn_idd) FROM '$$PATH$$/5667.dat';

--
-- Data for Name: salorders; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.salorders (sor_id, sor_hed_id, sor_releasedtg, sor_status, sor_remarks, sor_srq_id, sor_closedtg, sor_unt_id, sor_month, sor_netsalary, sor_salary, sor_emp_id, sor_effhed_id, sor_empnamecomp, sor_bnkacctitle, sor_effunt_id, sor_bnkaccdetail, sor_ctrsalary, sor_checked, sor_contracts, sor_noloan, sor_transtype, sor_sudohed, sor_remarks2, sor_type, sor_grosalary, sor_arrears, sor_dues, sor_overwork, sor_underwork, sor_loaned, sor_withheld, sor_award, sor_penalty, sor_paidalready) FROM stdin;
\.
COPY fin.salorders (sor_id, sor_hed_id, sor_releasedtg, sor_status, sor_remarks, sor_srq_id, sor_closedtg, sor_unt_id, sor_month, sor_netsalary, sor_salary, sor_emp_id, sor_effhed_id, sor_empnamecomp, sor_bnkacctitle, sor_effunt_id, sor_bnkaccdetail, sor_ctrsalary, sor_checked, sor_contracts, sor_noloan, sor_transtype, sor_sudohed, sor_remarks2, sor_type, sor_grosalary, sor_arrears, sor_dues, sor_overwork, sor_underwork, sor_loaned, sor_withheld, sor_award, sor_penalty, sor_paidalready) FROM '$$PATH$$/5668.dat';

--
-- Data for Name: salorders_shd; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.salorders_shd (sod_sor_id, sod_type, sod_subhead, sod_ratio) FROM stdin;
\.
COPY fin.salorders_shd (sod_sor_id, sod_type, sod_subhead, sod_ratio) FROM '$$PATH$$/5669.dat';

--
-- Data for Name: sharesalloc; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.sharesalloc (sha_hed_id, sha_ficmt_id, sha_focmt_id, sha_transtype, sha_id, sha_cf, sha_pcc, sha_prj, sha_prj_sal, sha_prj_pur) FROM stdin;
\.
COPY fin.sharesalloc (sha_hed_id, sha_ficmt_id, sha_focmt_id, sha_transtype, sha_id, sha_cf, sha_pcc, sha_prj, sha_prj_sal, sha_prj_pur) FROM '$$PATH$$/5671.dat';

--
-- Data for Name: sharesinstall; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.sharesinstall (shi_hed_id, shi_fitrn_id, shi_fotrn_id, shi_id, shi_cf, shi_pcc, shi_msn_idd, shi_prj) FROM stdin;
\.
COPY fin.sharesinstall (shi_hed_id, shi_fitrn_id, shi_fotrn_id, shi_id, shi_cf, shi_pcc, shi_msn_idd, shi_prj) FROM '$$PATH$$/5673.dat';

--
-- Data for Name: subheads; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.subheads (sbh_hed_id, sbh_name, sbh_alloc, sbh_id) FROM stdin;
\.
COPY fin.subheads (sbh_hed_id, sbh_name, sbh_alloc, sbh_id) FROM '$$PATH$$/5675.dat';

--
-- Data for Name: subheads_zzz; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.subheads_zzz (sbh_hed_id, sbh_name, sbh_alloc) FROM stdin;
\.
COPY fin.subheads_zzz (sbh_hed_id, sbh_name, sbh_alloc) FROM '$$PATH$$/5677.dat';

--
-- Data for Name: transactions; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.transactions (trn_id, trn_cmt_id, trn_date, trn_amount1, trn_balance, trn_seq, trn_tax1, trn_amount2, trn_transtype, trn_noloan) FROM stdin;
\.
COPY fin.transactions (trn_id, trn_cmt_id, trn_date, trn_amount1, trn_balance, trn_seq, trn_tax1, trn_amount2, trn_transtype, trn_noloan) FROM '$$PATH$$/5678.dat';

--
-- Data for Name: transfers; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.transfers (trf_id, trf_date, trf_type, trf_title, trf_amount, trf_fromhed, trf_fromunt, trf_tohed, trf_tount, trf_status) FROM stdin;
\.
COPY fin.transfers (trf_id, trf_date, trf_type, trf_title, trf_amount, trf_fromhed, trf_fromunt, trf_tohed, trf_tount, trf_status) FROM '$$PATH$$/5680.dat';

--
-- Data for Name: facils; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.facils (fcl_xfrm_id, fcl_facil) FROM stdin;
\.
COPY frm.facils (fcl_xfrm_id, fcl_facil) FROM '$$PATH$$/5682.dat';

--
-- Data for Name: firmz; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.firmz (frm_id, frm_name, frm_entity, frm_type, frm_group, frm_emp, frm_points, frm_black, frm_notes, frm_id_old, frm_ntn, frm_gst) FROM stdin;
\.
COPY frm.firmz (frm_id, frm_name, frm_entity, frm_type, frm_group, frm_emp, frm_points, frm_black, frm_notes, frm_id_old, frm_ntn, frm_gst) FROM '$$PATH$$/5683.dat';

--
-- Data for Name: info; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.info (inf_xmsc_id, inf_xmsc_entity, inf_type, inf_value) FROM stdin;
\.
COPY frm.info (inf_xmsc_id, inf_xmsc_entity, inf_type, inf_value) FROM '$$PATH$$/5685.dat';

--
-- Data for Name: offices; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.offices (off_id, off_entity, off_xfrm_id, off_name, off_type, off_address, off_city) FROM stdin;
\.
COPY frm.offices (off_id, off_entity, off_xfrm_id, off_name, off_type, off_address, off_city) FROM '$$PATH$$/5686.dat';

--
-- Data for Name: persons; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.persons (per_id, per_entity, per_xfrm_id, per_title, per_name, per_desig, per_dept, per_exprt) FROM stdin;
\.
COPY frm.persons (per_id, per_entity, per_xfrm_id, per_title, per_name, per_desig, per_dept, per_exprt) FROM '$$PATH$$/5688.dat';

--
-- Data for Name: projects; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.projects (prj_xfrm_id, prj_name, prj_scope, prj_awarddt, prj_status, prj_compdt, prj_tech, prj_cost) FROM stdin;
\.
COPY frm.projects (prj_xfrm_id, prj_name, prj_scope, prj_awarddt, prj_status, prj_compdt, prj_tech, prj_cost) FROM '$$PATH$$/5690.dat';

--
-- Data for Name: specs; Type: TABLE DATA; Schema: frm; Owner: postgres
--

COPY frm.specs (spc_xfrm_id, spc_spec) FROM stdin;
\.
COPY frm.specs (spc_xfrm_id, spc_spec) FROM '$$PATH$$/5691.dat';

--
-- Data for Name: applicants; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.applicants (apl_id, apl_cnic, apl_name, apl_discip, apl_qualif, apl_spec, apl_paddress, apl_dob, apl_dtg, apl_marital, apl_ntnlty, apl_pob, apl_taddress, apl_mobile, apl_landline, apl_gender, apl_mobile2, apl_rank, apl_status, apl_remarks, apl_appliedfor, apl_currentsal, apl_expectedsal, apl_experience, apl_expjoindt, apl_email, apl_father, apl_unt_id) FROM stdin;
\.
COPY hr.applicants (apl_id, apl_cnic, apl_name, apl_discip, apl_qualif, apl_spec, apl_paddress, apl_dob, apl_dtg, apl_marital, apl_ntnlty, apl_pob, apl_taddress, apl_mobile, apl_landline, apl_gender, apl_mobile2, apl_rank, apl_status, apl_remarks, apl_appliedfor, apl_currentsal, apl_expectedsal, apl_experience, apl_expjoindt, apl_email, apl_father, apl_unt_id) FROM '$$PATH$$/5694.dat';

--
-- Data for Name: applicjobs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.applicjobs (apj_company, apj_jobtitle, apj_repto, apj_team, apj_from, apj_to, apj_resp, apj_ach, apj_id, apj_apl_id, apj_city) FROM stdin;
\.
COPY hr.applicjobs (apj_company, apj_jobtitle, apj_repto, apj_team, apj_from, apj_to, apj_resp, apj_ach, apj_id, apj_apl_id, apj_city) FROM '$$PATH$$/5692.dat';

--
-- Data for Name: applicqualifs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.applicqualifs (apq_id, apq_type, apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_apl_id, apq_grade, apq_license, apq_spec) FROM stdin;
\.
COPY hr.applicqualifs (apq_id, apq_type, apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_apl_id, apq_grade, apq_license, apq_spec) FROM '$$PATH$$/5696.dat';

--
-- Data for Name: attendance; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.attendance (att_id, att_emp_id, att_empnamecomp, att_unt_id, att_startdt, att_enddt, att_1, att_2, att_3, att_4, att_5, att_6, att_7, att_8, att_9, att_10, att_11, att_12, att_13, att_14, att_15, att_16, att_17, att_18, att_19, att_20, att_21, att_22, att_23, att_24, att_25, att_26, att_27, att_28, att_29, att_30, att_31, att_locked1, att_locked2, att_eahreplace) FROM stdin;
\.
COPY hr.attendance (att_id, att_emp_id, att_empnamecomp, att_unt_id, att_startdt, att_enddt, att_1, att_2, att_3, att_4, att_5, att_6, att_7, att_8, att_9, att_10, att_11, att_12, att_13, att_14, att_15, att_16, att_17, att_18, att_19, att_20, att_21, att_22, att_23, att_24, att_25, att_26, att_27, att_28, att_29, att_30, att_31, att_locked1, att_locked2, att_eahreplace) FROM '$$PATH$$/5697.dat';

--
-- Data for Name: bdapps; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.bdapps (bda_brd_id, bda_apl_id) FROM stdin;
\.
COPY hr.bdapps (bda_brd_id, bda_apl_id) FROM '$$PATH$$/5699.dat';

--
-- Data for Name: bdmems; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.bdmems (bdm_id, bdm_brd_id, bdm_name, bdm_rank) FROM stdin;
\.
COPY hr.bdmems (bdm_id, bdm_brd_id, bdm_name, bdm_rank) FROM '$$PATH$$/5700.dat';

--
-- Data for Name: bnkaccounts; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.bnkaccounts (bac_id, bac_emp_id, bac_bnkname, bac_bchname, bac_bchcode, bac_acctitle, bac_accnum, bac_bchcity, bac_selforpay) FROM stdin;
\.
COPY hr.bnkaccounts (bac_id, bac_emp_id, bac_bnkname, bac_bchname, bac_bchcode, bac_acctitle, bac_accnum, bac_bchcity, bac_selforpay) FROM '$$PATH$$/5701.dat';

--
-- Data for Name: boards; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.boards (brd_id, brd_date, brd_status, brd_hir_id) FROM stdin;
\.
COPY hr.boards (brd_id, brd_date, brd_status, brd_hir_id) FROM '$$PATH$$/5703.dat';

--
-- Data for Name: contractplans; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.contractplans (cpn_id, cpn_ctr_id, cpn_startdt, cpn_enddt, cpn_hed_id) FROM stdin;
\.
COPY hr.contractplans (cpn_id, cpn_ctr_id, cpn_startdt, cpn_enddt, cpn_hed_id) FROM '$$PATH$$/5705.dat';

--
-- Data for Name: contracts; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.contracts (ctr_unt_id, ctr_num, ctr_startdt, ctr_enddt, ctr_date, ctr_jobtitle, ctr_grade, ctr_salary, ctr_hed_id, ctr_id, ctr_prob, ctr_probsal, ctr_termindt, ctr_remarks, ctr_path, ctr_type, ctr_path2) FROM stdin;
\.
COPY hr.contracts (ctr_unt_id, ctr_num, ctr_startdt, ctr_enddt, ctr_date, ctr_jobtitle, ctr_grade, ctr_salary, ctr_hed_id, ctr_id, ctr_prob, ctr_probsal, ctr_termindt, ctr_remarks, ctr_path, ctr_type, ctr_path2) FROM '$$PATH$$/5707.dat';

--
-- Data for Name: empattachments; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empattachments (eat_objtype, eat_objid, eat_type, eat_path, eat_id) FROM stdin;
\.
COPY hr.empattachments (eat_objtype, eat_objid, eat_type, eat_path, eat_id) FROM '$$PATH$$/5709.dat';

--
-- Data for Name: emps; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.emps (emp_id, emp_cnic, emp_name, emp_joindt, emp_locked, emp_rank, emp_status, emp_remarks, emp_unt_id, emp_hed_id, emp_lastdt, emp_title, emp_photodest) FROM stdin;
\.
COPY hr.emps (emp_id, emp_cnic, emp_name, emp_joindt, emp_locked, emp_rank, emp_status, emp_remarks, emp_unt_id, emp_hed_id, emp_lastdt, emp_title, emp_photodest) FROM '$$PATH$$/5713.dat';

--
-- Data for Name: empsexta; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empsexta (empexta_emp_id, emp_discip, emp_qualif, emp_spec, emp_paddress, emp_dob, emp_marital, emp_ntnlty, emp_pob, emp_taddress, emp_mobile, emp_landline, emp_gender, emp_mobile2, emp_email, emp_father) FROM stdin;
\.
COPY hr.empsexta (empexta_emp_id, emp_discip, emp_qualif, emp_spec, emp_paddress, emp_dob, emp_marital, emp_ntnlty, emp_pob, emp_taddress, emp_mobile, emp_landline, emp_gender, emp_mobile2, emp_email, emp_father) FROM '$$PATH$$/5714.dat';

--
-- Data for Name: empsextb; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empsextb (empextb_emp_id, emp_nokname, emp_nokrelation, emp_nokcnic, emp_emername, emp_emerrelation, emp_emermobile, emp_idmark, emp_height, emp_caste, emp_religion, emp_sect, emp_police, emp_political) FROM stdin;
\.
COPY hr.empsextb (empextb_emp_id, emp_nokname, emp_nokrelation, emp_nokcnic, emp_emername, emp_emerrelation, emp_emermobile, emp_idmark, emp_height, emp_caste, emp_religion, emp_sect, emp_police, emp_political) FROM '$$PATH$$/5715.dat';

--
-- Data for Name: empsextc; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.empsextc (empextc_emp_id, emp_cnum, emp_cissuedt, emp_cexpdt, emp_secclear) FROM stdin;
\.
COPY hr.empsextc (empextc_emp_id, emp_cnum, emp_cissuedt, emp_cexpdt, emp_secclear) FROM '$$PATH$$/5716.dat';

--
-- Data for Name: hirings; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.hirings (hir_id, hir_hhd_id, hir_reference, hir_date, hir_jobtitle, hir_grade, hir_salarymin, hir_salarymax) FROM stdin;
\.
COPY hr.hirings (hir_id, hir_hhd_id, hir_reference, hir_date, hir_jobtitle, hir_grade, hir_salarymin, hir_salarymax) FROM '$$PATH$$/5717.dat';

--
-- Data for Name: hrheads; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.hrheads (hhd_id, hhd_dpt_id, hhd_name) FROM stdin;
\.
COPY hr.hrheads (hhd_id, hhd_dpt_id, hhd_name) FROM '$$PATH$$/5718.dat';

--
-- Data for Name: jobs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.jobs (job_company, job_jobtitle, job_repto, job_team, job_from, job_to, job_resp, job_ach, job_emp_id, job_id, job_city) FROM stdin;
\.
COPY hr.jobs (job_company, job_jobtitle, job_repto, job_team, job_from, job_to, job_resp, job_ach, job_emp_id, job_id, job_city) FROM '$$PATH$$/5719.dat';

--
-- Data for Name: qualifs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.qualifs (qlf_id, qlf_type, qlf_level, qlf_name, qlf_inst, qlf_duration, qlf_unit, qlf_enddt, qlf_emp_id, qlf_grade, qlf_license, qlf_spec) FROM stdin;
\.
COPY hr.qualifs (qlf_id, qlf_type, qlf_level, qlf_name, qlf_inst, qlf_duration, qlf_unit, qlf_enddt, qlf_emp_id, qlf_grade, qlf_license, qlf_spec) FROM '$$PATH$$/5711.dat';

--
-- Data for Name: salreqs; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.salreqs (srq_id, srq_emp_id, srq_empnamecomp, srq_unt_id, srq_hed_id, srq_effunt_id, srq_effhed_id, srq_month, srq_unpaiddays, srq_salary, srq_status, srq_remarks, srq_releasedtg, srq_closedtg, srq_fulfilment, srq_ctrsalary, srq_grosalary, srq_netsalary, srq_bnkaccdetail, srq_bnkacctitle, srq_contracts, srq_checked, srq_arrears, srq_dues, srq_overwork, srq_underwork, srq_loaned, srq_withheld, srq_award, srq_penalty, srq_paidalready, srq_paidholidays, srq_remarks2, srq_sudohed) FROM stdin;
\.
COPY hr.salreqs (srq_id, srq_emp_id, srq_empnamecomp, srq_unt_id, srq_hed_id, srq_effunt_id, srq_effhed_id, srq_month, srq_unpaiddays, srq_salary, srq_status, srq_remarks, srq_releasedtg, srq_closedtg, srq_fulfilment, srq_ctrsalary, srq_grosalary, srq_netsalary, srq_bnkaccdetail, srq_bnkacctitle, srq_contracts, srq_checked, srq_arrears, srq_dues, srq_overwork, srq_underwork, srq_loaned, srq_withheld, srq_award, srq_penalty, srq_paidalready, srq_paidholidays, srq_remarks2, srq_sudohed) FROM '$$PATH$$/5723.dat';

--
-- Data for Name: vehicles; Type: TABLE DATA; Schema: hr; Owner: postgres
--

COPY hr.vehicles (vcl_id, vcl_emp_id, vcl_type, vcl_maker, vcl_variant, vcl_year, vcl_regis, vcl_color) FROM stdin;
\.
COPY hr.vehicles (vcl_id, vcl_emp_id, vcl_type, vcl_maker, vcl_variant, vcl_year, vcl_regis, vcl_color) FROM '$$PATH$$/5725.dat';

--
-- Data for Name: inaattachments; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.inaattachments (iat_objtype, iat_objid, iat_type, iat_path, iat_id) FROM stdin;
\.
COPY ina.inaattachments (iat_objtype, iat_objid, iat_type, iat_path, iat_id) FROM '$$PATH$$/5727.dat';

--
-- Data for Name: invatcomps; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.invatcomps (iac_id, iac_ias_id, iac_qty, iac_qtyunit, iac_parent_id, iac_isparent, iac_isassembly, iac_location, iac_group, iac_person, iac_dtg, iac_dispdate, iac_dispdtg, iac_status, iac_remarks, iac_details, iac_shared) FROM stdin;
\.
COPY ina.invatcomps (iac_id, iac_ias_id, iac_qty, iac_qtyunit, iac_parent_id, iac_isparent, iac_isassembly, iac_location, iac_group, iac_person, iac_dtg, iac_dispdate, iac_dispdtg, iac_status, iac_remarks, iac_details, iac_shared) FROM '$$PATH$$/5729.dat';

--
-- Data for Name: invatlocs; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.invatlocs (ial_id, ial_ias_id, ial_date, ial_unt_id, ial_location, ial_custgroup, ial_custperson) FROM stdin;
\.
COPY ina.invatlocs (ial_id, ial_ias_id, ial_date, ial_unt_id, ial_location, ial_custgroup, ial_custperson) FROM '$$PATH$$/5731.dat';

--
-- Data for Name: invats; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.invats (ias_id, ias_pcs_id, ias_pci_id, ias_desc, ias_details, ias_spec_id, ias_qty, ias_qtyunit, ias_unt_id, ias_prj_id, ias_effhed_id, ias_chargedate, ias_price, ias_type, ias_subtype, ias_dtg, ias_type2) FROM stdin;
\.
COPY ina.invats (ias_id, ias_pcs_id, ias_pci_id, ias_desc, ias_details, ias_spec_id, ias_qty, ias_qtyunit, ias_unt_id, ias_prj_id, ias_effhed_id, ias_chargedate, ias_price, ias_type, ias_subtype, ias_dtg, ias_type2) FROM '$$PATH$$/5733.dat';

--
-- Data for Name: invatspecs; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.invatspecs (iap_ias_id, iap_specname, iap_specvalue) FROM stdin;
\.
COPY ina.invatspecs (iap_ias_id, iap_specname, iap_specvalue) FROM '$$PATH$$/5735.dat';

--
-- Data for Name: invenitems; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.invenitems (ini_id, ini_inv_id, ini_qty, ini_qtyunit, ini_parent_id, ini_isparent, ini_isassembly, ini_location, ini_group, ini_person, ini_dtg, ini_dispdate, ini_dispdtg, ini_status, ini_remarks) FROM stdin;
\.
COPY ina.invenitems (ini_id, ini_inv_id, ini_qty, ini_qtyunit, ini_parent_id, ini_isparent, ini_isassembly, ini_location, ini_group, ini_person, ini_dtg, ini_dispdate, ini_dispdtg, ini_status, ini_remarks) FROM '$$PATH$$/5736.dat';

--
-- Data for Name: inventory; Type: TABLE DATA; Schema: ina; Owner: postgres
--

COPY ina.inventory (inv_id, inv_pcs_id, inv_pci_id, inv_desc, inv_details, inv_spec_id, inv_qty, inv_qtyunit, inv_unt_id, inv_chargedate, inv_price, inv_type, inv_subtype, inv_dtg, inv_prj_id) FROM stdin;
\.
COPY ina.inventory (inv_id, inv_pcs_id, inv_pci_id, inv_desc, inv_details, inv_spec_id, inv_qty, inv_qtyunit, inv_unt_id, inv_chargedate, inv_price, inv_type, inv_subtype, inv_dtg, inv_prj_id) FROM '$$PATH$$/5737.dat';

--
-- Data for Name: comments; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.comments (cmt_xpgh_id, cmt_id, cmt_dtg, cmt_comment, cmt_author, cmt_status) FROM stdin;
\.
COPY prj.comments (cmt_xpgh_id, cmt_id, cmt_dtg, cmt_comment, cmt_author, cmt_status) FROM '$$PATH$$/5738.dat';

--
-- Data for Name: events; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.events (evt_id, evt_name, evt_doer, evt_effectee, evt_dtg, evt_xprj_id, evt_xpgh_id, evt_xcmt_id) FROM stdin;
\.
COPY prj.events (evt_id, evt_name, evt_doer, evt_effectee, evt_dtg, evt_xprj_id, evt_xpgh_id, evt_xcmt_id) FROM '$$PATH$$/5740.dat';

--
-- Data for Name: milestones; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.milestones (msn_id, msn_xprj_id, msn_type, msn_desc, msn_cost, msn_startdt, msn_targetdt, msn_achvdt, msn_comp, msn_pay, msn_paydt, msn_status, msn_rem, msn_idd) FROM stdin;
\.
COPY prj.milestones (msn_id, msn_xprj_id, msn_type, msn_desc, msn_cost, msn_startdt, msn_targetdt, msn_achvdt, msn_comp, msn_pay, msn_paydt, msn_status, msn_rem, msn_idd) FROM '$$PATH$$/5742.dat';

--
-- Data for Name: mprgroup; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.mprgroup (mgp_id, mgp_name, mgp_dtg, mgp_status) FROM stdin;
\.
COPY prj.mprgroup (mgp_id, mgp_name, mgp_dtg, mgp_status) FROM '$$PATH$$/5744.dat';

--
-- Data for Name: prghistory; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.prghistory (pgh_xprj_id, pgh_id, pgh_dtg, pgh_progress, pgh_author, pgh_status, pgh_closedtg, pgh_level, pgh_group, pgh_trail, pgh_trailinherited, pgh_underedit, pgh_path) FROM stdin;
\.
COPY prj.prghistory (pgh_xprj_id, pgh_id, pgh_dtg, pgh_progress, pgh_author, pgh_status, pgh_closedtg, pgh_level, pgh_group, pgh_trail, pgh_trailinherited, pgh_underedit, pgh_path) FROM '$$PATH$$/5746.dat';

--
-- Data for Name: prjattachments; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.prjattachments (jat_objtype, jat_objid, jat_type, jat_path, jat_id) FROM stdin;
\.
COPY prj.prjattachments (jat_objtype, jat_objid, jat_type, jat_path, jat_id) FROM '$$PATH$$/5748.dat';

--
-- Data for Name: projects; Type: TABLE DATA; Schema: prj; Owner: postgres
--

COPY prj.projects (prj_title, prj_id, prj_startdt, prj_id_old, prj_scope, prj_sponsor, prj_rcptdt, prj_assigndt, prj_propdt, prj_propcost, prj_aprvdt, prj_aprvcost, prj_estenddt, prj_cfycost, prj_status, prj_enddt, prj_rem, prj_notes, prj_notes1, prj_unt_id, prj_reporting, prj_code) FROM stdin;
\.
COPY prj.projects (prj_title, prj_id, prj_startdt, prj_id_old, prj_scope, prj_sponsor, prj_rcptdt, prj_assigndt, prj_propdt, prj_propcost, prj_aprvdt, prj_aprvcost, prj_estenddt, prj_cfycost, prj_status, prj_enddt, prj_rem, prj_notes, prj_notes1, prj_unt_id, prj_reporting, prj_code) FROM '$$PATH$$/5750.dat';

--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.
COPY public.cache (key, value, expiration) FROM '$$PATH$$/5781.dat';

--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.
COPY public.cache_locks (key, owner, expiration) FROM '$$PATH$$/5782.dat';

--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.
COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM '$$PATH$$/5787.dat';

--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.
COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM '$$PATH$$/5785.dat';

--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.
COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM '$$PATH$$/5784.dat';

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
\.
COPY public.migrations (id, migration, batch) FROM '$$PATH$$/5776.dat';

--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.
COPY public.password_reset_tokens (email, token, created_at) FROM '$$PATH$$/5779.dat';

--
-- Data for Name: project_activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.project_activities (pja_id, pja_prj_id, pja_action, pja_details, pja_user, created_at, updated_at) FROM stdin;
\.
COPY public.project_activities (pja_id, pja_prj_id, pja_action, pja_details, pja_user, created_at, updated_at) FROM '$$PATH$$/5789.dat';

--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.
COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM '$$PATH$$/5780.dat';

--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
\.
COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM '$$PATH$$/5778.dat';

--
-- Data for Name: noquotes; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.noquotes (nqt_id, nqt_pcs_id, nqt_frm_id) FROM stdin;
\.
COPY pur.noquotes (nqt_id, nqt_pcs_id, nqt_frm_id) FROM '$$PATH$$/5753.dat';

--
-- Data for Name: purattachments; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purattachments (pat_objtype, pat_objid, pat_type, pat_path, pat_id) FROM stdin;
\.
COPY pur.purattachments (pat_objtype, pat_objid, pat_type, pat_path, pat_id) FROM '$$PATH$$/5755.dat';

--
-- Data for Name: purcaseitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcaseitems (pci_pri_id, pci_serial, pci_desc, pci_estprice, pci_qty, pci_qtyunit, pci_id, pci_pcs_id, pci_price, pci_fulfilment, pci_type, pci_subtype, pci_category, pci_type2, pci_subhead) FROM stdin;
\.
COPY pur.purcaseitems (pci_pri_id, pci_serial, pci_desc, pci_estprice, pci_qty, pci_qtyunit, pci_id, pci_pcs_id, pci_price, pci_fulfilment, pci_type, pci_subtype, pci_category, pci_type2, pci_subhead) FROM '$$PATH$$/5757.dat';

--
-- Data for Name: purcaseminuterefs; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcaseminuterefs (pmr_pcm_id, pmr_min, pmr_title, pmr_date, pmr_from, pmr_flag, pmr_ref, pmr_encl) FROM stdin;
\.
COPY pur.purcaseminuterefs (pmr_pcm_id, pmr_min, pmr_title, pmr_date, pmr_from, pmr_flag, pmr_ref, pmr_encl) FROM '$$PATH$$/5759.dat';

--
-- Data for Name: purcaseminutes; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcaseminutes (pcm_id, pcm_texta, pcm_textb, pcm_textc, pcm_textd, pcm_texte, pcm_lwoamount, pcm_minute, pcm_purcases, pcm_eqdue, pcm_hrdue, pcm_msdue, pcm_date) FROM stdin;
\.
COPY pur.purcaseminutes (pcm_id, pcm_texta, pcm_textb, pcm_textc, pcm_textd, pcm_texte, pcm_lwoamount, pcm_minute, pcm_purcases, pcm_eqdue, pcm_hrdue, pcm_msdue, pcm_date) FROM '$$PATH$$/5760.dat';

--
-- Data for Name: purcases; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcases (pcs_id, pcs_date, pcs_remarks, pcs_status, pcs_unt_id, pcs_hed_id, pcs_purreqs, pcs_releasedtg, pcs_effhed_id, pcs_intprice, pcs_inttax, pcs_midprice, pcs_midtax, pcs_price, pcs_title, pcs_frm_id, pcs_type, pcs_effunt_id, pcs_intunt_id, pcs_transtype, pcs_noloan, pcs_sudohed, pcs_minute, pcs_recomm, pcs_quotetype, pcs_approvedtg, pcs_closedtg) FROM stdin;
\.
COPY pur.purcases (pcs_id, pcs_date, pcs_remarks, pcs_status, pcs_unt_id, pcs_hed_id, pcs_purreqs, pcs_releasedtg, pcs_effhed_id, pcs_intprice, pcs_inttax, pcs_midprice, pcs_midtax, pcs_price, pcs_title, pcs_frm_id, pcs_type, pcs_effunt_id, pcs_intunt_id, pcs_transtype, pcs_noloan, pcs_sudohed, pcs_minute, pcs_recomm, pcs_quotetype, pcs_approvedtg, pcs_closedtg) FROM '$$PATH$$/5751.dat';

--
-- Data for Name: purcases_shd; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcases_shd (pcd_pcs_id, pcd_subhead, pcd_ratio, pcd_type) FROM stdin;
\.
COPY pur.purcases_shd (pcd_pcs_id, pcd_subhead, pcd_ratio, pcd_type) FROM '$$PATH$$/5762.dat';

--
-- Data for Name: purreceiptitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreceiptitems (pti_id, pti_prt_id, pti_desc, pti_qty, pti_qtyunit, pti_pci_id, pti_pri_id, pti_serial) FROM stdin;
\.
COPY pur.purreceiptitems (pti_id, pti_prt_id, pti_desc, pti_qty, pti_qtyunit, pti_pci_id, pti_pri_id, pti_serial) FROM '$$PATH$$/5767.dat';

--
-- Data for Name: purreceipts; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreceipts (prt_id, prt_date, prt_unt_id, prt_prj_id, prt_status, prt_pcs_id, prt_dtg) FROM stdin;
\.
COPY pur.purreceipts (prt_id, prt_date, prt_unt_id, prt_prj_id, prt_status, prt_pcs_id, prt_dtg) FROM '$$PATH$$/5765.dat';

--
-- Data for Name: purreqitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreqitems (pri_prq_id, pri_serial, pri_desc, pri_price, pri_qty, pri_qtyunit, pri_type, pri_category, pri_id, pri_fulfilment, pri_appqty, pri_remarks, pri_subtype) FROM stdin;
\.
COPY pur.purreqitems (pri_prq_id, pri_serial, pri_desc, pri_price, pri_qty, pri_qtyunit, pri_type, pri_category, pri_id, pri_fulfilment, pri_appqty, pri_remarks, pri_subtype) FROM '$$PATH$$/5763.dat';

--
-- Data for Name: purreqs; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreqs (prq_id, prq_date, prq_unt_id, prq_hed_id, prq_status, prq_fulfilled, prq_dtg, prq_effhed_id, prq_appeffhed_id, prq_intunt_id, prq_desc, prq_minute) FROM stdin;
\.
COPY pur.purreqs (prq_id, prq_date, prq_unt_id, prq_hed_id, prq_status, prq_fulfilled, prq_dtg, prq_effhed_id, prq_appeffhed_id, prq_intunt_id, prq_desc, prq_minute) FROM '$$PATH$$/5769.dat';

--
-- Data for Name: quoteitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.quoteitems (qti_qte_id, qti_serial, qti_desc, qti_price, qti_qty, qti_qtyunit, qti_id, qti_pcsdesc, qti_pci_id) FROM stdin;
\.
COPY pur.quoteitems (qti_qte_id, qti_serial, qti_desc, qti_price, qti_qty, qti_qtyunit, qti_id, qti_pcsdesc, qti_pci_id) FROM '$$PATH$$/5771.dat';

--
-- Data for Name: quotes; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.quotes (qte_date, qte_firmname, qte_pcs_id, qte_num, qte_id, qte_price, qte_frm_id, qte_techaccept, qte_recomm, qte_midprice, qte_midtax, qte_intprice, qte_inttax, qte_quotetype) FROM stdin;
\.
COPY pur.quotes (qte_date, qte_firmname, qte_pcs_id, qte_num, qte_id, qte_price, qte_frm_id, qte_techaccept, qte_recomm, qte_midprice, qte_midtax, qte_intprice, qte_inttax, qte_quotetype) FROM '$$PATH$$/5773.dat';

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
-- Name: commitments_cmt_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.commitments_cmt_id_seq', 5220, true);


--
-- Name: loanadjustments_lad_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.loanadjustments_lad_id_seq', 55, true);


--
-- Name: salorders_sor_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.salorders_sor_id_seq', 3927, true);


--
-- Name: sharesalloc_sha_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.sharesalloc_sha_id_seq', 58, true);


--
-- Name: sharesinstall_shi_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.sharesinstall_shi_id_seq', 197, true);


--
-- Name: subheads_sbh_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.subheads_sbh_id_seq', 159, true);


--
-- Name: transactions_trn_id_seq; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.transactions_trn_id_seq', 5387, true);


--
-- Name: transfers_trf_id_seq1; Type: SEQUENCE SET; Schema: fin; Owner: postgres
--

SELECT pg_catalog.setval('fin.transfers_trf_id_seq1', 135, true);


--
-- Name: firmz_1_frm_id_seq; Type: SEQUENCE SET; Schema: frm; Owner: postgres
--

SELECT pg_catalog.setval('frm.firmz_1_frm_id_seq', 216, true);


--
-- Name: offices_1_off_id_seq; Type: SEQUENCE SET; Schema: frm; Owner: postgres
--

SELECT pg_catalog.setval('frm.offices_1_off_id_seq', 125, true);


--
-- Name: persons_1_per_id_seq; Type: SEQUENCE SET; Schema: frm; Owner: postgres
--

SELECT pg_catalog.setval('frm.persons_1_per_id_seq', 27, true);


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
-- Name: inaattachments_iat_id_seq; Type: SEQUENCE SET; Schema: ina; Owner: postgres
--

SELECT pg_catalog.setval('ina.inaattachments_iat_id_seq', 1, false);


--
-- Name: invatcomps_iac_id_seq; Type: SEQUENCE SET; Schema: ina; Owner: postgres
--

SELECT pg_catalog.setval('ina.invatcomps_iac_id_seq', 8362, true);


--
-- Name: invatlocs_ial_id_seq; Type: SEQUENCE SET; Schema: ina; Owner: postgres
--

SELECT pg_catalog.setval('ina.invatlocs_ial_id_seq', 1, false);


--
-- Name: invats_ias_id_seq; Type: SEQUENCE SET; Schema: ina; Owner: postgres
--

SELECT pg_catalog.setval('ina.invats_ias_id_seq', 13597, true);


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
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 4, true);


--
-- Name: project_activities_pja_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_activities_pja_id_seq', 25, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, false);


--
-- Name: compstat_cst_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.compstat_cst_id_seq', 1486, true);


--
-- Name: noquotes_nqt_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.noquotes_nqt_id_seq', 27, true);


--
-- Name: purattachments_pat_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.purattachments_pat_id_seq', 3044, true);


--
-- Name: purcaseitems_pci_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.purcaseitems_pci_id_seq', 12056, true);


--
-- Name: purcaseminutes_pcm_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.purcaseminutes_pcm_id_seq', 745, true);


--
-- Name: puritems_pri_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.puritems_pri_id_seq', 56, true);


--
-- Name: purreceipt_prt_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.purreceipt_prt_id_seq', 624, true);


--
-- Name: purreceiptitem_pti_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.purreceiptitem_pti_id_seq', 5528, true);


--
-- Name: purreq_prq_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.purreq_prq_id_seq', 37, true);


--
-- Name: quoteitems_qti_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.quoteitems_qti_id_seq', 19445, true);


--
-- Name: quotes_qte_id_seq; Type: SEQUENCE SET; Schema: pur; Owner: postgres
--

SELECT pg_catalog.setval('pur.quotes_qte_id_seq', 2158, true);


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
-- Name: commitments commitments_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.commitments
    ADD CONSTRAINT commitments_pk PRIMARY KEY (cmt_docid, cmt_type);


--
-- Name: commitments commitments_un; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.commitments
    ADD CONSTRAINT commitments_un UNIQUE (cmt_id);


--
-- Name: contractsverif contractsverif_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.contractsverif
    ADD CONSTRAINT contractsverif_pk PRIMARY KEY (cvf_ctr_id);


--
-- Name: empeffheads empsfinhead_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.empeffheads
    ADD CONSTRAINT empsfinhead_pk PRIMARY KEY (eeh_emp_id);


--
-- Name: loanremarks loanremarks_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.loanremarks
    ADD CONSTRAINT loanremarks_pk PRIMARY KEY (lrm_string);


--
-- Name: loanadjustments loanreturns_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.loanadjustments
    ADD CONSTRAINT loanreturns_pk PRIMARY KEY (lad_id);


--
-- Name: msncosts msncosts_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.msncosts
    ADD CONSTRAINT msncosts_pk PRIMARY KEY (mct_msn_idd);


--
-- Name: msncosts msncosts_un; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.msncosts
    ADD CONSTRAINT msncosts_un UNIQUE (mct_prj_id, mct_msn_id);


--
-- Name: salorders salorders_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.salorders
    ADD CONSTRAINT salorders_pk PRIMARY KEY (sor_id);


--
-- Name: salorders_shd salorders_shd_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.salorders_shd
    ADD CONSTRAINT salorders_shd_pk PRIMARY KEY (sod_sor_id, sod_subhead);


--
-- Name: sharesinstall shareinstall_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.sharesinstall
    ADD CONSTRAINT shareinstall_pk PRIMARY KEY (shi_hed_id, shi_fitrn_id);


--
-- Name: sharesalloc sharesalloc_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.sharesalloc
    ADD CONSTRAINT sharesalloc_pk PRIMARY KEY (sha_hed_id, sha_ficmt_id);


--
-- Name: subheads_zzz subheads_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.subheads_zzz
    ADD CONSTRAINT subheads_pk PRIMARY KEY (sbh_hed_id, sbh_name);


--
-- Name: subheads subheads_pk_1; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.subheads
    ADD CONSTRAINT subheads_pk_1 PRIMARY KEY (sbh_hed_id, sbh_name);


--
-- Name: transactions transactions_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.transactions
    ADD CONSTRAINT transactions_pk PRIMARY KEY (trn_id);


--
-- Name: transfers transfers_pk; Type: CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.transfers
    ADD CONSTRAINT transfers_pk PRIMARY KEY (trf_id);


--
-- Name: facils facils_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.facils
    ADD CONSTRAINT facils_pk PRIMARY KEY (fcl_xfrm_id, fcl_facil);


--
-- Name: firmz firmz_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.firmz
    ADD CONSTRAINT firmz_pk PRIMARY KEY (frm_id);


--
-- Name: firmz firmz_un; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.firmz
    ADD CONSTRAINT firmz_un UNIQUE (frm_name);


--
-- Name: info info_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.info
    ADD CONSTRAINT info_pk PRIMARY KEY (inf_xmsc_id, inf_xmsc_entity, inf_type, inf_value);


--
-- Name: offices offices_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.offices
    ADD CONSTRAINT offices_pk PRIMARY KEY (off_id);


--
-- Name: persons persons_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.persons
    ADD CONSTRAINT persons_pk PRIMARY KEY (per_id);


--
-- Name: projects projects_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.projects
    ADD CONSTRAINT projects_pk PRIMARY KEY (prj_xfrm_id, prj_name);


--
-- Name: specs specs_pk; Type: CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.specs
    ADD CONSTRAINT specs_pk PRIMARY KEY (spc_xfrm_id, spc_spec);


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
-- Name: inaattachments intattachments_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.inaattachments
    ADD CONSTRAINT intattachments_pk PRIMARY KEY (iat_id);


--
-- Name: invatcomps invatcomps_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invatcomps
    ADD CONSTRAINT invatcomps_pk PRIMARY KEY (iac_id);


--
-- Name: invatlocs invatlocs_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invatlocs
    ADD CONSTRAINT invatlocs_pk PRIMARY KEY (ial_id);


--
-- Name: invats invats_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invats
    ADD CONSTRAINT invats_pk PRIMARY KEY (ias_id);


--
-- Name: invatspecs invatspecs_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invatspecs
    ADD CONSTRAINT invatspecs_pk PRIMARY KEY (iap_ias_id, iap_specname);


--
-- Name: invenitems invenitems_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invenitems
    ADD CONSTRAINT invenitems_pk PRIMARY KEY (ini_id);


--
-- Name: inventory inventory_pk; Type: CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.inventory
    ADD CONSTRAINT inventory_pk PRIMARY KEY (inv_id);


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
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: project_activities project_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project_activities
    ADD CONSTRAINT project_activities_pkey PRIMARY KEY (pja_id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: purcases compstat_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcases
    ADD CONSTRAINT compstat_pk PRIMARY KEY (pcs_id);


--
-- Name: purreqitems items_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreqitems
    ADD CONSTRAINT items_pk PRIMARY KEY (pri_prq_id, pri_serial);


--
-- Name: noquotes noquotes_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.noquotes
    ADD CONSTRAINT noquotes_pk PRIMARY KEY (nqt_id);


--
-- Name: noquotes noquotes_unique; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.noquotes
    ADD CONSTRAINT noquotes_unique UNIQUE (nqt_pcs_id, nqt_frm_id);


--
-- Name: purattachments purattachments_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purattachments
    ADD CONSTRAINT purattachments_pk PRIMARY KEY (pat_id);


--
-- Name: purcaseitems purcaseitems_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseitems
    ADD CONSTRAINT purcaseitems_pk PRIMARY KEY (pci_serial, pci_pcs_id);


--
-- Name: purcaseitems purcaseitems_un; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseitems
    ADD CONSTRAINT purcaseitems_un UNIQUE (pci_id);


--
-- Name: purcaseminutes purcaseminutes_un; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseminutes
    ADD CONSTRAINT purcaseminutes_un UNIQUE (pcm_purcases);


--
-- Name: purcases_shd purcases_shd_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcases_shd
    ADD CONSTRAINT purcases_shd_pk PRIMARY KEY (pcd_pcs_id, pcd_subhead);


--
-- Name: purcaseminutes purcasesminutes_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseminutes
    ADD CONSTRAINT purcasesminutes_pk PRIMARY KEY (pcm_id);


--
-- Name: purcaseminuterefs purcasetextref_pk_1; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseminuterefs
    ADD CONSTRAINT purcasetextref_pk_1 PRIMARY KEY (pmr_pcm_id, pmr_min);


--
-- Name: purreqitems puritems_un; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreqitems
    ADD CONSTRAINT puritems_un UNIQUE (pri_id);


--
-- Name: purreceiptitems purreceiptitems_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreceiptitems
    ADD CONSTRAINT purreceiptitems_pk PRIMARY KEY (pti_prt_id, pti_serial);


--
-- Name: purreceiptitems purreceiptitems_un; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreceiptitems
    ADD CONSTRAINT purreceiptitems_un UNIQUE (pti_id);


--
-- Name: purreceipts purreceipts_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreceipts
    ADD CONSTRAINT purreceipts_pk PRIMARY KEY (prt_id);


--
-- Name: purreqs purreqs_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreqs
    ADD CONSTRAINT purreqs_pk PRIMARY KEY (prq_id);


--
-- Name: quoteitems quoteitems_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.quoteitems
    ADD CONSTRAINT quoteitems_pk PRIMARY KEY (qti_serial, qti_qte_id);


--
-- Name: quoteitems quoteitems_un; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.quoteitems
    ADD CONSTRAINT quoteitems_un UNIQUE (qti_id);


--
-- Name: quotes quotes_pk; Type: CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.quotes
    ADD CONSTRAINT quotes_pk PRIMARY KEY (qte_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


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
-- Name: salorders_shd salorders_shd_fk; Type: FK CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.salorders_shd
    ADD CONSTRAINT salorders_shd_fk FOREIGN KEY (sod_sor_id) REFERENCES fin.salorders(sor_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sharesalloc sharesalloc_fk; Type: FK CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.sharesalloc
    ADD CONSTRAINT sharesalloc_fk FOREIGN KEY (sha_ficmt_id) REFERENCES fin.commitments(cmt_id);


--
-- Name: sharesinstall sharesinstall_fk; Type: FK CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.sharesinstall
    ADD CONSTRAINT sharesinstall_fk FOREIGN KEY (shi_fitrn_id) REFERENCES fin.transactions(trn_id);


--
-- Name: transactions transactions_fk; Type: FK CONSTRAINT; Schema: fin; Owner: postgres
--

ALTER TABLE ONLY fin.transactions
    ADD CONSTRAINT transactions_fk FOREIGN KEY (trn_cmt_id) REFERENCES fin.commitments(cmt_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: facils facils_fk; Type: FK CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.facils
    ADD CONSTRAINT facils_fk FOREIGN KEY (fcl_xfrm_id) REFERENCES frm.firmz(frm_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: offices offices_fk; Type: FK CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.offices
    ADD CONSTRAINT offices_fk FOREIGN KEY (off_xfrm_id) REFERENCES frm.firmz(frm_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: persons persons_fk; Type: FK CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.persons
    ADD CONSTRAINT persons_fk FOREIGN KEY (per_xfrm_id) REFERENCES frm.firmz(frm_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: projects projects_fk; Type: FK CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.projects
    ADD CONSTRAINT projects_fk FOREIGN KEY (prj_xfrm_id) REFERENCES frm.firmz(frm_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: specs specs_fk; Type: FK CONSTRAINT; Schema: frm; Owner: postgres
--

ALTER TABLE ONLY frm.specs
    ADD CONSTRAINT specs_fk FOREIGN KEY (spc_xfrm_id) REFERENCES frm.firmz(frm_id) ON UPDATE CASCADE ON DELETE CASCADE;


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
-- Name: invatcomps invatcomps_fk; Type: FK CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invatcomps
    ADD CONSTRAINT invatcomps_fk FOREIGN KEY (iac_ias_id) REFERENCES ina.invats(ias_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: invatlocs invatlocs_fk; Type: FK CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invatlocs
    ADD CONSTRAINT invatlocs_fk FOREIGN KEY (ial_ias_id) REFERENCES ina.invats(ias_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: invatspecs invatspecs_fk; Type: FK CONSTRAINT; Schema: ina; Owner: postgres
--

ALTER TABLE ONLY ina.invatspecs
    ADD CONSTRAINT invatspecs_fk FOREIGN KEY (iap_ias_id) REFERENCES ina.invats(ias_id) ON UPDATE CASCADE ON DELETE CASCADE;


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
-- Name: purreqitems items_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreqitems
    ADD CONSTRAINT items_fk FOREIGN KEY (pri_prq_id) REFERENCES pur.purreqs(prq_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: noquotes noquotes_purcases_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.noquotes
    ADD CONSTRAINT noquotes_purcases_fk FOREIGN KEY (nqt_pcs_id) REFERENCES pur.purcases(pcs_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purcaseitems purcaseitems_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseitems
    ADD CONSTRAINT purcaseitems_fk FOREIGN KEY (pci_pcs_id) REFERENCES pur.purcases(pcs_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purcaseitems purcaseitems_fk1; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseitems
    ADD CONSTRAINT purcaseitems_fk1 FOREIGN KEY (pci_pri_id) REFERENCES pur.purreqitems(pri_id);


--
-- Name: purcaseminuterefs purcaseminuterefs_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcaseminuterefs
    ADD CONSTRAINT purcaseminuterefs_fk FOREIGN KEY (pmr_pcm_id) REFERENCES pur.purcaseminutes(pcm_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purcases_shd purcases_shd_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purcases_shd
    ADD CONSTRAINT purcases_shd_fk FOREIGN KEY (pcd_pcs_id) REFERENCES pur.purcases(pcs_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purreceiptitems purreceiptitems_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreceiptitems
    ADD CONSTRAINT purreceiptitems_fk FOREIGN KEY (pti_prt_id) REFERENCES pur.purreceipts(prt_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purreceiptitems purreceiptitems_fk_1; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.purreceiptitems
    ADD CONSTRAINT purreceiptitems_fk_1 FOREIGN KEY (pti_pci_id) REFERENCES pur.purcaseitems(pci_id);


--
-- Name: quoteitems quoteitems_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.quoteitems
    ADD CONSTRAINT quoteitems_fk FOREIGN KEY (qti_qte_id) REFERENCES pur.quotes(qte_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: quotes quotes_fk; Type: FK CONSTRAINT; Schema: pur; Owner: postgres
--

ALTER TABLE ONLY pur.quotes
    ADD CONSTRAINT quotes_fk FOREIGN KEY (qte_pcs_id) REFERENCES pur.purcases(pcs_id);


--
-- PostgreSQL database dump complete
--

