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
-- Name: fin; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA fin;


ALTER SCHEMA fin OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

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
-- Data for Name: commitments; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.commitments (cmt_id, cmt_docid, cmt_type, cmt_date, cmt_amount, cmt_status, cmt_effhed_id, cmt_effunt_id, cmt_hed_id, cmt_unt_id, cmt_sudohed) FROM stdin;
\.
COPY fin.commitments (cmt_id, cmt_docid, cmt_type, cmt_date, cmt_amount, cmt_status, cmt_effhed_id, cmt_effunt_id, cmt_hed_id, cmt_unt_id, cmt_sudohed) FROM '$$PATH$$/5293.dat';

--
-- Data for Name: contractsverif; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.contractsverif (cvf_ctr_id, cvf_verif, cvf_dtg) FROM stdin;
\.
COPY fin.contractsverif (cvf_ctr_id, cvf_verif, cvf_dtg) FROM '$$PATH$$/5295.dat';

--
-- Data for Name: empeffheads; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.empeffheads (eeh_emp_id, eeh_emphed_id, eeh_dtg, eeh_status, eeh_remarks, eeh_sudohed) FROM stdin;
\.
COPY fin.empeffheads (eeh_emp_id, eeh_emphed_id, eeh_dtg, eeh_status, eeh_remarks, eeh_sudohed) FROM '$$PATH$$/5296.dat';

--
-- Data for Name: loanadjustments; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.loanadjustments (lad_string, lad_amount, lad_remarks, lad_id, lad_dtg, lad_from, lad_to) FROM stdin;
\.
COPY fin.loanadjustments (lad_string, lad_amount, lad_remarks, lad_id, lad_dtg, lad_from, lad_to) FROM '$$PATH$$/5297.dat';

--
-- Data for Name: loanremarks; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.loanremarks (lrm_string, lrm_remarks) FROM stdin;
\.
COPY fin.loanremarks (lrm_string, lrm_remarks) FROM '$$PATH$$/5299.dat';

--
-- Data for Name: msncosts; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.msncosts (mct_prj_id, mct_hed_id, mct_msn_id, mct_cost, mct_msn_idd) FROM stdin;
\.
COPY fin.msncosts (mct_prj_id, mct_hed_id, mct_msn_id, mct_cost, mct_msn_idd) FROM '$$PATH$$/5300.dat';

--
-- Data for Name: salorders; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.salorders (sor_id, sor_hed_id, sor_releasedtg, sor_status, sor_remarks, sor_srq_id, sor_closedtg, sor_unt_id, sor_month, sor_netsalary, sor_salary, sor_emp_id, sor_effhed_id, sor_empnamecomp, sor_bnkacctitle, sor_effunt_id, sor_bnkaccdetail, sor_ctrsalary, sor_checked, sor_contracts, sor_noloan, sor_transtype, sor_sudohed, sor_remarks2, sor_type, sor_grosalary, sor_arrears, sor_dues, sor_overwork, sor_underwork, sor_loaned, sor_withheld, sor_award, sor_penalty, sor_paidalready) FROM stdin;
\.
COPY fin.salorders (sor_id, sor_hed_id, sor_releasedtg, sor_status, sor_remarks, sor_srq_id, sor_closedtg, sor_unt_id, sor_month, sor_netsalary, sor_salary, sor_emp_id, sor_effhed_id, sor_empnamecomp, sor_bnkacctitle, sor_effunt_id, sor_bnkaccdetail, sor_ctrsalary, sor_checked, sor_contracts, sor_noloan, sor_transtype, sor_sudohed, sor_remarks2, sor_type, sor_grosalary, sor_arrears, sor_dues, sor_overwork, sor_underwork, sor_loaned, sor_withheld, sor_award, sor_penalty, sor_paidalready) FROM '$$PATH$$/5301.dat';

--
-- Data for Name: salorders_shd; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.salorders_shd (sod_sor_id, sod_type, sod_subhead, sod_ratio) FROM stdin;
\.
COPY fin.salorders_shd (sod_sor_id, sod_type, sod_subhead, sod_ratio) FROM '$$PATH$$/5302.dat';

--
-- Data for Name: sharesalloc; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.sharesalloc (sha_hed_id, sha_ficmt_id, sha_focmt_id, sha_transtype, sha_id, sha_cf, sha_pcc, sha_prj, sha_prj_sal, sha_prj_pur) FROM stdin;
\.
COPY fin.sharesalloc (sha_hed_id, sha_ficmt_id, sha_focmt_id, sha_transtype, sha_id, sha_cf, sha_pcc, sha_prj, sha_prj_sal, sha_prj_pur) FROM '$$PATH$$/5304.dat';

--
-- Data for Name: sharesinstall; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.sharesinstall (shi_hed_id, shi_fitrn_id, shi_fotrn_id, shi_id, shi_cf, shi_pcc, shi_msn_idd, shi_prj) FROM stdin;
\.
COPY fin.sharesinstall (shi_hed_id, shi_fitrn_id, shi_fotrn_id, shi_id, shi_cf, shi_pcc, shi_msn_idd, shi_prj) FROM '$$PATH$$/5306.dat';

--
-- Data for Name: subheads; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.subheads (sbh_hed_id, sbh_name, sbh_alloc, sbh_id) FROM stdin;
\.
COPY fin.subheads (sbh_hed_id, sbh_name, sbh_alloc, sbh_id) FROM '$$PATH$$/5308.dat';

--
-- Data for Name: subheads_zzz; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.subheads_zzz (sbh_hed_id, sbh_name, sbh_alloc) FROM stdin;
\.
COPY fin.subheads_zzz (sbh_hed_id, sbh_name, sbh_alloc) FROM '$$PATH$$/5310.dat';

--
-- Data for Name: transactions; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.transactions (trn_id, trn_cmt_id, trn_date, trn_amount1, trn_balance, trn_seq, trn_tax1, trn_amount2, trn_transtype, trn_noloan) FROM stdin;
\.
COPY fin.transactions (trn_id, trn_cmt_id, trn_date, trn_amount1, trn_balance, trn_seq, trn_tax1, trn_amount2, trn_transtype, trn_noloan) FROM '$$PATH$$/5311.dat';

--
-- Data for Name: transfers; Type: TABLE DATA; Schema: fin; Owner: postgres
--

COPY fin.transfers (trf_id, trf_date, trf_type, trf_title, trf_amount, trf_fromhed, trf_fromunt, trf_tohed, trf_tount, trf_status) FROM stdin;
\.
COPY fin.transfers (trf_id, trf_date, trf_type, trf_title, trf_amount, trf_fromhed, trf_fromunt, trf_tohed, trf_tount, trf_status) FROM '$$PATH$$/5313.dat';

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
-- PostgreSQL database dump complete
--

