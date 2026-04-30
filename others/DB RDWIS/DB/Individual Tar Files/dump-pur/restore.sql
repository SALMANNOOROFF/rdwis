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
-- Name: pur; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA pur;


ALTER SCHEMA pur OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

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
-- Data for Name: noquotes; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.noquotes (nqt_id, nqt_pcs_id, nqt_frm_id) FROM stdin;
\.
COPY pur.noquotes (nqt_id, nqt_pcs_id, nqt_frm_id) FROM '$$PATH$$/5322.dat';

--
-- Data for Name: purattachments; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purattachments (pat_objtype, pat_objid, pat_type, pat_path, pat_id) FROM stdin;
\.
COPY pur.purattachments (pat_objtype, pat_objid, pat_type, pat_path, pat_id) FROM '$$PATH$$/5324.dat';

--
-- Data for Name: purcaseitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcaseitems (pci_pri_id, pci_serial, pci_desc, pci_estprice, pci_qty, pci_qtyunit, pci_id, pci_pcs_id, pci_price, pci_fulfilment, pci_type, pci_subtype, pci_category, pci_type2, pci_subhead) FROM stdin;
\.
COPY pur.purcaseitems (pci_pri_id, pci_serial, pci_desc, pci_estprice, pci_qty, pci_qtyunit, pci_id, pci_pcs_id, pci_price, pci_fulfilment, pci_type, pci_subtype, pci_category, pci_type2, pci_subhead) FROM '$$PATH$$/5326.dat';

--
-- Data for Name: purcaseminuterefs; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcaseminuterefs (pmr_pcm_id, pmr_min, pmr_title, pmr_date, pmr_from, pmr_flag, pmr_ref, pmr_encl) FROM stdin;
\.
COPY pur.purcaseminuterefs (pmr_pcm_id, pmr_min, pmr_title, pmr_date, pmr_from, pmr_flag, pmr_ref, pmr_encl) FROM '$$PATH$$/5328.dat';

--
-- Data for Name: purcaseminutes; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcaseminutes (pcm_id, pcm_texta, pcm_textb, pcm_textc, pcm_textd, pcm_texte, pcm_lwoamount, pcm_minute, pcm_purcases, pcm_eqdue, pcm_hrdue, pcm_msdue, pcm_date) FROM stdin;
\.
COPY pur.purcaseminutes (pcm_id, pcm_texta, pcm_textb, pcm_textc, pcm_textd, pcm_texte, pcm_lwoamount, pcm_minute, pcm_purcases, pcm_eqdue, pcm_hrdue, pcm_msdue, pcm_date) FROM '$$PATH$$/5329.dat';

--
-- Data for Name: purcases; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcases (pcs_id, pcs_date, pcs_remarks, pcs_status, pcs_unt_id, pcs_hed_id, pcs_purreqs, pcs_releasedtg, pcs_effhed_id, pcs_intprice, pcs_inttax, pcs_midprice, pcs_midtax, pcs_price, pcs_title, pcs_frm_id, pcs_type, pcs_effunt_id, pcs_intunt_id, pcs_transtype, pcs_noloan, pcs_sudohed, pcs_minute, pcs_recomm, pcs_quotetype, pcs_approvedtg, pcs_closedtg) FROM stdin;
\.
COPY pur.purcases (pcs_id, pcs_date, pcs_remarks, pcs_status, pcs_unt_id, pcs_hed_id, pcs_purreqs, pcs_releasedtg, pcs_effhed_id, pcs_intprice, pcs_inttax, pcs_midprice, pcs_midtax, pcs_price, pcs_title, pcs_frm_id, pcs_type, pcs_effunt_id, pcs_intunt_id, pcs_transtype, pcs_noloan, pcs_sudohed, pcs_minute, pcs_recomm, pcs_quotetype, pcs_approvedtg, pcs_closedtg) FROM '$$PATH$$/5320.dat';

--
-- Data for Name: purcases_shd; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purcases_shd (pcd_pcs_id, pcd_subhead, pcd_ratio, pcd_type) FROM stdin;
\.
COPY pur.purcases_shd (pcd_pcs_id, pcd_subhead, pcd_ratio, pcd_type) FROM '$$PATH$$/5331.dat';

--
-- Data for Name: purreceiptitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreceiptitems (pti_id, pti_prt_id, pti_desc, pti_qty, pti_qtyunit, pti_pci_id, pti_pri_id, pti_serial) FROM stdin;
\.
COPY pur.purreceiptitems (pti_id, pti_prt_id, pti_desc, pti_qty, pti_qtyunit, pti_pci_id, pti_pri_id, pti_serial) FROM '$$PATH$$/5336.dat';

--
-- Data for Name: purreceipts; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreceipts (prt_id, prt_date, prt_unt_id, prt_prj_id, prt_status, prt_pcs_id, prt_dtg) FROM stdin;
\.
COPY pur.purreceipts (prt_id, prt_date, prt_unt_id, prt_prj_id, prt_status, prt_pcs_id, prt_dtg) FROM '$$PATH$$/5334.dat';

--
-- Data for Name: purreqitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreqitems (pri_prq_id, pri_serial, pri_desc, pri_price, pri_qty, pri_qtyunit, pri_type, pri_category, pri_id, pri_fulfilment, pri_appqty, pri_remarks, pri_subtype) FROM stdin;
\.
COPY pur.purreqitems (pri_prq_id, pri_serial, pri_desc, pri_price, pri_qty, pri_qtyunit, pri_type, pri_category, pri_id, pri_fulfilment, pri_appqty, pri_remarks, pri_subtype) FROM '$$PATH$$/5332.dat';

--
-- Data for Name: purreqs; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.purreqs (prq_id, prq_date, prq_unt_id, prq_hed_id, prq_status, prq_fulfilled, prq_dtg, prq_effhed_id, prq_appeffhed_id, prq_intunt_id, prq_desc, prq_minute) FROM stdin;
\.
COPY pur.purreqs (prq_id, prq_date, prq_unt_id, prq_hed_id, prq_status, prq_fulfilled, prq_dtg, prq_effhed_id, prq_appeffhed_id, prq_intunt_id, prq_desc, prq_minute) FROM '$$PATH$$/5338.dat';

--
-- Data for Name: quoteitems; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.quoteitems (qti_qte_id, qti_serial, qti_desc, qti_price, qti_qty, qti_qtyunit, qti_id, qti_pcsdesc, qti_pci_id) FROM stdin;
\.
COPY pur.quoteitems (qti_qte_id, qti_serial, qti_desc, qti_price, qti_qty, qti_qtyunit, qti_id, qti_pcsdesc, qti_pci_id) FROM '$$PATH$$/5340.dat';

--
-- Data for Name: quotes; Type: TABLE DATA; Schema: pur; Owner: postgres
--

COPY pur.quotes (qte_date, qte_firmname, qte_pcs_id, qte_num, qte_id, qte_price, qte_frm_id, qte_techaccept, qte_recomm, qte_midprice, qte_midtax, qte_intprice, qte_inttax, qte_quotetype) FROM stdin;
\.
COPY pur.quotes (qte_date, qte_firmname, qte_pcs_id, qte_num, qte_id, qte_price, qte_frm_id, qte_techaccept, qte_recomm, qte_midprice, qte_midtax, qte_intprice, qte_inttax, qte_quotetype) FROM '$$PATH$$/5342.dat';

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

