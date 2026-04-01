--
-- PostgreSQL database dump
--

\restrict tVH5KsjAv3vQlpoe3004BvJMKbrKxHF5j7yLfeNMFXNd9HHQq0L250R7e6QfNqQ

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-01-19 08:51:30

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
-- TOC entry 7 (class 2615 OID 27891)
-- Name: cen; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA cen;


ALTER SCHEMA cen OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 237 (class 1259 OID 27966)
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
-- TOC entry 238 (class 1259 OID 27993)
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
-- TOC entry 239 (class 1259 OID 27999)
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
-- TOC entry 240 (class 1259 OID 28009)
-- Name: levels; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.levels (
    lvl_id smallint NOT NULL,
    lvl_cat character varying(255)
);


ALTER TABLE cen.levels OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 28013)
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
-- TOC entry 242 (class 1259 OID 28023)
-- Name: routes; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.routes (
    rte_doc character varying(255) NOT NULL,
    rte_steps character varying(255) NOT NULL
);


ALTER TABLE cen.routes OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 28030)
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
-- TOC entry 244 (class 1259 OID 28043)
-- Name: version; Type: TABLE; Schema: cen; Owner: postgres
--

CREATE TABLE cen.version (
    ver_version numeric NOT NULL,
    ver_compat numeric
);


ALTER TABLE cen.version OWNER TO postgres;

--
-- TOC entry 5269 (class 0 OID 27966)
-- Dependencies: 237
-- Data for Name: accounts; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.accounts (acc_id, acc_unt_id, acc_level, acc_type, acc_reportlevel, acc_username, acc_pass, acc_startdt, acc_status, acc_enddt, acc_name, acc_title, acc_rank, acc_desig, acc_desigshort, acc_desigtype, acc_lowerm, acc_upperm, acc_access, acc_lowers, acc_uppers, acc_untname, acc_untnamesh, acc_unttype, acc_auth, acc_untarea) FROM stdin;
29	820000	25	Standard	39	aali	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2021-09-06 09:35:26+05	Closed	\N	Asif Ali	\N	Lt Cdr PN	Manager HR	MHR	lead	160000	999999	multiple	820000	839999	Human Resource Department	HR	Department	approver	hr
32	840000	25	Standard	39	nislam	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-12-31 17:49:59+05	Closed	\N	Nasir Islam	\N	Lt PN	Admin Officer	Admin Officer	lead	160000	999999	single	840000	859999	Administration Department	Admin	Department	approver	adm
14	200000	19	Standard	39	amushtaq	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:37:29+05	Active	\N	Aleem Mushtaq	Dr	Captain PN	Director Communication	DCom	lead	160000	999999	single	200000	249999	Communication Division	Comm	Division	approver	prj
15	250000	19	Standard	39	hraza	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:38:34+05	Active	\N	Rana Hammad Raza	Dr	Captain PN	Director Enabling Technology	DEnab	lead	160000	999999	single	250000	299999	Enabling Technology Division	Enab	Division	approver	prj
18	400000	19	Standard	39	ssaleem	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:41:00+05	Active	\N	Sajid Saleem	Dr	Captain PN	Director System of Systems	DSOS	lead	160000	999999	single	400000	449999	System of Systems Division	SoS	Division	approver	prj
21	160000	29	Standard	49	jhussain	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:58:23+05	Closed	\N	Junaid Hussain	Dr	Commodore	Managing Director	MD	lead	160000	999999	multiple	160000	179999	Research and Development Wing	RDW	Wing	approver	rdw
24	820000	25	Standard	39	mahmed	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-10-09 11:46:37+05	Closed	\N	Mansoor Ahmed	\N	\N	Manager HR	MHR	lead	160000	999999	multiple	820000	839999	Human Resource Department	HR	Department	approver	hr
27	800000	24	Standard	29	msaleem	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-12-14 16:08:00+05	Closed	2022-06-01 11:21:09.515+05	Muhammad Saleem	\N	\N	Accounts Officer	AccOfficer	staff	160000	999999	multiple	800000	819999	Finance Department	Finance	Department	approver	fin
33	180000	25	Standard	39	sjaved	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-03-28 10:29:30+05	Closed	2022-05-30 11:37:30+05	Sameer Javed	\N	Lt Cdr PN	Director R&D	SO R&D	lead	160000	999999	multiple	160000	179999	Research and Development Wing	RDW	Wing	approver	rdwprj
34	860000	25	Standard	39	amustafa2	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-05-17 16:09:16+05	Closed	2022-06-02 13:17:46.892+05	Adnan Mustafa	\N	Lt Cdr (R)	System Admin	Sys Admin	dptlead	100000	999999	multiple	860000	879999	Information Technology Department	IT	Department	approver	it
35	800000	24	Standard	29	atanveer	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-06-01 11:39:16.417+05	Active	\N	Ammara Tanveer	\N	Lt PN	Staff Officer Finance	SO-Finance	staff	160000	999999	multiple	800000	819999	Finance Department	Finance	Department	approver	fin
28	840000	25	Standard	39	massad	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-12-31 17:49:59+05	Active	\N	Muhammad Assad	\N	Lt Cdr PN	Admin Officer	Admin Officer	lead	160000	999999	single	840000	859999	Administration Department	Admin	Department	approver	adm
16	300000	19	Standard	39	amemon	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:39:19+05	Active	\N	Attaullah Memon	Dr	Commodore	Director Naval Weapon Systems	DNWS	lead	160000	999999	single	300000	349999	Naval Weapons System Division	NWS	Division	approver	prj
22	880000	25	Standard	39	amustafa	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-18 09:35:28+05	Active	\N	Adnan Mustafa	\N	Lt Cdr (R)	Manager IS	MIS	lead	160000	999999	single	880000	899999	Information System Department	IS	Department	approver	is
17	350000	19	Standard	39	tmairaj	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:40:11+05	Active	\N	Tariq Mairaj Rasool	Dr	Commodore	Director Sensors	DSensors	lead	160000	999999	single	350000	399999	Sensors Division	Sensors	Division	approver	prj
30	160000	29	Standard	49	famir	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2021-10-13 14:34:00+05	Closed	2023-07-20 10:25:13+05	Faisal Amir SI(M)	Dr	Commodore	Managing Director	MD	lead	160000	999999	multiple	0	0	Research and Development Wing	RDW	Wing	viewer	rdw
40	100000	49	Standard	\N	sarshad	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-12-02 15:58:13.306+05	Closed	\N	Sohail Arshad HI(M)	\N	R/Admiral	Director General	DG NRDI	lead	100000	999999	multiple	0	0	Naval Research & Developement Institute	NRDI	Organization	viewer	nrdi
19	450000	19	Standard	39	shaider	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2020-05-13 10:43:28+05	Active	\N	Sajjad Haider Zaidi	Dr	Cdre	PI Systems	DSystems	lead	160000	999999	single	450000	499999	Systems Division	Sys	Division	approver	prj
37	860000	25	Standard	39	oshuja	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-06-02 13:05:33.947+05	Active	\N	Osama Shuja		Lt PN	Staff Officer IT&CYS	SO IT&CYS	dptlead	160000	999999	multiple	860000	879999	Information Technology Department	IT	Department	approver	it
38	800000	24	Standard	29	araza	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-08-26 13:09:36.175+05	Closed	2023-11-06 09:59:37.647+05	Syed Arif Raza Zaidi	\N	Lt Cdr (R)	Director Finance	DFin	head	160000	999999	multiple	800000	819999	Finance Department	Finance	Department	viewer	fin
39	160000	29	Standard	49	jkhan	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-11-16 15:35:07.937+05	Closed	2023-11-10 15:50:33+05	Junaid Khan	Dr	Commodore	Managing Director	MD	lead	160000	999999	multiple	0	0	Research and Development Wing	RDW	Wing	viewer	rdw
45	800000	24	Standard	29	frahman	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2023-11-07 10:15:07+05	Active	\N	Fazal ur Rahman	\N	Cdr (R)	Director Finance	DFin	head	160000	999999	multiple	800000	819999	Finance Department	Finance	Department	approver	fin
42	160000	29	Standard	49	famir2	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2023-07-20 10:30:48+05	Active	\N	Faisal Amir SI(M)	Dr	Commodore	Subject Matter Expert	SME	lead	160000	999999	multiple	0	0	Research and Development Wing	RDW	Wing	viewer	rdw
49	160000	29	Standard	49	srehman	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2024-05-17 09:21:15+05	Active	\N	Shafiq ur Rehman SI(M)	Dr	Commodore	Managing Director	MD	lead	160000	999999	multiple	0	0	Research And Design	RDW	Wing	viewer	rdw
46	100000	49	Standard	\N	mhussain	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2023-12-21 09:26:49.98+05	Active	\N	Muhammad Hussain Sial SI(M)	\N	R/Admiral	Director General	DG NRDI	lead	100000	999999	multiple	0	0	Naval Research & Developement Institute	NRDI	Organization	viewer	nrdi
31	820000	25	Standard	39	smoin	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-02-01 13:43:09.343+05	Active	\N	Sana Moin	\N	Lt Cdr	Staff Officer HR	SO-HR	lead	160000	999999	multiple	820000	839999	Human Resource Department	HR	Department	approver	hr
51	450000	19	Standard	39	iahmed	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2024-06-12 09:32:44.648+05	Active	\N	Iftikhar Ahmad	Dr	Cdr	Director Systems	DSystems	lead	160000	999999	single	450000	499999	Systems Division	Sys	Division	approver	prj
36	180000	25	Standard	39	nislam2	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2022-06-02 12:04:54+05	Active	\N	Nasir Islam	\N	Lt Cdr PN	Staff Officer R&D	SO R&D	staff	160000	999999	multiple	160000	179999	Research and Development Wing	RDW	Wing	approver	rdwprj
48	100000	35	Standard	49	zaltaf	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2024-01-05 12:53:15.118+05	Active	\N	Zarrar Bin Altaf		Cdr	Director Planning And Coordination	DP&C	lead	100000	999999	multiple	0	0	Naval Research & Developement Institute	NRDI	Organization	viewer	nrdi
47	160000	29	Standard	49	tmairaj2	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2024-01-04 10:16:07.588+05	Closed	2024-05-17 09:17:41+05	Tariq Mairaj Rasool	Dr	Commodore	Managing Director	MD	lead	160000	999999	multiple	0	0	Research and Development Wing	RDW	Wing	viewer	rdw
50	990000	19	Standard	39	mtanveer	$2y$12$6cL4ZqW2Sgw22uPxSm9bAOEtbEa.v1IsCuEVdNz0VYOsGoh0z9l2i	2024-06-05 11:14:01.942+05	Active	\N	Muhammad Tanveer	\N		MTSS Representative	MTSS Rep	lead	160000	999999	multiple	0	0	Maritime Technology Security Services	MTSS	External	viewer	mtss
\.


--
-- TOC entry 5270 (class 0 OID 27993)
-- Dependencies: 238
-- Data for Name: globalvars; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.globalvars (gvar_name, gvar_value, gvar_type, gvar_remarks) FROM stdin;
attstart_for_pay	26		Attendance start day of a month for salary generation.
mpr_route	-19-(25)-		MPR route.
salhead_applicable	False	boolean	Salary head applied for project employees.
salreq_allow_multiple	26 JUN 24		Date when generation of multiple salary requisitions for an employee is allowed.
att_disregard	26 JUN 24		Date when the attendance is disregarded for salary requisition generation.
\.


--
-- TOC entry 5271 (class 0 OID 27999)
-- Dependencies: 239
-- Data for Name: heads; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.heads (hed_id, hed_name, hed_type, hed_code, hed_opendt, hed_closedt, hed_transtype, hed_unt_id, hed_prj_id) FROM stdin;
100000	Navy Research and Development Institute	Institute	NRDI	2001-01-01	\N	\N	100000	\N
160000	Research and Development Wing	Wing	RDW	2001-01-01	\N	\N	160000	\N
180000	Projects Department	Department	PROJECTS	2001-01-01	\N	\N	180000	\N
200000	Communication Division	Division	COMM	2001-01-01	\N	\N	200000	\N
250000	Enabling Technology Division	Division	ENAB	2001-01-01	\N	\N	250000	\N
300000	Naval Weapons System Division	Division	NWS	2001-01-01	\N	\N	300000	\N
350000	Sensors Division	Division	SENSORS	2001-01-01	\N	\N	350000	\N
400000	System of Systems Division	Division	SOS	2001-01-01	\N	\N	400000	\N
450000	Systems Division	Division	SYS	2001-01-01	\N	\N	450000	\N
800000	Finance Department	Department	FIN	2001-01-01	\N	\N	800000	\N
820000	Human Resource Department	Department	HR	2001-01-01	\N	\N	820000	\N
840000	Administration Department	Department	ADMIN	2001-01-01	\N	\N	840000	\N
860000	Information Technology Department	Department	IT	2001-01-01	\N	\N	860000	\N
880000	Information System Department	Department	IS	2001-01-01	\N	\N	880000	\N
990000	External	External	EXT	2001-01-01	\N	\N	990000	\N
990001	Project Funder	ExtHead	PFND	2001-01-01	\N	\N	990000	\N
990009	Other Source	ExtHead	OSRC	2001-01-01	\N	\N	990000	\N
990011	MTSS	ExtHead	MTSS	2001-01-01	\N	\N	990000	\N
990019	Other Consumer	ExtHead	OCNM	2001-01-01	\N	\N	990000	\N
200007	Development of NIU for PNS/M HAMZA	Project	NIU-HZ	2021-10-20	\N	1	200000	200007
200001	Metgraph	Project	MET	2001-01-01	\N	1	200000	200001
200003	Indigenous development of NIU for PNS/M HURMAT	Project	NIU	2019-06-20	\N	1	200000	200003
200004	Overhaul of ELINT System of RDS Nathiagali	Project	ELINT	2019-10-01	\N	2	200000	200004
200008	Functional Replacement of CRTs of CDS with LED Displays for PNS ALAMGIR	Project	CDS	2021-10-20	\N	1	200000	200008
250001	Video Analytics	Project	VA	2016-07-15	\N	2	250000	250001
250002	Establishment of Virtual Reality Center	Project	VRC	2020-08-27	\N	2	250000	250002
300001	Indigenous Development of ROV for UW Operations onboard MCMVs	Project	ROV	2019-09-03	\N	2	300000	300001
300002	Unmanned Underwater Vehicle	Project	UUV	2010-04-17	2020-04-05	2	300000	300002
350002	Eagle Hunt - UAVs GPS jamming and spoofing	Project	EH	2018-02-01	\N	1	350000	350002
350003	Directed Energy Weapons	Project	MC	2020-02-12	\N	2	350000	350003
350004	Medium Altitude Long Endurance Unmanned Combat Aerial Vehicle (Male UCAV)	Project	UCAV	2020-02-12	\N	2	350000	350004
350005	High Speed Target Drone	Project	HSTD	2020-02-12	\N	2	350000	350005
350010	Indigenous Development of VFDs onboard Agosta 90B Submarine	Project	VFD	2021-01-01	\N	1	350000	350010
350012	Remaining Useful life estimation of in-service systems onboard PN ships	Project	RUL	2021-07-01	\N	1	350000	350012
400001	Supersonic metal spray for submarines	Project	SMSRS	2001-01-01	\N	2	400000	400001
350014	RAVEN-2	Project	RAV-II	2001-01-01	\N	1	350000	350014
200006	Development of NIU for PNS/M HASHMAT	Project	NIU-HS	2021-10-20	\N	1	200000	200006
350006	High Speed Target Drone 2	Project	HSTD-II	2021-12-15	\N	1	350000	350006
350015	Directed Energy Weapons 2	Project	MC-II	2021-12-15	\N	1	350000	350015
300003	ROV-II	Project	ROV-II	2022-02-02	\N	1	300000	300003
350018	xxx	Project	SUR	2022-04-12	\N	1	350000	350018
200009	ELINT-II	Project	ELINT-II	2022-02-09	\N	1	200000	200009
350009	Eagle Hunt-01	Project	EH1	2020-02-11	\N	1	350000	350009
350013	RAVEN-1	Project	RAV-I	2001-01-01	\N	1	350000	350013
200011	xxx	Project	METLP	2022-11-24	\N	1	200000	200011
200012	xxx	Project	DADSS	2022-12-28	\N	1	200000	200012
350022	xxx	Project	BSE-D	2023-03-27	\N	1	350000	350022
350023	xxx	Project	BSE-F	2023-03-27	\N	1	350000	350023
350021	xxx	Project	LDL	2023-03-27	\N	1	350000	350021
350020	xxx	Project	LRFA	2023-03-27	\N	1	350000	350020
350024	xxx	Project	RADOME	2023-04-04	\N	1	350000	350024
350025	xxx	Project	TWT	2023-05-18	\N	1	350000	350025
350026	xxx	Project	DNF	2023-06-08	\N	1	350000	350026
300005	xxx	Project	ROV-III	2023-06-15	\N	1	300000	300005
350028	xxx	Project	MC3	2023-07-03	\N	1	350000	350028
200014	xxx	Project	IGS	2023-07-04	\N	1	200000	200014
200013	xxx	Project	TLI	2023-07-04	\N	1	200000	200013
350027	xxx	Project	GPI	2023-07-20	\N	1	350000	350027
250003	xxx	Project	STLT	2023-07-25	\N	1	250000	250003
350029	xxx	Project	VFD-AC	2023-08-03	\N	1	350000	350029
350030	xxx	Project	HH1	2023-08-09	\N	1	350000	350030
350031	xxx	Project	VFD2	2023-08-15	\N	1	350000	350031
450002	25GUN	Project	25MMGUN	2021-11-05	\N	1	450000	450002
160001	Research Grant Account	Account	RGA	2001-01-01	\N	2	160000	\N
300006	xxx	Project	HYDROV	2023-10-05	\N	1	300000	300006
350019	xxx	Project	L-PRO	2023-03-27	\N	1	350000	350019
300007	xxx	Project	PAP104	2023-11-24	\N	1	300000	300007
200017	xxx	Project	SSMLP	2024-01-29	\N	1	200000	200017
200018	xxx	Project	NDB	2024-01-29	\N	1	200000	200018
350011	Vertical Takeoff and Landing Aircraft	Project	VTOL	2001-01-01	2023-08-04	1	350000	350011
450001	Underwater Sea Surveillance System Acoustic Sensor Network	Project	PKS	2001-01-01	2023-08-04	2	450000	450001
200002	Interfacing of Link Y with DVBS	Project	DVBS	2018-11-15	2024-02-02	2	200000	200002
400002	Data Bell Logger	Project	DBL	2001-01-01	2024-02-02	1	400000	400002
350007	Video Telemetry System	Project	VTS	2001-01-01	2024-02-02	1	350000	350007
300008	xxx	Project	ROV-PH2	2024-05-16	\N	1	300000	300008
350033	xxx	Project	MDB-1	2024-05-16	\N	1	350000	350033
350032	xxx	Project	HFT	2024-05-16	\N	1	350000	350032
250007	xxx	Project	VR-HARBAH	2024-06-25	\N	1	250000	250007
\.


--
-- TOC entry 5272 (class 0 OID 28009)
-- Dependencies: 240
-- Data for Name: levels; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.levels (lvl_id, lvl_cat) FROM stdin;
11	Department
12	Department
13	Department
14	Department
15	Department
16	Department
17	Department
18	Department
19	Department
20	Department
21	R&D Wing
22	R&D Wing
23	R&D Wing
24	R&D Wing
25	R&D Wing
26	R&D Wing
27	R&D Wing
28	R&D Wing
29	R&D Wing
30	R&D Wing
31	NRDI Department
32	NRDI Department
33	NRDI Department
34	NRDI Department
35	NRDI Department
41	NRDI
42	NRDI
43	NRDI
44	NRDI
45	NRDI
46	NRDI
47	NRDI
48	NRDI
49	NRDI
50	NRDI
36	NRDI Department
37	NRDI Department
38	NRDI Department
39	NRDI Department
40	NRDI Department
51	HQs Department
52	HQs Department
53	HQs Department
54	HQs Department
55	HQs Department
56	HQs Department
57	HQs Department
58	HQs Department
59	HQs Department
60	HQs Department
61	HQs NRDI
62	HQs NRDI
63	HQs NRDI
64	HQs NRDI
65	HQs NRDI
66	HQs NRDI
67	HQs NRDI
68	HQs NRDI
69	HQs NRDI
70	HQs NRDI
\.


--
-- TOC entry 5273 (class 0 OID 28013)
-- Dependencies: 241
-- Data for Name: roles; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.roles (rol_xunt_id, rol_level, rol_desig, rol_desigshort, rol_type, rol_reportlevel, rol_access, rol_authprj) FROM stdin;
100000	49	Director General	DG	specific	49	multiple	editor
200000	19	Director Communication	DCom	specific	39	single	editor
250000	19	Director Enabling Technology	DEnab	specific	39	single	editor
300000	19	Director Naval Weapon Systems	DNWS	specific	39	single	editor
350000	19	Director Sensors	DSensor	specific	39	single	editor
400000	19	Director System of Systems	DSOS	specific	39	single	editor
450000	19	Director Systems	DSys	specific	39	single	editor
800000	25	Director Finance	DFin	specific	39	single	editor
820000	25	Manager HR	MHR	specific	39	multiple	editor
840000	25	Admin Officer	AO	specific	39	single	editor
880000	25	Manager IS	MIS	specific	39	single	editor
180000	25	Director R&D	DR&D	specific	39	multiple	editor
800000	24	Accounts Officer	AccOfficer	specific	29	multiple	editor
820000	25	Staff Officer HR	SO HR	specific	39	multiple	editor
800000	24	Staff Officer Finance	SO Finance	specific	29	multiple	editor
180000	25	Staff Officer R&D	SO R&D	specific	39	multiple	editor
860000	25	Staff Officer IT&CYS	SO IT&CYS	specific	39	single	editor
100000	11	System Admin	Sys Admin	specific	0	multiple	viewer
10000	59	Director NRD	DNRD	specific	69	single	viewer
450000	19	PI Systems	DSys	specific	39	single	editor
160000	25	Subject Matter Expert	SME	specific	49	multiple	viewer
160000	39	Managing Director	MD	specific	49	multiple	editor
100000	35	Director Planning And Coordination	DP&C	specific	49	multiple	viewer
990000	49	MTSS Representative	MTSS Rep	specific	49	multiple	viewer
\.


--
-- TOC entry 5274 (class 0 OID 28023)
-- Dependencies: 242
-- Data for Name: routes; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.routes (rte_doc, rte_steps) FROM stdin;
MPR	-5-(10)-15-
\.


--
-- TOC entry 5275 (class 0 OID 28030)
-- Dependencies: 243
-- Data for Name: units; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.units (unt_id, unt_type, unt_name, unt_namesh, unt_alowerlimit, unt_aupperlimit, unt_nlowerlimit, unt_nupperlimit, unt_area, unt_leadname, unt_leadtitle, unt_leadrank, unt_leaddesig, unt_leaddesigshort) FROM stdin;
100000	Institute	Navy Research and Development Institute	NRDI	100000	999999	100000	119999	Nrdi	\N	\N	\N	\N	\N
180000	Department	Projects Department	Projects	160000	999999	180000	199999	Prj	\N	\N	\N	\N	\N
860000	Department	Information Technology Department	IT	160000	999999	860000	879999	It	\N	\N	\N	\N	\N
200000	Division	Communication Division	Comm	160000	999999	200000	249999	Prj	Aleem Mushtaq	Dr	Captain PN	Director Communication Division	Director Comm
250000	Division	Enabling Technology Division	Enab	160000	999999	250000	299999	Prj	Hammad Raza	Dr	Capt PN	Director Enabling Technologies	Dir ET
160000	Wing	Research and Development Wing	RDW	160000	999999	160000	179999	Rdw	\N	\N	\N	\N	\N
400000	Division	System of Systems Engineering Division	SoSE	160000	999999	400000	449999	Prj	Sajid Saleem	Dr	Capt PN	Director System of Systems Engineering Division	Director SoSE
880000	Department	Information System Department	IS	160000	999999	880000	899999	Is	Adnan Mustafa	\N	Lt Cdr (Rtd)	Manager Information System	Manager IS
990000	External	External	EXT	0	0	0	0	\N	\N	\N	\N	\N	\N
820000	Department	Human Resource Department	HR	160000	999999	820000	839999	Hr	Asif Ali	\N	Lt Cdr PN	Staff Officer HR	SO HR
300000	Division	Naval Weapons System Division	NWS	160000	999999	300000	349999	Prj	Attaullah Memon	Dr	Commodore	Director Naval Weapon Systems	Director NWS
10000	Head Quarters	Head Quarters	HQs NRD	100000	999999	10000	99999	Hqs					\N
350000	Division	Sensors Division	Sensors	160000	999999	350000	399999	Prj	TARIQ MAIRAJ	DR	Commodore	Director Sensors	Director Sensors
840000	Department	Administration Department	Admin	160000	999999	840000	859999	Adm	Assad Mahmood	\N	Lt Cdr PN	SO Admin	SO Admin
800000	Department	Finance Department	Finance	160000	999999	800000	819999	Fin	Syed Arif Raza Zaidi	\N	Lt Cdr (R) Pakistan Navy	Director Finance	Director Finance
450000	Division	Systems Division	Sys	160000	999999	450000	499999	Prj	Syed Sajjad Haider Zaidi	Dr.	Cdre	PI Systems	PI Systems
\.


--
-- TOC entry 5276 (class 0 OID 28043)
-- Dependencies: 244
-- Data for Name: version; Type: TABLE DATA; Schema: cen; Owner: postgres
--

COPY cen.version (ver_version, ver_compat) FROM stdin;
33.69	33.69
\.


--
-- TOC entry 5099 (class 2606 OID 28964)
-- Name: accounts accounts_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_pk PRIMARY KEY (acc_id);


--
-- TOC entry 5101 (class 2606 OID 28966)
-- Name: accounts accounts_un; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_un UNIQUE (acc_username);


--
-- TOC entry 5111 (class 2606 OID 28968)
-- Name: roles desigs_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.roles
    ADD CONSTRAINT desigs_pk PRIMARY KEY (rol_desig);


--
-- TOC entry 5103 (class 2606 OID 28970)
-- Name: globalvars globalvars_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.globalvars
    ADD CONSTRAINT globalvars_pk PRIMARY KEY (gvar_name);


--
-- TOC entry 5105 (class 2606 OID 28972)
-- Name: heads heads_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.heads
    ADD CONSTRAINT heads_pk PRIMARY KEY (hed_id);


--
-- TOC entry 5107 (class 2606 OID 28974)
-- Name: heads heads_un; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.heads
    ADD CONSTRAINT heads_un UNIQUE (hed_code);


--
-- TOC entry 5109 (class 2606 OID 28976)
-- Name: levels levels_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.levels
    ADD CONSTRAINT levels_pk PRIMARY KEY (lvl_id);


--
-- TOC entry 5113 (class 2606 OID 28978)
-- Name: routes routes_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.routes
    ADD CONSTRAINT routes_pk PRIMARY KEY (rte_doc);


--
-- TOC entry 5115 (class 2606 OID 28980)
-- Name: units units_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.units
    ADD CONSTRAINT units_pk PRIMARY KEY (unt_id);


--
-- TOC entry 5117 (class 2606 OID 28982)
-- Name: version version_pk; Type: CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.version
    ADD CONSTRAINT version_pk PRIMARY KEY (ver_version);


--
-- TOC entry 5118 (class 2606 OID 29151)
-- Name: accounts accounts_fk; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_fk FOREIGN KEY (acc_desig) REFERENCES cen.roles(rol_desig);


--
-- TOC entry 5119 (class 2606 OID 29156)
-- Name: accounts accounts_fk_1; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_fk_1 FOREIGN KEY (acc_unt_id) REFERENCES cen.units(unt_id);


--
-- TOC entry 5120 (class 2606 OID 29161)
-- Name: accounts accounts_fk_2; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.accounts
    ADD CONSTRAINT accounts_fk_2 FOREIGN KEY (acc_level) REFERENCES cen.levels(lvl_id);


--
-- TOC entry 5121 (class 2606 OID 29166)
-- Name: roles roles_fk; Type: FK CONSTRAINT; Schema: cen; Owner: postgres
--

ALTER TABLE ONLY cen.roles
    ADD CONSTRAINT roles_fk FOREIGN KEY (rol_xunt_id) REFERENCES cen.units(unt_id);


-- Completed on 2026-01-19 08:51:30

--
-- PostgreSQL database dump complete
--

\unrestrict tVH5KsjAv3vQlpoe3004BvJMKbrKxHF5j7yLfeNMFXNd9HHQq0L250R7e6QfNqQ

