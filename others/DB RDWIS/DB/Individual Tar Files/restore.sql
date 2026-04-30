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
-- Name: purnew; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA purnew;


ALTER SCHEMA purnew OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: items; Type: TABLE; Schema: purnew; Owner: postgres
--

CREATE TABLE purnew.items (
    item_id integer NOT NULL,
    title text,
    serial smallint,
    unt_id integer,
    type smallint,
    subtype character varying
);


ALTER TABLE purnew.items OWNER TO postgres;

--
-- Name: rfq; Type: TABLE; Schema: purnew; Owner: postgres
--

CREATE TABLE purnew.rfq (
    rfq_id integer NOT NULL,
    pcs_date date,
    pcs_remarks character varying,
    pcs_unt_id integer,
    pcs_hed_id integer,
    pcs_effhed_id integer,
    pcs_effunt_id integer
);


ALTER TABLE purnew.rfq OWNER TO postgres;

--
-- Name: rfq_items; Type: TABLE; Schema: purnew; Owner: postgres
--

CREATE TABLE purnew.rfq_items (
    rfq_item_id integer NOT NULL,
    rfq_id integer,
    item_id integer,
    est_price numeric,
    price numeric
);


ALTER TABLE purnew.rfq_items OWNER TO postgres;

--
-- Name: rfq_items_rfq_item_id_seq; Type: SEQUENCE; Schema: purnew; Owner: postgres
--

ALTER TABLE purnew.rfq_items ALTER COLUMN rfq_item_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME purnew.rfq_items_rfq_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: items; Type: TABLE DATA; Schema: purnew; Owner: postgres
--

COPY purnew.items (item_id, title, serial, unt_id, type, subtype) FROM stdin;
\.
COPY purnew.items (item_id, title, serial, unt_id, type, subtype) FROM '$$PATH$$/5301.dat';

--
-- Data for Name: rfq; Type: TABLE DATA; Schema: purnew; Owner: postgres
--

COPY purnew.rfq (rfq_id, pcs_date, pcs_remarks, pcs_unt_id, pcs_hed_id, pcs_effhed_id, pcs_effunt_id) FROM stdin;
\.
COPY purnew.rfq (rfq_id, pcs_date, pcs_remarks, pcs_unt_id, pcs_hed_id, pcs_effhed_id, pcs_effunt_id) FROM '$$PATH$$/5302.dat';

--
-- Data for Name: rfq_items; Type: TABLE DATA; Schema: purnew; Owner: postgres
--

COPY purnew.rfq_items (rfq_item_id, rfq_id, item_id, est_price, price) FROM stdin;
\.
COPY purnew.rfq_items (rfq_item_id, rfq_id, item_id, est_price, price) FROM '$$PATH$$/5304.dat';

--
-- Name: rfq_items_rfq_item_id_seq; Type: SEQUENCE SET; Schema: purnew; Owner: postgres
--

SELECT pg_catalog.setval('purnew.rfq_items_rfq_item_id_seq', 9759, true);


--
-- Name: items items_pkey; Type: CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.items
    ADD CONSTRAINT items_pkey PRIMARY KEY (item_id);


--
-- Name: rfq_items rfq_items_pkey; Type: CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq_items
    ADD CONSTRAINT rfq_items_pkey PRIMARY KEY (rfq_item_id);


--
-- Name: rfq rfq_pkey; Type: CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq
    ADD CONSTRAINT rfq_pkey PRIMARY KEY (rfq_id);


--
-- Name: rfq_items rfq_items_item_id_fkey; Type: FK CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq_items
    ADD CONSTRAINT rfq_items_item_id_fkey FOREIGN KEY (item_id) REFERENCES purnew.items(item_id);


--
-- Name: rfq_items rfq_items_rfq_id_fkey; Type: FK CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq_items
    ADD CONSTRAINT rfq_items_rfq_id_fkey FOREIGN KEY (rfq_id) REFERENCES purnew.rfq(rfq_id);


--
-- PostgreSQL database dump complete
--

