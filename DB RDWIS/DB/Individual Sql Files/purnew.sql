--
-- PostgreSQL database dump
--

\restrict VI7hyP2ePUOIuhkgnCoeyX5PsDw1PsCJ3k4ODbbe02npz7u6tzdsevX95aLpbJx

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-03-04 15:21:08

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
-- TOC entry 16 (class 2615 OID 373610)
-- Name: purnew; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA purnew;


ALTER SCHEMA purnew OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 396 (class 1259 OID 373611)
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
-- TOC entry 397 (class 1259 OID 373619)
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
-- TOC entry 399 (class 1259 OID 373628)
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
-- TOC entry 398 (class 1259 OID 373627)
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
-- TOC entry 5301 (class 0 OID 373611)
-- Dependencies: 396
-- Data for Name: items; Type: TABLE DATA; Schema: purnew; Owner: postgres
--

COPY purnew.items (item_id, title, serial, unt_id, type, subtype) FROM stdin;
150	DC Wire	9	350000	2	Other
2273	Power supply	2	200000	2	Parts
4180	Mylar	15	350000	2	Other
2409	Keyboard	2	200000	7	Parts
7710	Showers	3	160000	3	Other
10036	Scale	42	350000	2	Other
5751	Rope	8	350000	2	Other
2756	Bolts and Nuts for launcher 2, M42, M30, M18, M16, M12, M8	25	350000	7	Parts
701	SS Nuts and Bolts (packet)	30	350000	7	Other
2718	PLA Material for 3D Printer	1	300000	2	Chemicals
5734	4.6 ft. Brass rod 7mm	6	350000	2	Fabrication
8346	Diode 1N 4007	19	450000	2	Other
1640	30KW DC Power Supply	1	300000	7	Machinery / Equipment
2515	Finishing operation and paint of pressure hull	4	300000	7	Parts
8585	54LS08 Ics	4	200000	2	Parts
741	Tweezer Set	70	350000	7	Other
7959	‘EL90’ 90º elevations angle	3	350000	7	Parts
2514	Installlation of vent for pressurised air filling for pressure testing of pressure hull	3	300000	7	Parts
2513	Machining, Fabrication and Fitting of all stiffiners and end bulk head of pressure hull	2	300000	7	Machinery / Equipment
7274	Spiral sleeve 8mm x 20ft	30	350000	7	Other
2609	Mechanical Work Bench	1	300000	7	Furniture
2512	Mild Steel Pressure Hull (Length 1000mm, Dia 500mm, Thickness 3mm) with internal stiffiner and both end bolted bulk head	1	300000	7	Machinery / Equipment
6493	Copper/Metal Plate PCB (0.02 inch thickness)	43	350000	2	Fabrication
6494	SMA connectors (50 Ohms)	44	350000	2	Parts
6495	Acrylic Sheet (2.5 inch dia, custom made) (3 x 2.5 inch dia)	45	350000	2	Fabrication
6351	Posters printing for INTELL	16	350000	3	Other
4790	Rasberry Pi based Target Demonstration equipment	22	350000	2	Other
2536	ROV Test Platform Main Body Structure	1	300000	7	Machinery / Equipment
2537	Fastener Clamp Attachment	2	300000	7	Machinery / Equipment
4687	Connectors  	11	350000	2	Other
7751	Bat	1	160000	2	Other
153	Connector (Terminal Blocks)	12	350000	2	Other
5198	DIN rail	2	350000	2	Other
414	63A 3 pole	3	350000	2	Other
1219	Lubrication oil ISO VG-68	42	350000	2	Chemicals
1600	O- Lugs	34	350000	2	Other
2538	Cable Tray	3	300000	7	Machinery / Equipment
2624	Locker	14	300000	7	Furniture
2629	Sofa Set	16	300000	7	Furniture
2634	Conference Table	17	300000	7	Furniture
9706	Bearing	27	350000	2	Other
2618	Office Chairs	9	300000	7	Furniture
2615	Cabinets	6	300000	7	Furniture
2617	Side Table	8	300000	7	Furniture
2619	First Aid Box	10	300000	7	Furniture
2620	Display Cupboard	11	300000	7	Furniture
2621	Book Shelf	12	300000	7	Furniture
2623	Reception Table	13	300000	7	Furniture
2616	Table	7	300000	7	Furniture
2637	Inverter Split Air Conditioner - 02 Ton	1	300000	7	Machinery / Equipment
9764	RGB LED PWM Fan	32	350000	2	Other
2645	Ceiling Fan	3	300000	7	Machinery / Equipment
2649	Exhaust Fan 36''	5	300000	7	Machinery / Equipment
2638	Inverter Floor Standing Air Conditioner - 04 Ton	2	300000	7	Office Equipment
2648	Exhaust Fan 10''	4	300000	7	Machinery / Equipment
10047	Battery 9V	53	350000	2	Other
10186	UBEC CDI - 6V/5A regulated	6	350000	2	Other
10187	Wiring	7	350000	2	Other
10188	Assorted Connectors	8	350000	2	Other
2664	a.  Main cable dismantling from electric pole for dismantling of electric pole\r\nb.  Excavation for electrical pole removal area & backfilled\r\nc.   Excavation for electrical pole installation area & backfilled\r\nd.  Stone soiling with lean for electric pole installation\r\ne.  PCC filling in excavated area of electric pole for installation\r\nf.   Installation of supporting wire for pole strength\r\ng.   Main cable connections of electric pole	1	300000	7	Other
2665	Fabrication of Moveable Trolley L58" x W37" x H18" (Frame with M Channel 3" x 1.5" with base plates & 8" -04 Movable wheels with paint)	2	300000	7	Other
2829	RCC Flooring for 34 x 20 meter	1	300000	7	Other
2832	PEB Shed entrance  ramp and outer wall lean	4	300000	7	Other
10326	12.9 Grade M10 bolts	2	350000	2	Other
2831	Pump Room, Pantry and Observatory Room Tiling	3	300000	7	Other
2830	Perimeter Boundary Wall	2	300000	7	Other
2896	Portable Operator Console for Prototype ROV Test Platform	1	300000	7	Machinery / Equipment
2989	Power Cables (4400V 100A)	3	300000	7	Machinery / Equipment
7822	Video balun	1	350000	2	Other
2666	Aluminum 6061 block	1	350000	2	Other
2988	Load Resistance Pack	2	300000	7	Machinery / Equipment
2987	Capacitor Bank (400V, 1500 uF)	1	300000	7	Machinery / Equipment
145	Tapping set (Ingco)	4	350000	2	Other
2990	Circuit Breaker (160 A)	4	300000	7	Machinery / Equipment
5331	Manufacturing of skin for rear section of lab version of prototype ROV	2	300000	7	Machinery / Equipment
5329	Manufacturing of skin for front section of lab version of prototype ROV	1	300000	7	Machinery / Equipment
10420	Scanning Imaging Sonar	1	300000	7	Parts
10421	Control Board (Control System Itegration Board)	2	300000	7	Parts
4787	Processor INTEL core i-9-9900K,Motherboard:Gigabye GA Z390M,CPU cooler:MA620\nP RAM G.Skill Aegis 16 Gb DDR4,Graphic card: MSI Nvidia GTX1660 Super,SSD:512 GB, Monitor: AOC 9E1H LED monitor, Keyboard, Mouse, Power supply: Corsair CV650	19	350000	7	Office Equipment
6584	Multimeters	89	350000	7	Tools / Test Equipment
4251	512 GB SSD SATA	15	350000	7	Parts
4250	2TB HDD	14	350000	7	Parts
4249	8 GB RAM DDR3	13	350000	7	Parts
5334	Manufacturing/ fabrication of internal structure tray for mounting equipments inside pressure hull	3	300000	7	Machinery / Equipment
5566	Downward looking underwater camera (300 M rated)	2	300000	7	Machinery / Equipment
5565	Under water lights	1	300000	7	Machinery / Equipment
11888	Frieght	2	350000	3	Transport
2569	High torque motor with built in control unit & power supply	1	350000	7	Machinery / Equipment
3036	Tripod stand	44	350000	7	Other
3038	Fixtures seals	2	350000	7	Parts
3039	Load transmission coupling	3	350000	7	Parts
3037	Spare motor control unit	1	350000	7	Parts
8401	Tallies customized	56	350000	7	Parts
8400	Panel customized	55	350000	7	Other
8370	Voltage/ampere meter 32V DC	34	350000	7	Parts
8365	Power Supply 32V DC 20A	32	350000	7	Parts
8360	Power Supply 12V DC 10A	30	350000	7	Parts
8357	Digital voltage/ampere meter 220V	29	350000	7	Parts
8349	Panel box	25	350000	7	Parts
2814	HS-200-LV SENSOR BOARD 2x 10cm 10AWG	1	350000	7	Parts
2815	2-6S BEC/5.35V-3A/DF-13-4P	2	350000	7	Parts
1641	Power Cable	2	300000	2	Parts
2084	3500 PSI Cylindrical Ready Mix	1	300000	2	Other
2085	SRC BAGS (Landed)	1	300000	2	Chemicals
7615	Silicon Sealant (Water proofing)	1	300000	2	Chemicals
7616	Anti corrosion Solution	2	300000	2	Chemicals
2059	Nozzle and Temperature Sensor for P1000	1	350000	2	Parts
8527	Surgical Masks	14	350000	2	Other
8521	FR4 PCB Substrate 1ft x 1ft	8	350000	2	Other
8522	Safety Gloves	9	350000	2	Other
8523	Safety Goggles	10	350000	2	Other
8524	Safety Helmet	11	350000	2	Other
5648	Waterproof IP65 connector YU-USB3-JSX-03-100	3	350000	2	Parts
5649	Waterproof IP65 connector YF-24-C03PE-02-001	4	350000	2	Parts
5650	Waterproof IP65 connector YF-24-J03SX-02-001	5	350000	2	Parts
5652	Generator Fuel	7	350000	2	POLs
5655	Power Supply 24V 30A	10	350000	2	Parts
5656	Power cable	11	350000	2	Parts
5658	Drip set for antenna	2	350000	2	Parts
5659	29x31 and 32x32 antenna clips	3	350000	2	Parts
5660	Soldering wire 70/30mm 200g	4	350000	2	Fabrication
5661	N Type for RG58	5	350000	2	Parts
5662	Writeable CDs/DVDs	6	350000	2	Other
5663	3.5uF Capacitors	7	350000	2	Parts
5664	LiPo Battery Wire	8	350000	2	Parts
5665	Fiber washers set	9	350000	2	Parts
5666	LMR300 Cable	10	350000	2	Parts
5667	HackRF SDR USB Cable	11	350000	2	Parts
5668	Tweezer	12	350000	2	Other
5669	Iron Cutting disk	13	350000	2	Parts
5670	Heat sinks multiple sizes (local)	14	350000	2	Parts
4244	AOC 9E1H LED Monitor	8	350000	7	Parts
4243	Cougar MG120-G Casing	7	350000	7	Parts
4242	Thermaltake Litepower 650W Power Supply	6	350000	7	Parts
4496	Din Rail	24	350000	2	Other
4241	Thermaltake Riing 12 Fans	5	350000	7	Parts
4240	Gigabyte NVMe SSD 512GB	4	350000	7	Parts
4239	ADATA 8GB DDR4 2666MHz RAM	3	350000	7	Parts
4238	Gigabyte B460M DS3H Motherboard	2	350000	7	Parts
4237	Intel Core i5 10400 Processor	1	350000	7	Office Equipment
4233	X-plane	23	350000	7	Software
4225	RAM 4GB	15	350000	7	Parts
4224	HDD 1TB	14	350000	7	Parts
4223	Power supply 600 W	13	350000	7	Parts
4222	Processor i5 10th gen	12	350000	7	Parts
4221	HDD 2TB	11	350000	7	Parts
4220	SSD 500 GB	10	350000	7	Parts
4219	RAM 32 GB DDR4	9	350000	7	Parts
4218	Mouse A4 tech	8	350000	7	Office Equipment
5671	30m Cable + Exterior reel for generator	15	350000	7	Parts
3021	Pipe hose extension	29	350000	2	Parts
3023	SMA-SMA cable	31	350000	2	Parts
3024	Epoxy clear	32	350000	2	Other
3026	SS 2kg material	34	350000	2	Parts
3028	Petrol & Diesel for generator and compressor	36	350000	2	POLs
3029	Silicon Tube (ft)	37	350000	2	Other
3030	Cottan rag	38	350000	2	Other
3034	Avid Corn End Mill	42	350000	2	Other
3019	Wrapping sheet	27	350000	2	Other
3032	Die grinde tungsten pin set	40	350000	2	Parts
3018	Silicone vaccum pipe 100ft	26	350000	2	Parts
3013	Oxygen cylinder refill	22	350000	2	Other
3017	SS 5*20 Allen head screws	25	350000	2	Other
3016	Welding rod dia 12	24	350000	2	Other
3011	Spray WD40	20	350000	2	POLs
3012	Acetylene Cylinder refill	21	350000	2	Other
3014	Argon gas refill	23	350000	2	Other
3042	Suction Filter Element (C3020051)	3	350000	2	Parts
3040	Filter Elemant (H70E)	1	350000	2	Parts
5673	15x8” nylon propellers	2	350000	2	Parts
5680	JST-XH male/female connectors set	9	350000	2	Parts
5681	Battery Elimination Circuit	10	350000	2	Parts
5688	Thermal Paste	6	350000	2	Other
5697	Android Cables	2	350000	2	Parts
5698	Ethernet Cables	3	350000	2	Parts
5699	Nozzles	4	350000	2	Parts
5700	Sockets	5	350000	2	Parts
5702	BNC Male Triangle to BNC Female Adapter 	7	350000	2	Parts
5703	Patch cord BNC male to male RG59	8	350000	2	Parts
5704	Patch cord BNC male to male RG142	9	350000	2	Parts
5707	Fiber Tape	12	350000	2	Other
5708	Paper Tape	13	350000	2	Other
5709	Safety Goggles	1	350000	2	Other
5710	Safety Gloves	2	350000	2	Other
5711	Dust Mask	3	350000	2	Other
5712	Plastic Gloves Set	4	350000	2	Other
5713	Drill bits set	5	350000	2	Other
5716	Sand paper	8	350000	2	Other
5719	Cutting blades	11	350000	2	Other
5720	Brass Square Rod 18 ft.	1	350000	2	Fabrication
5721	Brass Rod 1/4, 5/16mm	2	350000	2	Fabrication
5722	Brass rod 2mm	3	350000	2	Fabrication
3041	Filter Element (P70E)	2	350000	2	Parts
8317	L type hook for hatch	8	350000	2	Parts
8179	Hobbywing 60A ESC	1	350000	2	Parts
8307	9x6 APC props	3	350000	2	Parts
8312	MDF Sheets	5	350000	2	Other
8313	German Glue	6	350000	2	Other
8315	San Ace 80 (9GV0812P1H04)	7	350000	2	Parts
8409	14 AWG wire (black)	62	350000	2	Parts
8384	Fan 220V AC 3 inch	46	350000	2	Parts
8319	SS spring loaded latch	9	350000	2	Parts
8390	Terminal block	49	350000	2	Parts
8393	Velcro	51	350000	2	Other
8395	Cable ties assorted	52	350000	2	Other
8396	Lugs assorted	53	350000	2	Other
8398	Tie tags	54	350000	2	Other
8402	ABS spool for printing	57	350000	2	Parts
8403	SMA to bulhead SMA cable	58	350000	2	Parts
8404	24 AWG wire (yellow)	59	350000	2	Parts
8383	10mm LED Panel Mount	45	350000	2	Parts
8407	24 AWG wire (red)	61	350000	2	Parts
5729	22.8 ft. Brass square pipe ½”	1	350000	2	Fabrication
5730	8.3 ft. Brass rod 3mm	2	350000	2	Fabrication
5731	4.2 ft. Brass rod 4mm	3	350000	2	Fabrication
5732	4 ft. Brass rod 5mm	4	350000	2	Fabrication
8385	Cable glands	47	350000	2	Parts
8410	14 AWG wire (red)	63	350000	2	Parts
5733	4.7 ft. Brass rod 6mm	5	350000	2	Fabrication
5737	Silver Marker 	9	350000	2	Stationary
8406	24 AWG wire (black)	60	350000	2	Parts
5822	Engine Test Bed	2	350000	7	Parts
8345	Cable Marker	23	350000	2	Other
8323	Aluminum Din rail	12	350000	2	Parts
8326	2-pole DC breaker (10A)	14	350000	2	Parts
8328	Lugs connector tubeless packets	15	350000	2	Parts
8332	Din rail Terminal block 2.5mm	16	350000	2	Parts
8335	Slotted cable duct 40x40mm	17	350000	2	Parts
8336	Vent Fan 4"	18	350000	2	Parts
8339	Vent Fan Cabinet 4"	19	350000	2	Parts
8341	2-pin Mil connector	20	350000	2	Parts
8342	8-pin Mil connector	21	350000	2	Parts
8392	Wire Mesh Assorted	50	350000	2	Parts
8322	Contactor 5kW	11	350000	2	Parts
8325	2-pole AC breaker (63A)	13	350000	2	Parts
8343	Industrial socket	22	350000	2	Parts
8374	Circuit breaker 220V AC 3A	36	350000	2	Parts
8381	GX-16 2 Pin	43	350000	2	Parts
8380	40/76 Wire Coil 2 Core	42	350000	2	Parts
8379	Emergency stop 220V AC	41	350000	2	Parts
8378	Indicator 220V AC	40	350000	2	Parts
5796	wire harness 4pin	5	350000	2	Parts
5797	wire harness 2pin	6	350000	2	Parts
5798	header 40pin	7	350000	2	Parts
5799	24V relay	8	350000	2	Parts
5801	Solder wire 60/40 100g	10	350000	2	Parts
5803	USB cable for Arduino	12	350000	2	Parts
5804	Nano cable for Arduino	13	350000	2	Parts
5805	2pin c3 fan connector	14	350000	2	Parts
5806	Fan grill for jammer	15	350000	2	Parts
5807	2.5mm 3core electrical cable 5m	16	350000	2	Parts
5808	Stickers for jammer	17	350000	2	Other
5810	Hard cover files	2	350000	2	Stationary
8377	Circuit Breaker 32V DC 5A	39	350000	2	Parts
8382	GX-16 5 Pin	44	350000	2	Parts
8375	Circuit breaker 12V DC 8A	37	350000	2	Parts
8373	Circuit breaker 220V AC 16A	35	350000	2	Parts
5858	2 part epoxy 1kg/0.8kg	1	350000	2	Fabrication
5859	250g fiber glass cloth 1m	2	350000	2	Fabrication
5860	Paint Brush	3	350000	2	Fabrication
5861	Thinner 1/2l	4	350000	2	Fabrication
5862	2” cloth tape	5	350000	2	Fabrication
5863	1” duct tape	6	350000	2	Fabrication
5865	Cable tie 6inch	8	350000	2	Other
5869	Generator Fuel	12	350000	2	POLs
5870	Copper Aluminum rod for UAVs 10ft. length	13	350000	2	Fabrication
5872	Masking Tape	15	350000	2	Stationary
5873	Mortein coils	16	350000	2	Cleaning Material
5874	Metal Rope	17	350000	2	Parts
5883	DB9 cable	1	350000	2	Parts
5884	DB9 connector	2	350000	2	Parts
5885	½ x ¾ socket	3	350000	2	Parts
5886	Heat sleeve	4	350000	2	Parts
5887	19pin Army connector	5	350000	2	Parts
5888	U Lugs small	6	350000	2	Parts
5889	U Lugs Medium	7	350000	2	Parts
5890	17wire harness 5ft.	8	350000	2	Parts
8355	Cable 2.5mm	28	350000	2	Parts
8353	Panel stand	27	350000	2	Parts
8351	Indicating Light 16mm	26	350000	2	Parts
8347	Zip Tie packet	24	350000	2	Other
5917	Nuts, Bolts, Washers	9	350000	2	Parts
5918	Nitrile Gloves	10	350000	2	Parts
8376	Circuit breaker 12 V DC 5A	38	350000	2	Parts
5919	Sanding Block	11	350000	2	Parts
5920	Paper Tape	12	350000	2	Parts
5921	Scotch Tape	13	350000	2	Parts
5923	Kevlar Fiber for in-house fuel tank (1 m^2)	15	350000	2	Parts
5924	Spring	16	350000	2	Parts
5927	Aluminum Sheet 2mm / 5mm Max. length 23” x 16”	1	350000	2	Parts
5930	Eminent Hand Sanitizers	1	350000	2	Cleaning Material
5931	Gloves pack	2	350000	2	Cleaning Material
5932	Surgical Masks box	3	350000	2	Other
5933	Gloves Plastic 100 pcs pack	4	350000	2	Cleaning Material
5934	Coveralls	5	350000	2	Other
5935	Surface Cleaner	6	350000	2	Cleaning Material
5936	Tissue boxes (Rose Petal Regular)	7	350000	2	Other
5937	Carbon fiber tube 12x10x1000mm	1	350000	2	Parts
5938	Carbon fiber tube 14x12x500mm	2	350000	2	Parts
5940	Screws and washers 	4	350000	2	Parts
5941	heat sleeve	5	350000	2	Parts
5942	Spray paint for UAV	6	350000	2	Parts
5879	Selector switch with box 	2	350000	7	Parts
5992	Gens Ace 5000mAh 3s LiPo	26	350000	2	Parts
7235	Photocopy paper rim	1	350000	2	Other
7236	Tissue box	2	350000	2	Other
7237	Tissue Roll	3	350000	2	Other
7238	Air freshener	4	350000	2	Other
7239	Minute sheets pack	5	350000	2	Other
7240	Pointers blue/black	6	350000	2	Other
7241	Highlighter red	7	350000	2	Other
7242	Highlighter yellow	8	350000	2	Other
6982	Bush Gland	9	200000	2	Other
6236	Lacquer - can	7	350000	2	Fabrication
8280	Battries 100 Ah, 12 V	2	450000	2	Other
6984	Electric Tape 	11	200000	2	Other
6985	Cable Ties 12” Packet 	12	200000	2	Other
6986	Cable Ties 8” Packet 	13	200000	2	Other
6987	Cut screws (SS) 	14	200000	2	Other
8290	Extension Board	12	450000	2	Other
8292	Solar plates	14	450000	2	Other
4098	CF Wooven Cloth 90 GSM (1 m2)	1	350000	2	Other
4099	CF Wooven Cloth 210 GSM (1 m2)	2	350000	2	Other
6927	Routers	9	200000	7	Parts
4128	SDD 512 GB	13	350000	7	Other
4100	CF Wooven Cloth 375 GSM (1 m2)	3	350000	2	Other
4101	UD CF Cloth 100 GSM (1 m2)	4	350000	2	Other
4102	UD CF Cloth 250 GSM (1 m2)	5	350000	2	Other
4103	Cured CF Sheets 1mm (500*450 mm)	6	350000	2	Other
4104	Cured CF Sheets 2mm (500*450 mm)	7	350000	2	Other
4105	Cured CF Sheets 3mm (500*450 mm)	8	350000	2	Other
4106	Kevlar (1 m2)	9	350000	2	Other
4107	Baffling Foam (30litres)	10	350000	2	Other
4108	Epoxy dispenser	11	350000	2	Other
5317	uLCD-70DT Display Modules 7" TFT LCD Touch w DIABLO16 processor	2	200000	2	Parts
6928	Data cables Roll 	10	200000	2	Other
4140	Jumpers, servo wires	12	350000	2	Other
4141	Aluminum 6061 Rail holders (640x40x85 mm), Aluminum 6061 base plate (600x293x20 mm), Aluminum 6061 Vertical Blocks (259x150x20 mm),Aluminum 6061 Load cell holders (60x60x50 mm) & Aluminum 6061 Engine-Bench mount (140x93x5 mm)	13	350000	2	Other
4142	Linear Guides 640mm length	14	350000	2	Other
4143	Fasteners	15	350000	2	Other
4147	Fuel Check Valves	19	350000	2	Other
4148	Flexible Pipes	20	350000	2	Parts
4149	S Duct Fiberglass	21	350000	2	Other
4214	Display 22" Full HD	4	350000	7	Office Equipment
4213	Tower casing	3	350000	7	Parts
4212	Mother board B460	2	350000	7	Parts
4211	Graphic card GTX 1030	1	350000	7	Parts
4210	Laptop ASUS ROG Strix G512LI, Core i7 10750H, 8GB RAM, 512 GB SSD, Nvidia 1650Ti	16	350000	7	Office Equipment
4209	Thermaltake ring 12 LED RGB case fans	15	350000	7	Parts
4208	HP P241v 23.8" Monitor	14	350000	7	Office Equipment
4207	Logitech Wireless Combo MK345	13	350000	7	Parts
4206	Cooler Master Master Liquid ML240L	12	350000	7	Parts
4205	Cougar MX-331-T casing	11	350000	7	Parts
10572	Housing	4	450000	7	Parts
4204	Cooler Master MWE Gold 750W Power Supply	10	350000	7	Parts
4203	Network Card TPLink TL-WN881ND	9	350000	7	Parts
4202	Gigabyte B550 AORUS Elite V2 Motherboard	8	350000	7	Parts
4200	ZOTAC Gaming GeForce RTX 2060 Graphics Card	6	350000	7	Parts
4199	Seagate BarraCuda ST100DM010 1TB HDD	5	350000	7	Parts
7744	Notice Board Fabrication & Installation 2'x3'	3	160000	2	Other
7713	Commode Drain	6	160000	2	Other
7712	Taps for wash Basin	5	160000	2	Other
7745	Notice Board Fabrication & Installation 1'x1'	4	160000	2	Other
7662	Laser Pointer	24	160000	2	Other
7659	Insulation Tape	22	160000	2	Other
7657	Measuring Tape	21	160000	2	Other
7711	Taps for toilets	4	160000	2	Other
7742	Notice Board 2'x3' Designing	1	160000	2	Other
7743	Notice Board 1'x1' Designing	2	160000	2	Other
8247	Door Threshold (Sill) 9x3.5 foot with divider. 18-gauge sheet.	1	160000	2	Other
6994	Cement /Sand 	21	200000	2	Building Material
6991	PVC sealing tapes 	18	200000	2	Other
6993	Chisel Hammer 	20	200000	2	Tools
6990	Drilling machine with bits 	17	200000	2	Tools
6992	Heat Gun 	19	200000	2	Tools
4198	Gigabyte NVMe SSD 512GB	4	350000	7	Parts
4197	G Skill TridentZ RGB 16GB DDR4 RAM	3	350000	7	Parts
4196	Gigabyte Z490 AORUS Elite Motherboard	2	350000	7	Parts
4195	Intel i7 10700 Processor	1	350000	7	Office Equipment
4179	Buffing Machine(Buffing Machine Compound)	14	350000	7	Other
6976	Toolkit (Electronic)	3	200000	2	Other
4227	Sanitization material for lab	17	350000	2	Cleaning Material
4231	CF Woven	21	350000	2	Other
4232	Wegstr CNC Bits	22	350000	2	Other
4236	DXZ Limit Switch	26	350000	2	Other
4256	Aluminium Disk Al 6061 T4 24mm radius and 10 mm thickness	20	350000	2	Other
4257	Aluminum Sheet Al 6061 T4 86.36 x 43.18 mm, sheet thickness 2mm & 125mm length	21	350000	2	Other
4263	Al 1050 flanges for adapters	1	350000	2	Parts
4265	Panel mount N-connector	3	350000	2	Other
4266	heat sink 100W copper billet & machining work	4	350000	2	Other
4272	Adapter N(F) - N(F)	10	350000	2	Other
4273	Adapter N(M) - N(M)	11	350000	2	Other
6084	Grinder with plates	5	350000	7	Parts
6083	HDMI Cable	4	350000	7	Parts
6092	Name plates for Labs	13	350000	7	Office Equipment
6107	Spinner 	11	350000	7	Tools / Test Equipment
6132	USB 16 GB	23	350000	7	Office Equipment
6974	Heat shrink Sleeves (32mm)                                                                                     	1	200000	2	Other
6975	Heat shrink Sleeves (22mm)                                                                                     	2	200000	2	Other
6977	2” Dia PVC Pipe	4	200000	2	Other
6978	Elbows (2”)	5	200000	2	Other
6979	Clamps (2”)	6	200000	2	Other
6981	Coupling Socket (2”)	8	200000	2	Other
6983	PVC Solution (0.5 kg)	10	200000	2	Chemicals
6094	Mechanical Vias	15	350000	2	Parts
6105	Landing gear springs	9	350000	2	Parts
6106	Rawal bolts	10	350000	2	Parts
6108	Gasket disc	12	350000	2	Parts
6110	RF Power LDMOS Transistor, 136-941 MHz, 7 W, 7.5 V	1	350000	2	Parts
6111	Coaxial Connectors SMA, Edge Mount	2	350000	2	Parts
6112	Ferrite Beads 30ohms 6A 10mOhms 0805 Ferrite Chip	3	350000	2	Parts
6113	100pF Capacitor	4	350000	2	Parts
6114	8.2pF Capacitor	5	350000	2	Parts
6115	36pF Capacitor	6	350000	2	Parts
6116	27pF Capacitor	7	350000	2	Parts
6117	1uF Capacitor	8	350000	2	Parts
6118	0.01uF Capacitor	9	350000	2	Parts
6119	18pF Capacitor	10	350000	2	Parts
6120	9.1pF Capacitor	11	350000	2	Parts
6121	240pF Capacitor	12	350000	2	Parts
6122	2.2uF Capacitor	13	350000	2	Parts
6142	Padestal fan large	33	350000	7	Office Equipment
6133	Portable HDD 1 TB	24	350000	7	Office Equipment
6131	USB 64 GB	22	350000	7	Office Equipment
6109	Master Offisys Chairs Aura LBC  	1	350000	7	Office Equipment
6103	Spray gun	7	350000	7	Parts
10535	Petrol	20	350000	2	POL
4314	Digital screen vernier caliper	52	350000	7	Tools / Test Equipment
4320	Soldering iron 30W	2	350000	7	Tools / Test Equipment
4216	CPU cooler	6	350000	7	Parts
6123	4.7uF Capacitor	14	350000	2	Parts
6124	0.01uF Capacitor	15	350000	2	Parts
6167	Coveralls	58	350000	2	Other
6168	Fire extinguisher metallic buckets	59	350000	2	Other
6169	red paint	60	350000	2	Other
6174	M2 x 12mm LN nuts	2	350000	2	Parts
6175	M2 x 12mm LN bolts	3	350000	2	Parts
6182	Spray paints for casing and UAV	10	350000	2	Fabrication
6187	Nuts and bolts mix	15	350000	2	Parts
6189	Jumper wires m-m	17	350000	2	Parts
6190	Jumper wires m-f	18	350000	2	Parts
6988	Wooden Sticks 	15	200000	2	Other
6191	Jumper wires f-f	19	350000	2	Parts
6192	40pin header strip (m&f)	20	350000	2	Parts
6197	LMR-400 LLPX (3m)	25	350000	2	Parts
6198	GPS Receiver Module	26	350000	2	Parts
6233	Gel coat - kg	4	350000	2	Fabrication
6234	Polyester (2 parts) - kg	5	350000	2	Fabrication
6235	Thinner - ltr	6	350000	2	Fabrication
6237	Wooden frame piece	8	350000	2	Fabrication
6238	polycate box	9	350000	2	Fabrication
6239	waxing cloth	10	350000	2	Fabrication
6241	nitrile gloves pack	12	350000	2	Fabrication
6242	grey paint	13	350000	2	Fabrication
6246	nuts and bolts	17	350000	2	Parts
6249	Plastic propellers & dampers (pair)	20	350000	2	Parts
6250	UBEC 5V, 3A	21	350000	2	Parts
6258	GensAce 3S 5000mAh	29	350000	2	Parts
8299	IC 7400	1	450000	2	Other
8344	JFT Transistor 2N5484/2N5486	18	450000	2	Other
8348	Zener Diode 9V,12V  1/2 Watt	20	450000	2	Other
8350	BJT Transistor C1383	21	450000	2	Other
8352	SCR (400V,5A)(BT 151,600R)	22	450000	2	Other
8354	TRIAC (400V,5A)(BT 151,600E)	23	450000	2	Other
6324	Power supply 15V 10A for field testing	7	350000	7	Tools / Test Equipment
6333	DJI Screen Attachment	1	350000	7	Parts
6285	Microscope	1	350000	7	Tools / Test Equipment
6290	First Aid Box with medicines	6	350000	7	Other
6287	Iron	3	350000	7	Machinery/Equipment
6267	Vernier Caliper	1	350000	7	Tools / Test Equipment
6268	Fine Cutter x2	2	350000	7	Tools / Test Equipment
6254	150A watt meter and power analyzer (local)	25	350000	7	Tools / Test Equipment
6253	LiPo battery charger	24	350000	7	Tools / Test Equipment
6255	Hotrc battery tester	26	350000	7	Tools / Test Equipment
6334	Propeller guards set for DJI	2	350000	2	Parts
6335	Extra DJI Phantom Battery	3	350000	2	Parts
6336	DB9 connectors (PCB Mount)	1	350000	2	Parts
6337	DB9 Cable M/F 1.5m and 3m	2	350000	2	Parts
6338	USB to RS232 Adapter	3	350000	2	Parts
6339	Terminal Blocks 2/pin	4	350000	2	Parts
6340	Max232 Module	5	350000	2	Parts
6341	Arduino Nano	6	350000	2	Parts
6342	AA Cells	7	350000	2	Parts
6343	Screws and Washers	8	350000	2	Parts
6345	Soldering Wire Roll	10	350000	2	Fabrication
6346	6x6 Fiber PCB Board	11	350000	2	Fabrication
6347	FeCl for etching PCB	12	350000	2	Fabrication
6348	Cling Film 	13	350000	2	Other
9129	DC convertor	6	350000	7	Parts
6353	Generator Fuel for trials 	18	350000	2	POLs
6354	16AWG High Quality silicon black and red wires 	19	350000	2	Parts
6355	Bullet connectors	20	350000	2	Parts
6358	Arduino ATMega Microcontroller	1	350000	2	Parts
6359	Male & Female Headers	2	350000	2	Parts
6360	Teflon Spacers	3	350000	2	Parts
6361	Push coils	4	350000	2	Parts
6362	RS232 to TTL Adapters	5	350000	2	Parts
6363	6x12 Fiber PCB Board	6	350000	2	Parts
6364	FeCl packs for etching	7	350000	2	Parts
6365	Drill bits	8	350000	2	Fabrication
6366	Drill bit Holder	9	350000	2	Fabrication
6371	220V Switches	14	350000	2	Parts
6372	2 Pin Plugs	15	350000	2	Parts
6357	12V 60Ah Battery \n(with one month warranty)	1	350000	2	Parts
7243	Correction pen	9	350000	2	Other
7244	Ball point blue/black pack	10	350000	2	Other
4382	Neylon Block for permittivity Testing	16	350000	2	Other
7309	Processing Control	1	400000	2	Parts
7310	Interfacing Connector	2	400000	2	Parts
7218	Breakfast Nathiagali 06 Jun 19	4	350000	2	Other
7225	Tea 26 Nov 19	7	350000	2	Other
7231	Refreshments	1	350000	2	Other
7318	Data and Bell Logger Interfacing Device	1	400000	2	Parts
6383	File cover (printed)	1	350000	2	Stationary
4335	Siemens PLC (Model # S7-1215C)	1	350000	7	Other
6377	USB 32GB	20	350000	7	Office Equipment
6344	Soldering iron 	9	350000	7	Tools / Test Equipment
6292	Large tool box	8	350000	7	Tools / Test Equipment
6293	200W Soldering Iron and solder wire	9	350000	7	Tools / Test Equipment
6314	Soldering sucker	11	350000	7	Tools / Test Equipment
6315	Torch	12	350000	7	Tools / Test Equipment
6384	A4 paper rim	2	350000	2	Stationary
6385	Ball point blue	3	350000	2	Stationary
6386	Gel pen blue & black	4	350000	2	Stationary
6387	Pencil packet	5	350000	2	Stationary
6388	Minute sheets	6	350000	2	Stationary
6389	White pen	7	350000	2	Stationary
6390	Glue sticks	8	350000	2	Stationary
6391	Stapler pins	9	350000	2	Stationary
6394	Register (300 pages)	12	350000	2	Stationary
7233	Standeasy & lunch at RDS NATHIAGALI (Bill no 389) - 23 Jan 20	1	350000	2	Other
7234	Messing charges at PNS SIDDIQ (Bill Nos. 2334, 2335, 2336 and 2337) - 07 Feb 20)	2	350000	2	Other
6936	Cable Clamp	5	200000	2	Other
7456	Electronics components & accessories 	6	200000	2	Parts
7455	APC  UPS Batteries 16x2 No 01 pack  	5	200000	2	Other
7475	APC UPS Batteries 16x2 No 01 pack	3	200000	2	Other
7457	Connectors & connection Pin 	1	200000	2	Parts
7458	Heating sleeves	2	200000	2	Other
7461	Solder Wire	5	200000	2	Other
7466	Data Cable Roll 12 Core	10	200000	2	Other
7480	Electronics accessories	8	200000	2	Parts
6395	Paper clips	13	350000	2	Stationary
6396	Tissue boxes	14	350000	2	Stationary
6397	Tissue rolls	15	350000	2	Stationary
6398	Air freshener	16	350000	2	Stationary
6399	Marker blue, black and green	17	350000	2	Stationary
6400	Highlighter	18	350000	2	Stationary
6740	Black permanent marker	245	350000	2	Stationary
6401	Envelope small & large (set)	19	350000	2	Stationary
6402	Box files	20	350000	2	Stationary
6403	Sticky notepad	21	350000	2	Stationary
4449	Mouser # 757-TBAW56LM\nSchottky diodes and rectifiers switching diode 80V 215mA	16	350000	2	Parts
4516	Bruches (assorted)	3	350000	2	Stationary
4518	Spray: WD40 400ml	5	350000	2	POLs
4519	PCB Board (Bakelite 6x4 & Fiber - 10 each)	6	350000	2	Parts
4520	Silica Gel packet (5gm, 100 pcs)	7	350000	2	Chemicals
4521	Filament (01 each)\n-PLA, Black\n-PLA, White\n-PLA, Grey	8	350000	2	Other
4522	Tupperware Box (Large)	9	350000	2	Other
4523	Choloroform	10	350000	2	Chemicals
4524	Syringe	11	350000	2	Other
4581	Choloroform	32	350000	2	Chemicals
4583	Filler primer spray	34	350000	2	Chemicals
4584	Glue sticks for printer	35	350000	2	Parts
6496	Cable ties packet	46	350000	2	Other
6497	Coaxial Cable (3mm dia, 50 Ohms) 100ft.	47	350000	2	Fabrication
6728	RG174 GPS Cables	233	350000	2	Parts
6730	N type male connector for LMR	235	350000	2	Parts
6731	Copper sheet	236	350000	2	Fabrication
6732	Fiber PCB boards	237	350000	2	Fabrication
7487	Electronic Accessories	7	200000	2	Other
6941	PVC Steel Rope 	4	200000	2	Other
6950	Travel Expenses and Miscellaneous	13	200000	2	Other
6951	CT-511WB Antistatic wrist stripe	1	200000	2	Other
6952	ASB230X330MM Special Quality Space age Antistatic bags silver /clear original Taiwan  	2	200000	2	Other
6953	ASB300X400MM Antistatic bags	3	200000	2	Other
6954	ASB150X200MM Anti static bags High quality Taiwan	4	200000	2	Other
6955	ESD gloves 1 pair Anti static gloves (ESD electronics work repair gloves M size)	5	200000	2	Other
6956	ESD mat with connection point 4'x1.5'	6	200000	2	Other
7716	18 Watt LED Light	1	160000	2	Other
4358	Pneumatic Circuit (flexible pipes, cross joint, elbow joint, oxygen tank, threaded pipe fittings, valves, rack sheets, mounts, gusset, nuts and bolts, pressure gauge, actuator)	1	350000	2	Parts
4359	Mid Beam Assembly (rail beam plates, angle iron, anchor, barrier test load, nuts & bolts, plates)	2	350000	2	Parts
4360	Front End  Assembly (rail beam plates, angle iron, pulley, mount, pins, bushing, spacer, lock)	3	350000	2	Parts
4361	Launcher Assembly (pulley, bushing, spacer, block plates, connection plates, front & back pins, lock nuts, rope, clevis mouth and connectors)	4	350000	2	Parts
4362	Locking Assembly (angle iron, base plate mild steel, holder, hook, nuts & bolts, spacer)	5	350000	2	Parts
6733	Plastic gloves	238	350000	2	Fabrication
6734	Photographic film paper	239	350000	2	Stationary
6738	Jumper wires	243	350000	2	Parts
4755	Wire Mesh & F450 landing gear	8	350000	2	Other
6379	R&S ZNB8     1311.6010.42 \nVector Network Analyzer, 2 ports, 9KHz – 8.5 GHz, N(f) connectors	1	350000	7	Tools / Test Equipment
7471	Printer laser jet color pro 452 NW	4	200000	7	Office Equipment
7470	Presenter Logitech	3	200000	7	Office Equipment
7469	Multimedia Projector Sony	2	200000	7	Office Equipment
6739	FeCl solution	244	350000	2	Fabrication
6741	Ceramic capacitors 102, 104, 683, 105pF	246	350000	2	Parts
6744	Heat sink	249	350000	2	Parts
6745	N type RS58 crimp connector	250	350000	2	Parts
6747	Glue gun sticks	252	350000	2	Fabrication
6751	Duct tape, Aluminum tape and Fiber tape	256	350000	2	other
6770	Patch cord N type to SMA	275	350000	2	Parts
6771	SMA male to male 6” patch	276	350000	2	Parts
6772	Patch SMA male to female	277	350000	2	Parts
6773	N type SMA adapters	278	350000	2	Parts
6775	Heat sleeve multiple sizes	280	350000	2	Other
6776	Cable ties pack	281	350000	2	Other
6777	Alpha blade, Elfi and drill bits multiple	282	350000	2	Other
6778	Copper rod	283	350000	2	Fabrication
6779	7ft. PVC pipe with hand caps	284	350000	2	Fabrication
6780	Generator plug, wire and female connector	285	350000	2	Parts
6781	DC Battery cable	286	350000	2	Parts
6942	Brush Set	5	200000	2	Other
6940	Chemicals Set	3	200000	2	Chemicals
4364	Carriage Assembly (wheels, side wall, supports, rotating part pins, nuts & bolts)	7	350000	2	Parts
4365	Arresting Mechanism (shock cord, mounts, mount nuts & bolts)	8	350000	2	Parts
4366	Fasteners (actuator snatch block shaft, pulley pin shaft, head support bolt, rail structure joints, steel cable, gusset bolts)	9	350000	2	Parts
4367	Oscillator FOX801BELf	1	350000	2	Parts
4368	Wooden magnet splitter	2	350000	2	Other
4369	Passive analog components for frequency pushing	3	350000	2	Other
4370	Electrician’s gloves made of 12kV high voltage insulation	4	350000	2	Other
4371	Safety goggles	5	350000	2	Other
4372	Acrylic shielding box for injection locking experiment	6	350000	2	Other
4375	Rogger substrate RO 4350B (30mils) 12” x 18”	9	350000	2	Parts
4378	Raw material for waveguide termination	12	350000	2	Parts
4379	Alumina Silica Block for permittivity Testing	13	350000	2	Other
4380	Alumina Silica Block for permittivity Testing	14	350000	2	Other
4381	Teflon Block for permittivity testing	15	350000	2	Other
4384	Cordless screw driver machine	18	350000	7	Tools / Test Equipment
6393	Double punch	11	350000	7	Office Equipment
6392	Stapler	10	350000	7	Office Equipment
4385	Master office chairs	19	350000	7	Office Equipment
4376	SEW 276 HD Telescopic High Voltage Detector	10	350000	7	Parts
4374	White board 3x6 ft.	8	350000	7	Office Equipment
7613	PLA Filement (1 kg spool)	1	300000	2	Other
6784	Plastic spacers pack	289	350000	2	Other
8177	Water Dispenser	2	160000	2	Other
8169	Window blinds	2	160000	2	Other
7715	Flash Tank Accessories	8	160000	2	Other
7714	Flash Tank	7	160000	2	Other
7733	Patch cord cable (3 meter)	6	160000	2	Other
7731	Patch cord cable (2 meter)	5	160000	2	Other
6785	12x12 PCB board	290	350000	2	Parts
8461	Mixture for filling the earthing pit consisting of wood, coal powder, sand and salt	20	350000	2	Other
4671	Control Servos Hitec 5585/ MKSHBL 960	11	350000	2	Parts
4675	Fuel tank 3L for test bench	15	350000	2	POLs
4676	Fuel circuit pipe and fitting	16	350000	2	POLs
4681	AC Power Connector:M16 3 Pin IP67 Waterproof AC/DC Connector	5	350000	2	Parts
4743	Wing Spars & RIBS (1000*950*2mm)	1	350000	2	Other
4744	Tail Spars(2000*950*2mm)	2	350000	2	Other
4745	Kevlar(300 GSm)	3	350000	2	Other
4748	3k Woven 200gsm	1	350000	2	Other
4749	Self-Clenching Standoffs	2	350000	2	Other
4750	Epoxy Resin	3	350000	2	Other
4751	Bonding Adhesive	4	350000	2	Other
4752	Infusion Mesh	5	350000	2	Other
4753	Vacuum Sealing Tape	6	350000	2	Other
4756	Cobolts bits for Launcher	9	350000	2	Other
4757	Mounts for PANTILIT	10	350000	2	Other
4758	Machining Of Metal Parts	1	350000	2	Other
4759	Machining Of Ribs & Bulkheads	2	350000	2	Other
4760	Machining of Pattern	3	350000	2	Other
4761	Counter Sunk m3 bolts	4	350000	2	Other
4764	Fiber sheets for tables	7	350000	2	Other
4765	Launcher cover	8	350000	2	Other
4769	CAP CER 10PF 250V C0G/NP0 0805	1	350000	2	Parts
4770	FIXED IND 330NH 650MA 350 MOHM	2	350000	2	Parts
4771	IC HOT SWAP CTRLR GP 8SOIC	3	350000	2	Parts
4772	IC REG CHARG PUMP -4V 12MA 8SOIC	4	350000	2	Parts
4773	IC OPAMP GP 1 CIRCUIT SOT23-5	5	350000	2	Parts
4774	IC REG LINEAR 5V 50MA 8MSOP	6	350000	2	Parts
4775	CAP CER 0805 1NF 250V X7R 10%	7	350000	2	Parts
4776	FIXED IND 330NH SMD	8	350000	2	Parts
4777	FIXED IND 330NH 780MA 200 MOHM	9	350000	2	Parts
4778	RESHIGHPOWERA 0805 10R 1% 1/2W T	10	350000	2	Parts
4779	MOSFET N-CH 80V 82A LFPAK56	11	350000	2	Parts
4780	IC RF AMP GSM 2GHZ-4GHZ 8HWSON	12	350000	2	Parts
4781	FIXED IND 16NH 700MA 104 MOHM	13	350000	2	Parts
4782	FIXED IND 8.7NH 700MA 109 MOHM	14	350000	2	Parts
4784	RASPBERRY PI HIGH-PRECISION AD/D	16	350000	2	Parts
4389	VP032 High vacuum  pump oil 1 ltr	3	350000	2	POLs
6432	LED Tube lights 	3	350000	2	Building Modification
4785	CBL ASSY SMA PLUG RG142 3.3'	17	350000	2	Parts
4786	CBL ASSY N TYPE PLUG RG174 9.8'	18	350000	2	Parts
4788	Raw material for development of Horn Antenna	20	350000	2	Parts
4791	Push button 22mm	1	350000	2	Parts
4792	LED indicator 22mm	2	350000	2	Parts
4793	Emergency switch	3	350000	2	Other
3003	Scrapper	12	350000	2	Other
4801	Connector (10 way screw terminal)	11	350000	2	Other
4802	Wire roll (0.75 sq. mm)	12	350000	2	Other
4803	Front plate VFD sheet incl. machining	13	350000	2	Other
4804	U Plate for VFD Mounting	14	350000	2	Other
4805	Consumables (screws, cable ties, wire duct, spiral sleeve etc.)	15	350000	2	Other
4806	24 V powe supply for VFD panel	16	350000	2	Parts
4807	24 V power supply for fans	17	350000	2	Parts
4808	DIN Connector (2 pin)	18	350000	2	Other
4809	Airtight boxes (multiple sizes) Large x 05 & Regular x 15	19	350000	2	Other
6957	Contact Cleaner - 600 ml	1	200000	2	Chemicals
6959	Magnifying 50 mm	3	200000	2	Other
6966	Extender cables	10	200000	2	Other
6967	Extender, Jumper Wires and Power Cable	11	200000	2	Other
6968	Power Cord	12	200000	2	Other
6969	PVC Rope 1.2 mm (30 ft * 18/ft =  540)	13	200000	2	Other
6970	Anti Static Mat Green	14	200000	2	Other
4810	Coveralls	1	350000	2	Other
4811	Safety Shoes	2	350000	2	Other
6973	TCS kck to IBD	17	200000	2	Travelling/Boarding/Lodging
6971	Connector N Type Panel Mount	15	200000	2	Parts
4812	Chemical Respirator	3	350000	2	Other
4813	KN-95 Masks	4	350000	2	Other
4814	Safety Goggles	5	350000	2	Other
4815	Face Shield	6	350000	2	Other
4816	Ear Muffs	7	350000	2	Other
4817	Safety Gloves	8	350000	2	Other
4818	Fire Extinguisher	9	350000	2	Other
4819	File Cover (printed)	10	350000	2	Stationary
4820	A4 paper rim	11	350000	2	Stationary
4821	Ball point blue	12	350000	2	Stationary
4822	Gel pen blue & black	13	350000	2	Stationary
4408	Wing Spars CF Sheet(1000*980*2mm)	1	350000	2	Parts
4409	Tail Spars(2000*980*2mm)	2	350000	2	Parts
4410	Kevlar(300 GSm)(15m2)	3	350000	2	Parts
4411	Delta HMI	4	350000	2	Parts
4416	Shock Chord 25 mm Double Jacket Mil Spec 	9	350000	2	Parts
4417	Shock Chord Loops	10	350000	2	Parts
4431	MMRF5014HR5 RF MOSFET\nTransistor 1-2690 MHz, 125 W CW, 50V  	1	350000	2	Parts
4432	Analog Phase Shifter IC HMC928LP5E\n0-13V, 450 degree, 2-4 GHz	2	350000	2	Parts
4433	Analog Phase Shifter SF32A7\n360 degree, SMA, F = 2.0 – 2.5Ghz	3	350000	2	Parts
4434	Mouser # 772-QPA2237\nRF amplifier 0.03-2.5GHz 10W	1	350000	2	Parts
4435	Mouser # 771-BGU8053X\nRF amplifier low noise high linearity	2	350000	2	Parts
4436	Mouser # 584-3010EMS8E-5TRPBF\nLDO voltage regulators 80Vin, 50mA	3	350000	2	Parts
4437	Mouser # 584-LTC1261CS8-4\nSwitching voltage regulators -4V fix switch cap reg volt inverter	4	350000	2	Parts
4438	Mouser # 584-LT4256-1CS8TRPBF\nHot swap voltage controllers 48V latch off mode	5	350000	2	Parts
4439	Mouser # 926-LM7321MF/NOPB\nOperational amplifiers	6	350000	2	Parts
4440	Mouser # 506-MLL1200S\nTE connectivity	7	350000	2	Parts
4441	Mouser # 630-HSMG-C170 	8	350000	2	Parts
4442	Mouser # 771-BZX384-C11F Zener diodes	9	350000	2	Parts
4443	Mouser # 630-HSMYC170\nStandard SMD LEDs 586nm 8mcd	10	350000	2	Parts
4444	Mouser # 652-CRK0612-FZ-R005l\nCurrent sense resistors SMD 0.005ohm	11	350000	2	Parts
4445	Mouser # 71-WSL25126L800FEA\nCurrent sense resistors SMD 0.0068ohm	12	350000	2	Parts
4446	Mouser # 81-BLM21BD102SH1D Ferrite beads 0805 1Kohm Hi speed signal line tape	13	350000	2	Parts
4447	Mouser # 512-BSS84\nMOSFET SOT-23	14	350000	2	Parts
4448	Mouser # 771-BSH103235\nMOSFET Tape 13	15	350000	2	Parts
4430	LV Oscilloscope probe, SP100B	4	350000	7	Parts
4429	HV Oscilloscope probe, CT4024	3	350000	7	Parts
4420	3D printer (Prusa i3 MK3S)	2	350000	7	Machinery/Equipment
6429	FCPM-6000RC \nINTG Frequency, Counter & PWR Meter, Connector type N/BNC 	1	350000	7	Parts
6425	Conference table 8-10 persons with aluminum/fiber sheet on top	1	350000	7	Office Equipment
6419	Honeycomb Vent \nPart # 9500-200-200-K-DC-A	1	350000	7	Parts
4399	Airconditioner 1.5ton\nIncluding complete accessories and installation kits & installation & premises 	1	350000	7	Office Equipment
1280	Helmet ingco	56	350000	2	Other
4823	Pencil packet	14	350000	2	Stationary
4824	Minute sheets	15	350000	2	Stationary
4825	White pen	16	350000	2	Stationary
4826	Glue sticks 	17	350000	2	Stationary
4827	Stapler pins	18	350000	2	Stationary
4828	Stapler	19	350000	2	Stationary
4829	Double punch	20	350000	2	Stationary
4830	Register (300 pages)	21	350000	2	Stationary
4831	Paper clips	22	350000	2	Stationary
4832	Tissue boxes	23	350000	2	Other
4833	Tissue rolls	24	350000	2	Other
4834	Air freshner	25	350000	2	Other
4835	Marker blue, black and green	26	350000	2	Stationary
4836	Highlighter	27	350000	2	Stationary
4837	Envelope small & large (set)	28	350000	2	Stationary
4838	Box files	29	350000	2	Stationary
8185	Yellow A4 Paper	4	160000	2	Other
8186	Envelop All Size (1 set)	5	160000	2	Other
7721	Purchase of double pol breaker & DP for AC	2	160000	2	Other
7720	TL-WR940N TPLINK 450 MBPS with patch cable	1	160000	2	Other
4839	Sticky notpad	30	350000	2	Stationary
4845	Mesh Cloth	36	350000	2	Other
4846	Embroidery Hoop	37	350000	2	Other
4853	Carbon fiber Parts	1	350000	2	Other
4854	Shock cord 25 mm double jacket Mil Spec 	1	350000	2	Other
4855	[FWB-120-S-4P-OB] Fixed wing bundle 44lbs(20 Kg) Parachute	2	350000	2	Other
4856	Gensace LiPo Battery 4S 4000 mAh	1	350000	2	Other
4857	AV battery (4S 10000 mAh)	2	350000	2	Other
4859	Pitot Tube	4	350000	2	Other
4860	BMS Engine Battery	5	350000	2	Other
4861	Connectors (MR30,XT30,MT30,XT90)	6	350000	2	Other
4862	12 Pin Connector	7	350000	2	Other
4863	Power Module 	8	350000	2	Other
4864	Voltage Regulator (Boost Buck)	9	350000	2	Other
4868	5/2 Solenoid Valve	13	350000	2	Other
4869	Wire mesh 40mm	14	350000	2	Other
4870	Wire mesh 30mm	15	350000	2	Other
4871	Ethernet shell	16	350000	2	Other
4872	Mil connectors 20 pin	17	350000	2	Other
4873	Mil connectors 40 pin	18	350000	2	Other
4874	Cleaning Cloth, Poster, Table Cover	19	350000	2	Other
4875	Epoxy Reisin	20	350000	2	Other
4876	Power Distrbution	21	350000	2	Other
4877	Metal Files	22	350000	2	Other
4881	Soldering Consumables	26	350000	2	Other
4882	Heat Shrink	27	350000	2	Other
4883	Wire Mesh	28	350000	2	Other
4885	Hoisting Guide Rail Cutting	30	350000	2	Other
4886	Sand paper	31	350000	2	Other
4887	Auto Paint	32	350000	2	Other
4888	Nitrile Gloves Box	33	350000	2	Other
4889	Shackle for parachute release	34	350000	2	Other
4890	Hardpoint inserts	35	350000	2	Other
4891	Launcher Hoisting 30m wire	36	350000	2	Other
4459	M8 Screws for Waveguide Assemblies	2	350000	2	Parts
4452	Digital Storage Oscilloscope (GWInstek GDS-3154)	1	350000	7	Machinery/Equipment
4462	Raw Material for Power Combiner(Aluminum sheets ,M2 screws, Spade connectors)	5	350000	2	Parts
4466	SMA Connectors	9	350000	2	Other
6484	‘Ma-50’ MASTTOP up to 5’-Mast	8	350000	7	Parts
6479	Bi-Axial Antenna Positioner 180º AZ and 40º EL, Pay load: max. 65kg, max. 2.4m antenna, accuracy 0.2º including desktop controller 	3	350000	7	Machinery/Equipment
4479	Threaded Rods	7	350000	2	Other
4480	Clevis	8	350000	2	Other
4481	Buckle Linkage	9	350000	2	Other
4482	Servo Horn	10	350000	2	Other
4484	Brush	12	350000	2	Other
4489	3 Core Wire (1.5mm)	17	350000	2	Other
4490	Multicore wire	18	350000	2	Other
4493	Cable Channel	21	350000	2	Other
4494	Nuts+ Bolts+ Washer	22	350000	2	Other
4495	Wire Mesh	23	350000	2	Other
4497	Din Rail Terminal Block Connectors	25	350000	2	Other
4500	Drill Bits	28	350000	2	Parts
4501	Drill Tap Bits	29	350000	2	Parts
4483	Wood Sealer	11	350000	2	Other
4502	DB9 Connectors	30	350000	2	Other
4504	Solenoid Valve Local	1	350000	2	Other
4514	Double sided tape 30mm	1	350000	2	Stationary
4515	Micro fiber cloth (pack of 3)	2	350000	2	Cleaning Material
4534	Printer Toner & Refill	21	350000	2	Other
4561	Auto door closers	12	350000	2	Other
4562	Storage boxes for avionics/hardware inventory	13	350000	2	Other
4563	Male/Female headers	14	350000	2	Other
4566	Heat sleeves pack	17	350000	2	Other
4568	USB to RS485 convertor	19	350000	2	Other
4571	Tubelight replacement in lab	22	350000	2	Other
4572	Servo wire extensions	23	350000	2	Other
4574	WD40: SPRAY	25	350000	2	POLs
4575	Engraving bit	26	350000	2	Parts
4576	PCB Board	27	350000	2	Other
4577	Filament: PLA, Black	28	350000	2	Parts
4578	Filament: PLA, White	29	350000	2	Parts
4579	Filament: PLA, Grey	30	350000	2	Parts
4580	Tupperware Box 	31	350000	2	Other
4582	Syringe	33	350000	2	Other
6546	SMA connectors	34	350000	2	Parts
6547	Cable with SMA connector	35	350000	2	Parts
6548	Ethernet Cable	36	350000	2	Parts
6551	LiPo Battery 3S (5000mAh)	39	350000	2	Parts
4570	Soldering sucker	21	350000	7	Tools / Test Equipment
4536	Bench Grinder (500W)	2	350000	7	Tools / Test Equipment
6552	LiPo Transmitter Battery (GensAce 3S 450mAh)	40	350000	2	Parts
4564	Ethernet camera with battery	15	350000	7	Office Equipment
5006	Joystick	35	350000	2	Other
6554	Copper Tubing (22” gauge, 0.5 inch dia) 13ft.	42	350000	2	Fabrication
6555	Gens ace 5000 mAh 3S Battery for Quadcopter	60	350000	2	Parts
6556	Transmitter Battery Local	61	350000	2	Parts
6561	Coaxial Cable T2C 500-97	66	350000	2	Parts
6562	N type male crimping connector	67	350000	2	Parts
4892	Soft Start For Hoisting Motor 	37	350000	2	Other
4893	Balancing Weights	38	350000	2	Other
4894	Rubber Gourmets	39	350000	2	Other
4895	Drill Bits	40	350000	2	Other
4896	Wax	41	350000	2	Other
4901	bracket bearing	46	350000	2	Other
4902	Dead weight Inserts	47	350000	2	Other
4903	spring for Full scale	48	350000	2	Other
4958	HDMI cables for GCS IPCs	30	350000	2	Parts
4960	Gannet Fuel lines and Masking tape	32	350000	2	Parts
4964	M1475A: JR MAGNETRON OEM: NJR	1	350000	2	Other
4984	Balsa	13	350000	2	Other
4985	Polyester Resin	14	350000	2	Other
4986	Matt Fiber	15	350000	2	Other
4987	Gel Coat	16	350000	2	Other
4988	Pigment	17	350000	2	Other
4989	Gel Coat Spray Gun	18	350000	2	Other
4990	Vacuum Line	19	350000	2	Other
4991	Manufacturing of Nose Wheel Assembly	20	350000	2	Other
4994	Solenoid Valve	23	350000	2	Other
4995	Launcher PWM Mod, Servos, Wire Mounts	24	350000	2	Other
5004	Screws	33	350000	2	Other
5005	Connectors	34	350000	2	Other
5007	IPC	36	350000	2	Other
5011	Mounting	40	350000	2	Other
5013	Power cable 2.5mm, 3 core, 5 meters	42	350000	2	Other
5014	Power cable 1.5mm, 3 core 3 meters	43	350000	2	Other
5015	DC Wire(4 core,3m)	44	350000	2	Other
5016	DC Wire(2 core,3m)	45	350000	2	Other
5017	Power Switch	46	350000	2	Other
5019	Spiral Sleeve	48	350000	2	Other
5020	Cable channeling	49	350000	2	Other
5021	Heat shrinks	50	350000	2	Other
6502	8 GB RAM for PCs	52	350000	7	Parts
6557	LiPo Safe bags	62	350000	7	Other
6506	SD Card 16GB	56	350000	7	Parts
6563	Patch cord (N male to SMA mode)	68	350000	2	Parts
6567	Gens ace 3000 mAh 3S 11.1V Lipo Battery	72	350000	2	Parts
6571	Zip ties pack	76	350000	2	Other
6572	Quadcopter Frame - Metal	77	350000	2	Fabrication
6573	Quadcopter Frame - Fiber Glass	78	350000	2	Fabrication
6576	Propellers 10 inch	81	350000	2	Parts
6587	Connectors	92	350000	2	Parts
6590	Propellers 	95	350000	2	Parts
6591	Damper	96	350000	2	Parts
6592	Soldering wire	97	350000	2	Other
6593	Zip ties pack	98	350000	2	Other
6595	Aluminum Rods	100	350000	2	Fabrication
6596	Aluminum Boom	101	350000	2	Fabrication
6598	Connectors	103	350000	2	Parts
6599	Clips	104	350000	2	Parts
6600	Coaxial Cable (2m)	105	350000	2	Parts
1613	USB Connector Type A	44	350000	2	Other
1634	Freight/ Hnadling	6	350000	3	Other
6332	SMD soldering station with accessories	1	350000	7	Tools / Test Equipment
6508	Spectrum Analyzer with required Accessories	58	350000	7	Tools / Test Equipment
6602	N type male panel module	107	350000	2	Parts
6603	N type male to female adapter	108	350000	2	Parts
6604	N type male connectors	109	350000	2	Parts
5022	Solder Wire	51	350000	2	Other
5023	Powder Coating	52	350000	2	Other
5024	Sheet Metal	53	350000	2	Other
5025	Epoxy	54	350000	2	Other
5026	Foam	55	350000	2	Other
7281	Laptop Core i3 10th Gen with 14' LCD, 4 GB RAM, 1TB HDD	1	400000	2	Parts
7282	Laser Jet Printer	2	400000	2	Parts
7283	Custom Built Data Logger Enclosure	3	400000	2	Parts
7284	Custom Built Bell Logger Enclosure	4	400000	2	Parts
7285	Interface Card for Data and Bell Logger	5	400000	2	Parts
7286	Military Equipment Box	6	400000	2	Parts
5067	Fasteners, spanner, silicon pipe, micro fiber cloth, clear coat	20	350000	2	Parts
5068	Nuts & bolts, Loctite 5543, Teflon tape, red oxide, cable ties,3D printing filament,	21	350000	2	Parts
6607	LCD Adapter	112	350000	7	Parts
6605	RAM 8 GB	110	350000	7	Parts
6606	LCDs	111	350000	7	Parts
6586	Storage boxes large for tools	91	350000	7	Other
6627	12 AWG wires (pack of 1m red, 1m black)	132	350000	2	Parts
6628	Servo Extensions (10 cm, 15 cm, 20 cm – 10 pcs each)	133	350000	2	Parts
6629	Heat Shrinks pack	134	350000	2	Other
6639	Power supply adapter 24V 4A	144	350000	2	Parts
6640	SMA Male to male adapter	145	350000	2	Parts
6642	SMA Clamp	147	350000	2	Parts
6650	Propeller sets	155	350000	2	Parts
6651	Quadcopter stand / Carbon Fiber landing gear	156	350000	2	Parts
6654	N type female to male connectors	159	350000	2	Parts
6655	Input pins M/F	160	350000	2	Parts
6657	Small connectors	162	350000	2	Parts
6659	Small RG58 crimp connector	164	350000	2	Parts
6662	Propeller sets	167	350000	2	Parts
6663	Heat shrinks	168	350000	2	other
6664	Ferric solution 	169	350000	2	Fabrication
6666	Breadboards	171	350000	2	Fabrication
6667	Copper clad 	172	350000	2	Fabrication
6669	Resistors & Capacitors (multiple)	174	350000	2	Parts
6671	Teflon pipe 10m	176	350000	2	Parts
6672	Nozzle spray for paint	177	350000	2	Fabrication
6673	Pipe hand cap	178	350000	2	Parts
6675	Sticker paper sheets 	180	350000	2	Stationary
6685	Silver Paste	190	350000	2	Fabrication
6688	Inductors pack (5.5 – 27.3nH)	193	350000	2	Parts
6689	Inductors pack (1.65 – 12.55nH)	194	350000	2	Parts
5069	Soldering iron element, soldering wire	22	350000	2	Parts
5070	Load cell	23	350000	2	Parts
5071	Machining of metal parts	24	350000	2	Parts
5072	Metal Sheet cutting	25	350000	2	Parts
5073	Acrylic Sheet cutting, screws	26	350000	2	Parts
5075	Huntsmen 420 bonding adhesive	28	350000	2	Other
5076	3x Helmets & 3x rubber gloves	29	350000	2	Other
5277	GRM31CR60J227ME11L  Multilayer Ceramic Capacitors MLCC - SMD/SMT 220UF 6.3V 20% 1206	1	200000	2	Parts
5278	12063C104JAT2A  Multilayer Ceramic Capacitors MLCC - SMD/SMT 25V 0.1uF X7R 1206 5%	2	200000	2	Parts
571	M3 screws + nuts	25	350000	2	Other
581	Power supply repair	35	350000	3	Equipment Repairs & Maintenance
5274	SKT011409NC-CP Norm. Closed Thermostat Fahrenheit	5	200000	7	Parts
6686	Fixed Click Type RF Torque Wrench	191	350000	7	Tools / Test Equipment
6634	Eachine EV800 5.8 GHz Goggles	139	350000	7	Other
5279	GCG31MR71E225JA12L  Multilayer Ceramic Capacitors MLCC - SMD/SMT	3	200000	2	Parts
6737	PCB Tray	242	350000	7	Fabrication
6690	Capacitors pack (0.1pF – 100pF)	195	350000	2	Parts
6691	Inductors pack (0.3 – 33nH	196	350000	2	Parts
6692	Thick Film Surface mount Resistors pack	197	350000	2	Parts
6693	SMA connectors	198	350000	2	Parts
6694	Power Werx RF Adapter Kit	199	350000	2	Parts
6695	Pro Power RG 58C (Coaxial Roll)	200	350000	2	Fabrication
6696	Capacitors pack (0.1pF – 100pF)	201	350000	2	Parts
6698	Tarot 680 Pro TL2829 T series 1355 Carbon Fiber CW and CCW propellers	203	350000	2	Parts
6707	Buck converters	212	350000	2	Parts
6708	Switch/connectors	213	350000	2	Parts
6709	Multiple resistors & capacitors pack	214	350000	2	Parts
6710	Landing gear for Skycam	215	350000	2	Parts
6712	Drill bits	217	350000	2	Other
5283	EEEFT1V681UP Panasonic 680?F Electrolytic Capacitor 35V dc, Surface Mount - EEEFT1V681UP	6	200000	2	Parts
6713	Bit Holder	218	350000	2	other
5292	NS10155T220MNA  FIXED IND 22UH 3.12A 45.6MOHM SM	12	200000	2	Parts
5285	EEE-FC1V221P Aluminum Electrolytic Capacitors - SMD 220uF 35V	7	200000	2	Parts
6714	Paint on acrylic box	219	350000	2	Fabrication
6715	SMA male & female connectors	220	350000	2	Parts
5287	RT1206FRE0710KL  Thin Film Resistors - SMD 1/4W 10K ohm 1% 50ppm	9	200000	2	Parts
5289	1206ZD105KAT2A Multilayer Ceramic Capacitors MLCC - SMD/SMT 10V 1uF X5R 1206 10%	10	200000	2	Parts
6716	LMR wire 	221	350000	2	Fabrication
5286	T491A105K035AT CAP TANT 1UF 10% 35V 1206	8	200000	2	Parts
5281	VJ1206A103JXXTW1BC  Multilayer Ceramic Capacitors MLCC - SMD/SMT 1206 0.01uF 25volts C0G 5%	4	200000	2	Parts
6720	FeCl packs	225	350000	2	Fabrication
6727	SMA connector	232	350000	2	Parts
5282	VJ1206A100JXXCW1BC  Multilayer Ceramic Capacitors MLCC - SMD/SMT 1206 10pF 25volts C0G 5%	5	200000	2	Parts
5290	VJ1206A103JXXTW1BC Multilayer Ceramic Capacitors MLCC - SMD/SMT 1206 0.01uF 25volts C0G 5%	11	200000	2	Parts
6754	Turnigy High Capacity 12000mAH 6S12C Multi Rotor LiPo Pack	259	350000	2	Parts
6759	BNC Male to Female and Female to Female SMA connectors local	264	350000	2	Parts
6760	Patch cord including connectors	265	350000	2	Parts
6762	Generator fuel	267	350000	2	POLs
6763	Hot Glue gun sticks	268	350000	2	Fabrication
6764	Buck converters	269	350000	2	Parts
5307	151350606 CLICKMATE 6 CIRCUIT 600MM	24	200000	2	Parts
5303	151350406 Rectangular Cable Assemblies Click mate 4CKT CBL ASSY SR 600MM BEIGE	20	200000	2	Parts
6765	Multiple wire leads	270	350000	2	parts
6767	24V 8.3A DC Power Supply Adapter	272	350000	2	Parts
6769	6mm red & black wire	274	350000	2	Parts
5300	151350306 CLICKMATE 3 CIRCUIT 600MM	18	200000	2	Parts
5306	151350500  Cable Assembly UL 1061 0.05m 24AWG Wire to Board to Wire to Board 5 to 5 POS M-M Crimp CLIK-Mate Bag	23	200000	2	Parts
5309	15135-0806  Rectangular Cable Assemblies CLIK-MATE 8CKT CBL ASSY SR 600MM BEIGE	26	200000	2	Parts
5312	151350200 Rectangular Cable Assemblies Click mate 2CKT CBL ASSY SR 50MM BEIGE	29	200000	2	Parts
5294	CLF10060NIT-101M-D  Power Inductors - SMD 100uH +/-20% AECQ200 -55 to +150C	13	200000	2	Parts
5296	LM2596T-3.3/NOPB  IC REG BUCK 3.3V 3A TO220-5	15	200000	2	Parts
5298	LM2596T-5.0/LF03  Switching Voltage Regulators SIMPLE SWITCHER & reg; 4.5V to 40V, 3A Low Component Count Step-Down Regulator 5-TO-220	16	200000	2	Parts
5310	5031590800 CONN RCPT 8POS 0.059 TIN PCB	27	200000	2	Parts
4630	3k Wooven 200gsm	5	350000	2	Parts
5308	503159-0600  Headers & Wire Housings 1.50MM CLIK-MATE REC 06P VT TH	25	200000	2	Parts
5304	5031590400  Headers & Wire Housings 1.50MM CLIK-MATE REC 04P VT TH	21	200000	2	Parts
5305	5031590500  Headers & Wire Housings 1.50MM CLIK-MATE REC 05P VT TH	22	200000	2	Parts
5302	5031590300 CONN RCPT 3POS 0.059 TIN PCB	19	200000	2	Parts
5295	1N5822RL  DIODE SCHOTTKY 40V 3A DO201AD	14	200000	2	Parts
4585	Selector switch (3 way)	1	350000	2	Parts
4586	Relays (double pole double throw)	2	350000	2	Parts
4587	Indication Lamp 24V DC	3	350000	2	Parts
4588	Push button	4	350000	2	Parts
4589	Breaker 5/10 A	5	350000	2	Parts
4590	Bulb (200W)	6	350000	2	Other
4591	Bulb holder	7	350000	2	Parts
4592	Screw Terminal Connector Strip	8	350000	2	Other
4593	VARIAC	9	350000	2	Other
4594	Diode Bridge	10	350000	2	Parts
4595	Electrolytic Capacitor	11	350000	2	Other
4596	PCB board (5" x 4.4")	12	350000	2	Other
4597	4 core, 0.75 mm wire (10 meter)	13	350000	2	Parts
4598	24V DC Power supply	14	350000	2	Parts
4599	Panel board/wooden board	15	350000	2	Other
4600	24V to 4-20 mA Module	16	350000	2	Parts
4611	300 G 2x 2 Twill Weave Kevlar Cloth (1000 mm) k-22-300-100	9	350000	2	Parts
4612	Lantor Soric SF infusion core (1270 mm) T= 2mm SORIC2	10	350000	2	Other
4613	High Strength Carbon Fiber Sheet T=2mm,1000 mm x980 mm (CFS-RI-2-0950)	11	350000	2	Parts
4614	High Strength Carbon Fiber Sheet T=2mm,2000 mm x 980 mm  (CFS-RI-1-1900)	12	350000	2	Parts
5077	Toner refill for documentation	30	350000	2	Other
5079	PVC tube roll (100m)	2	350000	2	Other
5080	U lugs (packet)	3	350000	2	Other
5081	Metal sheet plate	4	350000	2	Other
5082	Hole saw	5	350000	2	Parts
5107	Button Cell	15	350000	2	Other
5108	JST Connectors Box	16	350000	2	Other
5109	Silicon Wires	17	350000	2	Other
5111	SD Card	19	350000	2	Parts
5112	SD Card Modules	20	350000	2	Parts
5113	Sensor Interfacing PCB	21	350000	2	Parts
7000	12 core Data cable	3	200000	2	Other
7002	10 wire Ribbon 	5	200000	2	Other
7004	Cat6 Cable Roll	7	200000	2	Other
7009	60 wire Ribbon 	12	200000	2	Other
5179	Sheet metal (Clamp fabrication)	33	350000	2	Other
5181	DC Wire	35	350000	2	Other
5459	PSD813F2A-90MI    QFP52 ST	2	200000	2	Parts
6798	NS10155T220MNA Fixed Inductors 10155 22uH 45.6mOhms +/-20% 3370mA	5	200000	2	Parts
6795	1805327 GREEN POWER CONNECTOR PLUG TERM BLOCK PLUG 4POS STR 5.08MM	2	200000	2	Parts
4932	Toggle Switch Large	4	350000	2	Other
6796	826646-2 3.3V GND 2 POSITIONS HEADER	3	200000	2	Parts
4626	Heat Shrinks (for AV & GCS circuitry)	1	350000	2	Other
4627	Solder Wires	2	350000	2	Other
4628	Microcontroller for GCS joystick testing	3	350000	2	Parts
4631	CF UD (250 gsm UD)	6	350000	2	Other
4632	Peel Ply (180 GSM)	7	350000	2	Other
4633	Self-Clenching Standoffs	8	350000	2	Other
4634	Epoxy Resin	9	350000	2	Chemicals
4635	Bonding Adhesive	10	350000	2	Chemicals
4638	Paint	13	350000	2	Stationary
4639	Sand Paper	14	350000	2	Stationary
4640	Primer	15	350000	2	Chemicals
4641	Polycate	16	350000	2	Chemicals
4642	Fasteners	17	350000	2	Other
4643	Vacuum Bagging Sheet	18	350000	2	Other
4644	Wax	19	350000	2	Chemicals
4645	Thinner	20	350000	2	POLs
4646	Tacky tape(Roll)	21	350000	2	Other
4647	Infusion mesh(Infusion Net)	22	350000	2	Other
4648	Balsa	23	350000	2	Other
4649	Polyester Resin(Mold Making)	24	350000	2	Chemicals
4650	Matt Fiber(Mold Making)	25	350000	2	Other
6817	JR16WP-7S(71) Standard Circular Connector PLUG IP67 7POS FEM	24	200000	2	Parts
4651	Gel Coat(Mold Making)	26	350000	2	Chemicals
4652	Pigment(Aircraft Coloring)	27	350000	2	Other
4655	Paint brushes	30	350000	2	Stationary
4656	Buffing disc	31	350000	2	Other
4657	Grinder discs	32	350000	2	Other
4658	Waxing cloth	33	350000	2	Other
4660	Vacuum Line (For line replacement of Vacuum pump)	35	350000	2	Parts
6816	JR16WR-7S(71) Standard Circular Connector RCPT IP67 7POS FEM	23	200000	2	Parts
6815	JR16WP-7P(71) Standard Circular Connector PLUG IP67 7POS MALE	22	200000	2	Parts
6814	TM4C123GH6PMI IC MCU 32BIT 256KB FLASH 64LQFP	21	200000	2	Parts
6813	MAX232ACSE+ IC RS-232 DRVR/RCVR 16-SOIC	20	200000	2	Parts
6802	LM2596T-3.3/NOPB IC REG BUCK 3.3V 3A for microcontroller	9	200000	2	Parts
6801	LM2596T-5.0/LF03 IC REG BUCK 5V 3A TO 220-5 (24vdc change 5 volts for CCT )	8	200000	2	Parts
6799	CLF10060NIT-101M-D Fixed Inductors 100uH +/-20% AECQ200 -55 to +150C	6	200000	2	Parts
6803	EEE-FT1V681UP CAP ALUM 680UF 20% 35V SMD	10	200000	2	Parts
6800	1N5822RL Schottky Diodes & Rectifiers Vr/40V Io/3A	7	200000	2	Parts
6804	EEE-FC1V221P CAP ALUM 220UF 20% 35V SMD	11	200000	2	Parts
6805	GRM31CR60J227ME11L MLCC - SMD/SMT 220UF 6.3V 20% 1206	12	200000	2	Parts
6812	LFXTAL003240Bulk Crystals 16.0MHz	19	200000	2	Parts
6808	VJ1206A103JXXTW1BC MLCC - SMD/SMT 1206 0.01uF 25volts C0G 5%	15	200000	2	Parts
6811	RT1206FRE0710KL Thin Film Resistors - SMD 1/4W 10K ohm 1% 50ppm	18	200000	2	Parts
6809	VJ1206A100JXXCW1BC MLCC - SMD/SMT 1206 10pF 25volts C0G 5%	16	200000	2	Parts
6810	T491A105K035AT CAP TANT 1UF 10% 35V 1206	17	200000	2	Parts
6806	GRM319R71E104JA01D MLCC - SMD/SMT 0.1UF 25V 2% 1206	13	200000	2	Parts
6807	GCG31MR71E225JA12L MLCC - SMD/SMT 1206 2.2uF 25volts X7R 5%	14	200000	2	Parts
4688	Power cable 2.5mm, 3 core, 5 meters	12	350000	2	Other
4689	Power cable 1.5mm, 3 core 3 meters	13	350000	2	Other
4690	Misc: Wiring, Charging Circuit, AC to DC converter, PDB, Custom Electronics, \nMounting	14	350000	2	Other
4691	Spiral Sleeve 10m,8mm	15	350000	2	Other
4692	Cable channeling	16	350000	2	Other
4693	Nuts + Bolts + Washer	17	350000	2	Other
4694	Heat shrinks: Assortment	18	350000	2	Other
4695	Solder Wire:1 Spool	19	350000	2	Parts
4696	Black spray paint: Spray Paint, Black	20	350000	2	Other
4697	DC Wire :4 Core, 3m	21	350000	2	Other
4698	Sheet Metal (3 mm) 	22	350000	2	Other
4700	PCB Boards FR4	24	350000	2	Other
4701	Fuel tank 3L for test bench 	25	350000	2	POLs
4702	Fuel circuit pipe and fitting 	26	350000	2	Other
4703	Honeycomb 2mm 2440x1220x2mm sheet 3sqm 	27	350000	2	Parts
4704	Gel coat spray with cup sets 	28	350000	2	Chemicals
4708	NGK CM6 CM6 spark plugs	4	350000	2	Parts
4709	26x10 Prop	5	350000	2	Other
4710	28x10 Prop	6	350000	2	Other
4715	"Remove Before Flight" Tags	11	350000	2	Stationary
4684	Master Office Chairs	8	350000	7	Furniture
4717	ALM LED	13	350000	2	Other
4729	Marlin Metal parts machining	3	350000	2	Other
4731	precision hexagon steel wet film comb (with delivery)	5	350000	2	Other
165	Spray paint	23	350000	2	Other
5209	DC voltage display meter	13	350000	2	Parts
5916	Hotwire Cutter	8	350000	7	Tools / Test Equipment
6823	Data Cable 12 core roll	30	200000	2	Parts
6822	JR16WCC-10(71) Standard Circular Connector STRAIN RLF JR16 10MM	29	200000	2	Parts
6819	JR16WP-10P(71) Standard Circular Connector PLUG IP67 10POS MALE	26	200000	2	Parts
6820	JR16WR-10S(71) Standard Circular Connector RCPT IP67 10POS FEM	27	200000	2	Parts
6818	JR16WR-7P(71) Standard Circular Connector RCPT IP67 7POS MALE	25	200000	2	Parts
6824	Enclosur front plate	31	200000	2	Parts
6062	MSI Nvidia GeForce GTX 1070 Armour 8GB	96	350000	7	Parts
6058	Gigabyte Z390 AORUS PRO	92	350000	7	Machinery/Equipment
4736	Resin infusion Silicone connector	10	350000	2	Other
6056	Folding chairs	90	350000	7	Furniture
4737	Resin infusion line clamp	11	350000	2	Other
4738	VB160 vacuum bagging film LFT (1520mm) 100mm	12	350000	2	Other
4739	Vacuum Bagging Sealant Tape 15m Box of 20	13	350000	2	Other
4740	FM100 infusion mesh (1050mm) 100m roll	14	350000	2	Other
4741	Straight hose connector 6mm	15	350000	2	Other
4742	Tee hose connector 6mm	16	350000	2	Other
6958	Brush Set	2	200000	2	Other
4794	Key switch	4	350000	2	Parts
4795	Hour meter 24 V	5	350000	2	Parts
4796	Cooling Fan 24 V	6	350000	2	Parts
4797	4 pole relays	7	350000	2	Other
4798	4 pole relay base	8	350000	2	Other
4799	Box for relay	9	350000	2	Other
4800	DIN rail	10	350000	2	Other
4979	Buffing Compound	8	350000	2	Other
8178	Electrical Stove	3	160000	2	Other
7647	Tester	13	160000	7	Tools / Test Equipment
5036	Repsol Extra Coolant 1 Liter Pack	9	350000	2	Chemicals
6055	Extension boards	89	350000	7	Tools / Test Equipment
6061	Corsair VS650	95	350000	7	Parts
6077	Mouse Logitech B100	111	350000	7	Parts
6076	Keyboard Logitech K120	110	350000	7	Parts
4841	UV Solder Mask (10CC Syringe)	32	350000	2	Other
6074	RAM 8GB DDR3	108	350000	7	Parts
4842	OHP sheet	33	350000	2	Other
4904	glass for material testing	49	350000	2	Other
4905	Breaker double pole	50	350000	2	Other
4906	5 pin connector	51	350000	2	Other
4908	led volt meter	53	350000	2	Parts
4909	capacitor for hoist motor	54	350000	2	Parts
4910	Selector	55	350000	2	Other
4911	Nylon Chrods	56	350000	2	Other
4912	Spiral	57	350000	2	Other
4913	Coverall	58	350000	2	Other
7705	High-Density Lithium-ion compact battery pack for prototype ROV	1	300000	7	Machinery / Equipment
4847	Dremel Tool	38	350000	7	Tools / Test Equipment
4899	Flexible shaft (dremill)	44	350000	7	Other
4898	Pliers	43	350000	7	Other
6069	Seagate Baracuda 3.5"	103	350000	7	Parts
6068	A4Tech Fstyler FG1010	102	350000	7	Parts
6053	I/P Power Plug socket	87	350000	7	Parts
6073	HDD Hard Drive 1 TB WD Purple SATA	107	350000	7	Parts
7700	Floor Tiling	1	300000	7	Other
7688	Vertical Mill (13" x 11" x 5' with) 03 stepper motors, in metric measurements (with hand wheels on the rear shafts of the stepper motors for manual control)	1	300000	7	Machinery / Equipment
7689	Rotary table with stepper motor for use with lathe and mill setup (for use as a 4th axis on the mill)\r\nOperator Console (in the form of a dedicated computer system with Ubuntu Linux-based OS with Linux CNC 4-axis CNC software preinstalled)\r\n4- axis driver board and power supply installed inside the computer to run either the mill or late, one at atime	1	300000	7	Other
4914	spray paint black	59	350000	2	Chemicals
4915	Lubrication Oil	60	350000	2	POLs
4916	Safety blocks	61	350000	2	Other
4917	Disposable epoxy cups	62	350000	2	Other
4933	Toggle Switch Large	5	350000	2	Other
4934	Toggle switch with cover	6	350000	2	Other
4935	Momentary Push button	7	350000	2	Other
4937	Relay with base	9	350000	2	Other
4940	Capacitor (fuel pump)	12	350000	2	Other
4941	Din rail	13	350000	2	Other
4942	Wire Mesh	14	350000	2	Other
4943	Wire Holder clip	15	350000	2	Parts
4843	Squeegee	34	350000	2	Other
4944	Electric Box	16	350000	2	Parts
4953	SD card	25	350000	2	Parts
4945	Wire Routing	17	350000	2	Parts
4946	Wire Battery	18	350000	2	Parts
4947	Consumables	19	350000	2	Other
4948	Proper configuration & installation of ROTAX 912	20	350000	2	Other
4949	Experimental aircraft fuel system for ROTAX	21	350000	2	Other
4967	Switch Mode Power Supply Components (MOSFETS, TRANSFORMERS, I/O Rectifier\n & Filters, Feedback control circuit)	2	350000	2	Parts
7290	USP 3.0 DVD/ CD-RW External	10	400000	2	Parts
4968	Raw material for waveguide termination (Silicon carbide & Machining)	3	350000	2	Parts
4971	HDMI Cables	6	350000	2	Other
4972	CF UD	1	350000	2	Other
4973	Peel Ply	2	350000	2	Other
4974	Paint	3	350000	2	Other
4975	Sand Paper	4	350000	2	Other
4976	Primer	5	350000	2	Other
4977	Polycate	6	350000	2	Other
4978	Fasteners	7	350000	2	Other
4980	Plastic sheet	9	350000	2	Other
4981	Wax	10	350000	2	Other
4982	Thinner	11	350000	2	Other
4983	Breather	12	350000	2	Other
7287	Media Converter with optical cables	7	400000	2	Parts
5038	Engine Oil for Rotax Engine 1L	11	350000	2	POLs
5041	Raw material for MR-ZERO Casing Assembly	1	350000	2	Other
5043	15 U casing	3	350000	2	Other
5044	N to N Adapter	4	350000	2	Other
5045	N type connector jack	5	350000	2	Other
5046	Co axial cable(1m) RG-142 Cable N-N Male connector	6	350000	2	Other
5047	ADF 4351 Evaluation board	7	350000	2	Other
5049	3rd porthole pneumatic fittings	2	350000	2	Parts
5050	Bearing for reduced weight pulleys	3	350000	2	Parts
5051	Dampers for Diesel Generator	4	350000	2	Parts
5052	hydraulic damper	5	350000	2	Parts
5053	Hydraulic damper oil and fittings	6	350000	2	Parts
5054	carriage bearing (for AL carriage)	7	350000	2	Parts
5055	Pneumatic Fittings SS	8	350000	2	Parts
5056	Pulley Sets (Movable)	9	350000	2	Parts
5057	Pulley Sets (Stationary)	10	350000	2	Parts
5059	Clevis plates	12	350000	2	Parts
5062	Cutter, Nylon straps, Nylon stitching, Rubber pad	15	350000	2	Other
5063	Wire rope (8mm, /m)	16	350000	2	Other
5066	Parachute release mechanism machining	19	350000	2	Parts
5083	Rubber plug	6	350000	2	Other
4930	Red lamp	2	350000	7	Parts
5084	Glands	7	350000	2	Parts
5086	Front panel tellies	9	350000	2	Other
5087	Gas kit sealing (ft)	10	350000	2	Other
5088	Diode 	11	350000	2	Other
5090	Holding allen key screws	13	350000	2	Other
5091	VFD PCB	14	350000	2	Parts
5092	Signal Generator knob display	15	350000	2	Other
5098	Microcontroller Arduino Uno	6	350000	2	Parts
5099	Battery 2S	7	350000	2	Other
5100	Resistors, Capacitors	8	350000	2	Other
5101	Jumper Wires  (1x40) Male-Male	9	350000	2	Other
5102	Jumper Wires  (1x40) Male-Female	10	350000	2	Other
5103	Jumper Wires  (1x40) Female-Female	11	350000	2	Other
5104	PCB Boards FR4 (6x4) Single Sided	12	350000	2	Other
5105	Sensor Housing Filament (ABS)	13	350000	2	Other
5106	Real Time Clock for Micro-controllers	14	350000	2	Other
7085	Electrical Wiring for Workstation terminals in design lab	3	300000	7	Parts
5147	Square Channel(1"x1"x360mm)	1	350000	2	Parts
5148	Square Channel(1"x1"x310mm)	2	350000	2	Parts
5149	Base Plate(FS)	3	350000	2	Parts
5150	Base Plate (SD)	4	350000	2	Parts
5151	Base Peg(FS)	5	350000	2	Parts
5152	Base Peg(SD)	6	350000	2	Parts
5153	Platform Peg(SD)	7	350000	2	Parts
5154	Platform Peg(FS)	8	350000	2	Parts
5155	Platform Plate(FS)	9	350000	2	Parts
5156	Platform Plate(SD)	10	350000	2	Parts
5157	Motor_Arm (for Full Scale)	11	350000	2	Parts
5158	Motor_Arm ( for SD)	12	350000	2	Parts
5159	Threaded rod(M5x500mm)	13	350000	2	Parts
5160	Connecting rod(M8x40mm)	14	350000	2	Parts
5161	Connecting rod(M5x40mm)	15	350000	2	Parts
5162	Nema_42_motor_mount	16	350000	2	Parts
5163	Threaded rod(M8x500mm)	17	350000	2	Parts
5164	Ball joint (Female -M5)	18	350000	2	Parts
7383	Floor switches	8	250000	2	Other
5165	Ball joint (Female -M8)	19	350000	2	Parts
5171	Custom PCB 	25	350000	2	Parts
5173	FR4 PCB(6"x4")	27	350000	2	Other
5175	Spiral Sleeve (10m,8mm)	29	350000	2	Other
5176	Cable channeling	30	350000	2	Other
5177	Nuts + Bolts + Washer (M8, M6, M5, M4, M3)	31	350000	2	Other
7499	2000 maH AA rechargeable batteries with charger	4	250000	2	Other
5178	Heat shrinks (Assortment)	32	350000	2	Other
5180	Solder Wire (1 Spool)	34	350000	2	Other
5186	Carbon Fiber Spars	2	350000	2	Other
5188	Bonding adhesive	4	350000	2	Chemicals
8207	Karone 10 pair 20	3	160000	2	Other
8210	Back Box	5	160000	2	Other
8211	Face plate 02 holes	6	160000	2	Other
5189	Fiber Glass	5	350000	2	Other
5190	Epoxy Resin	6	350000	2	Chemicals
142	Full scale platform machining	1	350000	2	Other
143	Small scale platform machining	2	350000	2	Other
148	Square Channel (1"x1"x360mm)	7	350000	2	Other
149	Ac Wire	8	350000	2	Other
151	Filment (PLA)	10	350000	2	Other
152	Connectors (GX-16)	11	350000	2	Other
154	PCB Boards (FR4, Double sided)	13	350000	2	Other
155	Spiral Sleeve (10m , 8mm)	14	350000	2	Other
6705	Lenovo v330 i5 4GB RAM	210	350000	7	Office Equipment
156	Raceway (wire Channel	15	350000	2	Other
157	Nuts + Bolts + Washer (M8, M6, M5, M4, M3, Box)	16	350000	2	Other
158	Rivets (M3, M4, Box)	17	350000	2	Other
159	Heat shrinks (assortment)	18	350000	2	Other
6766	Antenna/Table stand and folding chairs	271	350000	7	furniture
162	Soldering Iron Tips (Kada 852AD+)	20	350000	2	Other
163	Solder Wire (480grams)	21	350000	2	Parts
4610	xxxxx2	25	350000	7	Tools / Test Equipment
4711	PRP Spinner for DLE120CC (2 Blade)	7	350000	2	Other
180	Limit switch	37	350000	2	Parts
181	Light gate/speed sensor	38	350000	2	Parts
183	Wiring (control wires 1.5mm 3 core & 1.5mm 2 core)	40	350000	2	Parts
184	Mesh 30mm/40mm	41	350000	2	Parts
1848	Jumper Wire	36	350000	2	Other
187	Connectors (3 pin solenoid connector / MIL connectors)	42	350000	2	Parts
188	I type Lugs	43	350000	2	Parts
189	Sheet metal plates	44	350000	2	Parts
194	Safety rubber dampers	47	350000	2	Parts
5210	25 sq.mm wire (ft)	14	350000	2	Other
5211	10 sq.mm wire (ft)	15	350000	2	Other
5212	LUG round 10-8	16	350000	2	Other
5213	LUG round 25-8	17	350000	2	Other
5214	Heat shrink sleeve misc size red + black (ft)	18	350000	2	Other
5215	Gas kit (ft)	19	350000	2	Other
5216	Screws for casing plate mounting and connector mounting	20	350000	2	Other
5217	Wire mesh (25, 35 mm) (roll)	21	350000	2	Other
5218	VFD wire cover	22	350000	2	Other
5219	Plastic file	23	350000	2	Stationary
5220	Box file	24	350000	2	Stationary
5221	Clear bag A4 size	25	350000	2	Stationary
5222	U & I type lugs (packet)	26	350000	2	Stationary
5223	Tellies (multiple)	27	350000	2	Other
5224	WD40	28	350000	2	POLs
5225	Lacquer	29	350000	2	Chemicals
5226	Thinner	30	350000	2	Chemicals
5227	Push button 	31	350000	2	Other
5228	Indication LEDs	32	350000	2	Other
5229	Front panel manufacturing	33	350000	2	Other
5230	Relay base	34	350000	2	Other
5231	Relay	35	350000	2	Other
5232	Casing coating	36	350000	2	Other
5233	Hinges for front plate	37	350000	2	Other
5234	Glands	38	350000	2	Parts
294	Filter part no: 51105-0054	5	350000	2	Other
295	HF/UHF/XHF Fuel Vent - Size : 5/16" ID XHF source: Ultimate jets USA	6	350000	2	Other
296	Kevral Ultimate Fuel Buffer UBFK series size: UFBK1000/10 mm OD Pisco / Horizontal Source: Ultimate jets USA	7	350000	2	Other
297	HF/UHF/XHF Tygon Fuel Lines - Size 5/16" ID per foot source : Ultimate Jets USA	8	350000	2	Other
299	Check Valve, TD 150P-SKU: TD150P 1.5" Plastic trap door style check valve Source: Fuel Safe Systems USA	9	350000	2	Other
301	Freight/Handling	10	350000	2	Other
564	2n222 BJT Transistor	19	350000	2	Parts
565	2 pin JST connector	20	350000	2	Parts
566	6 pin JST connector	21	350000	2	Parts
567	male & female headers	22	350000	2	Other
252	Raw material for penetrators	8	300000	2	Other
5199	Buck converter (variable)	3	350000	2	Parts
5200	ON start delay circuit	4	350000	2	Other
5201	10 way green connector	5	350000	2	Other
5202	24V power supply for VFD panel	6	350000	2	Parts
5203	Wire roll (0.75 sq. mm) (90m)	7	350000	2	Other
5204	Wire red (1.5 sq.mm) (10m)	8	350000	2	Other
5205	Wire BLK (1.5 sq.mm) (10mm)	9	350000	2	Other
5206	Casing of coating and machinig	10	350000	2	Other
5207	Hour meter 	11	350000	2	Parts
5208	Hinges for control and display	12	350000	2	Other
306	Insert	5	350000	2	Other
312	Tapping Chuck set M3 to M12	11	350000	2	Other
313	Tapping Chuck set M8 to M20	12	350000	2	Other
315	Carbide Ball Nose 1mm	14	350000	2	Other
319	Carbide Ball Nose 5mm	15	350000	2	Other
320	Center Drill	16	350000	2	Other
323	Dial Indicator	3	350000	2	Other
324	Bench grinder wheels	4	350000	2	Other
325	Cobalt bits for drill press	5	350000	2	Other
326	Drill Chuck	6	350000	2	Other
327	Pressure Gauge	7	350000	2	Other
328	8mm wire for testing (6x19 SWC)	8	350000	2	Other
333	RF cable RP-SMA plug straight to RP-SMA jack bulkhead straight RG 316 15 cm	11	350000	2	Other
335	RF cable RP-SMA jack bulkhead to MMCX plug RG 316	13	350000	2	Other
339	Bench Antenna and cables	17	350000	2	Other
340	RF cable RP-SMA Female to N-Type Male	18	350000	2	Other
342	One piece trolley (Aluminum)	20	350000	2	Other
344	Barbed Brass Fitting OD 8mm	22	350000	2	Other
345	Retention clip sets	23	350000	2	Other
2110	HDD Seagate 1TB 3.5"	7	350000	7	Parts
346	Copper Flared  pipe fittings tube OD 12mm	24	350000	2	Other
347	Copper pipe OD 12mm 1D 10 mm 5ft	25	350000	2	Other
348	Air trap refuel ball valve	26	350000	2	Other
349	Sloshing foam	27	350000	2	Other
350	Fuel Clunk	28	350000	2	Other
351	Allen Bolts (different sizes)	29	350000	2	Other
353	Metal sheet	30	350000	2	Other
569	soldering iron tips	23	350000	2	Other
570	zip ties	24	350000	2	Other
572	soldering flux	26	350000	2	Other
573	Tweezer	27	350000	2	Other
574	Solder wire	28	350000	2	Other
576	spray for mounting 3D printed parts	30	350000	2	Chemicals
577	Wegstr bits	31	350000	2	Parts
578	WD 40	32	350000	2	Parts
579	Glue sticks	33	350000	2	Other
580	Printer toner & plastic sheet cover	34	350000	2	Other
588	USB hub	42	350000	2	Parts
378	Extended box	1	200000	2	Parts
379	Extended Card	2	200000	2	Parts
381	Extended Cable	3	200000	2	Parts
382	Thinner supper ltrs	4	200000	2	Chemicals
383	Heating Sleeves (Nos 2.3.10,11,14,16,18,20,24,30)	5	200000	2	Parts
384	IDE to SATA convertor	6	200000	2	Parts
385	SSD for storage	7	200000	2	Machinery / Equipment
388	SS Net bolts DOZ	10	200000	2	Parts
394	Pins and covers	16	200000	2	Parts
415	20A Single Pole	4	350000	2	Other
416	Lugs 35mm cable	5	350000	2	Other
417	Indication Light	6	350000	2	Other
418	Control Wiring Cable 3m	7	350000	2	Other
419	Power Wiring	8	350000	2	Other
426	AGS Exide Battery 12V 40Ah	2	350000	2	Other
428	Lugs Pair or Single with Cover 8mm	4	350000	2	Other
430	Capacitor 22000uF 25V	6	350000	2	Other
431	Sample Connector	7	350000	2	Other
471	RF passive SMD components for 100 W PA (resistors, capacitors,inductors)	9	350000	2	Other
545	RTC DS 3231+ cell	1	350000	2	Parts
546	16GB SD card	2	350000	2	Other
547	SD card reader	3	350000	2	Other
8205	200 pair iron boxes	1	160000	2	Other
8206	CAT-5 4 pair cable	2	160000	2	Other
548	DHT22 temperature & humidity sensors	4	350000	2	Parts
549	Linear Hall Effect sensor (DRY5057)	5	350000	2	Parts
550	18 awg 5M Red, 5m black DC wire	6	350000	2	Other
551	Li-ion 18650 2000mAh Battery	7	350000	2	Other
437	Line Filter (P70)	4	350000	2	Other
589	Paper lining for lab cabinets	43	350000	2	Other
592	Paper Rim	46	350000	2	Other
593	Glue stick	47	350000	2	Other
594	Green paper	48	350000	2	Other
595	Colored flags	49	350000	2	Other
597	Registers 300pg	51	350000	2	Other
598	A4 small envelope	52	350000	2	Other
599	Gel pen Blue/Black	53	350000	2	Other
552	Battery holder	8	350000	2	Other
553	10m nickle wire	9	350000	2	Other
554	BMS 1s 3A	10	350000	2	Other
555	PLA filament	11	350000	2	Parts
556	FR4 solder mask single side PCB board fabrication	12	350000	2	Other
557	At Mega 328P-Dip 28 Micro controller	13	350000	2	Parts
559	Dip 28 IC holder	14	350000	2	Other
560	2 pin push button	15	350000	2	Parts
561	0.1 uF capacitor	16	350000	2	Parts
562	10k resistor	17	350000	2	Parts
563	1k resistor	18	350000	2	Parts
644	LED Display	4	200000	7	Other
600	Dollar dry board marker (blue)	54	350000	2	Other
601	Blue pen	55	350000	2	Other
602	White correction pen	56	350000	2	Other
603	Writing pad	57	350000	2	Other
604	Tissue box	58	350000	2	Other
605	Air Freshner	59	350000	2	Other
606	Aroma Air Freshner	60	350000	2	Other
607	Power plus spray 600ml	61	350000	2	Chemicals
608	Power plus refill oil	62	350000	2	Chemicals
609	Pencils	63	350000	2	Other
610	Tape transparent 1 inch	64	350000	2	Other
611	Tape transparent 2 inch	65	350000	2	Other
613	Face Mask	67	350000	2	Other
625	standee	3	350000	2	Other
633	Manufacturing of Die (as per design) for Buoyancy Chamber of Scale down ROV for Testing Purpose.	4	300000	2	Other
634	Casting Material for Buoyancy Chamber .	5	300000	2	Other
632	Slip Resistant Rubber Mat for Test Tank Area	3	300000	2	Other
626	Command shelter container modification (server rack front sheet, usb cable, 12 core wire 65 ft)	4	350000	2	Parts
636	Gazebo covers, shelter clip, matching tape, gland holesaw, 3 core wire, extension boards, LCD stand	7	350000	2	Other
637	Diesel for Command shelter generator and petrol	8	350000	2	Other
638	04 power cord, 02 HDMI, 1 Ethernet Splitter	9	350000	2	Other
639	White spray paint	10	350000	2	Other
640	AV TO HDMI CONVERTOR	1	200000	2	Other
642	AV TO VGA CONVERTOR	2	200000	2	Other
643	Circuits LEDs	3	200000	2	Other
645	AV Leads	5	200000	2	Other
646	DP to HDMI cable	6	200000	2	Other
647	HDMI TO VGA CONVERTOR	7	200000	2	Other
648	AV CONNECTOS	8	200000	2	Other
649	Rable roll	9	200000	2	Other
661	Flexible pipe	1	350000	2	Parts
663	Nylon braking straps	3	350000	2	Other
664	Dead weight hardpoints	4	350000	2	Other
665	Diesel engine oil	5	350000	2	Chemicals
756	Fiber 1 Server-PTZ CX01	1	350000	2	Other
758	Fiber 2 PTZ-host panel CX02	2	350000	2	Other
759	DD-50SZ 50 Pin DSUB Connector - Female Straight	1	200000	2	Parts
769	50 Pin DSUB Connector - Male L Shaped Right Angled	2	200000	2	Parts
770	Semi Rigid RG402 Connectors	3	200000	2	Parts
772	Semi Rigid Coaxial Cable RG402 1 meter cable	4	200000	2	Parts
773	Semi Rigid Coaxial Cable RG402 1.5 meter cable	5	200000	2	Parts
776	XC9572-PQ100AMM0501-101 Xilinx CPLD	6	200000	2	Parts
778	XC9536-VQ44AMM0233-151 Xilinx CPLD	7	200000	2	Parts
781	SNJ54LS245J Buffer IC DIP	8	200000	2	Parts
782	54LS245 Buffer IC DIP	9	200000	2	Parts
784	CD54HC245F3A Buffer IC DIP	10	200000	2	Parts
785	SN54HC245J Buffer IC DIP	11	200000	2	Parts
788	GAL16V8D-15LD PLD IC Dip	12	200000	2	Parts
790	GAL16V8D-150P PLD IC Dip	13	200000	2	Parts
792	AM27C040-150DI   4 Megabit (512K x 8-bit) CMOS PROM (DIP IC)	14	200000	2	Parts
795	M27C4001-10F1   4 Megabit (512Kb x 8-bit) UV EPROM and OTP Eprom (DIP IC)	15	200000	2	Parts
796	20PIN IC Base normal IC Sockets base	16	200000	2	Parts
798	20PIN IC Base round IC Sockets base	17	200000	2	Parts
799	32PIN IC Base round IC Sockets base	18	200000	2	Parts
800	A472J-9 Register Array	19	200000	2	Parts
801	MFBM1V2012-000-R Ferrite Beads Multi H COR (Inductor) 0.0 OHM, 6A SDT	20	200000	2	Parts
803	X-4.7 uf 20v (T491D475k050AT 4.7uf 50v) Electrolytic Tantalum Capacitor	21	200000	2	Parts
806	X-22uf 20v (TAJD226k025R 227uf 25v) Electrolytic Tantalum Capacitor	22	200000	2	Parts
809	0.01uf 20v size 1206L, 0805S Surface Mount Ceramic Capacitor	23	200000	2	Parts
816	Check valve TD150-OR	2	350000	2	Parts
822	Circuit Fittings	1	350000	2	Other
823	Brushes for layup	2	350000	2	Other
824	Brushes & ss converter	3	350000	2	Other
825	MS rods	4	350000	2	Parts
826	Fastners	5	350000	2	Parts
827	Mounting bolts	6	350000	2	Other
828	Reducer bush and fittings	7	350000	2	Other
829	Rubber for wheels	8	350000	2	Other
830	Aluminum material for spare wheels	9	350000	2	Other
831	Aluminum axels	10	350000	2	Other
832	Belt for compressor engine	11	350000	2	Parts
834	Pipe shaft	13	350000	2	Parts
835	Lubrication oil	14	350000	2	Other
837	2 inch pipe	15	350000	2	Other
838	2 inch fittings	16	350000	2	Other
839	rubber mounts for engine	17	350000	2	Other
840	Locking circuit with mounting	18	350000	2	Parts
841	Temperature sensor k type thermocouple	19	350000	2	Parts
842	Mill- spec connector 20 pins	20	350000	2	Other
843	20 core wire	21	350000	2	Other
844	4 mm ID pipe	22	350000	2	Other
845	RF connectors PCB mount MMCX connectors	23	350000	2	Other
847	Servo MG996R Metal Gear	24	350000	2	Parts
848	Frame F 450 Quad Frame	25	350000	2	Other
849	Motors EMAX XT2216-910 KV	26	350000	2	Parts
850	Electronic speed controller skywalker 40 amp	27	350000	2	Parts
852	Propellers 1045 CF propeller pair	28	350000	2	Parts
853	Propellers for skycam	29	350000	2	Parts
854	Receiver X8R FR sky 2.4GHz	30	350000	2	Parts
855	Receiver L9R Frsky 2.4 GHz	31	350000	2	Parts
856	RF D900x modems	32	350000	2	Parts
857	Pixhawk cube orange flight controller + Here 3 GNSS GPS modules	33	350000	2	Parts
858	Anti vibration mount for pixhawk	34	350000	2	Other
859	Landing gear for quadcopter	35	350000	2	Parts
860	Battery tester BX100	36	350000	2	Other
861	Nitrile Gloves	37	350000	2	Other
862	Sand paper 80	38	350000	2	Other
863	Sand paper 600	39	350000	2	Other
864	Sand paper 1000	40	350000	2	Other
865	Thinner	41	350000	2	Chemicals
866	Wax	42	350000	2	Other
867	Disposible Glass	43	350000	2	Other
868	Spiral Pipe	44	350000	2	Other
870	Paint brush 1.5"	45	350000	2	Other
871	Paint brush 2"	46	350000	2	Other
872	Paint brush 4"	47	350000	2	Other
873	Paint grey primer	48	350000	2	Chemicals
874	Auto paint grey	49	350000	2	Chemicals
875	Cotton Rag	50	350000	2	Other
876	Hand Gloves	51	350000	2	Other
877	KN-95 masks	52	350000	2	Other
881	Base plate	54	350000	2	Parts
882	Engine test bench electronics (DLE)	55	350000	2	Parts
883	Mount & Clevis	56	350000	2	Parts
927	Control Box 470x700mm	1	350000	2	Parts
928	Control Box 470x940mm	2	350000	2	Parts
929	24V Power Supply	3	350000	2	Other
930	Casing of coating and machining	4	350000	2	Parts
931	HOUR METER	5	350000	2	Parts
933	Mounting hings	6	350000	2	Other
934	LUG Round 10-8	7	350000	2	Other
935	LUG Round 25-8	8	350000	2	Other
936	Mil Connector	9	350000	2	Other
937	GX 16 (2 & 3 pins) connector	10	350000	2	Other
938	Glands	11	350000	2	Other
939	Allen Key screws	12	350000	2	Other
940	Tellies (multiple)	13	350000	2	Other
941	GX 16 (2 & 5 pins) connector	14	350000	2	Other
942	Selector 3 & 2 way	15	350000	2	Other
943	T Spanner	16	350000	2	Other
944	Push Button	17	350000	2	Other
945	LED indication	18	350000	2	Other
1018	System Cleaning Brush	10	200000	2	Parts
946	Selector Switch (3 pole, 1-0-2)	19	350000	2	Other
948	Selector Switch (2 pole, 1-0-2)	20	350000	2	Other
949	ON/OFF Selector	21	350000	2	Other
950	Heat Shrink Sleeve Misc Size RED + Black (ft)	22	350000	2	Other
951	Gas kit (ft)	23	350000	2	Other
952	Screws for Casing plate mounting and Connector Mounting	24	350000	2	Other
953	Wire Mesh (roll)	25	350000	2	Other
954	U and I type Lugs (packet)	26	350000	2	Other
955	Wire Roll (0.75 sq.mm)	27	350000	2	Other
956	Cable Tie (packet)	28	350000	2	Other
957	Soldering wire	29	350000	2	Other
958	SS Nuts and Bolts (packet)	30	350000	2	Other
959	Rivet Nuts (packet)	31	350000	2	Other
960	Refreshments for FATs team	32	350000	2	Other
965	First Aid Kit	37	350000	2	Other
973	Measuring Tape	45	350000	2	Other
1110	Vector network analyzer transmission/reflection. N9917A-210	3	350000	2	Parts
1111	Vector network analyzer full 2-port S-parameters. N9917A-211	4	350000	2	Parts
2504	Fabrication of LNA PCB	9	350000	3	Fabrication
966	Teflon Tape Set	38	350000	2	Other
967	Loctite/thread sealant	39	350000	2	Chemicals
968	Grease can	40	350000	2	Chemicals
969	Oil bottle	41	350000	2	Chemicals
970	Cutting Disc	42	350000	2	Parts
971	Drill Bits spare set	43	350000	2	Other
972	Extension wire (4-6 socket, 5m cable)	44	350000	2	Other
985	Lux punch power	57	350000	2	Other
1002	Cable Set 26m 8mm	74	350000	2	Other
1005	Wi-Fi Dongle for PC	77	350000	2	Other
1007	Printer toner refill & Packing material for VFDs (plastic wrap and tapes)	79	350000	2	Other
1009	PCB Coating Spray CPL	1	200000	2	Parts
1010	PCB Coating Spray 70	2	200000	2	Parts
1011	Panasonic MSDA 023AIA 200W Servo Driver Tested	3	200000	2	Parts
1015	DB50 Female wire connector	7	200000	2	Parts
1016	DB37 Female connector	8	200000	2	Parts
1019	Battery sets (Pac16x1)	11	200000	2	Parts
1023	Working Gloves	15	200000	2	Parts
1025	Oiling Botle	17	200000	2	Parts
1038	3m I/O	4	200000	2	Parts
1039	3m Patch cord	5	200000	2	Parts
1040	3m RJ45 Metal Connector CAT6	6	200000	2	Parts
1042	3m Cable Roll CAT6	8	200000	2	Parts
1053	Epoxy resins for UCAV material sample manufacturing (for different airframe components)	2	350000	2	Chemicals
1055	AC, DC wire Procuremnet & Profi base Modification for Aero Marine Mounting for avionics bench testing	3	350000	2	Other
1056	Portable Metal Jerry Fuel Cans 20L	4	350000	2	Other
1057	Avionic Test bench Landing Pad (6mm rubber, 8 x 10ft)	5	350000	2	Other
1058	Spools of filament for printing s-duct Avionics Box	6	350000	2	Other
1060	HRC36945-Hitec D945TW D-Series Ultra Torque Titanium Gear Digital Servo for Avionics test bench	7	350000	2	Parts
1061	RC Servo 25kg full Metal Gear Digital Servo waterproof for Avionics test bench	8	350000	2	Parts
1062	QC450 frame for avionics test bench	9	350000	2	Parts
1063	Emax XT2216 910 kv for Multicopter thrust 1kg Plus (avionics test bench)	10	350000	2	Other
1064	Carbon Filter propeller 10 x 4.5 for Avionics test bench	11	350000	2	Parts
1065	amp pixhawk mount for avionics test bench	12	350000	2	Parts
1066	Lipo Buzzer for Avionics test bench	13	350000	2	Other
1067	Silicon wire 22AWG Red for avionics test bench	14	350000	2	Other
1068	Silicon wire 22AWG Black for avionics test bench	15	350000	2	Other
1069	Silicon wire 22AWG Red for avionics test bench	16	350000	2	Other
1071	Silicon wire 22AWG Black for avionics test bench	17	350000	2	Other
1072	Pixhawk cube orange with gps for avionics test bench	18	350000	2	Parts
1073	RFD 900 for avionics test bench	19	350000	2	Parts
1074	Carbon fiber Landing Gear for Avionics test bench	20	350000	2	Parts
1075	Frsky X8r for Avionics test bench	21	350000	2	Parts
1076	Frsky L9R for Avionics test bench	22	350000	2	Parts
1077	Tmotor AT 30A 3S ESC for Avionics test bench	23	350000	2	Parts
1078	Gelcoat for UCAV Sample material manufacturing (for different airframe components)	24	350000	2	Chemicals
1079	Avionics Box  Aluminum Plate	25	350000	2	Other
1080	GPS Antenna Plate for avionics test bench	26	350000	2	Other
1081	Handheld Engine Starter 50cc-200cc Engine for avionics test bench	27	350000	2	Parts
1082	Sheet Metal bent for test bench	28	350000	2	Other
1083	Nylon rope for avionics test bench Box tightening	29	350000	2	Other
1084	Petrol for Avionics test bench engine testing	30	350000	2	Chemicals
1085	Avionics test Bench Fuel Line	31	350000	2	Other
1086	Avionics test Bench Nozzel & Pipe	32	350000	2	Other
1087	Avionics test Bench Fuel	33	350000	2	Chemicals
1088	Avionics test Bench rubber damper	34	350000	2	Other
1089	Avionics test Bench Fuel for complete Breakin	35	350000	2	Chemicals
1090	Sealant Patterns for Avionics test bench	36	350000	2	Chemicals
1091	2" Nozzle for avionics test bench	37	350000	2	Other
1093	Compressor welding for ROTAX test bench	39	350000	2	Other
1095	Compressor Socket for ROTAX test bench	40	350000	2	Other
1096	Thimbal 12 mm for ROTAX test bench	41	350000	2	Other
1097	MS Pipes for Break Tightening (ROTAX Test Bench)	42	350000	2	Other
1098	Spare hosepipe 2" for ROTAX test bench	43	350000	2	Other
1100	Diesel for compressor (ROTAX test bench)	45	350000	2	Chemicals
1101	Starter Battery for ROTAX test bench	46	350000	2	Other
1112	Rugged phase-stable cable, type-N(m) to type-N(m), 18 GHz, 3.28 ft or 1 m. N9910X-701	5	350000	2	Parts
1116	UPS Battery 135 Ah	1	350000	2	Other
1118	RF shielding cloths per meter	3	350000	2	Other
1119	Target stand	4	350000	2	Other
1149	Supply with installation of 1" GI 12 guage Pipe	2	350000	2	Other
1150	Supply with installation of 1" GI union	3	350000	2	Other
1153	Supply with installation of 1" GI barrel nipple	4	350000	2	Other
1154	Supply with installation of 1" GI Elbow	5	350000	2	Other
1155	Supply with installation of 1" GI Tee	6	350000	2	Other
1156	Supply with installation of 1" valve	7	350000	2	Other
1157	Supply with installation of 1" GI socket	8	350000	2	Other
1158	Supply with installation of 1/2" valve	9	350000	2	Other
1176	avionic hatch	1	350000	2	Parts
1177	left recovery hatch pattern	2	350000	2	Parts
3005	Polycate	14	350000	2	Other
1178	right recovery hatch pattern	3	350000	2	Parts
1179	left front fuselage pattern	4	350000	2	Parts
1180	right front fuselage pattern	5	350000	2	Parts
1181	left rear fuselage pattern	6	350000	2	Parts
1182	right rear fuselage pattern	7	350000	2	Parts
1183	left wing leading edge	8	350000	2	Parts
1185	left horizontal tail leading edge	9	350000	2	Parts
1186	vertical tail leading edge	10	350000	2	Parts
1187	right horizontal tail leading edge	11	350000	2	Parts
1188	right wing leading edge	12	350000	2	Parts
1189	fuselage midleft	13	350000	2	Parts
1190	fuselage midright	14	350000	2	Parts
1191	Engine cowling left	15	350000	2	Parts
1192	Engine cowling right	16	350000	2	Parts
1195	Spar 25x20	18	350000	2	Parts
1196	Spar 30x25	19	350000	2	Parts
1197	Spar 36x30	20	350000	2	Parts
1198	Aluminum T connector	21	350000	2	Parts
1199	Parachute Release Mount	22	350000	2	Other
1200	Ball link 8mm	23	350000	2	Other
1201	Servo horn 4.5mm	24	350000	2	Other
1202	AC Wire Flexible  cable, 16 mm.sq 4 core	25	350000	2	Other
1203	Drywall Screws 1 x 8"	26	350000	2	Other
1204	Wire Lugs 16mm.sq	27	350000	2	Other
1205	Junction Box 10" x 12"	28	350000	2	Other
1206	Circuit Breaker 50 A	29	350000	2	Other
1207	Shroud	30	350000	2	Other
1208	Wire Duct (Dura Duct)	31	350000	2	Other
1209	Earth Bar	32	350000	2	Other
1210	Screw assortment Nut bolt with washer	33	350000	2	Other
1211	PVC saddle	34	350000	2	Other
1212	PVC pipe	35	350000	2	Other
1213	PVC Elbow	36	350000	2	Other
1214	PVC Socket	37	350000	2	Other
1215	Steel Nail	38	350000	2	Other
1216	DLE 120 woodruff keys	39	350000	2	Other
1217	Throttle lever assembly for rotax test bench	40	350000	2	Other
1218	Non Soluable cutting oil Grade Micron c (sharp oil)	41	350000	2	Chemicals
1220	Oil for spindle cooler ISO VG 10 (holspin 10)	43	350000	2	Chemicals
1221	Hydraulic oil ISO VG 32 (Horizon 32)	44	350000	2	Chemicals
1223	Carbon Fiber 200 gsm (100 mtr)	45	350000	2	Other
1231	JXT Conn 4 pin	8	350000	2	Other
1232	Connector Panel mount 3 pin AC Conn	9	350000	2	Other
1233	Push Button 12mm dia	10	350000	2	Other
1234	Switch SPST	11	350000	2	Other
1235	JXT Conn 5 pin	12	350000	2	Other
1236	Connector 3 pin AC Male + Converter	13	350000	2	Other
1237	Cable Spiral Black 6mm	14	350000	2	Other
1238	Serve Motors Hitee D950 TW	15	350000	2	Parts
1711	Stairs	9	350000	7	Furniture
1113	Calibration kit, 4-in-1, open, short, load and through, DC to 18 GHz, Type-N(f), 50 ohm. 85519A	6	350000	2	POLs
1239	Serve Horns Protec 25T Metal Servo Arm	16	350000	2	Parts
1240	3D Filament PLA	17	350000	2	Other
1241	Arduinos and GPS module	18	350000	2	Parts
1268	Amass MT60 Connectors (wiring harness for AV)	44	350000	2	Other
1269	Amass MT60 connectors (wiring harness for AV)	45	350000	2	Other
1270	Hobbywing 10A UBEC ( Power management for AV system)	46	350000	2	Other
1271	M3 Ball links Heavy Duty (AV Control Surfaces)	47	350000	2	Other
1272	Silicon wire 22 awg yellow (wiring harness for AV)	48	350000	2	Other
1273	Silicon wire 24 awg yellow (wiring harness for AV)	49	350000	2	Other
1274	Diesel generator fuel & petrol for transport (144 ltr)	50	350000	2	Chemicals
1277	Auto Paint	53	350000	2	Chemicals
1279	Gloves	55	350000	2	Other
1281	Weld rod No 10	57	350000	2	Other
1282	Weld rod No 12	58	350000	2	Other
1284	Wiring	60	350000	2	Other
1285	Electronics Housing 15 U Server casing	61	350000	2	Other
1287	Custom PCBs for controls (in house manufacturing)	63	350000	2	Other
1288	Microcontroller ATMega 328p	64	350000	2	Other
1289	Din Connectors, AC power connectors	65	350000	2	Other
1290	AC power wires, DC Multicore wires	66	350000	2	Other
1291	Spiral Sleeves	67	350000	2	Other
1292	Cable channeling (cable raceways)	68	350000	2	Other
1293	DC power supply 24 volt 20 A	69	350000	2	Other
1297	SS slab 200 mm * 100 mm * 20 mm	73	350000	2	Other
1298	MS slab 200 mm * 100 mm * 20 mm	74	350000	2	Other
1299	Torque tube	75	350000	2	Parts
1300	Tube control horn	76	350000	2	Parts
1301	Standees for PN Expo	77	350000	2	Other
1308	KADA 850+ soldring bits	1	200000	2	Other
1314	Seal rubbers 12 SQ foot	7	200000	2	Parts
1315	Nut bolts SS DOZ	8	200000	2	Parts
1316	Pce connectors	9	200000	2	Parts
1317	Cable ties pkt	10	200000	2	Parts
1320	Cacher nuts 19" rack	13	200000	2	Parts
1322	Paper Tape	15	200000	2	Parts
1323	RJ 45 connector Metal	16	200000	2	Parts
1324	Electric cable for fans per feet	17	200000	2	Parts
1325	DD-50SZ  50 Pin DSUB Connector - Female - Straight	18	200000	2	Parts
1326	IDE to SATA convertor	19	200000	2	Parts
1360	Bouyancy bars for a scale down ROV for testing	5	300000	2	Parts
1359	PLA material for 3D-printer	4	300000	2	Other
1327	N type Male RG58 Clamp connectors	20	200000	2	Parts
1328	RJ 45 Female sockets	21	200000	2	Parts
1366	2 No. 18 U Casing, 2 Full Tray, 2 Cantilever Tray, 1 Cantilever Tray Slider	1	350000	2	Other
1367	Front sheet, single sheet metal laser cutting	2	350000	2	Other
1368	Powder coating front & back plate	3	350000	2	Other
1369	Back plate laser cutting	4	350000	2	Other
1371	NUC PC power push button (Lot of 10 pcs)	5	350000	2	Other
1372	USB Mount Front Panel( Lot of 5 pcs)	6	350000	2	Parts
1373	Ethernet Socket Front panel mount	7	350000	2	Other
1374	Circuit Breaker 220V-16A, Double	8	350000	2	Other
1375	Industrial Power Female Plug 3 Prong	9	350000	2	Other
2247	Printer Toner Refill INTELL	23	350000	3	Equipment Repairs & Maintenance
2248	AC Repair SPF	24	350000	3	Equipment Repairs & Maintenance
1376	Industrial Power Male plug 3 prong	10	350000	2	Other
1377	Fans AC, 4inch w/grill	11	350000	2	Other
1378	Power Cable 1.5 mm, 3 core 5 meter	12	350000	2	Other
1379	AC Terminal Block	13	350000	2	Other
1380	Multi-input HDMI Switcher	14	350000	2	Other
1381	HDMI Extension cable	15	350000	2	Other
1383	Right Angle PC Power Cable	17	350000	2	Other
1384	Ethernet Extension Cable	18	350000	2	Other
1385	USB A Male to Male Extension cable	19	350000	2	Other
1386	EDB 5 port Extension Board	20	350000	2	Other
1387	2 pin AC Power Cord	21	350000	2	Other
1390	Bolt and Screws for mounting	24	350000	2	Other
1391	Sheet Metal Clamps	25	350000	2	Other
1392	2-Core Signal Wire DC 5V	26	350000	2	Other
1393	Powe Cable 2.5mm, 3 core	27	350000	2	Other
1394	2-Pin-Circular-Power-Connector Military-Spec-5015-Straight-Plug-Ms3106A10SL-4s	28	350000	2	Other
1395	Tellies	29	350000	2	Other
1403	Driver Board (Model: EVD-20KL-24V-1)	1	350000	2	Parts
1405	CPU Board (Model: EVD-20KL-24V-1)	2	350000	2	Parts
1406	CPU Connector Board (Model: EVD-20KL-24V-1)	3	350000	2	Parts
1407	Capacitor Board (Model: EVD-20KL-24V-1)	4	350000	2	Parts
1408	Current Sensor Board (Model: EVD-20KL-24V-1)	5	350000	2	Parts
1409	Relay Control Board (Model: EVD-20KL-24V-1)	6	350000	2	Parts
1412	Driver Board (Model: EVD-30KL-24V-1)	7	350000	2	Parts
1413	CPU Board (Model: EVD-30KL-24V-1)	8	350000	2	Parts
1414	CPU Connector Board (Model: EVD-30KL-24V-1)	9	350000	2	Parts
1415	Current Sensor Board (Model: EVD-30KL-24V-1)	10	350000	2	Parts
1416	Relay Control Board	11	350000	2	Parts
1417	Driver Board (Model: EVD-45KL-24V-1)	12	350000	2	Parts
1418	CPU Board (Model: EVD-45KL-24V-1)	13	350000	2	Parts
1419	CPU Connector Board (Model: EVD-45KL-24V-1)	14	350000	2	Parts
1420	Current Sensor Board (Model: EVD-45KL-24V-1)	15	350000	2	Parts
1421	Relay Control Board	16	350000	2	Parts
1248	Cooler Master MasterBox Q500L	25	350000	2	Other
1447	Misc. items (wrench, pipe, scale, hook, measurement tape)	2	350000	2	Other
1448	Studs	3	350000	2	Other
1462	T8 Nut M8 LS, Lead: 2mm S: 2	16	350000	2	Parts
1464	Profiles 3030 L: 205, 120 + Rails L:320, 350, 370	18	350000	2	Parts
8552	Completer floor tiling work (18" x 18" porcelein tiles) and false ceiling	5	300000	7	Other
1465	Printing PLA/ABS spool	19	350000	2	Other
1467	0.4 mm MK3 Brass Nozzel	21	350000	2	Parts
1468	Linear Bearing LM8UU	22	350000	2	Parts
1469	Zip Tie 2.5 * 100 mm	23	350000	2	Parts
1470	Screws nuts and Washers	24	350000	2	Parts
1522	GR5 Titanium plate ASTM B381 Size 100*35*140mm	1	350000	2	Other
1523	GR5 Titanium plate ASTM B381 Size 100*30*140mm	2	350000	2	Other
1524	GR5 Titanium plate ASTM B265 Size 50*20*120mm	3	350000	2	Other
1525	GR5 Titanium plate ASTM B265 Size 80*20*120mm	4	350000	2	Other
1244	Kingston NV1 500G M.2 2280 NV Me PCLe SSD SNVS/500G	21	350000	2	Other
1265	Thermaltake Ring 12 LED RGB Radiator Fan Sync Edition (3-Fan pack) CL-F071-PL12SW-A	42	350000	2	Other
1264	Corsair Hydro H45 Liquid CPU Cooler	41	350000	2	Other
1283	Weld googles	59	350000	2	Other
1526	GR5 Titanium plate ASTM B265 Size  65*25*80mm	5	350000	2	Other
1527	GR5 Titanium plate ASTM B265 Size 25*30*80mm	6	350000	2	Other
1560	Cutting Oil	2	350000	2	Chemicals
1261	Cooler Master MasterBox Q500L	38	350000	2	Other
1573	3 Phase Wire (Meter)	11	350000	2	Other
1574	1.5 Sqmm 3 Core AC Wire, 0.8 sqmm 2 core single wire	12	350000	2	Other
1575	Cable N -type Male to SMA Male RP RG-223	13	350000	2	Other
1253	Kingston 32 GB Data Traveler Exodia Flash Drive DTX/32GB	30	350000	2	Other
2754	Tygon fuel lines	23	350000	7	Parts
2759	Batteries 3S 4000mAH	28	350000	7	Parts
2755	Marlin 1D Metal Parts	24	350000	2	Other
2757	Spray WD40	26	350000	2	POLs
2758	Repaint of MARLIN 2	27	350000	2	Other
1576	Cable N -type Male to SMA Male RP RG-400	14	350000	2	Other
1577	Cable N -type Male to Male RG-223	15	350000	2	Other
1578	Cable N -type Male to Male RG-400	16	350000	2	Other
1581	Plug Adapter	19	350000	2	Other
1584	4140 Steel Raw Material Rod 12 Samples	21	350000	2	Other
1589	Battery Wires , battery Terminals, Lugs and Connectors	24	350000	2	Other
1594	16 Sqmm 4 Core 3 Phase Wire ( in RFT)	28	350000	2	Other
1566	C Clamps 6"	6	350000	2	Other
7937	Electric power boards	14	350000	7	Other
7944	Vinyl Flooring size: 241 sq. ft.	4	350000	7	Building Modification
1256	RAM corsair Vengeance DDR4 16GB 3600 Bus RGB pro (8GBx2)	33	350000	2	Other
1258	Kingston A2000 NVMe PCLe SSD 500 GB	35	350000	2	Other
1595	1.4 Sq mm 2 Core Wire (in RFT)	29	350000	2	Other
1596	50 Amps Breaker	30	350000	2	Other
1597	Lugs for Breaker	31	350000	2	Other
1598	Cable Channel	32	350000	2	Other
1606	16 sqmm 4 Core 3 Phase Wire (in RFT)	39	350000	2	Other
1260	Gigabyte P750 GM 750 Watt 80 plus	37	350000	2	Other
1259	Seagate 1TB 3.5" HDD	36	350000	2	Other
1607	3 Pole 50 Amp Breaker	40	350000	2	Other
1608	2  pole 32 Amp Breaker	41	350000	2	Other
1609	10 mm , 16 mm Lugs, Sleeves , PVC tape ,Power cord converter , panel Box, PG gland, PVC pipe earth strip, Black wire Strip	42	350000	2	Other
1614	Cable Connectors 6 pin	45	350000	2	Other
1615	Spiral Sleeves 6mm Dia	46	350000	2	Other
1617	Wiring 4 Core 10m	47	350000	2	Other
1618	Wiring 5 Core Solid , 5m	48	350000	2	Other
1619	Power Distribution Board 5 Socket	49	350000	2	Other
1620	Microcontroller STM32 "Blue Pill" Board - STM32F103C8T6	50	350000	2	Parts
1621	ST Link V2 ST Programmer Module	51	350000	2	Other
1622	FTDI USB to TTL Serial Converter Module	52	350000	2	Other
1623	Cable Channelling (data Cable)	53	350000	2	Other
1625	MDF Spoil Board for Avid CNC	54	350000	2	Other
1252	Thermaltake Ring 12 LED RGB Radiator Fan Sync Edition (3-Fan pack) CL-F071-PL12SW-A	29	350000	2	Other
1257	Graphic card Gigabyte GeForce GTX 1650	34	350000	2	Other
1245	Seagate Barracuda 1 TB Hard Drive	22	350000	2	Other
1246	Corsair vengeance DDR4 16GB RAM 3600 Bus RGB pro (8GBx2)	23	350000	2	Other
1247	PSU Gigabyte P750 GM 750W	24	350000	2	Other
7661	Pad Lock	23	160000	7	Other
1437	Deformed Steel (G-60) Size 16mm x 12m	3	300000	2	Parts
1436	Deformed Steel (G-60) Size 12mm x 12m	2	300000	2	Parts
1435	Deformed Steel (G-60) Size 9.5mm x 12m	1	300000	2	Parts
1660	Electric APC UPS Batteries 16x2 No 01 pack	4	200000	2	Other
1663	Electric USB to serial convertors	7	200000	2	Other
1666	Electric Solder Wire	9	200000	2	Parts
1667	Electric D38999/26WG35SN-US Back shell of 8D521W35SN	10	200000	2	Parts
1668	Electric D38999/26WB98SN-US Back Shell of 8D511W98SN	11	200000	2	Parts
7780	Electric Kettle (Anex 1ltr)	5	350000	7	Furniture
7782	Single Door Mini Fridge (Dawlance)	7	350000	7	Furniture
7781	Microwave (Dawlance MD-4N)	6	350000	7	Furniture
2854	Quick-Release Vacuum Coupling Set	9	350000	7	Other
2862	Vacuum Oil	17	350000	7	Parts
1669	Electric Mini Centronics connector 20 pin	12	200000	2	Parts
1670	Electric Terminal Board Connector Dual Orange 10 pin	13	200000	2	Parts
1671	Terminal Board Connector Dual Orange 6 pin	14	200000	2	Parts
2864	Barb connector 1/2 inch	19	350000	7	Parts
2865	Power lead	20	350000	7	Parts
6886	Workstation 6' x 2' x 2' 1/2 with Oak lapping and polish	8	350000	7	Furniture
2863	6mm id PVC vacuum hose	18	350000	7	Parts
2858	Air filter	13	350000	7	Parts
8480	Floor Tiling	1	300000	7	Other
8482	Lights & Fans for False Ceiling	3	300000	7	Other
8481	False Ceiling	2	300000	7	Other
1604	Measuring Tape	37	350000	2	Other
1605	30 cm Steel Ruler	38	350000	2	Other
1672	Electric DB 44 Pin Male DB Connectors with metal covers	15	200000	2	Parts
1673	Electric Connectors DB9 Male/Female with covers	16	200000	2	Parts
1674	Electric IDE64 connector Male/Female with ribbon	17	200000	2	Parts
1675	Electric Molex connectors Male & Female 8 pin	18	200000	2	Parts
1683	Electric Fuse 1A Fuse SMD	26	200000	2	Parts
1702	Hitech Servos Spares	1	350000	2	Parts
1703	02 4140 rod	2	350000	2	Other
1704	U-Clamp and thimbal	3	350000	2	Other
1705	Rubber for carriage wheels	4	350000	2	Other
1706	Magic Depoxy for dummy AV	5	350000	2	Chemicals
1708	Tool box	6	350000	2	Other
1710	Safety Gloves	8	350000	2	Other
1712	Bubble wrap	10	350000	2	Other
1713	Safety Helmet	11	350000	2	Other
1714	SS Set Screw and Weldnut	12	350000	2	Other
1715	Lubrication oil for full scale compressor	13	350000	2	Chemicals
1716	Cable Ties	14	350000	2	Other
1719	Fuel utilized for launcher compressor and generator (in liter)	17	350000	2	Chemicals
1720	Launcher paint 03 liter	18	350000	2	Chemicals
8485	Washroom Tiling & Sanitary Fittings Work	6	300000	7	Other
8483	Window Blinds	4	300000	7	Other
1830	T slots mounting brackets	18	350000	2	Other
7654	S/Driver Set	19	160000	7	Tools / Test Equipment
7652	Fix Spaner Set	18	160000	7	Tools / Test Equipment
7650	Monkey Plier	16	160000	7	Tools / Test Equipment
1676	Secrews spl steel &13 DOZ	19	200000	2	Other
1677	Electric Power socket & Pluge 15A	20	200000	2	Other
1678	Electric DB50 male	21	200000	2	Other
1679	Electric DB50 Female	22	200000	2	Other
1680	Electric DB50 cover plastic	23	200000	2	Other
1681	Electric Cable 50 pair 4m	24	200000	2	Other
1682	Electric  Connector ether cat6	25	200000	2	Other
1684	Electric Data Cable Roll 12 Core	27	200000	2	Other
1685	Electric Cable 5 core Tiawan 50m	28	200000	2	Other
1686	Electric Heating sleave 26	29	200000	2	Other
1687	Electric Lugs, Thimbles 12 inno & earth cable 4m	30	200000	2	Other
1688	Electric  Patch cord 3m cables	31	200000	2	Other
1689	Electric  Power Supply Cables 3m	32	200000	2	Other
1690	Electronic Accessories	33	200000	2	Other
8495	Suspension Grill Mechanism for ROV Test Tank	3	300000	7	Other
1721	Launcher Carriage bearing	19	350000	2	Other
1722	Nitrile Gloves box	20	350000	2	Other
1723	Vacuum Guage	21	350000	2	Other
1724	Catch pot nozzle	22	350000	2	Other
1725	Catch pot	23	350000	2	Other
1727	Plastic cannisters	24	350000	2	Other
1729	Funnel	25	350000	2	Other
1730	Mixing glass	26	350000	2	Other
1732	Duster clothes (kg)	27	350000	2	Other
1733	Acrylic Sheet for Catch Pot	28	350000	2	Other
1734	Silicon pipe (ft)	29	350000	2	Other
1735	AV battery (4S 10000 mAh)	30	350000	2	Chemicals
1744	Aluminum sheet 4ft x 4ft 2mm	2	350000	2	Other
1745	Aluminum sheet 4ft x 4ft x 4mm	3	350000	2	Other
1746	Aluminum sheet 4ft x 4ft x 5mm	4	350000	2	Other
1747	Aluminum Billet 330 x 330 x 75mm	5	350000	2	Other
4718	EH 640 TM Series 30X optical/14x Zoom IR thermal camera with 3-axis Gimbal	14	350000	7	Machinery/Equipment
1749	Rivets (pack of 200)	7	350000	2	Other
1750	Aluminum End Mill 4mm	8	350000	2	Other
1751	C1, C9 0.1uF Capacitor	9	350000	2	Other
1752	C2, C10, C11 1000pF Capacitor	10	350000	2	Other
1753	C7 0.7pF Capacitor	11	350000	2	Other
1754	C8 1000pF Capacitor	12	350000	2	Other
1755	R1 100 ohm resistor	13	350000	2	Other
1756	R2 499 ohm resistor	14	350000	2	Other
1757	R4 0 ohm resistor	15	350000	2	Other
1758	R5 240 mohm resistor	16	350000	2	Other
1759	JST 4 pin female with wire	17	350000	2	Other
1760	Arduino Uno	18	350000	2	Other
1761	i2c module (display screen)	19	350000	2	Other
1762	ESD tweezer	20	350000	2	Other
1763	ESD Gloves	21	350000	2	Other
1764	ESD wrist band	22	350000	2	Other
1765	ESD bag	23	350000	2	Other
7046	Casing manufacturing	17	350000	2	Parts
1771	Wireless Module (Air) 1.4G ETH in ETH out 1W Range : 30Km	1	350000	2	Other
1774	Locks for drawer	3	350000	2	Other
1775	Plastic Storage Box (small)	4	350000	2	Other
1776	Plastic Storage Box (medium)	5	350000	2	Other
1777	Plastic Storage Box (large)	6	350000	2	Other
1779	Waterbed (4 x 4 ft)	8	350000	2	Other
1780	Tips for Plasma Cutter	9	350000	2	Other
1781	Wiwu Alpha HDMI-USB Type C to HDMI Convertor	10	350000	2	Other
1802	Inconel 718 Alloy Steel Sheet Size: 2x2000x2000mm Origin: China	3	350000	2	Other
1803	Inconel 718 Alloy Steel rods Size: 2mmx300mm Origin: China	4	350000	2	Other
1817	Ethernet Cable	5	350000	2	Other
1819	Power Supply 550w	7	350000	2	Other
1820	RJ 45 Panel mount Connectors	8	350000	2	Other
1821	4 Pin mil Panel mount  Connectors	9	350000	2	Other
1822	USB Connectors (Male & Female pair)	10	350000	2	Other
1823	6 Pin Cable Connectors Panel mount	11	350000	2	Other
1825	1.5mm 4 core wire (Stranded) in meters	13	350000	2	Other
1826	0.5mm 5 core wire (Solid) in meters	14	350000	2	Other
1827	40x20mm length 1500mm Extrusion	15	350000	2	Other
1828	40x20mm length 1000mm Extrusion	16	350000	2	Other
1829	90 degree corner mounting brackets	17	350000	2	Other
1831	M5 Bolts length 35mm	19	350000	2	Other
1832	Door hinge in SS grade 37mm	20	350000	2	Other
1833	Door Knob M5, backelite hand tighten knobs	21	350000	2	Other
1834	3mm Bullet Connectors	22	350000	2	Other
1835	UBEC 5V/3A	23	350000	2	Other
7633	Plier 8''	1	160000	7	Tools / Test Equipment
7637	Star Set Japan	4	160000	7	Tools / Test Equipment
7640	Screw Driver 2 Way	7	160000	7	Tools / Test Equipment
7638	LN Key Set	5	160000	7	Tools / Test Equipment
7641	Screw Driver L-	8	160000	7	Tools / Test Equipment
7655	Tool Bag	20	160000	7	Tools / Test Equipment
1836	MP6050	24	350000	2	Other
1837	Neo 6m	25	350000	2	Other
1838	Energizer AA	26	350000	2	Other
1839	Nickle Cadmium battery (NiCD) Pack	27	350000	2	Other
1840	Vero board	28	350000	2	Other
1841	Reed Switch	29	350000	2	Other
1842	Reed Switch Module	30	350000	2	Other
1843	Neodymium Magnets	31	350000	2	Other
1844	Sim 808 module & PTA registration	32	350000	2	Other
1845	12v Adaptor	33	350000	2	Other
1846	Buck Converter	34	350000	2	Other
1847	STM32 "Blue Pill"	35	350000	2	Other
1849	Stationery, printer toner, electric board	37	350000	2	Other
1850	Damper / Spring test bench	38	350000	2	Other
1851	CAT 6 LAN Cable	39	350000	2	Other
1852	T nuts	40	350000	2	Other
1853	Power Distribution Board	41	350000	2	Other
1854	Zip Tie Packet	42	350000	2	Other
1855	M6 Nuts and Bolts	43	350000	2	Other
1856	3 Pin Plug Socket	44	350000	2	Other
1890	Aluminum Pipe C Clamps 85 x 25 x 55	1	350000	2	Other
1891	Aluminum Antenna Tube 12mm x 240mm x 1000	2	350000	2	Other
1892	Aluminum Base Rod Small Dia 37mm x 1000	3	350000	2	Other
1894	Aluminum Base Rod Large Dia 20mm x 450mm	4	350000	2	Other
6821	JR16WCC-8(71) Standard Circular Connector STRAIN RLF JR16 08MM	28	200000	7	Parts
1895	Aluminum Base Rod Sleeve Adapter 120mm x 40mm x 30mm	5	350000	2	Other
1896	Fiber Glass Roll (Composite Pole) wooven 400 gsm	6	350000	2	Other
1897	Epoxy Resin (kg) (Clear)	7	350000	2	Chemicals
1898	Brushes	8	350000	2	Other
1899	Mixing Cups	9	350000	2	Other
1900	Silicon Pipes	10	350000	2	Other
1901	Nitrile Gloves	11	350000	2	Other
1902	Power Splitter Connectors	12	350000	2	Other
1903	LMR 400 N(F) Connectors Model No: EZ-400-NF-X	13	350000	2	Other
4120	Display 22" full HD	5	350000	7	Other
4121	WiFi Adapter TPLink	6	350000	7	Other
1904	LMR 400-LLPX RF Cable	14	350000	2	Other
1905	N type PCB Mount ACx1165-ND	15	350000	2	Other
1906	Carbide End Mill 4 Flute 1x50mm	16	350000	2	Other
1908	Carbide End Mill 4 Flute 2x50mm	17	350000	2	Other
1909	Carbide End Mill 4 Flute 3x50mm	18	350000	2	Other
1910	Carbide End Mill 4 Flute 4x75mm	19	350000	2	Other
1911	Carbide End Mill 4 Flute 6x100mm	20	350000	2	Other
1912	Carbide End Mill 4 Flute 8x100mm	21	350000	2	Other
1913	Carbide End Mill 4 Flute 10x100mm	22	350000	2	Other
1915	Carbide End Mill 4 Flute 12x100mm	23	350000	2	Other
1917	Center Drill 5mm dia	25	350000	2	Other
1918	Dial Indicator	26	350000	2	Other
1925	Substrate R04350b 12" x 18" 1.5 24mm for Power Splitter	31	350000	2	Other
7362	Shredder machine heavy duty	1	400000	7	Office Equipment
1938	Voltage Converter RCNUN 8-36 to 13.8V, 40A	1	350000	2	Other
1939	Rivnuts M3, M4, M5	2	350000	2	Other
1940	4 Pin 30 Degree Socket Locking Connector	3	350000	2	Other
1941	4 Pin Elbow with Socket Plug Locking Connector	4	350000	2	Other
1942	FGG PHG 4 Pin Locking Connector	5	350000	2	Other
1943	WS20 9 Pin angled waterproof locking connector	6	350000	2	Other
1944	WS20 5 Pin angled waterproof locking connector	7	350000	2	Other
1947	WS28 20 Pin angled waterproof  connector	8	350000	2	Other
1948	4mm fitting push connector	9	350000	2	Other
1950	300 mm copper heat tubing	10	350000	2	Other
1951	400 mm copper heat tubing	11	350000	2	Other
1952	69 mm Aluminium radiator	12	350000	2	Other
1953	50x50x15 copper heatsink	13	350000	2	Other
1954	100x50x15 copper heatsink	14	350000	2	Other
1955	Waterproof heat shrink connectors	15	350000	2	Other
1958	12V, 5A DC Adapter	17	350000	2	Other
1959	USB Type C Cable	18	350000	2	Other
1960	Ethernet Cable	19	350000	2	Other
6937	HP EliteDesk 800 G5 Ci7 9th 8GB 1TB DVD with 23” Monitor	1	200000	7	Parts
6938	HP Elite book 840 G5 Ci7 8th 16GB 512GB 14 Win10	1	200000	7	Machinery / Equipment
7477	NAS device	5	200000	7	Machinery / Equipment
1961	Latch Catch Lock SS Industrial Grade	20	350000	2	Other
1962	L brackets	21	350000	2	Other
7451	Intel NUC-BoxNUC7i7BNHi w/o Ram & HDD	1	200000	7	Machinery / Equipment
7453	NEMA STUDIO	3	200000	7	Software
7452	HP LED N246 23.8”	2	200000	7	Machinery / Equipment
6944	Chain Wrench	7	200000	7	Tools / Test Equipment
1963	AC 3pin industrial socket	22	350000	2	Other
1964	Wiring Ducts	23	350000	2	Other
1965	Emergency stop button IP64,400 voltage	24	350000	2	Other
1966	Rubber Boots (Universal Shock Absorber Boots)	25	350000	2	Other
1967	Circuit breaker	26	350000	2	Other
6949	Heat Gun	12	200000	7	Tools / Test Equipment
6946	Digital Venire Caliper	9	200000	7	Tools / Test Equipment
6945	Screw Driver Set	8	200000	7	Tools / Test Equipment
6943	Spanners and Ratchets Set	6	200000	7	Tools / Test Equipment
1969	Bolts, Washers and Nuts	27	350000	2	Other
1974	Aluminum Sheets (530x300mm: 4 Pieces) (530x500mm: 2 pieces)	28	350000	2	Other
1981	FR4 PCB Boards	30	350000	2	Other
1983	GX16 8 pin connector	31	350000	2	Other
1985	8mm sheet 8x4ft	32	350000	2	Other
7786	HP Laserjet Pro M254DW Color Printer	4	350000	7	Office Equipment
4095	Mechanical Tachometer Pick-up	3	350000	7	Machinery/Equipment
2857	Vac checker	12	350000	7	Parts
1986	5mm sheet 8x4ft	33	350000	2	Other
1987	15mm sheet 8x4ft	34	350000	2	Other
1988	18mm sheet 8x4ft	35	350000	2	Other
1989	20mm sheet 8x4ft	36	350000	2	Other
1990	30mm sheet 4x4ft	37	350000	2	Other
1991	Round shaft od 60mm length 1500mm	38	350000	2	Other
1992	Round shaft od 80mm length 1000mm	39	350000	2	Other
1993	Round shaft od 40mm length 1000mm	40	350000	2	Other
1994	Round shaft od 50mm length 800mm	41	350000	2	Other
1995	Round shaft od 100mm length 250mm	42	350000	2	Other
1996	Round shaft 320mm length 100mm	43	350000	2	Other
1997	Angle iron 75x75 length 1500mm	44	350000	2	Other
1998	Flexible hose 50mm (/ft) 100 bar rated	45	350000	2	Other
1999	Fasteners set (nut,bolt and 2x washers) 12mmx50mm	46	350000	2	Other
2000	Nylon strap 4 ton 50mm wide	47	350000	2	Other
2004	Isolators	50	350000	2	Other
1968	RF Power Detector Module AD8317	1	350000	2	Other
1970	RF Log Detector IC 8GHz 50dB	2	350000	2	Other
1971	Substrate RO4350B 12x18", 0.8mm	3	350000	2	Other
1972	Passive Components (capacitors, resistors, diodes & rectifiers) for RF Power Meter	4	350000	2	Other
7568	Shredder	1	250000	7	Office Equipment
1973	Linear Voltage Regulators 450V Adj 3 Terminal	5	350000	2	Other
1975	Passive Components (capacitors, resistors, inductors) for bandstop filter	6	350000	2	Other
2016	Cooler Master RPD Grease High Performance Thermal Paste	19	350000	2	Other
2050	Matt Glass Fiber	1	350000	2	Other
2052	Polyester Resin	2	350000	2	Other
2053	Prime 27 epoxy resin	3	350000	2	Other
2054	Thinner	4	350000	2	Chemicals
2063	Pre-Preg carbon fiber	1	350000	2	Other
2065	Air Vent valve for marlin 2 fuel tank	2	350000	2	Parts
2066	Double Electric Sockets	3	350000	2	Other
2069	Single Electric Sockets	4	350000	2	Other
2122	Aluminum Sheets 4x8 ft, 3mm	18	350000	2	Other
2123	Emergency Stop button IP64, 400 volts	19	350000	2	Other
7670	UPS 3KVA	3	160000	7	Machinery / Equipment
7669	Laptop	2	160000	7	Other
7668	Roller Blinds	1	160000	7	Other
2124	16 A Industrial Circuit Breaker	20	350000	2	Other
7703	Window Blinds	4	300000	7	Other
7701	False Ceiling	2	300000	7	Other
7702	Lights & Fans for False Ceiling	3	300000	7	Machinery / Equipment
7704	Paint Work & Electric Wiring	5	300000	7	Other
7686	Feston wire wih accessories	3	300000	7	Other
2125	Bolts, Washers and Nuts Socket head SS, m3,m4,m5	21	350000	2	Other
2132	Tallies for hardware tagging	22	350000	2	Other
2133	Rubber Seal	23	350000	2	Other
2245	05xCDs and 1x CD ROM	21	350000	2	Other
2197	Aluminum tube 22*16mm (rod of 20ft length each)	1	350000	2	Other
7687	Electric DB with accessories	4	300000	7	Machinery / Equipment
2198	Fiber Glass roll (Composite Pole) wooven 400gsm	2	350000	2	Other
2199	Epoxy Resin (kg) (Clear)	3	350000	2	Chemicals
2200	Brushes	4	350000	2	Other
2201	Mixing cups	5	350000	2	Other
2202	Silicon Pipes	6	350000	2	Other
2203	Heat Shrink Tube	7	350000	2	Other
2204	Carbide End Mill	8	350000	2	Other
2205	Ball Nose	9	350000	2	Other
2206	Lathe Machine cutting tool	10	350000	2	Other
2207	PVC Pipe length of 20ft each	11	350000	2	Other
2208	Nitrile Glove	12	350000	2	Other
2209	KN95 mask	13	350000	2	Other
2211	Tool holder apmt facemill	14	350000	2	Other
2774	Key wire cut	7	350000	2	Other
7567	HP colour Laser jet pro M254DW	1	250000	7	Office Equipment
2214	IC RF AMP GP 9GHz-18GHz 32 SMT	2	350000	2	Other
7570	Blower	3	250000	7	Machinery / Equipment
2215	IC RF AMP GP 8 GHz -12 GHz  16 QFN	3	350000	2	Other
2241	Corsair Hydro H45 Liquid CPU Cooler	17	350000	2	Other
2233	Graphic card Gigabyte GE force RTX 2060	10	350000	2	Parts
2213	PC Cooler Fan	1	350000	2	Other
2232	RAM corsair vengeance DDR4 16 GB 3200 Bus	9	350000	2	Parts
2234	SSD kingston A2000 NVMe PCle SSD 1 TB	11	350000	2	Other
2871	Brass R Nipple 1/4 x 1/2	26	350000	7	Parts
2870	Brass F Nozzle 1/2 x 3/8	25	350000	7	Parts
2872	SS Jubilee Clip 1in	27	350000	7	Parts
2873	SS Jubilee Clip 5/8	28	350000	7	Parts
2866	Brass B Nipple 1/4	21	350000	7	Parts
2867	Brass H-Nozzle 8x 1/4	22	350000	7	Parts
7569	Vaccum cleaner	2	250000	7	Machinery / Equipment
7397	HDMI /DVI /VGA convertor	19	250000	7	Parts
7592	Water Dispenser	11	250000	7	Other
2859	Tubblar bagging flim	14	350000	7	Parts
2860	Exhaust silencer	15	350000	7	Parts
2216	Drill Bit	4	350000	2	Other
2218	Lacquer for Mesh Antenna	6	350000	2	Other
2219	Mesh for Antenna	7	350000	2	Parts
2223	Riveting set for PCB vias 0.8mm,0.6mm,0.4mm	8	350000	2	Other
2225	Areldite 420 Bonding adhesive (1.4kg kit)	2	350000	2	Other
2227	Stock for Aluminum clamps	4	350000	2	Other
2228	Stainless Steel stock for gravity casting mold	5	350000	2	Other
2244	Stationery Items (Rims, Minute sheets, pens, staplers with pins, gumstick, white board marker, envelopes, sticky notes, flags)	20	350000	2	Other
2250	HDMI, duct tape, cells, socktes, ties	26	350000	2	Other
2251	Generator Fuel for field trials	27	350000	2	Other
2261	Supply of MCV (Manual Charging Valve) 1/2" dia Rated Pressure:100 bar, Type: Ball Valve Including 600 Class Flange, Thread Fabrication, Gasket, Nut & Bolts & Installation	1	350000	2	Other
2235	HDD Seagate 1 TB 3.5"	12	350000	2	Other
2263	Supply of MPG(Manual Pressure guage) Type: Analog Dial Pressure Guage, Rated Pressure: 100 Bar including 600 Class Flange, Thread Fabrication, Gasket, Nut & Bolts, Installation & Calibration Validation	2	350000	2	Other
2265	Supply of MVV(Manual Vent Valve) 1" Dia Rated Pressure: 100 Bar, Type: Ball Valve Including 600 Class Flange, Thread Fabrication, Gasket, Nut & Bolts & Installation	3	350000	2	Other
2268	Supply of MRV (Mechanical Relive Valve ) 1/2" Dia Rated Pressure: 100 Bar , Type: Safety valve including 600 Class Flange, Thread Fabrication, Gasket, Nut & Bolts, Installation & Calibration Validation	4	350000	2	Other
2270	Supply of MDV (Manual Drain Valve) 1/2" Dia Rated Pressure: 100 Bar , Type: Ball valve including 600 Class Flange, Thread Fabircation, Gasket, Nut & Bolts, Installation	5	350000	2	Other
2278	16Pin molex connector Male	7	200000	2	Parts
2271	Supply of 4" dia Header with Tee, Flanges & Fittings. Including 600 Class Flange, Gasket, Nut & Bolts & Installation. For outlet of tank.	6	350000	2	Other
2272	Electric cable 90m three core copper 1 roll	1	200000	2	Parts
2242	PC Fan Cooler Master Sickle Flow 120 ARGB case Fan	18	350000	2	Other
7403	Dustbin	25	250000	7	Office Equipment
8479	Manufacture of main skeleton hull frame of prototype ROV with following characteristics:\r\na.  Size: 2700mmx1170mmx1250mm (LxWxH)\r\nb.  Operational depth: up to 300m\r\nc.  Magnetic Signatures <50nT at 1.5m (in X, Y and Z axis)\r\nd.  Acoustic Signatures < 30dB	1	300000	7	Other
7506	Haier Split AC 1.5 Ton with installation	7	250000	7	Office Equipment
5647	Waterproof IP65 connector YT-RJ45-JSX-16-001	2	350000	2	Parts
7584	Lights (2'x2')	2	250000	7	Other
7585	Dimmer Lights	3	250000	7	Other
2874	Voltage Regulator	29	350000	7	Parts
2936	Auto dimming helmet	37	350000	7	Parts
2881	12 ft ladder	35	350000	7	Other
2868	Brass H-Nozzle 8 x 3/8	23	350000	7	Parts
2869	Brass Bush 3/8 x 1/2	24	350000	7	Parts
2933	Tungsten Electrode (Thoriated, Ceriated, Lanthanated, Zirconiated, Pure)	34	350000	7	Parts
7586	Fans	5	250000	7	Other
7587	42U Cabinet	6	250000	7	Other
7589	4 Ton standing AC and 2 Ton Inverter AC with Installation	8	250000	7	Other
7590	Electric Tea Kettle plus Crockery	9	250000	7	Other
7591	Neon Lights with 20 adaptor	10	250000	7	Other
7583	Electric & Cameras Cabling and Network (02 x lab)	1	250000	7	Parts
7393	CCTV camera adaptors	15	250000	7	Parts
7398	LED TV (32")	20	250000	7	Office Equipment
7396	Rasberry PI device, accessories and wire	18	250000	7	Office Equipment
7395	16 port switches	17	250000	7	Parts
7390	Sound system	12	250000	7	Machinery / Equipment
7496	VR Headset and accessories	1	250000	7	Office Equipment
7497	Audio Headset	2	250000	7	Parts
7498	Wireless Router	3	250000	7	Parts
7502	Emerson UPS Vertiv Liebert GXT4 On-Line 10KVA	3	250000	7	Other
7291	Complete Desktop Computer with monitor, mouse and keyboard	11	400000	7	Office Equipment
4925	Sonatest RDT1220 20Mhz ?” Delay Line Probe, Microdot Connector.	6	350000	7	Parts
4923	Sonatest RDT2525 2.25Mhz 1/4” Delay Line\nProbe, Microdot Connector.	4	350000	7	Parts
4926	SMG NDT BNC to Microdot Cable 6ft	7	350000	7	Parts
4955	GPU for Gomma PC	27	350000	7	Parts
7289	UPS 600 VA	9	400000	7	Office Equipment
7292	Multimedia Projector	12	400000	7	Office Equipment
7320	Complete Computer System	1	400000	7	Office Equipment
166	Processor (AMD Ryzen 5 5600X)	24	350000	7	Parts
168	Motherboard (ASUS Strix B550A)	25	350000	7	Parts
169	RAM Corsair Vengeance DDR4 16GB 3600	26	350000	7	Parts
170	Graphics Card Gigabyte GeForce GTX1650	27	350000	7	Parts
171	SSD Samsung 500GB 980 NVMe M2 SSD	28	350000	7	Parts
173	Cooler Corsair H60 Liquid CPU Cooler	30	350000	7	Parts
174	Power Supply Corsair TX650 650W 80Plus	31	350000	7	Parts
172	1TB Seagate Barracuda HDD	29	350000	7	Parts
1120	HP Laser Jet pro wireless printer (Double Sided printing)	5	350000	7	Office Equipment
1121	Lenovo V530 desktop (core i3, 9th Gen, 8GB RAM, 1TB HDD, LCD, keyboard & mouse included)	6	350000	7	Office Equipment
1226	Lamp with stand	3	350000	7	Office Equipment
1228	Magnetic Drill (1500 w, 50 mm bore & 390 mm Stroke)	5	350000	7	Office Equipment
1026	Drill Machine Stand	18	200000	7	Tools / Test Equipment
1028	Fix Spanner set USA	20	200000	7	Tools / Test Equipment
1017	Screw driver Long	9	200000	7	Tools / Test Equipment
1295	Keyboard A4 Tech KR-85	71	350000	7	Office Equipment
1242	Intell Processor Core i5-10400 LGA 1200	19	350000	7	Office Equipment
1249	Logitech K120 USB Keyboard	26	350000	7	Office Equipment
1255	Asus Prime B550 M-A Wifi Motherboard	32	350000	7	Office Equipment
1652	Window 10 rigister version	2	200000	7	Software
1654	Electric RAM DDR4 8GB up to 2666	4	200000	7	Machinery / Equipment
1565	Imperial Allen Wrenches 3/32" , !/4"	5	350000	7	Tools / Test Equipment
1570	Levelling Tool 4ft	8	350000	7	Tools / Test Equipment
1795	Supply of PVC Duct, Main Duct for Dust Suction 6 to 8 - 30RFT Elbows, Reduces, Flexible Pipe, Damper with Supporting Materials. Complete in all respect	2	350000	7	Parts
2932	TIG Welding Torch Stubby Gas Lens (Pyrex) for WP17	33	350000	7	Parts
2935	TIG welding kit (single)	36	350000	7	Parts
1801	HD COFDM Wireless Digital Video Transmitter (KP-CM100H) COFDM MODULATER RF POWER AMPLIFIER (100 WATT) VIDEO ENCORDER GPS DATA ENCORDER. Airborne Omni Antenna Frequency: 1244~1254MHz Gain: 5~8dBi, Polarization: Vertical Maximum Input Power: 50W, Power Capacity: 100W Connector: N, Length: 20~25cm (QTY 02)	2	350000	7	Parts
1800	1 Channel HD COFDM video receiver (KP-DRB01) COFDM MODULATER RF RECEIVER MODULE VIDEO ENCORDER GSP DATA ENCORDER . FRP Omni Antenna Frequency: 1244~1254MHz Gain: 9.5-15dBi, Polarization: Vercital Max Power:100W, Beamwidth:360 Digress, Connector: N-K, Size: 1.8 meter (QTY 02)	1	350000	7	Parts
1816	Avid Enclosure	4	350000	7	Parts
1772	Storage Racks for Avid	1	350000	7	Furniture
2093	Bathroom Exhaust Fan (With Shutter)	1	350000	7	Furniture
2094	Air Conditioner	2	350000	7	Furniture
2017	10kV High Voltage Probe	20	350000	7	Tools / Test Equipment
1818	Vaccum Cleaner	6	350000	7	Tools / Test Equipment
2120	Hydraulic Crane (lifting capacity 2 ton, universal ball joint for manual movement)	16	350000	7	Machinery / Equipment
2117	AOC 24E1H 23.8" FHD 60Hz IPS Frameless Monitor	14	350000	7	Office Equipment
2104	Lab Upgradation (workstations, tables, office chairs, UPS and cabinet required for Ros)	1	350000	7	Furniture
2105	Processor AMD Ryzen 5 5600G	2	350000	7	Parts
2012	Kingston 32GB Data Traveler Exodia Flash Drive DTX/32GB	15	350000	7	Parts
2013	Gigabyte Nvidia GeForce GTX 1650 OC 4GB GV-N1650OC-4GD	16	350000	7	Parts
2014	Corsair Hydro H45 Liquid CPU Cooler	17	350000	7	Parts
2015	Thunder Air Box 72 Tcs-720 Case Fans Kit	18	350000	7	Parts
1814	Hand Torch	2	350000	7	Tools / Test Equipment
1815	Weighing Scale 300kg	3	350000	7	Tools / Test Equipment
1813	Vernier Calliper	1	350000	7	Tools / Test Equipment
2335	Notice boards	13	350000	7	Furniture
2229	IT Equipments (Monitor 42 inch, HDMI cable, keyboard, mouse)	6	350000	7	Office Equipment
2394	RO Plant	25	350000	7	Office Equipment
2236	Power Supply Gigabyte p750GM 750 Watt 80 Plus	13	350000	7	Office Equipment
2237	PC Case Thermaltake H350 Tempered Glass RGB	14	350000	7	Office Equipment
2240	Logitech M90 USB Mouse-910-001795	16	350000	7	Office Equipment
2243	AOC 24E1H 23.8" FHD 60Hz IPS Frameless Monitor	19	350000	7	Office Equipment
2239	Logitech k120 USB Keyboard	15	350000	7	Office Equipment
2224	Single Door Deep Freezer 15 cubic feet	1	350000	7	Office Equipment
2231	Motherboard Asus prime B550,-A WIFI	8	350000	7	Office Equipment
2230	Processor AMD Ryzen 5 5600G	7	350000	7	Office Equipment
2466	25mm chuck	34	350000	7	Parts
2497	Vacuum Pump	2	350000	7	Machinery / Equipment
2436	Precision Cutter Plier blue	6	350000	7	Tools / Test Equipment
2438	INGCO Soldering iron 60W	8	350000	7	Tools / Test Equipment
2437	INGCO Soldering iron 30W	7	350000	7	Tools / Test Equipment
2455	Nail gun	24	350000	7	Tools / Test Equipment
2565	Mouse	25	350000	7	Other
2563	Metal Furnace	23	350000	7	Office Equipment
2564	UPS INTELL Lab	24	350000	7	Office Equipment
2527	INGCO CDT08120 Drill/Screwdriver	8	350000	7	Tools / Test Equipment
2526	Laser level stand	7	350000	7	Parts
2939	Low Noise Amplifier with Filter Frequency: 1245-1255, Gain: 10dBm, Power: 12V DC	1	350000	7	Parts
7306	1.5 Ton Inverter Air Conditioner	2	400000	7	Office Equipment
7305	2 Ton Inverter Air Conditioner	1	400000	7	Office Equipment
2258	UPS battery and wiring in NDT Lab	1	350000	7	Office Equipment
3007	Thinner	16	350000	2	Other
2226	Angle measuremnt laser	3	350000	7	Tools / Test Equipment
2119	Lathe Machine (4ft, 1 HP imported motor, 200mm bed, 38mm bore, 225mm center)	15	350000	7	Machinery / Equipment
1004	Office Chairs (Masters Aura LBC Black)	76	350000	7	Office Equipment
1003	Storage rack	75	350000	7	Other
146	UPS (Rating: 3.7KVA)	5	350000	7	Other
666	High pressure air vessel horizontal capsule type, capacity 880L, size: 32.5" dia & 48" straight length	1	350000	7	Parts
4425	Tesla meter to measure magnetic induction, 3-4 measure ranges\nMaterials supplied: Tesla / Gauss Meter type KOSHAVA 5 (without probe), 1m probe extension cord, plastic Etui, USB cable to connect PC, with calibration certificate, manuals in English and soft	5	350000	7	Parts
4992	Vacuum Pump	21	350000	7	Other
4746	Jet Cat P220 Rxi – Turbine / Accessories / Quantity as below:\n(i) JetCat P220 Rxi Turnine Part no: 71152- 0000 Qty:01\n(ii) Net Jet Part no: 61128-0052 Qty:01\n(iii) Engine Mount P220-RXi Part no: 41152- 0048 Qty:01\n(iv) ECU V12 Part no: 61102-0025 Qty:01\n(	4	350000	7	Other
4734	Fabric rolls rack	8	350000	2	Other
7096	Material	4	300000	7	Other
1794	Supply of Dust Collector with Automatic Motorized Shaking System, Bag Filters, Hooper, Dust Bin,  BC SWSI DD Centrifugal Fan with Best Quality Imported Motor, Control Panels &  HEPA-13 Filter's Sections with Holding Frames, Imported Filters. Complete in all respect (Size: 42"W x 33"L x 100"H) Note: G.I HEPA Plenum, Damper at fan suction & G.I ducting connecting fan and DC are included.	1	350000	7	Parts
8426	Ground Data Receiver with power supplies and 10x Zoom camera with gyro stablization	16	350000	7	Parts
2802	CIMC-3500VH \r\nISUZU Truck NPR66PU with custom green color\r\n- custom extended bed\r\n- container mounting assembly points\r\n- bed leveller hard point\r\n- generator mounting points\r\n- installation of new tyres tyres ti accommodate bed size and stability enhancement\r\n- bed levelling system\r\n- hyraulic jacks\r\n- hydraulic pressure tank with motor\r\n- Genset 19KVA\r\nPower Smartgen controller + MCCB + Battery charger generator can perform its utmost capacity both at 100% prime and 110% emergency standby use. Customized built-in high performance residential muffler minimizes the noise level. User-friendly external power outlet flap.	1	350000	7	Machinery / Equipment
2934	Leather gloves pair	35	350000	7	Parts
5131	Flange AP-201	6	350000	7	Parts
4153	Jerry Can	25	350000	7	Other
6294	Storage boxes multiple sizes	10	350000	7	Other
7929	Electric power rails	6	350000	7	Other
7931	False ceiling 2’x2’ gypsum for complete ceiling including fitting and fixtures with 03 x false ceiling fans, 04 x false ceiling lights 2’x2’) (244 sq. ft.)	8	350000	7	Office Equipment
8997	XRF testing of P220 engine outer body	1	350000	3	Consultancy
8998	3D Scaning	2	350000	3	Fabrication
8999	MS bending parts	3	350000	2	Raw Material
9000	MS rod for axle	4	350000	2	Raw Material
9002	Stand off EDM	5	350000	3	Fabrication
9003	Front Stand off Aluminum 1 set (spare)	6	350000	3	Fabrication
9004	Jetcat EGT probe Temperature Sensor	7	350000	2	Other
9005	Front stand off EDM	8	350000	3	Fabrication
9006	Copper electrode machining	9	350000	3	Fabrication
9008	Launcher straps stitching	10	350000	3	Fabrication
9009	Engine parts replacement	11	350000	3	Equipment Modification
9010	Engine repair	12	350000	3	Equipment Modification
9011	Diesel Engine lubricants	13	350000	2	POL
9012	Engine starter (spare + repair)	14	350000	7	Parts
9013	Launcher Cover	15	350000	7	Other
9015	SS Sheet for circular body	16	350000	2	Raw Material
9016	SS base mounting	17	350000	2	Raw Material
9017	Laser Cut	18	350000	3	Fabrication
9018	Sheet Rolling	19	350000	2	Raw Material
9019	Wire Cut	20	350000	3	Fabrication
9020	Brass rods	21	350000	2	Raw Material
9021	Brass pipe	22	350000	2	Raw Material
9238	Lamination pannels for SVDC observatory room	1	300000	2	Raw Material
9239	Installation/ labour charges	2	300000	3	Building Modification
9240	5 seater for SVDC observatory room	3	300000	7	Furniture
9241	LED lights for SVDC observatory lobby	4	300000	7	Appliance
9242	3 M CAT-6 cable coil	1	300000	2	Raw Material
9243	Internet Switch for SVDC	2	300000	7	Other
9244	PA Exchange System	3	300000	7	IT Equipment
9245	Excavation/ Installation Charges	4	300000	3	Equipment Installation
9126	CONN Terminator plug SMA 50 Ohm	3	350000	7	Parts
9127	DC Block SMA-F/SMA-M 18GHz ROHS	4	350000	7	Parts
9128	GPS antennas	5	350000	7	Parts
9124	RF Adapter QMA PLUG - SMA PLUG	1	350000	7	Parts
9125	RF Adapter QMA JACk - SMA PLUG	2	350000	7	Parts
9130	Temperature sensor	7	350000	7	Test / Measuring Equipment
9131	Cleco fasteners	8	350000	7	Parts
9132	Centre of Gravity meter with angle meter	9	350000	7	Test / Measuring Equipment
9133	Machine Taper Reamer	10	350000	7	Parts
9134	Crimping Tool and Consumables	11	350000	7	Tools
9135	Clinging Stand-offs	12	350000	7	Parts
9140	Precision Tool kit	17	350000	7	Tools
9023	Metal sheet for Dish manufacturing & bending charges	1	350000	2	Raw Material
9024	Refill of argon cylinder	2	350000	3	Other
9025	Aluminum RF connectors	3	350000	7	Parts
9026	Aluminum SA FEM part I dia 80mm x 100mm (each of 4 kg)	4	350000	7	Parts
9027	Aluminum SA FEM part I dia 30mm x 80mm (each of 0.7 kg)	5	350000	7	Parts
9028	Aluminum SA male part I dia 15mm x 80mm (each of 0.2 kg)	6	350000	7	Parts
9029	Teflon SA fem Isolator dia 40mm x 300mm (each of 1 kg)	7	350000	7	Parts
9030	MS sheet 8ft x 4ft x 10mm	8	350000	2	Raw Material
9031	Aluminum stock	9	350000	2	Raw Material
9032	Powder coating	10	350000	2	Other
9033	Acrylic sheet	11	350000	2	Raw Material
9034	Jubilee clamp set	12	350000	2	Other
9160	Solenoid	1	450000	7	Parts
9161	Hydraulic Manifold	2	450000	7	Parts
9162	Hydraulic fittings	3	450000	7	Parts
9233	2.0 Ton Ceiling Cassette AC Unit	1	300000	7	Appliance
9234	Fitting Accessories kit for ceiling unit	2	300000	7	Parts
9235	Installation	3	300000	3	Equipment Installation
9236	False ceiling of the mechanical engineering section	4	300000	3	Building Modification
9136	Rivet Nut (M3, 4, 6, 8, 10, 12)	13	350000	7	Parts
9137	Side Clamp Fasteners (Pack of 10)	14	350000	7	Parts
9138	Lenovo IdeaPad 3 15IAU7 Laptop - Intel Core i7 - 1255U, 8GB DDR4, 512GB SSD, 15.6" FHD Display	15	350000	7	Office Equipment
9139	DMM Fluke	16	350000	7	Test / Measuring Equipment
9142	Banana connectors (pair)	18	350000	7	Parts
9143	Toggle Switches	19	350000	7	Parts
9144	Assorted wires (coil 30m)	20	350000	7	Parts
9203	Custom 17mm socket	1	350000	7	Tools
9204	Custom 15mm socket	2	350000	7	Tools
9205	Custom grip wrench  100mm	3	350000	7	Tools
9206	Custom locknut grip 120mm	4	350000	7	Tools
9207	USB Hub charger	5	350000	7	Parts
9208	A4 Tech wired USB mouse	6	350000	7	Office Equipment
9209	Hypertherm 65 & 85 Replace. Ohmic-Sensing Retaining Cap Mech	7	350000	7	Parts
9210	Hypertherm 45XP 65 & 85  FineCut Nozzle, 5/pk	8	350000	7	Parts
9211	Hypertherm 65 & 85 Mechanized Shield	9	350000	7	Parts
9213	Hypertherm Electrode Copper Plus-Bulk Package (25 qty)	10	350000	7	Parts
9214	Hypertherm Electrode Copper Plus-Bulk Package (25 qty)	11	350000	7	Parts
9215	Hypertherm 65 - 85 Amp Drag Cutting Nozzle	12	350000	7	Parts
9216	Hypertherm 45 - 65 Amp Drag Cutting Nozzle	13	350000	7	Parts
9217	Hypertherm Handheld Fine Cut Swirl Ring	14	350000	7	Parts
9035	Display Pannels CDS 17" SQ	1	200000	2	Other
9036	Green connector	2	200000	2	Other
9037	Ribbon Connector 28pin female	3	200000	2	Other
9038	Ide connector 20pin	4	200000	2	Other
9039	Molex connector 20pin	5	200000	2	Other
9040	Pvolium potentio meter	6	200000	2	Other
9041	Power cable	7	200000	2	Other
9164	Hydraulic cylinder	4	450000	7	Parts
9151	Multimode (MM) SC fiber media converter	1	160000	7	Parts
9152	Optical fiber connector cables (ST-ST(Pair))	2	160000	7	Parts
9154	Optical fiber connector cables (SC-SC(Pair))	3	160000	7	Parts
9155	Customized box	4	160000	7	Parts
9156	Sel. 3m, 5m, 6m	5	160000	7	Parts
9157	RJ45 Ios	6	160000	7	Parts
9237	XRF analyzer: VEL-S\r\nwith USB Wi-Fi and Bluetooth USB. (without field stand)	1	350000	7	Test / Measuring Equipment
9042	VGA Cable	8	200000	2	Other
9218	Carbide End Mill dia 2mm length 50mm	15	350000	7	Tools
9219	Carbide End Mill dia 3mm length 50mm	16	350000	7	Tools
9220	Carbide End Mill dia 3mm length 100mm	17	350000	7	Tools
9221	Carbide End Mill dia 4mm length 100mm	18	350000	7	Tools
9222	Carbide End Mill dia 6mm length 75mm	19	350000	7	Tools
9223	Carbide End Mill dia 12mm length 100mm	20	350000	7	Tools
9224	Carbide Ball Nose dia 6mm length 100mm	21	350000	7	Tools
9225	Carbide Ball Nose dia 10mm length 100mm	22	350000	7	Tools
9226	Collet Set APMT 1604	23	350000	7	Tools
9228	Machining Tap dia 3mm	24	350000	7	Tools
9145	Pin probes	21	350000	7	Tools
9229	Machining Tap dia 4mm	25	350000	7	Tools
9230	Machining Tap dia 6mm	26	350000	7	Tools
9231	Machining Tap dia 8mm	27	350000	7	Tools
9146	BNC connectors	22	350000	7	Parts
9147	Assorted LEDs	23	350000	2	Other
5833	Resin & hardener	6	350000	2	Fabrication
9148	Momentary push button	24	350000	7	Parts
9248	MS Stock  (100mm X 50mm X 50mm)	3	350000	2	Raw Material
9249	Bending charges	4	350000	3	Fabrication
9043	Three Phase Surge Protection Device	1	200000	2	Other
9044	Three Phase Over & Under voltage Protection Device	2	200000	2	Other
9045	4-Position Universal Rotary Cab Change Over Switch	3	200000	2	Other
9046	Thinner supper 3 letter	4	200000	2	Other
9047	SMD Capacitor 22 25v	5	200000	2	Other
9049	IC Base round 8,14,16,18,20 pin	6	200000	2	Other
9050	RTV L	7	200000	2	Other
9051	Regulator Mica	8	200000	2	Other
9052	6A8 SMD Diode	9	200000	2	Other
9053	SMD 334	10	200000	2	Other
9054	Secrew ss 2.50m	11	200000	2	Other
9250	MS Rods	5	350000	2	Raw Material
9251	Reflector Supporting Pipe	6	350000	7	Parts
9252	Drill Bits (12mm, 12.5mm, 14mm, 14.5mm, 16mm, 16.5mm)	7	350000	7	Parts
9253	Paint and Red Oxide	8	350000	2	Chemical
9254	Teflon	9	350000	7	Parts
9255	Nuts and Bolts	10	350000	7	Parts
9286	Molykote 1000 Thread Paste	11	350000	2	Chemical
9287	WD-40 330 ml (330X48 = 1 Pack)	12	350000	7	Parts
9288	Dongcheng Magnetic Drill DJC series	13	350000	7	Parts
9289	BSE Antenna support boom	14	350000	7	Parts
9290	Teflon Rod (Dia: 50 mm Length: 1m)	15	350000	7	Parts
9291	PVC pipes (20 ft)	16	350000	7	Parts
9292	Turnbuckle S.S.	17	350000	7	Parts
9293	Paint (in Quarter)	18	350000	2	Chemical
9294	Tee Joint	19	350000	7	Parts
9295	Elbows	20	350000	7	Parts
9296	PVC Glue	21	350000	2	Chemical
9246	MS Sheet (4ft X 8ft X 5mm)	1	350000	2	Raw Material
9247	MS Sheet (4ft X 4ft X 3mm)	2	350000	2	Raw Material
4783	CONN SMA JACK STR 50OHM EDGE MNT	15	350000	2	Parts
9341	PCB Fabrication of Filters	1	350000	3	Fabrication
9342	N-type connector (panel mount)	2	350000	2	Other
9343	SMA connector (panel mount)	3	350000	2	Other
9344	Conference room AC repair	4	350000	3	Equipment Repairs & Maintenance
9345	Lipo safety bag	5	350000	2	Other
9355	Storage Drive 256 GB	6	350000	7	IT Equipment
9356	Wiring	7	350000	2	Other
9357	Jazz internet charges RF Lab	8	350000	3	Other
9358	Internet charges at MF	9	350000	3	Other
9055	2.0 Ton Ceiling Cassette AC Unit	1	300000	7	Appliance
9056	Fitting Accessories kit for ceiling unit	2	300000	3	Equipment Installation
9057	Installation Charges	3	300000	3	Equipment Installation
9158	Customized sticker for data diode	7	160000	2	Other
9159	Screws, nut bolts, connectors	8	160000	2	Other
9058	False Ceiling of the Executive Room	4	300000	3	Building Modification
6523	20 watt ruggedized power amplifier	10	350000	7	Parts
4858	Wiring	3	350000	2	Other
9059	Steel Wire Rope	23	350000	7	Parts
9060	6S 22000 mAh Lipo batteries	24	350000	2	Other
6648	Line Attenuator BW S3W2+ 3dB	153	350000	7	Parts
9061	Battery System	1	300000	2	Other
9062	Color Video Camera	2	300000	7	Parts
9063	Under Water Lights with Mountings	3	300000	2	Other
4616	ESC Tmotor Alpha 60A	14	350000	7	Parts
8804	SMA (M to F) RT Angled Connector	7	200000	7	Parts
8805	N type (F) to N type (F) Connector	8	200000	7	Parts
5089	Fan	12	350000	2	Parts
6998	14 pin Round Connectors	1	200000	7	Parts
9336	Steel Structure works	1	350000	3	Building Construction
9337	Electrical Works	2	350000	3	Building Construction
9338	Earthing Cost	3	350000	3	Building Construction
9339	Transportation Cost	4	350000	3	Other
9335	Control Board PCB	1	350000	7	Parts
5235	Heat shrink misc size (m)	39	350000	2	Other
5236	Allen key screws	40	350000	2	Other
5237	Wire mesh (m)	41	350000	2	Other
5239	VFD wire harness cover	43	350000	2	Parts
5240	U lugs (packet)	44	350000	2	Other
5241	Acrylic sheet with laser cutting (6mm)	45	350000	2	Other
5242	3U chassis	46	350000	2	Parts
5244	Metal sheet with laser cutting	48	350000	2	Other
5245	Fan filters set ( 4x large & 4 x small)	49	350000	2	Other
9109	Nut & bolts with washer	7	350000	7	Parts
9110	Cutting, grinding, buffing disc (packet)	8	350000	7	Parts
9111	Brake shoe	9	350000	7	Parts
7307	Research Officer Table (Standard)	1	400000	7	Office Equipment
7326	Complete Complete computer system CPU, monitor, keyboard, mouse	3	400000	7	Office Equipment
5238	Hydraulic wire crimper	42	350000	7	Tools / Test Equipment
4262	Lenovo V530 desktop (Core i3 9th Gen, 8GB RAM, 1TB HDD, keyboard & mouse included)	26	350000	7	Office Equipment
1243	Motherboard Asus TUF B560-Plus Gaming (Wifi) LGA 1200	20	350000	7	Office Equipment
9102	INGCO Air impact wrech - AIW11223	1	350000	7	Parts
9104	INGCO Air impact wrech - AIW12562	2	350000	7	Parts
9105	INGCO 10pcs 1/2 impact socket set - HKISSD12101 (10-24)	3	350000	7	Parts
9106	Paint (qtr) + Red Oxide	4	350000	2	Chemical
9107	Parachute release mechanism	5	350000	7	Parts
9108	AVID Mach4 software renewal	6	350000	7	Software
5243	PCB development	47	350000	3	Fabrication
7609	30 A ESC	2	300000	7	Tools / Test Equipment
1356	RS485 controller with temperature sensor PT-100	1	300000	7	Parts
5922	Wheels	14	350000	7	Parts
425	Structure block for Test bench	1	350000	7	Other
9112	Rawal bolts for ground anchoring	10	350000	7	Parts
9113	Actuator lubrication oil	11	350000	2	POL
9114	Actuator high temperature grease	12	350000	2	Chemical
9115	Welding rod	13	350000	2	Raw Material
9116	Drill bits 6,8,10,12,14,16,18	14	350000	2	Other
9117	Welding, safety gloves packet	15	350000	2	Other
9118	High pressure pnuematic pipe	16	350000	7	Parts
9119	Pneumatic hose	17	350000	7	Parts
9120	Loctite Red	18	350000	2	Other
9121	Argon, oxygen, LPG cylinder refill	19	350000	3	Other
6847	Electric wiring for UPS	5	350000	3	Other
4230	Anemometer	20	350000	7	Tools / Test Equipment
989	T type spanner set (18' long handle)	61	350000	7	Tools / Test Equipment
5316	TM4C123GH6PMI  ARM Microcontrollers - MCU Tiva C Series MCU	1	200000	2	Parts
4456	Dipolar Switch Mode Microwave Power Supply	1	350000	7	Parts
2442	INGCO Hot Melt Glue Gun	12	350000	7	Tools / Test Equipment
1743	Aluminum Welding plant (argon) along with cylinder and consumables	1	350000	7	Machinery / Equipment
467	Lenovo V530 desktop(core i3 9th Gen,8GB RAM, 1 TB HDD, Keyboard & Mouse Included)	6	350000	7	Machinery / Equipment
1266	Aoc 24E1H 23.8" FHD 60Hz IPS Frameless Monitor	43	350000	7	Office Equipment
983	L Type Spanner Set (8-24 mm)	55	350000	7	Tools / Test Equipment
974	Vernier Calliper	46	350000	7	Tools / Test Equipment
4093	ROTAX Engine (914UL3)	1	350000	7	Machinery/Equipment
814	SCREW COMPRESSOR,DRYER i)High pressure air compressor SCW150 150L/ Min 300BAR 3KW Single Phase 220V 50Hz ii)Consumables/Spares .High pressure pipe  . Air filter  .Activated Carbon filter  iii) Maintenance parts (+ auxillaries)	1	350000	7	Parts
6856	DJI Battery	14	350000	2	Parts
6857	Box files for training course material	1	350000	2	Other
6861	Paper rim	5	350000	2	Other
1227	Plastic Storage Box	4	350000	7	Other
5458	PSD4135G2-90UI  TQFP80 ST	1	200000	2	Parts
5313	MAX232ACSE+  IC TRANSCEIVER FULL 2/2 16SOIC	30	200000	2	Parts
5311	5031590200 Headers & Wire Housings 1.5W/BSGLDIPSTRecAssy2CktW/Bos WKnk Beige	28	200000	2	Parts
5299	LFXTAL003240Bulk  CRYSTAL 16.0000MHZ 30PF TH	17	200000	2	Parts
5461	Fiber Glass mold for body of storage box	2	350000	7	Other
2116	Thermaltake Riing 12 LED RGB Radiator Fan Sync Edition (3-fan Pack) CL-F071-PL12SW-A	13	350000	7	Parts
1694	Turbine mounting Clips for JetCat P1000-PRO (Part# 411570048)	1	350000	7	Parts
433	COAIRE BRAND Oil Free Scroll Air Compressor (Without Receiver Tank ) Model: AL10  Main Specifications: Motor Power: 50/380v/3-Phase/3.7 kW x 2, Maximum Working Pressure : 9.9 bar, Capacity FAD : 0.704 m3/min , Controller : MICOM , Noise: 53 dB(A)	1	350000	7	Other
8487	Integrated Smart UPS 500VA 230V for ROV Design Lab	1	300000	7	Machinery / Equipment
7422	HP LEDs HP N246 23.8" 	2	200000	7	Machinery / Equipment
7158	Meanwell HLG-320H-30A 320W Power Supply (30V, 10.7A)	1	350000	7	Parts
5912	Landing Gear Axles	4	350000	2	Parts
5913	Vacuum Gauge	5	350000	2	Parts
8548	Purchase of electronic test bench equipment with power supply and MISC, Desktop Computers Core i5, 8gb ram, 1TB HD, 19" LED, Laptop 8 Geneal Core i5, Quad Core, 8gb ram, DDR Core, DDR4, 500GB.\r\nUnderwater performance testing setup	1	300000	7	Other
8555	Purchase of equipments for UUVs Project	8	300000	7	Other
7101	Desktop PC core i5 8gb ram/ 1tb hard drive	2	300000	7	Machinery / Equipment
5834	Wax boxes	7	350000	2	Fabrication
7104	Workstation table	4	300000	7	Furniture
630	Desktop Computer (Specs: Intel Core i.7 ,Hard disc 1 TB,RAM 16GB, 64 bit window)	1	300000	7	Office Equipment
7607	Mounting Bracket	2	300000	7	Parts
7103	Laptop core i5 8gb ram/ 1tb hard drive	3	300000	7	Machinery / Equipment
7658	HP Probook 8GB 1TB	3	300000	7	Machinery / Equipment
5559	turbat	1	350000	3	Building Construction
6880	12 double socket and wiring	2	350000	3	Building Modification
7656	Dell Precision T7810 Workstation	2	300000	7	Machinery / Equipment
7653	HP Z440 Workstation	1	300000	7	Machinery / Equipment
7088	Lenovo Idea Pad L3 15IML05	1	300000	7	Machinery / Equipment
4094	Electric Starter large (High Duty)	2	350000	7	Machinery/Equipment
4096	Engine Suspension Frame	4	350000	7	Parts
5723	Brass Rod 3mm	4	350000	2	Fabrication
5724	Brass Rod 4mm	5	350000	2	Fabrication
5725	Brass rod 4.7mm	6	350000	2	Fabrication
5726	Brass Rod 5mm	7	350000	2	Fabrication
5727	Brass Rod 5.5mm	8	350000	2	Fabrication
5728	PVC Pipe	9	350000	2	Fabrication
5735	8 ft. Brass rod 8mm	7	350000	2	Fabrication
5736	A3 drawing sheets for jammer	8	350000	2	Stationary
5739	VGA to DVI converter	11	350000	2	Parts
5740	HDMI to VGA converter	12	350000	2	Parts
5741	5 pin switches	13	350000	2	Parts
5742	Toggle switch 10A	14	350000	2	Parts
5743	Toggle switch 15A	15	350000	2	Parts
5745	Generator Fuel 	2	350000	2	POLs
5746	Cable for transmitter (BNC Cable)	3	350000	2	Parts
5747	Contact cleaner Philips	4	350000	2	Cleaning Material
5752	Folder for office 	9	350000	2	Stationary
5754	Gemfam 7x5 Propeller	2	350000	2	Parts
5756	GensAce 5000mAh 3S battery	4	350000	2	Parts
5757	Mauch 5V BEC	5	350000	2	Parts
5759	N type male to SMA female connector	1	350000	2	Parts
5760	SMA male RG58 connector	2	350000	2	Parts
5761	BNC male RG58 connector	3	350000	2	Parts
5762	2pin plugs/sockets	4	350000	2	Parts
5763	Plug & socket 16x3	5	350000	2	Parts
5764	Power cord for jammer	6	350000	2	Parts
5765	3 pin power input plug	7	350000	2	Parts
5766	non latching switches	8	350000	2	Parts
5767	DVI to DVI cable	9	350000	2	Parts
5768	Buzzer 5V	10	350000	2	Parts
5769	Buzzer 12V	11	350000	2	Parts
5770	Buzzer 24V	12	350000	2	Parts
5771	LED cover	13	350000	2	Parts
5772	power LEDs	14	350000	2	Parts
5773	Switch for LEDs	15	350000	2	Parts
5774	Push buttons	16	350000	2	Parts
5775	Cable tie pack	17	350000	2	Other
5776	Clips pack	18	350000	2	Other
5778	Boost converter 2A MT3608	20	350000	2	Parts
5779	7805 Voltage regulator	21	350000	2	Parts
5781	2pin shoe	23	350000	2	Parts
5782	1.5mm 3core 5m electrical cable	24	350000	2	Parts
5783	2.5mm 3core 5m electrical cable	25	350000	2	Parts
5784	4mm DC cable for jammer power supply 3m	26	350000	2	Parts
5787	Generator fuel	3	350000	2	POLs
5788	Electrical tape	4	350000	2	Other
5789	Double sided tape	5	350000	2	Other
5791	Faraday Cage LED light	7	350000	2	Office Equipment
5792	Arduino Nano	1	350000	2	Parts
5793	Buck converters	2	350000	2	Parts
5794	2pin plug	3	350000	2	Parts
5795	3pin plug	4	350000	2	Parts
5812	Saniplast pack	4	350000	2	Other
5813	Mineral water bottles for trials	5	350000	2	Meals/Refreshments
5815	Generator Fuel	7	350000	2	POLs
5817	Rcexl Kill Switch	1	350000	2	Parts
5818	Hobbywing UBEC 5-12V	2	350000	2	Parts
5823	19x10 propeller	3	350000	2	Parts
5828	Balsa 7m2	1	350000	2	Fabrication
5829	100gsm glass fiber 4x2.2m2	2	350000	2	Fabrication
5830	200gsm glass fiber 4 x 2.2m2	3	350000	2	Fabrication
5831	100gsm glass fiber 4x3.6m2	4	350000	2	Fabrication
5832	100gsm glass fiber 4x 2m2	5	350000	2	Fabrication
5835	CF Spars 18-16 4x 3m2	8	350000	2	Fabrication
5836	CF Spars 16-14 6x2m2	9	350000	2	Fabrication
5837	Super glue	10	350000	2	Fabrication
5838	3M double sided tape	11	350000	2	Stationary
5839	Peel ply	12	350000	2	Fabrication
5840	Paint brush set	13	350000	2	Fabrication
5841	Resin containers set	14	350000	2	Fabrication
5842	Vacuum bagging sheet	15	350000	2	Fabrication
5843	Vacuum valves	16	350000	2	Fabrication
5844	Perforated release film	17	350000	2	Fabrication
5845	Tubing 	18	350000	2	Fabrication
5891	16 yard GPS cable	9	350000	2	Parts
5893	Aluminum Sheet (1m x 2m)	1	350000	2	Fabrication
5895	OS GT ignition module	1	350000	2	Parts
5896	Break-in Propeller	2	350000	2	Parts
5897	Saito Engines CM6 Spark plug	3	350000	2	Parts
5898	Shell Advance SX 2-Stroke Oil	4	350000	2	Parts
5899	Spinner	5	350000	2	Parts
5900	Benewake TF02 LiDAR	6	350000	2	Parts
5901	mRo I2C Digitl Airspeed Sensor	7	350000	2	Parts
5902	Engine Starter	8	350000	2	Parts
5905	Silicon wire (300cm - 12 AWG)	11	350000	2	Parts
5906	XT90 connector	12	350000	2	Parts
5907	Servo extension cable	13	350000	2	Parts
5908	DC-DC buck boost converter	14	350000	2	Parts
4097	Alternator Set	5	350000	7	Parts
5909	Plywood (Sheets)	1	350000	2	Parts
5910	Pink Foam Sheets	2	350000	2	Parts
5914	Vacuum Switch	6	350000	2	Parts
5915	Sand Papers	7	350000	2	Parts
5943	nuts and bolts 5/16 x 3	7	350000	2	Parts
5947	2" and 1" scotch tape	11	350000	2	Stationary
5948	9V 1.5A Adapter for Parser	12	350000	2	Parts
5949	Bush for tracker	13	350000	2	Parts
5950	RW CD/DVDs with case	14	350000	2	Office Equipment
5951	RW CD/DVDs without case	15	350000	2	Office Equipment
5952	Box files small	16	350000	2	Stationary
5956	Bias Tee	20	350000	2	Parts
5959	Mortein refills for Ormara field trials	23	350000	2	Cleaning Material
5961	Daichi bulbs 25W	25	350000	2	Office Equipment
5965	EXIDE n100 car battery	29	350000	2	Equipment Repairs & Maintenance
5966	Greasing and diesel	30	350000	2	POLs
5968	2 pin plug/socket	2	350000	2	Parts
5976	LED Indicator - PA, Tx	10	350000	2	Parts
5980	Main Input Power Connector - Female	14	350000	2	Parts
5981	Circuit Breaker	15	350000	2	Parts
5985	MCU-WFE-NI-X3	19	350000	2	Parts
5987	profi controller connection port	21	350000	2	Parts
5991	1045 Propeller	25	350000	2	Parts
5994	33 pF capacitor	28	350000	2	Parts
5995	0.4 pF capacitor	29	350000	2	Parts
5996	2.2 uF capacitor	30	350000	2	Parts
5997	1000 pF capacitor	31	350000	2	Parts
5998	220 uF capacitor	32	350000	2	Parts
5999	2.2 uF capacitor	33	350000	2	Parts
6000	0.8 pF capacitor	34	350000	2	Parts
6001	9.1 pF capacitor	35	350000	2	Parts
6002	0.5 pF capacitor	36	350000	2	Parts
6003	0.2 pF capacitor	37	350000	2	Parts
6004	11pF  Chip Capacitor	38	350000	2	Parts
6005	10 pF  Chip Capacitor	39	350000	2	Parts
6006	130 pF  Chip Capacitor	40	350000	2	Parts
6007	3 pF  Chip Capacitor	41	350000	2	Parts
6008	3.1 pF  Chip Capacitor	42	350000	2	Parts
6009	3.2 pF  Chip Capacitor	43	350000	2	Parts
6010	1.1 pF  Chip Capacitor	44	350000	2	Parts
6011	1.2 pF  Chip Capacitor	45	350000	2	Parts
6012	1.3pF  Chip Capacitor	46	350000	2	Parts
6013	24 pF  Chip Capacitor	47	350000	2	Parts
6014	25 pF  Chip Capacitor	48	350000	2	Parts
6015	26 pF  Chip Capacitor	49	350000	2	Parts
6016	27 pF  Chip Capacitor	50	350000	2	Parts
6017	28pF  Chip Capacitor	51	350000	2	Parts
6018	29 pF  Chip Capacitor	52	350000	2	Parts
6019	0.01 microF Chip Capacitor	53	350000	2	Parts
6020	1 uF  Chip Capacitor	54	350000	2	Parts
6021	240 pF Chip Capacitor	55	350000	2	Parts
6022	2.2 microF Chip Capacitor	56	350000	2	Parts
6023	4.7 microF 50 V Chip Capacitor	57	350000	2	Parts
6024	4.5nH Inductor	58	350000	2	Parts
6025	4.6nH Inductor	59	350000	2	Parts
6026	4.7nH Inductor	60	350000	2	Parts
6027	13 nH Inductor	61	350000	2	Parts
6028	14nH Inductor	62	350000	2	Parts
6029	17 nH Inductor	63	350000	2	Parts
6030	33 nH inductor	64	350000	2	Parts
6031	17.5 nH inductor	65	350000	2	Parts
6032	8.3nH Inductor	66	350000	2	Parts
6033	8.4nH Inductor	67	350000	2	Parts
6034	8.5nH Inductor	68	350000	2	Parts
6035	6.4 nH Inductor	69	350000	2	Parts
6036	6.5 nH Inductor	70	350000	2	Parts
6037	6.6 nH Inductor	71	350000	2	Parts
6038	75 ohm	72	350000	2	Parts
6039	500 ohm	73	350000	2	Parts
6040	470 ohm	74	350000	2	Parts
6041	39 ohm	75	350000	2	Parts
6042	51 ohm Chip Resistor	76	350000	2	Parts
6043	30 ohm, 6 A Ferrite Bead	77	350000	2	Parts
6044	Transistor	78	350000	2	Parts
6045	PCB substrate	79	350000	2	Parts
6047	solder paste	81	350000	2	Fabrication
6048	Amplifier IC	82	350000	2	Parts
6051	Selector switch with box	85	350000	2	Parts
6052	Electric wire 2 core (Load O/P)	86	350000	2	Parts
4109	Composite Composite (Slow) (200 ml)	12	350000	2	Other
4110	Composite Adhesive (Fast) (200 ml)	13	350000	2	Other
4111	Peel Ply	14	350000	2	Other
4112	Depoxy Adhesive (50 ml)	15	350000	2	Other
4113	Aluminum Sheet (500*500*3mm)	16	350000	2	Other
4114	Stainless Steel Fasteners (M12, M8, M5, M3, M2 4 units each	17	350000	2	Other
4115	Epoxy Resin (Pack of 1.8 kg)	18	350000	2	Other
4132	GensAce 2200mAh 2S battery	4	350000	2	Other
4136	22, 20, 18, 16 AWG wire set	8	350000	2	Other
4137	XT60, 3pin locking, DF13, JST connectors	9	350000	2	Other
4139	Male headers	11	350000	2	Other
4150	Kevlar For Fuel Tank	22	350000	2	Other
4151	Epoxy Resin	23	350000	2	Other
4152	Heat Resistant Resin	24	350000	2	Other
4154	Acrylic Tank	26	350000	2	Other
4160	Arduino Uno	32	350000	2	Other
4161	Arduino Nano	33	350000	2	Parts
4164	Single Sided PCB sheet	36	350000	2	Other
4166	CF UD (250 gm UD)	1	350000	2	Other
4167	Peel Ply(180gsm)	2	350000	2	Other
4168	Self-Clenching Standoffs(m3, pack of 200)	3	350000	2	Other
4319	Component blocks	1	350000	2	Other
4169	Epoxy Resin (Chemcoats Epoxy Resin)	4	350000	2	Other
4170	Bonding Adhesive(2k epoxy based bonding adhesive 400ml)	5	350000	2	Other
4171	Machining Of Ribs & Bulkheads	6	350000	2	Machinery/Equipment
4172	Machining of Pattern(High Density Fiber Board 4ft*8ft*12mm)	7	350000	2	Machinery/Equipment
4173	Bits for Ribs Machining (0.8mm)	8	350000	2	Other
4174	Paint(Auto Paint)	9	350000	2	Other
4175	Sand Paper(Different Grits)	10	350000	2	Cleaning Material
4176	Primer(Oil Based)	11	350000	2	POLs
4177	Polycate(1kg)	12	350000	2	Other
4178	Fasteners(Different Sizes)	13	350000	2	Parts
4181	Plastic sheet	16	350000	2	Other
4182	Wax(Honey wax)	17	350000	2	Other
4183	Thinner	18	350000	2	Other
4184	3m double sided tape(33m roll)	19	350000	2	Other
4185	Breather(Tissue Rolls)	20	350000	2	Other
4186	Balsa(For Leading & Trailing Edges)	21	350000	2	Other
4187	Polyester Resin (For Mold Making)	22	350000	2	Other
8367	ALLIGATOR CLIPS	29	450000	2	Other
4188	Matt Fiber (For Mold Making)	23	350000	2	Other
4189	Gel Coat (For Mold Making)	24	350000	2	Other
4190	Pigment(For Aircraft Colouring)	25	350000	2	Other
4192	FR4 sheet (For Test Structure)	27	350000	2	Other
4193	Vacuum Line(For Line Replacement of Vacuum Pump)	28	350000	2	Other
4274	Adapter N(F) - N(M)	12	350000	2	Other
4275	Adapter N(M) - SMA (F)	13	350000	2	Other
4276	Adapter N(F) - SMA (M)	14	350000	2	Other
4277	Adapter N(M) - SMA (M)	15	350000	2	Other
4278	Adapter N(F) - SMA (F)	16	350000	2	Other
4279	RG142 N(M) - N(M) 2 ft.	17	350000	2	Other
4280	RG142 N(M) - SMA(M) 1ft	18	350000	2	Other
4281	RG142 SMA (M) - SMA(M) 1ft	19	350000	2	Other
4282	Resistor SMD 33k	20	350000	2	Other
4283	Resistor SMD 100k	21	350000	2	Other
4284	Resistor SMD 150k	22	350000	2	Other
4285	Resistor SMD 5.1k	23	350000	2	Other
4286	Resistor SMD 10k	24	350000	2	Other
4287	Resistor SMD 330ohm	25	350000	2	Other
4288	Resistor SMD 510k	26	350000	2	Other
4289	Resistor SMD 1k	27	350000	2	Other
4290	Capacitor SMD 470pF	28	350000	2	Other
4291	Capacitor SMD 330pF	29	350000	2	Other
4292	Capacitor SMD 6.8nF	30	350000	2	Other
4293	Capacitor SMD 10nF	31	350000	2	Other
4294	Capacitor SMD 0.1uF	32	350000	2	Other
4295	Capacitor SMD 100pF	33	350000	2	Other
4296	Capacitor SMD 10pF	34	350000	2	Other
4297	Capacitor SMD 1nF	35	350000	2	Other
4298	Capacitor SMD E 10uF	36	350000	2	Other
6141	Safety shoes	32	350000	7	Other
4299	Capacitor SMD E 22uF	37	350000	2	Other
4300	LM324 SMD	38	350000	2	Other
4301	DC Jack	39	350000	2	Other
4302	Adapter 12V	40	350000	2	Other
4303	Headers	41	350000	2	Other
4304	3.3V Regulator IC (AMS1117)	42	350000	2	Other
4305	5V Regulator IC (LT1085)	43	350000	2	Parts
4306	Trimmer 1k	44	350000	2	Other
4307	Trimmer 5k	45	350000	2	Other
4308	RF PLL Frequency Synthesizer IC	46	350000	2	Parts
4310	Jumpers (male & Female)	48	350000	2	Other
4311	RF Pre-amplifier IC BLP8G27-10Z	49	350000	2	Parts
4312	Heat sink Cu Billet	50	350000	2	Other
4315	capacitor TANT 10uF 10V 1206	53	350000	2	Other
4316	Capacitor CER 0.9pF 250C 0805	54	350000	2	Other
4317	Capacitor CER 2.4pF 250V 0805	55	350000	2	Other
4318	Phase Detector IC AD8302	56	350000	2	Parts
4321	Aluminum disk Al 6061 T4 (24mm radius, 10mm thickness)	3	350000	2	Other
4322	Aluminum sheet Al 6061 T4 (wall dimensions: 86.36x43.18mm, sheet thickness \n2mm & 125mm length)	4	350000	2	Other
4327	Analog Phase Shifter IC (local) - HMC928LP5E	1	350000	2	Parts
6085	PCB fiber board	6	350000	2	Parts
6086	Printer toner	7	350000	2	Other
6087	AA Cells	8	350000	2	Parts
6088	Capacitors mix	9	350000	2	Parts
6089	Ultrasonic couplant and Curve	10	350000	2	Parts
6090	Data package for Python Library	11	350000	2	Other
6125	8.9nH Inductor	16	350000	2	Parts
6126	1.65nH Inductor	17	350000	2	Parts
6127	16.6nH Inductor	18	350000	2	Parts
6128	2.55nH Inductor	19	350000	2	Parts
6129	51ohms Thick Film resistor	20	350000	2	Parts
6130	1.5 OHM Thick Film resistor	21	350000	2	Parts
6137	1045 CF Propeller pair	28	350000	2	Parts
6138	Gens Ace 5000 mAh 3S LiPo Battery	29	350000	2	Parts
6139	X8R Receiver antennae	30	350000	2	Parts
6143	Dustbin	34	350000	2	Other
6145	2 pin plug/socket	36	350000	2	Parts
6146	Antenna RF Ports	37	350000	2	Parts
6147	Power Switch AC-5A	38	350000	2	Parts
6148	Main Power Switch 220V-3A	39	350000	2	Parts
6149	Power Socket 220V-3A	40	350000	2	Parts
6150	AC-5A Switches	41	350000	2	Parts
6151	LED Indicator (5A)	42	350000	2	Parts
6154	Power Connector - Female	45	350000	2	Parts
6155	Circuit Breaker 220V-16A	46	350000	2	Parts
6199	XT60 connectors	27	350000	2	Parts
6200	Wire mesh 6mm	28	350000	2	Parts
6201	wire mesh 15mm	29	350000	2	Parts
6202	12 AWG silicon wire (red)	30	350000	2	Parts
6203	12 AWG silicon wire (black)	31	350000	2	Parts
6204	14 AWG silicon wire (red)	32	350000	2	Parts
6205	14 AWG silicon wire (black)	33	350000	2	Parts
8069	RS485 to usb	3	350000	2	Other
6206	16 AWG silicon wire (red)	34	350000	2	Parts
6207	16 AWG silicon wire (black)	35	350000	2	Parts
6208	22 AWG silicon wire (red)	36	350000	2	Parts
6209	24 AWG silicon wire (black)	37	350000	2	Parts
6210	24 AWG silicon wire (red)	38	350000	2	Parts
6211	L298N	39	350000	2	Parts
6212	Servo Extension wire	40	350000	2	Parts
6213	10A UBEC	41	350000	2	Parts
6214	Electrical Connectors	42	350000	2	Parts
6215	Push buttons	43	350000	2	Parts
6218	Silicon wire 4m	46	350000	2	Parts
6223	Assorted Heat Shrink (Box)	5	350000	2	Other
6227	Soldering Iron Tips	9	350000	2	Parts
6230	Matt fiber (300 gsm) - 25m2	1	350000	2	Fabrication
6231	wax box	2	350000	2	Fabrication
6232	Grey pigment box	3	350000	2	Fabrication
6266	CF propellers 1045	37	350000	2	Parts
6275	SMA connectors	9	350000	2	Parts
6276	bonding adhesive	10	350000	2	Fabrication
6282	Lux connector for profi	16	350000	2	Parts
8500	Consumable Item for Power Lab	1	450000	2	Other
7421	Industrial PCS Ci3 8GB, 2TB HDD, 08 Serials ports, 02 DVI/HDMI/VGA	1	200000	7	Machinery / Equipment
8300	IC 7404	2	450000	2	Other
8301	IC 7408	3	450000	2	Other
8302	Soldering Iron	4	450000	2	Other
8303	Soldering Wire 70/30	5	450000	2	Other
8304	Sleeve 1mm,3mm,5mm,10mm(10 Meter Each)	6	450000	2	Other
8305	Soldering Paste	7	450000	2	Other
8306	LEDs Red, Green(150 Each)	8	450000	2	Other
8309	DIP Switch (8 pin pack)	9	450000	2	Other
8314	IDC Connector (50 Holes for Flate Cable)	10	450000	2	Other
8318	Push botton (small)	11	450000	2	Other
8320	Resistor (MJE 3055T)	12	450000	2	Other
8327	Resistor (2907A)	13	450000	2	Other
8329	TIP 110 Resistor	14	450000	2	Other
8333	BJT Transistor 2SC828/2SC829	16	450000	2	Other
8337	100 MicroF,103pf, 16V/25V(20 Each)	17	450000	2	Other
8356	ZENER DIODE (12 V,2 W)	24	450000	2	Other
8358	ZENER DIODE (18V,2W)	25	450000	2	Other
8361	DIAC (20-60V)	26	450000	2	Other
8362	Glass Fuses	27	450000	2	Other
8366	LED Large (Red,Yellow,Green)	28	450000	2	Other
8369	PCB BoRD 6"x10"	30	450000	2	Other
6286	RG59 Patch cord BNC to BNC 	2	350000	2	Parts
6288	Double Sided Tape & Paper tape	4	350000	2	Other
6289	Glossy paper	5	350000	2	Stationary
6291	Brass pipes 7.5m	7	350000	2	Fabrication
6295	5mm/8mm glass slab	11	350000	2	Fabrication
6296	Sticker Sheets	12	350000	2	Stationary
6297	RG58 SMA male with crimping	13	350000	2	Parts
6298	SMA female to female adapter	14	350000	2	Parts
6299	N Type female chassis mount clamp type	15	350000	2	Parts
6300	Spray paints	16	350000	2	Fabrication
6301	Drill bits	17	350000	2	Fabrication
6302	Heat paste	18	350000	2	Fabrication
6305	SMD capacitors for PA PCB	2	350000	2	Parts
6306	16m 1.5mm wire for 10W Power Amplifier	3	350000	2	Parts
6307	2m Patch cord N male to RG142 male 	4	350000	2	Parts
6308	1m Patch cord SMA male to RG142 male	5	350000	2	Parts
6309	MT60 Connectors	6	350000	2	Parts
6310	XT90 connectors	7	350000	2	Parts
6311	Connection Lux	8	350000	2	Parts
6312	Cat6 Ethernet Cable local including connectors 	9	350000	2	Parts
6313	SS Nut/Bolts (6,8,12 mm)	10	350000	2	Parts
6316	Arduino Nano, Buzzer, Arduino jumpers, connectors, PCB fiber board (for temperature buzzer cct)	13	350000	2	Parts
6317	USB to RS232 Cable, Arduino Nano with power cable, RS2232 to TTL converter, Breadboard, Max232 IC Voltage Regulators, DB9 converter (for parsing circuit)	14	350000	2	Parts
6318	Copper brass mix rod	1	350000	2	Fabrication
6319	Welding Rods	2	350000	2	Fabrication
6321	N type connectors 	4	350000	2	Parts
6322	SMD Capacitors mix	5	350000	2	Parts
6328	Electric Tape	2	350000	2	Other
6329	Velcro strip roll	3	350000	2	Other
6331	4ft (1.2m) Cat6a Snagless Shielded (SFTP) PVC Ethernet Network Patch Cable	2	350000	2	Parts
6373	Cable Ties Pack (6, 8 and 10 inch)	16	350000	2	Other
6375	N Type Female to Female connectors	18	350000	2	Parts
6378	Fuel for Generator	21	350000	2	POLs
2274	CONN RCPT 2POS 0.059 TIN PCB	3	200000	2	Parts
2275	CLICKMATE 2 CIRCUIT 600MM PCB	4	200000	2	Parts
2276	CONN RCPT 8POS 0.059 TIN PCB	5	200000	2	Parts
2277	CLICKMATE 8 CIRCUIT 600MM PCB	6	200000	2	Parts
8331	RJ 45 Cable Box	15	450000	2	Other
2279	16Pin molex connector FeMale	8	200000	2	Parts
2280	2Pin molex connector Male	9	200000	2	Parts
2281	2Pin molex connector FeMale	10	200000	2	Parts
2282	SMA, N Type BNC Adapters	11	200000	2	Parts
2314	Hypertherm 65 & 85 Replace. Ohmic-Sensing Retaining Cap Mech - 220953	1	350000	2	Other
2315	Hypertherm Swirl Ring 45-85 Amps - 220857	2	350000	2	Other
2316	Hypertherm 65 and 85 Replacement FineCut Ohmic Shield - 220948	3	350000	2	Other
2317	Hypertherm 85 Replacement 85A Nozzle, 5/pk - 220816	4	350000	2	Other
2318	Hypertherm CopperPlus Electrode, 5/pk -  220777	5	350000	2	Other
2319	Hypertherm 45XP 65 and 85 FineCut Nozzle, 5/pk -  220930	6	350000	2	Other
2320	Hypertherm 65 and 85 Mechanized Shield -  220817	7	350000	2	Other
2321	Hypertherm 65 and 85 Replacement Electrode, 5/pk -  220842	8	350000	2	Other
2322	100 Gram Paper Rim	1	350000	2	Other
2323	Ink Cartirdge Filling (Black)	2	350000	2	Other
2324	Hard File Cover	3	350000	2	Other
2325	Uniball pointer	4	350000	2	Other
2326	Separators	5	350000	2	Other
2327	Posters	6	350000	2	Other
2328	Rubber seals for control panel	7	350000	2	Other
2331	Burnt resistors of DA VFD	9	350000	2	Other
2332	Jazz Internet recharge INTELL	10	350000	2	Other
2333	Internet charges at SPF	11	350000	2	Other
2334	Selector Switch for VFD	12	350000	2	Other
2336	Diesel generator Fuel	14	350000	2	Chemicals
2344	CSD1412-1600 Ferrite Disk	1	350000	2	Parts
2345	CSD2015-1600 Ferrite Disk	2	350000	2	Parts
2346	Surface Mount RF Transformer	3	350000	2	Parts
2350	DC BLOCK / SMA-F / SMA-M / RoHS	6	350000	2	Parts
2352	Mosfet N-CH 80V 82A LFPAK56	1	350000	2	Parts
2353	Conn Adapt Jack-Jack SMA 50 OHM	2	350000	2	Parts
2354	Conn Adapt Plug-Plug SMA 50 OHM	3	350000	2	Parts
2355	18 GHz Type N Jack to Jack Low p	4	350000	2	Parts
2356	18 GHz Type N Plug to Plug Low P	5	350000	2	Parts
2357	RF N-Type Connector Jack Female Socket	6	350000	2	Parts
2358	Passive Electronic components for power supply X-Band Modulator	7	350000	2	Parts
2359	PCB Printing for power Divider	8	350000	2	Parts
2361	Aluminum (1000 Series)	10	350000	2	Parts
2362	A-36 Carbon Steel	11	350000	2	Parts
2363	First Aid Box items for field trials	12	350000	2	Other
2366	Jazz Internet recharge RF Lab	15	350000	2	Other
2367	Power supplies repair	16	350000	2	Parts
2369	4 Pin connector automotive	1	350000	2	Other
2370	5 Pin connector automotive	2	350000	2	Other
2371	SSR 40A relay	3	350000	2	Other
2372	Thermal Tape	4	350000	2	Other
2373	M4 Rivnuts	5	350000	2	Other
2374	M4X10mm button head HEX screws	6	350000	2	Other
2375	M3X65mm button head HEX screws	7	350000	2	Other
2376	M3X60mm button head HEX screws	8	350000	2	Other
2377	M3X60mm button head HEX screws	9	350000	2	Other
2378	M3X8mm button head HEX screws	10	350000	2	Other
2379	Wire Gland PG9	11	350000	2	Other
2380	Wire Gland PG11	12	350000	2	Other
2381	Heat Sink aluminum	13	350000	2	Other
2382	Exhaust Fan (120mm)	14	350000	2	Other
2383	3 Pin Industrial plug	15	350000	2	Other
2384	Circuit Breaker	16	350000	2	Other
2385	Panel mount connectors (7pin)	17	350000	2	Other
2386	RJ45 connector panel mount	18	350000	2	Other
2387	USB Panel mount connectors	19	350000	2	Other
2389	HDMI Panel mount connectors	20	350000	2	Other
2392	p1000 inner cone	23	350000	2	Other
2393	PCB for launcher electronics	24	350000	2	Other
2411	Ribbon cales in foot	4	200000	2	Parts
2412	Ribbon connectors	5	200000	2	Parts
2413	Rack power rail 4 way	6	200000	2	Parts
2415	HDMI cable 3m	8	200000	2	Parts
2417	VGA cable 3m	9	200000	2	Parts
2418	Power supply cable 3m	10	200000	2	Parts
2419	SATA cables	11	200000	2	Parts
2421	Fitting netbolts ss in DOZ	13	200000	2	Parts
2424	Compact Disk CD RW pkt	16	200000	2	Parts
2425	Fitting Accessories	17	200000	2	Parts
2429	XK LA2018T4010-4MH Frequency (GHz) 2-18 Ghz Gain (db) +/- 1.75 Noise Figure 1.2 Max Input VSWR 2:1 Max Output VSWR 2:1 Output PIDB (dbm) +10dbm Biasing Voltage (V) +12V Interface Connection SMA Female at both ends Impedance Matching 50 Ohm Case 4HM	1	200000	2	Parts
2430	XK LA8018T4215-81 Frequency (GHz) 8-18 Ghz Gain (db) +/- 1.75 Noise Figure 1.2 Max Input VSWR 2:1 Max Output VSWR 2:1 Output PIDB (dbm) +15dbm Biasing Voltage (V) +12V Interface Connection SMA Female at both ends Impedance Matching 50 Ohm Case 81	2	200000	2	Parts
2439	Spare Element & Bits	9	350000	2	Other
2441	Soldering Iron Cleaning Sponge	11	350000	2	Other
2443	Hot melt Glue Sticks	13	350000	2	Parts
2444	Chloroform	14	350000	2	Chemicals
2445	Isopropanol	15	350000	2	Chemicals
2434	Spare Boxes XL	4	350000	2	Other
2446	Ceramic Heater Cartridge	16	350000	2	Parts
2447	NTC 100K Thermistor	17	350000	2	Other
2448	Cleaning brush	18	350000	2	Other
2449	Lubricant Oil	19	350000	2	Chemicals
2451	Silicon grease	20	350000	2	Chemicals
2456	Composite Nail 1" (Pack of 2300)	25	350000	2	Other
2457	Composite Nail 2" (Pack of 2300)	26	350000	2	Other
2460	Carbide drill bit set (Pack of 5)	28	350000	2	Other
2463	WD40	31	350000	2	Chemicals
2464	Thinner	32	350000	2	Chemicals
2465	Cleaning cloth	33	350000	2	Other
2467	Powder coating GSP full scale	35	350000	2	Chemicals
2469	Optical sensor	37	350000	2	Other
2470	PCB	38	350000	2	Parts
2472	MFD sheets	40	350000	2	Other
2473	Connectors	41	350000	2	Other
2475	Miscellaneous (printer cartridges x 04 and paper rim x 06)	43	350000	2	Other
2478	Copper heat sinks material	46	350000	2	Parts
2479	3 phase breaker	47	350000	2	Other
2480	Aluminum raw material	48	350000	2	Other
2481	GSP mast material	49	350000	2	Other
2482	Launcher Rail SS 304 sheet (8' x 4') 6mm	1	350000	2	Parts
2483	Marlin Batteries	2	350000	2	Other
2484	MDF Sheets	3	350000	2	Other
2485	Bonding Adhesive for sheet	4	350000	2	Other
2486	Polyester resin	5	350000	2	Other
2487	Matt Fiber	6	350000	2	Other
2488	Gel coat	7	350000	2	Other
2489	Epoxy resin	8	350000	2	Other
2490	Fiber glass woven	9	350000	2	Other
2491	Mixing cups	10	350000	2	Other
2492	Primer	11	350000	2	Other
2493	Marlin paint, stand & cover	12	350000	2	Other
2496	H-13 Dye Material for compund mould	1	350000	2	Chemicals
2433	Spare Boxes Large	3	350000	2	Other
2498	Acetylene cylinder refill	3	350000	2	Chemicals
2499	Oxygen cylinder refill	4	350000	2	Chemicals
2500	Argon Gas Refill	5	350000	2	Chemicals
2501	Capacitors for matching network	6	350000	2	Parts
2502	Resistors for 100W RF PA	7	350000	2	Parts
2503	RF Connectors	8	350000	2	Parts
6837	Cleaning gear (Cleaning liquid, mosquito repellent, tissue & air freshener)	6	350000	2	Other
7847	Sheet metal ( front sheet,single sheet, Laser cutting)	1	350000	2	Other
7848	Powder coating	2	350000	2	Other
7849	Back plate	3	350000	2	Other
7850	Wheels	4	350000	2	Other
7851	Mil Spec Connector (19 & 4 pin)	5	350000	2	Other
7852	Circuit breaker	6	350000	2	Other
7853	Industrial power plug	7	350000	2	Other
7854	Fans (AC, 4 inch, w/grill)	8	350000	2	Other
7855	HDMI cable	9	350000	2	Other
7856	Power cable 1.5mm, 3 core	10	350000	2	Other
7857	EDB (electronics Distribution box)	11	350000	2	Other
7858	Power cable 2.5mm, 3 core	12	350000	2	Other
7859	Spiral sleeve	13	350000	2	Other
7860	Cable channeling	14	350000	2	Other
7433	D38999/26WB98SN-US Back Shell of 8D511W98SN	5	200000	7	Parts
7861	Nuts + Bolts + Washer	15	350000	2	Other
7862	Heat Shrinks	16	350000	2	Other
7863	Sheet metal	17	350000	2	Other
7864	Solder Wire	18	350000	2	Other
7865	black spray paint	19	350000	2	Other
7866	DC wire	20	350000	2	Other
7867	Double AA battery	21	350000	2	Other
7868	Teflon rod for spacer	22	350000	2	Other
5428	VTx to Antenna Cable (RG-142 4m)	1	350000	2	Other
7902	matt fiber (300 gsm) m2	1	350000	2	Other
7903	Wax box	2	350000	2	Other
7904	pigment (white)	3	350000	2	Other
7905	gel coat (kg)	4	350000	2	Other
7906	Polyester (2 parts) (kg)	5	350000	2	Other
7907	thinner (ltr)	6	350000	2	Other
7908	Crushed Fiber (kg)	7	350000	2	Other
7909	Waxing Cloth	8	350000	2	Other
7910	Brushes	9	350000	2	Other
7911	Nitrile Gloves (box of 100)	10	350000	2	Other
7912	Nuts & Bolts	11	350000	2	Other
7913	Rubber gasket	12	350000	2	Other
7914	Silicon tubes	13	350000	2	Other
7915	White paint	14	350000	2	Other
7918	Sanitization material (Mortein spray, Phenyl, Dettol surface cleaner)	17	350000	2	Cleaning Material
7921	Box files	20	350000	2	Stationary
7946	Electric Light Plug 13Amp (double Plug)	1	350000	2	Building Modification
7947	Electric power Plug 16Amp (Single Plug)	2	350000	2	Building Modification
5644	LMR 400 (1ft) N(M) - N(M) Digi-Key Part Number: ARF3008-ND	1	350000	2	Parts
5645	RG-142 (1ft) N(M) - SMA(M) Digi-Key Part Number: J6912-ND	2	350000	2	Parts
6825	Adapter Coaxial Connector N(F)-N(F) Digi-Key Part Number: ACX2139-ND	3	350000	2	Parts
6826	LMR 400 (3m) N(F)-N(F) Digi-Key Part Number: 095-909-173-120-ND	4	350000	2	Parts
6827	RG-58 SMA (M) Connector N(F)-N(F) Digi-Key Part Number: 2368-06-N002-ND	5	350000	2	Parts
6828	RG-58 SMA (M) Connector N(F)-N(F) Digi-Key Part Number: A112879-ND	6	350000	2	Parts
6829	Attenuator SMA 7dB Digi-Key Part Number: ARF2707-ND	7	350000	2	Parts
6830	Attenuator SMA 10dB Digi-Key Part Number: ARF2710-ND	8	350000	2	Parts
6831	SFP-10G-T (10G Copper to SFP + Adapters) Generic Compatible 10GBASE-T SFP + Copper RJ-45 30m Transceiver Module	9	350000	2	Parts
5598	10G NIC Card Intel X540-T2	1	350000	2	Other
5599	1284-1016-1-ND Ceramic capacitor 100pF	2	350000	2	Parts
5600	1284-1026-1-ND Ceramic capacitor 1pF	3	350000	2	Parts
5601	1284-1042-1-ND Ceramic capacitor 2pF	4	350000	2	Parts
5602	1284-1046-1-ND Ceramic capacitor 2.7pF	5	350000	2	Parts
5603	1284-1351-1-ND Ceramic capacitor 0.8pF	6	350000	2	Parts
5605	YAG1327CT-ND SMD Jumper	7	350000	2	Parts
5606	311-169CRCT-ND Resistance SMD 169ohm	8	350000	2	Parts
5608	311-150CRCT-ND Resistance SMD 150ohm	9	350000	2	Parts
5609	311-165CRCT-ND Resistance SMD 165ohm	10	350000	2	Parts
5610	13-RT0805FRE07140RLCT-ND Resistance SMD 140ohm	11	350000	2	Parts
5611	13-RT0805FRE07490R9LCT-ND Resistance SMD 49.9ohm	12	350000	2	Parts
5612	931-1175 Connector SMA jack STR 50ohm Edge Mount	13	350000	2	Parts
5613	478-12145-1-ND Capacitor Ceramic 10uF	14	350000	2	Parts
5614	ACX1445-ND Connector Adaptor Jack-Jack N 50ohm	15	350000	2	Parts
5615	ACX1169-ND Connector Adapter Jack-Jack N 50ohm	16	350000	2	Parts
5616	1946-1034-ND Connector N plug straight crimp	17	350000	2	Parts
5589	Adapter coaxial connector N(F)-N(F) Digikey ACX2139-ND	1	350000	2	Parts
5590	744-1262-ND RF Attenuator 7dB SMA	2	350000	2	Parts
5591	744-1265-ND RF Attenuator 10dB SMA	3	350000	2	Parts
5592	SPF-10G-T adapters Generic compatible 10Gbase-T-SFP Copper RJ45 39m	4	350000	2	Parts
5641	LMR 400 LLPX connector	1	350000	2	Parts
5642	N(F) - N(F) adapters ACX1169-ND	2	350000	2	Parts
5643	N(F) - N(F) adapters ACX1445-ND	3	350000	2	Parts
7119	2 pin plug/socket	12	350000	2	Parts
7120	USB Port	13	350000	2	Parts
7121	Ethernet Port	14	350000	2	Parts
7122	Antenna RF Port (GPS/Tele)	15	350000	2	Parts
7123	MCU Power Switch (AC-5A)	16	350000	2	Parts
7124	Main Power Switch (220V-3A)	17	350000	2	Parts
7125	Power Socket (220V-3A)	18	350000	2	Parts
7126	PA Switch (AC-5A)	19	350000	2	Parts
7127	Tx  Switch (AC-5A)	20	350000	2	Parts
7128	5A LED indicator	21	350000	2	Parts
7129	Cooling Fans + Brackets (6 inch)	22	350000	2	Parts
7130	Cooling Fans + Brackets (4 inch)	23	350000	2	Parts
7131	Main Input Power Connector - Female	24	350000	2	Parts
7132	Circuit Breaker (220V-16A)	25	350000	2	Parts
7136	Main Power Cable 2.5mm (3 core, Pakistan cable)	29	350000	2	Parts
7137	MCU-Power Supply (960mm)	30	350000	2	Parts
7138	MCU-USB (front panel)  (900mm)	31	350000	2	Parts
7139	MCU-Ethernet Port (900mm)	32	350000	2	Parts
7140	MCU-X310 cable (1200mm rugged Cat7)	33	350000	2	Parts
7141	Power Supply - 100W PA I/P 220 VAC: 900 mm O/P DC: 1800mm	34	350000	2	Parts
7142	Power Supply - 30W PA I/P 220 VAC: 900 mm O/P DC: 1800mm	35	350000	2	Parts
5530	15U Network Rack	1	350000	2	Parts
5531	Full rack track	2	350000	2	Parts
5532	Cantilever tray	3	350000	2	Parts
5534	Fiber glass (m2)	4	350000	2	Parts
5535	Epoxy resin (set of 1.8 kg)	5	350000	2	Parts
5540	Rubber Dampers	10	350000	2	Parts
5541	Wood work	11	350000	2	Other
5542	Filler material (per piece)	12	350000	2	Other
5543	Paint, Primer	13	350000	2	Other
5545	Brushes and Waxing Cloth	15	350000	2	Other
5546	Sand Paper (per piece)	16	350000	2	Other
5547	Wax	17	350000	2	Other
5548	Wood	18	350000	2	Other
5550	Filler and Primer	20	350000	2	Other
5551	Clamps	21	350000	2	Parts
7030	YT-RJ45-JSX-16-001 Connectors	1	350000	2	Parts
7031	IP67 waterproof connectors	2	350000	2	Parts
7042	Jammer button PCB manufacturing	13	350000	2	Parts
7043	Arduino Nano	14	350000	2	Parts
7044	2pin JST XH Connector	15	350000	2	Parts
7045	Aviation Connector GX16-6	16	350000	2	Parts
7047	Arduino Uno Cable	18	350000	2	Parts
7048	USB Mini B Connector	19	350000	2	Parts
7049	Mounting screws (Top to bottom cover crew) + nuts + washers	20	350000	2	Parts
7080	Side Mounting screws	21	350000	2	Parts
7081	PCB Mounting screws	22	350000	2	Parts
7014	Matt fiber (300 gsm) - 25m2	8	350000	2	Parts
7015	Wax box	9	350000	2	Other
7016	Grey pigment box	10	350000	2	Other
7017	Polyester (2 parts) - 26kg	11	350000	2	Parts
7018	Thinner - 2 ltr	12	350000	2	Chemicals
7019	Wooden frame piece	13	350000	2	Other
7020	Polycate box	14	350000	2	Other
7021	Waxing cloth	15	350000	2	Other
7022	Plywood sheet	16	350000	2	Other
7023	Brushes	17	350000	2	Other
7024	Nitrile gloves pack	18	350000	2	Other
7025	Grey paint	19	350000	2	Chemicals
7026	Latches	20	350000	2	Parts
7027	Handles	21	350000	2	Parts
7028	Dampers	22	350000	2	Parts
7029	Nuts and bolts	23	350000	2	Parts
5619	Brass boom 12.5mmx12.5mmx1100mm	1	350000	2	Parts
5620	Brass elements multiple sizes	2	350000	2	Parts
5621	Spacers Teflon 10mmx30mmx4mm block	3	350000	2	Parts
5622	Brass room 16mm hollow tube with wall thickness of 3mm and length 608mm	4	350000	2	Parts
5623	Brass element L =79mm	5	350000	2	Parts
5624	Brass element L =50mm	6	350000	2	Parts
5625	Brass element L =45mm	7	350000	2	Parts
5626	Brass/SS connector sleeve (20mm hollow tube with wall thickness of 2mm and length 26mm)	8	350000	2	Parts
5627	Teflon (28mm hollow tube with wall thickness of 6mm and length 25mm)	9	350000	2	Parts
5628	Teflon (28mm hollow tube with wall thickness of 6mm and length 25mm)	10	350000	2	Parts
5629	Spiral wire 6mm x 20 ft	11	350000	2	Parts
5630	Spiral wire 8mm x 20 ft	12	350000	2	Parts
5631	Knife/blade set	13	350000	2	Other
5632	Nuts & bolts 4/15mm (100pcs box)	14	350000	2	Parts
5633	Nuts & bolts 3/10mm (100pcs box)	15	350000	2	Parts
5634	Nuts & bolts 5/40mm (100pcs box)	16	350000	2	Parts
5635	Nuts & bolts 6/30mm (100pcs box)	17	350000	2	Parts
5636	Packing tape	18	350000	2	Other
5637	Electrical tape set	19	350000	2	Other
5638	Heat sleeve pack	20	350000	2	Other
5639	2core wire 23/76mm dia (10mm)	21	350000	2	Parts
5640	Antenna stand arm	22	350000	2	Parts
7248	Black Spray paint	4	350000	2	Chemicals
7265	Ethernet cable cat7	21	350000	2	Parts
7266	Ethernet cable cat6	22	350000	2	Parts
7329	Heat sleeve pack	37	350000	2	Other
7330	Cable Raceway 10ft	38	350000	2	Parts
7331	Grinder disc	39	350000	2	Other
7332	Cutting disc	40	350000	2	Other
7333	Soldering wire spool	41	350000	2	Parts
7334	Teflon rod (for spacer) (12mmx4ft)	42	350000	2	Parts
7434	D38999/26WD19SN-US Back shell of 8D515F19SN	6	200000	7	Parts
7335	Teflon rod (for spacer) (10mmx4ft)	43	350000	2	Parts
7336	Push Button	44	350000	2	Parts
7337	Arduino Nano	45	350000	2	Parts
7338	Famale header strips	46	350000	2	Parts
7339	GX-16 2pin connector	47	350000	2	Parts
7340	Female usb to mini B cable	48	350000	2	Parts
7341	JST-xh 2pin wire	49	350000	2	Parts
7342	Male header strip	50	350000	2	Parts
7345	Matt fiber 300gsm m2	53	350000	2	Parts
7346	Wax box	54	350000	2	Other
7347	Grey pigment box	55	350000	2	Other
7348	Gel coat kg	56	350000	2	Other
7349	Polyester (2 parts) kg	57	350000	2	Parts
7350	Thinner ltr	58	350000	2	Chemicals
7353	Polycate box	60	350000	2	Other
7355	Waxing cloth	61	350000	2	Other
7357	Brushes	62	350000	2	Other
6843	SCR relay for jammer Batch 3	1	350000	2	Parts
6844	SSR modules for jammers AC	2	350000	2	Parts
6845	SSR modules for jammers DC	3	350000	2	Parts
6846	PCB Sheets incl manufacturing cost	4	350000	2	Other
6848	Components for jammer button PCB (SSR power supply, 0.75mm wiring, spiral sleeves, on/off switches & button) - 4 sets	6	350000	2	Other
6849	DPDT Switches for Jammers	7	350000	2	Parts
6851	Cleaning gear (soap, phenyl, detol)	9	350000	2	Other
6854	Raw material for antenna (brass rods, brass boom, paint for coating) - 02 yagi & 02 LPDA	12	350000	2	Other
7185	Black spray paint	16	350000	2	Chemicals
7193	Momentory switches 16mm	20	350000	2	Parts
7206	Ethernet cable cat7	28	350000	2	Parts
7208	Ethernet cable cat6	29	350000	2	Parts
7232	Stationery for facility	38	350000	2	Other
6875	LMR900 connectors	4	350000	2	Parts
6876	Jammer switch box, handle, spiral & heat sleeves for upkeep of wiring, paint, silicon pipe, fiber sheet glands for upkeep	5	350000	2	Other
5484	LMR-400 LLPX (3m)	1	350000	2	Parts
5485	N Type Connectors	2	350000	2	Parts
5489	Solder Pb94B	5	350000	2	Other
6893	PVC Pipe structure	2	350000	2	Other
6894	Plastic Sheet	3	350000	2	Other
6895	PLA-CF	4	350000	2	Other
6896	Nylon-CF	5	350000	2	Other
6897	E3D SS Nozzle	6	350000	2	Other
6898	E3D HS Nozzle	7	350000	2	Other
6900	CDs	9	350000	2	Other
6902	Opto isolators	11	350000	2	Other
6903	SSR	12	350000	2	Other
6904	Printer repair Refill	13	350000	2	Other
6905	Ethernet Cable	14	350000	2	Parts
6906	Braking pads for casing	15	350000	2	Parts
6907	Bonding adhesive	16	350000	2	Parts
6908	Metallic sheet	17	350000	2	Other
5520	UPS Battery	2	350000	2	Parts
6858	SSD for IPC	2	350000	2	Other
6864	Refreshments for trails team	6	350000	2	Other
6867	Covid gear (nasks, sanitizers, Dettol etc)	8	350000	2	Parts
6868	Table cloth	9	350000	2	Other
7967	Screws (M3x5 screws for PCB mounting)	4	350000	2	Other
7968	Screws (M3x15 screws for casing)	5	350000	2	Other
7969	GX16-6 Aviation socket	6	350000	2	Other
7970	JST Xh 3 Pin (Connector for DIO)	7	350000	2	Other
7971	USB Mounting cable	8	350000	2	Other
7972	LEDCHIP-LED0603 (LED indication)	9	350000	2	Other
7973	2 Pin Jst Xh connector (Connector for reset button)	10	350000	2	Other
7974	Momentary Button (Reset Button, Mountable)	11	350000	2	Other
7975	0.1uF Capacitor	12	350000	2	Other
7976	10K Resistor	13	350000	2	Other
7977	12MHz Crystal Unit	14	350000	2	Other
7978	16MHz Crystal Unit	15	350000	2	Other
7979	1K Resistor	16	350000	2	Other
7980	1N5819 Schottky Diode	17	350000	2	Other
7981	1uF (Capacitor)	18	350000	2	Other
7982	20pF Capacitor	19	350000	2	Other
7983	8pF Capacitor	20	350000	2	Other
7984	CH340 (USB Programmer IC)	21	350000	2	Other
7985	MAX232_TIDW	22	350000	2	Other
7986	MCU-ATMEGA2560-16AU(TQFP100) Microcontroller	23	350000	2	Other
7987	Micro USB Port (Connector for programming)	24	350000	2	Other
7988	Profi interface cable with PC (50m/164 ft.)	1	350000	2	Parts
7989	Profi interface cable with controller (50m/164 ft)	2	350000	2	Parts
7990	Matt Fiber (305m2)	3	350000	2	Parts
7991	Epoxy (166 sets - 1.8kg/set)	4	350000	2	Other
7992	Wax	5	350000	2	Other
7993	Brushes	6	350000	2	Other
7994	Waxing cloth	7	350000	2	Other
7995	Nylon Nut Bolts	8	350000	2	Parts
7996	Epoxy get coat (12 sets - 1.5 kg/set)	9	350000	2	Other
7997	Epoxy pigment packet (white)	10	350000	2	Other
7998	Roller	11	350000	2	Parts
7999	Mechanial vise (clips)	12	350000	2	Parts
8000	LN key	13	350000	2	Parts
8001	Thinner	14	350000	2	Other
8002	grey paint	15	350000	2	Other
8003	19 pin army connector	16	350000	2	Parts
8004	Back power pipe threading for profi	17	350000	2	Parts
8017	GX16-3 data port	30	350000	2	Other
8018	Male Headers	31	350000	2	Other
8019	0.1uF capacitor	32	350000	2	Other
8020	0.33uF capacitor	33	350000	2	Other
8021	10K resistor	34	350000	2	Other
8022	16MHz crystal	35	350000	2	Other
8023	JST XH Connector 4pin	36	350000	2	Other
8024	22pF capacitor	37	350000	2	Other
8025	AMS1117-5.0 dropout voltage regulator	38	350000	2	Other
8026	ATMEGA328P-AU flash 2KB SRAM 8bit	39	350000	2	Other
8027	DCJ0202 DC Power jack	40	350000	2	Other
8033	Matt fiber (300 gsm)m2	1	350000	2	Other
8034	Wax box	2	350000	2	Other
8035	Pigment (white)	3	350000	2	Other
8036	gel coat (kg)	4	350000	2	Other
8037	Polyester (2 parts) (kg)	5	350000	2	Other
8038	thinner (ltr)	6	350000	2	Other
8039	Crushed Fiber (kg)	7	350000	2	Other
8040	Waxing Cloth	8	350000	2	Other
8041	Brushes	9	350000	2	Other
8042	Nitrile Gloves (box of 100)	10	350000	2	Other
8043	Hardener	11	350000	2	Other
8044	Nuts & Bolts	12	350000	2	Other
8045	Rubber gasket	13	350000	2	Other
8046	Silicon tubes	14	350000	2	Other
8049	Video cable	10	350000	2	Other
8050	balancers	11	350000	2	Other
8054	Industrial plugs/ connectors & wiring	15	350000	2	Other
8056	Electric Circuit Breaker	17	350000	2	Parts
8057	PLA Filament (for 3D printing)	18	350000	2	Parts
8058	TPU filament for gasket fabrication of glands for profi and RF cables for radome tower	1	350000	2	Other
8060	GPS Antenna, DB9 connector & battery wire	3	350000	2	Parts
8061	Disinfection material for lab & facility	4	350000	2	Cleaning Material
8062	LWF trials wiring, USB cable & connectors	5	350000	2	Other
8063	USB to TTL module for parcer	6	350000	2	Other
8067	AC wire 1.5mm,3 core	1	350000	2	Other
8068	DC wire 0.8mm,2 core	2	350000	2	Other
8070	USB to TTL	4	350000	2	Other
8071	DB 9 connector Female	5	350000	2	Other
8072	AC plug 3 pin	6	350000	2	Other
8073	RG-142 N-type to N-type cable	7	350000	2	Other
8074	LED light 5W	8	350000	2	Other
8075	Steel nails 1", 1.5"	9	350000	2	Other
8076	Extension board	10	350000	2	Other
8077	BNC to BNC connector	11	350000	2	Other
8078	Bulb holder	12	350000	2	Other
8079	GYMSA super glue	13	350000	2	Other
8080	UPS 1 kVA	14	350000	2	Other
8081	Camera 2MP	15	350000	2	Other
8082	DVR	16	350000	2	Other
8088	Inaugration plate, ribbon, scissor, petrol, bnc to bnc connector for camera	6	350000	2	Other
8089	04x chairs, HDMI splitter, inuaguration plate, 2pin connectors, lock, channel, ropes	7	350000	2	Other
8090	Epoxy based gel coat (box of 400gm)	1	350000	2	Other
8091	Epoxy resin (pack of 1.8kg)	2	350000	2	Other
8095	Mold PLA (gm)	6	350000	2	Other
8096	Mid core + Edge core (ABS)	7	350000	2	Other
8104	Power selector switch	15	350000	2	Other
8105	N-type connectors	16	350000	2	Parts
8106	AC plug	17	350000	2	Parts
8108	RS232-USB Cable	19	350000	2	Parts
8109	USB to TTL module	20	350000	2	Parts
8110	Clamps	21	350000	2	Parts
8111	Industrial Power plugs (3 prong female & male)	22	350000	2	Parts
8115	front metal sheet incl. laser cutting	4	350000	2	Other
8116	Powder coating	5	350000	2	Other
8117	back plate laser cutting	6	350000	2	Other
8121	Circuit breaker 220V-16A, double	10	350000	2	Parts
8122	Industrial power plug, 3 prong female	11	350000	2	Parts
8123	Industrial power plug, 3 prong male	12	350000	2	Parts
8124	AC fans 4inch with grill	13	350000	2	Parts
8125	HDMI cable	14	350000	2	Parts
8126	Ethernet cable	15	350000	2	Parts
8127	Cable channeling	16	350000	2	Parts
8128	VGA cable	17	350000	2	Parts
8129	Power cable 1.5mm, 3 core	18	350000	2	Parts
8130	Power cable 2.5mm, 3 core	19	350000	2	Parts
7789	Paint & oil	2	350000	2	Chemicals
7790	Registers	3	350000	2	Stationary
7791	Air filter & oil filter	4	350000	2	Other
7792	HDMI cable	5	350000	2	Other
7793	Plastic Storage boxes	6	350000	2	Other
7797	UPS battery & wiring	2	350000	2	Parts
7800	Misc. items (mortin spray, tyfon, waste pipe, bell & paint for trees)	5	350000	2	Stationary
7804	Raw material for VTS Yagi antenna & manufacturing cost	9	350000	2	Parts
7805	Nuts and bolts for radome	10	350000	2	Parts
7807	Oil & Air Filter	12	350000	2	Other
7810	Box folder	15	350000	2	Stationary
7811	Safety Goggles	1	350000	2	Other
7812	Safety gloves (form fitting)	2	350000	2	Other
7813	Chemical respirator	3	350000	2	Other
7814	KN-95 masks	4	350000	2	Other
7815	Coveralls	5	350000	2	Other
7816	Safety shoes	6	350000	2	Other
7819	Ear muffs	9	350000	2	Other
7820	Safety helmet	11	350000	2	Other
7821	Face shields	12	350000	2	Other
7823	RG-59 Cable	2	350000	2	Other
7824	BNC connector	3	350000	2	Other
7825	BNC 3 pin adapter	4	350000	2	Other
7826	GPS antenna	5	350000	2	Other
7827	XT90 connector	6	350000	2	Other
7828	Velcro tape	7	350000	2	Other
7829	cable tie clip	8	350000	2	Other
7830	Double side tape	9	350000	2	Other
8148	Electrical cable	8	350000	2	Equipment Modification
8150	PVC pipe, elebow socket, GI pipe, sand , cement	10	350000	2	Parts
8152	Chicken paker 7x300	1	350000	2	Meals/Refreshmnet
8153	French Fries	2	350000	2	Meals/Refreshmnet
8154	Tea	3	350000	2	Meals/Refreshmnet
8155	Mineral Water	4	350000	2	Meals/Refreshmnet
8156	Chicken Karahi	5	350000	2	Meals/Refreshmnet
8157	Chapati	6	350000	2	Meals/Refreshmnet
8158	Cold Drink 1.5 liter	7	350000	2	Meals/Refreshmnet
8159	5 person Dinner	8	350000	2	Meals/Refreshmnet
8160	Tea	9	350000	2	Meals/Refreshmnet
8161	Mineral Water	10	350000	2	Meals/Refreshmnet
2518	Magnets 1600 gauss	1	350000	2	Parts
2520	Transistor NPN DARL 400V 10A T0220	3	350000	2	Parts
2523	IGBT 1200V 50A 326W T0247-3	4	350000	2	Parts
2531	End mill for avid	11	350000	2	Parts
2532	Teflon rod	12	350000	2	Other
2533	Aluminum sheet 4mm (4ft x 4ft), welding, acrylic and rivnuts	13	350000	2	Other
2541	OBD II Connector	1	350000	2	Other
2542	Receiver Neo 6m	2	350000	2	Other
2543	Sim 800L GSM Module	3	350000	2	Other
2544	ESP 32 dev board	4	350000	2	Other
2545	SMD components	5	350000	2	Other
2546	E3D Steel Spanner for MK8 MK10 Nozzle	6	350000	2	Other
2547	24V 40W Cartridge Heater	7	350000	2	Other
2548	Blue PTFE tube ID: 1.75, L: 1m	8	350000	2	Other
2549	Thermistor HT-NTC100K B3950	9	350000	2	Other
2552	Drill Set (Avid Machine Accessories)	12	350000	2	Other
2553	Tapping Tools	13	350000	2	Other
6980	Tees (2”)	7	200000	2	Other
2554	Lathe Tools	14	350000	2	Other
2555	Fasteners and stainless steel rivnuts	15	350000	2	Other
2556	Drill bits, rawl bolts & actuator nut extension	16	350000	2	Other
2557	Rubber Boot with clips	17	350000	2	Other
2558	Stainless Steel Spring Handle	18	350000	2	Other
2559	USB panel mount cnlinko	19	350000	2	Other
2560	STM 32 with cable	20	350000	2	Other
2561	Eye Bolt: Size M12(MS), wire sleeves (35 & 18 mm) and 3.5mm audio jack	21	350000	2	Other
2562	Oil ISO 100 3L & lighter	22	350000	2	Other
2567	Stationery & Miscellaneouse (box files, CDs, A4 paper rims, masks, tissue box etc.)	27	350000	2	Other
2575	Actuator bolt socket head No. 46	2	350000	2	Other
2576	16x65 bolts for launcher	3	350000	2	Other
2577	Screws 4x15 for MARLIN	4	350000	2	Other
2578	Strap for MARLIN launcher	5	350000	2	Other
2579	Bearings large and small	6	350000	2	Parts
2581	End Mill cutters (6mm)	8	350000	2	Other
2582	Threaded rod M3	9	350000	2	Other
2583	D-Sub connector + Breakout for USB interface	10	350000	2	Other
2584	GX-17 7/9 Pin	11	350000	2	Other
2585	USB to USB cable 3m	12	350000	2	Other
2586	Audio jack 3.5mm metal	13	350000	2	Other
2587	Wire mesh 5 & 10mm	14	350000	2	Other
2588	M4 Rivnuts	15	350000	2	Other
2589	MARLIN 2 wiring 20 AWG yellow 30m	16	350000	2	Other
2590	Publication Charges	17	350000	2	Other
2591	Stationary for SPF (Rims, Thumb pins, markers, stickey notes, ball points, toner refill etc.)	18	350000	2	Other
2551	Carbide ball Nose Cutter for Avid	11	350000	2	Other
2550	Carbide End Mill Cutter for Avid	10	350000	2	Other
2667	Aluminum sheets of 4ft x 4ft (10mm)	2	350000	2	Other
2669	SUR-17 clamps powder coating	4	350000	2	Other
2671	SS FR4 PCBs	5	350000	2	Other
2672	Surgical gloves and masks	6	350000	2	Other
2685	Magic tee flange material & EDM charges	1	350000	2	Other
2686	Direction Coupler (51dB) material & EDM charges	2	350000	2	Other
2762	10mm Aluminum sheet (4ft x 4ft)	1	350000	2	Other
2763	Material for Antenna Mast strengthening and repaint (hardener, clear coat, PVC 1", auto paint, fiber layup, paint remover nad red oxide)	2	350000	2	Other
2769	IGBTs for Modulator restoration	3	350000	2	Other
2775	Polyester resin	8	350000	2	Other
2776	Gel coat	9	350000	2	Other
2777	Matt fiber	10	350000	2	Other
2778	Cobalt	11	350000	2	Other
2779	Spray WD40	12	350000	2	POLs
2780	Thread locker	13	350000	2	Other
2781	Paint bruches and paint	14	350000	2	Other
2782	Mixing cups	15	350000	2	Other
2783	Silicon Tube	16	350000	2	Other
2784	Safety gloves	17	350000	2	Other
2786	MS rod 32mm and 4140 rod	19	350000	2	Other
2787	Pipe 8mm	20	350000	2	Other
2788	U clamp for wire stopper	21	350000	2	Other
2789	Tygon lines	22	350000	2	Other
2790	Metal parts	23	350000	2	Parts
2816	Balsa Sheet 6mm	1	350000	2	Other
2824	Sanding pads for sander	7	350000	2	Parts
2846	Aluminum Sheet 4ft x 8ft (2mm)	1	350000	2	Other
2848	Battery Pack for a cordless sander, oscillator and drill	3	350000	2	Other
2875	MS sheet 4ftx4ft	30	350000	2	Other
2877	Nut, bolts and washers	31	350000	2	Other
2878	Die for bending	32	350000	2	Other
2882	PVC pipe 20 ft, elbows and glue	36	350000	2	Other
2883	Glass fiber cloth 1 meter	37	350000	2	Other
2884	Reed switches and relay	38	350000	2	Other
2885	Fishing Wire	39	350000	2	Other
2886	Extrusions, socket hex head, T slots nuts & corner brackets	40	350000	2	Other
2887	Auto paint (hardner, thinner)	41	350000	2	Other
2897	Aluminum Block	1	350000	2	Other
2898	Aluminum Sheet 8ft x 4ft 2mm thickness	2	350000	2	Other
2900	PVC elbows	4	350000	2	Other
2901	PVC T	5	350000	2	Other
2902	PVC Glue	6	350000	2	Other
2903	MS tumbuckle	7	350000	2	Other
2529	Rivnut Tool	10	350000	2	Other
2904	MS Thimble Eye bolt	8	350000	2	Other
2905	Nylon Rope 30 meters	9	350000	2	Other
2906	Silver paint quarter	10	350000	2	Other
2907	Nylon rod 40mm 1 meter length	11	350000	2	Other
2908	GSP mast paint	12	350000	2	Other
2909	B18.2.41M - Hex nut, style 1, M8 x 1.25 DN (201 Annealed stainless steel (SS) )	13	350000	2	Other
2910	Weld bung	14	350000	2	Other
2911	Rubber Damper	15	350000	2	Other
2912	B18.22M - Plain washer, 8mm, regular.	16	350000	2	Other
2913	B18.22M - plain washer, 8mm, wide ( 201 Annealed stainless steel (SS) )	17	350000	2	Other
2914	B18.2.2.4M - Hex Flange nut, M8 x 1.25 -- N (201 Annealed stainless steel (SS) )	18	350000	2	Other
2918	Spacers (Nylon)	19	350000	2	Other
2919	B18.3.1M - 8 x 1.25 x 16 Hex SHCS 16 NHX (201 Annealed stainless steel (SS) )	20	350000	2	Other
2920	Silicon sealant	21	350000	2	Other
2921	GPS receivers, USB to TTL modules and mil spec connectors	22	350000	2	Other
2923	Power jack	24	350000	2	Other
2924	Linear guides	25	350000	2	Other
2925	Linear bearings	26	350000	2	Other
2926	Fasteness	27	350000	2	Other
2927	Manual pulley wheel	28	350000	2	Other
2928	SS marking scale	29	350000	2	Other
2929	USB to TTL , Receiver Neo 6m and Neo 8m	30	350000	2	Other
2930	Powder coating , bending and sheet welding	31	350000	2	Other
2937	Argon Cylinder Refill	38	350000	2	Other
2942	Electronics componants Servo Display PCB (05 Sets)	1	200000	2	Parts
2943	Plugs and multi sockets	2	200000	2	Parts
2947	Auxalary computer repair	3	200000	2	Building Repairs & Maintenance
2952	Printer repair	4	200000	2	Equipment Repairs & Maintenance
2959	Heating Sleaves 35, 28, 25, 20 18, 16, 14	8	200000	2	Parts
2974	3m Patch cord  1 meter	7	200000	2	Parts
2976	3m Patch cord  3 meter	8	200000	2	Parts
2982	Electronic accessories	11	200000	2	Parts
2986	Electronic Accessories	4	200000	2	Parts
5246	PSD4135G2-90UI  TQFP80 ST	1	200000	2	Parts
5247	TQFP80 Adapter	2	200000	2	Parts
5248	PSD813F2A-90MI    QFP52 ST	3	200000	2	Parts
5249	PQFP52 Adapter	4	200000	2	Parts
5250	XC95144   PQ100AMM0905   F1292754A 10I   CPLD	5	200000	2	Parts
2961	Soldering station gun	9	200000	2	Parts
2958	Soldring station Gun	10	200000	2	Parts
2984	Window 10 registration DVD	2	200000	2	Parts
6078	DVD RW	112	350000	2	Other
962	Tool box for storage	34	350000	2	Other
5654	Generator Tires & Terminals set	9	350000	7	Parts
4880	Tupperware Box	25	350000	2	Other
1109	Quickcal. N9917A-112	2	350000	2	Parts
179	Monitor AOC 22B2HM 21.5"	36	350000	7	Parts
195	safety straps	48	350000	2	Parts
228	Hydraulic vise	28	350000	7	Tools / Test Equipment
1115	article number: 743EX BicoLOG 20100E X, Active biconical precision antenna, EMC version (20MHz-1GHz) Incl. external preamplifier with power supply	8	350000	2	Parts
5074	Rivet Tool & gun	27	350000	7	Tools / Test Equipment
4451	Dipolar switch mode microwave power supply	1	350000	2	Parts
438	Line Filter (H70)	5	350000	2	Other
5183	PLC Control System Industrial Control System with digital human-machine interface, customized software and associated wiring for enhanced zone control and temperature optimization.	2	350000	7	Parts
4226	USB-C to HDMI Converter	16	350000	2	Other
4469	Extension boards	12	350000	7	Office Equipment
4970	Door mat for Lab	5	350000	7	Office Equipment
1224	Fire Extinguishers	1	350000	2	Chemicals
1571	24" Hand trigger clamps	9	350000	2	Other
2566	02 x White boards, 01 x Green Notice board replacement	26	350000	2	Other
2861	Half inch vacuum hose wire reenforced	16	350000	7	Parts
6134	Transcend 832S SATA III M.2 SSD 256GB	25	350000	7	Parts
5706	Extension boards	11	350000	7	Tools / Test Equipment
5748	8m Shade/umbrella cloth	5	350000	7	Other
4659	Rubber mallet (For Mold & Parts releasing)	34	350000	7	Tools / Test Equipment
6082	Logitech IC400 Plus wireless Touch keyboard	3	350000	7	Parts
4720	AC Gree 2Ton (24000 BTU) –GF-24CDHAA+	1	350000	7	Office Equipment
6643	Storage boxes small	148	350000	7	Other
6611	Extension boards	116	350000	7	Tools / Test Equipment
1276	Standness and poster printing	52	350000	2	Other
2432	Spare Boxes Medium	2	350000	2	Other
6500	500GB Hard disk for PC	50	350000	7	Parts
6899	Scraping Tool	8	350000	2	Other
8458	Aluminum frame (9x7x8 feet)	17	350000	7	Parts
4603	DLE 120 CC CDI/Ignition Module	3	350000	7	Parts
2931	TIG Welding Torch Stubby Gas Lens for WP17	32	350000	7	Parts
6558	Extension wires (for field testing)	63	350000	7	Tools / Test Equipment
6706	Lenovo v330 i7 8GB RAM with Graphics Card	211	350000	7	Office Equipment
6059	Kingston 8GB 2666MHz	93	350000	7	Parts
6075	Wifi USB Dongle TpLink TL-WN725N	109	350000	7	Machinery/Equipment
4454	N9000B-526  Frequency range ,26.5 Ghz	2	350000	2	Other
3004	Auto paint	13	350000	2	Other
3001	Paint brush 2inch	10	350000	2	Other
2431	Spare Boxes Small	1	350000	2	Other
2991	MS sheet 4ftx4ft	1	350000	2	Other
2435	Air-Tight Box	5	350000	2	Other
2992	Stand-off	2	350000	2	Other
2993	Nutand bolts with washer M3, M4, M6	3	350000	2	Other
2994	Metal U clamp	4	350000	2	Other
2996	Powder coat	6	350000	2	Other
3006	Wax	15	350000	2	Other
2998	Sanding paper pack	8	350000	2	Other
3002	Paint remover	11	350000	2	Other
3008	Clear coat	17	350000	2	Other
3009	Hardener	18	350000	2	Other
3010	C clamps	19	350000	2	Other
2997	Emery paper disk 4in	7	350000	2	Other
2999	Paint Gun	9	350000	7	Tools / Test Equipment
4850	Electrical Appliances with installation at intell lab\n-Electric cable 18 rolls\n-Circuit breakers x 15\n-Face plate assembly x 9\n-Outdoor plugs\n-Main panel board\n-Ceiling fans x 7\n-LED Lights x 10\n-Flood lights with poles x 3	3	350000	7	Other
4526	Drawer Locks	13	350000	7	Other
5880	Electric wire 2 core (Load O/P)	3	350000	7	Parts
4609	xxxxx1	24	350000	7	Tools / Test Equipment
4608	Air Frame Molds & Patterns ( Fuselage)\n- Wing\n-Empennage	8	350000	7	Tools / Test Equipment
7788	Folding chairs	1	350000	7	Furniture
5346	Anti-slosh fuel cell foam	11	350000	2	Other
5345	Kevlar rope	10	350000	2	Other
5343	Fasteners	8	350000	2	Other
5344	Dowel Pins & Drill bit and reamer	9	350000	2	Other
5339	Front standoff 150x75x30	4	350000	2	Other
5338	Aluminum Rod Dia- 70mm x 500mm	3	350000	2	Other
5337	Electrode 30 x 30 x50	2	350000	2	Other
5336	Side Plate 250 x 300 x80 (43.8 kg each)	1	350000	2	Other
5348	Powder coating	13	350000	2	Other
5349	P1000 fasteners	14	350000	2	Parts
5340	Rear standoff 100x150x50	5	350000	2	Other
5347	Payload release mechanism	12	350000	2	Other
5351	High Pressure Fuel Lines 5/16" ID per foot	1	350000	2	Other
3020	MDF sheets	28	350000	2	Other
3033	Brad Nailer	41	350000	7	Tools / Test Equipment
3035	Carbide tip vernier caliper 8"	43	350000	7	Tools / Test Equipment
3031	Die Grinder	39	350000	7	Tools / Test Equipment
3025	Darkaero Composite Fabrication Guidelines	33	350000	7	Other
3022	Dell G5 180W charger	30	350000	7	Parts
8540	Thinner	26	350000	2	Other
8529	Hex Saw Blade	16	350000	2	Parts
8531	Cutting Disk	18	350000	2	Parts
8532	Emry Papers (100,240,320,600,1000,1500,80)	19	350000	2	Parts
8533	Buffing Disk	20	350000	2	Other
8535	Bonding adhesive box	21	350000	2	Other
8536	AA,AAA (Alkaline/ Rechargeable) Batteries	22	350000	2	Other
8539	Auto Paint (Smoke Grey & Red)	25	350000	2	Other
8526	Safety Shoes	13	350000	2	Other
8541	Office Stationery (rims, thumb pins,box file,markers,sticky notes, flags, ball points, register etc.)	27	350000	2	Other
8538	Red Oxide	24	350000	2	Other
6909	8D7C13F04PN Conn Circular PIN 4 POS Solder ST Jam Nut 4 Terminal Nut 1 port	1	200000	7	Parts
6913	8D7C13F98PN Circular MIL Connector	5	200000	7	Parts
5846	HP Color LaserJet Pro \nM454dn W1Y44A	1	350000	7	Office Equipment
8520	Teflon rod	7	350000	2	Other
8515	Cable LMR-400 TPE 100' & 250' spool	2	350000	2	Other
8516	RF connector N-type Male connector right angle	3	350000	2	Other
8517	RF connector N-type Male connector straight	4	350000	2	Other
8528	KN95 masks	15	350000	2	Other
8519	SMA connectors	6	350000	2	Other
8525	Coverall	12	350000	2	Other
8537	AA,AAA Battery charger	23	350000	7	Office Equipment
8530	Multi Screw Driver Set	17	350000	7	Tools / Test Equipment
8423	GMN Bearing Model: HY-KH-6007-2RZ-C-TXM-P4	1	350000	7	Parts
8424	NSK Bearing Model: 6003DW	2	350000	7	Parts
7594	Partition with Glass work, 04 Cupboards, Glass wall with wooden work including glass door, Main door, Coffee maker table, table drawer, wall side table, downside table separator, key rings, key boxes and furniture polish	12	250000	7	Furniture
4405	Frankonia EMV Germany\nField Strength meter (part EFS-500)\nE field probe, frequency range 300KHz – 26.5GHz, operation time 80h, consisting of E field probe (weight 25g, dia 53mm). measuring range: 0.4 – 800V/m, Resolution 0.001V/m, Measuring data: X, Y, Z 	1	350000	7	Machinery/Equipment
4840	Processor: AMD Ryzen 5 5600X\nMotherboard: Asus TUF B550-Plus Wifi\nRAM: G.SKILL TridentZ RGB 16GB DDR4 3600\nGC: Gigabyte GeForce GTX 1650\nSDD: Gigabyte 512GB NVMe M.2 SSD\nHDD: Seagate 1TB 3.5"\nCooler Master Air MA620P\nPower Supply: Gigabyte P750GM 750 Watt	31	350000	7	Office Equipment
1957	Tube bender	16	350000	7	Office Equipment
2505	Fabrication of RF PA PCB	10	350000	3	Fabrication
2506	Rack replacement	11	350000	3	Equipment Repairs & Maintenance
2510	Jazz Recharge RF Lab	12	350000	3	Other
2517	Freight/Handling	2	350000	3	Other
2249	Battery & Keyboard Repair	25	350000	3	Equipment Repairs & Maintenance
2760	Jazz recharge NDT & RF Lab	29	350000	3	Other
2761	Posters painting	30	350000	3	Other
8668	EDM of Top Plate	8	350000	3	Fabrication
8672	EDM of Bottom Plate	12	350000	3	Fabrication
8704	Bending and laser charges	41	350000	3	Fabrication
8715	Internet recharge Jazz & broadband connection at MF	50	350000	3	Other
8794	Jazz Internet charges for NDT, RF and Intell	13	350000	3	Other
8796	Electrical works at MF (repair/installation of lights)	14	350000	3	Building Repairs & Maintenance
4682	Joystick 	6	350000	7	Parts
7010	Control Indicator lights	13	200000	7	Parts
5133	Top trolley 	1	350000	7	Parts
285	T-200 thruster with ESC	3	300000	7	Parts
987	Precision Screw Driver Set	59	350000	7	Tools / Test Equipment
980	Tailor Scissor	52	350000	7	Tools / Test Equipment
996	Glue Gun	68	350000	7	Tools / Test Equipment
1388	Intel NUC KIT Client PC NUC11PAHi5 Ci5	22	350000	7	Machinery / Equipment
1389	Samsung 19" LED Display	23	350000	7	Machinery / Equipment
1357	Thruster T-200 with ESC	2	300000	7	Parts
1519	SOURIAU 8D7C19F32PN 32 pin Conn Circular PIN 32 POS Solder ST Jam Nut 32 Terminal Nut 1 port	6	200000	7	Parts
1889	Monitor  Samsung Odyssey G5 27inch Curved QHD 144hz HDR10 Gaming Monitor	11	200000	7	Machinery / Equipment
8655	Designing with AXI DMA package as described in Green-Electronics webpage (complete package including all items)	1	200000	3	Other
7087	HP design jet T520 36 in plotter	2	300000	7	Machinery / Equipment
8120	Ethernet mount	9	350000	7	Parts
7666	Multipurpose Color Laser Network Printer for ROV Design Lab	4	300000	7	Machinery / Equipment
7665	Desktop Computer Terminal with Hi-end Graphics Cards for ROV Design & Simulation	3	300000	7	Machinery / Equipment
7664	02 Ton Standing Air Conditioner	2	300000	7	Machinery / Equipment
7663	04 Ton Standing Air Conditioner	1	300000	7	Machinery / Equipment
2981	Power supply 220v to 110v	10	200000	7	Parts
2980	Molex Connection 10 pin male/femal	9	200000	7	Parts
5653	GPS Antenna	8	350000	7	Parts
8490	Workstation Chairs	4	300000	7	Furniture
8489	Workstation Tables	3	300000	7	Furniture
7089	Mechanical Tool kit with trolley	2	300000	7	Tools / Test Equipment
7086	Electrical Tool kit	1	300000	7	Tools / Test Equipment
1916	Clamping Kit 12A	24	350000	7	Office Equipment
1920	Screw Guage Mitutoyo (0-25mm)	28	350000	7	Office Equipment
7098	PX1-C415 SBC accessories	1	300000	7	Tools / Test Equipment
6566	Video Balun	71	350000	7	Parts
5993	GPS stand	27	350000	7	Parts
2966	HP Laptop Elitebook	1	200000	7	Machinery / Equipment
2700	HP Printer laser jet 404	1	200000	7	Machinery / Equipment
1788	Engineering solution for work specific to Super HET HF: \r\na. Development of methodology for dissassembly of Super HET HF structure onsite for TPT\r\nb. Dissassebly of complete Super HET HF structure down to parts level in company facility\r\nc. Inspection, degreasing and reconditioning of all parts of Super HET HF\r\nd. CAD modeling of rotation mechanism of the Antenna Mast\r\ne. Detail CAD of inner structural details of Chassis of Super HET HF\r\nf. Greasing and re-assembly of Super HET HF Structure for improved future life supportability.	1	200000	7	Parts
1364	MIC-3022AE 3U Computer System of MIC-3022 with CPCI PSU, MIC-3329C1-D1E MIC-3329 w/E3845 4G RAM dual Slot RoHS, AQD-SD3L4GE16-SG 4G ECC SO-DDR3-1600 512X8 1.35V SAM, MIC-3955B1-S3E MIC-3955 8-Ports DB62 RS-232/422/485 FIO, MIC-3955A1-S1E MIC-3955 4-Port DB44 RS-232/422/485 FIO, MIC-3954-BE MIC-3954 CPCI-S IO REV.A101-2 HDD, 96ND1T-ST-SG7E SG Enterprise 2.5” 1TB 7KRPM SATA 128MB, EKI-2725-CE  5-Port Ind. Unmanaged GbE Switch, 968QW7PROE Win Pro 7 Emb x 32/64 ORY OEI 42C-00021	1	200000	7	Machinery / Equipment
2828	NI-X310-SDS101-ENC Industrial PC FPC-7700, NI USB-6003, mounting for USRP X310 with UBX 10-6000 MHz Rx/Tx (160 MHz), 500-2700 MHz 100W Amplifier with Heatsink, XP Power DNR480, Intelligent DC-DC Converter with USB Interface, Industrial Touch Panel Monitor, OMNI Antenna with Stand OMNIA0266/VCT-680RM, Antenna RF Cable, Brief Case	1	350000	7	Machinery / Equipment
2601	Lean ratio 1:3 after compacted surface of stone	4	300000	7	Other
2528	Rivnut Gun	9	350000	7	Tools / Test Equipment
7308	Revolving Chair	2	400000	7	Office Equipment
2803	LCD Monitor 32inch Samsung with Installation	1	350000	7	Machinery/Equipment
2796	Industrial control system	29	350000	7	Parts
1513	Overall Design & Manufacturing of Mechanical\r\nEnclosure for Onboard NUI Interface Unit, based on\r\nour meeting, client supplied information, data sheets,\r\njoint study/analysis and mutually agreed conceptual\r\ndesign.  (Specifications: Aluminum Alloy, CNC Machined, Internal Alodine, Eextrnal PU Paint Finish) Refer attached Drawing.	1	200000	7	Parts
8550	Purchase of slide scan sonar (IMAGENEX 852)	3	300000	7	Tools / Test Equipment
8551	Purchase & Installation of 4 Ton standing Air Conditioner and Paint work	4	300000	7	Other
1509	MIC-3022CE 3U SYSTEM OF MIC-3022 W/ CPCI PSU, LEGAC, MIC-3329C1-D1E MIC-3329 W/ E3845 4G RAM DUAL SLOT ROHS, MIC-3955A1-S1E MIC-3955 4PORT DB44 RS-232/422/485 FIO 6 card each, SQF-S25M8-512G-SAE SQF 2.5 SATA SSD 830512G MLC, 968QW7PROE Win Pro 7 Emb x 32/64 ORY PEI, AGS-CTOS-SYS-A Standard Assembly+Functional Testing+Software	1	200000	7	Parts
5270	"PCB 6 LAYERS METMAX_2.1_Locked_2022-10-\n10_Y111     PCB COLOR Green FR4 TG155 BOARG EIGS Gold Thickness 1u PCB Thickness 1.6 MM   & PCB 6 LAYERS MET_MAX_V2.0_LOCKED_2022-10-10 5 7,500 37,500 4-5 weeks PCB COLOR Green FR4 TG155 BOARG EIGS Gold Thickness 1u PCB Thickness 1.6 MM"	1	200000	7	Parts
8549	Purchase of thruster (BTD 150), underwater colour HD camera, Underwater light	2	300000	7	Other
7097	3DM-GX5-25 Attitude and Heating Reference System	1	300000	7	Other
7667	ROV Design Lab Meeting Room Media Display	5	300000	7	Machinery / Equipment
2973	Electric SSD  for systems	6	200000	7	Parts
2972	Synchro convertor supply	5	200000	7	Parts
2970	Mother board and processor	4	200000	7	Parts
2968	EK switch power supply (12-48v)	3	200000	7	Parts
2953	CDVD ROM	5	200000	7	Parts
8493	SeaLevel ® 1-Port Isolated RS-422 DB25 Serial Interface Adapter	1	300000	7	Other
8316	T1080 Underwater Thruster. Rated power 2.4KW, Rated voltage 260VDC, Thrust forward 48kg, Thrust reverse 48kg, Material of propeller:SS, Material of housing:AL alloy, Subsea connector:MCBH8M, Operating depth:0-850m, Control mode & speed feddback:CAN, Electronics:built-in	2	300000	7	Machinery / Equipment
1930	REGOL DS1202Z-E 2 channel oscilloscope with 200 MHz bandwidth, 1 GSa/s and up to 24 Mpts memory depth and 17.8 cm LCD display (800×480 pixel) INCLUDED ACCESSORIES The DS1202Z-E comes with two 350 MHz PVP2350 probes Calibration Certificate Power Cord USB Cable	1	200000	7	Tools / Test Equipment
2949	Battery 9v multimeter	6	200000	7	Parts
2940	SWYV-9 Cable - 20m	2	350000	7	Parts
6450	IsoLOG 3D 80 Array\n3D Tracking Antenna (400 MHz to 8 GHz) 8 sectors with 16 antennas, white color (default) with or without print (customer choice includes 8 sector pre-amplifier with bypass, power supply, Ethernet cable, power cable, control software, AP	1	350000	7	Machinery/Equipment
4342	[VECS030] PCS Control Station (LOS 900MHz +BLOS Radio) V4.5\nPortable control station in a rugged IP67 case installed on a foldable mast with metallic joints, having an adjustable height from 1.50m to 3.15m\nEmbedded Veronte Autopilot BCS, GNSS antenna & 4G	7	350000	7	Other
4336	VEA9077]Veronte Autopilot (LOS 900MHz + BLOS Radio) V4.5\nOnboard Veronte Autopilot unit for advanced control of unmanned systems\nTwo way communications radio embedded\n- LOS radio 902-928MHz, FHSS, -112dB sensitivity, 100Mw-1W\n- BLOS HSPA+/3.75G BLOS commu	1	350000	7	Other
7082	PX1-C415 SBC with 8GB RAM and Mini Display Port	1	300000	7	Tools / Test Equipment
1362	Pressure sensor (300 meter depth rate)	6	300000	7	Parts
7682	Desktop Computer Terminals for ROV project team members	2	300000	7	Machinery / Equipment
7683	Chairs	3	300000	7	Furniture
7699	File/ Storage Cabinets	3	300000	7	Furniture
7697	Workstation Tables	1	300000	7	Furniture
1924	Vernier Calliper Mitutoyo 200mm	30	350000	7	Office Equipment
8119	HDMI mount	8	350000	7	Parts
5191	Exhaust pipe, Oil radiator fan\n-Coolant radiator fan, Hose: Radiator oil, Hose: Radiator coolant, Hose: Fuel\n-Hose reducer, Oil tank line fittings\n-Oil tank fittings\n-Filter - Fine fuel filter,Fuel check valve, Fuel tank, Jubilee clamps\n-Browden cable	7	350000	7	Parts
8311	T1020 Underwater Thruster. Rated power 1.25KW, Rated voltage 260VDC, Thrust forward 24kg, Thrust reverse 9.5kg, Material of propeller:SS, Material of housing:AL alloy, Subsea connector:MCBH6M, Operating depth:0-850m, Control mode & speed feddback:CAN, Electronics:built-in	1	300000	7	Machinery / Equipment
5588	Onboard Junction Box (CNC Mechined Aluminum Mechanical Enclosur) a. Data Terminal Blockx20, b. Power Terminal Blockx5, c. Mill spec ON/OFF locking Toggle Switch 2TL1-20x2, d. Indication Lamps Geen/Red x2, e. Cable Gland PG11x7	2	200000	7	Parts
2922	ASUS MINI PC C13 1115 G4 @4 77,000 + DDR4 8GB + 256GB NVME	23	350000	7	Parts
2899	55" and 65" LCD stands	3	350000	7	Parts
2888	GSP tellies	42	350000	7	Parts
2967	Window 10 registration DVD	2	200000	7	Software
2847	Voltage Stabilizer and 110V converter	2	350000	7	Parts
2822	Matek airspeed sensor	5	350000	7	Other
2819	Matek M8Q-5883 GPS	4	350000	7	Parts
2818	Matek F7 flight controller	3	350000	7	Parts
2817	FPV Ranger UAV	2	350000	7	Parts
2797	Batteries 4000mAh	30	350000	7	Parts
2950	Heat Gun Soldering iron SMD Rework 2 in1 Station	7	200000	7	Parts
2785	Bolts, nuts and clamps	18	350000	7	Parts
2768	Current and voltage sensor	2	350000	7	Parts
2767	Capacitors, resistors and feedback voltage controllers	1	350000	7	Parts
2766	RF Adapters N-N type	5	350000	7	Parts
7579	LED Side Panel	5	250000	7	Other
248	Ethernet Cable	4	300000	7	Parts
2765	RF SMA connector PCB Mount	4	350000	7	Parts
2764	RF Cable Assembly	3	350000	7	Parts
2717	4-Position Universal Rotary Cam Changeover Switch	3	200000	7	Parts
2711	Osciloscope Main IC controller	9	200000	7	Parts
2983	HP Laptop Pro book	1	200000	7	Parts
2716	Three Phase Over & Under voltage Protection Device	2	200000	7	Parts
2715	Three Phase Surge Protection Device	1	200000	7	Parts
2714	PCB 6 Layers Extender V4  1.6mm width 233mm hight 270mm	2	200000	7	Parts
250	Joystick (Logitech F710)	6	300000	7	Parts
7610	Rasberry Pi Processor Board	1	300000	7	Machinery / Equipment
7623	DC Gear Motor with Encoder (12V-166 rpm)	1	300000	7	Machinery / Equipment
7608	Waterlight enclousure 11.75" with 4- diameter Depth rating 100 meter	1	300000	7	Parts
2713	PCB 4 Layers Angle Display V5 1.6mm width 78.45mm hight 130.55mm	1	200000	7	Parts
2712	DB15 connector with cover	10	200000	7	Parts
2710	SBC DC supplies 5vdc 5A	8	200000	7	Parts
7624	Provision of Paper Shreder	2	300000	7	Machinery / Equipment
7706	Lumen ® Subsea Lights (Pre-Connected Set) for prototype ROV	2	300000	7	Machinery / Equipment
7606	T-100 Thruster (120 watt 2-3 Kg)	1	300000	7	Machinery / Equipment
8324	T2020 Underwater Thruster. Rated power 6.5KW, Rated voltage 260VDC, Thrust forward 120kg, Thrust reverse 60kg, Material of propeller:SS, Material of housing:AL alloy, Subsea connector:MCBH12M, Operationg depth:0-850m, Control mode & speed feddback:CAN, Electronics:built-in	3	300000	7	Machinery / Equipment
7605	Manufacture of below mentioned subsystems for prototype ROV:\r\na.  Pressure Hull for Sensors Electronics and Control System\r\nb.  Mountings of Sensors/ Electronic Equipment placed on outer structure of the vehicle\r\nCharacteristics:\r\na.  Operational depth: up to 300m\r\nb.  Magnetic Signature <50nT at 1.5m (in X, Y and Z axis)\r\nc.  Acoustic Signature < 30dB	1	300000	7	Machinery / Equipment
2291	Underwater Light (1500 Lumens)	3	300000	7	Parts
1398	Low light high resolution camera for 300 meter depth rated (720 TVL and 65 degrees diagonal angle of view)	1	300000	7	Machinery / Equipment
7100	Desktop PC core i3 8gb ram/ 1tb hard drive	1	300000	7	Machinery / Equipment
1401	Customized mounting design/ fabrication for camera	2	300000	7	Parts
8554	Purchase of equipments for UUVs Project	7	300000	7	Other
8492	Portable Diagnostic Display (Laptop) for UUV Performance Testing	6	300000	7	Machinery / Equipment
7612	Camera (Pixy2)	3	300000	7	Tools / Test Equipment
2752	Ball Links	21	350000	7	Parts
8363	Power Supply 12V DC 15A	31	350000	7	Parts
8368	Voltage/ampere meter 12V DC	33	350000	7	Parts
7690	Deluxe 3.5" x 17" lathe with two stepper motors (metric measurements)\r\n3.1" 3-jaw chuck, 3/8" drill chuck and accessories	1	300000	7	Machinery / Equipment
7685	Hoist structure with H-beam	2	300000	7	Other
283	Manufacturing of frame/body structure of scaled down design (Mold Manufacturing  ,  Outer Body Structure manufacturing , main frame /inner cage fabrication (Aluminium) , buoyancy bars manufacturing , fastners and clamps for main structure)	1	300000	7	Tools / Test Equipment
7099	Cable kit for AHRS	2	300000	7	Tools / Test Equipment
7105	Thruster T200	1	300000	7	Tools / Test Equipment
7106	Pixhawk with accessories	2	300000	7	Tools / Test Equipment
8338	MCIL6F//MCDLS-F//3.2m Cable// MCDLS-F//MCIL6M MCBH6F//1.3 pigtail	6	300000	7	Tools / Test Equipment
7084	LattePanda 4GB/64GB	2	300000	7	Tools / Test Equipment
7611	BW0055 (9 Dof IMU Sensor)	2	300000	7	Tools / Test Equipment
7614	MPV 9250/ GY 85 - DoF IMU sensor low accuracy	2	300000	7	Tools / Test Equipment
2709	IDE 20 pin connectors	7	200000	7	Parts
2708	Bios cell base	6	200000	7	Parts
2707	CF SD Cards	5	200000	7	Parts
2705	Spactrum Analyser battery	4	200000	7	Parts
2704	SBC Flash memory IC K9F5608U0D	3	200000	7	Parts
2703	USB to parrallel port card	2	200000	7	Parts
2524	Oscillating Multi Tool	5	350000	7	Tools / Test Equipment
2691	Passive components including DC block, RF chokes & resistors	7	350000	7	Parts
2690	X-band frequency synthesizer IC	6	350000	7	Parts
2689	Final Stage Amplifier IC for X-band 100W	5	350000	7	Parts
7622	Arduino Mega 2560	4	300000	7	Other
8112	Network Rack 18U full rack	1	350000	7	Parts
1791	Special handling equipment for for secure removal, transportation & reassembly for final installation at site.	4	200000	7	Tools / Test Equipment
981	Calibrator (Current Source)	53	350000	7	Tools / Test Equipment
8131	Electric Distribution Box (EDB)	20	350000	7	Parts
2342	Paint Work (Distamper)	4	300000	7	Other
2688	Intermediate Amplifier IC for X-band 100 W Amplifier	4	350000	7	Parts
8488	Integrated Project Data Back Up Server System ROV Design Lab	2	300000	7	Machinery / Equipment
5272	PYB15-Q24-S12-H-T DC/DC CONVERTER 12V 15W	3	200000	7	Parts
5136	Servo horns(Q-Mia)	2	350000	7	Parts
1647	25kW Modulator PCB T65825812-7	2	350000	7	Tools / Test Equipment
7110	Construction of SVDC Phase-1	1	300000	7	Other
5271	PCB 6 LAYERS METGRAPH_1-4_FINAL_DESIGN_10th  7,000 35,000 4-5 weeks PCB COLOR Green FR4 TG155 BOARG EIGS Gold Thickness 1u PCB Thickness 1.6 MM	2	200000	7	Parts
2339	Office Furniture Set	1	300000	7	Furniture
2687	Driver Amplifier IC for X-band 100 W Amplifier	3	350000	7	Parts
2196	96PS-A250WCPC SPS 100-240V 250W W-PFC LF	4	200000	7	Parts
372	DMC TOOL 12-26 AWG Wire range M22520/1-01 Crimping	11	200000	7	Parts
1029	Digital Vernier Caliper	21	200000	7	Tools / Test Equipment
2140	EKI-2725-CE 5-port Ind. Unmanaged GbE Switch	2	200000	7	Machinery / Equipment
5273	01609.0-01 Enclosure Heating Element, 10W, 120°C, 12 ? 36 V ac/dc	4	200000	7	Parts
3027	Actuator	35	350000	7	Parts
7684	02 ton Mechanical hoist	1	300000	7	Machinery / Equipment
1034	Networking Switch 24 Ports	1	200000	7	Parts
1655	HP Elitebook 840 G8 Core i5 11th Gen 8GB 512GB SSD 14"	5	200000	7	Machinery / Equipment
386	40 PC Hex Bits Hand Impact Screwdriver Set	8	200000	7	Tools / Test Equipment
7294	Laptop Core i7 with SSD and HD Display	13	400000	7	Tools / Test Equipment
2525	Laser Level	6	350000	7	Tools / Test Equipment
144	Electric Screwdriver (Ingco)	3	350000	7	Machinery / Equipment
4127	Power Supply 600W	12	350000	7	Other
1773	SS Cabinet for Assembly area	2	350000	7	Furniture
7619	DC converter 12/24 V to 5V/10A	1	300000	7	Machinery / Equipment
2535	Mouse, keyboard, HDMI cable	15	350000	7	Office Equipment
2519	Power Supply of 9GHz Magnetrons	2	350000	7	Machinery / Equipment
2516	Power Supply PCB T65825816	1	350000	7	Parts
5275	MiniPlex-BUF MiniPlex-BUF is an advanced NMEA buffer/splitter	6	200000	7	Parts
5276	 4DBEZEL-70-B Bezel for 7" Display Modules - Black with sd card	7	200000	7	Parts
8147	Plywood table	7	350000	7	Equipment Modification
6919	PCIE-1612B-AE 8-port RS-232/422/485 PCI-express UPCI COM card/S	1	200000	7	Parts
8571	MIC-3955A1-S1E, MIC-3955 4-Port, DB44, RS-232/422/485, FIO	1	200000	7	Parts
6920	Connectors DB9 with covers	2	200000	7	Parts
6921	IDE64 connector Male/Female with ribbon	3	200000	7	Parts
6922	USB DVD RW 	4	200000	7	Machinery / Equipment
6923	SSD 120	5	200000	7	Parts
6924	Keyboard K400 Logitech	6	200000	7	Parts
6925	Synchro convertors old	7	200000	7	Parts
6926	Power supply synchro convertors	8	200000	7	Parts
8144	AC outer steel cover	4	350000	7	Parts
8143	Electrical fittings	3	350000	7	Equipment Installation
8142	AC GREE 1 ton	2	350000	7	Machinery/Equipment
7580	Doors	6	250000	7	Other
8141	LCD Monitor	1	350000	7	Machinery/Equipment
7836	Material (beams, plates, fasteners, paint, and other consumables	4	350000	7	Equipment Installation
7833	Material (Steel, cement, gravel & stand)	1	350000	7	Equipment Installation
7832	SP5050 Modified Pan-Tilt	1	350000	7	Machinery/Equipment
7831	SP050 modified paan-tilt	1	350000	7	Machinery/Equipment
7806	VTS Stand	11	350000	7	Parts
7798	GPS receiver Module (MCNV)	3	350000	7	Parts
7795	3U chassis for IPC	8	350000	7	Parts
8132	K400 Keyboard/Mouse	21	350000	7	Parts
8118	wheels (set of 04)	7	350000	7	Parts
6929	Dual port Ethernet cards	11	200000	7	Parts
6930	Ethernet card 	12	200000	7	Parts
7488	Serial card 4 ports	1	200000	7	Parts
8114	2U rack mount keyboard tray	3	350000	7	Parts
8113	1U cantilever tray	2	350000	7	Parts
8564	Material for Antenna structure	2	200000	7	Parts
8107	Electrical Distribution Board	18	350000	7	Parts
720	Long Nose plier	49	350000	7	Machinery / Equipment
2628	Director Office Table	15	300000	7	Furniture
8560	Desktop HP 400 G5 Core i7	1	200000	7	Machinery / Equipment
8094	GPS Receiver (USB type)	5	350000	7	Parts
6865	MIC-3955A1-S1E, MIC-3955 4-Port, DB44, RS-232/422/485, FIO	1	200000	7	Parts
2812	PVC pipe, Elbow, Socket, GI pipe, Sand, Cement	9	350000	7	Other
8093	Keyboard K400+	4	350000	7	Parts
8092	Del Optiplex PC	3	350000	7	Machinery/Equipment
8087	Aero marine base plate	5	350000	7	Parts
8086	Mount for yagi antenna	4	350000	7	Parts
8085	Mount for Aeromarine	3	350000	7	Parts
8575	IP camera	3	250000	7	Other
2805	Electrical items	3	350000	7	Machinery/Equipment
5584	Onboard NUI interface unit (CNC Mechined Aluminum Mechanical Enclosur) with standard COTS items and Accessories (Ref Drawing)	1	200000	7	Parts
2810	Electrical cable 16mm x 220ft	7	350000	7	Other
8084	Mount for Profi tracker	2	350000	7	Parts
8066	HDMI Cable, raceway, ac wire for radome	9	350000	7	Parts
8573	Purchased of GPU based PCs, with LED, Keyboard & Mouse	1	250000	7	Office Equipment
2809	Chipboard table	6	350000	7	Furniture
2806	AC Outer Steel Cover	4	350000	7	Parts
2804	AC Gree 1.5 Ton	2	350000	7	Machinery/Equipment
8580	LAN Switch 10/100 CISCO	7	250000	7	Tools / Test Equipment
8065	Los VTS Van Chair	8	350000	7	Parts
8064	Power enhancement of batch 3 video decoders	7	350000	7	Parts
8055	Cooling Fans with casing	16	350000	7	Parts
8053	Keyboard with mouse	14	350000	7	Parts
8052	LCD for display	13	350000	7	Parts
8051	15 U Casings with supporting trays for GT R&S Station	12	350000	7	Parts
8032	HDMI splitter	45	350000	7	Parts
8031	HDMI cable	44	350000	7	Parts
8030	Mouse and Keyboard	43	350000	7	Parts
8029	AC power cord	42	350000	7	Parts
8028	Dell Opitplex	41	350000	7	Machinery/Equipment
8016	LCD i2c module/interface	29	350000	7	Parts
8015	LCD display 20x4 monochrome	28	350000	7	Parts
8012	Logitech K400 PLUS Wireless Touch Keyboard	25	350000	7	Parts
8011	19" LCD incl. manufacturing of rack mount	24	350000	7	Parts
8010	Power Supply for PC (Thermaltake Litepower Series GEN2 450W)	23	350000	7	Parts
8009	SSD ADATA Ultimate SU650 120GB	22	350000	7	Parts
8008	RAM ADATA 8GB Premier DDR4 2666	21	350000	7	Parts
8007	Motherboard Gigabyte D365M	20	350000	7	Parts
2454	Tube cutter	23	350000	7	Tools / Test Equipment
5820	Saitek Logitech x56 HOTAS	4	350000	7	Parts
8006	Processor Intel i3 9100	19	350000	7	Machinery/Equipment
8005	Plate/bracket, spacers & screws for antenna mounting with making	18	350000	7	Parts
7964	18U rack with trays	1	350000	7	Parts
7428	2 TB HDD	8	200000	7	Parts
8587	UPS 2KVA brand universal	9	250000	7	Other
8588	Battery for UPS 200 Amp	10	250000	7	Other
8561	Scanner Canon Lide 300	2	200000	7	Office Equipment
5646	INTEL PCI-Express X540-T2 10G Dual RJ45 port Network Interface Card	1	350000	7	Parts
5651	Cooling fans (for Jammer)	6	350000	7	Parts
5657	Moveable stands for antenna	1	350000	7	Parts
5672	SunnySky X4125KV465 Motor	1	350000	7	Parts
5674	YEP 10A ESC	3	350000	7	Parts
5675	Corona DS339MG Servo Motor	4	350000	7	Parts
5676	M3 Push rods & control horns	5	350000	7	Parts
5677	Eachine 5.8GHz Video Transmitter	6	350000	7	Parts
5678	Patch Antenna 5.8GHz	7	350000	7	Parts
8596	Water proof Splicing box	17	250000	7	Other
6874	LMR900 cable 70ft.	3	350000	7	Parts
6873	Cooler & fans for jammer PC	2	350000	7	Other
7230	Wheels  (set of 4)	37	350000	7	Other
7229	VGA to HDMI converter	36	350000	7	Parts
7226	Power Supply Corsair 450W	35	350000	7	Parts
7224	SSD 256GB	34	350000	7	Other
7221	RAM 8GB	33	350000	7	Other
7217	Gigabyte B560M DS3H AC Intel Durable Motherboard	32	350000	7	Office Equipment
7213	Intel i5 10400 processor	31	350000	7	Office Equipment
7204	Power cable 2.5mm 3 core 5m	27	350000	7	Parts
7203	Power cable 1.5mm 3 core 5m	26	350000	7	Parts
5679	Foxeer CCD Camera	8	350000	7	Parts
5682	Analog Screen	11	350000	7	Parts
5693	Power Supply 30V/16A with 220/230V AC 	1	350000	7	Parts
5694	Power Supply 28V/10A with 220/230V AC 	2	350000	7	Parts
5695	Power Supply 12V/15A with 220/230V AC 	3	350000	7	Parts
5696	Wireless LAN UE300 (USB to Ethernet converter)	1	350000	7	Parts
5705	GPS Antenna	10	350000	7	Parts
5738	128x64 LCD	10	350000	7	Parts
5750	Aviation grade Cargo belts	7	350000	7	Parts
5753	Pixhawk 2.1 with accessories (Here+GNSS & RFD900)	1	350000	7	Parts
5755	Tarot 12V BEC	3	350000	7	Parts
5777	Control box	19	350000	7	Parts
5785	Antenna Stands	1	350000	7	Parts
5800	Acrylic box for Arduino circuit	9	350000	7	Parts
5819	RC Receiver	3	350000	7	Parts
5821	OSGT33 30cc Engine	1	350000	7	Parts
5824	4.8-6V Whole set Electric CNC metal gear fuel pump	4	350000	7	Parts
5825	EMAX Payload Servo	5	350000	7	Parts
5826	MKS HV69 Servo	6	350000	7	Parts
5866	Video Balans	9	350000	7	Parts
5867	GPS Antenna 5m	10	350000	7	Parts
5868	GPS Antenna 3m	11	350000	7	Parts
5876	LAN card	19	350000	7	Other
5877	Power Supply	20	350000	7	Parts
7707	Desktop PC (Intel Core i7) with HD Graphics Card for development and executio of a homegrown acoustic Navigation Algorithm	3	300000	7	Machinery / Equipment
7426	HP LEDs V194 18.8" 	6	200000	7	Machinery / Equipment
8486	Paint Work and Electric Wiring	7	300000	7	Other
7423	Industrial PCS Core2 Due  4GB HDD, 2TB, 02 Serials ports, 02 DVI/HDMI/VGA	3	200000	7	Machinery / Equipment
7424	HP LEDs V194 18.8" 	4	200000	7	Machinery / Equipment
7425	Industrial PCS Corei5 8GB, 1TB HDD, 02 Serials ports, 	5	200000	7	Machinery / Equipment
7427	HDD Cloning Docking Station 2 Ports	7	200000	7	Tools / Test Equipment
5790	Compass	6	350000	7	Tools / Test Equipment
5903	Fuel tank	9	350000	7	Parts
5904	Landing gear actuator	10	350000	7	Parts
7646	Hammer With Chicle	12	160000	7	Tools / Test Equipment
7644	Screw Driver S+	11	160000	7	Tools / Test Equipment
7642	Screw Driver S-	9	160000	7	Tools / Test Equipment
7649	Knife	15	160000	7	Tools / Test Equipment
5911	CF landing gear main	3	350000	7	Parts
5945	Landing gear for Skywalker	9	350000	7	Parts
5946	F450 frame for quadcopter	10	350000	7	Parts
5955	GPS Antenna	19	350000	7	Parts
5967	Electrical Distribution Box (EDB)	1	350000	7	Parts
5969	USB Port	3	350000	7	Parts
5970	Ethernet Port	4	350000	7	Parts
5971	Antenna RF Port	5	350000	7	Parts
5972	MCU Power Switch	6	350000	7	Parts
8133	AOC 9E1 LCD Monitor	22	350000	7	Parts
5973	Main Power Switch	7	350000	7	Parts
5974	Power Socket	8	350000	7	Parts
5975	PA Switch	9	350000	7	Parts
5977	Brackets - Cooling Fans / Fan Grill	11	350000	7	Parts
5978	Cooling Fans 6inch	12	350000	7	Parts
5979	Cooling Fans 4inch	13	350000	7	Parts
5982	Main Power Cable	16	350000	7	Parts
5983	Power Supply- 30W PA	17	350000	7	Parts
5984	Power Supply-100W PA	18	350000	7	Parts
5986	15U rack with Accessories	20	350000	7	Parts
5988	F450 Quad frame	22	350000	7	Parts
5989	Racerstar 2312 960KV	23	350000	7	Parts
5990	BLHeli 30A Skyliner	24	350000	7	Parts
6070	HDMI to VGA adapter	104	350000	7	Parts
6071	VGA to HDMI Convertor	105	350000	7	Parts
6072	SSD 480 GB ADATA SU630	106	350000	7	Parts
6537	Skycam Telecommand System	24	350000	7	Parts
8494	1155 KHZ (0.5m-30m range) SONAR altimeter/ Echo Sounder	2	300000	7	Machinery / Equipment
8496	Single Extruder PLA Prototyping Test bed for prototype ROV frame parts	4	300000	7	Parts
8286	Digital Video Recorder	8	450000	7	Other
8287	Hard Drive	9	450000	7	Software
8291	PVC Box	13	450000	7	Other
8294	Electro-Hydraulic System With Cylinder	16	450000	7	Other
4129	Hobbywing 10A UBEC	1	350000	7	Other
4130	MKS HBL380 Servos	2	350000	7	Other
4131	1.75" Servo horns	3	350000	7	Other
4133	MG996R Metal Gear Servo	5	350000	7	Other
4134	Flex sensor SR400 Strain Gauge	6	350000	7	Other
4135	SD Card & Module	7	350000	7	Other
4138	CNC engraved power distribution box	10	350000	7	Other
4144	Fuel Trap 1	16	350000	7	Other
4146	Fuel Valves	18	350000	7	Other
4155	PT 100 Module MAX31865	27	350000	7	Other
4156	PT 100	28	350000	7	Other
4157	Load Cell 200Kg	29	350000	7	Other
4158	Load Cell 40Kg	30	350000	7	Other
4159	Load Cell module HX711	31	350000	7	Other
4162	LCD 20x4	34	350000	7	Other
4163	I2C Module	35	350000	7	Other
4165	Yagi antenna 2450MHz & 900 MHz (01 each)	37	350000	7	Parts
4229	Camera and Gimbal	19	350000	7	Machinery/Equipment
4234	Rasberry Pi 4 & Accessories	24	350000	7	Other
4235	Winch Motor Tmax ATW 3000	25	350000	7	Machinery/Equipment
4259	Electromagnet axially applied variable magnetic field of flux density (1580 Gauss) & 24mm radius	23	350000	7	Parts
4260	Flanges Al 6061 T4 100mm thick billet with wire cutting	24	350000	7	Parts
4261	Used magnetron	25	350000	7	Parts
4267	Magnetrons 1.25kW, 2.45 GHz	5	350000	7	Parts
4268	PCB for circulator biasing	6	350000	7	Parts
4269	flanges for waveguide transition	7	350000	7	Parts
6097	18U rack and trays with integration	1	350000	7	Parts
6098	Profi interface cable with PC (30 ft.)	2	350000	7	Parts
6099	Profi interface cable with controller (54 ft.)	3	350000	7	Parts
6101	Chassis & power supply ad-on for fanless IPC	5	350000	7	Parts
6102	Logitech K400 PLUS Wireless Touch Keyboard	6	350000	7	Parts
6135	Saitek Logitech x56 HOTAS	26	350000	7	Parts
6136	Flight controller Pixhawk 2.1 + Here GNSS	27	350000	7	Parts
6140	L9R FRSky Receiver 2.4GHz	31	350000	7	Parts
6144	Electrical Distribution Box	35	350000	7	Parts
6152	Cooling Fans + Brackets (6 inch)	43	350000	7	Parts
6153	Cooling Fans + Brackets (4 inch)	44	350000	7	Parts
6156	Antenna Stands	47	350000	7	Parts
6157	Power cable 2.5mm, 3 core	48	350000	7	Parts
6158	MCU-Power supply 960mm power cable	49	350000	7	Parts
6159	MCU-USB 900mm power cable	50	350000	7	Parts
6160	MCU-Ethernet Port 900mm power cable	51	350000	7	Parts
7083	PX1-C415 SBC WINSYSTEMS	1	300000	7	Tools / Test Equipment
6161	Rugged Cat7 Ethernet Cable (1200mm)	52	350000	7	Parts
6162	Power cable (I/P 220V AC, 900mm, O/P DC, 1800mm)	53	350000	7	Parts
6163	18U casing	54	350000	7	Parts
6164	Trays (02 large, 02 small)	55	350000	7	Parts
6165	Wheels (set of 04)	56	350000	7	Parts
6665	5V Adapter	170	350000	7	Parts
6166	USB to RS232 cable (length 1m)	57	350000	7	Parts
6170	AOC 9E1H LED Monitor	61	350000	7	Parts
6171	TPLink Archer T1U AC450 Wireless Nano USB Adapter	62	350000	7	Parts
6172	A4Tech Styler FG1010 Keyboard	63	350000	7	Parts
6181	HDMI converter	9	350000	7	Parts
6183	Power supply 30V, 10A	11	350000	7	Parts
6184	Dead weights	12	350000	7	Parts
6195	Power cords	23	350000	7	Parts
6216	Rugged cat 6 Ethernet cable	44	350000	7	Parts
6217	AC power cord 2 pin	45	350000	7	Parts
6243	Latches	14	350000	7	Parts
6244	Handles	15	350000	7	Parts
6245	Dampers	16	350000	7	Parts
6247	Wheels (set of 4)	18	350000	7	Parts
6248	GPS Receiver UBLOX	19	350000	7	Parts
6256	Emax Simon ESC 30A	27	350000	7	Parts
6257	Eachine battery strap	28	350000	7	Parts
6259	Antenna stand arms	30	350000	7	Parts
6262	Power Inverter DC 12V to 220V 500W with LCD display	33	350000	7	Parts
6263	Quadcopter frame	34	350000	7	Parts
6264	Emax MT2213 935kv 3-4S Brushless motor (CW)	35	350000	7	Parts
6265	Emax MT2213 935kv 3-4S Brushless motor (CCW)	36	350000	7	Parts
6269	MicroSD Memory Card	3	350000	7	Other
6277	Stand for profitracker	11	350000	7	Parts
6303	Casing for jammer	19	350000	7	Parts
6304	5.8 GHz Antenna PCB	1	350000	7	Parts
6320	RF Cable	3	350000	7	Parts
6323	Gigabit Ethernet to USB 3.0 cable	6	350000	7	Parts
6330	10GBase-T SFP+Copper RJ-45 30m Transceiver module for FS switches	1	350000	7	Parts
6349	Acrylic casing for GPS circuit	14	350000	7	Parts
6350	Wheels for Director	15	350000	7	Parts
6367	Acrylic Casing	10	350000	7	Other
6368	Step up Transformer 	11	350000	7	Parts
6369	Step Down Transformer	12	350000	7	Parts
6370	Power Cables for Transformer	13	350000	7	Parts
5397	Overall design and manufacturing of Mechanical Enclosure of DVBS interface unit	1	200000	7	Parts
5528	Overall design and manufacturing of Mechanical Enclosure of DVBS interface unit	1	200000	7	Parts
5399	Overall design and manufacturing of Mechanical Enclosure of DVBS interface unit	1	200000	7	Parts
7429	8D7C15F19PN Circular MIL Spec Connector 19P Size 15 JAM Nut PIN Receptacle	1	200000	7	Parts
7430	8D7C13F98SN 10 position Circular connector Receptacle Female Sockets solder 	2	200000	7	Parts
7431	8D515F19SN Circular MIL Spec Connector 19P Size 15 JAM Nut PIN Receptacle, Female	3	200000	7	Parts
7432	D38999/26WG35SN-US Back shell of 8D521W35SN	4	200000	7	Parts
6910	8D7C11F05PN Conn Circular PIN 5 POS Solder ST Jam Nut 5 Terminal Nut 1 port	2	200000	7	Parts
6911	8D7C19F32PN Conn Circular PIN 32 POS Solder ST Jam Nut 32 Terminal Nut 1 port	3	200000	7	Parts
6912	8D521W35SN (KTKK1186A) Conn Circular PIN 10 POS Solder ST Jam Nut 10 Terminal Nut 1 port	4	200000	7	Parts
6914	8D511W98SN (KTKK1181A) Circular MIL Connector	6	200000	7	Parts
8503	Safety Vault	1	450000	7	Tools / Test Equipment
7680	Aver EVC-350 Video Conferencing Multipoint Unit (1+3)	1	250000	7	Machinery / Equipment
6915	RJFTV7 1N RJ45 I/O 	7	200000	7	Parts
6916	MS3471L14-4P 4 PIN male panel mount 	1	200000	7	Parts
6917	MS3475L14-4S 4 PIN female cable mount 	2	200000	7	Parts
6918	M85049/51-1-14N back shell for cable mount	3	200000	7	Parts
4337	[VEAP031] Mounting Kit Onboard 900MHz\n- Autopilot Harness\n- GPS Antenna SSMA\n- RF Antenna\n- Antenna Extension Cable (25cm)	2	350000	7	Other
4339	[VEAP045] Veronte Autopilot Damping System\nAutopilot mounting with foam vibration isolation\n	4	350000	7	Other
4345	[VECS021] MCS Control Station\nMultimedia control station including high brightness dual touch screen with antiglare treatment for outdoor use. Embedded Windows-based computer and power unit for on-field operations\n	10	350000	7	Other
4347	[SESN001] Micro-RADAR Altimeter\nHigh precision upto 500m, 350g, 4W, 7-32V, CAN Bus connection\n	12	350000	7	Other
7575	LED	1	250000	7	Office Equipment
7435	ADAM-4561-CE 1-port Isolated USB to RS-232/422/485 Converter 	1	200000	7	Parts
7436	ADAM-4521-AE Addressable RS-422/485 to RS-232 Converter 	2	200000	7	Parts
7437	ADAM-4520I-AE Wide-Temp RS-232 to RS-422/485 Converter 	3	200000	7	Parts
7438	ADAM-4510s Data Acquisition Modules Isolated Digital I/O	4	200000	7	Parts
7439	PCIE-1612B-AE PCI Express	1	200000	7	Parts
7440	PCIE-1622B-BE PCI Express	2	200000	7	Parts
7441	BB-9PCDT RS-232 Data Tap	3	200000	7	Parts
7442	PCIE-1612B-AE 4-port RS-232/422/485 PCIe Comm. Card (OPT4A is included)	1	200000	7	Parts
7443	OPT8H-AE 1m Male DB-62 to 8x Male DB-9 Cable	2	200000	7	Parts
7444	OPT8J-AE 1m Male DB-78 to 8x Male DB-9 Cable	3	200000	7	Parts
6931	PCIE-1622C-AE 8-port RS-232/422/485 with isolation	1	200000	7	Parts
7445	6- Layers FR-4 Board, TIN plated, UL certified board, outer layer 1oz Copper, Inner layer 1oz Copper, Electrical testin	1	200000	7	Parts
7446	8D521W35SN 79 Way Cable Mount MIL Spec Circular Connector Plug, Socket Contacts, Shell Size 21, Screw Coupling	2	200000	7	Parts
4338	[SERF005] MH 900MHz Amplifier Kit\nUp to 25W power output when used with Veronte Autopilot embedded radio. Up to 10W power output when used with QAM16 video modulation 12-14db Typical Tx. Power gain, 8dB (+-) 1dB Rx gain, 24-30VDC. SMA female jack Input an	3	350000	7	Other
4340	[SEVA021] Mode S transponder with ADS-B Out-Ping200Sr\nPing200Sr is  a compact 80 gram, 250W FCC approved remote mount Mode S transponder with ADS-B Out\n- Replies to Mode C and Mode S radar interrogations\n- Transmits ADS-B on 1090MHz Extended Squitter\n- Me	5	350000	7	Other
4341	[VETR002] Veronte Tracker V2\nHigh precision tracking antenna system for long range operations\nCompatible with dual antenna (directional + omnidirectional) Veronte tracker antenna kits or custom antennas. Includes:\n- Motion platform\n- PCS connection cable\n	6	350000	7	Other
4343	[SERF005] MH 900MHz Amplifier Kit\nUp to 25W power output when used with Veronte Autopilot embedded radio.\nUp to 10W power output when used with QAM16 video modulation\n12-14db Typical Tx Power gain, 8dB (+-) 1dB Rx gain, 24-30VDC. SMA female jack Input and	8	350000	7	Other
4344	[VETR003] Veronte Tracker 900MHz Antenna Kit\n-902-928 MHz Yagi Antenna -14dB-30° Vertical Beam Width- 30° Horizontal Beam Width- 50 Ohm Impedance – 1.8 kg -1090mm- Aluminum -902-928 MHz Omnidirectional Antenna- dBi- 44° Vertical Beam Width- 360° Horizonta	9	350000	7	Other
4346	[SRSV017] Veronte HIL Simulator\nVeronte HIL Simulator license for enabling the connectivity between Veronte Autopilot and Xplane simulator.Includes: Generic simulation model for training and testing. Custom Simulation model and autopilot configuration can	11	350000	7	Other
7447	8D511W98SN Conn Circular SKT 6 POS Crimp ST Cable Mount 6 Terminal 1 Port	3	200000	7	Parts
7448	44M30-03-4-03N Multi Deck Rotary Switches Gray hill	4	200000	7	Parts
7449	1217861-1 Conn QC Tab 0.250 Solder	5	200000	7	Parts
7578	Alphabet Letter	4	250000	7	Other
7577	LED Frame	3	250000	7	Other
7576	Glossy Sheet	2	250000	7	Other
7581	Ceiling	7	250000	7	Other
7582	Monogram	8	250000	7	Other
7450	0154001.DRTL Fuse Brd Mnt 1A 125VAC/VDC 25MD	6	200000	7	Parts
6932	Circular MIL Spec Connector 19 Position recept (Male)	1	200000	7	Parts
6933	Circular MIL Spec Connector 19 Position recept (Female)	2	200000	7	Parts
6934	Circular MIL Spec Connector 19 Position recept (Male) without Cable Clamp	3	200000	7	Parts
6935	Circular MIL Spec Connector 19 Position recept (Female) without Cable Clamp	4	200000	7	Parts
7459	Convertors Rs422	3	200000	7	Parts
7462	Router TP link for NIU2	6	200000	7	Parts
7463	Step down Transformer 3000W	7	200000	7	Parts
7464	SSD 120GB 	8	200000	7	Parts
7465	Computer old Pentium-III	9	200000	7	Parts
7476	SSD 256 GB NVMe M.2 for systems	4	200000	7	Parts
7478	Raspberry PI 4 4GB Ram	6	200000	7	Machinery / Equipment
7479	Raspberry PI 4 accessories 	7	200000	7	Parts
7481	GPS with Antenna (used)	1	200000	7	Parts
7482	Synchro to Digital Converters (used)	2	200000	7	Parts
7483	Mini Centronics connector 20 pin 	3	200000	7	Parts
8372	RESISTORS	31	450000	2	Other
7484	Terminal Board Connector Dual Orange 10 pin	4	200000	7	Parts
7485	Terminal Board Connector Dual Orange 6 pin	5	200000	7	Parts
7486	Data cable 24AWG 10 pair 30m	6	200000	7	Parts
6947	32 GB USB Kingston	10	200000	7	Parts
6948	Cables and connectors	11	200000	7	Parts
7467	Desktop HP 400 G5 ci7 8700& LED	11	200000	7	Machinery / Equipment
7474	Laptop HP Elite book 850 Bag	2	200000	7	Other
8491	CCTV Security System for ROV Design Lab with Video Recording Mechanism	5	300000	7	Machinery / Equipment
7620	Paper Shreder	2	300000	7	Machinery / Equipment
7460	Laptop core2 due old	4	200000	7	Machinery / Equipment
8428	COFDM RX SPU	18	350000	7	Parts
7473	Laptop HP Elite book 850 G5 ci7 840 8th 8GB RAM, 512 SSD 	1	200000	7	Machinery / Equipment
6939	Canon G9X Mark II	2	200000	7	Machinery / Equipment
6538	Skycam VHF telecommand module	25	350000	7	Parts
4363	Hoisting Mechanism (anchor assembly, lifting assembly base plates, nuts & bolts)	6	350000	7	Parts
6408	Metal Rack based Steel Docking for GPS Jammer Electronics	1	350000	7	Other
6409	Ruggedized Outer Composite Casing with Elastomeric Shock Absorber mounts for Docking Unit	2	350000	7	Other
6411	S-Glass Fairing and Mount for folded dipole antenna-Gain 4dBI LENGTH<=15inches	1	350000	7	Parts
6412	Folded Diplole Antenna-Gain 4Dbi. LENGTH<=15inches	2	350000	7	Parts
6413	Transmitter Airborne Unit	1	350000	7	Machinery/Equipment
6406	RF Equalization BP Filter for enhancing Amplified Signal Quality	2	350000	7	Parts
4386	P220-RXI, Engine Mount for P220, Net Jet, GSU, Mini GSU, BMS,USB interface, Airspeed tube, Temp sensor/Probe, Adapter telemetri, Filter, Tube, Cable set, ECU V12 and Cable set	1	350000	7	Other
7698	Workstation Chairs	2	300000	7	Furniture
4387	Wireless module with patch antenna 1.4 ETH in ETH out 1W Range 30km	1	350000	7	Parts
4388	DS26-P professional vaccum degassing system 26L	2	350000	7	Parts
4400	FCPM-6000RC Intg. Frequency Counter & Power Meter, Connector Type: N/BNC	1	350000	7	Parts
4401	Electric Vehicle Driver \nRated operating input voltages: 200~400V DC Cont. output: 80A, max current limit: 240A\nModel: EVD-20KL	1	350000	7	Machinery/Equipment
4402	Electric Vehicle Driver \nRated operating input voltages: 200~400V DC Cont. output: 110A, max current limit: 330A\nModel: EVD-30KL	2	350000	7	Machinery/Equipment
4403	Electric Vehicle Driver \nRated operating input voltages: 200~400V DC Cont. output: 160A, max current limit: 480A\nModel: EVD-45KL	3	350000	7	Machinery/Equipment
4404	Keyboards	4	350000	7	Parts
6417	Echo 236FE Fanless Mini PC\nIntel Core i5-7600T Processor\nMini PCI Express Intel Wireless N 7260 2.4GHz 8GB DDR4 SO-DIMM, 500 GB HDD, PCIe x 16 slot	1	350000	7	Parts
6426	ZHL -10W+ 2G+ High Power Amplifier, 800 – 2000 MHz, 50?, connector type: SMA	1	350000	7	Parts
6435	Ettus USRP GPSDO	30	350000	7	Parts
6436	NI-X310- SDS102 USRP X310 (Kintex-410T) FPGA, 2 channels, 10 Gig &PCIe Bus	1	350000	7	Parts
6505	Camera Module v1.3	55	350000	7	Parts
6437	NI-UBX160-SDS103 UBX 160 USRP Daughterboard, (10MHz-6 GHz, 160 MHz BW)	2	350000	7	Parts
5576	Scaffolding with accessories	1	200000	7	Tools / Test Equipment
7507	150 meter Fiber cable with 02 Media converter	8	250000	7	Other
7503	EMS ROOM Alert 3E PoE with Digital Temperature Sensore	4	250000	7	Machinery / Equipment
7501	Workstation (for backup)	2	250000	7	Office Equipment
7500	Lenovo Thinkstation P500 work station	1	250000	7	Office Equipment
7504	Matrix 42U Rack 600	5	250000	7	Other
4547	ROTAX Spacer Kit Assy, 85 MM (Sky Leader)	8	350000	7	Parts
4412	Solenoid Valve(2-1/2” Max 16 Bar)	5	350000	7	Parts
4413	Solenoid Valve(1/2” Max 50 Bar)	6	350000	7	Parts
4414	Solenoid Valve(2” Max 16 Bar)	7	350000	7	Parts
4415	Pressure Sensor	8	350000	7	Parts
4418	[FWRB-120-S-4P-OB]FWRB-120-S-Fixed Wing Bundle 441lbs(20KG Parachute )	1	350000	7	Parts
4421	CSD3245-1600\nMaterial: TTVG-1600 \nSize: ?32.0 x 4.5mm\nIncluding tooling cost	1	350000	7	Parts
4422	Coaxial Termination TERM-50W-183N+ 50W, DC-18 GHz	2	350000	7	Parts
4423	FPGA Xilinx Spartan S7 EK-S7-SP701-G	3	350000	7	Parts
4424	Coaxial Circulator D3C2327N-2, 3 ports, power rating 200W avg power, 2.30-2.70 GHz	4	350000	7	Parts
4427	WR340 Waveguide high power load \nFrequency range: 2.2-3.3 GHz, VSWR: 1.2, Max Average power: 2500W, peak power: 800KW, length: 508mm, Flange: FDP26, AI	1	350000	7	Parts
4428	Waveguide launcher, BJ-26NS	2	350000	7	Parts
6439	Part No. 783346-01\nPCle Interface Kit for USRP X3xx series (Desktop)	1	350000	7	Parts
6440	Yagi 15 dBi 1575 MHz \nAC1.5 13G	1	350000	7	Parts
6441	Omni 6 dBi 2.4-2.483 GHz 	2	350000	7	Parts
6442	Part No. 785887-01\nUSRP B200 Mini-I with enclosure (1x1, 70 MHz – 6 GHz) – Ettus Research	1	350000	7	Parts
6443	Model No. RP-H717-TRB-IP65\n7U 17” rack mount LCD with high brightness 1000 cd/m2, 1280x1024 (SXGA), Signal Input: VGA + DVI-D, TRB: e-resistive touch screen w/ USB controller, IP65 NEMA4 Dust and water sealed front protection 	1	350000	7	Parts
6445	UBX-160 USRP Daughterboard\n(10 MHz – 6 GHz, 160 MHz BW)	2	350000	7	Parts
6446	Power Amplifier 100W\nZHL -100W -272+ 	1	350000	7	Parts
4461	Raspberry Pi I4	4	350000	7	Parts
676	Mounting hings	6	350000	2	Parts
6447	Power Amplifier 30W\nZHL -30W -252S+ 	2	350000	7	Parts
6448	Mean Well HLG-480H-30A	1	350000	7	Parts
6449	Mean Well HLG-240H-12A 	2	350000	7	Parts
8453	Aluminum walls (top and ground) size: 9x7x8 feet Sheets 2mm on each side	12	350000	7	Parts
8308	Aluminum stock (500*200*40)mm	4	350000	2	Parts
4426	Transverse Probe for Koshava 5 and Koshava USB passive probe (for use in high \nmagnetic fields), 4 ranges (2,5mT, 25,0mT, 250mT, 2500mT) with temperature sensor  (active area 1mm x 2mm) Accuracy: 0.3% (DC) / 2% (AC)\nProbe electronics placed in the cable e	6	350000	7	Parts
4407	P1000 Pro	1	350000	7	Other
8583	XC9536 VQ44AMM0333   A1277072A 10I	2	200000	2	Parts
8584	54LS245 Buffers IC DIP	3	200000	2	Parts
6416	S-Glass Fairing & Mount for 100W Antenna \nGain 4dBi length <=15 inches 	1	350000	7	Parts
8581	Supply & Installation of PVC Ducting Cable laying & Termination	8	250000	3	Equipment Installation
8508	Circuit Braker installation	1	450000	7	Machinery / Equipment
8608	UPS libert ITA KVA/9KW UPS with batteries, Cables and installation	29	250000	7	Machinery / Equipment
8607	Purchase of LED, HDMI cable converter,wood work	28	250000	7	Other
8606	Purchasing of ANPR pole,fiber Shade, Misc equipment	27	250000	7	Other
8605	Purchase of Bullet Camera with housing	26	250000	7	Other
8604	TVs in C&CC	25	250000	7	Other
6451	Standstativ IsoLOG 3D\nHeavy tripod for IsoLOG 3D	2	350000	7	Parts
6452	Elevating Tripod for IsoLOG 3D Antennas Min height: 1.75m, Max height 4m, Color: black	3	350000	7	Parts
6453	N-Cable 10m\n10m Low-loss-N-Cable, both sides N-male, waterproof	4	350000	7	Parts
6455	UBBV2 – 40Db (Low noise pre-amplifier external low noise pre-amplifier, 40Db (1MHz – 10 GHz) incl. SMA tool, SMA F/F Adapter, USB, Cleaning Brush	2	350000	7	Parts
6514	Ruggedized 12 dBi helical antenna & matching hardware ATPS 1200	1	350000	7	Parts
6515	ATPS 1200 system interface power supplies and cable and combiners.	2	350000	7	Parts
6454	Spectran V5 Command Center\n(9 KHz (1Hz) – 20 GHz)\nReal Time RF Command Center\nSpectrum including internal 20 Db preamplifier (option 020), OmniLOG 70600 antenna, carrying case with rolls, accessories, gross weight: 34 kg. Dimension carton: 74x54x62 cm Spe	1	350000	7	Machinery/Equipment
6456	Bi-Axial Antenna Positioner 180º AZ and 40º EL, Pay load: max. 65kg, max. 2.4m antenna, accuracy 0.2º including desktop controller 	1	350000	7	Machinery/Equipment
6459	‘DATA’ RS232 Interface	4	350000	7	Parts
6461	‘Ma-50’ MASTTOP up to 5’-Mast	6	350000	7	Parts
6462	B12 (PT-T2) ANTENNA-MOUNT	7	350000	7	Parts
6466	ACP2.4-24G	1	350000	7	Parts
6467	ACL-1750/2400	2	350000	7	Parts
6468	ZHL-100W-272+	1	350000	7	Parts
6469	ZHL-30W-252-S+	2	350000	7	Parts
6470	ZHL-50W-63+	3	350000	7	Parts
6471	ZHL-20W-13+	4	350000	7	Parts
6472	ZSWA4-63DR+	5	350000	7	Parts
6473	VAT-15W2+	6	350000	7	Parts
6474	VAT-20+	7	350000	7	Parts
6475	LCDR6U15-04\n6U Tiltable rack mount ready monitor	1	350000	7	Parts
6476	BBHA 9120E\nBroadband Horn Antenna\n0.5 – 6 GHz, N connector	1	350000	7	Parts
6477	X540-T2 10G NIC Card INTEL 	1	350000	7	Parts
8562	Chair Interwood	3	200000	7	Furniture
8582	XC95144 PQ100AMM0905   F1292754A 10I	1	200000	2	Parts
6478	SFP-10G-T 10G Copper to SFP+ Adapters	2	350000	7	Parts
6485	Power Supply HLG-80H-24	9	350000	7	Parts
6486	Power Supply HLG-320H-30	10	350000	7	Parts
6487	Power Supply HLG-600H-42	11	350000	7	Parts
6488	Power Supply HLG-480H-30	12	350000	7	Parts
6489	Meanwell HLG-240H-24 	13	350000	7	Parts
6490	BW-40N100W+\nFixed Atten/N/40dB 100W RoHS	14	350000	7	Parts
6491	BW-30N100W+ \nFixed Atten/N/30dB 100W RoHS	15	350000	7	Parts
6492	ZY-JKFZ-TDS004 UAV Detection RADAR	1	350000	7	Machinery/Equipment
4455	CSD3245-1600\nMaterial: TTVG-1600 32*4.5 mm\n2. Tooling Cost	1	350000	7	Parts
4457	Wave Guide Termination (340WMPL1200)	1	350000	7	Parts
4458	High Speed Cooling Fan for magnetrons	1	350000	7	Parts
4460	N52 Magnets	3	350000	7	Parts
4470	Pneumatic actuator of 2.5m length and 250 x 250 mm end area	1	350000	7	Other
4473	Compressor & Launcher cylinder	1	350000	7	Other
4487	24V 11 pin 220V AC Relay	15	350000	7	Parts
4488	11pin relay base	16	350000	7	Other
4491	Power Supply	19	350000	7	Other
4492	DB Panel	20	350000	7	Other
4498	HMI Casing	26	350000	7	Parts
4499	10 Ampere Power Breaker	27	350000	7	Other
4503	Hinges for Launcher	31	350000	7	Parts
4505	Linear Guides for launcher	2	350000	7	Other
4507	Crimping Tool	4	350000	7	Other
4508	Protect UAV/ Skygraphics AG \nDUM-0-00-0-000 Circular 40.0 S Light (27 gm / sq.m) including: Slider, Stabilizer, Inner Sleeve, Pilot Chute\n	1	350000	7	Other
4509	DUM-0-00-0-000 Spring box Nano incl. pilot chute Spring based pilot chute deployment box	2	350000	7	Other
4510	DUM-0-00-0-000 Set suspension lines One set of suspension lines (containing 4 lines, each of 2.5 m) with loops attachment at line ends	3	350000	7	Other
4511	DUM-0-00-0-000 Parachute container	4	350000	7	Other
4513	1630 Protector Transport Case (Pelican Case)	6	350000	7	Other
4540	1 Channel IFM/DFD Reciever (Frequency: 2-18 GHz, Dynamic ranges: 65 dB)	1	350000	7	Parts
4542	Intell Ethernet Server Adapter I350-T4	3	350000	7	Parts
4543	21.5" Light sensor Digital Outdoor Sunlight Readable LCD Open frame monitor	4	350000	7	Parts
4544	In Flight Variable Pitch Three Blades Propeller	5	350000	7	Parts
4545	Hydraulic Governor for ROTAX 914 UL	6	350000	7	Parts
4546	Spinner Cone in Carbon 305x350	7	350000	7	Parts
4465	PC with DB9 Connector	8	350000	7	Parts
4548	Push Pull Cable for Hydraulic Governor	9	350000	7	Parts
4559	Industrial Test Bench: Processor I7 10700, Motherboard: ASUS ROG Stric Z490-H\n2 x RAM: ADATA Premier 16 GB Internal Storage: Samsung 970 EVO Plus 250 GB Power \nSupply, 4U chasis	10	350000	7	Machinery/Equipment
6498	Antenna Stand	48	350000	7	Parts
6499	Voltage Controlled Oscillator ZX95 -1300+	49	350000	7	Parts
6503	433 MHz 5W RF Power Amplifier	53	350000	7	Parts
6504	Raspberry Pi 3B+ kit	54	350000	7	Parts
6516	Ruggedized ATPS-1200 stable platform 	3	350000	7	Parts
6517	Ruggedized servo Actuators for ATPS 1200	4	350000	7	Parts
6518	ATPS 1200 Pseudo Mono pulse (PMP) system	5	350000	7	Parts
7676	Circuit Breaker, 32 A Hager	5	250000	7	Parts
7677	Cicuit Breaker, 32 A Clopal	6	250000	7	Parts
6519	ATPS 1200 Tracker with 0.5 degree accuracy	6	350000	7	Machinery/Equipment
6520	Ruggedized Ground control station (GCS) with external I/O interfaces	7	350000	7	Machinery/Equipment
6521	Ground data receiver with preamplifier and signal conditioning hardware	8	350000	7	Parts
6522	GCS powers supplies, daylight video display with DVR	9	350000	7	Parts
6524	Power amplifier heat sink and coupler	11	350000	7	Parts
4541	PCI Express 4-port RS-232, RS-422, RS-485 Serial Interface	2	350000	7	Parts
6794	1915233 GREEN POWER CONNECTOR RECPT TERM BLOCK HDR 4POS VERT 5.08MM	1	200000	2	Parts
6797	826646-5 TCK TMS TDI TDO RST, 5V RX TX GND RST 5 POSITIONS HEADER	4	200000	2	Parts
6525	Coupler and tripod mounting interface for ATPS-1200	12	350000	7	Parts
6526	Slip ring electrical commutator interfaces for ATPS-1200	13	350000	7	Parts
6527	Slip Dual-axis motor driver ATPS-1200	14	350000	7	Parts
6528	Slip Electrical transformer and power hub ATPS-1200	15	350000	7	Parts
6529	Test wideband jamming amplifier 20W/1.2-1.3 GHz	16	350000	7	Parts
6530	Test Data emulation terminal interface	17	350000	7	Parts
6531	Data decoder interface for GPS/Audio datalink	18	350000	7	Parts
6532	Terminal emulation signal integrator hub	19	350000	7	Parts
6533	Skycam Uav System Airframe	20	350000	7	Parts
6534	SKYCAM GCS	21	350000	7	Parts
6535	Skycam Power Train & Motor Controller	22	350000	7	Parts
6536	Skycam Flight Control System	23	350000	7	Parts
6539	Skycam UHF Telecommand Module	26	350000	7	Parts
6540	UHF Telecommand Test Amplifier	27	350000	7	Parts
6541	Heavy Lift Quadcopter Airframe with motors	28	350000	7	Parts
6542	Heavy Lift Quadcopter Flight Control System	29	350000	7	Parts
6543	Active GPS Antenna	31	350000	7	Parts
6544	BIAS TEE Modules	32	350000	7	Parts
6545	18-20 dB Low noise Amplifier	33	350000	7	Parts
6549	DC Power Supply 5/3.3V	37	350000	7	Parts
6550	IMAX Battery Charger	38	350000	7	Parts
6553	Low Battery Buzzer	41	350000	7	Parts
6559	Charging Terminal leads for UAV batteries	64	350000	7	Parts
6564	Timer circuit	69	350000	7	Parts
6565	12V 5A Power Supply	70	350000	7	Parts
6568	Transmitter with case (Taranis QX7) 	73	350000	7	Parts
6569	OrangeRx Open LRS 433MHz 9 Channel Receiver	74	350000	7	Parts
6574	Motor Speed Controller	79	350000	7	Parts
6575	BLDC Motors	80	350000	7	Parts
6577	Orientation indication LED	82	350000	7	Parts
6578	Power Distribution Board	83	350000	7	Parts
6579	Pixhawk-1 Flight Controller with GPS	84	350000	7	Parts
6585	Arduino Uno	90	350000	7	Parts
6588	Pixhawk 1.0	93	350000	7	Parts
6589	Power supply circuit 12V, 5A	94	350000	7	Parts
6594	Yagi antenna 	99	350000	7	Parts
6597	Balun	102	350000	7	Parts
6601	9V Batteries	106	350000	7	Parts
6610	Power Plugs	115	350000	7	Other
6612	Pixhawk 2.1 The Cube Autopilot	117	350000	7	Parts
6613	Here GNSS module for Pixhawk 2.1	118	350000	7	Parts
6614	Pixhawk 2.1 Cable set	119	350000	7	Parts
6615	Mauch 075 – HS Current Sensor 200A LV 2-6S	120	350000	7	Parts
6616	Mauch 076 – HS Current Sensor 200A HV 4-14S	121	350000	7	Parts
6617	Mauch 083 – 2-6s backup BEC for Pixhawk 2.1	122	350000	7	Parts
6618	Pixhawk 2.1 GPS1 Port Cable – 45 cm long	123	350000	7	Parts
6619	Power Amplifier (1-1000 MHz 3W)	124	350000	7	Parts
6620	Power Amplifier (433 MHz 5W)	125	350000	7	Parts
6621	Fixed Power Attenuator	126	350000	7	Parts
6622	Eachine TS5828L 5.8 GHz Transmitter	127	350000	7	Parts
6623	Turbo wing 5.8 GHz 8dBi RHCP whip antenna for VTx 	128	350000	7	Parts
6624	Eachine ROTG 01 5.8 GHz FPV Receiver	129	350000	7	Parts
6625	Runcam Swift 2 HD FPV Camera with wifi	130	350000	7	Parts
6626	Runcam OSD cable	131	350000	7	Parts
6630	Taranis X9D (mode 2) with X8R 2.4 GHz	135	350000	7	Parts
6631	Thrust stand	136	350000	7	Parts
6632	Pixhawk 1.0 Power Module	137	350000	7	Parts
6633	Pixhawk shock absorber	138	350000	7	Parts
6635	Mini on-screen display	140	350000	7	Parts
6636	Orange 915 MHz Transmitter/Receiver module Radio	141	350000	7	Parts
6637	FRSky X8R 8/16 Ch Telemetry Receiver 2.4 Ghz	142	350000	7	Parts
6638	Hack RF SDR \n1 MHz to 6 GHz operating frequency	143	350000	7	Parts
6641	Attenuator F/F 6dB converter SMA	146	350000	7	Parts
6645	Buck converter	150	350000	7	Parts
6646	DC Block BLK 18 -S+	151	350000	7	Parts
6647	50 Ohm RF Termination ANN E -50+	152	350000	7	Parts
8770	Installation of Sea Water Capacity 20,000 GPD Reverse Osmosis System (SWRO) Plant Including all Components of Reverse Osmosis Plant	1	300000	3	Equipment Installation
8732	Xilinx XC9572 10PQ100I Controller	2	200000	7	Parts
8731	Xilinx XC95144 10PQ100I Controller	1	200000	7	Parts
8733	Xilinx  XC9536 5VQ44C Controller	3	200000	7	Parts
8734	Soldring Wire 400gm	4	200000	2	Parts
8735	Soldring Paste AMTECH	5	200000	2	Parts
8730	Synchro Convertors	1	200000	7	Parts
8586	410-248 ZEDBOARD ZYNQ-7000	1	200000	7	Parts
8696	Waveguide Magnetron Launcher	33	350000	7	Parts
8685	Wax	22	350000	2	Raw Material
6649	Yagi antenna stand/holder	154	350000	7	Parts
6652	Propeller guards	157	350000	7	Parts
6653	Voltage Controlled Oscillator (Signal Generator, SMA connectors and amplifier)	158	350000	7	Parts
6656	Acrylic box for jammer circuit	161	350000	7	Parts
6658	Power Modules XL4015 cc/cv	163	350000	7	Parts
6660	Power Attenuator BW -N20W50+\nHigh Power Handling 50, Excellent VSWR, 1.30 type, Wide frequency range, DC to 18 GHz	165	350000	7	Parts
6661	433 MHz Transmission module	166	350000	7	Parts
6676	Line Attenuator BW S3W2+ 3dB	181	350000	7	Parts
6677	Voltage Controlled Oscillator ZX95 -1750W+ 	182	350000	7	Parts
6678	Voltage Controlled Oscillator ZX95 -1300+	183	350000	7	Parts
6679	DC Attenuator	184	350000	7	Parts
6680	Folded Dipole Antenna \nGain 4dBi, length <15 inches at 1.2GHz	185	350000	7	Parts
6681	RF Equivalization Filter	186	350000	7	Parts
6682	Power Amplifier ZHL -10W+ 2G+\n43dB gain, Fmin 800 MHz & Fmax 2000 MHz	187	350000	7	Parts
6687	NXP RF Transistor	192	350000	7	Parts
6699	Tarot 680 Pro parts Carbon Fiber Pattern Canopy TL2851	204	350000	7	Parts
6700	Tarot 680 Pro TL68P00 6-axis Carbon Fiber Folding frame	205	350000	7	Parts
6701	Mauch Battery Eliminator Circuit	206	350000	7	Parts
6702	Hobbywing XRotor 40A APAC Brushless ESC-2 6S	207	350000	7	Parts
6703	Tarrot 6S 380KV 4008 4108 Brushless Motor for RC Drone	208	350000	7	Parts
6711	Acrylic box	216	350000	7	Parts
6717	BIASTEE modules	222	350000	7	Parts
6718	Storage boxes local	223	350000	7	Parts
6721	Power Attenuators UNAT -4+, -6+,-7+, -8+ 	226	350000	7	Parts
6723	915MHz Telemetry Module	228	350000	7	Parts
6724	Yagi Antenna AC 1.5-9G / 1575 MHz	229	350000	7	Parts
6725	PPM encoder	230	350000	7	Parts
6726	SimonK ESC 30Amp	231	350000	7	Parts
6729	5V Chargers	234	350000	7	Parts
6735	NE555 Timer IC	240	350000	7	Parts
2139	MIC-3022CE 3U SYSTEM OF MIC-3022 W/ CPCI PSU, LEGAC, MIC-3329C1-D1E MIC-3329 W/ E3845 4G RAM DUAL SLOT ROHS, MIC-3955A1-S1E MIC-3955 4PORT DB44 RS-232/422/485 FIO (Qty 06 Cards in System), SQF-S25M8-512G-SAE SQF 2.5 SATA SSD 830 512G MLC (-40~85°C), 968QW7PROE Win Pro 7 Emb x 32/64 ORY PEI 42C-00021, AGS-CTOS-SYS-A Standard Assembly+FunctionalTesting+Software	1	200000	7	Machinery / Equipment
6736	TL082CP IC & UA741 IC	241	350000	7	Parts
6742	Terminal Blocks	247	350000	7	Parts
2195	MIC-3329C1-D1E MIC-3329 W/ E3845 4G RAM DUAL SLOT ROHS	3	200000	7	Parts
8779	EDM RF Adaptors	1	350000	3	Fabrication
8711	Safety Instructions posters printing (size A1)	46	350000	3	Fabrication
8791	Standoff EDM	10	350000	3	Fabrication
8714	Stationery & misc expense (Box files, Panaflex for Expo, printer repair / refill, cleaning material & habitability charges for lab, locks for labs)	49	350000	2	Other
8856	H13 metal stock base for machining (310mmx62.5mmx110mm)	1	350000	2	Raw Material
8780	Copper Electrode for EDM	2	350000	2	Raw Material
8783	XT60 connectors	3	350000	7	Other
8784	2 dp port cables and 1 mouse	4	350000	7	Office Equipment
8785	Spring	5	350000	7	Raw Material
8660	Top Adapter (130mmx90mmx90mm)	1	350000	7	Parts
8665	WR340 Flanges (150mm x 110mm x 15mm)	5	350000	7	Parts
8680	Color pigment (KG)	18	350000	2	Raw Material
8788	MS stock for manufacturing	7	350000	2	Raw Material
8786	Aluminum stock side plate	6	350000	7	Raw Material
8790	Braking strap	9	350000	7	Parts
8789	MS rod	8	350000	2	Raw Material
6748	N type female panel module	253	350000	7	Parts
6749	5, 12 and 15V Power supply	254	350000	7	Parts
6752	Video Balan	257	350000	7	Parts
6753	Video anti-jamming equipment	258	350000	7	Parts
6755	Battery Chargers 12V, 6A and 10A	260	350000	7	Parts
6756	SMA fixed Attenuators 	261	350000	7	Parts
6761	1-1000MHz 3W Amplifiers	266	350000	7	Parts
6768	Acrlyic plastic housing	273	350000	7	Parts
6774	12V 60Ah Batteries with one month warranty	279	350000	7	Parts
6786	Antenna stands	291	350000	7	Parts
6787	S Glass Fairing and Mount for Folded Dipole Antennas	292	350000	7	Parts
8394	High Power Operational Amplifier PSUs and Power supplies	5	350000	7	Parts
8405	3D printing of disconnect mount	7	350000	7	Parts
8416	20 Watt Ruggedized Power Amplifier	10	350000	7	Parts
8417	DV Recorders for airborne/ground video	11	350000	7	Parts
8418	ATPS-1000 Radio Telemetry OSD interface board and GPS	12	350000	7	Parts
8420	Ruggedized ATPS-1000 Antenna Tracking System Controller	13	350000	7	Parts
8421	ATPS-1000 Upgraded Servi actuators for helical antenna	14	350000	7	Parts
8427	COFDM RX LNA	17	350000	7	Parts
8429	COFDM Tx Signal Generator	19	350000	7	Parts
8430	COFDM RX Signal Decoder	20	350000	7	Parts
8431	COFDM Power Amplifier	21	350000	7	Parts
8433	Power Amplifier  casing, connectors, heat sink with EMI/EMC sheilding	22	350000	7	Parts
8434	COFDM Tx Pre Amplifier	23	350000	7	Parts
8435	COFDM Tx Signal Coder	24	350000	7	Parts
8894	JR16WP-7S(71) / JR16WP-7S(31) CIRCULAR CONNECTOR PLUG IP67 7 POS 7 PIN FEMALE	2	200000	7	Parts
8895	JR16WR-7P(71) / JR16WR-7P(31) CIRCULAR CONNECTOR PLUG IP67 7 POS 7 PIN MALE	3	200000	7	Parts
1649	Electronic Components (Digikey/Mouser items: resistors,cables,connectors)	4	350000	2	Parts
8883	Aluminum EDM Machining charges	28	350000	3	Fabrication
8885	EDM charges for sharp corners	30	350000	3	Fabrication
8887	Granite slabs (anti slip lines and beveling half round edges for stair cases)	1	300000	3	Building Modification
8888	Riser for slabs	2	300000	3	Building Modification
8889	Stair landing	3	300000	3	Building Modification
8890	Skirting	4	300000	3	Building Modification
8892	Material for installation	5	300000	3	Building Modification
8825	Operational Amplifiers - Op Amps Low Power Dual OP AMP 100 dB	18	350000	7	Raw Material
8824	Silicon RF Capacitors / thin Film 250V 1pF Tol 0.1pF	17	350000	7	Raw Material
8823	Thin Film Resistors - SMD 1/10W 50 Ohm 75%V .1%	16	350000	7	Raw Material
8822	RF Inductors - SMD 17.5 nH Unshld 5% 7A 2mOhms	15	350000	7	Raw Material
8821	Silicon RF Capacitors / thin Film 250volts 33pF 1%	14	350000	7	Raw Material
8881	Aluminum Stock Size for machining (165mmx100mmx171mm)	26	350000	7	Raw Material
8886	Fire protection covers for Avid Plasma	31	350000	7	Other
8884	Material stock for waveguide	29	350000	7	Raw Material
8882	Copper Electrode material	27	350000	7	Raw Material
1643	130BYGH3318 Stepper motor, torque 50Nm and Stepper Driver  Match with Nema 42 stepper motor	1	350000	7	Tools / Test Equipment
1648	Power Supply PCB T65825816	3	350000	7	Tools / Test Equipment
8436	COFDM  Rx  casing, connectors, heat sink with EMI/EMC sheilding	25	350000	7	Parts
8438	COFDM TX  casing, connectors, heat sink with EMI/EMC sheilding	27	350000	7	Parts
4601	DLE 120 Engine with 180 watt Alternator 	1	350000	7	Parts
4602	DLE 120 CC Starter Complete Kit  W/ESC/Motor	2	350000	7	Parts
4604	NGK CM6 CM6 spark plugs	4	350000	7	Parts
4605	26x10 Prop	5	350000	7	Parts
4606	28x10 Prop	6	350000	7	Parts
4615	Motor Tmotor U13 II KV65	13	350000	7	Parts
4617	Propeller Tmotor G32x11 CF Tmotor G30 x 10.5 CF	15	350000	7	Parts
8896	JR16WR-7S(71) / JR16WR-7S(31) CIRCULAR CONNECTOR PLUG IP67 7 POS 7 PIN FEMALE	4	200000	7	Parts
8897	JR16WP-10P(71) CIRCULAR CONNECTOR PLUG IP67 10 POS 10 PIN MALE	5	200000	7	Parts
8902	JR16WP-10S(71) CIRCULAR CONNECTOR PLUG IP67 10 POS 10 PIN FEMALE	6	200000	7	Parts
8903	JR16WR-10P(71) CIRCULAR CONNECTOR PLUG IP67 10 POS 10 PIN MALE	7	200000	7	Parts
4618	Cube orange standard set (ADS-B Carrier P/N : HX4-06159)	16	350000	7	Parts
4619	Here 3 GNSS	17	350000	7	Parts
4620	Pixhawk Cable Set	18	350000	7	Parts
4621	"Remove Before Flight" Tags	19	350000	7	Parts
4622	DP420 NS Bonding Adhesive	20	350000	7	Parts
4623	Mixing nozzle	21	350000	7	Parts
4624	Screens 21.5" 1000nits for GCS	22	350000	7	Parts
4629	PDB (Power distribution board onboard AV)	4	350000	7	Parts
4661	UBEC Hobbywing 10A	1	350000	7	Parts
4662	Connectors AS150, XT90, XT60	2	350000	7	Parts
4663	Temperature sensor SKYRC Temperature Sensor Probe	3	350000	7	Parts
4664	Digital Airspeed Sensor MS4525DO	4	350000	7	Parts
4665	Current Sensor Mauch PL200	5	350000	7	Parts
4666	Sensor HUB	6	350000	7	Parts
4667	Power Board	7	350000	7	Parts
4668	Connectors (Mauch Connectors)	8	350000	7	Parts
4672	Battery 16000mAh 6S Tattu 30C	12	350000	7	Parts
4673	Battery 5000mAh 4S 50C	13	350000	7	Parts
4674	VTOL Wiring (Onboard AV)	14	350000	7	Parts
4677	Panel Mount Keyboard with Touchpad	1	350000	7	Other
739	Glue Gun	68	350000	7	Other
4678	Voltage converter:30V to 19V 5A	2	350000	7	Parts
4679	Voltage converter:30V to 5V 3A	3	350000	7	Parts
4680	Voltage converter:30V to 12V 5A	4	350000	7	Parts
4683	Throttle	7	350000	7	Parts
4685	IPC:Intel NUC 8 Core i3/Asus Vivo mini	9	350000	7	Parts
4686	7s,10P, Lithium Ion Battery pack	10	350000	7	Parts
4699	Arduino Nano	23	350000	7	Other
4705	DLE 120 Engine with 180 watt Alternator	1	350000	7	Parts
4706	DLE 120 CC Starter Complete Kit  W/ESC/Motor	2	350000	7	Parts
4707	DLE 120 CC CDI/Ignition Module	3	350000	7	Parts
4712	Cube orange standard set (ADS-B Carrier P/N : HX4-06159)	8	350000	7	Parts
4713	Here 3 GNSS	9	350000	7	Parts
4714	Pixhawk Cable Set	10	350000	7	Machinery/Equipment
4716	UAV EFI Kit	12	350000	7	Parts
4670	Hall Affect Joy Stick	10	350000	7	Parts
4607	PRP Spinner for DLE120CC (2 Blade)	7	350000	7	Parts
6790	ZHL-30 W-252-S+\nBROADBAND PML/SMA/RoHS	1	350000	7	Parts
6791	ZHL-10W-2G+\nBROADBAND PML/SMA/RoHS	2	350000	7	Parts
8422	Ruggedized ground control station (GCS) with OSD Display & external I/O interface	15	350000	7	Parts
8387	Video Transmitter with data encoder	2	350000	7	Machinery / Equipment
8414	Ruggedized 12 dBi helical antenna plus system interfaces, power supplies and cables	9	350000	7	Parts
8408	Pilot Pivot (point of view) video and joystick console for onboard control of camera and gimbal	8	350000	7	Parts
8399	High gain patch Antenna system cables interface and power supplies	6	350000	7	Parts
8386	System cables, power supplies interface video down link antenna and HD Camera	1	350000	7	Parts
8391	Receiver with display	4	350000	7	Machinery / Equipment
8388	Antenna Tracking System	3	350000	7	Parts
8437	Omni Antenna	26	350000	7	Parts
8893	JR16WP-7P(71) / JR16WR-7S(31) CIRCULAR CONNECTOR PLUG IP67 7 POS 7 PIN MALE	1	200000	7	Parts
8904	JR16WR-10S(71) CIRCULAR CONNECTOR PLUG IP67 10 POS 10 PIN FEMALE	8	200000	7	Parts
8906	JR16WCC-8(71) CIRCULAR CONNECTOR STRAIN RLF JR16 08MM BACK SHELL	9	200000	7	Parts
8907	JR16WCC-10(71) CIRCULAR CONNECTOR STRAIN RLF JR16 10MM BACK SHELL	10	200000	7	Parts
8908	PYB15-Q24-S12-H-T DC/DC CONVERTER 12V 15W	11	200000	7	Parts
8909	LM2596T-5.0/LF03 IC REG BUCK 5V 3A TO 220-5 (24vdc change 5 volts for CCT )	12	200000	7	Parts
8910	LM2596T-3.3/NOPB IC REG BUCK 3.3V 3A for microcontroller	13	200000	7	Parts
8911	EEE-FT1V681UP CAP ALUM 680UF 20% 35V SMD	14	200000	7	Parts
8912	EEE-FC1V221P CAP ALUM 220UF 20% 35V SMD	15	200000	7	Parts
8880	Internet charges	25	350000	3	Other
8879	Adaptor	24	350000	7	Office Equipment
8878	Buck convertor, current sensors, passive components, terminal box, zener diodes, fuses	23	350000	7	Raw Material
8918	Fiber 4 core 5M Coil	2	300000	7	Parts
8919	01 x 24 Port Manageable Gigabit Switch	3	300000	7	IT Equipment
8920	9u and 4u Double Section Rackmount (with accessories)	4	300000	7	IT Equipment
8921	Cat - 6 Patch Cord	5	300000	7	IT Equipment
8922	Telecom DB Box 50 Pair	6	300000	7	IT Equipment
8923	Telephone UG Cable 20 Pair Coil	7	300000	7	Parts
8917	Media Converter Gigabit	1	300000	7	IT Equipment
8924	UG Pipe (with accessories)	8	300000	7	Parts
4730	Electric carbon fiber cutter scissors (with delivery)	4	350000	7	Other
8934	HDMI cable (Ugreen)	9	350000	7	Parts
8933	Female Socket Adopter	8	350000	7	Parts
4723	IP Gimbal  Camera	1	350000	7	Machinery/Equipment
4727	Engine Auto Start	1	350000	7	Other
4728	Launcher Drill Press	2	350000	7	Other
4732	Fuel pump	6	350000	7	Other
4754	Aviation Grade Honeycomb(2MM)	7	350000	7	Other
4762	IR Sensors (for launch speed measurement)	5	350000	7	Other
4763	Marlin Data Logger	6	350000	7	Other
4768	100 watt Amplifiers Mini circuit\nZHL-100W-272+ 	1	350000	7	Other
4867	Winch with long shaft 1 ton	12	350000	7	Parts
4884	SBC Motherboard	29	350000	7	Other
4900	MG996R servo motor	45	350000	7	Other
4907	box for breaker	52	350000	7	Parts
4918	Launcher Hoist Mounting	63	350000	7	Parts
4919	High Torque Servos (MKS HBL 599 & MKS HBL 380)	64	350000	7	Parts
4920	SKU CR21-1.0-32C - Chronos 2.1-HD High Speed Camera (RAM: 32GB, Image Sensor:\n Colour) § Lens: Fujian 35mm f/1.7 prime lens § Lens Adapter: Canon EF to C – FotodioX & FotodioX Nikon F Lens Adapter to CMount § Trigger Switch cable § eSATA to SATA Cable	1	350000	7	Machinery/Equipment
4921	Antenna FM Unit Cavity Backed Spiral Antenna\nPol.: RHCP	2	350000	7	Parts
8330	MCIL 12F//DLSA-F//4.2m Cable//DLSA-M//MCIL 12M MCBH12F+DLSA-F//1.3m pigtail	4	300000	7	Tools / Test Equipment
8334	MCIL8F//MCDLS-F//4m Cable// MCDLS-F//MCIL6M MCBH8F//1.3m pigtail	5	300000	7	Tools / Test Equipment
8340	Clamp mount parts	7	300000	7	Parts
6962	Chain Wrench	6	200000	7	Tools / Test Equipment
6961	Screw Set	5	200000	7	Tools / Test Equipment
6964	Heat Gun	8	200000	7	Tools / Test Equipment
7228	Lunch at 31st Creek Battalion Sujawal 29 Nov 19	9	350000	2	Other
4922	Sonatest D-70 Digital Flaw Detector, Battery,\nCharger, Charger, Mains Cable, User Guide,\nCalibration Certificate, Certificate of\nConformance, Carry Bag, Utility & USB cable,\nDisplay Window Cover, Ultrasonic Couplant –\nBNC Connectors.	3	350000	7	Parts
8940	Outsource Machining (Wire Cutting)	14	350000	3	Other
8939	Aluminum Servo Plate Mounting	13	350000	7	Parts
8938	Aluminum Wing Box	12	350000	7	Parts
8553	Reallocation, restoration, connection & drain system of wter test tank	6	300000	7	Other
7091	Shifting of HT electrical poles and electric work	1	300000	7	Other
6173	Heatsink machining for 8W PA	1	350000	2	Fabrication
6261	Refreshments for Gharo trials	32	350000	2	Meals/Refreshments
6838	Printer toner refill	7	350000	2	Other
8937	Aluminum Hinge Stock	11	350000	7	Raw Material
8936	MS Stock for balancing (165mm X 100mm X 50mm)	10	350000	7	Raw Material
8932	Mat-Black Spray Paint	7	350000	7	Chemical
8930	Cat-6 cable (3-meter each)	5	350000	7	Parts
8929	Network Switch	4	350000	7	IT Equipment
8928	Voltmeter	3	350000	7	Test / Measuring Equipment
8927	Battery Terminals	2	350000	7	Parts
8926	Battery Wires	1	350000	7	Parts
8931	HD Video Streaming Router	6	350000	7	IT Equipment
8963	Bend parts	37	350000	7	Parts
8950	Power cord	24	350000	7	Parts
8949	5-Pin Mil-Spec connector	23	350000	7	Parts
8948	2-Pin Mil-Spec connector	22	350000	7	Parts
8947	USB Hub	21	350000	7	Parts
8942	Parachute Hatch Lock (90mm X 25mm X 30mm)	16	350000	7	Parts
8943	Feul Pump	17	350000	7	Machinery / Equipment
8944	Ethernet cable	18	350000	7	Parts
8945	Mouse	19	350000	7	IT Equipment
8946	LCD (AOC 9E1H)	20	350000	7	Machinery / Equipment
8941	Tail Box	15	350000	7	Parts
6960	Digital Vernier Caliper	4	200000	7	Tools / Test Equipment
6963	Spanner fix	7	200000	7	Tools / Test Equipment
6965	Pliers	9	200000	7	Tools / Test Equipment
4924	Sonatest RDT2510 10Mhz 1/4” Delay Line\nProbe, Microdot Connector.	5	350000	7	Parts
4927	149093 GB Inspection Systems CBU Universal Test Block - Mild Steel	8	350000	7	Parts
4928	OPT8J-AE – 1m Male DB-78 to 8 x Male DB-9 Cable	9	350000	7	Parts
4929	Orange lamp	1	350000	7	Parts
4931	Green lamp	3	350000	7	Parts
4936	Ammeter/Voltmeter	8	350000	7	Parts
4938	Battery	10	350000	7	Parts
4939	Bus bar/Terminal Block	11	350000	7	Parts
4951	SSD Samsung 870 EVO 500 GB	23	350000	7	Parts
4952	SSD enclosure	24	350000	7	Parts
4957	Wifi card for Avionics	29	350000	7	Parts
4961	Gannet Landing gear	33	350000	7	Parts
4966	Magnets N48SH	1	350000	7	Parts
8514	Conn N Plug Straight Crimp	1	350000	2	Other
4993	Pressure Sensor	22	350000	7	Other
4996	Power Supply	25	350000	7	Other
4997	Keyboard	26	350000	7	Other
4998	30V to 19V 10A (Powering IPC)	27	350000	7	Other
4999	30V to 5V 3A	28	350000	7	Other
738	Hot air Gun	67	350000	7	Other
5000	30V to 12V 10A (Poweing Display, Fans)	29	350000	7	Other
5001	UBEC	30	350000	7	Other
5002	BMS	31	350000	7	Other
5003	Battery Indicator	32	350000	7	Other
5008	Batteries	37	350000	7	Other
5009	Battery Holder	38	350000	7	Other
5010	Nickel Strip	39	350000	7	Other
5012	Misc. (PDB, Custom Electronics)	41	350000	7	Other
5018	PCB Boards	47	350000	7	Other
5027	Shear testing Jig	1	350000	7	Other
5028	Radiator	1	350000	7	Parts
5029	Overflow bottle with cap	2	350000	7	Parts
5030	Shock mount set	3	350000	7	Parts
5031	Oil Tube M4M-2 12X19	4	350000	7	Parts
5032	Air Filter	5	350000	7	Parts
5033	Oil Radiator set matric	6	350000	7	Parts
5034	Capacitor 22000 MYF 25 V	7	350000	7	Parts
5035	Rev. Counter 0-7000 Rpm	8	350000	7	Parts
5037	Avmap Engine Box	10	350000	7	Parts
5039	Termination (TCB SERIES) TCB50-200SN-T A-INFO	1	350000	7	Parts
5048	Carriage hard point 	1	350000	7	Parts
5058	Separation plates	11	350000	7	Parts
5060	Carriage hard point 	13	350000	7	Parts
5061	3rd porthole pneumatic fittings	14	350000	7	Parts
5064	Solenoid Valve (1/2" Valve)	17	350000	7	Parts
5065	Pressure valves	18	350000	7	Parts
6995	MS3470W14-12P Conn Circular PIN 12 POS Crimp ST Flange Mount (Male)	1	200000	7	Parts
6996	MS3476W14-12S Conn Circular SKT 12 POS Crimp ST Cable Mount 12	2	200000	7	Parts
6997	M85049/52-1-14W Cable Clamp	3	200000	7	Parts
5093	Temperature & Humidity Sensors (HIH6031-021-001)	1	350000	7	Parts
5094	Linear Hall Effect Sensor (DRV5057)	2	350000	7	Parts
5095	Linear Hall Effect Sensor (DRV5053)	3	350000	7	Parts
5096	Magnetoresistive Sensor (SS39ET)	4	350000	7	Parts
5097	Magnetoresistive Sensor(SS59ET)	5	350000	7	Parts
5110	Raspberry Pi 4GB	18	350000	7	Parts
5114	Electronic Box	22	350000	7	Parts
6792	Part No. MMRF5014HR5\nRF MOSFET HEMT 50V 350mA 2.5GHz 18dB 125W NI-360-2SB	1	350000	7	Parts
6999	15 pin D type connectors with covers	2	200000	7	Parts
7001	9 pin D type male & Female	4	200000	7	Parts
7003	12 VDC supply 3 Amp with cable	6	200000	7	Parts
7005	Molex Cables & connectors 6 pins	8	200000	7	Parts
7006	Molex Cables & connectors 8 pins	9	200000	7	Parts
7007	Molex Cables & connectors 2 pins	10	200000	7	Parts
7008	IDE 60 pin connectors Male and Female	11	200000	7	Parts
5134	Carriage assembly 	2	350000	7	Parts
5135	Hitec servos (HS- 1100wp)	1	350000	7	Parts
5141	Model: 110BYGH4201 Stepper Motor	1	350000	7	Machinery/Equipment
5142	Model: 2DM2260 DRIVE	2	350000	7	Parts
5143	Spindle 11000 rpm OEM: WEGSTR	3	350000	7	Parts
5144	Universal Joint UJ-SS1500x18K (BELDEN)	4	350000	7	Parts
5145	Universal Joint UJ-SS1000x12K (BELDEN)	5	350000	7	Parts
5166	Stepper motor	20	350000	7	Parts
5167	Stepper motor Driver(A4988)	21	350000	7	Parts
5168	Stepper motor Driver(TB6600)	22	350000	7	Parts
5169	PSU(24V dc, 480 W)	23	350000	7	Parts
5170	Arduino Uno	24	350000	7	Parts
5172	IMU(MPU9250)	26	350000	7	Parts
5174	Raspberry pi 4(4 GB RAM)	28	350000	7	Parts
95	Functional replacement of Antenna & Microwave unit- power supply 1 (E104) - ELINT. Input: 220 VAC. Output: DC +12V/13.35A, +5V/2.5A, -5V/2.5A, +15V/1A, +24V/3A	1	200000	7	Parts
519	Paper Rim	46	350000	2	Parts
98	Functional replacement of Antenna & Microwave unit- power supply 1 (E105) - ELINT. Input: 220 VAC. Output: DC +12V/7A, +5V/2A, -5V/4.5A, +5V/5A (for digital circuits), -15V/1.25A	2	200000	7	Parts
6793	SP5050 Modified Pan-Tilt 	1	350000	7	Parts
7011	Mechanical Enclosure of DVBS Interface Unit, (Specifications: Aluminum Alloy, CNC Machined, Internal Alodine, External PU Paint Finish) 	1	200000	7	Parts
5396	NI SBRio 9637	1	200000	7	Parts
5185	Servos (D941 TW HITECH SERVOS)	1	350000	7	Machinery/Equipment
5192	Starting Battery	8	350000	7	Parts
5193	Manufacturing of Rotax Test bench\n-Test Stand Frame\n-Safety Wall\n-Instrumentation Assembly	1	350000	7	Fabrication
147	Battery (48V 200Amp)	6	350000	7	Other
160	Sheet metal (clamp fabrication)	19	350000	7	Machinery / Equipment
164	Syring/Dropper	22	350000	7	Other
2838	UPVC Doors and Windows	5	300000	7	Furniture
2839	Backfiling/ Uplifting of required area	6	300000	7	Other
5328	Buoyancy hull for left and right (length 1300 mm x dia 355.6) with pressure  seal ends both sides	2	300000	7	Other
5327	Electronics/ battery pressure hull (length 2200 mm x dia 457 mm) with pressure sealed flanged	1	300000	7	Machinery / Equipment
5332	Jigs and fixtures of main structure to assemble lab version of prototype ROV	1	300000	7	Machinery / Equipment
5333	 Manufacturing of Base structure/ mount frame of lab version of prototype ROV	2	300000	7	Machinery / Equipment
5568	Pressure hull penetrator for underwater camera connection	4	300000	7	Machinery / Equipment
5567	Electronics rack for housing control circuit inside pressure hull	3	300000	7	Machinery / Equipment
176	Case fans (firebase)	33	350000	7	Parts
5137	Spring test bench	3	350000	7	Parts
177	Keyboard & Mouse Logitech MK345	34	350000	7	Parts
175	PC Case Cooler Master Masterbox Q500L	32	350000	7	Parts
182	PLC Analog I/O Expansion Module	39	350000	7	Parts
191	braking assembly	46	350000	7	Parts
198	safety straps metal mount	49	350000	7	Parts
199	MOTUS2-RUG-EK IMU Rugged Evaluation Kit	1	350000	7	Parts
5197	Control Box	1	350000	7	Parts
286	Mini-GSU Part no: 61161-0000	1	350000	7	Machinery / Equipment
289	Power Supply cable Part no. 41168-0003	2	350000	7	Other
291	Connection Cable part no. 31109-0003	3	350000	7	Other
293	Telemetry adapter Part no: 61108-0070	4	350000	7	Other
302	End Mill Holder 10mm	1	350000	7	Other
303	Flat End Mill 25mm	2	350000	7	Other
304	Flat End Mill 32mm	3	350000	7	Other
247	HDMI Cable	3	300000	7	Parts
249	VGA Y Cable	5	300000	7	Parts
227	carbide end 2 flute mill set	27	350000	7	Tools / Test Equipment
224	Spring collect chuck set 3-20mm	24	350000	7	Tools / Test Equipment
223	Carbide end 4 Flute mill set	23	350000	7	Tools / Test Equipment
220	Flat end mill 20 mm	20	350000	7	Tools / Test Equipment
284	Optical fiber cable	2	300000	7	Parts
246	SPLIT Jointer	2	300000	7	Parts
251	Tibbo DS 100B (RS234/485-Ethernet)	7	300000	7	Parts
219	Flat end mill 16 mm	19	350000	7	Tools / Test Equipment
210	Ball nose 6mm(TNF-060-10M)	10	350000	7	Tools / Test Equipment
208	HSS Drill set 25 Pcs set (HSD-13)	8	350000	7	Tools / Test Equipment
215	Carbide ball nose 4mm (VBB-61-4.0)	15	350000	7	Tools / Test Equipment
214	Carbide ball nose 3mm (VBB61-3.0)	14	350000	7	Tools / Test Equipment
212	Carbide ball nose 2mm (VBB-61-2.0)	12	350000	7	Tools / Test Equipment
221	Insert (NFB060-SM)	21	350000	7	Tools / Test Equipment
216	Insert for finishing	16	350000	7	Tools / Test Equipment
222	Insert for aluminium	22	350000	7	Tools / Test Equipment
225	Insert for aluminium (3PKT 150508R-AL)	25	350000	7	Tools / Test Equipment
305	Ball Nose 8mm	4	350000	7	Other
307	3 way angle milling vise	6	350000	7	Other
308	Drill Chuck 1 to 13mm	7	350000	7	Other
309	Side Lock End Mill holder 25mm	8	350000	7	Other
310	Side Lock End Mill holder 32mm	9	350000	7	Other
311	Simple Type Tool setting stand	10	350000	7	Other
314	End Mill Grinder 4 to 13mm	13	350000	7	Other
321	Lathe Machine tools	1	350000	7	Other
322	Vernier Calliper	2	350000	7	Other
330	650W Corsair Power Supply	10	350000	7	Other
334	700-2700 mHZ 5 dBi Antenna set	12	350000	7	Other
336	Solenoid Valve 1.5 inch 50 bar,24V DC operated	14	350000	7	Other
337	Solenoid Valve 0.5 inch 50 bar, 24V DC operated	15	350000	7	Other
200	Magnetic stand (VMB-70)	1	350000	7	Tools / Test Equipment
101	pennetrater	1	300000	2	Parts
230	Consumable/ spares: High pressure pipe, Air Filter, Activated carbon filter	2	350000	2	Other
102	RF Generator & Accessories Rent (153 days) 1st Aug 2021 to 31st Dec 2021	1	200000	3	Equipment / Tools rental
8969	MOSFET P-CH 30V 50A PPAK SO-8	43	350000	7	Parts
8970	MOSFET N-CH 30V 40A/100A 8PQFN	44	350000	7	Parts
8971	SMA extensions (male to male)	45	350000	7	Parts
8973	Vacuum bagging film (plastic)	46	350000	7	Raw Material
8974	Sealant Tape	47	350000	2	Other
1162	Motus 2-RUG-EK Motus 2 IMU Rugged Evaluation Kit	1	350000	2	Parts
226	Face mill cutter 63mm	26	350000	7	Tools / Test Equipment
218	End mill holder 12 mm	18	350000	7	Tools / Test Equipment
217	Ground parallel	17	350000	7	Tools / Test Equipment
206	Side lock end mill holder 20mm(BT-449)	6	350000	7	Tools / Test Equipment
201	Pull Stud (BT-503)	2	350000	7	Tools / Test Equipment
205	Face mill arbor for 63 mm(BT-152)	5	350000	7	Tools / Test Equipment
202	Dial Test Indicator (VDI-.8A)	3	350000	7	Tools / Test Equipment
207	Clamping kit (CK-16)	7	350000	7	Tools / Test Equipment
178	Wi-Fi Card TPLink TL WN881ND	35	350000	7	Parts
231	Maintenance parts: Activated carbon filter, air filter element, second stage valve plate O-ring, Drain valve spool, O-ring of second stage oil water separator, inlet o-ring, filter bucket o-ring, back pressure valve o-ring, belt	3	350000	2	Other
2661	Electric Main Cable 4C x 50mm	10	300000	7	Machinery / Equipment
338	Power Supply 24V , 20A Power supply for Seimens s7 PLC	16	350000	7	Other
341	Battery 3S 2200 mAh	19	350000	7	Other
2662	LED Flood Light IP-65 for PEB Shed with cable	11	300000	7	Machinery / Equipment
2650	Hand Dryer	6	300000	7	Machinery / Equipment
343	Servo Horn	21	350000	7	Other
2660	Electric Boards, Switchs, lights, Power Sockets breaker etc	9	300000	7	Other
2652	Dewatering/Submercible Pump	7	300000	7	Machinery / Equipment
2653	Vaccum Pump with accessories for tank cleaning	8	300000	7	Other
209	Drill chuck 3-16 MM (INT-16-BT40)	9	350000	7	Tools / Test Equipment
211	Tooling rack stand (VTT-30-40)	11	350000	7	Tools / Test Equipment
213	Z Zero setter (HP-50EM)	13	350000	7	Tools / Test Equipment
204	Touch point sensor (VPS-20B)	4	350000	7	Tools / Test Equipment
256	CAD modeling of inner mechanism and structure (Fine)	2	200000	3	Equipment Repairs & Maintenance
254	Testing services (20 Engg Hrs, 99 Tech Hrs, 48 Eqpt Hrs)	3	200000	3	Consultancy
253	Travel & Logistics (visit by engineer and TCS charges)	2	200000	3	Travel
258	Assembly and Factory Acceptance Test with NRDI Team	4	200000	3	Equipment Repairs & Maintenance
104	RF Generator & Accessories Rent (296 days) till 30 Sep 2021	1	200000	3	Equipment / Tools rental
127	Lug Round 25-8	8	350000	2	Other
128	Heat shrink sleeve Misc. Size RED + Black (ft)	9	350000	2	Other
129	Gas Kit	10	350000	2	Other
130	Screws for casing plate mounting and connector mounting	11	350000	2	Parts
131	Wire Mesh (Roll)	12	350000	2	Other
359	Conn Circular PIN 19 POS Crimp ST Flange Mount 19 Terminal 1 Port MS3470W14-19PW Panel Mounted Male	1	200000	7	Parts
360	Conn Circular SKT 19 POS Crimp ST Cable Mount 19 Terminal 1 PortMS3476W14-19SW Cable Mounted Female	2	200000	7	Parts
361	Strain Relief Clamp 180° 14 Shell Size Cadmium Over Electro less Nickel Aluminum Alloy M85049/52-1-14W Cable Clamp	3	200000	7	Parts
363	Conn Circular PIN 15 POS Crimp ST Flange Mount 15 Terminal 1 Port MS3470W14-15PW Panel Mounted Male	4	200000	7	Parts
365	Conn Circular SKT 15 POS Crimp ST Cable Mount 15 Terminal 1 Port MS3476W14-15SW Cable Mounted Female	5	200000	7	Parts
367	Conn Circular PIN 12 POS Crimp ST Flange Mount 12 Terminal 1 Port MS3470W14-12PW Panel Mounted Male	6	200000	7	Parts
395	Wire Tracer tool	17	200000	7	Tools / Test Equipment
368	Conn Circular SKT 12 POS Crimp ST Cable Mount 12 Terminal 1 Port MS3476W14-12SW Cable Mounted Female	7	200000	7	Parts
369	Conn Circular PIN 8 POS Crimp ST Flange Mount 8 Terminal 1 Port MS3470W16-8PW Panel Mounted Male	8	200000	7	Parts
745	Cable Set 26m 8mm	74	350000	7	Other
229	High pressure air compressor SCW150 150L/ MIN 300 bar 3KW Single phase 220V	1	350000	7	Machinery / Equipment
124	Hour meter	5	350000	7	Machinery / Equipment
121	24V Power supply for VFD panel	2	350000	7	Machinery / Equipment
119	Control box	1	350000	7	Machinery / Equipment
232	1/2" Check valve 50 bar 90 C	4	350000	2	Other
139	Tellies (multiple)	20	350000	2	Other
122	Wire roll (.75 sq.mm)	3	350000	2	Other
123	Casing of coating and machining	4	350000	2	Other
125	Mounting hinges	6	350000	2	Parts
140	GX 16 (2 & 5 Pins) Connector	21	350000	2	Other
126	Lug Round 10-8	7	350000	2	Other
132	U and I type Lugs (packet)	13	350000	2	Other
133	Mil Connector	14	350000	2	Other
134	Cable Tie (Packet)	15	350000	2	Other
135	Soldering wire	16	350000	2	Other
136	GX 16 (2 & 3 Pins) Connector	17	350000	2	Other
137	Glands	18	350000	2	Other
138	Allen Key Screws	19	350000	2	Other
141	Selector 3 & 2 way	22	350000	2	Other
234	Freight/Handling	6	350000	2	Other
233	UFBK 1000 Horizontal	5	350000	2	Other
190	sheet metal machining	45	350000	3	Fabrication
329	PC repair	9	350000	3	Equipment Repairs & Maintenance
276	Optical fiber cable	2	300000	7	Parts
274	Distribution box with dimension 2' , 7" x 1', 10"x5'	8	350000	7	Parts
520	Glue stick	47	350000	2	Other
398	Ethernet Switch	20	200000	7	Machinery / Equipment
387	Impact drill machine with screw driver bits	9	200000	7	Tools / Test Equipment
370	Conn Circular SKT 8 POS Crimp ST Cable Mount 8 Terminal 1 Port MS3476W16-8SW Cable Mounted Female	9	200000	7	Parts
371	Strain Relief Clamp 180° 16 Shell Size Cadmium Over Electro less Nickel Aluminum AlloyM85049/52-1-16W Cable Clamp	10	200000	7	Parts
373	IDE Male connector PDS-150JW RIGHT ANGLE	12	200000	7	Parts
374	IDE Female connector PDS-150JW Strait	13	200000	7	Parts
377	IDE Female connector PDS-150 Right Angle	14	200000	7	Parts
393	N type connector	15	200000	7	Parts
396	LAN Cards	18	200000	7	Machinery / Equipment
420	Distribution Box with deminsion 2',7"x1',10"x5'	9	350000	7	Other
421	GAS500-M / GAS500 Gascolator, - Left Fitting: Male (AN-8, JIC-8) , -Right Fitting: Male (AN-8, JIC-8)	1	350000	7	Parts
7202	Fan filters (6 & 4 inch)	25	350000	7	Other
423	CORTLAND PN@V308C, DESC 1/2" VECTRAN CHAIN W/6-INCH EYES SPLICED ON BOTH ENDS X 100 METERS	2	350000	7	Other
427	25-30 Core wire 0.75mm	3	350000	7	Other
429	Sensors Adapters (Plug Type) 12.3mm Hex	5	350000	7	Other
432	Protective Cover	8	350000	7	Other
391	Threading die set	13	200000	7	Tools / Test Equipment
397	Petinum 4 computers	19	200000	7	Machinery / Equipment
460	Specialized/custom made antenna transportation box (Inner Size: Dia 1200 mm, Height 1208 mm)	1	200000	7	Other
461	Part No RWR-218N, Antenna 2-18 GHz	1	200000	7	Parts
469	RF Power Amplifier 100W PCB fabrication	8	350000	7	Machinery / Equipment
472	Epoxy bonded heat sink for 300 W	10	350000	7	Machinery / Equipment
596	Box file	50	350000	7	Other
614	File lock type	68	350000	7	Other
627	Mast for tracker	5	350000	7	Other
628	Field tents	6	350000	7	Other
650	Beagle Bone Black with accessories	10	200000	7	Other
282	T-200 thruster with ESC	3	300000	7	Parts
281	Optical fiber cable	2	300000	7	Parts
279	Manufacturing of frame and body sructure of miniature for lab testing	1	300000	7	Parts
275	Manufacturing of frame and body structure of prototype ROV	1	300000	7	Machinery / Equipment
278	LIDAR	4	300000	7	Parts
277	Optical fiber cable tester	3	300000	7	Tools / Test Equipment
389	Pipe wrench pliers 12"	11	200000	7	Tools / Test Equipment
390	Grip pliers 12"	12	200000	7	Tools / Test Equipment
392	Bench Grinder Machine	14	200000	7	Tools / Test Equipment
462	Generator 5kW	1	350000	7	Machinery / Equipment
463	Voltage Stabilizer 5kVA	2	350000	7	Machinery / Equipment
439	Air Receiver Tank (HAV-30)	6	350000	7	Parts
434	Refrigerated Air Dryer/50 Hz (KWD 20B)	2	350000	7	Other
435	After Cooler (AT-A10G2)	3	350000	7	Other
403	20A Single Pole	4	350000	7	Machinery / Equipment
402	63A 3 pole	3	350000	7	Machinery / Equipment
9375	3D Scan	13	350000	3	Fabrication
446	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) condition Annealed, finished: cold finished):Ti6AI4V Grade5 ASTM B265	5	350000	2	Other
651	Respberry Pi4 8GB with accessories	11	200000	7	Other
653	Tiva C with accessories	12	200000	7	Other
662	Trolley front spare part	2	350000	7	Parts
813	Viulinx FX: 1.4 G ETH in ETH out 2W Wireless Module With 2 SBUS  ports	1	350000	7	Office Equipment
984	Lux punch control	56	350000	7	Other
986	Watch Maker set	58	350000	7	Other
992	Fluke Clamp Meter (with low pass filter)	64	350000	7	Tools / Test Equipment
631	LCD Monitor (24 Inch)	2	300000	7	Office Equipment
991	Fluke Multimeter ( with low pass filter)	63	350000	7	Tools / Test Equipment
818	00231U GASCOLATOR ACS USA/LA Bottega Dell'aquilone Speed Com Italy	4	350000	7	Tools / Test Equipment
817	UFBK 1000 Horizontal 10mm OD Pisco	3	350000	7	Tools / Test Equipment
993	Fluke Multimeter	65	350000	7	Tools / Test Equipment
999	Bench Power Supply	71	350000	7	Tools / Test Equipment
961	Ingo Tool kit (pliers, screw drivers, wire cutters etc.)	33	350000	7	Tools / Test Equipment
982	Spanner Set (6-32 mm)	54	350000	7	Tools / Test Equipment
988	Rachet Set	60	350000	7	Tools / Test Equipment
7201	AC fans 4inch with grill	24	350000	7	Other
7200	AC fans 6inch with grill	23	350000	7	Other
7197	Industrial power plug (3 prong male & female)	22	350000	7	Parts
998	Tweezer set	70	350000	7	Tools / Test Equipment
7196	Circuit breaker 220V-16A	21	350000	7	Parts
995	Hot Air Gun	67	350000	7	Tools / Test Equipment
990	Monkey plier	62	350000	7	Tools / Test Equipment
975	Industrial plier	47	350000	7	Tools / Test Equipment
994	Wire Stripper	66	350000	7	Tools / Test Equipment
979	Philips Flathead Screw Driver	51	350000	7	Tools / Test Equipment
612	Aplha cutter	66	350000	7	Tools / Test Equipment
978	Philips Crosshead Screw Driver	50	350000	7	Tools / Test Equipment
977	Long Nose plier	49	350000	7	Tools / Test Equipment
997	Glue Gun rods (set)	69	350000	7	Tools / Test Equipment
976	Diagonal Cutter plier	48	350000	7	Tools / Test Equipment
1008	PC Intel core i5 10400, B560 Motherboard, 1TB HDD, 500G SSD, 16GB RAM,23.8" LCD, Casing, Keyboard & mouse included	80	350000	7	Office Equipment
1014	Stair 8x16 feet	6	200000	7	Other
1035	3m Patch Panel 24 port	2	200000	7	Parts
1036	3m Cable Manager	3	200000	7	Parts
1041	RJ45 Connector Boots	7	200000	7	Parts
1051	Carrying case (wooden) for Avionics test bench	1	350000	7	Other
1092	Megaphone for avionics test bench Field Trials	38	350000	7	Other
1114	 B/B AMPL/SMA-N RoHS. ZHL-100W-272+	7	350000	7	Office Equipment
1148	Providing and construction of concreat base for installation of compressor	1	350000	7	Other
1159	Supply with installation of MS shed for compressor as per compressor size and according to the site location along with all respect also with coloring	10	350000	7	Other
448	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) condition Annealed, finished: cold finished)Ti6AI4V Grade5 ASTM B265	6	350000	2	Other
440	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) condition Annealed, finished: cold finished:Ti6AI4V Grade5 ASTM B381	1	350000	2	Other
442	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) condition Annealed, finished: cold finished)Ti6AI4V Grade5 ASTM B381	2	350000	2	Other
615	Manual Calculator	69	350000	7	Other
582	Notice board 3x6 ft	36	350000	7	Office Equipment
1000	De Soldering Pump	72	350000	7	Tools / Test Equipment
1001	Fire Extinguisher	73	350000	7	Machinery / Equipment
963	700W Grinder	35	350000	7	Tools / Test Equipment
964	Drill machine	36	350000	7	Tools / Test Equipment
424	Shipping/Freight	3	350000	3	Other
501	Hiring advertisement Rozee charges for 01 month	29	350000	3	Consultancy
444	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) condition Annealed, finished: cold finished)Ti6AI4V Grade5 ASTM B265	4	350000	2	Other
1286	Industrial PC-i7, 16 gb ram, 256 SSD NUC, dell optiplex, asus vivomini	62	350000	7	Office Equipment
1309	Power supply elint Computer master	2	200000	7	Machinery / Equipment
1310	DVD Rom	3	200000	7	Machinery / Equipment
1311	DDR1	4	200000	7	Machinery / Equipment
1312	Key boards PS2	5	200000	7	Machinery / Equipment
1313	Mouse PS2	6	200000	7	Machinery / Equipment
1318	Patch Pannel	11	200000	7	Machinery / Equipment
1319	Cable manager	12	200000	7	Machinery / Equipment
1020	Tube Binder copper pipe	12	200000	7	Tools / Test Equipment
1027	Drill bit set, Goti set, & Screw driver bit set for cardless machine	19	200000	7	Tools / Test Equipment
1012	Torq Wrench SMA 8no	4	200000	7	Tools / Test Equipment
1021	Vise Portable	13	200000	7	Tools / Test Equipment
1013	Grease GUN	5	200000	7	Tools / Test Equipment
1024	Electronice Cutter Force	16	200000	7	Tools / Test Equipment
1022	Wire Extractor Player	14	200000	7	Tools / Test Equipment
1099	Axe for shrub cleaning during field trials of avionics test bench	44	350000	7	Tools / Test Equipment
1329	FANs 24V	22	200000	7	Machinery / Equipment
1330	SMA, N Type BNC Adapters	23	200000	7	Parts
1363	Supply, installation and Commisioning of Earth pits in mountain area providing consolidated resistance of less than 2 Ohms using complete Copper material such as copper plate 2' x 2', bare copper conductor and earth enhancement compounds complete.	1	200000	7	Other
1382	Wireless keyboard K400 plus	16	350000	7	Office Equipment
1429	PCL-722-BE 144ch TTL Digital I/O Card	2	200000	7	Machinery / Equipment
1430	PCL-10150-1.2E IDC-50 Flat Cable, 1.2m	3	200000	7	Machinery / Equipment
1431	ADAM-3950-AE 50-Pin Flat Cable Terminal, DIN-rail Mount	4	200000	7	Machinery / Equipment
1432	RF Absorber material each piece is 600 x 600 mm or 0.6 x 0.6 mtrs so if you buy 6 pcs you will have a size of 1.2 x 2.4 mtrs which will be 2 square meter part No 3640-50	1	200000	7	Parts
744	Fire Extinguisher	73	350000	7	Other
449	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) Condition Annealed, Finished: Cold Finished: Ti6AI4V Grade5 ASTM B381	1	350000	2	Parts
450	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) Condition Annealed, Finished: Cold Finished) Ti6AI4V Grade5 ASTM B381	2	350000	2	Parts
494	male & female headers	22	350000	2	Parts
451	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) Condition Annealed, Finished: Cold Finished) Ti6AI4V Grade5 ASTM B265	3	350000	2	Parts
458	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) Condition Annealed, Finished: Cold Finished): Ti6AI4V Grade5 ASTM B265	5	350000	2	Parts
443	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) condition Annealed, finished: cold finished)Ti6AI4V Grade5 ASTM B265	3	350000	2	Other
456	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) Condition Annealed, Finished: Cold Finished) Ti6AI4V Grade5 ASTM B265	4	350000	2	Parts
459	Titanium Alloy Sheet (Ti-6AI-4V (Grade-5) Condition Annealed, Finished: Cold Finished) Ti6AI4V Grade5 ASTM B265	6	350000	2	Parts
473	RTC DS3231 + cell	1	350000	2	Parts
672	Control Box 470x940mm	2	350000	2	Parts
675	HOUR METER	5	350000	2	Parts
528	Blue pen	55	350000	2	Other
474	16 GB SD card	2	350000	2	Other
475	SD Card Reader	3	350000	2	Other
476	DHT22 temperature & humidity sensors	4	350000	2	Parts
477	Linear Hall Effect Sensor (DRV5057)	5	350000	2	Parts
478	18 AWG 5m Red, 5m black DC wire	6	350000	2	Other
479	Li-ion 18650 2000 mAH Battery	7	350000	2	Parts
480	Battery holder	8	350000	2	Parts
481	10m nickle wire	9	350000	2	Parts
482	BMS 1s 3A	10	350000	2	Parts
483	PLA filament	11	350000	2	Parts
1108	FieldFox 18 GHz Microwave Analyzer CAT Cable and Antenna Analyzer. N9917A	1	350000	7	Office Equipment
1225	Steel racks	2	350000	7	Other
1006	Internet recharge	78	350000	3	Other
464	Power meter annual calibration from OEM	3	350000	3	Equipment Repairs & Maintenance
465	Jazz Internet Recharge	4	350000	3	Equipment / Tools rental
466	Toner refill for printer	5	350000	3	Equipment Repairs & Maintenance
468	RF posters printing	7	350000	3	Other
510	Internet upgrade to 30Mbps	37	350000	3	Other
511	Internet recharge	38	350000	3	Other
512	Lab paint work	39	350000	3	Building Repairs & Maintenance
513	Poster printing size A2	40	350000	3	Other
507	Power supply repair	35	350000	3	Equipment Repairs & Maintenance
517	Laptop LCD repair	44	350000	3	Equipment Repairs & Maintenance
518	Manual colored printing	45	350000	3	Other
1433	LOW NOISE AMPLIFIER (2-18G) AQUAERO	1	200000	7	Parts
1434	LOW NOISE AMPLIFIER (2-8G) AQUAERO	2	200000	7	Parts
1446	Megger Fluke 1507 Insulation Resistance Multimeter MTDM-32	1	350000	7	Machinery / Equipment
1449	USB-128GB	4	350000	7	Office Equipment
1450	Steel Spring Sheet 253.8*241mm (Heated bed)	5	350000	7	Parts
1451	PEI Layer 253.8*241mm	6	350000	7	Parts
486	Dip 28 IC holder	14	350000	2	Parts
488	0.1uF capacitor	16	350000	2	Parts
489	10k resistor	17	350000	2	Parts
514	keyboard mouse & extension board	41	350000	7	Machinery / Equipment
508	Notice board 3x6 ft	36	350000	7	Other
487	2 pin push Button	15	350000	7	Parts
484	FR4 solder mask single side PCB board fabrication	12	350000	2	Parts
485	At Mega 328P-Dip28  Micro controller	13	350000	2	Parts
502	spray for mounting 3D printed parts	30	350000	2	Chemicals
490	1k resistor	18	350000	2	Parts
491	2n222 BJT Transistor	19	350000	2	Parts
492	2 pin JST connector	20	350000	2	Parts
493	6 pin JST connector	21	350000	2	Parts
495	soldering iron tips	23	350000	2	Parts
496	zip ties	24	350000	2	Parts
497	M3 screws + nuts	25	350000	2	Other
498	soldering flux	26	350000	2	Other
499	Tweezer	27	350000	2	Other
500	Solder wire	28	350000	2	Parts
503	Wegstr bits	31	350000	2	Parts
504	WD 40	32	350000	2	Chemicals
505	Glue sticks	33	350000	2	Other
506	Printer toner & plastic sheet cover	34	350000	2	Other
515	USB hub	42	350000	2	Parts
516	Paper lining for lab cabinets	43	350000	2	Other
686	T Spanner	16	350000	2	Parts
687	Push Button	17	350000	2	Parts
688	LED inidcation	18	350000	2	Parts
689	Selector Switch (3 pole, 1-0-2)	19	350000	2	Parts
690	Selector Switch (2 pole, 1-0-2)	20	350000	2	Parts
1278	Electric Are Plant	54	350000	7	Office Equipment
1230	HP Laser Jet Pro-M4 02d/MPF 135w printer	7	350000	7	Office Equipment
1229	Laptop HP 250 GB Core i5 11th generation 8GB 256 GB SSD 15.6 inches DOS	6	350000	7	Office Equipment
1296	Cuttoff Machine 16" motor 220V BS-8011D	72	350000	7	Machinery / Equipment
1254	AMD Ryzen 5 processor 5600G	31	350000	7	Office Equipment
1251	AOC 24E1H 23.8" FHD 60 Hz IPS Frameless Monitor	28	350000	7	Office Equipment
1250	Logitech M90 USB Mouse	27	350000	7	Office Equipment
1262	Logitech K120 USB Keyboard	39	350000	7	Office Equipment
1263	Logitech M90 USB Mouse	40	350000	7	Office Equipment
1294	Mouse A4 Tech N300	70	350000	7	Office Equipment
706	700W Grinder	35	350000	7	Machinery / Equipment
702	Rivet Nuts (packet)	31	350000	7	Machinery / Equipment
523	Box file	50	350000	7	Office Equipment
655	Trolley front spare part	2	350000	7	Parts
521	Green paper	48	350000	2	Other
522	Colored flags	49	350000	2	Other
752	Internet recharge	78	350000	3	Other
820	EH640TM-S-bus incorporation and firmware upgrade	5	350000	3	Other
821	Freight/Handling	6	350000	3	Other
1453	MK52 heated bed with thermister + Y carriage	7	350000	7	Parts
524	Registers 300 pg	51	350000	2	Other
1454	Motherboard EinsyRambo 1.1b Mainboard For Prusa i3 MK3 + Tinamic TMC2130	8	350000	7	Parts
1455	Prusa i3 MK3 PINDA V2 Auto-leveling Sensor Probe	9	350000	7	Parts
1456	PSU Mean Well 24V 10 A	10	350000	7	Parts
1457	NEMA 17 , 2*LS Stepper Motors	11	350000	7	Parts
1459	24V 4010 Fan	13	350000	7	Parts
1460	GT2 Timing Belts Belt L: 10m, W: 6mm + GT2 pulley T:20*10	14	350000	7	Parts
1461	Idler Pulley SW: 7mm, S: 6	15	350000	7	Parts
1463	Machining (Frame)	17	350000	7	Parts
1466	V6 Metal Hotend Extruder 1.75mm with Cooling Fan	20	350000	7	Parts
1510	EKI-2725-CE 5-port Ind. Unmanaged GbE Switch	2	200000	7	Parts
1511	MIC-3329C1-D1E MIC-3329 W/ E3845 4G RAM DUAL SLOT ROHS	3	200000	7	Parts
1691	96PS-A250WCPC SPS 100-240V 250W W/PFC LF	4	200000	7	Parts
1514	SOURIAU 8D7C13F04PN 04 pin Conn Circular PIN 4 POS Solder ST Jam Nut 4 Terminal Nut 1 port	1	200000	7	Parts
1515	SOURIAU 8D7C11F05PN 05 pin Conn Circular PIN 5 POS Solder ST Jam Nut 5 Terminal Nut 1 port	2	200000	7	Parts
1516	SOURIAU 8D7C13F98PN 10 pin Conn Circular PIN 10 POS Solder ST Jam Nut 10 Terminal Nut 1 port	3	200000	7	Parts
1517	SOURIAU 8D7C13F98SN 10 pin 10 position Circular connector Receptacle Female Sockets solder	4	200000	7	Parts
525	A4 small envelope	52	350000	2	Other
526	Gel pen Blue/Black	53	350000	2	Other
527	Dollar dry board marker (blue)	54	350000	2	Other
529	White correction pen	56	350000	2	Other
530	Writing pad	57	350000	2	Other
531	Tissue box	58	350000	2	Other
532	Air Freshner	59	350000	2	Other
533	Aroma Air Freshner	60	350000	2	Other
534	Power plus spray 600ml	61	350000	2	Other
535	Power plus refill oil	62	350000	2	Chemicals
536	pencils	63	350000	2	Other
538	Tape transparent 2 inch	65	350000	2	Other
540	Face mask	67	350000	2	Other
620	Manufacturing of Die (as per design) for Buoyancy Chamber of Scale down ROV for Testing Purpose.	4	300000	2	Other
753	Printer toner refill & Packing material for VFDs (plastic wrap and tapes)	79	350000	2	Other
537	Tape transparent 1 inch	64	350000	2	Other
670	Control Box 470x700mm	1	350000	2	Parts
907	Casing of coating and machining	4	350000	2	Parts
673	24V Power supply	3	350000	2	Parts
908	HOUR METER	5	350000	2	Parts
910	Mounting hings	6	350000	2	Parts
918	LUG Round 10-8	7	350000	2	Other
904	Control Box 470x700mm	1	350000	2	Parts
905	Control Box 470x940mm	2	350000	2	Parts
919	LUG Round 25-8	8	350000	2	Other
920	Mil Connector	9	350000	2	Other
921	GX 16 (2 & 3 pins) connector	10	350000	2	Other
922	Glands	11	350000	2	Other
923	Allen Key screws	12	350000	2	Other
906	24V Power supply	3	350000	2	Parts
924	Tellies (multiple)	13	350000	2	Other
654	Flexible pipe	1	350000	2	Parts
657	Nylon braking straps	3	350000	2	Other
658	Dead weight hardpoints	4	350000	2	Other
660	Diesel engine oil	5	350000	2	Chemicals
575	Hiring advertisement Rozee charges for 01 month	29	350000	3	Consultancy
583	Internet upgrade to 30Mbps	37	350000	3	Other
584	Internet recharge	38	350000	3	Other
585	Lab paint work	39	350000	3	Building Repairs & Maintenance
586	Posters printing size A2	40	350000	3	Other
746	Storage rack	75	350000	7	Other
717	Vernier Calliper	46	350000	7	Machinery / Equipment
715	Extension wire (4-6 socket, 5m cable)	44	350000	7	Tools / Test Equipment
1358	Optical fiber tester	3	300000	7	Tools / Test Equipment
1321	Light emergency battery	14	200000	7	Tools / Test Equipment
1888	ETC Wireless Combo of keyboard and mouse	10	200000	7	Machinery / Equipment
1977	Base Stand (30 kg MS)	29	350000	7	Parts
8589	2MP IP Camera Hik Vision	11	250000	7	Other
8597	Network switch (08 port)	18	250000	7	Tools / Test Equipment
8592	Network Media converter	14	250000	7	Tools / Test Equipment
714	Drill Bits spare set	43	350000	7	Tools / Test Equipment
708	First Aid Kit	37	350000	7	Other
707	Drill Machine	36	350000	7	Machinery / Equipment
712	Oil bottle	41	350000	2	Other
713	Cutting disc	42	350000	2	Other
7191	Automatic Voltage Regulator (AVR)	19	350000	7	Parts
1428	ACP-4010BP Comprisingof below:\r\nACP-4010BP-00C ACP-4010BP Rev.C W/O PSU (62368)\r\nPS8-500ATX-BB 80+ Bronze PS/2 SPS 500W ATX (FSP) RoHS\r\nPCA-6114P7-0E1E 14Slots PICMGBP,4ISA,6PCI,3PICMG, 1PCI/ISARoHS K\r\nPCA-6029G2-00A2LGA1151 FSBC/VGA/DVI/ Dual GbE LAN/HISA,w/o LPT\r\n1700021831-01 A Cable DVI 29P(F)/2*10P-1.25+G-TEM 30CM\r\n1960050255N001 CL R3 I-LGA1156 S-95W 83*83*56.5mm-SS 12V0.3A P\r\n96MPI5K-3.4-6M11T CORE 3.4G 6M 1151P 4CORE I5-7500\r\nAQD-D4U4GN26-SG 4G DDR4-2666 512MbX8 1.2V SAM\r\n96FD25-S256-TR71  TR 256GB 2.5" SSD SATAIII MLCStandard Assembly + Functional Testing\r\n1702031801 Power Cord BSI 3P 10A 250V 183cm	1	200000	7	Machinery / Equipment
1520	Amphenol RJFTV7 1N 11-33 RJ45 I/O Plugs Plate mount	7	200000	7	Parts
1521	D38999/26WD19SN-US Back shell of 8D515F19SN	8	200000	7	Parts
1593	Stock Material for Avid CNC spindle Plate	27	350000	7	Parts
1599	Junction Box	33	350000	7	Other
1611	IMU MPU 9250 9 axis accelerometer	43	350000	7	Machinery / Equipment
4124	Mouse A4 Tech	9	350000	7	Other
1657	Electric Step UP Transformer 3000W	1	200000	7	Machinery / Equipment
1658	Electric Synchro to Digital Converters USE	2	200000	7	Machinery / Equipment
1662	Electric Ethernet card PCIe	6	200000	7	Parts
5744	Folding chairs	1	350000	7	Furniture
1664	Electric Fan blower for NIU Casing	8	200000	7	Machinery / Equipment
1695	Industrial Turbine Installation Kit (Part# 71105-0070)	2	350000	7	Parts
1696	JetCat Pro Interface (Part# 61168-0010)	3	350000	7	Parts
1698	Engine Airspeed Sensor (Part# 61120-0000)	4	350000	7	Parts
1699	USB Interface (Part# 61109-0010)	5	350000	7	Parts
1700	Battery Management System (Part# 61108-0060)	6	350000	7	Parts
716	Measuring Tape	45	350000	2	Other
751	Wi-Fi Dongle for PC	77	350000	2	Other
694	Screws for Casing plate mounting and Connector Mounting	24	350000	2	Parts
1978	Gigabyte GA B560M DS3H AC	8	350000	7	Parts
725	Spanner Set (6-32 mm)	54	350000	7	Machinery / Equipment
724	Calibrator (Current Source)	53	350000	7	Machinery / Equipment
235	High pressure air vessel horizontal capsule type, capacity: 880L, Size: 32.5 " Dia. And 48" straight length	1	350000	7	Machinery / Equipment
718	Industrial plier	47	350000	7	Machinery / Equipment
705	Tool box for storage	34	350000	7	Tools / Test Equipment
704	Ingco Tool kit (pliers, screw drivers, wire cutters etc.)	33	350000	7	Tools / Test Equipment
747	Office chairs (Masters Aura LBC Black)	76	350000	7	Office Equipment
709	Teflon Tape Set	38	350000	2	Other
711	Grease can	40	350000	2	Other
590	Laptop LCD repair	44	350000	3	Equipment Repairs & Maintenance
591	Manual colored printing	45	350000	3	Other
1879	PROCESSOR  Core  i9 12900k Boxed	1	200000	7	Machinery / Equipment
755	Stands Fabrication, Wooden Floor, Grey Carpet,LCD Stand, Inclusive of Labour & Transport	1	350000	3	Other
729	Watch Maker Set	58	350000	7	Other
727	Lux punch control	56	350000	7	Other
726	L Type Spanner Set  ( 8-24mm)	55	350000	7	Other
680	GX 16 (2 & 3 pins) connector	10	350000	2	Parts
684	GX 16 (2 & 5 pins) connector	14	350000	2	Parts
683	Tellies (multiple)	13	350000	2	Parts
682	Allen Key screws	12	350000	2	Parts
1737	06 x Led lights for NDT extended portion and 06 x light plugs	32	350000	7	Office Equipment
1742	08 x Led lights for NDT lab, wifi dongle, mouse	36	350000	7	Office Equipment
7187	SSR modules DC	18	350000	7	Parts
7186	SSR modules AC	17	350000	7	Parts
7184	Antenna stand arms	15	350000	7	Parts
7178	GPS Tx Rx antenna	11	350000	7	Parts
7177	Attenuator SMA 10 dB	10	350000	7	Parts
7176	Attenuator SMA 7 dB	9	350000	7	Parts
7174	RG-58 SMA(M) connector	8	350000	7	Parts
7172	RG-58 N(M) connector	7	350000	7	Parts
7166	LMR 400 LLPX 30ft Spool	6	350000	7	Parts
7164	LMR 400 N(M)	5	350000	7	Parts
7163	LMR 400 (3m) N(M)-N(M)	4	350000	7	Parts
1778	Aluminum Saw	7	350000	7	Machinery / Equipment
1782	DLE 120 Crank Shaft	11	350000	7	Parts
1787	Panasonic AC Servo Motor with Configuration, installation and Onsite Testing	1	200000	7	Parts
1881	CPU COOLER  Cougar Aqua 360 LIquid Cooler	3	200000	7	Machinery / Equipment
1661	Electric APC UPS 2KVA	5	200000	7	Machinery / Equipment
5457	fsd	1	160000	7	Tools / Test Equipment
1659	Electric Power supply synchro convertors	3	200000	7	Tools / Test Equipment
1718	Weighing Scale	16	350000	7	Tools / Test Equipment
1567	Thread Locker Locktite 242	7	350000	7	Tools / Test Equipment
1709	Spanner	7	350000	7	Tools / Test Equipment
1717	Nose Plier	15	350000	7	Tools / Test Equipment
1824	120mm 220V AC exhaust fan	12	350000	7	Office Equipment
730	precision Screw Driver Set	59	350000	7	Machinery / Equipment
734	Fluke Multimeter ( with low pass filter)	63	350000	7	Machinery / Equipment
733	Monkey plier	62	350000	7	Other
732	T type spanner set (18' long handle)	61	350000	7	Parts
731	Ratchet Set	60	350000	7	Other
685	Selector 3 & 2 way	15	350000	2	Parts
681	Glands	11	350000	2	Parts
679	Mil Connector	9	350000	2	Parts
678	LUG Round 25-8	8	350000	2	Parts
677	LUG Round 10-8	7	350000	2	Parts
674	Casing of coating and machining	4	350000	2	Parts
728	Lux punch power	57	350000	2	Other
1642	Procurement and installation of 2 x fans and 4 x LED lights	4	350000	7	Office Equipment
1579	Manual Antenna Stand	17	350000	7	Parts
1588	HP Laser Jet Pro - M254 dw Printer (Black & White)	23	350000	7	Office Equipment
1602	Screen Casting Device (Chromecast)	35	350000	7	Other
1603	Digital Vernier Caliper 300mm	36	350000	7	Other
1559	Storage Racks	1	350000	7	Furniture
1572	Grease Gun	10	350000	7	Machinery / Equipment
1651	Desktop HP  Ci7  8GB 1TB DVD with Display	1	200000	7	Machinery / Equipment
1656	Electric Logitech MK295 Silent Wireless Keyboard + Mous	6	200000	7	Machinery / Equipment
1653	Electric SSD  for systems (4 pcs+4 niu+2 bk+1 HZ)	3	200000	7	Machinery / Equipment
766	Mountings bolts	6	350000	7	Machinery / Equipment
764	MS Rods	4	350000	7	Machinery / Equipment
762	Brushes for Layup	2	350000	7	Machinery / Equipment
761	Circuit fittings	1	350000	7	Machinery / Equipment
786	2 inch fittings	16	350000	7	Machinery / Equipment
783	2 inch pipe	15	350000	7	Machinery / Equipment
779	Pipe shaft	13	350000	7	Office Equipment
777	Engine Self sport Repair	12	350000	7	Machinery / Equipment
765	Fastness	5	350000	2	Other
793	Mill-Sper Connector 20 pins	20	350000	2	Other
710	Loctite/thread sealant	39	350000	2	Other
691	ON/OFF Selector	21	350000	2	Parts
693	Gas kit (ft)	23	350000	2	Parts
696	U and I type Lugs (packet)	26	350000	2	Parts
697	Wire Roll (0.75 sq.mm)	27	350000	2	Parts
698	Cable Tie (packet)	28	350000	2	Parts
699	Soldering wire	29	350000	2	Tools / Test Equipment
804	RF Connectors PCB mount MMCX Connectors	23	350000	2	Other
1857	PDS-150KB1 150 Pin PDS Connector - Female - Straight	1	200000	7	Parts
1880	MOTHERBOARD Z690 Gigabyte UD AX D4	2	200000	7	Machinery / Equipment
1882	RAM PNY XLR8 DDR4 RGB 3600mhz 16GBx4 64GB	4	200000	7	Machinery / Equipment
1883	SSD PNY CS3030 500GB NVME M.2	5	200000	7	Machinery / Equipment
1884	HDD PNY CS900 960GB Sata SSD	6	200000	7	Machinery / Equipment
1885	GPU PNY XLR8 RTX 3070Ti 8GB GDDR6X 256Bit	7	200000	7	Machinery / Equipment
812	Propellers 1045 CF Propeller pair	28	350000	7	Parts
811	Electronic Speed Controller	27	350000	7	Machinery / Equipment
810	Motors EMAX XT2216-910 KY	26	350000	7	Machinery / Equipment
808	Frame F 450 Quad Frame	25	350000	7	Office Equipment
807	Servos MG996R Metal Gear	24	350000	7	Machinery / Equipment
802	4 mm ID pipe	22	350000	7	Machinery / Equipment
1130	Portable cabin	1	300000	7	Other
1102	FieldZFox 18 GHz Microwave Analyzer CAT Cable and Antenna Analyzer, 1a) Quickcal 1b) Vector Network Analyzer Transmission/reflection 1c)Vector Network Analyzer ful 2-port S-parameters 1d) Rugged phase-stable cable 1c) Calibration Kit, 4-in-1	1	350000	7	Other
1106	ZHL-100W-272+ B/B AMPL / SMA-N RoHS	2	350000	7	Other
797	20 core wire	21	350000	7	Tools / Test Equipment
767	Reducer bush and fittings	7	350000	7	Machinery / Equipment
774	Aluminium axels	10	350000	7	Machinery / Equipment
775	Belt for compressor engine	11	350000	7	Machinery / Equipment
791	Temperature Sensor K type thermo Couple	19	350000	7	Machinery / Equipment
789	Locking circuit with mounting	18	350000	7	Machinery / Equipment
787	Rubber mounts for engine	17	350000	7	Machinery / Equipment
771	Aluminium material for spare wheels	9	350000	7	Machinery / Equipment
768	Rubber for wheels	8	350000	7	Machinery / Equipment
763	Bushes and SS Converter	3	350000	2	Other
780	Lubrication Oil	14	350000	2	Other
833	Engine self start repair	12	350000	3	Equipment Repairs & Maintenance
723	Tailor Scissor	52	350000	7	Office Equipment
722	Philips Flathead Screw Driver	51	350000	7	Machinery / Equipment
721	Philips Crosshead Screw Driver	50	350000	7	Machinery / Equipment
1030	Article number: 743EX BicoLOG 20100E X Active biconical precision antenna, EMC version (20MHz-1GHz) Incl. external preamplifier with power supply, Transport case & SMA tools including external preamp with power supply transport case & SMA tool	1	350000	2	Parts
2114	Logitech M90 USB Mouse	11	350000	7	Office Equipment
1886	PSU Cougar GEX 850 80+ GOLD	8	200000	7	Machinery / Equipment
11488	Sand paper	9	350000	2	Other
1887	CASE Cougar Archon 2 Mesh Black	9	200000	7	Machinery / Equipment
2001	Locking actuator bore 125mm, stroke 100mm	48	350000	7	Parts
1921	Screw Guage Mitutoyo 25-50mm	29	350000	7	Office Equipment
2019	90WMPL225 WR90 Waveguide Medium Power Load 8.2-12.4GHz with Rectangular Waveguide Interface	1	350000	7	Machinery / Equipment
2070	Power Supply	5	350000	7	Parts
1919	LEVER DIAL Tester	27	350000	7	Tools / Test Equipment
2141	SOURIAU 8D7C13F04PN 04 pin Conn Circular PIN 4 POS Solder ST Jam Nut 4 Terminal Nut 1 port	1	200000	7	Parts
2142	SOURIAU 8D7C11F05PN 05 pin Conn Circular PIN 5 POS Solder ST Jam Nut 5 Terminal Nut 1 port	2	200000	7	Parts
2143	SOURIAU 8D7C13F98PN 10 pin Conn Circular PIN 10 POS Solder ST Jam Nut 10 Terminal Nut 1 port	3	200000	7	Parts
2109	SSD Kingston A2000 NVMe Pcle SSD 500GB	6	350000	7	Parts
2144	SOURIAU 8D7C15F19PN 19 pin Circular MIL Spec Connector 19P Size 15 JAM Nut PIN Receptacle	4	200000	7	Parts
2145	SOURIAU 8D7C19F32PN 32 pin Conn Circular PIN 32 POS Solder ST Jam Nut 32 Terminal Nut 1 port	5	200000	7	Parts
2146	D38999/26WD19SN-US Back shell of 8D515F19SN	6	200000	7	Parts
2147	8D513F04SN Circular Connector, Souriau 8D Series, Straight Plug, 4 Contacts, Crimp Socket, Threaded, 13-4	7	200000	7	Parts
2148	8D511F05SN Conn Circular SKT 5 POS Crimp ST Cable Mount 5 Terminal 1 Port	8	200000	7	Parts
2149	8D513F98SN Conn Circular SKT 10 POS Crimp ST Cable Mount 10 Terminal 1 Port	9	200000	7	Parts
2150	8D519F32SN Conn Circular SKT 32 POS Crimp ST Cable Mount 32	10	200000	7	Parts
2151	M85049/38-13N Backshell stright	11	200000	7	Parts
2152	M85049/38-11N Backshell stright	12	200000	7	Parts
2153	M85049/38-13N Backshell stright	13	200000	7	Parts
2154	M85049/38-19N Backshell stright	14	200000	7	Parts
2155	4 Layers PCB 1 OZ Copper, 2mm Tick PCB	15	200000	7	Parts
2156	65" Class The Terrace Full Sun Outdoor QLED 4k Smart TV SAMSUNG QN65LST9TAFXZA	1	350000	7	Machinery / Equipment
7162	Adapter Coaxial Connector N(F)-N(F)	3	350000	7	Parts
7161	RG-142 (1 ft) N(M)-SMA(M)	2	350000	7	Parts
2163	AD9434-500EBZ Data Conversion IC Development Tools 12 Bit 500Msps 1.8V ADC	1	200000	7	Parts
735	Fluke Clamp Meter (with low pass filter)	64	350000	7	Other
2106	Motherboard Asus Prime B550M-A Wifi	3	350000	7	Parts
1976	Intel Core i7 11700 11th Gen. 2.1GHz 25MB Cache	7	350000	7	Parts
1748	Rivet Gun	6	350000	7	Tools / Test Equipment
1561	Oscillating Cutter Multi Tool 300 W for Curved CF sheets	3	350000	7	Tools / Test Equipment
1562	Metric Ball End Allen Wrench 2.5, 3,4,5,6 mm	4	350000	7	Tools / Test Equipment
1117	Jazz Internet recharge	2	350000	3	Other
255	Detailed Engineering Inspection of fine mechanical features	1	200000	3	Equipment Repairs & Maintenance
1174	Painting job (Red oxide and sky blue oil paint kerosine oil, brush and other colour)	11	350000	3	Chemicals
1165	Supply with installation of 1 " GI union	3	350000	2	Other
1168	Supply with installation of 1 " GI Elbow	5	350000	2	Other
1166	Supply with installation of 1" GI barrel nipple	4	350000	2	Other
2107	RAM Corsair Vengeance DDR4 16GB 3200Bus	4	350000	7	Parts
2108	Graphic Card Gigabyte Geforce GTX 1650	5	350000	7	Parts
2115	Corsair Hydro H45 Liquid CPU Cooler	12	350000	7	Parts
2113	Logitech K120 USB Keyboard	10	350000	7	Office Equipment
2011	Logitech Wireless Combo MK345	14	350000	7	Parts
1979	Gigabyte 512GB NVMe M.2 SSD	9	350000	7	Parts
1980	Seagate BarraCuda 1TB Hard Drive	10	350000	7	Parts
1982	Corsair Vengeance DDR4 32GB 3200Bus RGB Pro (16GB x 2)	11	350000	7	Parts
1984	PSU Gigabyte P750GM 750W	12	350000	7	Parts
1173	Supply with installation of MS shed for compressor as per compressor size and according to the site location along with all respect also with coloring	10	350000	7	Other
1169	Supply with installation of 1 " GI Tee	6	350000	7	Other
1140	Providing and construction of concreat base for installation of compressor	1	350000	7	Other
1163	Providing and construction of concreat base for installation of compressor	1	350000	7	Other
1144	Providing and construction of concreat base for installation of compressor	1	350000	7	Other
719	Diagonal Cutter plier	48	350000	7	Machinery / Equipment
743	De Soldering Pump	72	350000	7	Machinery / Equipment
742	Bench power Supply	71	350000	7	Other
740	Glue Gun rods (set)	69	350000	7	Other
737	Wire Stripper	66	350000	7	Machinery / Equipment
736	Fluke Multimeter	65	350000	7	Tools / Test Equipment
692	Heat Shrink Sleeve Misc Size RED + Black (ft)	22	350000	2	Parts
695	Wire Mesh (roll)	25	350000	2	Parts
1145	Supply with installion of 1" GI 12 guage Pipe	2	350000	2	Other
1164	Supply with installation of 1" GI 12 guage Pipe	2	350000	2	Other
2111	Power Supply Gigabyte P750GM 750 Watt 80 Plus	8	350000	7	Parts
2112	PC Case Cooler Master MasterBox Q500L	9	350000	7	Parts
2164	410-248 ZEDBOARD ZYNQ-7000	2	200000	7	Parts
2165	T65550B Video Driver IC (China)	1	200000	7	Parts
2169	T65550B Video Driver IC (Korea)	2	200000	7	Parts
2170	K9F560800D Memory IC	3	200000	7	Parts
2171	FLASH LINK JTAG Emulator	4	200000	7	Parts
2172	GBDRIVERRA2A SD CARD DRIVER IC	5	200000	7	Parts
2173	M54FP1984F07 SMA Female Thread-in RF Connector, 12.7 x 4.8mm / 0.500 x 0.190inch Flange Jack with 1.27mm / .050 Pin	1	200000	7	Parts
2175	M54FD50F06-090 SMA Female Airline Bulkhead RF Connector, 12.7 x 4.8mm / 0.500 x 0.190 Flange Jack with 1.27mm / .050 Pin	2	200000	7	Parts
2176	M29FB12E02-023 2.92mm Female PCB RF Connector, ?7.5mm / .295? Flange Jack with 0.3mm / .012 Pin & D: 0.76mm / 0.030	3	200000	7	Parts
2178	M29FP0587F07 2.92mm Female Thread-in RF Connector, 12.7 x 4.8mm / 0.500 x 0.190 Flange Jack with 0.3mm / .012 Pin	4	200000	7	Parts
2179	M29FP0403F02 2.92mm Female Thread-in RF Connector, 9.5mm / .375 Square Flange Jack with 0.3mm / .012 Pin	5	200000	7	Parts
2181	RA54MFP2L10 SMA Male to SMA Female Attenuator, SMA 10dB RF Coaxial Fixed Attenuator	6	200000	7	Parts
2182	RA54MFP2L06 SMA Male to SMA Female RF Attenuator, 30.7mm (L) SMA 6dB RF Coaxial Fixed Attenuato	7	200000	7	Parts
2183	RA54MFP2L03 MA Male to SMA Female RF Attenuator, 30.7mm (L) SMA 3dB RF Coaxial Fixed Attenuator	8	200000	7	Parts
2121	Drill sets of 0.6mm, 0.8mm and 1mm	17	350000	7	Tools / Test Equipment
2252	AL-130R Loop Antenna, 9kHz to 30MHz Make: COM-POWER CORPORATION	1	350000	7	Machinery / Equipment
2260	Mandrel for marlin 2 Engine Inconel cowling	3	350000	7	Parts
2347	40 dB Fixed Attenuator	4	350000	7	Machinery / Equipment
2348	Coaxial Cables	5	350000	7	Machinery / Equipment
2360	Modulator PCB Casing	9	350000	7	Parts
2364	Termination connector	13	350000	7	Parts
2365	Circuit breaker casing	14	350000	7	Parts
2390	Camera for Marlin 1	21	350000	7	Office Equipment
2410	Mouse	3	200000	7	Parts
2414	Dvd Rom	7	200000	7	Parts
2420	solid state disk	12	200000	7	Parts
2426	EVAL-SDP-CH1Z BOARD EVAL SDP CH1Z	1	200000	7	Parts
2427	AD9467-FMC-250EBZ BOARD EVAL FOR AD9467	2	200000	7	Parts
2428	BEAGLEBOARD X15 BEAGLEBOARD-X15 AM5729 EVAL BRD	3	200000	7	Parts
2461	Lathe Circular tool	29	350000	7	Parts
2468	Radar station outer casing	36	350000	7	Parts
2471	Nyron brakes rim	39	350000	7	Parts
2494	Specialized / Custom Made Cable Management System with onsite installation (Refer attached Drawing)	1	200000	7	Parts
7842	Provision of Honda petrol generator	13	350000	7	Parts
5388	Video Management Server with License	1	350000	7	Machinery / Equipment
5389	Encoder 1-channel (HDMI based)	2	350000	7	Parts
5391	Hard Drive SATA (2TB)	4	350000	7	Parts
5392	Server Machine with special configuration	5	350000	7	Parts
1170	Supply with installation of 1 " valve	7	350000	2	Other
1171	Supply with installation of 1 " GI socket	8	350000	2	Other
1172	Supply with installation of 1/2 " valve	9	350000	2	Other
2005	Corsair Carbide 275R Black Mid Tower ATX Gaming Case	13	350000	7	Parts
5393	Operating System with 10 clients	6	350000	7	Parts
5394	Client Machine	7	350000	7	Parts
5385	Pan-Tilt Mount SP5050	1	350000	7	Parts
5386	Bias Tees for 2-port R&S ZNB8, maximum bias current 300mA R&S ZNB-B1 1316. 1700.02	1	350000	7	Machinery / Equipment
5400	Mold for 1/6 part of dome as per given drawing	1	350000	7	Parts
5401	Mold for top part of dome	2	350000	7	Parts
6832	AVR & Filter for jammer Fans	1	350000	7	Parts
6840	Hinges	9	350000	7	Parts
2495	Custom Made LCD Bezel (Refer attached Drawing)	2	200000	7	Parts
2289	Cast Acrylic Tube 4" (100 meter depth rated) with a blank cap and end cap 18 holes	1	300000	7	Parts
2422	Portable HDD 1TB	14	200000	7	Parts
2408	LCD 18.5"	1	200000	7	Parts
2423	Portable HDD 2TB	15	200000	7	Parts
2462	Tap set (1-10mm)	30	350000	7	Tools / Test Equipment
6841	Latches	10	350000	7	Parts
6842	Handles	11	350000	7	Parts
7880	Provision of Honda petrol generator	34	350000	7	Parts
7883	IPC for GTRS (i3 10 gen PC,LCD, 4U casing, keyboard & Mouse)	37	350000	7	Machinery/Equipment
5430	Power Cable VTx to Airborne Source (AWG-8 Fireproof Aviation Grade MIL-SPEC 20m)	1	350000	7	Parts
7885	HD COFDM Wireless Digital Video Transmitter (ID-CM100 H)	1	350000	7	Parts
7886	Cable VTX to Antenna	1	350000	7	Parts
7887	Cable VRX to Antenna	2	350000	7	Parts
5432	HD COFDM Video Receiver with ruggedized case (ID _ CMB01H)	1	350000	7	Parts
7890	HD COFDM video receiver with ruggedized case (ID-DRB01H)	1	350000	7	Parts
7891	Video TX Airborne Antenna with fairing	3	350000	7	Parts
7892	Video RX OMNI Antenna	4	350000	7	Parts
7893	Video Yagi antenna	5	350000	7	Parts
7894	Ext Cable VRX to Antenna	1	350000	7	Parts
7895	COFDM Video Modulator & Data Card	1	350000	7	Other
7896	HD COFDM Wireless Digital Video Transmitter (KP-CM100H) \n-COFDM MODULATOR BOARD \n- RF POWER AMPLIFIER (100 W)\n-VIDEO ENCODER BOARD\n-GPS DATA ENCODER BOARD	1	350000	7	Machinery/Equipment
7897	Airborne Omni Antenna\n-Frequency: 1244 ~ 1254 MHz\n-Gain: 5-8dBi, Polarization: Vertical\n-Max Input Power: 50W, Power Capacity: 100W                    \n-Connector: N, Length: 20 ~ 25 cm\n	2	350000	7	Machinery/Equipment
7899	1 Channel HD COFDM video receiver (KP-DRB01)\n- COFDM MODULATOR BOARD\n- RF RECEIVER MODULE BOARD\n-VIDEO ENCODER BOARD\n-GPS DATA ENCODER BOARD	1	350000	7	Machinery/Equipment
7900	FRP Omni Antenna\n-Frequency: 1244 ~ 1254 MHz\n-Gain: 9.5-15dBi, Polarization: Vertical\n-Max Power: 100W, Beamwidth: 360?\n-Connector: N-K, Size: 1.8 meter\n\n	2	350000	7	Machinery/Equipment
5483	USRP B200 Mini	1	350000	7	Parts
7148	NI-X310-SDS102 \r\nUSRP X310 (Kintex7 -410T FPGA, 2 Channels, 10GigE & PCIE Bus)	1	350000	7	Parts
7143	900mm (Semi-rigid SMA to SMA RF Cable)	36	350000	7	Parts
7149	NI-UBX160-SDS103\r\nUBX 160 USRP Daughterboard \r\n(10 MHz - 6 GHz, 160 M MHz BW)	2	350000	7	Parts
7151	NI-X310-SDS102 \r\nUSRP X310 (Kintex7 -410T FPGA, 2 Channels, 10GigE & PCIE Bus)	1	350000	7	Parts
7134	LPDA Antenna	27	350000	7	Parts
7152	NI-UBX160-SDS103\r\nUBX 160 USRP Daughterboard \r\n(10 MHz - 6 GHz, 160 M MHz BW)	2	350000	7	Parts
7154	NI-X310-SDS102 \r\nUSRP X310 (Kintex7 -410T FPGA, 2 Channels, 10GigE & PCIE Bus)	1	350000	7	Parts
7155	NI-UBX160-SDS103\r\nUBX 160 USRP Daughterboard \r\n(10 MHz - 6 GHz, 160 M MHz BW)	2	350000	7	Parts
5479	30W Power Amplifier ZHL-30W-252-S+	1	350000	7	Parts
5480	100W Power Amplifier ZHL-100W-272-+	2	350000	7	Parts
5481	320W Power Supply (30V, 10.7A) Meanwell HLG-320H-30	3	350000	7	Parts
5482	480W Power Supply (30V, 16A) Meanwell HLG-480H-30	4	350000	7	Parts
5564	Power Supply Corsair CV 450 450 Watt 80 Plus Bronze	5	350000	7	Parts
5478	Model No. RP-H717-TRB-IP65\r\n7U 17" rack mount LCD with high brightness 1000 cd/sq.m, 1280x1024 (SXGA), Signal Input: VGA + DVI-D, TRB: e-resistive touch screen w/USB controller, IP65 NEMA4 Dust and water sealed front protection	1	350000	7	Parts
5552	30W Power Amplifier ZHL-30W-252-S+	1	350000	7	Parts
5553	100W Power Amplifier ZHL-100W-272+	2	350000	7	Parts
5555	320W Power Supply (30V, 10.7A) Meanwell HLG-480H-30A	3	350000	7	Parts
2458	Graphic card	27	350000	7	Software
1194	HDF Sheets	17	350000	2	Other
257	Manufacture of new part for replacement of damaged servo housing	3	200000	3	Equipment Repairs & Maintenance
1275	Jazz internet recharge	51	350000	3	Other
1335	OPTICAL FIBER TESTER	3	300000	7	Tools / Test Equipment
1334	THRUSTER T-200 with ESC	2	300000	7	Machinery / Equipment
1333	RS485 CONTROLLER WITH TEMPERATURE SENSOR PT-100	1	300000	7	Machinery / Equipment
1337	BOUYANCY BARS for a Scale Down ROV for Testing	5	300000	2	Parts
1347	PLA material for 3D-Printer	4	300000	2	Other
1351	Bouyancy bar for a scale down ROV for testing	5	300000	2	Parts
5556	380W Power Supply (30V, 10.16A) Meanwell HLG-480H-30A	4	350000	7	Parts
5557	Model No. RP-H717-TRB-IP65 7U 17" rack mount LCD	5	350000	7	Office Equipment
6871	USRP B200 Mini	1	350000	7	Parts
5617	1946-1110-10-ND LMR400 LLPX	18	350000	7	Parts
5618	1946-1110-50-ND LMR400 LLPX	19	350000	7	Parts
5593	30W Power Amplifier ZHL-30W-252-S+	1	350000	7	Parts
5594	100W Power Amplifier ZHL-100W-252 +	2	350000	7	Parts
5595	320W Power Supply (30V, 10.7A)\r\nMeanwell HLG-320H-30A	3	350000	7	Parts
5596	480W Power Supply (30V, 16A)\r\nMeanwell HLG-480H-30A	4	350000	7	Parts
5597	Model No. RP-H717-TRB-IP65\r\n7U 17" rack mount LCD with high brightness 1000 cd/m2, 1 \r\n280x1024 (SXGA), Signal Input: VGA + DVI-D, TRB: e-resistive touch screen w/ USB controller, IP65 NEMA4 Dust and water sealed front protection.	5	350000	7	Parts
7159	Meanwell HLG-480H-30A 480W Power Supply (30V, 16A)	2	350000	7	Parts
6877	10 G NIC CARD	1	350000	7	Other
5517	MOSFEET & Heat Sink ZHL-30W-252-S+	1	350000	7	Parts
5490	Power Supply for power Amplifier HLG-320-30A	1	350000	7	Parts
7273	Spiral sleeve 6mm x 20ft	29	350000	7	Other
7957	Bi-Axial Antenna Positioner 180º AZ and 40º EL, Pay load: max. 65kg, max. 2.4m antenna, accuracy 0.2º including desktop controller	1	350000	7	Machinery/Equipment
7958	‘AZ360’ 360º azimuth rotation range	2	350000	7	Parts
7960	‘DATA’ RS232 Interface	4	350000	7	Parts
7961	‘MOTION CONTROL’ Servo Speed Control	5	350000	7	Parts
7962	‘Ma-50’ MASTTOP up to 5’-Mast	6	350000	7	Parts
7963	DS-6701HFHI/V(STD)\n1-ch 1080p H.264/M-JPEG encoding formats available, 1-ch ch HDMI or VGA video input \nand 1-ch VGA video loop out provided. Encoding at HD resolution of 1080p/720p/UXGA, etc. Upto 128 Micro SD card available.\n	1	350000	7	Machinery/Equipment
7107	Gigabyte H310M S2H 2.0 Intel H310 Ultra Durable Motherboard	1	350000	7	Office Equipment
7108	Kingston 8GB 2666MHz RAM	2	350000	7	Parts
7109	Intel Core i5 9400F Processor	3	350000	7	Office Equipment
7111	Corsair VS650 Power Supply	4	350000	7	Parts
7112	X540-T2 Network Interface Card	5	350000	7	Parts
7113	2U Compact Chassis KI-N2055	6	350000	7	Parts
7114	2U chassis intake fans	7	350000	7	Parts
7115	Gigabyte M.2 2280 PCIe SSD 256GB HDD	8	350000	7	Parts
7116	Controller Box	9	350000	7	Parts
7117	Electrical Distribution Box (EDB)	10	350000	7	Parts
7118	10G Copper to SPF + Adapters	11	350000	7	Parts
7133	Yagi Antenna	26	350000	7	Parts
7135	Antenna Stands	28	350000	7	Parts
7144	1ft (LMR-400 RF Cable N-type to N-type)	37	350000	7	Parts
7145	900mm (Semi-rigid SMA to N-type RF Cable)	38	350000	7	Parts
7146	N-N type 10 ft cable	39	350000	7	Parts
1402	fdsdfsd	1	300000	7	Tools / Test Equipment
1345	Thruster T-200 with ESC	2	300000	7	Machinery / Equipment
1344	RS485 Controller with temperature Sensor PT-100	1	300000	7	Machinery / Equipment
1352	Pressure Sensor (300 meter depth rate)	6	300000	7	Tools / Test Equipment
1346	Optical Fiber tester	3	300000	7	Machinery / Equipment
7147	RG58 cable spool	40	350000	7	Parts
5536	Wheels (per piece)	6	350000	7	Parts
5537	Latches (per piece)	7	350000	7	Parts
5538	Handles (per piece)	8	350000	7	Other
5539	Wooden cores	9	350000	7	Parts
5544	Fastners	14	350000	7	Parts
5549	Wood Machining	19	350000	7	Machinery / Equipment
5560	Processor Intel Core i5 10400	1	350000	7	Parts
2453	3D printer nozzle wrench	22	350000	7	Tools / Test Equipment
5561	Motherboard Gigabyte B460M DS3H	2	350000	7	Parts
5562	RAM ADATA 8GB DDR4 2666MHz	3	350000	7	Parts
5563	SSD Kingston A2000 250GB	4	350000	7	Parts
7012	3U Compact Chassis	6	350000	7	Parts
7013	Network Card Intel X540 T2 10Gbps	7	350000	7	Parts
7245	Brass boom	1	350000	7	Parts
7246	Brass elements (bundle)	2	350000	7	Parts
7247	Spacers (bundle)	3	350000	7	Parts
7249	Network rack 18U	5	350000	7	Parts
7250	Tray full	6	350000	7	Other
7251	Tray half	7	350000	7	Other
7252	Metal sheet	8	350000	7	Other
7253	Laser cutting	9	350000	7	Parts
7254	Powder coating	10	350000	7	Other
7255	Wheels (set of 4)	11	350000	7	Other
7256	Power switch	12	350000	7	Other
7257	LED	13	350000	7	Other
7258	Circuit breaker 220V-16A	14	350000	7	Parts
7259	Industrial power plug (3 prong male & female)	15	350000	7	Other
7260	AC fans 6inch with grill	16	350000	7	Other
7261	AC fans 4inch with grill	17	350000	7	Other
7262	Power cable 1.5mm 3 core 5m	18	350000	7	Parts
7263	Power cable 2.5mm 3 core 5m	19	350000	7	Parts
7264	Power cable 23/76mm 2 core 10m	20	350000	7	Parts
7267	Electrical distribution box	23	350000	7	Other
7268	2pin Ac power cord	24	350000	7	Other
7269	Silicon wire 14AWG 4m	25	350000	7	Parts
7270	Tellies (set of 52)	26	350000	7	Parts
7271	Antenna stands	27	350000	7	Parts
7272	Antenna stand arms	28	350000	7	Parts
7275	Knife/blade set	31	350000	7	Other
7276	Nuts & bolts 4/15mm (100pcs box)	32	350000	7	Parts
7277	Nuts & bolts 3/10mm (100pcs box)	33	350000	7	Parts
7278	Nuts & bolts 5/40mm (100pcs box)	34	350000	7	Parts
7279	Nuts & bolts 6/30mm (100pcs box)	35	350000	7	Parts
7280	Electrical tape set	36	350000	7	Parts
7343	3U chassis	51	350000	7	Parts
7344	Network Interface cards	52	350000	7	Parts
7352	Wooden frame	59	350000	7	Other
7358	Nitrile Gloves	63	350000	7	Other
7360	Intel core i5 processor	64	350000	7	Office Equipment
7361	Gigabyte B460 D3H motherboard	65	350000	7	Office Equipment
7363	ADATA 8GB DDR4 2666MHz RAM	66	350000	7	Office Equipment
7364	Gigabyte SSD 256GB NVMe SSD	67	350000	7	Parts
7365	Corsair CV450 450 Watt 80 Plus Bronze power supply	68	350000	7	Parts
6850	Box files for jsp batch 3 manuals	8	350000	7	Other
6855	02 AVR and 02 boards for Gwadar and Ormara Jammers	13	350000	7	Parts
7160	LMR 400 (1 ft) N(M)-N(M)	1	350000	7	Parts
8279	Inverter 4KVA	1	450000	7	Machinery / Equipment
2985	Electric SSD  for systems	3	200000	7	Parts
1489	Freight/Handling	3	350000	3	Other
1458	Radial fan	12	350000	7	Parts
754	Pc Intel core i5 10400, B560 Motherboard, 1TB HDD, 500G SSD, 16GB RAM, 23.8" LCD, Casing, Keyboard & mouse included	80	350000	7	Office Equipment
617	LCD Monitor (24 Inch)	2	300000	7	Office Equipment
616	Desktop Computer (Specs: Intel Core i.7 ,Hard disc 1 TB,RAM 16GB, 64 bit window)	1	300000	7	Office Equipment
1486	25kW Modulator PCB T65825812-7	1	350000	2	Parts
1488	Power Supply PCB T65825816	2	350000	2	Parts
1549	3 Phase wire	11	350000	2	Other
1539	Imperial Allen Wrenches 3/32" , 1/4"	5	350000	7	Tools / Test Equipment
1551	1.5 sqmm 3 core Ac wire, 0.8 sqmm	12	350000	2	Other
5811	SD Card 16GB	3	350000	7	Other
1580	Extension Boards	18	350000	7	Other
587	Keyboard mouse & extension board	41	350000	7	Machinery / Equipment
2574	Ripstop Nylon material	1	350000	7	Other
6100	Dell Optiplex 5040 SFF Intel Ci3 6th Gen 8GB	4	350000	7	Parts
1554	Cable N-Type Male to SMA Male RP RG223	13	350000	2	Other
1540	C Clamps-6"	6	350000	2	Other
1537	Metric Ball End Allen Wrench 2.5, 3 ,4 , 5 , 6mm	4	350000	7	Tools / Test Equipment
1535	Oscillating cutter Multi Tool 300W for Curved CF Sheets	3	350000	7	Machinery / Equipment
1531	Storage Racks	1	350000	7	Furniture
1530	Repairing of 1 TB Hard Disk Drive	1	300000	7	Parts
1548	Grease Gun	10	350000	7	Tools / Test Equipment
1547	24" Hand trigger Clamps	9	350000	7	Tools / Test Equipment
1543	Levelling Tool 4 ft	8	350000	7	Tools / Test Equipment
1541	Thread locker Loctile 242	7	350000	7	Tools / Test Equipment
1534	Cutting Oil	2	350000	2	Chemicals
2540	Foundation Footing	4	300000	7	Machinery / Equipment
1480	Bar30 Hig Resoulution 300m Depth/ Pressure Sensor	2	300000	7	Parts
1481	Ping360 Scanning Imaging Sonar	3	300000	7	Parts
1482	T200 Thruster	4	300000	7	Machinery / Equipment
1483	T100/ T200 Nozzle	5	300000	7	Parts
11489	Vinyl sheet	10	350000	2	Other
2611	Electrical Work Bench	2	300000	7	Furniture
2614	Drawer	5	300000	7	Furniture
2613	Cupboard	4	300000	7	Furniture
2612	HR Work Station	3	300000	7	Furniture
2570	Mounting clips	2	350000	7	Parts
2571	Motor installation kit	3	350000	7	Parts
2572	Motor interface	4	350000	7	Parts
2573	Speed sensor	5	350000	7	Parts
354	Machining	31	350000	3	Other
412	Transformer rewinding (02 HT & 02 LT sides)	1	350000	3	Equipment Repairs & Maintenance
413	Transformer Oil Replacement	2	350000	3	Equipment Repairs & Maintenance
622	Poster printing (30 x 20 inch)	1	350000	3	Other
623	Panaflex (5 x 2 ft)	2	350000	3	Other
1586	Cutting Cost for 6 Samples	22	350000	3	Other
1590	Jazz Internet Charges (INTELL & NDT LAB)	25	350000	3	Other
1591	Internet & Cable Charges At SPF	26	350000	3	Other
8484	2 - Ton Standing AC	5	300000	7	Machinery / Equipment
1633	Electronic Components (Digikey/Mouser items : resistors,cables,connectors)	5	350000	2	Parts
7621	Motor Mounting Bracket 37 mm	3	300000	7	Parts
7617	A2212B2 DC Motor	3	300000	7	Machinery / Equipment
7618	Laser Printer	4	300000	7	Machinery / Equipment
245	BNC cable	1	300000	7	Parts
7681	Corrosion resistant fresh water paint scheme for ROV test tank	1	300000	7	Other
7602	Fresh Water paint sheme for ROV test tank and Computer Terminals	1	300000	7	Other
4419	CNC Wegstr	1	350000	7	Tools / Test Equipment
7371	Purchase of 1.5 Ton AC for Director ET Office (Room # 114)	1	250000	7	Machinery / Equipment
6404	DJI Phantom Drone	1	350000	7	Machinery/Equipment
2889	IT Equipments (usb, Keyboard, mouse, WiFi dongl, 1 TB harddrive, CF card 1 GB, jacket)	43	350000	7	Office Equipment
2856	VAC bas VB20 Vacuum pump	11	350000	7	Parts
2772	Dremel Tool Kit	5	350000	7	Tools / Test Equipment
2770	Bench Vise	4	350000	7	Tools / Test Equipment
2580	Small weight machines	7	350000	7	Tools / Test Equipment
7818	Soldering fume absorber	8	350000	7	Machinery/Equipment
7817	Fire extinguisher with stand	7	350000	7	Office Equipment
7809	Office keys hanging board	14	350000	7	Office Equipment
7802	White board 6x3 ft.	7	350000	7	Office Equipment
1631	Power Supply PCB T65825816	4	350000	7	Machinery / Equipment
1630	25kW Modulator PCB T165825812-7	3	350000	7	Tools / Test Equipment
1629	3DM3722 Stepper Driver, Match with Nema 42 stepper motor	2	350000	7	Tools / Test Equipment
1627	130BYGH3318 Stepper motor, torque 50Nm	1	350000	7	Tools / Test Equipment
542	Manual Calculator	69	350000	7	Office Equipment
539	Alpha cutter	66	350000	7	Tools / Test Equipment
541	File lock type	68	350000	7	Office Equipment
8135	Tea set & Plates set	2	350000	7	Other
8137	Glass set (6 pcs)	4	350000	7	Other
8136	Wooden Tray (Large)	3	350000	7	Other
7794	Dustbin	7	350000	7	Office Equipment
7787	Router + Internet connection	5	350000	7	Office Equipment
2773	Ball links	6	350000	7	Tools / Test Equipment
2673	ADATA DDR4 SODIMM Laptop RAM	7	350000	7	Parts
2855	Microporous Vacuum Strip	10	350000	7	Parts
2853	Silicon seal for Catch-Pot	8	350000	7	Parts
2852	Silicon Connector	7	350000	7	Parts
2851	Through-Bag Connector	6	350000	7	Parts
2850	Infusion Line Clamp	5	350000	7	Parts
2849	Resin Catch-Pot 1.2L	4	350000	7	Parts
1582	Heat Treatment For 4140 Steel	20	350000	3	Other
1493	RECONSTRUCTION WORK (Wall dismantling, AC, electrical fitting readjustment)	3	350000	3	Building Repairs & Maintenance
1650	Freight/Handling	5	350000	3	Other
7936	False ceiling 2’x2’ gypsum for complete ceiling including fitting and fixtures with 03 x false ceiling fans, 04 x false ceiling lights 2’x2’) (249 sq. ft.)	13	350000	7	Office Equipment
7796	ESP-810 J UPS System with battery chamber	1	350000	7	Office Equipment
7785	AOC E970SWN 19" Widescreen Monitor	3	350000	7	Office Equipment
7784	Lenovo V530T Ci3 8th Gen, 4GB, 1TB HDD, DVDRW	2	350000	7	Office Equipment
7783	Dell Vostro 3580 Core i5 8265U, 4GB RAM, 1TB HDD, 15.6" HD LED & Intel HD Graphics	1	350000	7	Office Equipment
8103	Network Interface Card for OAS PC - 100 MB	14	350000	7	Office Equipment
8100	RAM 4 GB	11	350000	7	Office Equipment
8099	SSD 512 GB	10	350000	7	Office Equipment
8083	Laptop Lenovo ideapad 1TB HDD for field trials	1	350000	7	Office Equipment
5488	LMR-400 Crimp Tool	4	350000	7	Tools / Test Equipment
5486	LMR-400 Strip Tool	3	350000	7	Tools / Test Equipment
5519	UPS 3KVA	1	350000	7	Machinery / Equipment
7210	Internet router	30	350000	7	Parts
6852	White board for SPF (4x7)	10	350000	7	Furniture
7041	A4Tech Fstyler FG1010	12	350000	7	Office Equipment
7040	Monitor AOC 9E1 LED Monitor	11	350000	7	Office Equipment
7039	Power Supply Corsair CV650 650W	10	350000	7	Office Equipment
7038	Case Cooler Master MB501L	9	350000	7	Office Equipment
7037	SSD Gigabyte NVMe 512 GB	8	350000	7	Office Equipment
7036	Graphics Card MSI Nvidia GTX 1660 Super	7	350000	7	Office Equipment
7035	RAM G.Skill Aegis 16GB DDR4	6	350000	7	Office Equipment
7034	CPU Cooler Master MA620P	5	350000	7	Office Equipment
7033	Motherboard Gigabyte GA Z390 M	4	350000	7	Office Equipment
7032	Processor Intel i7 9700	3	350000	7	Office Equipment
6890	Book shelf 12x 3x 1 1/4	12	350000	7	Furniture
6889	Rack with sliding door 6'x6' x 2' with Oak lapping and polish	11	350000	7	Furniture
6888	Soft board with glass on ralling	10	350000	7	Furniture
2823	Orbital Sander	6	350000	7	Other
6884	Workstation 9' x 2' x 2' 1/2 with Oak lapping and polish	6	350000	7	Furniture
6883	Three x drawer sliding side table with two wheels	5	350000	7	Furniture
6882	Book shelf 11' x 3' x 1' 1/4	4	350000	7	Furniture
6879	28' x 2' x 2' 1/2 workstation	1	350000	7	Furniture
7956	Sheet metal storage box as per drawing	3	350000	7	Furniture
7955	Work Station for two persons	2	350000	7	Furniture
7954	Storage Rack 95x18x45	1	350000	7	Furniture
7953	Cemented Flooring for Container	8	350000	7	Building Modification
7952	SofaCum Bed 3 Seater	7	350000	7	Furniture
7951	Storage Shelves Size: H: 6ft, W: 4ft, D: 20inch	6	350000	7	Furniture
7949	Electrical Plug for Fans and Shed Area	4	350000	7	Other
7943	Wall mounted bracket fans (heavy duty)	3	350000	7	Furniture
7942	Wall mounted bracket fans (normal)	2	350000	7	Furniture
7941	Storage Shelves Size H: 6 ft., W: 4 ft., D: 20 in	1	350000	7	Furniture
7938	Computer desk with drawer	15	350000	7	Furniture
7935	Kenwood AC 1.5ton	12	350000	7	Furniture
7934	Samsung LED TV 40”	11	350000	7	Furniture
7933	Office Table (size: 6.5’x2’x2.6’)	10	350000	7	Furniture
7932	L shaped table (size 9’-11.5”)(9’-4”)	9	350000	7	Furniture
7945	Fiber shed for paint area Size: 20 x 17 ft.	5	350000	7	Building Modification
6887	Power wiring with board	9	350000	7	Furniture
8101	Mouse	12	350000	7	Office Equipment
8102	Wifi Dongle	13	350000	7	Office Equipment
1783	Jazz Internet Charges INTELL	12	350000	3	Other
1784	Internet and TV Charges at SPF	13	350000	3	Other
1767	PCB Fabrication services for RF PA biasing circuit	25	350000	3	Fabrication
1768	PCB Fabrication services for LNA circuit	26	350000	3	Fabrication
1736	OAS connection charges at INTELL	31	350000	3	Other
1738	Desktop PC repair, usb,wifi dongle,adapter,	33	350000	3	Other
1739	AC repair of labs	34	350000	3	Other
1740	Jazz internet charges of lab 105	35	350000	3	Other
1786	SuperHet Servo Mechanism Assembly Transportation Platform with Cover (Fiber) and Crane Lifting Attachments	1	200000	3	Fabrication
1789	Factory Acceptance,  trials and testing of complete functionality of mechanical aspect of Super HET HF Structure.	2	200000	3	Other
1790	Repair and rebuild of OMNI IFM, FCC and RF FE enclosures including customized FAN mounting and replacement of Fans.	3	200000	3	Equipment Repairs & Maintenance
4217	Keyboard logitech k120	7	350000	7	Office Equipment
5182	Epoxy Curing Incubator All steel galvanized 1.5 mm plate construction with two (02) independent compartments and individual temperature control, internal bifurcation, 6” glass wool all-round insulation, convection ducting, blowers, two (02) trolleys with 	1	350000	7	Tools / Test Equipment
7927	Electric works (Light plugs, power plugs)	4	350000	7	Other
7926	L-shaped reception table (32” height)	3	350000	7	Furniture
7925	Standard wooden bench (46”*15”)	2	350000	7	Furniture
7924	Coffee Table Round (34”x24”)	1	350000	7	Furniture
5402	HD COFDM Wireless Digital Video Transmitter (ID _ CM100H)	1	350000	7	Machinery / Equipment
5387	Calibration Unit 50 Ohm, 100kHz to 8.5 Ghz, 2 ports, N(f) R&S ZN-Z151 1317.9134.72	2	350000	7	Machinery / Equipment
5366	Split Type Air Conditioner Capacity: 1.5 ton, Make: Kenwood Warranty: 1 Year	1	350000	7	Office Equipment
5195	Hypertherm power max 85 plasma torch kit	2	350000	7	Parts
5194	AVIDCNC & HYPERTHERM PLASMA CUTTER\nPRO60120 5' x 10' CNC Router Kit- Version Nema 34 1/2" Shaft Compatible-\nZ-Axis Travel/Gantry Height: 12"/8"-Gantry Width: Extended & Components	1	350000	7	Machinery/Equipment
5138	20 lb hammer 	4	350000	7	Tools / Test Equipment
5140	S-duct 3d printing	6	350000	7	Machinery/Equipment
5139	7 ft ladder 	5	350000	7	Other
5115	AMD Ryzen 5 5600X (Processor)	23	350000	7	Office Equipment
5132	Hydraulic Tank	7	350000	7	Parts
5130	GSA 4th Axis Preparation Kit AMP + Motor + Cable	5	350000	7	Parts
5129	Manual 3-Jaw Chuck (GSA SC-12)	4	350000	7	Parts
5128	Manual Tailstock (GSA TS-B210)	3	350000	7	Parts
5127	Rotary Table (GSA CNC-320R)	2	350000	7	Parts
5577	HP Elite Desk 800 G5 Ci7 9th 8GB 1TB DVD with 23" Monitor	1	200000	7	Machinery / Equipment
7468	Printer laser jet HP pro M607N	1	200000	7	Office Equipment
7454	Chairs Inter wood	4	200000	7	Furniture
6382	RF Cable 50 Ohm; N(m) to 3.5mm(m); DC to 18GHz, flexible, phase stable; 610mm (24 inch)	3	350000	7	Parts
6381	RF Cable 50 Ohm; N(m) to N(m); DC to 18 GHz, flexible, phase stable; 610mm (24 inch)	2	350000	7	Parts
6283	Jazz internet device	17	350000	7	Office Equipment
6278	Notice board (room 105)	12	350000	7	Office Equipment
6274	HP core i5, 10th Gen, 8GB RAM, 1TB HDD, 14" screen	8	350000	7	Office Equipment
6273	Samsung non-smart TV 40"	7	350000	7	Office Equipment
6272	Samsung non-smart TV 32" 	6	350000	7	Office Equipment
6271	Printer HP laser M15w	5	350000	7	Office Equipment
6270	Fume Extractor	4	350000	7	Machinery/Equipment
5460	Fiber Glass mold for front & back lid (one mold)	1	350000	7	Other
5184	Shipping Container – 20 ft. Housing / placement facility for the Epoxy Curing Incubator	3	350000	7	Parts
1796	Installation, Transportation, Testing and Commissioning of Dust Collector with supporting Material (Electrical Connection From DC to Main Pannel-10RFT)	3	350000	3	Other
1804	Freight / Handling	5	350000	3	Other
5871	Door Locks	14	350000	7	Other
6221	Tachometer	3	350000	7	Tools / Test Equipment
6196	Lead free 2in1 soldering station	24	350000	7	Tools / Test Equipment
6222	Rachet set	4	350000	7	Tools / Test Equipment
6229	Tool kit	11	350000	7	Tools / Test Equipment
6225	Soldering Clamp Station	7	350000	7	Tools / Test Equipment
6224	Wire Stripper	6	350000	7	Tools / Test Equipment
6228	Mini Screwdriver Set	10	350000	7	Tools / Test Equipment
6220	HP Probook Core i7, 10th Gen, 8GB RAM, 256/512 GB SSD, 15.6" screen, 1TB HDD	2	350000	7	Office Equipment
6219	HP Spectre Core i5 10th Gen, 8GB RAM, 256GB SSD, 13.3" Touchscreen 4K OLED, Windows 10 Home	1	350000	7	Office Equipment
6226	Soldering Lamp	8	350000	7	Tools / Test Equipment
6194	Logitech K400 PLUS Wireless Touch Keyboard	22	350000	7	Parts
6193	AOC 9E1H LED Monitor with HDMI 18.5"	21	350000	7	Office Equipment
6188	USB 64 GB (Mechanical design group)	16	350000	7	Other
6093	Outside Notice board (6 x 3 ft.) + Cloth	14	350000	7	Office Equipment
6091	Soft boards for Room 105	12	350000	7	Office Equipment
6081	Canon Cano Scan LIDE 300 light weight A4 flatbed Scanner	2	350000	7	Machinery/Equipment
6080	AOC 9E1H LED Monitor with HDMI 18.5”	1	350000	7	Machinery/Equipment
4333	Fall ceiling LED lights	7	350000	7	Furniture
4328	Soldering station with temperature control	2	350000	7	Machinery/Equipment
4215	Wifi Tplink	5	350000	7	Office Equipment
4126	HDD 2 TB	11	350000	7	Other
4125	RAM 32 GB DDR4	10	350000	7	Other
4123	Keyboard Logitech K120	8	350000	7	Other
4122	CPU Cooler	7	350000	7	Other
4119	Chassis	4	350000	7	Other
4118	Graphic card GTX 1030	3	350000	7	Other
4117	Motherboard Gigabyte B460	2	350000	7	Other
4116	Processor i5 10th Gen	1	350000	7	Other
6430	Airconditioner 2ton with installation	1	350000	7	Office Equipment
4201	Ryzen 7 3700 Processor	7	350000	7	Parts
6079	USB Drive 64GB	113	350000	7	Office Equipment
6049	1GwInstek LCR Meter, Bench, 2 kHz, 9.99999 kH, 10 F, 99.9999 Mohm, LCR-6000 Series	83	350000	7	Tools / Test Equipment
6060	Intel Core I7 9700 9th Gen. 3.6GHZ	94	350000	7	Parts
6057	Table for field	91	350000	7	Furniture
6050	UPS with battery box	84	350000	7	Machinery/Equipment
5954	Mouse	18	350000	7	Office Equipment
5953	Keyboard	17	350000	7	Office Equipment
5881	I/P Power Plug socket	4	350000	7	Parts
6067	Transcend M.2 NvMe	101	350000	7	Parts
6066	Chassis fans	100	350000	7	Parts
6065	Thermaltake UX100 ARGB Cooler	99	350000	7	Parts
6064	VELOCIRISER V2 PCIE 3.0 X16	98	350000	7	Parts
6063	2U Compact Chassis KI-N2055	97	350000	7	Parts
4253	Mouse 	17	350000	7	Parts
4252	Keyboard	16	350000	7	Parts
6501	Mother Board	51	350000	7	Parts
5929	Master CRES LBC Office Chairs	1	350000	7	Office Equipment
5864	Pliers	7	350000	7	Tools / Test Equipment
5878	UPS with battery box	1	350000	7	Machinery/Equipment
5857	A4 Tech OP-720 USB Optical Mouse	11	350000	7	Parts
5802	Solder sucker	11	350000	7	Tools / Test Equipment
5827	Vacuum Pump	7	350000	7	Tools / Test Equipment
5856	A4Tech Fstyler FK10 Sleek Multimedia Comfort Keyboard	10	350000	7	Parts
5855	HP 22f - 21.5" FHD Display (2XN58AA), Monitor	9	350000	7	Parts
5854	Corsair CX Serieses CX750 750W 80+Bronze	8	350000	7	Parts
5853	Cooler Master Masterbox MB511 (with 3x chassis fan)	7	350000	7	Parts
5852	ZOTAC GeForce RTX 2070 Super 8GB	6	350000	7	Parts
5851	Seagate Baracudda 2TB HDD	5	350000	7	Parts
5850	Samsung 970 Evo Plus NVMe 500GB	4	350000	7	Parts
5849	GSKILL Trident Z 2x16GB Kit	3	350000	7	Parts
5848	ASUS ROG STRIX Z390-E	2	350000	7	Parts
5847	INTELL i7-9700 Processor	1	350000	7	Office Equipment
5814	Table for trials	6	350000	7	Furniture
4573	Screw driver set (1 IN 50)	24	350000	7	Tools / Test Equipment
6560	DC Power Supply GWInstek Model # GPS - 4303	65	350000	7	Tools / Test Equipment
4453	N9000B -CXA Signal analyzer ,multi touch,9 Khz to 26.5 Ghz	1	350000	7	Tools / Test Equipment
8474	Wooden Cabinets on wall	31	350000	7	Furniture
8473	BenQ DLP Projector	30	350000	7	Parts
8472	Power line Filter for special grounding	29	350000	7	Parts
8467	Samsung LED TV 50"	26	350000	7	Other
5126	HARTFORD CNC VERTICAL MACHININGCENTER (Model: MVP-10 with MGPS)\nI. Automatic Voltage regulator (AVR) 25kVA\nII. Dual Screen\nIII. Transformer\nIV. AI Contour Control	1	350000	7	Machinery/Equipment
8466	Remaining Workstations, Drawers and 4 Office Chairs	25	350000	7	Furniture
8465	Window blinds for NDT Lab	24	350000	7	Other
8462	500x500x10 mm GI Plate for placing in the middle of the earthing pit	21	350000	7	Parts
4406	Switching DC power supply (Model # SPS-606 GWInstek)	1	350000	7	Machinery/Equipment
6434	Work stations 30”x24”x108”	5	350000	7	Office Equipment
6433	Projector screen 6’x6.5’ and projector installation	4	350000	7	Office Equipment
5786	Extension leads	2	350000	7	Other
5780	Multi plug for lab	22	350000	7	Other
5714	Dremill	6	350000	7	Tools / Test Equipment
5718	Heat Gun	10	350000	7	Tools / Test Equipment
5717	Weighing scale	9	350000	7	Tools / Test Equipment
5715	Sanding machine	7	350000	7	Tools / Test Equipment
7472	HP LEDs 18.5”	5	200000	7	Office Equipment
1937	ROV Assembly area	2	300000	7	Other
1933	gfsn	1	300000	7	Office Equipment
2002	Jazz  Internet Charges for room 105 & 104	49	350000	3	Other
2062	Outward / Return Freight & Handling (PK to Germany to PK)	3	350000	3	Other
4852	Power cable laying, distribution box with power meter & accessories\n-Digital electrical meter\n-130m 70 sq. mm armored LT cable\n-Termination kit\n-Circuit Breaker\n-Excavation/refilling\n-DB board steel sheet 2'x2'x10" 	5	350000	7	Other
4954	Tripod Adapter	26	350000	7	Parts
4255	HP15 DA2174NIA Laptop 10th Gen Ci5 10210U 1TB Intel UHD Graphics, 12GB RAM & 512 GB SSD	19	350000	7	Office Equipment
4254	Dongle	18	350000	7	Parts
4247	LED	11	350000	7	Parts
4246	TP-Link Archer T1U AC450 Wireless Nano USB Adapter 	10	350000	7	Parts
5692	GPIO to USB	10	350000	7	Office Equipment
5691	CPU Case Fans	9	350000	7	Office Equipment
5690	2U Industrial Grade Server Chassis	8	350000	7	Office Equipment
5689	Cooler Master MWE Gold 550 Full Modular	7	350000	7	Office Equipment
5687	Corsair Hydro series H55	5	350000	7	Office Equipment
5686	Transcend 128GB M2 SSD 830S SATA III 6GB/s	4	350000	7	Office Equipment
5685	Transcend 8GB DDR4-2666 U-DIMM	3	350000	7	Office Equipment
5684	Gigabyte GA B360M DS3H	2	350000	7	Office Equipment
5683	Intel Core i5 8400 2.5GHz	1	350000	7	Office Equipment
4245	A4Tech Fstyler FG1010 Keyboard	9	350000	7	Parts
5125	AOC 22B2HM 22" IPS FHD Monitor	33	350000	7	Parts
5124	Logitech M90 USB Mouse	32	350000	7	Parts
5123	Logitech K120 USB Keyboard	31	350000	7	Parts
5122	Cooler Master Box Q500L(PC CASING)	30	350000	7	Parts
5121	Gigabyte P750GM 750 Watt 80 Plus (Power supply)	29	350000	7	Parts
5120	Seagate 1TB 3.5? (HDD)	28	350000	7	Parts
5119	Kingston A2000 NVMe PCIe SSD 500GB (SSD)	27	350000	7	Parts
5118	Gigabyte GeForce GTX 1650 (Graphics card)	26	350000	7	Parts
5117	Transcend 16GB DDR4-3200MHz (RAM)	25	350000	7	Parts
5116	Asus Prime B550M-A Wi-Fi (Motherboard)	24	350000	7	Parts
5078	Wire tag printer	1	350000	7	Tools / Test Equipment
4962	Shredder	34	350000	7	Office Equipment
4959	Mouse, Keyboard & wifi dongle	31	350000	7	Office Equipment
4956	Vacuum pump	28	350000	7	Machinery/Equipment
4950	ROTAX minutes, Rotax E-Learning series, operations & basic maintenance \n(yearlong subscription)	22	350000	7	Other
4879	Component Drawer	24	350000	7	Other
4878	Component Boxes	23	350000	7	Other
4866	Storage components box with drawers	11	350000	7	Other
4865	Storage component drawer	10	350000	7	Other
4851	02 x wooden workstations 2'x4' with drawers in Machine shop	4	350000	7	Furniture
4849	GREE AC 4 Ton (24000 BTU) GF-24CDHAA + equivalent, connection piping, electrical cable,\nducting and installation of complete system	2	350000	7	Office Equipment
4848	GREE AC 4 Ton (48000 BTU) GF-48CDHAA + equivalent, connection piping, electrical cable,\nducting and installation of complete system	1	350000	7	Office Equipment
4844	UV Lamp	35	350000	7	Other
2045	Matt Glass Fiber	1	350000	2	Other
2046	Polyester Resin	2	350000	2	Other
2047	Prime 27 epoxy resin	3	350000	2	Other
2048	Areldite 420 Bonding adhesive (1.4kg kit)	4	350000	2	Other
2049	Thinner	5	350000	2	Chemicals
2060	Engine repair estimates for Serial No 220-1620: original box qty 01, foam packaging qty 01, ECU Electronics qty 01, starter motor leg qty 01, Housing assembly qty 01, EGV system qty 01, Iso ring qty 01, Injection needle M3 qty 03, BK stick qty 12, combustion chamber ring qty 01, Inner sheet qty 01, BK-Reduction qty 01, ceramic bearings qty 01, shaft P140 / 180 qty 01, Inner jet nozzle with slot qty 01, Exhaust nozzle qty 01, Nozzle Speiche qty 05, engine balancing, engine final test run, repair services turbine	1	350000	3	Equipment Repairs & Maintenance
2101	Farooq Ayyaz	1	300000	3	Travel/Boarding/Lodging
2095	Closure / Removal of door (Plaster on wall with distemper)	3	350000	3	Building Modification
2061	Engine repair estimates for Serial No 220-1573: original box qty 01, foam packaging qty 01, GEN engine qty 01, Iso ring P220 qty 01, ceramic bearings qty 01, Injection needle M3 qty 12, engine balancing, engine final test run/repair services turbine	2	350000	3	Equipment Repairs & Maintenance
2096	Glass to be tinted with plastic film (room+Washroom)	4	350000	3	Other
2098	Paint work	5	350000	3	Building Modification
2099	Venile Flooring	6	350000	3	Building Modification
2351	Freight / Handling	7	350000	3	Other
2135	Internet Charges of SPF & INTELL	25	350000	3	Other
4766	N5173B EXG X-Series Microwave Analog Signal Generator N5173B-520 Frequency \nRange, 9 kHz-20 GHz	1	350000	7	Tools / Test Equipment
4767	N5173B-UNW Narrow Pulse Modulation	2	350000	7	Parts
4735	Processor :Ryzen 5 5600X,Motherboard:ASUSTUF Gaming B550M plus,2x Ram:Gskill16 GB, Graphic CardGTX:1650,SSD Kingston SSD 500 GB,HDD Seagate 1TB,Case,Power Supply: GB P650,Keyboard ,Mouse,2xMonitor:AOC22B1HS&24 Inch HP e243	9	350000	7	Other
4733	Samsung Smart TV 42 "	7	350000	7	Other
6788	Lenovo Think-Book 15 Ice Lake - 10th Gen Core i5 ,04GB RAM 1-TB HDD ,15.6" Full HD	1	350000	7	Office Equipment
4669	Battery Charger (SKYRC Q200)	9	350000	7	Other
8134	Cutlery Set (24 pcs)	1	350000	7	Other
2134	SPF printer Repair and Refill	24	350000	3	Other
2217	LCD Repairing	5	350000	3	Equipment Repairs & Maintenance
4654	Engine Test Bed (Engine Ground Testing)	29	350000	7	Other
6782	Clamp meter	287	350000	7	Tools / Test Equipment
6758	Table lamp with magnifying glass	263	350000	7	Tools / Test Equipment
6757	Small open lead tool/screw driver set	262	350000	7	Tools / Test Equipment
8457	Aluminum pillars for providing strength to the cage size: 8 feet height and space of 2 feet in between	16	350000	7	Parts
8456	Aluminum walls (sides) size: 9x7x8 feet	15	350000	7	Parts
8455	Aluminum door complete 6.5 feet long with tight closing mechanism and with hinges	14	350000	7	Parts
8452	Work stations (cubicles) size: W1200xD600xH760 mm with drawer and padestal and office chairs	11	350000	7	Furniture
8451	2 ton Kenwood Standing type  Airconditioner with complete fitting and installation	10	350000	7	Other
8450	Wooden table for Lab work (size: 12x4 feet) along with drawers (1 feet depth each & extra support for carrying heavy weights)	9	350000	7	Furniture
2080	M/s Waheed Son’s Remaining Services for construction of Test Tank	1	300000	7	Other
8449	Conference table (8-10 person size) strong wooden base and wooden color top including technology box, projector stand and projector screen	8	350000	7	Other
8448	1.5 ton ceiling type Kenwood Airconditioner with complete duct, fitting and installations	7	350000	7	Other
8446	Office Chairs with wheels (600Wx590Dx950H mm)	6	350000	7	Furniture
8444	False Ceiling 2x2 gypsum for complete ceiling including fitting and fixtures and 08  LED Lights	5	350000	7	Parts
8441	Conference room partition wall, window blinds, Interface Cable & Data points for Lab	2	350000	7	Parts
6719	Digital Vernier Caliper	224	350000	7	Tools / Test Equipment
6746	High speed drill with stand 	251	350000	7	Tools / Test Equipment
6743	Wire stripper	248	350000	7	Tools / Test Equipment
6722	Soldering iron	227	350000	7	Tools / Test Equipment
6697	TTI Bench Power Supply EX752M\nDigital 300W, 2 output 0 – 15V and 0 – 75V, 2A and 4A	202	350000	7	Tools / Test Equipment
6750	1TB Hard Drive External	255	350000	7	Parts
6704	Dell Laptops Inspiron 3576\nCore i7 8550U, 8GB RAM, 2TB HDD and 2GB AMD Dedicated Graphics with 1 year local Dell Warranty	209	350000	7	Office Equipment
6683	Rachet Crimper Tool	188	350000	7	Tools / Test Equipment
6684	Two blade Coax Stripper Tool	189	350000	7	Tools / Test Equipment
6668	Rechargeable drill	173	350000	7	Tools / Test Equipment
6570	Toolkit	75	350000	7	Tools / Test Equipment
6583	Clamp meter	88	350000	7	Tools / Test Equipment
6580	Toolkit	85	350000	7	Tools / Test Equipment
6581	Oscilloscope GW Instek GDS 2102 E\n2 channels, Bandwidth DC 100MHz (-3dB), Rise time 3.5ns\nBandwidth Limit 20 MHz	86	350000	7	Tools / Test Equipment
6582	2.5KVA/2KW Honda Petrol/Gas Generator \n(One genset petrol/natural gas operated, gaskit, battery,  trolley)  	87	350000	7	Machinery/Equipment
6509	Processor Core i7 7700 7th Gen (3.6 GHz Turbo Boost Up to 4.2 GHz (4 Cores/8 Threads), RAM 16GB, Hard Disk Drive 1 TB, GPU Nvidia GTX1060 3GB, Additional Features (DDRW, Wi-Fi, Keyboard & Mouse)	59	350000	7	Parts
6507	Function Generator GW Instek AFG 2112\n0.1Hz to 12 MHz with 0.1 Hz resolution\nSine, Square, Triangular, Noise, Arbitrary waveforms\n20MSa/s sampling rate and 10 bit vertical resolution	57	350000	7	Tools / Test Equipment
4537	Bench Drill Machine	3	350000	7	Tools / Test Equipment
4569	Notice board for project posters	20	350000	7	Other
4560	Network Storage: WD EX2 NAS, WD NAS Harddrive 6TB, Linksys NAS router, TP link LS1005 switch, LAN cables	11	350000	7	Office Equipment
2738	Silicon Tube	7	350000	2	Other
4475	Launcher Sand Blasting & Painting	3	350000	7	Other
6609	Keyboard	114	350000	7	Parts
6608	Mouse	113	350000	7	Parts
6644	Storage boxes medium	149	350000	7	Other
2259	Actuator Transport charges	2	350000	3	Other
2338	Freight/ Handling	9	350000	3	Other
2290	Fathom - X	2	300000	7	Parts
2343	Window roll up blind	5	300000	7	Furniture
2340	Provision and installation of 01 x Ton A.C	2	300000	7	Machinery / Equipment
4557	PC, Ryzen 5 3600, 16GB RAM, SDD, Samsung 256 GB, 2 x Monitor AOC 22B1HS, Graphic \nCard: MSI GT GTX 1650, keyboard & mouse	8	350000	7	Office Equipment
4556	PC, Ryzen 7 3700X, RAM 16GB, 512 GB SSD, 2 x Monitor AOC 22B1HS, keyboard & Mouse	7	350000	7	Office Equipment
4555	Kenwood AC 1.5 ton	6	350000	7	Furniture
4554	Gazebos for UCAV facility	5	350000	7	Other
4553	White boards and soft board	4	350000	7	Other
4552	03 Dispenser utensils with tap 70 liter	3	350000	7	Other
4551	01 Chemical Dispenser unit Table (6 x 4ft)	2	350000	7	Other
4550	02 work tables (8 x 3 ft)	1	350000	7	Furniture
4538	Engine lifter with Wheels	4	350000	7	Machinery/Equipment
4535	Lathe machine (8ft, 1400mm)	1	350000	7	Machinery/Equipment
4533	Jazz Internet device	20	350000	7	Other
4532	Lenovo Thinkbook 15 Ice Lake - 10th Gen Core i5, 04 GB RAM 1 TB HDD, 15.6" Full HD	19	350000	7	Office Equipment
4530	Safe	17	350000	7	Furniture
4525	Blower	12	350000	7	Tools / Test Equipment
4517	Screw driver set (50 in 1)	4	350000	7	Tools / Test Equipment
4529	Acrylic box for desktop CNC & 3D printer	16	350000	7	Other
4527	Seagate Hard Drive 1TB	14	350000	7	Parts
4506	Vacuum Pump	3	350000	7	Other
4486	PC, Intel i3 9100, 8GB RAM DDR4, 128 GB SSD, 19" LCD, DVD ROM, Keyboard & Mouse	14	350000	7	Other
4485	32 GB RAM	13	350000	7	Other
4478	Storage Racks	6	350000	7	Other
4477	Cooler of PC(Aqua 240 CPU)	5	350000	7	Other
4476	Paint Machine	4	350000	7	Other
4474	Torque Wrench	2	350000	7	Other
2285	Remedial Solution for ground water around Test Tank (Pit & Pipelining)	3	300000	7	Parts
2284	Crush	2	300000	2	Other
2283	Sand	1	300000	2	Other
2341	Vinyl Tile Flooring	3	300000	2	Other
1766	PCB Fabrication services for RF PA 15W	24	350000	3	Fabrication
2391	Actuator transport charges	22	350000	3	Other
2296	Mild Steel Pressure Hull (Length 1000mm, Dia 500mm, Thickness 3mm) with internal stiffiner and both end bolted bulk head	1	300000	7	Machinery / Equipment
2396	OAS Printer Toner Refill	27	350000	3	Equipment Repairs & Maintenance
1161	Painting job (Red oxid and sky blue oil paint kerosine oil, brush and other colour)	11	350000	3	Other
878	Laptop repair cost (replacement of LED hinges)	53	350000	3	Equipment Repairs & Maintenance
2337	Generator & UPS Repair Charges	15	350000	3	Equipment Repairs & Maintenance
2405	SBC (Computer Board) Reverse Engineering	1	200000	3	Fabrication
2330	VFD Doors	8	350000	7	Other
2403	DFSF	1	300000	2	POLs
2406	SBC (Computer Board) Stuffing & Assembling	2	200000	7	Parts
2407	SBC (Computer Board) Parts or Components	3	200000	2	Parts
6835	Door lock replacement	4	350000	3	Other
2395	Nitrogen pressure testing of actuator	26	350000	3	Other
2476	Jazz internet recharge INTELL	44	350000	3	Other
2477	Internet charges SPF	45	350000	3	Other
2511	Digital Muffle Furnace, PID Control System, Chamber Size 9.5x9.5x9.5 inch Approx, Element 3500W, Imported Parts, Assembled in PAK, After sales included	1	350000	7	Machinery / Equipment
1302	Reconstruction Work (Wall dismantling, Door removal, door closing , finishing and distemper activity)	1	350000	3	Building Modification
1303	Wooden Flooring (complete activity procurement and installation)	2	350000	3	Building Modification
2440	KADA Soldering Station Repairing	10	350000	3	Equipment Repairs & Maintenance
1490	FALSE CEILING (Complete activity False ceiling procurement and installation)	1	350000	3	Building Repairs & Maintenance
2301	Finishing operation and paint of pressure hull	4	300000	7	Parts
2300	Installation of vent for pressurised air filling for pressure testing of pressure hull	3	300000	7	Machinery / Equipment
2298	Machining, Fabrication and Fitting of all stifiners and end bulk head of pressure hull	2	300000	7	Machinery / Equipment
2404	PLA Material for 3D Printer	1	300000	2	Chemicals
2452	PTFE Tube	21	350000	2	Parts
2534	EDM charges	14	350000	3	Other
2592	Internet charges INTELL & SPF	19	350000	3	Other
2568	Jazz internet NDT Lab	28	350000	3	Other
2668	SUR-17 clamps machining	3	350000	3	Other
2599	Supply & Installation of Steel (cutting, bending, fixing & formwork with 6"x6" Spacing)	2	300000	7	Machinery / Equipment
2598	8" Stone Soiling & Compaction of Soiling on compacted & leveled surface/ground	1	300000	7	Other
2608	Fabrication & Erection of Tank Ladder at NRDI (SS Pipe 1.5" - 14 gauge)	2	300000	7	Parts
2607	Fabrication & Erection of Tank Railing at NRDI (1.5" SS Pipe, Elbow, Base Plate/Pipe Sleeve - 16 gauge)	1	300000	7	Parts
2603	Constrution of Block Masonry Wall with column & plinth beam of PEB shed (Size: H-10 ft x 6") with plaster	2	300000	7	Other
2602	Construction of Precast slab wall with column for office building PEB shed (Size: H-6 ft x 2")	1	300000	7	Machinery / Equipment
2600	Concrete flooring/pouring ratio 1:2:4 with 6" finished bed	3	300000	7	Other
2246	Internet charges RF Lab	22	350000	3	Other
1491	TILE WORK (Tiles, bond, cements and labour)	2	350000	3	Building Repairs & Maintenance
2692	Fabrication of X-band Amplifier PCBs	8	350000	3	Fabrication
2736	Hartford spindle seal replacement	5	350000	3	Equipment Repairs & Maintenance
2794	Rewelding of flanges	27	350000	3	Other
2795	Welding filling of holes of flanges of DB 51	28	350000	3	Other
2798	Hartford spindle seal replacement	31	350000	3	Other
2799	Jazz recharge NDT & RF lab	32	350000	3	Other
2800	Poster printing	33	350000	3	Other
1518	SOURIAU 8D7C15F19PN 19 pin Circular MIL Spec Connector 19P Size 15 JAM Nut PIN Receptacle	5	200000	7	Parts
2742	U clamp for wire stopper	11	350000	7	Parts
2741	M1 actuator pipe 8mm	10	350000	7	Parts
2734	Lipo current and voltage sensor	3	350000	7	Parts
2733	Launcher Capaciter 600 uF	2	350000	7	Parts
2732	PLC Marlin-02 Testbench	1	350000	7	Parts
2745	Gelcoat	14	350000	2	Other
2746	Matt Fiber	15	350000	2	Other
2747	Cobalt	16	350000	2	Other
2748	Paint Brushes	17	350000	2	Other
2749	Mixing cups box	18	350000	2	Other
2735	Petrol for Engine cleanup	4	350000	2	POLs
2737	Compressor clamps	6	350000	2	Other
2739	4140 rod for Marlin 1C standoff	8	350000	2	Other
2743	Fuel for MARLIN generator and compressor	12	350000	2	POLs
2744	Polyester Resin	13	350000	2	Other
2753	Saftey Gloves	22	350000	2	Other
2740	MS rod 32mm snatch block Launcher	9	350000	2	Other
2750	Bench Vise	19	350000	7	Tools / Test Equipment
2751	Dremel Tool Kit	20	350000	7	Tools / Test Equipment
2825	Internet Charges	8	350000	3	Other
2791	Wire cutting of heat sink	24	350000	3	Other
2792	EDM of WR90 flanges	25	350000	3	Other
2793	Wire cut of flanges from waveguide and magic-T	26	350000	3	Other
2801	Printer cartridges refill and repair	34	350000	3	Equipment Repairs & Maintenance
4145	Compression Fitting	17	350000	3	Other
4191	Machining Of Metal Parts(Engine mount, wing mount)	26	350000	3	Other
4194	Ground Leveling	29	350000	3	Building Modification
4228	Internet charges	18	350000	3	Other
2879	Sheet bending operation	33	350000	3	Other
2880	Powder Coating	34	350000	3	Equipment Modification
2890	2 x AC repair, gas refilling and cartridge refilling	44	350000	3	Other
2891	Jazz Recharge INTELL	45	350000	3	Other
4248	Laptop repair services	12	350000	3	Building Repairs & Maintenance
4258	Welding/Fabrication of waveguide components	22	350000	3	Fabrication
4264	Adapter fabrication	2	350000	3	Fabrication
4270	fabrication of waveguide transition	8	350000	3	Fabrication
4271	misc. services for waveguide components	9	350000	3	Other
2941	Freight / Handling	3	350000	3	Other
2938	Internet Charges ar SPF	39	350000	3	Other
4309	PCB Manufacturing charges	47	350000	3	Fabrication
4313	Heat sink milling	51	350000	3	Fabrication
4323	Flanges including fabrication cost Al 1050 (10mm thick billet with wire cutting)	5	350000	3	Fabrication
4324	fabrication of circulator	6	350000	3	Fabrication
4325	fabrication of directional coupler	7	350000	3	Fabrication
4326	fabrication of adapters	8	350000	3	Fabrication
4329	PCB manufacturing for pre-amplifier	3	350000	3	Fabrication
2474	AC repair SPF design room	42	350000	3	Equipment Repairs & Maintenance
4330	PCB manufacturing for biasing circuit	4	350000	3	Fabrication
4331	PCB manufacturing for controller circuit	5	350000	3	Fabrication
4332	Poster for HPM lab	6	350000	3	Other
4334	Jazz internet device	8	350000	3	Other
4348	[SREN001] shipping costs - Air freight Worldwide	13	350000	3	Other
4349	Concrete (without steel) (3000psi) m3	1	350000	3	Building Construction
4350	Crushed Stone (dumpers)	2	350000	3	Building Construction
4351	Labor	3	350000	3	Other
4352	40 ft. shipping container (2nd hand)	4	350000	3	Building Construction
4353	Door frame material (kg)	5	350000	3	Building Construction
4354	Fabrication of shop (labor) 	6	350000	3	Other
4355	Paint and paint application	7	350000	3	Building Modification
4356	Roof insulation (sq. ft.)	8	350000	3	Building Modification
4357	Wiring and lighting	9	350000	3	Building Modification
4373	Jazz internet package	7	350000	3	Other
2836	SS Railing & SS  Ladder for Tank	2	300000	7	Other
7173	UPS	2	160000	7	Office Equipment
7171	Battry	1	160000	7	Office Equipment
4377	Fabrication services of waveguide termination	11	350000	3	Other
4383	PCB Manufacturing of Phase Shifter	17	350000	3	Fabrication
4390	Workshop table size: 10 x 6 ft. height: 32in	1	350000	3	Building Modification
4391	Electrical power socket points (15A)	2	350000	3	Building Modification
4392	ESD mat size: 10 x 6 ft.	3	350000	3	Building Modification
4393	ESD mat size: 4 x 2 ft.	4	350000	3	Building Modification
4394	Wrist Straps for ESD mat	5	350000	3	Building Modification
4395	CO2 type fire extinguisher 3 Kg	6	350000	3	Building Modification
4396	Extension of earthing points	7	350000	3	Building Modification
4397	Wall mounted peg board along with hanging poles size: 9 x 3 ft.	8	350000	3	Building Modification
4398	Storage drawer size: 9 x 3 x 2 \nDrawers 3 x 1 ft.	9	350000	3	Building Modification
4463	Fabrication Services for Power Combiner	6	350000	3	Fabrication
4464	Phase Detector AD8302 PCB fabrication services	7	350000	3	Fabrication
4467	AC gas refill and repair	10	350000	3	Equipment Repairs & Maintenance
4468	Internet Recharge	11	350000	3	Other
4472	10” deep 2”dia anchor hole at distance of one meters in each direction	2	350000	3	Building Modification
4512	Shipping Charges for Parachute Items	5	350000	3	Other
4528	Internet Charges	15	350000	3	Other
4531	Electrical connection extension to workbenches	18	350000	3	Other
4539	Collaboration with PCSIR for regular material testing	1	350000	3	Other
4549	Shipment/Freight Charges	10	350000	3	Transport
4558	Internet monthly recharge	9	350000	3	Other
4565	AC gas refill	16	350000	3	Other
4567	Printer toner refill	18	350000	3	Other
4625	Freight	23	350000	3	Transport
4636	Machining Of Ribs & Bulkheads	11	350000	3	Other
4637	Machining of Pattern (High Density Fiber Board4*8*12mm)	12	350000	3	Other
4653	Machining of Metal Parts(Engine mount,wing mount)	28	350000	3	Other
4719	Freight	15	350000	3	Transport
4721	Plywood covering of open area and 3 x Wooden Workstation in Outer room False \nceiling at serial production facility Insulation (Thermopole) and Plywood paneling at serial Production facility Customized conference table 8' X 3'6" 	2	350000	3	Building Modification
4722	Washroom renovation	3	350000	3	Building Modification
4726	 Drive way Crete installation works 	3	350000	3	Other
4747	Shipping charges	5	350000	3	Other
4725	Earthing work for facility	2	350000	3	Building Modification
4789	Fabrication Services for Horn Antenna	21	350000	3	Fabrication
4897	Vacuum Fitting	42	350000	3	Other
8221	Panaflex Designing.	7	160000	7	Other
4963	Internet charges	35	350000	3	Other
4965	Shipping/Freight	2	350000	3	Transport
4969	Internet Charges	4	350000	3	Other
5040	Shipping charges	2	350000	3	Transport
5042	Fabrication of MR-ZERO Casing assembly	2	350000	3	Fabrication
5085	Powder coating	8	350000	3	Equipment Modification
5146	Freight	6	350000	3	Transport
5187	Laser cutting	3	350000	3	Other
5196	Shipment charges	3	350000	3	Transport
6834	Wiring at SPF	3	350000	3	Other
6833	Plastic coating of manual	2	350000	3	Other
6836	Internet Recharge	5	350000	3	Other
5320	Job ads at rozee.com	1	200000	3	Consultancy
5321	Job ads at Mustakbil.com	1	200000	3	Consultancy
6839	AC gas refill & repair SPF	8	350000	3	Other
6853	Jammer manuals for antenna printing, binding and coating	11	350000	3	Other
5368	Material Charges \r\n- Limepaint: 100 kg\r\n- Oil paint black & white : 5 gallon \r\n- Distemper white  off white : 7 drums \r\n- Tarpentine oil: 10 ltr \r\n- Neel, Emery paper, glue, puttine, malmal cloth as per requirement	2	350000	3	Building Repairs & Maintenance
5367	Labor Charges lump sum white wash of complete house \r\n- Inside & outside walls of residential building with distemper . All window & doors with white oil paint \r\n- white wash of boundery wall front and inside complete with limepaint\r\n- Main gate with black oil paint	1	350000	3	Building Repairs & Maintenance
5369	Transportation charges & pranch charges for staging drum etc.	3	350000	3	Other
6859	Internet charges	3	350000	3	Other
6860	Toner refill	4	350000	3	Other
6870	Field Trials	11	350000	3	Other
5352	Freight/Handling	2	350000	3	Other
5395	Installation & Configuration	8	350000	3	Equipment Installation
5390	Client License	3	350000	3	Other
5524	AC work	6	350000	3	Equipment Repairs & Maintenance
6869	Certificates printing	10	350000	3	Other
5429	DHL Shipping	2	350000	3	Other
6866	Power supply repair	7	350000	3	Other
5525	Toner Refill and printer repair	7	350000	3	Equipment Repairs & Maintenance
5527	Jazz  internet charges	8	350000	3	Other
5523	Washroom work	5	350000	3	Building Repairs & Maintenance
5521	UPS Wiring	3	350000	3	Parts
5529	Hiace for 01 day	1	200000	3	Other
5533	Hiace for 01 day	1	200000	3	Other
5522	Cleaning of labs and SPF	4	350000	3	Other
6881	Oak veneer polish work	3	350000	3	Other
6885	Power wiring with board	7	350000	3	Building Modification
6901	RSNF visit preparation	10	350000	3	Other
5571	Hiace and other vehicles for multiple days	1	200000	3	Other
5573	Rent a car for multiple days	1	200000	3	Other
5574	Rent a car for multiple days	1	200000	3	Other
5578	Refurbishment of mechanical enclosure (set of 17 items)	1	200000	3	Other
5583	SP335EER1-L/TR Interface - Specialized RS-232/485/422 Tnscvr w/ Intrl Term	2	200000	7	Parts
5582	MAX14851AEE+  Digital Isolators Six-Channel Digital Isolator	1	200000	7	Parts
5701	Lathe machine work for log periodic antenna	6	350000	3	Fabrication
5749	Colored print + Lamination + Spiral binding of SOP docs	6	350000	3	Other
5809	Colored print of jammer document	1	350000	3	Other
5816	Rust resistant Dual layer HASL finish PCBs including electronics soldering 	1	350000	3	Fabrication
5875	Hard coating of jammer doc	18	350000	3	Other
5882	Installation of UPS & Internal Wiring	5	350000	3	Equipment Installation
5892	Lathe Machine work & labor charges	10	350000	3	Fabrication
5894	Development/Making	2	350000	3	Fabrication
5925	CNC Machining 	17	350000	3	Fabrication
5926	Laser Cutting	18	350000	3	Fabrication
5928	Laser cutting	2	350000	3	Fabrication
5939	Laser cutting local	3	350000	3	Fabrication
5944	lathe machine work	8	350000	3	Fabrication
5957	Colored print of revised jammer documents	21	350000	3	Other
5958	Hard lamination of jammer documents	22	350000	3	Other
5960	Affidavit for clearance of shipment	24	350000	3	Other
5962	Car puncture/repair work	26	350000	3	Other
5963	Car service	27	350000	3	Equipment Repairs & Maintenance
5964	Oil filter and air filter (41010 genuine parts replacement)	28	350000	3	Equipment Repairs & Maintenance
6046	PCB manufacturing	80	350000	3	Fabrication
6054	Installation of UPS & Internal Wiring	88	350000	3	Equipment Installation
6095	Poster printing	16	350000	3	Other
6096	Paint for profi base	17	350000	3	Fabrication
6104	Welding charges for UAV scaled down	8	350000	3	Fabrication
6176	Casing manufacturing for 8W PA	4	350000	3	Fabrication
6177	Powder coating	5	350000	3	Fabrication
6178	Wooden mold repair	6	350000	3	Fabrication
6179	IPC replacement with new fan casing	7	350000	3	Equipment Repairs & Maintenance
6180	Laser cutting of front and back casing	8	350000	3	Fabrication
6185	Tellies/Tags for jammer buttons and equipment referecing	13	350000	3	Other
6186	Air Filter, oil filter and AC repair	14	350000	3	Equipment Repairs & Maintenance
6251	Vehicle repair work	22	350000	3	Equipment Repairs & Maintenance
6252	AC repair	23	350000	3	Equipment Repairs & Maintenance
6240	brushes	11	350000	2	Fabrication
6260	laser cutting front & grey color	31	350000	3	Fabrication
6279	PCB casing manufacturing charges	13	350000	3	Fabrication
6280	Vehicle repair services (puncture, ignition self)	14	350000	3	Equipment Repairs & Maintenance
6281	Miscellaneous charges for FATs	15	350000	3	Meals/Refreshments
6284	Laser cutting GPS & Reactive Jamming casing	18	350000	3	Fabrication
6325	Gas welding for log periodic antenna	8	350000	3	Fabrication
6326	PCB Fabrication including substrate	9	350000	3	Fabrication
6327	PCB Fabrication including substrate	1	350000	3	Fabrication
6352	Stickers printing for equipment	17	350000	3	Other
6356	Flyers printing for hiring	21	350000	3	Other
6374	N Type Male RG58 with making	17	350000	3	Parts
6376	SOP Document Print, Coat & Spiral Binding	19	350000	3	Other
6380	Receiver Step Attenuator port 2, electronic, 30dB in 10dB steps	1	350000	3	Parts
6410	Engineering, Design, Integration cost & Labor	3	350000	3	Transport
6418	Electrical Cabling complete inside Faraday Cage including lightning for the wooden workstations, Ethernet points and Shielded cabling	2	350000	3	Equipment Repairs & Maintenance
6420	Complete hall area porcelain tiles	1	350000	3	Building Modification
6421	False ceiling	1	350000	3	Building Modification
6422	Wall paint	1	350000	3	Building Modification
6423	Room partition planks with door	1	350000	3	Building Modification
6424	Electric works complete lab	1	350000	3	Building Modification
6427	Room 105 lab development/ upgradation (06 items)	1	350000	3	Building Modification
6428	Room 105 lab development/ upgradation (02 items)	1	350000	3	Building Modification
6431	Vinyl Flooring (306 sq. ft.) with installation	2	350000	3	Building Modification
6438	Shipping	3	350000	3	Transport
6457	‘AZ360’ 360º azimuth rotation range 	2	350000	3	Parts
6458	‘EL90’ 90º elevations angle 	3	350000	3	Parts
6460	‘MOTION CONTROL’ Servo Speed Control	5	350000	3	Parts
6463	B15 (Sat PC I)	8	350000	3	Other
6464	PC-Software for ‘LEOs’ ‘SatPCl’	9	350000	3	Other
6480	‘AZ360’ 360º azimuth rotation range 	4	350000	3	Software
6481	‘EL90’ 90º elevations angle 	5	350000	3	Software
6482	‘DATA’ RS232 Interface	6	350000	3	Software
6483	‘MOTION CONTROL’ Servo Speed Control	7	350000	3	Software
6670	Printing of poster for lab	175	350000	3	Other
6674	Printing of books	179	350000	3	Other
6789	CAT6-04 Pair, 200 ft for OAS Machine & installation charges	2	350000	3	Equipment Installation
6783	Drilling work	288	350000	3	Fabrication
4724	Launcher Work area Extension and earth filling work and extension in fiber shed 	1	350000	3	Building Modification
7095	Shifting of water pipeline	3	300000	7	Other
7094	Excavation work	2	300000	7	Other
7150	ShipmentFreight, Shipment Handling and Export processing at both ports	3	350000	3	Other
7153	ShipmentFreight, Shipment Handling and Export processing at both ports	3	350000	3	Other
7157	ShipmentFreight, Shipment Handling and Export processing at both ports	3	350000	3	Other
8687	Teflon Rod (20mm)	24	350000	2	Raw Material
7165	Replacement of inner AC unit in room 117	1	350000	3	Equipment Repairs & Maintenance
7170	Replacement of circuit breaker of AC in NDT lab room 103	3	350000	3	Equipment Repairs & Maintenance
7167	Replacement of door lock room 115	2	350000	3	Other
7175	TA/DA of SRT Muhammad Asif (M/s RIMS Jajja visit)	1	350000	3	Travel/Boarding/Lodging
7183	Power coating & laser cutting	14	350000	3	Fabrication
7181	TA/DA Waleed bin Yousuf (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7182	TA/DA Ahmed Ali (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7188	TA/DA Jawwad Zahed (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7189	TA/DA Rashad Reyaz (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7190	TA/DA Wamiq Sheikh (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7192	TA/DA Hamza Hashmi (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7194	TA/DA Muhammad Asif (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7195	TA/DA M Iftikhar (JNB Ormara visit - 27 Feb to 03 Mar 20)	1	350000	3	Travel/Boarding/Lodging
7198	TA/DA Waleed bin Yousuf (Turbat Visit - 21 to 24 Apr 20)	1	350000	3	Travel/Boarding/Lodging
7199	TA/DA Ahmed Ali (Turbat Visit - 21 to 24 Apr 20)	1	350000	3	Travel/Boarding/Lodging
7205	TA/DA Wamiq Sheikh (Turbat Visit - 21 to 24 Apr 20)	1	350000	3	Travel/Boarding/Lodging
7207	TA/DA Muhammad Asif (Turbat Visit - 21 to 24 Apr 20)	1	350000	3	Travel/Boarding/Lodging
7209	TA/DA M Iftikhar (Turbat Visit - 21 to 24 Apr 20)	1	350000	3	Travel/Boarding/Lodging
7211	Ac 4 ton complete service and gas recharge	1	350000	3	Equipment Repairs & Maintenance
7179	Brass raw material & manufacturing - LPDA	12	350000	3	Fabrication
7212	Lunch Nathiagali 25 Mar 19	1	350000	2	Other
7227	Lunch & Tea Nathiagali 27 Nov 19	8	350000	2	Other
7223	Lunch Nathiagali 26 Nov 19	6	350000	2	Other
7220	Lunch Gharo 02 Nov 19	5	350000	2	Other
7216	LunchNathiagali 29 Jun 19	3	350000	2	Other
7215	Lunch Nathiagali 27 Jun 19	2	350000	2	Other
7391	Installation of cameras, projectors bracket & sound system	13	250000	3	Equipment Installation
7495	TA/DA and airfare M Furqan Siddiqui	2	350000	3	Travel/Boarding/Lodging
7489	TA/DA and airfare Rashad Reyaz	1	350000	3	Travel/Boarding/Lodging
7505	Cabiling, Harnessing and Installation Services	6	250000	3	Equipment Installation
7571	MS structure size L=23FT*H=10FT*W= 7ft	1	250000	3	Other
7572	Fiber structure size: L=32ft*H= 10ft*W= 7ft	2	250000	3	Other
7573	Extra floor work	3	250000	3	Building Repairs & Maintenance
8706	Rivnuts	43	350000	7	Parts
7574	Additional camera fixing work	4	250000	3	Other
7588	Dismantling and re-installation of 01x old standing AC	7	250000	3	Equipment Installation
7593	Development of resolver to digital prototype	1	200000	3	Consultancy
7595	Development of synchro to digital converter	1	200000	3	Consultancy
7901	Freight/handling	3	350000	3	Transport
7673	AC Service	2	250000	3	Equipment Repairs & Maintenance
7674	Leak Repair	3	250000	3	Equipment Installation
7675	Gas Recharge	4	250000	3	Equipment Repairs & Maintenance
7678	Circuit Repair	1	250000	3	Equipment Repairs & Maintenance
7679	Gas Recharge and Valve change	2	250000	3	Equipment Repairs & Maintenance
7180	Brass raw material & manufacturing - Yagi Antenna	13	350000	3	Fabrication
6872	Jammer Manual printing	1	350000	3	Other
4471	Concrete Floor for steel fabrication (33’x20’) Earth filling, Stone pitching and concreting	1	350000	3	Building Modification
8869	Zip tie	14	350000	2	Raw Material
7773	Installation of lugage carrier on roof top	1	350000	3	Equipment Modification
7774	Fabrication and installation of mounts on right side and roof top	2	350000	3	Equipment Modification
7775	Installation of antenna connector and cable rigging	3	350000	3	Equipment Modification
7776	Fabrication, painting and installation of ladder on rear side	4	350000	3	Equipment Modification
7777	Fabrication and installation of partition walls with fabric coating	5	350000	3	Equipment Modification
7778	Removal of passenger seats	6	350000	3	Equipment Modification
7779	Fabrication and installation of table as per drawing	7	350000	3	Equipment Modification
7799	Internet, cable & gas service charges	4	350000	3	Other
8686	Thinner	23	350000	2	Raw Material
7801	Black cartridge refill for printer	6	350000	3	Equipment Repair & maintenance
7803	VSS Manual Photocopy	8	350000	3	Other
7808	AC repair	13	350000	3	Building Repairs & Maintenance
7834	Labor (steel bender/fixer, shuttering & mason works)	2	350000	3	Equipment Installation
7835	Transportation of material	3	350000	3	Equipment Installation
7837	Provision and installation of revolving operator chair	8	350000	3	Equipment Modification
7838	Provision and installation of 40” LED on front partition	9	350000	3	Equipment Modification
7839	Rigging of power supply from vehicle power system to command center and LED	10	350000	3	Equipment Modification
7840	Provision and installation of invertor for LED supply	11	350000	3	Equipment Modification
7841	Provision and installation of alternate power supply system for operation through generator	12	350000	3	Equipment Modification
7843	Fabrication of stowage for generator in store	14	350000	3	Equipment Modification
7844	Capacity enhancement of power system	15	350000	3	Equipment Modification
7845	Concrete floor for GRP Fabrication (37’x26’) Earth filling ,Stone Pitching and concreting	1	350000	3	Building Modification
7916	Habitability charges (cleaning gear & refreshments)	15	350000	3	Other
7846	Canopy for GRP Fabrication Area (37’x26’x12’) Pipe support structure & 2mm fiber glass sheet	2	350000	3	Building Modification
7708	Pipe for Wash Basin	1	160000	3	Other
7709	Sannitory Labor Work	2	160000	3	Other
7726	Microwave oven Dawlence	1	160000	3	Other
7750	Sink Mixture	3	160000	3	Other
7749	Deep Fryer	2	160000	3	Other
7748	Hot Plate	1	160000	3	Other
7869	Installation of lugage carrier on roof top	23	350000	3	Equipment Modification
7870	Fabrication and installation of mounts on right side and roof top	24	350000	3	Equipment Modification
7871	Installation of antenna connector and cable rigging	25	350000	3	Equipment Modification
7872	Fabrication, painting and installation of ladder on rear side	26	350000	3	Equipment Modification
7873	Fabrication and installation of partition walls with fabric coating	27	350000	3	Equipment Modification
7874	Fabrication and installation of table as per drawing	28	350000	3	Equipment Modification
7875	Provision and Installation of revolving operator chair	29	350000	3	Equipment Modification
7876	Provision and installation of 40” LED on front partition	30	350000	3	Equipment Modification
7877	Rigging of power supply from vehicle power system to command center and LED	31	350000	3	Equipment Modification
7878	Provision and installation of invertor for LED supply	32	350000	3	Equipment Modification
7879	Provision and installation of alternate power supply system for operation through generator	33	350000	3	Equipment Modification
7881	Fabrication of stowage for generator in store	35	350000	3	Equipment Modification
7882	Capacity enhancement of power system	36	350000	3	Equipment Modification
7888	DHL cost (Shipping to Pakistan)	3	350000	3	Transport
7898	Freight/handling	3	350000	3	Transport
7917	VTS van modification and finalization	16	350000	3	Equipment Modification
7919	Internet charges	18	350000	3	Other
7920	printer toner refills	19	350000	3	Equipment Repair & maintenance
7922	Vehicle repair (alignment & seat welding)	21	350000	3	Equipment Repair & maintenance
7928	Shifting of table, pegboard, cabinet and drawer set	5	350000	3	Other
7930	Sealing of Aquarium box	7	350000	3	Building Modification
7939	Sealing of perforated wall with dual ply and frame	16	350000	3	Building Modification
7940	Sealing of ventilators (roshan daans)	17	350000	3	Building Modification
7948	Electrician Labor	3	350000	3	Other
7950	Electric works in UAV Lab, Including plugs and fittings	5	350000	3	Building Modification
7965	PCB Manufacturing	2	350000	3	fabrication
7966	PCB Casing Print	3	350000	3	fabrication
8013	Parser MCNV fabrication	26	350000	3	Fabrication
8014	3D printing pcb casing without finishing	27	350000	3	Other
8047	White paint	15	350000	3	Other
8048	Internet charges	16	350000	3	Other
8059	LoS VTS Van Manuals printing & plastic coating	2	350000	3	Other
8097	AC repair	8	350000	3	Equipment Repair & maintenance
8098	Jazz internet recharge	9	350000	3	Other
8138	Labor (cutting, fabrication, welding, sand blasting, painting, rigging and assembling	5	350000	3	Equipment Installation
8139	Transportation of material	6	350000	3	Transport
8140	Labor Transport	7	350000	3	Transport
8145	False ceiling work	5	350000	3	Equipment Modification
8146	Electrical labour	6	350000	3	Other
8149	AC fitting	9	350000	3	Building Modification
8151	Cable Laying	11	350000	3	Other
2018	90WMPL225 WR90 Waveguide Medium Power Load 8.2-12GHz with Rectangular Waveguide Interface	1	350000	7	Machinery / Equipment
1934	Design and construction of pre engineering building	1	300000	7	Other
8440	Complete  hall area 2x2 for tile China made for complete Lab	1	350000	3	Other
8412	Lathe machine repair charges	65	350000	3	Equipment Repairs & Maintenance
8413	GPU repair charges	66	350000	3	Equipment Repairs & Maintenance
8415	Internet recharge	67	350000	3	Other
8425	Freight/Handling/Customs	3	350000	3	Other
8442	Electric work complete for hall area	3	350000	3	Other
8443	Wall paint work with matt finish	4	350000	3	Other
8454	Drilling work of 1mm holes on all four side walls and filling of industrial grade thermopore insulation netween two walls of aluminum	13	350000	3	Other
8459	Complete riveting and screwing between two sheets of aluminum used in each subframe of the Farady's cage	18	350000	3	Other
8204	Installation charges	2	160000	3	Equipment Installation
8208	Cannel patti 16/25	4	160000	3	Other
8234	Labour charges for light/lamps installation =	10	160000	3	Other
8297	Upgradation/Modification of 25 mm Gun	1	160000	3	Equipment Modification
8460	Excavation work for earthing pit. Area to be covered 1.5m and depth of 3m	19	350000	3	Other
8463	Connections between the earth plate and surface to be installed for overall earthing system	22	350000	3	Other
8464	Powder paint for the complete frame and sheets to be used in the cage	23	350000	3	Other
8468	Conductive Nickel Coating 3805	27	350000	3	Other
8469	Revamping of workstation	1	450000	3	Other
8471	Conductive Nickel Coating 3807	28	350000	3	Other
8475	Complete Networking System, ESD Mats and Wooden Table inside Faraday Cage	32	350000	3	Other
8870	Glue Sticks	15	350000	2	Raw Material
8321	Box machining	10	350000	3	Fabrication
8447	sdfasd	1	200000	7	Parts
8563	3D CAD Model	1	200000	3	Other
8565	Paint of Super HET LF and OMNI IFM LF Structures	3	200000	3	Other
8566	Paint of Super HET HF and OMNI DF OMNI IFM Cross beam, Self Test and GPS PT Structures	4	200000	3	Other
8567	Installation	5	200000	3	Equipment Installation
8568	Synchro to Digital Interface 01 channel (50% payment)	1	200000	3	Consultancy
8654	Final testing and acceptance of user input forms, user management and visualization in fully integrated software solution with Backend and networking. (Milestone 4)	4	200000	3	Other
8569	Ruggedized Enclosure	1	200000	7	Parts
8618	Increase in thickness of glass from 40 mm to 53 mm (thickness change due to change of placement)	1	300000	3	Equipment Installation
7923	FCPM-6000RC\nIntg. Frequency Counter & power meter\nCounter Type: N/BNC	1	350000	7	Machinery/Equipment
8658	StartKit Magdrive 1000\r\nInterface 0-10VDC ("dongle")\r\nOrigin: Sweden	1	350000	7	Parts
5318	MAAD-011021-SMB Sample BRD Attenuator DC 30 GHZ	1	200000	7	Parts
8623	12 V DC-DC Modules (For FFC Power Supply)	2	200000	7	Parts
8621	5V DC-DC Modules (FFC Power Supply)	1	200000	7	Parts
8647	AC TO DC Module (For FFC Power Supply)	5	200000	7	Parts
8625	24 V DC-DC Modules (For FFC Power Supply)	4	200000	7	Parts
8624	15 V DC-DC Modules (For FFC Power Supply)	3	200000	7	Parts
8649	Main PCB (For 400Hz Power Supply)	7	200000	7	Parts
8648	Back Pane PCB (For FFC Power Supply)	6	200000	7	Parts
8695	RF Mixer IC 5-12 GHz	32	350000	7	Parts
8694	Switching IC SPDT 8SOIC	31	350000	7	Raw Material
8693	OpAmp IC GP 1 CIRCUIT 8SOIC	30	350000	7	Parts
8692	Zener Diode 47V 5W AXIAL	29	350000	7	Parts
8691	Zener Diode 51V 5W AXIAL	28	350000	7	Parts
8690	Zener Diode 56V 1.3W DO41	27	350000	7	Parts
8689	Regulator IC	26	350000	7	Parts
8688	RF Switch for Biasing Circuit (SPDT 20GHZ 24QFN)	25	350000	7	Parts
8684	Paint brush 4"	21	350000	7	Raw Material
8683	Paint brush box	20	350000	7	Raw Material
8682	Cobalt (Liter)	19	350000	2	Raw Material
8679	Polyester hardner (Liter)	17	350000	2	Raw Material
8676	Gel coat (KG)	15	350000	2	Raw Material
8675	Glass Fiber (Meter)	14	350000	2	Raw Material
8674	Hartford lubrication oil	13	350000	2	Tools
8671	Top Plate (320mm x 260mm x 15mm)	11	350000	7	Parts
8670	Bottom Plate (320mm x 260mm x 70mm)	10	350000	7	Parts
8669	Fasteners Assorted (M3, M2.5 etc)	9	350000	7	Parts
8667	Flanges EDM	7	350000	7	Parts
8666	EDM of Top Adapter	6	350000	7	Parts
8664	Magic Tee (OD 80mm x 200mm)	4	350000	7	Parts
8663	Top Plate (350mmx210mmx20mm)	3	350000	7	Parts
8662	Bottom Adapter (350mmx210mmx70mm)	2	350000	7	Parts
9484	Resin	7	350000	2	Raw Material
8677	Polyester resin (KG)	16	350000	2	Raw Material
8913	LFXTAL003240Bulk Crystals 16.0MHz	16	200000	7	Parts
8798	N type (M) to N type (M) Connector	1	200000	7	Parts
8799	N type (M) to SMA (F)	2	200000	7	Parts
8800	SMA (M) to SMA (F) Connector	3	200000	7	Parts
8801	SMA (F) to SMA (F) Connector	4	200000	7	Parts
8802	SMA load 50 ohm	5	200000	7	Parts
8653	Delivery of user interface for visualization and analysis of data (Milestone 3)\r\na. UX sketches and wireframe \r\nb. Acceptance of clickable prototype\r\nc. Integration with backend through mutually agreed APIs	3	200000	2	Software
8697	Coverall	34	350000	7	Parts
8713	Capacitor 600S330FW250XT	48	350000	7	Parts
8712	Inductor GA3095	47	350000	7	Parts
8708	RTV Sealant	45	350000	7	Parts
8705	Rivets	42	350000	7	Parts
8707	Thermal Paste	44	350000	2	Fabrication Machinery
8703	Supporting arm (100*25*25)	40	350000	7	Fabrication Machinery
8702	Box top lid (600*200*20)	39	350000	7	Fabrication Machinery
8701	Box bottom lid (600*200*20)	38	350000	7	Fabrication Machinery
8700	Aluminum Sheet 2mm	37	350000	7	Fabrication Machinery
8699	Stand off (500*60*40)	36	350000	7	Parts
8698	Safety shoes	35	350000	7	Parts
8736	SN74AHC245DW 20SOIC IC	1	200000	7	Parts
8737	SN54LS245J Buffers IC DIP	2	200000	7	Parts
8738	3DK2B Transistors	3	200000	7	Parts
8771	SN54LS245J Buffers IC DIP	1	200000	7	Parts
8774	Dell PowerEdge R7525 Server, 02 x AMD 7352 2.30GHz, 24C/48T,128M,155W,32000, 2 x 32GB RDIMM, 3200MT/s, Dual Rank          02 x 1.2TB Hard Drive SAS 12Gbps 10k 512n 2.5inHot-Plug, 16 x 2.5 SAS/SATA + 8X 2.5 NVME without XGMI, 01 x BOSS controller card + with 2 M.2 Sticks 240G(RAID 1), LP 02 x Dell EMC PowerEdge 10/25GbE Dual Rate SFP28 SR 85C Optic or all SFP28 ports,    01 x Broadcom 57414 Dual Port 10/25GbE SFP28, OCP NIC 3.0	1	200000	7	IT Equipment
8797	Generator fuel	15	350000	2	POL
8792	3s 2200mah lipo	11	350000	7	Other
8793	3s 5000mah lipo	12	350000	7	Other
8837	Male Header	30	350000	7	Raw Material
8835	GaN HEMT	28	350000	7	Raw Material
8832	Operational Amplifiers - Op Amps 2.2-nV/ Hz, low - power, 36-V operational amplifier 8-SOIC -40 to 125	25	350000	7	Raw Material
8819	Silicon RF Capacitors / Thin Film 250volts 11pF 1%	12	350000	7	Raw Material
8818	Thick Film Resistors - SMD 3/4watt 120ohms 1% High Power AEC-Q200	11	350000	7	Raw Material
8831	Zener Diodes 4 Volt 0.5W 5% AUTO	24	350000	7	Raw Material
8817	Thick Film Resistors - SMD 3/4watt 1ohms 1% High Power AEC-Q200	10	350000	7	Raw Material
8816	Current Sens Resistors - SMD 1206 0ohm Jumper 5% CS Mtl Strp AEC-Q200	9	350000	7	Raw Material
8820	Silicon RF Capacitors / thin Film 250volts 1.6pF	13	350000	7	Raw Material
8808	IC REG CHARG PUMP -4.5V 8MSOP	1	350000	7	Raw Material
8809	DIODE ZENER 51V 5W AXIAL	2	350000	7	Raw Material
8810	DIODE ZENER 47V 5W AXIAL	3	350000	7	Raw Material
8811	IC REG LINEAR 5V 50MA 8MSOP	4	350000	7	Raw Material
8812	RES SMD 510 OHM 5% 1/2W 0805	5	350000	7	Raw Material
8813	RES SMD 680 OHM 5% 1/8W 0805	6	350000	7	Raw Material
8814	RES 3502 300R 1%	7	350000	7	Raw Material
8815	RF Inductors - SMD 1812 33nH Unshld 5% 3A 4.8mOhms AECQ2	8	350000	7	Raw Material
8830	Diodes - General Purpose, Power, Switching 300V 400mW	23	350000	7	Raw Material
8829	Zener Diodes 500mW 14.26V Zener	22	350000	7	Raw Material
8828	MOSFEET 80V 50A 136W 25.2mohm @ 10V	21	350000	7	Raw Material
8827	Nexperia BST82/SOT23/TO-236AB	20	350000	7	Raw Material
8826	Logic Gates Quad 2-input Positive-OR gates	19	350000	7	Raw Material
8803	SMA (M) to SMA (M) Connector	6	200000	7	Parts
2808	Electrical Labour	5	350000	3	Other
2811	AC fitting	8	350000	3	Other
2813	Labour charges main cable laying	10	350000	3	Other
2826	Radome paint	11	350000	3	Equipment Modification
2827	Transportation	12	350000	3	Other
8650	Signing of agreement (Milestone 1)	1	200000	3	Other
8844	IC Holder DIP 28	37	350000	7	Raw Material
8651	Delivery of user input forms and user management (Milestone 2)\r\na. UX sketches and wireframe \r\nb. Acceptance of clickable prototype\r\nc. Integration with backend through mutually agreed APIs	2	200000	2	Software
8772	3DK2B Transistors	2	200000	7	Parts
8876	8-pin click mate connector	21	350000	7	Raw Material
8871	Tellies	16	350000	7	Raw Material
8872	Tie Tags	17	350000	7	Raw Material
8873	MR-30 Connectors	18	350000	7	Raw Material
8874	5-pin click mate connector	19	350000	7	Raw Material
8875	6-pin click mate connector	20	350000	7	Raw Material
8877	SMA convertor cable	22	350000	7	Raw Material
8857	H13 metal stock combustor (310mmx62.5mmx110mm)	2	350000	7	Raw Material
8858	H13 shaft (30mmx500mm)	3	350000	7	Raw Material
8859	MDF 16mm	4	350000	7	Raw Material
8860	MDF 25mm	5	350000	7	Raw Material
8861	Electric Panel 12"x18"x8"	6	350000	7	Office Equipment
8862	GX-16 I/O connector (2-pins)	7	350000	7	Raw Material
8863	GX-16 I/O connector (3-pins)	8	350000	7	Raw Material
8864	Cable Glands PG-9	9	350000	7	Raw Material
8865	Cable Glands PG-29	10	350000	7	Raw Material
8866	Mesh Electric flexible duct 35mm	11	350000	7	Raw Material
8867	Mesh Electric flexible duct 5mm	12	350000	7	Raw Material
8868	Heat Shrinks	13	350000	7	Raw Material
8956	USB to 485 Module	30	350000	7	Parts
8951	Ethernet cord	25	350000	7	Parts
8952	USB to USB cable	26	350000	7	Parts
8953	4-core Signal cable (GPS)	27	350000	7	Parts
8954	5-core cable Signal+Power (Aeromarine)	28	350000	7	Parts
8955	DB9 Connector (F)	29	350000	7	Parts
8957	USB to TTL Module	31	350000	7	Parts
8958	Power Distribution Strip	32	350000	7	Parts
8959	Power cable 1.5mm	33	350000	7	Parts
8960	Soldering Wire 1 spool	34	350000	7	Raw Material
8961	K400 plus Keyboard	35	350000	7	IT Equipment
8962	8'x4'x2mm AI sheet	36	350000	7	Raw Material
8518	IGBTs	5	350000	2	Other
8964	Powder coat	38	350000	2	Raw Material
8914	MAX232ACSE+ IC RS-232 DRVR/RCVR 16-SOIC	17	200000	7	Parts
8915	Cable 10 Core with shield 45 meter	18	200000	7	Parts
8916	Onboard Maegraph box (CNC Mechined Aluminum Enclosur) wirh Standard COST items and Accessories (Ref. Drawing)	1	200000	7	Parts
8542	PC Motherboard repair	28	350000	3	Equipment Repairs & Maintenance
8543	Internet recharge	29	350000	3	Other
2995	Sheet bending operation	5	350000	3	Other
5341	EDM charges	6	350000	3	Other
5342	Bending charges	7	350000	3	Other
5350	Internet recharge at NDT	15	350000	3	Other
8966	IC Gate DRVR low-side 8SOIC	40	350000	7	Parts
8967	RES 0.01 Ohm 5% 1/4W 1206	41	350000	7	Parts
8968	MOSFET P-CH 30V 50A PPAK SO-8	42	350000	7	Parts
8965	RES 0.01 Ohm 1% 1/2W 1206	39	350000	7	Parts
9400	Storage SD Card & Module	6	350000	2	Other
2693	Civil Works (Foundation Laying, Control Room Construction [10ft L x 10ft W x 10ft H], Walkway [3ft wide from control room to adjacent road], paint works	1	350000	3	Building Construction
2694	Steel Structure (Workshop Fabrication, Preservation, Installation at RDS MIANWALI, Assistance in RADAR installation)	2	350000	3	Building Construction
9374	Metal gear water proof servo	12	350000	7	Parts
2695	Mobilization / Demobilization (Karachi to Jinnah Naval Base, JNB to RDS MIANWALI)	3	350000	3	Other
8619	Increase in water proofing chemical for frame area	2	300000	3	Building Repairs & Maintenance
8620	Change of thickness in frame of SS-316 to 4 mm from 3 mm	3	300000	3	Equipment Modification
8977	Jazz internet INTELL, RF Lab, NDT Lab	50	350000	3	Other
8978	Internet cable charges for MF	51	350000	3	Other
8979	Spray paint for MF chairs	52	350000	3	Equipment Repairs & Maintenance
8833	Biasing Circuit CW for power Amplifiers	26	350000	7	Raw Material
8834	Matching Circuit of power Amplifier for L-band & S-band (25W)	27	350000	7	Raw Material
8836	Aluminum sheet (950*950*20)	29	350000	7	Raw Material
8838	Potentiometer 10k	31	350000	7	Raw Material
8839	Capacitor 0.1uF	32	350000	7	Raw Material
8840	Capacitor 1uF	33	350000	7	Raw Material
8841	Resistor 10k	34	350000	7	Raw Material
8842	Resistor 390 ohm	35	350000	7	Raw Material
8843	Microcontroller ATMEGA328-20P	36	350000	7	Raw Material
8852	Sim800L	45	350000	7	Raw Material
8845	IC Holder DIP 8	38	350000	7	Raw Material
8846	Check IRFZ44N	39	350000	7	Raw Material
8847	Jst 2 pin	40	350000	7	Raw Material
8851	Ir Sensor TCRT5000	44	350000	7	Raw Material
8850	IC Holder DIP 8	43	350000	7	Raw Material
8849	OpAmp LM393N	42	350000	7	Raw Material
8848	Jst 6 pin	41	350000	7	Raw Material
8853	Reed Switch	46	350000	7	Raw Material
8975	CF rods (1000 mtr)	48	350000	7	Raw Material
8976	Thermopole sheet	49	350000	7	Raw Material
8980	750 watt gold supply	53	350000	7	Office Equipment
8982	Power Cable for Testing of Lab Prototype for Simulation Purposes	1	300000	7	Machinery / Equipment
8983	High Power Filter Module for 30KW Power Supply Unit	2	300000	7	Machinery / Equipment
8985	Reception Table	2	300000	7	Furniture
8987	Executive office table with side rack and LED rack mobile box	4	300000	7	Furniture
8986	Office Table	3	300000	7	Furniture
8984	Lamination workstaton	1	300000	7	Furniture
8988	Lawn area leveling/ soiling/ fertilizer treatment	1	300000	3	Other
8989	Purchase and placement expenses of pavers in the lawn area	2	300000	3	Other
8411	Antenna (Taisync + RFD)with RF cable	64	350000	2	Parts
4450	Sym-25DLHW\r\nMini circuits	17	350000	2	Parts
8180	Tmotor AS2814 motor	2	350000	2	Parts
6444	USRP X310 Kit\r\n(Kintex7-410T FPGA, 2 Channel, 10GIGE and PCle Bus)	1	350000	7	Parts
5758	Dell G7 15 G7588 Laptop\r\n8th Gen Ci7 8750H\r\n16GB RAM, 128GB SSD, 1 TB HDD,\r\nNVidia GeForce GTX 1060 6GB GC, \r\nWindows 10 & Backlit Keyboard (with one year local warranty)	1	350000	7	Office Equipment
8389	Power plug 3 pin	48	350000	2	Parts
8993	Servo Driver - MFDA43A1A	1	200000	7	Parts
8994	Servo Driver Motor - MFMA042A1H	2	200000	7	Parts
8995	ScanDisk CF CompactFlash	3	200000	7	Parts
8990	Display Pannels CDS	1	200000	7	Parts
8991	DC/DC Modules	2	200000	7	Parts
9361	Pilot Tube	1	350000	2	Other
9232	CIMC-3500-TPS-03\r\nShelter CIMC-3500-TPS-03 Large (3500mm)\r\nCore and Shell with Paint, EMI/EMC\r\nshielding, Shelter CIMC-3500-TPS-03 Large\r\n(3500mm) Core and Shell ready for \r\nmounting on ISUZU Truck	1	350000	7	Machinery / Equipment
9383	2.0 Ton Ceiling Cassatte AC Unit	1	300000	7	Appliance
9384	Fitting Accessories kit for ceiling uni	2	300000	3	Equipment Installation
9385	Installation	3	300000	3	Equipment Installation
9386	False Ceiling of the observatory room	4	300000	3	Building Modification
9362	Air Speed Sensor	2	350000	2	Other
9363	Safety Apron	3	350000	2	Other
9364	HMI CAT 6 signal cable	4	350000	2	Other
9365	Power Cable 25A 6awg	5	350000	2	Other
9366	Industrial Socket 32A	6	350000	2	Other
9367	Power Cable (40x76)	7	350000	2	Other
9368	Mesh HMI wire covering	8	350000	2	Other
9369	Fuel for Testing	9	350000	2	POL
9370	Aluminum (115mm x 115mm x 60mm) weight 2.3 kg each	10	350000	2	Raw Material
9373	Aluminum (55mm x 105mm x 45mm) weight 0.45kg each	11	350000	2	Raw Material
9376	Brass Plate for Reflector	14	350000	2	Raw Material
9377	Fiber Rods	15	350000	2	Raw Material
9378	Elbows	16	350000	2	Other
9379	UPS breaker for MF	17	350000	7	Other
9380	Mouse and Wifi Dongle	18	350000	7	IT Equipment
9395	MS Rods	1	350000	2	Raw Material
9396	MCU	2	350000	2	Other
9397	Temperature Sensor	3	350000	2	Other
9427	Teflon (300mm X 200mm)	1	350000	2	Raw Material
9428	Outsource machining	2	350000	3	Consultancy
9381	Jazz Internet recharge for NDT & INTELL Lab	19	350000	3	Other
9382	INTELL AC Repair & Maintenance	20	350000	3	Equipment Repairs & Maintenance
9387	PVC pipe	21	350000	2	Other
9392	gfrdgfjhg	1	300000	7	Equipment Installation
9394	thfdhtmhgv	2	300000	3	Equipment Installation
9418	785622-02\r\ncRIO-9042, 1.6 GHZ Quad-Core, 70T FPGA, RT, 4-Slot, XT, CC	1	300000	7	Machinery / Equipment
9419	779892-02\r\nNI 9871 4-Port RS422/RS485 Serial Module (Module Only)	2	300000	7	Machinery / Equipment
9420	779003-01\r\nNI 9474 8-Ch 24 V, 1 us, High-Speed Sourcing DO	3	300000	7	Machinery / Equipment
9421	779891-02\r\nNI 9870 4-Port RS232 Serial Module (Module Only)	4	300000	7	Machinery / Equipment
9422	120AS12-1\r\nAC-DC CONVERTER DIN RAIL 10A 12V Power Supply	5	300000	7	Machinery / Equipment
9165	Joy Stick	5	450000	7	Parts
9166	Relay	6	450000	7	Parts
9167	Contactor	7	450000	7	Parts
9168	Breaker	8	450000	7	Parts
9398	RTC	4	350000	2	Other
9399	Button cell	5	350000	2	Other
9424	Battery pack 24V 150A dry cell	1	300000	2	Other
9425	Battery 16.8V 17A dry cell	2	300000	2	Other
9426	PVC Cable 02 core 35mm	3	300000	2	Raw Material
9389	Glass Fiber	22	350000	2	Raw Material
9390	Resin	23	350000	2	Chemical
9391	Teflon Rod	24	350000	2	Raw Material
2730	MC Power Vise (VMC-130HV)	12	350000	7	Tools / Test Equipment
2719	End Mill Holder 16x150mm (3P TE90-216-16-06-L150)	1	350000	7	Tools / Test Equipment
9449	2.0 Ton Ceiling Cassatte AC Unit	1	300000	7	Appliance
9450	Fitting Accessories kit for ceiling unit	2	300000	3	Equipment Installation
9451	Installation	3	300000	3	Equipment Installation
9452	False Ceiling of the SRO room at SVDC	4	300000	3	Building Modification
9448	Repair of Graphic Cards \r\nBRAND/OEM: KIMPOK	1	350000	3	Equipment Repairs & Maintenance
9454	Glass fiber 450 gsm	2	350000	2	Raw Material
9430	410-248 ZEDBOARD ZYNQ-7000	1	200000	2	Other
8546	Civil Works (20ft high concrete tower) \r\nFoundation laying \r\nControl Room construction (10ft L X 10ft W X 10ft H) (as per drawing attached) \r\nWalkway (3ft wide) (from control room to adjacent road)\r\nPaint works	1	350000	3	Building Construction
8547	Mobilization / Demobilization Karachi to Turbat base	2	350000	3	Building Construction
2720	Insert (3PKT 060304R-M)	2	350000	7	Tools / Test Equipment
2721	End Mill Holder 20x170mm (3P TE90-220-20-10-L170)	3	350000	7	Tools / Test Equipment
2722	End Mill Holder 25x210mm (3P TE90-325-25-10-L210)	4	350000	7	Tools / Test Equipment
2723	Insert for Steel (3PKT 100408R-M)	5	350000	7	Tools / Test Equipment
2724	Insert for Hardened Steel (3PKT 100408R-M)	6	350000	7	Tools / Test Equipment
2725	Screw 3PKT 0603 (TS 20043I/HG-P)	7	350000	2	Other
2726	Screw 3PKT 1004 (TS 25C065I/HG)	8	350000	2	Other
2727	Insert (4NKT 060408R-M)	9	350000	7	Tools / Test Equipment
2728	Wrench (TD 6P)	10	350000	7	Tools / Test Equipment
2729	Wrench (TD 8)	11	350000	7	Tools / Test Equipment
9401	Relay 30V 30 A	7	350000	2	Other
9402	Thermister	8	350000	7	Parts
9403	Temperature Monitor	9	350000	7	Parts
9404	Mil Connector 4-Pin	10	350000	2	Other
9405	Mil Connector 7-Pin	11	350000	2	Other
9406	DC Voltage Display Module	12	350000	7	Parts
9407	DC Wire 6 Core-90 meters-voltage display module	13	350000	7	Parts
9408	GPS Module Neo m8n	14	350000	7	Parts
9409	USB to TTL CP210X	15	350000	2	Other
9410	DC wire 4 Core-10 meters- GPS Module	16	350000	7	Parts
9411	Filament ABS	17	350000	2	Other
9412	Filament PLA	18	350000	2	Other
9413	SSD NVME M.2 1TB	19	350000	2	Other
2731	Ground Parallel (VP-100)	13	350000	7	Tools / Test Equipment
9414	RS485	20	350000	2	Other
9416	GPS Antenna	21	350000	7	Parts
9417	M.2 Caddy	22	350000	2	Other
9432	Lid (Dia 280mm X 60mm)	1	350000	2	Raw Material
9433	Base (300mm X 90mm)	2	350000	2	Raw Material
9434	O ring seal	3	350000	2	Other
9435	Outsource machining	4	350000	3	Consultancy
9195	Frame ZD 850mm carbon fiber	2	160000	7	Parts
9196	ESC Hobbywing 40A	3	160000	7	Parts
9197	Propeller CF 15x5.5 Props	4	160000	7	Parts
9198	Power Module Cube Brick	5	160000	7	Parts
9199	Data Link / Controller SIYI Mk15-Enterprise (air + ground)	6	160000	7	Parts
9200	wiring / Hardware (assorted wiring, connectors, etc)	7	160000	7	Parts
9201	Expander Box SBUX Expander PCB	8	160000	7	Parts
9202	Strap/machining/antenna mounts	9	160000	7	Parts
9436	Dish	5	350000	7	Parts
9437	Aluminum Stock (600mm X 600mm X 25mm)	6	350000	2	Raw Material
9438	Aluminum Stock (600mm X 600mm X 50mm)	7	350000	2	Raw Material
9439	Aluminum Stock (600mm X 600mm X 75mm)	8	350000	2	Raw Material
9440	UD Aramid Fiber Fabric Rayon unidirectional cloth 0.19mm thickness 280g	9	350000	2	Raw Material
9441	Internet recharge at RF Lab	10	350000	3	Other
9442	UPS battery for OAS PC	11	350000	2	Chemical
9458	Aluminum rod 10ft (3 cm) imported	6	350000	2	Raw Material
9459	Teflon block for T part (High quality Nylon)	7	350000	2	Raw Material
9460	PVC supporting rod	8	350000	2	Other
9461	M4 locking screw with nuts	9	350000	2	Other
9462	Teflon block for H part	10	350000	2	Raw Material
9463	Special 14mm tool with 200mm length	11	350000	7	Tools
9464	Copper wire mesh guage	12	350000	2	Other
9465	DP to HDMI converter	13	350000	2	Other
9466	DP cables	14	350000	2	Other
9445	Safety Posters at MF	12	350000	2	Other
9467	Powder Coating	15	350000	3	Consultancy
9468	Pins	16	350000	2	Other
9469	Aluminum Stock	17	350000	2	Raw Material
9470	Spare springs	18	350000	2	Other
9471	Spare threaded hinge pin	19	350000	2	Other
9472	Aluminum rod	20	350000	2	Raw Material
9473	Coaxial tee	21	350000	2	Other
9474	AC repair machine shop SPF & Drain pipes	22	350000	3	Equipment Repairs & Maintenance
9475	Jazz internet NDT lab	23	350000	3	Consultancy
9477	Internet charges at SPF	24	350000	3	Consultancy
9478	NDT lab printer refill and staff printer refill	25	350000	3	Equipment Repairs & Maintenance
9453	PVC Pipe	1	350000	2	Other
9455	Resin	3	350000	2	Chemical
9456	Teflon rod	4	350000	2	Raw Material
9457	Aluminum rod 12ft (1.41 cm)	5	350000	2	Raw Material
9479	Aluminum Rod	1	350000	2	Raw Material
9480	Glass Fiber Cloth	2	350000	2	Raw Material
9481	GCS antenna cable extensions (SMA)	3	350000	2	Other
9482	SMA connectors 1.6mm	4	350000	2	Other
9483	RG142 6 inch cables	5	350000	2	Other
9499	Male Female Connectors	6	350000	2	Other
9485	Nylon Block	8	350000	2	Raw Material
9486	Nylon Rod	9	350000	2	Raw Material
9487	USB (Kingston 32GB)	10	350000	7	Office Equipment
9488	CVBS to HDMI	11	350000	2	Other
9489	HDMI to Ethernet pair	12	350000	2	Other
9490	MMCX connector	13	350000	2	Other
9491	CVBS Splitter	14	350000	2	Other
9492	Pipe for Cables	15	350000	2	Other
9493	Steel Wire	16	350000	2	Raw Material
9495	Ethernet Cable	17	350000	2	Other
9496	Emergency Switch	18	350000	2	Other
9497	2 Core Cables	19	350000	2	Other
11697	Splitter Box	6	350000	2	Other
9498	Consumables (Soldering wire, Heat Sleeves, connectors, electric tape, labels, hot glue gun stick etc)	20	350000	2	Other
9582	-Gantry pillars uninstallation\r\n-Transportation to SVDC\r\n-Structural repair prior string to work\r\n-\r\n-	2	300000	3	Building Repairs & Maintenance
9559	Front Plate Bending charges	15	350000	3	Consultancy
9560	Copper Stock (100 X 30 X 40)	16	350000	2	Raw Material
9561	EDM Charges for avionics box	17	350000	3	Consultancy
9562	Screws	18	350000	2	Other
9597	Nuts and Bolts	8	350000	2	Other
9544	Turn Buckles	1	350000	2	Other
9545	Powder Coating	2	350000	3	Consultancy
9546	Soldering Iron	3	350000	7	Tools
9547	Threading Operation	4	350000	3	Consultancy
9549	Lid (Dia 280mm X 60mm) 13	5	350000	2	Raw Material
9598	Power Supply 150Vdc 12A	1	200000	2	Other
9588	Bending charges	1	350000	3	Consultancy
9590	Supporting Pipe	2	350000	2	Other
9591	Brass Plate 2mm	3	350000	2	Raw Material
9592	PVC Pipe	4	350000	2	Other
9594	PVC Pipe fitting	5	350000	2	Other
9595	Powder coating	6	350000	3	Consultancy
9599	Voltage devider Module	2	200000	2	Other
9600	Signal conditioning PCB	3	200000	2	Other
9563	Thrusters (24V/ 40A-35lbf Fwd Thrust-300m depth rated electrical motor)	1	300000	7	Machinery / Equipment
9566	Scanning Sonar (750kHz Mechanical Scanning Sonar, 50m range, 300m depth)	2	300000	7	Machinery / Equipment
9550	Base (300mm X 90mm)	6	350000	2	Raw Material
9551	Aluminum Stock	7	350000	2	Raw Material
9552	EDM Machining charges for reflector Box	8	350000	3	Consultancy
9553	TZT 10M-6GHz Lao Noise Amplifier Gain 20dB  High Flatness	9	350000	7	Parts
9554	RF Connectors	10	350000	2	Other
9555	Safety Apron	11	350000	2	Other
9556	Steel Alloy 4140 Stock (70mm X 100mm)	12	350000	2	Raw Material
9557	Strapes Stitching	13	350000	3	Consultancy
9558	Strapes Mount Bending charges	14	350000	3	Consultancy
9569	Echo Sounder (115kHz Single Beam Echo Sounder, 30m depth range, 300m depth)	3	300000	7	Machinery / Equipment
9564	Conn Adapt Plug to Jack N50 OHM	19	350000	2	Other
9578	-Disassembling/ de-welding of test tank components and water tight glass assemblies\r\n-Shifting through crane from existing room/ building on to truck\r\n-Transportation to SVDC\r\n-Assembling/ welding of all test tank components and water tight glass assemblies	1	300000	3	Equipment Repairs & Maintenance
9596	Rods (Teflon, Aluminum, Brass)	7	350000	2	Raw Material
9648	Copper Stock	2	350000	2	Raw Material
9649	EDM charges	3	350000	3	Consultancy
9606	1700C Tube Furnace Model/PN: SK2-5-17TPB3	1	350000	7	Machinery / Equipment
9602	Molecular Vacuum System:\r\nModel: FDS600/20, \r\nMain Pump: Molecular Vacuum Pump \r\nModel: FF-600/150\r\nBacking Pump: Dry Screw Vacuum Pump\r\nModel: DPS020,\r\nOther Accessories:\r\n1Pc Composite Vacuum Gauge, \r\n2-3 Pcs Vacuum Solenoid \r\n1Pc Frequency Converter \r\n1Pc Vacuum Precision Filter \r\nElectrical Protection \r\nVacuum Pipelines and Joints	1	350000	7	Machinery / Equipment
9622	SS Sheet	5	350000	2	Raw Material
9565	Conn Adapt N Plug to SMA Jack	20	350000	2	Other
9567	Conn Adapt N Jack to SMA Plug	21	350000	2	Other
9568	Conn Adapt N Jack to SMA Jack	22	350000	2	Other
9570	Conn Adapt SMA Plug to N Plug	23	350000	2	Other
9571	RF Cable LMR400 LLPX	24	350000	2	Other
9572	RF Connector N Type Straight Plug	25	350000	2	Other
9573	RF Connector N Type Straight Jack	26	350000	2	Other
9574	Jazz Internet RF Lab	27	350000	3	Other
9575	Jazz Internet NDT Lab	28	350000	3	Other
9576	AC Repair & Maintenance of NDT Lab & MF	29	350000	3	Equipment Repairs & Maintenance
9577	Battery	30	350000	2	Other
9623	Bending Charges	6	350000	3	Consultancy
9624	Laser Welding	7	350000	3	Consultancy
9626	Safety related panaflex printing and posters	8	350000	3	Consultancy
9627	WD40 200ml	9	350000	2	Chemical
9628	Double sided tape	10	350000	2	Other
9630	Polylactic Acid Filament (Sunlu PLA)	11	350000	2	Raw Material
9631	Filament Dryer (Ensun Ebox Life)	12	350000	7	Machinery / Equipment
9632	Filament Vacuum bag	13	350000	2	Other
11761	Paper tape	25	350000	2	Other
9633	Engraving bits (0.1mm x 10 deg x 10)	14	350000	2	Other
9617	Steel Alloy (4140) stock	1	350000	2	Raw Material
9619	Wire cutting	2	350000	3	Consultancy
9620	USB HUB	3	350000	2	Other
9621	SS Base part	4	350000	2	Raw Material
9635	Engraving bits (0.1mm x 15 deg x 10)	15	350000	2	Other
9637	Engraving bits (0.1mm x 20 deg x 10)	16	350000	2	Other
9638	Engraving bits (0.1mm x 25 deg x 10)	17	350000	2	Other
9639	EndMill bits (1mm x 10)	18	350000	2	Other
9640	EndMill bits (2mm x 10)	19	350000	2	Other
9641	Printer build plate	20	350000	2	Other
9642	Polyethermide PEI Sheet	21	350000	2	Other
9650	Industrial timer circuit (with clock) 5sec every minute	4	350000	2	Other
9651	Solenoid Valve (normally closed 24V) 100 bar	5	350000	2	Other
9652	DC Power Supply for circuit	6	350000	2	Other
9653	Panel Box	7	350000	2	Other
9731	MS 4140 Rod (30mm x 800mm)	2	350000	2	Raw Material
9654	Power Plug Industrial	8	350000	2	Other
9702	Wire tags	23	350000	2	Other
9655	Mil spec connector (2 pin)	9	350000	2	Other
9643	USB to RS422, RS485 Cable 2.50' (TL2509-ND)	22	350000	2	Other
9645	USB-RS422 Cable Wire End 1.8M (768-1042-ND)	23	350000	2	Other
9723	Purchase of paint for outer and inner areas/ labour for paint work	8	300000	2	Chemical
9713	gsgsgshsjkdkdkddk	1	350000	7	Parts
9987	Emery paper	34	350000	2	Other
9716	Wall sized glass panes (3’x8’)	1	300000	7	Other
9656	24v Relay for Solenoid Valve	10	350000	2	Other
9657	Pipe holder	11	350000	2	Other
9717	Window grill works	2	300000	7	Other
9718	Auxiliary room access shutter door	3	300000	7	Furniture
9719	Window Blinds	4	300000	2	Raw Material
9720	Fabrication and installation of acrylic flags and quranic ayat	5	300000	2	Raw Material
9721	Fabrication of acrylic box and wooden base for 3D ROV model	6	300000	2	Raw Material
9722	Fabrication of acrylic boards/ posters and room/ section name tallies	7	300000	2	Raw Material
9646	Monitor	1	350000	7	Office Equipment
9658	Solenoid Valve (normally closed 24V)	12	350000	2	Other
9659	4-Pin mil spec	13	350000	2	Other
9660	Pressure transmitter 160 bar	14	350000	2	Other
9661	Limit Switch	15	350000	2	Other
9663	Contactor 5KW (24V operated) 3-phase	16	350000	2	Other
9696	Relay K707-3PL	17	350000	2	Other
9697	Relay HKK-JQX 76-F	18	350000	2	Other
9698	Fuse 1A 24V + box	19	350000	2	Other
9699	Arduino UNO	20	350000	2	Other
9816	LNA IC	1	350000	2	Other
9818	Capacitor 9.1 pF	2	350000	2	Other
9822	Capacitor 100pF	3	350000	2	Other
9823	Capacitor 5.6pF	4	350000	2	Other
9829	Capacitor 0.1uF	9	350000	2	Other
9830	Graphic Cards GDDR6	10	350000	2	Other
9831	IPS Monitor	11	350000	7	IT Equipment
9832	RF consultation Manual	12	350000	2	Other
9833	SS Bolts	13	350000	2	Other
9834	512 HDD	14	350000	7	IT Equipment
9836	Petrol	15	350000	2	POL
9837	2pin Mil-Spec connector	16	350000	2	Other
9838	Window Blind for Office	17	350000	7	Other
9840	Wire cutting	18	350000	3	Consultancy
9841	Internet charges at MF	19	350000	3	Consultancy
9842	Battery of Spectrum Analyzer	20	350000	2	Other
9852	Keyboard K400 Plus	6	350000	7	IT Equipment
9853	LCD AOC 9E1H	7	350000	7	IT Equipment
9730	MS A36 sheet stock (400mm x 400mm x 34mm)	1	350000	2	Raw Material
9732	MS Rod (75mm x 225 mm)	3	350000	2	Raw Material
9733	Taper roller bearing charges	4	350000	2	Other
9734	PCB	5	350000	2	Other
9736	Capacitors 0.1uF	6	350000	2	Other
9854	Mil-Spec connector 4 pin	8	350000	2	Other
9855	Mil-Spec connector 2 pin	9	350000	2	Other
9856	Mil-Spec connector 5 pin	10	350000	2	Other
9857	Momentary Push Button	11	350000	2	Other
9858	NUC PC	12	350000	7	IT Equipment
9859	Patch Cord	13	350000	2	Other
9845	Aluminum sheet 8ft x 4ft	1	350000	2	Raw Material
9737	Capacitor 1uF	7	350000	2	Other
9738	Capacitor 0.33uF	8	350000	2	Other
9739	Capacitor 22pF	9	350000	2	Other
9740	Resistor 10kohm	10	350000	2	Other
9741	Crystal 16MHz	11	350000	2	Other
9742	Button 2 pin	12	350000	2	Other
9743	GSM SIM 800L	13	350000	2	Other
9849	Bending Charges	3	350000	3	Consultancy
9850	CH340 RS485 Module	4	350000	2	Other
9851	CP2102 Module	5	350000	2	Other
9744	MCU at mega 328P	14	350000	2	Other
9783	CCSMA-MM-LL42-36 SMA M to M	1	200000	2	Other
9825	Resistor 750 Ohm	6	350000	2	Other
9784	CCN-MM-LL335-36 N to N type	2	200000	2	Other
9785	FM-SR086TB SMA male to N Female	3	200000	2	Other
9786	FMC0102143-36 N male to SMA male	4	200000	2	Other
9787	Adaptr IC SOP-20P	5	200000	2	Other
9788	Adaptr IC SOP-28P	6	200000	2	Other
9789	Power Supplies DF CPU	7	200000	2	Other
9754	Nylon stock (250 mm x 300mm)	23	350000	2	Raw Material
9700	Din rail	21	350000	2	Other
9701	Screw terminal (5A individual)	22	350000	2	Other
9703	Misc items (tape, shrinks, mesh, connectors etc)	24	350000	2	Other
9704	LiPo Battery (3s 2200mah)	25	350000	2	Other
9782	Superpro 610P Programmer	2	200000	7	Test / Measuring Equipment
9705	LiPo Battery (3s 5000mah)	26	350000	2	Other
9843	FIP-410001 ChipMaster Compact Professional	1	200000	2	Other
9824	Resistor 267 Ohm	5	350000	2	Other
9826	Inductor 6.8nH	7	350000	2	Other
9828	Inductor 15nH	8	350000	2	Other
9803	MNM 1314B Programable IC Servo	1	200000	2	Other
9804	Xilinx XCV300 BG432AMP0341	2	200000	2	Other
9805	CD54HCT244F3A IC	3	200000	2	Other
9707	Bearing installation and calibration	28	350000	3	Consultancy
9708	High Octane Petrol	29	350000	2	POL
9709	Printers ink refill (INTELL Lab)	30	350000	3	Consultancy
9710	Laptop Charger	31	350000	7	Office Equipment
9711	HDMI to VGA convertor for projector	32	350000	2	Other
9712	Jazz Internet Charges NDT Lab and SPF Lab	33	350000	3	Other
9807	P/N DFA2510-27-2-6	2	200000	2	Other
9781	ChipMaster Digital IC tester abi	1	200000	7	Test / Measuring Equipment
9802	Water chiller dispenser	5	300000	7	Machinery / Equipment
9848	Powder Coating	2	350000	3	Consultancy
9844	Superpro 6100n Programer	1	200000	2	Other
9806	ZHFG-K2750+High Pass Filter	1	200000	2	Other
9798	Multipurpose table tops	1	300000	2	Raw Material
9799	Portable chairs for multipurpose section	2	300000	7	Furniture
9811	P/N DFA2510-27-2-10	3	200000	2	Other
9812	ALC-7122 Adlink Board	4	200000	2	Other
9813	Part Number ATTINY2313-20MU	5	200000	2	Other
9814	Value: 10 Ohm Pakage: 0201 or 0402	6	200000	2	Other
9815	Value: 5 nF Pakage: 0201	7	200000	2	Other
9800	Marble top workstation with sink access	3	300000	7	Other
9801	Storage/ filing cabinet	4	300000	7	Furniture
9790	IDT 74FCT 16245ATPA LB0339M	1	200000	2	Other
9792	IDT 74FCT 16374ATPV  205519	3	200000	2	Other
9791	IDT 74FCT 16245ATPAG LB0951RK	2	200000	2	Other
9793	ATMEL AT49F8011 70TC 140	4	200000	2	Other
9794	MD82C55A/B Programable	5	200000	2	Other
9795	MD82C55A-5 Programable	6	200000	2	Other
9796	DAC1210LCJ D TO A Convertor	7	200000	2	Other
9797	Panasonic MNM 1314 Controller	8	200000	2	Other
9903	High Speed Fan 24V	2	350000	2	Other
9904	8.4mm Seal	3	350000	2	Other
9860	Power Connector	14	350000	2	Other
9745	Male Header single strip	15	350000	2	Other
9746	Female Header Single strip	16	350000	2	Other
9748	JST 2 pin	17	350000	2	Other
9749	JST 6 pin	18	350000	2	Other
9750	Shielded DC wire 2 core	19	350000	2	Other
9751	Reed switch 5V	20	350000	2	Other
9752	ABS filament	21	350000	2	Other
9753	Ferrite Tape x5	22	350000	2	Other
9755	SMA plug, male pin to TNC	24	350000	2	Other
9757	TNC plug, male pin to SMA Jack, Female Socket	25	350000	2	Other
9758	SMA Jack, female to SMA  Jack, Female	26	350000	2	Other
9759	SMA plug, male to SMA plug male	27	350000	2	Other
9760	Bias Tee	28	350000	2	Other
9761	Military connector	29	350000	2	Other
9877	340WHPL5500 WR340 Waveguide High Power Load 2.2-3.3GHz with Rectangular Waveguide Interface	1	350000	7	Test / Measuring Equipment
9774	GI Sliding Door	1	350000	3	Building Construction
9762	Miscellaneous items (Res, LEDs, wires, DC adapters)	30	350000	2	Other
9763	Power Supply Unit	31	350000	2	Other
9765	Wireless N PCI Express adapter	33	350000	2	Other
9767	Black Tower chasis	34	350000	2	Other
9768	ARGB CPU cooler	35	350000	2	Other
9769	kingston NV2 500 GB SSD	36	350000	2	Other
9770	Intel Core i9 13900k Desktop Processor	37	350000	7	IT Equipment
9771	Intel Hard drive	38	350000	2	Other
9772	Motherboard Intel LGA DDR4	39	350000	7	IT Equipment
9773	Desktop Memory 32GB	40	350000	7	IT Equipment
9884	steel	1	350000	2	Raw Material
9861	RJ45 connectors	15	350000	2	Other
9862	USB HUB type C	16	350000	2	Other
9864	USB connectors	17	350000	2	Other
9865	DC Wire 2 Core 1mm 1m	18	350000	2	Other
9866	DC Wire 4 Core 1mm 1m	19	350000	2	Other
9867	HDMI Cable	20	350000	2	Other
9868	USB Cable	21	350000	2	Other
9869	Wire 5 core 1.5mm 1m	22	350000	2	Other
9870	Spiral Sleeve	23	350000	2	Other
9871	Cable Channeling	24	350000	2	Other
9872	Heat Shrinks	25	350000	2	Other
9902	Capacitor 400V DC	1	350000	2	Other
9905	DVI to HDMI adapter	4	350000	2	Other
9955	20cm Jumper Cable M-M	3	350000	2	Other
9906	Hollow SS rod	5	350000	2	Raw Material
9907	Aluminum Sheet 1x4x1.5mm	6	350000	2	Raw Material
9950	FIP-510001  Linear Master Compact Professional	1	200000	7	Other
9908	Aluminum Sheet 1x4x3.2mm	7	350000	2	Raw Material
9909	Power supply unit (Corsair RM750X750 Watt 80 Plus)	8	350000	7	Machinery / Equipment
9910	DJI GPS Module	9	350000	7	Parts
9911	100x300x4140 steel round bar	10	350000	2	Raw Material
9912	Lab Workstation	11	350000	7	Furniture
9913	Lab Chairs (Master Offisys Anatom low back chair with PU arm rest)	12	350000	7	Furniture
9914	Screws	13	350000	2	Other
9915	63A double pole breaker	14	350000	2	Other
9917	3 core 6mm wire	15	350000	2	Other
9873	Solder Wire	26	350000	2	Other
9874	Gasket	27	350000	2	Raw Material
9937	6-32 SS bolt with nut and washer	11	350000	2	Other
9938	4.6mm SS bolt with nut and washer	12	350000	2	Other
9939	SS bolt with nut and washer 3.5mm	13	350000	2	Other
9926	SS Hollow Tube 1in x 1in x 1.5mm	1	350000	2	Raw Material
9888	GI sheet for front and side wall	1	350000	2	Raw Material
9889	MS angle 25mm x 25mm x 3mm	2	350000	2	Raw Material
9890	EPOXY paint	3	350000	2	POL
9891	Paint thinner	4	350000	2	POL
9892	Storage Rack 4' x 2' x 8'	5	350000	7	Furniture
9893	Exhaust Fan 18''	6	350000	7	Appliance
9894	Ceiling Fan	7	350000	7	Appliance
9895	LED Tube light	8	350000	2	Other
9898	Power Cable 4 core 10mm	9	350000	2	Other
9927	SS Sheet for base (8ft x 4ft x 4mm)	2	350000	2	Raw Material
9929	Resilient Mounts for base	3	350000	2	Other
9930	SS Welding Rod	4	350000	2	Raw Material
9931	Argon, Oxygen, LPG	5	350000	2	Chemical
9932	Copper Stock	6	350000	2	Raw Material
9933	EDM Machining Charges	7	350000	3	Consultancy
9934	DB joint switch	8	350000	2	Other
9935	4ft x 4ft x 5mm MS sheet	9	350000	2	Raw Material
9936	1/4 in SS bolt with but and washer	10	350000	2	Other
9940	M3 screws	14	350000	2	Other
9958	Terminal Block 10pin	6	350000	2	Other
9959	Molex Female 8pin	7	350000	2	Other
9960	STM32 discovery board	8	350000	2	Other
9961	AM26LS32	9	350000	2	Other
9962	Breadboard	10	350000	2	Other
9963	Propellers	11	350000	7	Parts
9964	Clamp meter	12	350000	7	Test / Measuring Equipment
9965	Hex keys	13	350000	7	Tools
9966	Torx keys	14	350000	7	Tools
9967	Pliers	15	350000	7	Tools
9969	3D printing spool (PLA)	16	350000	2	Raw Material
9970	3D printing spool (ABS)	17	350000	2	Raw Material
9971	Storage boxes	18	350000	2	Other
9972	UBEC 10/20 A	19	350000	7	Parts
9973	Dual UBEC	20	350000	7	Parts
9974	Fasterners (M8x75mm)	21	350000	2	Other
9975	Elfy box	22	350000	2	Other
9976	Paper tape 1"	23	350000	2	Other
9977	Paper tape 2"	24	350000	2	Other
9978	Double tape	25	350000	2	Other
9979	Electric tape	26	350000	2	Other
9980	Transparent tape	27	350000	2	Other
9981	Safety Gloves	28	350000	2	Other
9982	Duct tape	29	350000	2	Other
9983	Magic epoxy	30	350000	2	Chemical
9984	Plastic zip bag	31	350000	2	Other
9985	Cutting disc	32	350000	2	Other
9986	Buffing disc	33	350000	2	Other
9988	Grease box	35	350000	2	Chemical
9989	Samad bond box	36	350000	2	Chemical
9920	Pilot Valve	6	450000	7	Parts
9951	IBT2 H-Bridge driver	1	350000	2	Other
9954	Brushless ESC 35A	2	350000	2	Other
9956	20cm Jumper Cable M-F	4	350000	2	Other
9957	20cm Jumper Cable F-F	5	350000	2	Other
9998	Fuel for testing	7	350000	2	POL
10071	Wintek HMI	2	450000	7	Parts
10000	512 GB M2 SATA SSD	8	350000	7	IT Equipment
10001	Buffing	9	350000	3	Equipment Modification
10002	Anodizing	10	350000	3	Equipment Modification
10009	Lugs (50 Sq mm)	17	350000	2	Other
10023	UHU	29	350000	2	Chemical
9990	Hantek Oscilloscope 04 Channel	2	200000	7	Other
10056	LNA PCB fabrication	1	350000	3	Fabrication
10064	M8 kit	9	350000	2	Other
9991	10 mm aluminum stock (850 mm x 550 mm)	1	350000	2	Raw Material
9992	MS Rod for Spacer	2	350000	2	Raw Material
9993	Aluminum Plates	3	350000	2	Raw Material
9994	Aluminum Supporting Rod	4	350000	2	Raw Material
9995	Aluminum Supporting Clamps	5	350000	2	Other
9997	Aluminum Rod 19x16	6	350000	2	Raw Material
10070	Pelican Hard Case (20.5" x 11.5")	1	450000	7	Parts
10072	LCD Screen for Camera Display	3	450000	7	IT Equipment
10073	Push Button	4	450000	7	Parts
10074	Emergency Switch	5	450000	7	Parts
10075	19 Pins connector	6	450000	7	Parts
10076	Mechanical Structure Foundation base assembly	1	300000	7	Fabrication Machinery
10077	Trolley for test platform	2	300000	7	Parts
10069	Thread machining (3.125mm)	13	350000	3	Equipment Modification
10078	Pelican Hard Case (20.5" x 11.5")	1	450000	7	Parts
10079	Wintek HMI	2	450000	7	Parts
10080	LCD Screen for Camera Display	3	450000	7	Parts
10081	Push Button	4	450000	2	Parts
10082	Emergency Switch	5	450000	2	Parts
10084	19 Pins connector	6	450000	2	Parts
10085	PLC FBS 40 MAR	7	450000	7	Parts
10086	Power supply	8	450000	7	Parts
10012	Hydraulic wire crimper	20	350000	7	Tools
10013	Wiring harnesses	21	350000	2	Other
10014	LTE/ Confidential closet	22	350000	7	Furniture
10015	A4 Paper Rim BLC	23	350000	2	Other
10016	Legal paper (Green minute sheets)	24	350000	2	Other
10019	Ball pen blue & black	25	350000	2	Other
10020	Sticky flag	26	350000	2	Other
10021	Envelope small, medium, large	27	350000	2	Other
10022	Stapler Pin	28	350000	2	Other
10024	Paper Tape 1 inch	30	350000	2	Other
10025	Paper Tape 2 inch	31	350000	2	Other
9878	Hydraulic Block	1	450000	7	Parts
9880	Hydraulic fittings & Connectors	2	450000	7	Parts
9881	Saftey Valve	3	450000	7	Parts
9882	Pressure Regulator	4	450000	7	Parts
9883	Soleniod Valve	5	450000	2	Parts
10087	Ethernet module FBS for PLC	9	450000	7	Parts
10088	Encoder E6B2-CWZ6C	10	450000	7	Parts
10003	Granite wire cutting	11	350000	3	Equipment Modification
10057	Copper stock	2	350000	2	Raw Material
10004	Powder coating	12	350000	3	Equipment Modification
10005	608 High speed ceramic bearings (OD 22, ID8, RPM 100000)	13	350000	2	Other
10006	SS 316 filler rod	14	350000	2	Raw Material
10007	EDM charges	15	350000	3	Equipment Modification
10058	Al Bronze stock (270mm x 60mm)	3	350000	2	Raw Material
10059	Al Bronze stock (280mm x 110mm)	4	350000	2	Raw Material
10060	Outsource Machining	5	350000	3	Equipment Modification
10061	Acetone	6	350000	2	Chemical
10062	Imported Silicon grease	7	350000	2	Chemical
10063	High temperature RTV sealant	8	350000	2	Chemical
10065	M5 kit	10	350000	2	Other
10067	M2.5 kit	11	350000	2	Other
10068	EDM charges	12	350000	3	Equipment Modification
10008	Wire (50 Sq mm) flexible	16	350000	2	Other
10010	3 Pole Connector 400 A	18	350000	7	Parts
10011	Cable Glands for 50 Sq mm wire	19	350000	2	Other
10026	Transparent tape 1 inch	32	350000	2	Other
10027	Transparent tape 2 inch	33	350000	2	Other
10028	GMSA (Elfy)	34	350000	2	Chemical
10029	Uni ball pen blue & black	35	350000	2	Other
10030	Gel pen black & blue	36	350000	2	Other
10089	Prime27 resin small pack	1	350000	2	Chemical
10090	Balsa wood 100*500	2	350000	2	Raw Material
10091	Huntsmen (DP 420)	3	350000	2	Chemical
10092	Al rivnuts 3,4,5 mm	4	350000	2	Other
10093	Avid tools	5	350000	2	Other
10094	Glass fiber 300 GSM roll (1x100) meter	6	350000	2	Raw Material
10095	Hartford lubrication oil	7	350000	2	POL
10096	Hartford non soluble oil	8	350000	2	POL
10097	Ply wood 8*4	9	350000	2	Raw Material
10098	Clear Epoxy & depoxi	10	350000	2	Chemical
10099	GMSA	11	350000	2	Chemical
10100	Mixing pots	12	350000	2	Other
10101	Rubbing compound	13	350000	2	Chemical
10102	Buffing compound	14	350000	2	Chemical
10103	Wax	15	350000	2	Chemical
10104	Micro fiber cloth	16	350000	2	Other
10105	Abrasive paper	17	350000	2	Other
10106	Gloves	18	350000	2	Other
10107	Sticks	19	350000	2	Other
10108	Spiral	20	350000	2	Other
10109	Syringe	21	350000	2	Other
10110	Pink foam 8*4	22	350000	2	Raw Material
10111	PU foam	23	350000	2	Chemical
10031	Register (300 page)	37	350000	2	Other
10032	Box file	38	350000	2	Other
10033	Sticky notes (Yellow)	39	350000	2	Other
10034	Round Marker (Blue)	40	350000	2	Other
10035	Rough Pad	41	350000	2	Other
10037	Cell 1.5 V AA	43	350000	2	Other
10038	Cell 1.5 V AAA	44	350000	2	Other
10039	Rose petal roll	45	350000	2	Other
10040	Rose petal box	46	350000	2	Other
10041	Air freshener	47	350000	2	Other
10042	Power plus fast killer	48	350000	2	Other
10043	Permenant marker black	49	350000	2	Other
10044	Power plus refill liquid	50	350000	2	Other
10045	AROMA air freshener kingtox	51	350000	2	Other
10046	File thread	52	350000	2	Other
10048	Binding tape	54	350000	2	Other
10049	Transparent file	55	350000	2	Other
10112	Thermocol 8*4	24	350000	2	Raw Material
10113	Face mask	25	350000	2	Other
10050	Door bell	56	350000	2	Other
10051	Pencil	57	350000	2	Other
10052	Temperature sensor	58	350000	7	Parts
10053	Battery	59	350000	2	Other
10054	Chair Base Plate	60	350000	2	Other
10055	Jazz internet charges of labs for 2 months	61	350000	3	Consultancy
10114	Cutting disc	26	350000	2	Other
10120	Vinyl sticker roll	32	350000	2	Raw Material
10121	Thinner	33	350000	2	Chemical
10124	Kerb Side Paver Blocks	1	300000	7	Other
10125	Transportation/ Labour/ Leveling and Installation Charges	2	300000	3	Equipment Installation
10126	Crush Cement for installation of paver blocks	3	300000	3	Equipment Installation
10127	Oil paint and labour for painting paver blocks	4	300000	3	Other
10128	Palm tree plants for plantation in outer area	5	300000	7	Other
10147	Keyboard	4	350000	7	Parts
10129	SMA panel mount (female)	1	350000	2	Other
10130	SMA panel mount (male)	2	350000	2	Other
10131	Antenna to bulkhead cable	3	350000	2	Other
10122	Primer	34	350000	2	Chemical
10123	Color pigment	35	350000	2	Chemical
10132	Bulkhead to veronte cable	4	350000	2	Other
10133	Veronte to amp cable	5	350000	2	Other
10134	Amp to bulkhead cable	6	350000	2	Other
10135	SSMA connectors	7	350000	2	Other
10157	14 Pin Relay Base	2	350000	2	Other
10158	Momentary Push Button Green 22mm	3	350000	2	Other
10136	Brake nuts and bolts	8	350000	2	Other
10137	Pressure vessel valve	9	350000	2	Other
10138	MS rod 50mm dia 800mm	10	350000	2	Raw Material
10139	Rubber boot for actuator	11	350000	2	Other
10140	Steel wire rope 16mm dia 30 meter length	12	350000	2	Other
10141	SMD fuses	13	350000	2	Other
10142	Lathe machining charges	14	350000	3	Equipment Modification
10143	UPS repairing	15	350000	3	Equipment Repairs & Maintenance
10144	EVD-20KL:\r\nElectric Vehicle Driver\r\nRated Operating Input Voltage: 200~400 VDC\r\nContinuous Output: 80A\r\nMax. Current Limit: 240A	1	350000	7	Parts
10145	EVD-30KL:\r\nElectric Vehicle Driver\r\nRated Operating Input Voltage: 200~400 VDC\r\nContinuous Output: 110A\r\nMax. Current Limit: 330A	2	350000	7	Parts
10146	EVD-45KL:\r\nElectric Vehicle Driver\r\nRated Operating Input Voltage: 200~400 VDC\r\nContinuous Output: 160A\r\nMax. Current Limit: 480A	3	350000	7	Parts
10115	Masking tape	27	350000	2	Other
9429	EinScan HX Red Incl. Solid Edge S3D version & Geomagic Essentials	1	350000	7	Machinery / Equipment
10116	Aluminum rivets 3.2mm	28	350000	2	Other
10117	Screw	29	350000	2	Other
10118	Hatch latch lock	30	350000	2	Other
10119	Plycate	31	350000	2	Other
10152	DC/DC Module 12 to + - 15volts	1	200000	2	Other
10151	2 Layer PCB	1	200000	2	Other
10160	Momentary Push Button Red 22mm	4	350000	2	Other
10161	2 Pole Selector (position 1 0 2)	5	350000	2	Other
10162	3 Pole Selector (position 1 0 2)	6	350000	2	Other
10148	Xilinx XC18V02 VQ44AEND QFP44	1	200000	2	Other
10149	TQFP44 Adopter	2	200000	2	Other
10163	ON/OFF Selector Switch 22mm	7	350000	2	Other
10150	CD54HCT245 ic	3	200000	2	Other
10156	Industrial Grade 14 Pin Omron Relay	1	350000	2	Other
10181	Matek Flight Controller	1	350000	7	Parts
10182	Matek GPS Module M8Q-5883	2	350000	7	Parts
10183	Matek Airspeed Sensor 4525	3	350000	2	Other
10184	Servos 25KG	4	350000	2	Other
10185	UBEC Avionics	5	350000	2	Other
10189	Engine Battery 3S 5000mAh	9	350000	2	Other
10190	Avionics Battery 3S 5000mAh	10	350000	2	Other
10191	Power Distribution Board	11	350000	2	Other
10199	S-band anodizing	1	350000	3	Equipment Modification
10212	Micro Controller based logic analyzer 24M 8CH with USB interface	1	160000	2	Other
10213	USB 2.0 to RS 485, RS 422 DB9 com serial port device converter adapter	2	160000	2	Other
10214	HDMI to type D converter	3	160000	2	Other
10219	Uniross original 1.5 v AA size cell for fluke multimter	4	160000	2	Other
10221	Nylon (Size 250 mm dia x 200 mm length)	1	350000	2	Raw Material
10222	Aluminum bronze casting material	2	350000	2	Raw Material
10224	Wood Sheets	3	350000	2	Raw Material
10225	Handle rod	4	350000	2	Other
10226	Spray Paint	5	350000	2	Chemical
10227	Rivets & corner fittings	6	350000	2	Other
10228	Dell Vestro 3510 Core i5 11th Gen 4GB 256 SSD 15.6" HD Black Laptop	7	350000	7	IT Equipment
10229	Internet charges at MF	8	350000	3	Other
10230	Power supply & 4 GB RAM	9	350000	7	IT Equipment
10231	Paint	10	350000	2	Chemical
10232	Intell Printer repair & cartridge refill	11	350000	3	Equipment Repairs & Maintenance
10200	Patch feeder anodizing	2	350000	3	Equipment Modification
10201	Aluminum rod 12 ft (19*15)	3	350000	2	Raw Material
10202	Aluminum rod anodizing	4	350000	3	Equipment Modification
10203	Aluminum rod bending	5	350000	3	Equipment Modification
10204	M3*8 SS scerws	6	350000	2	Other
10205	Tap set	7	350000	2	Other
10206	Rubber dampers	8	350000	2	Other
10207	FKM seal	9	350000	2	Other
10208	Nylon Telfon (2ft x 2 ft x 40 mm)	10	350000	2	Raw Material
10209	Seal (196 x 6.99 mm)	11	350000	2	Other
10164	Yellow LED 24Vdc 22mm	8	350000	2	Other
10165	Green LED 24Vdc 22mm	9	350000	2	Other
10166	Red LED 24Vdc 22mm	10	350000	2	Other
10167	Hour Meter	11	350000	2	Other
10168	Buck Converter	12	350000	2	Other
10169	Timer Circuit	13	350000	2	Other
10170	10 Pin Connector	14	350000	2	Other
10171	37 Mil Connector (Set)	15	350000	2	Other
10172	32 Mil Connector (Set)	16	350000	2	Other
10173	GX 16 2 Pin Connector	17	350000	2	Other
10174	Power Supply 24V 3A	18	350000	2	Other
10192	Kill Switch	12	350000	2	Other
10193	SkyRC GPS Speed Meter	13	350000	7	Test / Measuring Equipment
10267	TM4C123GH6PMI	1	200000	2	Other
10268	LM2596T-5.0/LF03 IC REG BUCK 5V 3A	2	200000	2	Other
10269	LM2596T-3.3/NOPB IC REG BUCK 3.3V 3A	3	200000	2	Other
10270	EEE-FT1V681UP CAP ALUM 680UF 20% 35V SMD	4	200000	2	Other
10271	EEE-FC1V221P CAP ALUM 220UF 20% 35V SMD	5	200000	2	Other
10272	MAX232ACSE+ IC RS-232 DRVR/RCVR 16-SOIC	6	200000	2	Other
10276	TM4C123G LaunchPad Evaluation Kit	7	200000	2	Other
10273	Super Pro Adapters DX-3003 XELTEK	1	200000	2	Other
10274	Super Pro Adapters DX-3011 XELTEK	2	200000	2	Other
10275	Super Pro Adapters DX-3009 XELTEK	3	200000	2	Other
10278	High power DC-DC converter Module (300v-24v)	1	300000	7	Parts
10246	Mouse	3	350000	7	IT Equipment
10248	Market Box	2	350000	2	Other
10249	Pink foam	3	350000	2	Other
10234	SMA Connectors	1	350000	2	Other
10235	Mini Circuits (Coaxial-Ceramic Resonator & Multiplexers)	2	350000	2	Other
10236	Market Box	3	350000	2	Other
10237	Pink Foam	4	350000	2	Other
10238	Aluminum Rivets Box	5	350000	2	Other
10239	Black Spray Paint	6	350000	2	Chemical
10240	Aluminum Bronze Casting Material	7	350000	2	Raw Material
10241	Tuning Screws	8	350000	2	Other
10250	Aluminum rivets box	4	350000	2	Other
10264	4DBEZEL-70-B Bezel for 7" Black	1	200000	2	Other
10265	uSD-4GB-Industrial Memory card sdhc 4GB UHS	2	200000	2	Other
10251	Black spray paint	5	350000	2	Chemical
10233	GSP test bench name plate	12	350000	2	Other
10194	RF design 900 MHz datalink	14	350000	7	Parts
10177	USB 2.0 to  RS 485, RS 422 DB9 com serial port device converter adapter	2	160000	2	Other
10289	APH 1409 77820 7795069 CTVS06RF-21-29P	7	350000	2	Other
10178	HDMI to type D converter	3	160000	2	Other
10198	MDF sheet 25mm	18	350000	2	Raw Material
10252	Aluminum bronze casting material	6	350000	2	Raw Material
10253	Tuning screws	7	350000	2	Other
10263	uLCD-70DT MOD LCD 7" DIABLO16	1	200000	2	Other
10277	4 Layer MET - CADCAM PCB	2	200000	2	Other
10244	Mini circuits (Coaxial Ceramic Resonator & Multiplexers)	1	350000	2	Other
10195	Flow Sensor	15	350000	2	Other
10254	Disposable gloves box	8	350000	2	Other
10255	Lab coat	9	350000	2	Other
10256	Sponge	10	350000	2	Other
10245	Ferric Chloride	2	350000	2	Chemical
10247	SMA connectors	1	350000	2	Other
10196	Radio Controller QX7	16	350000	7	Machinery / Equipment
10197	Al stock for metal parts 500*500	17	350000	2	Raw Material
10257	Iron scrubber	11	350000	2	Other
10258	MS stock (145x145x80)	12	350000	2	Raw Material
10259	High grade bolts nut and washer	13	350000	2	Other
10279	High power DC-DC converter Module (300v-260v)	2	300000	7	Parts
10280	DC power supply capacitor bank and stable load	3	300000	7	Parts
10281	Tether DC power cable for ROV test setup (25m)	4	300000	7	Parts
10339	15135-0806  Rectangular Cable Assemblies CLIK-MATE 8CKT CBL ASSY SR 600MM BEIGE	1	200000	2	Other
10341	5031590800 CONN RCPT 8POS 0.059 TIN PCB	2	200000	2	Other
10342	NS10155T220MNA  FIXED IND 22UH 3.12A 45.6MOHM SM	3	200000	2	Other
10343	CLF10060NIT-101M-D  Power Inductors - SMD 100uH +/-20% AECQ200 -55 to +150C	4	200000	2	Other
10344	1N5822RL  DIODE SCHOTTKY 40V 3A DO201AD	5	200000	2	Other
10345	LM2596T-3.3/NOPB  IC REG BUCK 3.3V 3A TO220-5	6	200000	2	Other
10346	LM2596T-5.0/LF03  Switching Voltage Regulators SIMPLE SWITCHER & reg; 4.5V to 40V, 3A Low Component Count Step-Down Regulator 5-TO-220	7	200000	2	Other
10432	Gas refill (Oxygen, Argon and LPG)	8	350000	2	POL
10325	Stock Size (400mm x 80mm)	1	350000	2	Raw Material
10327	12.9 Grade M16 bolts	3	350000	2	Other
10347	LFXTAL003240Bulk  CRYSTAL 16.0000MHZ 30PF TH	8	200000	2	Other
10328	Rubber sheet	4	350000	2	Raw Material
10329	MS Rod (40 mm dia)	5	350000	2	Raw Material
10348	GRM31CR60J227ME11L  Multilayer Ceramic Capacitors MLCC - SMD/SMT 220UF 6.3V 20% 1206	9	200000	2	Other
10349	12063C104JAT2A  Multilayer Ceramic Capacitors MLCC - SMD/SMT 25V 0.1uF X7R 1206 5%	10	200000	2	Other
10330	STM32 Bluepill	6	350000	2	Other
10331	Arduino uno	7	350000	2	Other
10332	MPU 9250	8	350000	2	Other
10333	USB cable extension	9	350000	2	Other
10334	Push button	10	350000	2	Other
10335	Micro USB cable	11	350000	2	Other
10336	Aluminum bronze rod	12	350000	2	Raw Material
10337	Base Machining	13	350000	3	Equipment Modification
10282	15-30 SOURIAU MPS 8D5/ 17M20BN	1	350000	2	Other
10283	15/19 SOURIAU MPS D38999/ 26MJ35AN	2	350000	2	Other
10368	DC isolator	1	350000	7	Parts
10353	Integrated Circuits	1	200000	2	Other
10386	Gel coat	9	350000	2	Chemical
10389	MDF bonding adhesive	10	350000	2	Chemical
10377	Quarter lock fittings 2mm, 3mm, 5mm	1	350000	2	Other
10350	EEEFT1V681UP Panasonic 680?F Electrolytic Capacitor 35V dc, Surface Mount - EEEFT1V681UP	11	200000	2	Other
10584	Fabrication of payload plate	3	300000	3	Fabrication
10351	EEE-FC1V221P Aluminum Electrolytic Capacitors - SMD 220uF 35V	12	200000	2	Other
10378	Sealant Tape	2	350000	2	Other
10379	Vacuum Bagging	3	350000	3	Equipment Modification
10354	IC Base round	2	200000	2	Other
10355	Potentiometer	3	200000	2	Other
10356	Resistors	4	200000	2	Other
10369	Primary switch	2	350000	2	Other
10370	5 pin relay 24V	3	350000	2	Other
10371	Cooling Fan	4	350000	2	Other
10357	Capacitors	5	200000	2	Other
10358	Diodes ZENER 12V	6	200000	2	Other
10359	Terminal Block	7	200000	2	Other
10360	Resistors wire wound	8	200000	2	Other
10361	Three wire connector	9	200000	2	Other
10372	Emergency stop button	5	350000	2	Other
10380	Glass fiber 300 GSM roll (1x100) meter	4	350000	2	Raw Material
10362	IDE 20pin connector	10	200000	2	Other
10363	Power Cable roll	11	200000	2	Other
10364	Multi Power Sockets boards	12	200000	2	Other
10365	DATA cable 6 core 25m	13	200000	2	Other
10373	Cables wires and connectors	6	350000	2	Other
10366	VGA Cables	14	200000	2	Other
10381	Matt fiber	5	350000	2	Other
10382	Landing gear prototype	6	350000	7	Parts
10383	Infusion line	7	350000	2	Other
10384	Polyester resin & hardener	8	350000	2	Chemical
10390	Pattern sealant	11	350000	2	Chemical
10391	Rubber Damper	12	350000	2	Other
10392	PVC wiring channel/ duct 40x40mm	13	350000	2	Other
10393	Delta HMI	14	350000	7	Parts
10394	Proximity sensor/ light gate	15	350000	2	Other
10367	Power Cables	15	200000	2	Other
10395	Analog temperature sensor PT-100	16	350000	2	Other
10396	Limit switch	17	350000	2	Other
10397	Panel Mount lights 24V 22mm	18	350000	2	Other
10398	Lugs and connectors (u style+pin)	19	350000	2	Other
10399	Emergency switch	20	350000	2	Other
10400	Ethernet connectors	21	350000	2	Other
10401	Jazz internet at NDT & Intell Lab	22	350000	3	Consultancy
10428	Acrylic sheet full size	4	350000	2	Raw Material
10385	Replacement of door threshold, Replacement of door, door bidding, color of threshold, door and bidding, transporation and labor charges	1	450000	7	Other
10429	Srandoff MS hexagonal rod 20 ft	5	350000	2	Raw Material
10403	DLE-170M	1	350000	7	Parts
10404	DLE 170 spare ignition module	2	350000	2	Other
10405	Spark plugs	3	350000	2	Other
10406	DLE 170 fuel filter	4	350000	2	Other
10422	Echosounder (300m Depth Range)	3	300000	7	Parts
10423	Locator (ROV UW Locator Transducer)	4	300000	7	Parts
10424	Side Scan Sonar Module	5	300000	7	Parts
10402	AC repairing & gas charges at RF lab & SPF	23	350000	3	Equipment Repairs & Maintenance
9603	Civil Works	1	350000	3	Building Construction
9604	Steel Works	2	350000	3	Building Construction
9605	Electrical Work	3	350000	3	Building Construction
10407	Thrusters (20V 650W DC Power Thrusters)	1	300000	7	Machinery / Equipment
10409	Tether Wire (500m Neutrally Buoyant Tether Wire)	2	300000	2	Other
10410	Spool (Tether Dispenser Manual Spool)	3	300000	7	Parts
10411	Pressure Sensor (300m Depth Pressure Sensor)	4	300000	7	Parts
10412	Control Board (Control Integration Board)	5	300000	7	Parts
10413	Underwater Lights	6	300000	7	Parts
10414	4x8 ft 5mm MS sheet	1	350000	2	Raw Material
10415	Powder coating	2	350000	3	Equipment Modification
10416	Outer Gate (Metal Pipe)	1	300000	7	Other
10417	Outer Wall Metal Pipe Fence	2	300000	7	Other
10418	Exit Door Security Grill	3	300000	7	Other
5322	Manufacturing and fabrication of holders (SS) for assembiling pressure hulls and covering entire structure for lab version of prototype ROV	1	300000	7	Other
5324	Machined parts to be fabricated from (material 2083) as per structural design in order to assemble all sub-assemblies for lab version of prototype ROV	2	300000	7	Other
8259	Sand for cement mixture	3	160000	3	Other
5325	Manufacturing of structure for holding thrusters as per operational requirements for lab version of prototype ROV	3	300000	7	Machinery / Equipment
8626	Excavation	1	300000	3	Building Construction
8627	PCC 1:4:8	2	300000	3	Building Construction
8628	RCC Footing Plenth Beam + Column 1:2:4	3	300000	3	Building Construction
8630	RCC Roof Slab 1:2:4	4	300000	3	Building Construction
8631	Steel	5	300000	3	Building Construction
8632	Block Masonry	6	300000	3	Building Construction
10304	Tier 1 Solar Panel, 500 Watt (Minimum)	1	450000	7	Parts
10305	Solar Inveter 2.5 Kw	2	450000	7	Parts
10315	Batteries Lead Acid - 250 AH	3	450000	2	Parts
10317	MS Structure L2 setting (with red oxide coating)	4	450000	7	Parts
10321	Wiring and accessories as required (DC (4mm), AC (6mm 2 core), 35 mm cable for batteries)	5	450000	2	Parts
10322	Distribution Box (MS) with Earth strip (02 x 2 Pole AC Breaker, 01x DC Breakers and 01 x surge protection device)	6	450000	7	Parts
10323	Earthing (Chemical earth, Rod Type 20mm, Resistance < 5 ohm)	7	450000	7	Parts
9886	SDI-AV encoder (air)	1	350000	7	Parts
9887	HDMI decoder	2	350000	7	Parts
10426	Bending Charges	2	350000	3	Equipment Modification
10427	Powder coating	3	350000	3	Equipment Modification
10434	Welding rods	10	350000	2	Raw Material
10435	Drill bits (9-12 mm)	11	350000	2	Other
10436	Nut and bolts	12	350000	2	Other
10437	Machine screw	13	350000	2	Other
10438	Cut screws	14	350000	2	Other
10439	Cotton rag	15	350000	2	Other
10440	Aluminum hinges	16	350000	2	Other
10419	4x8 ft 5mm MS sheet	1	350000	2	Raw Material
10425	MS sheet (8 ft x 4 ft x 1.5 mm)	1	350000	2	Raw Material
10569	Hydraulic gear pump	2	450000	7	Parts
10516	Apex Seal Wedge (0.7kg)	2	350000	2	Other
10518	Apex Seal	3	350000	2	Other
10556	Internet charges at MF	40	350000	3	Other
10682	MAX31865 Converter	22	350000	2	Other
10499	Spare Propeller	2	350000	7	Parts
10501	6S 500mah GNES ACE lipo battery	3	350000	2	Other
10502	Servo 25kg standard	4	350000	7	Parts
10503	battery capacity tester	5	350000	7	Test / Measuring Equipment
10504	Digital servo tester	6	350000	7	Test / Measuring Equipment
10505	2S 5000mah	7	350000	2	Other
10498	Motor ESC prop Combo	1	350000	7	Parts
10506	Spinner 114mm	8	350000	7	Parts
10507	Spinner 127mm	9	350000	7	Parts
10508	Fuel flow sensor	10	350000	2	Other
10509	Propellers 30x12 & 32x10	11	350000	7	Parts
10676	MS stock	17	350000	2	Raw Material
10677	SS Nut and bolts	18	350000	2	Other
10678	Atmega 328P-PU DIP IC	19	350000	2	Other
10680	Ethernet Shield ENC28J60	20	350000	2	Other
10681	3 Pin PT-100	21	350000	2	Other
10567	Electric Motor 1.5 Kw 220v	1	450000	7	Parts
10571	Oil tank Upto 40 Liters Capacity	3	450000	7	Parts
10573	Suction Filter	5	450000	2	Parts
10574	Air breathe tank cap	6	450000	2	Parts
10581	Oil cooler	13	450000	7	Parts
10515	Corner Seal (0.7kg)	1	350000	2	Other
10519	Face Seal	4	350000	2	Other
10520	Face Seal Spring Punch (0.8kg)	5	350000	2	Other
10582	Fabrication of skeleton hull structure	1	300000	3	Fabrication
10521	Face Seal Spring Die (0.8kg)	6	350000	2	Other
10522	Apex Seal Spring	7	350000	2	Other
10523	Wire cutting	8	350000	3	Other
10524	Seal	9	350000	2	Other
10525	MCP2515 Can Bus Module	10	350000	2	Other
10526	Carbon Steel (180x180x15)	11	350000	2	Raw Material
10527	Carbon Steel (60x60x15)	12	350000	2	Raw Material
10585	Construction of SVDC outer garden boundary wall (height 7ft) including supporting column, plinth beam and wall foundation	1	300000	3	Building Construction
10583	Fabrication of base plate structure	2	300000	3	Fabrication
8260	Mason & labor charges	4	160000	3	Other
10528	Carbon Steel (250x100x15)	13	350000	2	Raw Material
10529	Carbon Steel (180x180x15)	14	350000	2	Raw Material
10430	Machine tap set	6	350000	2	Other
10431	CNC tooling	7	350000	2	Other
10433	WD40 spray pack	9	350000	2	Chemical
10653	Operator Console Main Structure Frame	1	300000	7	Parts
10654	User Interface LCD Screen	2	300000	7	IT Equipment
10626	Fabrication of PCB	1	200000	3	Fabrication
10630	Spliter 8 port	1	200000	2	Other
10631	VGA Cable 10m	2	200000	2	Other
10616	151350306 Rectangular cable assemblies CLICKMATE 3CKT  CBL ASSY SR 600MM	1	200000	2	Other
10624	GEN4-ULCD-70DT MOD LCD7" 800X420 4D System	1	200000	2	Other
10575	Tank oil level indicator	7	450000	7	Parts
10617	5031590300 Headers & Wire Housings 1.50W/BSGLDISPST REC 3CKTW/BosWknk BEIGE	2	200000	2	Other
10576	In line relief valve	8	450000	7	Parts
10577	Almonium block for solenoid assembly	9	450000	7	Parts
10578	Solenoid vale 220 (All ports closed double coil)	10	450000	7	Parts
10619	151350406 Rectangular cable assemblies CLICKMATE 4CKT  CBL ASSY SR 600MM BEIGE	3	200000	2	Other
10620	5031590400 Headers & Wire Housings 1.50MM clickmate REC 04P VT TH	4	200000	2	Other
10652	EK-TM4C123GXL LaunchPad TM4C123G Eval BRD	1	200000	2	Other
10579	Solenoid valve 220 v single coil	11	450000	7	Parts
10580	Return line filter	12	450000	2	Parts
10627	GRM31CR60J227ME11L Multilayer Ceramic Capacitors MLCC-SMD/SMT 220uf 6.3V 20% 1206	2	200000	2	Other
10632	DVI to VGA adapter cable 1080P	3	200000	2	Other
10633	Data Cable	4	200000	2	Other
10655	User Interface Switches	3	300000	7	Parts
10628	GCG31MR71E225JA12L Multilayer Ceramic Capacitors MLCC-SMD/SMT 2.2uf 25V 1206	3	200000	2	Other
10629	TA91A105K035AT CAP TANT 1uf 35V 10% 1206	4	200000	2	Other
10634	Breaker 60AMP with fitting box	5	200000	2	Other
10635	Three Core power cable cupper shielded	6	200000	2	Other
10606	Replacement of energy savers in R&D Wing alleyways	2	160000	7	Appliance
10595	Provision of cabinet with locks for storage of classified files/ documents	1	160000	7	Furniture
10612	Power cable (04 core, 10mm)	2	160000	2	Parts
10600	Installation of power board in R&D Wing porta cabin to supply power to water dispenser	3	160000	7	Appliance
10613	Power distribution box	3	160000	7	Parts
10656	Provision of circuit breaker 32 A	3	160000	7	Parts
10658	Provision of circuit breaker 40A	4	160000	7	Parts
10566	Freight and Handling	9	350000	3	Other
10672	Bending charges	13	350000	3	Other
10673	Pressure Gauge with fittings	14	350000	7	Parts
10674	MS Rod 120 mm x 120 mm	15	350000	2	Raw Material
10675	Aluminum stock	16	350000	2	Raw Material
10701	16MHz Crystal oscillator	41	350000	2	Other
10702	HeaderJumper wires (Male to Male)	42	350000	2	Other
10703	HeaderJumper wires (Male to Female)	43	350000	2	Other
10704	6 core cable (15 feet)	44	350000	2	Other
10705	Atmega 328P IC Base	45	350000	2	Other
10706	Terminal Block ( 2 pin)	46	350000	2	Other
10707	Terminal Block ( 4 pin)	47	350000	2	Other
10708	Multiplexer IC (74HC4051)	48	350000	2	Other
10659	Aluminum (500x220x20) 6.01 kg	1	350000	2	Raw Material
10709	Storage Boxes	49	350000	7	Other
10660	Aluminum (500x210x60) 17.2 kg	2	350000	2	Raw Material
10661	Aluminum (135x90x90) 3 kg	3	350000	2	Raw Material
10662	Aluminum (150x110x25) 0.7 kg	4	350000	2	Raw Material
10663	Copper (100x50x30) 1.35 kg	5	350000	2	Raw Material
10664	Fasteners	6	350000	2	Other
10665	HDMI 2 mtr wire	7	350000	2	Other
10667	Mosfet	8	350000	2	Other
10668	Resistor 560 Ohm	9	350000	2	Other
10669	Resistor 39 Ohm	10	350000	2	Other
10670	USB	11	350000	7	IT Equipment
10671	MS sheet 05 mm	12	350000	2	Raw Material
10710	Machining and Fabrication of Electronics Enclosure	1	300000	3	Fabrication
10530	Carbon Steel O2 (90x60x15)	15	350000	2	Raw Material
10531	Carbon Steel O2 (90x60x15)	16	350000	2	Raw Material
10532	Carbon Steel O2 (60x60x15)	17	350000	2	Raw Material
10533	Steel D2-D4 (175x175x12)	18	350000	2	Raw Material
10534	Plastic tables	19	350000	7	Furniture
10636	Accessories	7	200000	2	Other
10713	Fabrication of Operator Console top Screen mount and frame	1	300000	3	Fabrication
10711	Fabrication of Battery Pack Enclosure	2	300000	3	Fabrication
10712	Fabrication of mounting for payload	3	300000	3	Fabrication
10715	Fabrication of frame for processing unit	2	300000	3	Fabrication
11102	Wheels	38	350000	7	Parts
10716	User Interface LCD Screen	3	300000	7	IT Equipment
10536	46m 3 phase wire	21	350000	2	Other
10537	Breakers	22	350000	2	Other
10538	Plastic Channels	23	350000	2	Other
10539	Hydraulic Jack	24	350000	7	Tools
10540	Drill Machine Chuck	25	350000	7	Parts
10541	Bolts for rails	26	350000	2	Other
10542	O rings	27	350000	2	Other
10543	MS Raw Material	28	350000	2	Raw Material
10544	Ni Coating	29	350000	3	Equipment Modification
10545	Grease seal	30	350000	2	Other
10546	Spark Plugs	31	350000	2	Other
10547	Telescopic Gauge Measuring Inspection tool	32	350000	7	Test / Measuring Equipment
10548	Digital depth gauge	33	350000	7	Test / Measuring Equipment
10550	Internal Digital Gauge	34	350000	7	Test / Measuring Equipment
10551	Screw Gauge digital (0-25mm)	35	350000	7	Test / Measuring Equipment
10552	Screw Gauge digital (25-50mm)	36	350000	7	Test / Measuring Equipment
10683	HX-711 Converter	23	350000	2	Other
10684	20X4 LCD (Blue Display)	24	350000	2	Other
10685	LCD 12C	25	350000	2	Other
10686	Accelerometer MPU 6050	26	350000	2	Other
10553	Screw Gauge digital internal (50-75mm)	37	350000	7	Test / Measuring Equipment
10554	Digital vernier cliper (200mm)	38	350000	7	Test / Measuring Equipment
10555	Jazz Internet charges at NDT lab	39	350000	3	Other
9941	01 x Supply PVC sliding window with grey glass & installation \r\n(Width 5.6 x Hight 5.0)	1	300000	7	Furniture
9944	05 x Supply PVC ventilator with frosted glass & installation\r\n(Width 1.6 x Hight 2.0)	4	300000	7	Furniture
9945	Supply exhaust fan	5	300000	7	Appliance
9946	Supply door closers	6	300000	7	Parts
10717	Operator Console Interface Switches	4	300000	7	Parts
10687	Arduino Uno	27	350000	2	Other
10688	Power Supply 12V 3A	28	350000	2	Other
10689	Power Supply 5V 3A	29	350000	2	Other
10690	Buck converter LM2596	30	350000	2	Other
10724	False ceiling	1	200000	3	Building Construction
10725	Tiles Fixing	2	200000	3	Building Construction
10726	Glass Partition	3	200000	3	Building Construction
10727	Electrical Work	4	200000	3	Building Construction
10728	White Wash	5	200000	3	Building Construction
10729	Grill Work	6	200000	3	Building Construction
10730	Wooden Door change new	7	200000	3	Building Construction
10731	Wooden Partition	8	200000	3	Building Construction
10691	GX-16 connector (3Pin)	31	350000	2	Other
9942	02 x Supply PVC openable door with grey glass & installation\r\n(Width 4.6 x Hight 7.6)	2	300000	7	Furniture
9943	04 x Supply PVC sliding window with reflective blue glass & installation\r\n(Width 3.0 x Hight 5.0)	3	300000	7	Furniture
10795	Standoff MS hexagonal rod 20ft	2	350000	2	Other
10796	Gas refill (Oxygen, Argon and LPG)	3	350000	2	POL
10797	Aluminum sheet (8ft*4ft*2mm)	4	350000	2	Raw Material
10800	Aluminum Hinges	5	350000	2	Other
10801	Bending Charges	6	350000	3	Equipment Modification
10802	Jazz Internet charges at NDT & RF lab	7	350000	3	Other
10831	3A Diodes	25	350000	2	Other
10764	Removal of accessories and piping (35%)	2	200000	3	Other
10692	GX-16 connector (4 Pin)	32	350000	2	Other
10693	GX-16 connector (6 Pin)	33	350000	2	Other
10694	PCB Copper Board (6X6inch)	34	350000	2	Other
10695	JST wired connectors (3 pin)	35	350000	2	Other
10696	JST wired connectors (4 pin)	36	350000	2	Other
10697	Header Strips Male & Female	37	350000	2	Other
10698	Ethernet connectors	38	350000	2	Other
10699	Switch button	39	350000	2	Other
10700	22pF Capacitor	40	350000	2	Other
10732	Unit (A)\r\nCore 2C Conductor Tinned Copper 8 AWG\r\nConstruction (N/mm)\r\n168/0.54+0.008\r\nInsulation XLPE\r\nAverage thickness (mm)\r\nMinimum Thickness (mm) 0.40\r\nInsulation diameter (mm) 5.50+0.15\r\nUnit (B) SMF\r\nMaterial Single mode fiber\r\nFiber count 2C\r\nInsulation\r\nOverall diameter (mm) 3.00+0.1\r\nAssemble Filler\r\nFiber+Kevlar\r\nDrain Wire Tinned Copper\r\nConstruction (N/mm) 9/0.254+0.008\r\nUnit C\r\nCoaxial Cable Single Pair\r\n26 AWG/1P+AL\r\nB) Warehouse/ Cargo Cost:\r\n0.5m3 and 300 Kg shipping\r\nFrom China port to Karachi port\r\nC) Custom clearance china port and Pakistan Port:\r\nAs per requirement	1	300000	7	Parts
10760	CPU/ IT hardware setup for operator GUI Console\r\n•\tIntel Core i7 (25 MB Cache, 2.10 Ghz)\r\n•\t8 GB RAM\r\n•\tUHD Graphics\r\n•\tWindows Operating system\r\n•\tLAN, USB ports\r\nInternal wiring/ connectivity ports with installation connectors etc	1	300000	7	IT Equipment
10733	MS sheet (4ft x 4ft x 12mm)	1	350000	2	Raw Material
10735	Chrome Coating	2	350000	2	Other
10751	Pressure reducing valve (Make: MONNEY USA)	1	200000	7	Equipment Installation
10736	Aluminum Stock for Sensor Mounting	3	350000	2	Other
10780	Tiling work on broken ground area including provision of material and masionary work charges	6	160000	7	Other
10777	Replacement of water taps on sinks	4	160000	7	Tools
10781	Provision of electric cattle for tea bar pantry	1	160000	7	Appliance
10783	Microwave oven	3	160000	7	Appliance
10784	Flat dish	4	160000	7	Other
10785	Electric hand mixer	5	160000	7	Appliance
10786	Electric induction heater/ stove	6	160000	7	Appliance
10787	Wall fan	7	160000	7	Appliance
10788	Tray set (set of 03 x serving trays)	8	160000	7	Other
10789	Water glass set (set of 06 x glasses)	9	160000	7	Other
10790	Water Jug	10	160000	7	Other
10791	Set of 06 x tea cups with saucer	11	160000	7	Other
10792	Sugar pot	12	160000	7	Other
10793	Fabrication and installation of pantry cabinets for storage of crockert	13	160000	7	Furniture
10772	Color on roof and walls of conference room including provision of material, labor charges and transportation	1	160000	3	Building Modification
10773	Installatino of blinds in R&D Wing conference room. Cost includes provision of material, installation charges and transportation	1	160000	7	Other
10766	Construction, plaster and color of bricked wall to replace temporary wooden wall in R&D Wing conference room	1	160000	7	Other
10767	Provision of 07 x seater sofa set with 02 x side tables	2	160000	7	Furniture
11103	Locknut	39	350000	2	Other
10761	Fabrication and Installation of canopy structure which includes 08 x supporting frames, fiber glass canopy (20' x 20'), base frame structure	1	160000	7	Other
10752	Smart Pressure Transmitter Capsule type ,(Make:  WIKA Germany)	2	200000	7	Equipment Installation
10621	151350806 Rectangular cable assemblies CLICKMATE 8CKT CBL ASSY SR 600MM BEIGE	5	200000	2	Other
10622	5031590800 8 Position Receptancle Connector 0.059" Throuh Hole Tin	6	200000	2	Other
10758	CNC Model Designing and Fabrication of Main Buoyancy Chamber for ROV Skeleton hull	1	300000	7	Parts
10759	CNC Machining/ Fabrication of auxiliary buoyancy chamber for ROV skeleton hull	2	300000	7	Parts
10806	DWIN Debug Kit	2	350000	2	Other
10737	Fuel Tank	4	350000	7	Parts
10794	Acrylic sheet full size	1	350000	2	Other
10808	DWIN DB9 Connector	3	350000	2	Other
10809	RS232 to TTL Converter	4	350000	2	Other
10738	Pipe Fittings & Valves	5	350000	2	Other
10811	Raspberry Pi 4 8GB	5	350000	2	Other
10824	Raspberry Pi 4 Power Adapter	18	350000	2	Other
10825	10 Socket Power Board	19	350000	2	Other
10826	10m Outdoor Extension Cable	20	350000	2	Other
10833	IBT2/BTS7960b	26	350000	2	Other
10834	L298 module	27	350000	2	Other
10835	XL4016 module	28	350000	2	Other
10836	Digital Voltmeter/Ammeter Display	29	350000	2	Other
10837	Futaba JR Female	30	350000	2	Other
10838	Futaba JR Male	31	350000	2	Other
10839	Aligator Clips	32	350000	2	Other
10840	Banana Plugs Male & Female	33	350000	2	Other
10841	Paper Cutter & Blades	34	350000	2	Other
10842	Fine Cutter	35	350000	2	Other
10765	Removal of burner (20%)	3	200000	3	Other
10763	Mobilization (45%)	1	200000	3	Consultancy
10804	DWIN 10.1" Industrial Screen	1	350000	7	IT Equipment
10812	16GB Sandisk Micro SD Card	6	350000	7	Parts
10813	SD Card Reader	7	350000	7	Parts
10814	HDMI Cable	8	350000	7	IT Equipment
10815	SMPS 12V 20A	9	350000	7	IT Equipment
10816	SMPS 5V 40A	10	350000	7	IT Equipment
10817	Ethernet Male to Male Connector	11	350000	2	Other
10818	IC Base/ Caddy	12	350000	2	Other
10819	Jumper cables 200 pair pack	13	350000	2	Other
10820	Serial to UART Reader	14	350000	2	Other
10821	ESD Band	15	350000	2	Other
10822	Raspberry Pi Official 7-Inch Capacitive Touch LCD Screen	16	350000	7	IT Equipment
10823	Casing For Raspberry Pi 7 Inch Display Touch Screen	17	350000	2	Other
10827	20 AWG Cable 5m Pair	21	350000	2	Other
10828	Assorted Heat Shrink Tubing	22	350000	2	Other
10829	Plastic wrap	23	350000	2	Other
10830	Assorted Tapes	24	350000	2	Other
10843	Tweezer	36	350000	2	Other
10844	Small Screw Driver Kit	37	350000	2	Other
10845	Arduino Female Header	38	350000	2	Other
10847	Wired Mouse	40	350000	7	IT Equipment
10848	RS422 to USB Converter	41	350000	2	Other
10849	Lens cleaner	42	350000	2	Other
10850	Micro fiber cloth thin	43	350000	2	Other
10851	Micro fiber cloth thick	44	350000	2	Other
10852	ESD Gloves pair	45	350000	2	Other
10853	Amplifier IC LM3900N	46	350000	2	Other
10854	Amplifier IC LM2902N/LM2900N	47	350000	2	Other
10855	AM26LS32ACN (Differential Line Receivers)	48	350000	2	Other
10859	IRF740 (N Channel MOSFET)	52	350000	2	Other
10860	IRF9540 (P channel MOSFET)	53	350000	2	Other
10861	IRF9530 (P channel MOSFET)	54	350000	2	Other
10775	Replacement of door threshold. Cost includes masion and carpentar charges, provision of complete material and transportation charges	2	160000	7	Other
10776	Replacement of old broken window glass. Cost includes provision of new glass, fixing chanrges and transportation	3	160000	2	Raw Material
10768	Provision of conference room table	1	160000	7	Furniture
10782	Provision of refrigerator (12 cu ft)	2	160000	7	Appliance
10769	Provision of conference room chairs	2	160000	7	Furniture
10771	Provision of conference room head chairs	3	160000	7	Furniture
10864	Resistor Pack	57	350000	2	Other
10897	Vertical Tail alignement jig (250*250x38)	2	350000	2	Other
10898	Canard alignement jig (280*160x38)	3	350000	2	Other
10899	Pink Foam Parting jig (1250x170x4)	4	350000	2	Other
10900	Pink Foam Parting jig (1219x1219x66)	5	350000	2	Other
10901	Slant spar Parting Jig (550*50*6)	6	350000	2	Other
10902	Nose to canard alignemnt jig (700*400*66)	7	350000	2	Other
10903	Canard control mechanism	8	350000	2	Other
10904	Main landing Gear Al 7075 (10001'50)	9	350000	2	Other
10905	Main landing Bending	10	350000	2	Other
10906	Nose landing gear	11	350000	2	Other
10907	Landing Wheels	12	350000	2	Other
10908	Fastners	13	350000	2	Other
10909	German Glue	14	350000	2	Chemical
10910	Control Rod and hinges	15	350000	2	Other
10911	Control Horns	16	350000	2	Other
10912	DLE Kill switch	17	350000	2	Other
10913	Loctite 243	18	350000	2	Chemical
10914	Nozzle of engine	19	350000	2	Other
10915	Whiteside CNC Spoilboard Cutter - 2"	20	350000	2	Other
10916	Whiteside Straight Cut Two Flute CNC Router Bit - 1/2" x 2"	21	350000	2	Other
10917	Grease Gun Needle Tip Adapter	22	350000	2	Other
10918	Multi-Purpose Lithium Grease, 3 oz Cartridge	23	350000	2	Other
10920	RAPTOR Composite Nails (14ga, 2.25")	24	350000	2	Other
10921	RAPTOR Composite Nails (15ga, 2" )	25	350000	2	Other
10922	PRO CNC Machine Spare Parts Bundle (NEMA 34)	26	350000	7	Parts
10924	Intel Core i7-1225U HP ProBook 450 G9	27	350000	7	IT Equipment
10753	Pressure Safety Valve with accessories (Make: Taiwan)	3	200000	7	Equipment Installation
10936	Mid Section Rod	5	350000	2	Other
10927	D38999/26WG35SN-US Back shell of 8D521W35SN	1	200000	2	Other
10929	D38999/26WB98SN-US Back Shell of 8D511W98SN	2	200000	2	Other
10930	Control Cable 79 Core 25m	3	200000	2	Other
10885	Breaker 80 A	78	350000	2	Other
11104	Neodium Magnets	40	350000	2	Other
10886	single phase 3 core 6mm wire	79	350000	2	Other
10888	5 core 6 mm wire	80	350000	2	Other
10931	Serial Convertor	4	200000	2	Other
10846	Wired Keyboard	39	350000	7	IT Equipment
10856	MAX490 (RS422 Transceiver)	49	350000	2	Other
10857	SG5325A (PWM Controller)	50	350000	2	Other
10858	IRFZ44N (N Channel MOSFET)	51	350000	2	Other
10862	NTST20100CTG (Schottky diode)	55	350000	2	Other
10863	6N137 (opto coupler)	56	350000	2	Other
10865	1nF Capacitor	58	350000	2	Other
10866	10nF Capacitor	59	350000	2	Other
10867	100nF Capacitor	60	350000	2	Other
10868	10uF Capacitor	61	350000	2	Other
10869	100uF Capacitor	62	350000	2	Other
10870	1000uF Capacitor	63	350000	2	Other
10871	2200uF Capacitor	64	350000	2	Other
10872	100pF capacitor (EMI)	65	350000	2	Other
10873	Push Button (SPST)	66	350000	2	Other
10874	Push Button (SPDT)	67	350000	2	Other
10875	Assorted Headers	68	350000	2	Other
10876	Jumpers and shorting headers	69	350000	2	Other
10877	Vero Board	70	350000	2	Other
10878	PCB Sheet	71	350000	2	Other
10879	Soldering Iron	72	350000	7	Machinery / Equipment
10880	Soldering wire	73	350000	2	Other
10881	Soldering Paste	74	350000	2	Other
10882	High precision weighing machine	75	350000	7	Test / Measuring Equipment
10883	Sintering material storage jar	76	350000	2	Other
10884	5 Core wire	77	350000	2	Other
10889	breaker box	81	350000	2	Other
10890	1.25 inch Pipe	82	350000	2	Other
10891	Bend 1.25 inch	83	350000	2	Other
10892	flexible pipe 1 inch	84	350000	2	Other
10893	industrial socket 32 A 5 pin	85	350000	2	Other
10894	industrial socket 32 A 3 pin	86	350000	2	Other
10895	Aluminum Oxide	87	350000	2	Raw Material
10896	Wing and fueslage alignement jig (1320x1280x75)	1	350000	2	Other
10925	ADATA Premier DDR4 3200 MHz	28	350000	7	IT Equipment
10926	1TB Hard Disc	29	350000	7	IT Equipment
10933	Directors	2	350000	2	Other
10934	End Caps	3	350000	2	Other
10935	Mounting support	4	350000	2	Other
10971	Fabrication and Installation of Box Beam and Support	1	300000	7	Parts
10972	Construction of RCC Store Shelves	2	300000	7	Other
9899	Cable Tray/ Duct	10	350000	2	Other
9900	DB with Breakers	11	350000	7	Other
9901	Wiring with labour	12	350000	3	Consultancy
9775	PVC windows	2	350000	3	Building Construction
9776	Air vents side of GI wall	3	350000	3	Building Construction
9777	Air vents top of wall	4	350000	3	Building Construction
10739	Machining inserts	6	350000	2	Other
9778	Wooden Workstation	5	350000	3	Building Construction
9779	Cement Flooring	6	350000	3	Building Construction
9780	Labour for structure works	7	350000	3	Consultancy
9586	Part No: MSD0305A300\r\nMotorShield, 480V, 305A, 3 Phase, Type 1/3R, Sinewave Filter \r\nOEM/ORIGIN: TCI USA	1	350000	7	Parts
9587	Freight / Handling	2	350000	3	Other
6465	Part No. NFFS13327G\nMIL-SPEC Part No. M16878/32 BPL	1	350000	7	Parts
6405	100W COFDM Video & Data Amplifier with built-in temperature compensation, antenna open and short circuit protection	1	350000	7	Machinery/Equipment
6407	DHL Shipping	3	350000	3	Transport
10969	Moleclar Pump accessories\r\nPart Number: YTP-50SAB\r\nMFG: ULVAC	1	350000	7	Parts
6414	Receiver for Ground Station	2	350000	7	Machinery/Equipment
10740	End Mill 10mm x 100mm	7	350000	2	Other
6415	S-Glass Fairing & Mount for Folded Dipole Antenna Gain 4dBi & length <=15 inches	1	350000	7	Parts
10970	Part Number YTP-50SAB vacuumed automatically (fully automatic) with the start/stop switch MFG: ULVAC	1	350000	7	Machinery / Equipment
10260	Customized design and development of Bezel-OJ94	1	200000	7	Parts
10262	Customized design and development of Bezel-OJ97	2	200000	7	Parts
10932	Tripod	1	350000	7	Other
10741	End Mill 6mm x 100mm	8	350000	2	Other
10742	Linear Bearing & Linear Guides	9	350000	2	Other
10743	Fasteners	10	350000	2	Other
10744	Paint	11	350000	2	Other
10745	RTV Sealant	12	350000	2	Other
10746	Pedestal Fan	13	350000	7	Appliance
10747	Digital Tachometer	14	350000	7	Test / Measuring Equipment
10748	Fuel Charges	15	350000	2	POL
10749	EDM Charges	16	350000	3	Equipment Modification
10750	Copper Electrode	17	350000	2	Raw Material
10937	Mid Section Cap	6	350000	2	Other
10938	Tap and Die	7	350000	2	Other
10939	Wirecut	8	350000	3	Other
10940	UPS repair	9	350000	3	Equipment Repairs & Maintenance
10941	SMD Resistor 10 k Ohm (0805)	10	350000	2	Other
10942	SMD Resistor 56 Ohm (0805)	11	350000	2	Other
10943	SMD Resistor 39 Ohm (0805)	12	350000	2	Other
10944	SMD Capacitor 1 nF (0805)	13	350000	2	Other
10945	SMD Push Button	14	350000	2	Other
10946	SMD Mosfet IRL3803	15	350000	2	Other
10947	MDF Sheets	16	350000	2	Raw Material
10948	German Glue	17	350000	2	Chemical
10949	Wax	18	350000	2	Chemical
10950	Polycate	19	350000	2	Chemical
10951	Polyester Resin	20	350000	2	Chemical
10952	Gel Coat	21	350000	2	Chemical
10953	Cobalt Hardener	22	350000	2	Chemical
10954	Mixing cups paint brush and gloves	23	350000	2	Other
10955	CAN Adapter	24	350000	7	Parts
10956	LIPO safe bag battery safe guard	25	350000	2	Other
10957	IC HIN232CBZ	26	350000	2	Other
10958	30V 20A bench power supply	27	350000	7	IT Equipment
10959	8S 120A HV ESC	28	350000	2	Other
10960	HP Laptop Victus 15-FA1093DX,13th Gen Intel Core i5, 8GB, 512GB SSD, NVIDIA GeForce RTX 3050 6GB, Backlit KB, 15.6" FHD IPS 144Hz, Windows 11, Performance Blue	29	350000	7	IT Equipment
10961	ADATA Premier DDR4 8GB 3200MHz	30	350000	7	IT Equipment
10963	Kingston NV2 PCle 4.0 NVMe M.2 2280 SSD 1TB	31	350000	7	IT Equipment
11105	Hinge	41	350000	2	Other
10964	Jazz internet charges for NDT & INTELL Lab	32	350000	3	Other
10965	Internet cable for SPF	33	350000	3	Other
10966	Inernet charges for RF lab	34	350000	3	Other
10967	USB 64 GB	35	350000	7	IT Equipment
10968	1TB SSD, 2 x 8GB RAMs	36	350000	7	IT Equipment
10973	Input\r\nVoltage: 3 Phase 380VAC+5%\r\nFrequency: 50/60Hz+5%\r\nOutput\r\nVoltage: 450VDC+2%	1	300000	2	Other
10974	Resistive load bank \r\n550vdc	2	300000	2	Other
10991	Tape	14	350000	2	Other
10976	Arduino Pro Mega	1	350000	2	Other
10978	I2C Module	2	350000	2	Other
10980	Bread Board	3	350000	2	Other
10981	JS connector Male & Female (6pin)	4	350000	2	Other
10982	Jumper wire Male & Female	5	350000	2	Other
10983	Alumina Crucibles	6	350000	2	Other
10984	nichrome wire	7	350000	2	Other
10985	High power supply	8	350000	7	IT Equipment
10986	Acrylic sheet 8ft x 4 ft (5mm)	9	350000	2	Other
10987	Acrylic sheet 8ft x 4 ft (8mm)	10	350000	2	Other
10988	Acrylic sheet 8ft x 4 ft (6mm)	11	350000	2	Other
10989	Bonding Adhesive	12	350000	2	Other
10990	UV protected covering	13	350000	2	Other
10992	Signal Wire 24/26 AWG 100ft	15	350000	2	Other
10993	Buck Convertor XL-4016	16	350000	2	Other
10994	Fiberglass Veroboard	17	350000	2	Other
10995	Terminal Blocks 2pin	18	350000	2	Other
10996	Terminal Blocks 5pin	19	350000	2	Other
10997	Female Headers 2x40	20	350000	2	Other
10998	Male Headers 2x40	21	350000	2	Other
10999	Female Headers 1x40	22	350000	2	Other
11000	Male Headers 1x40	23	350000	2	Other
11001	JST-3pin Male Header	24	350000	2	Other
11002	JST-3pin Female Cable	25	350000	2	Other
11003	Molex 4-pin Male Header Female Cable Pair	26	350000	2	Other
11004	JST-5pin Male Cable	27	350000	2	Other
11005	JST-5pin Female Header	28	350000	2	Other
11006	XLR 2-Pin External Connector Pair	29	350000	2	Other
11007	XLR 4-Pin External Connector Pair	30	350000	2	Other
11008	XLR 7-Pin External Connector Pair	31	350000	2	Other
11012	16 Pin IC Base	35	350000	2	Other
11013	14 Pin IC Base	36	350000	2	Other
11014	8 Pin IC Base	37	350000	2	Other
11015	1206 SMD Resistor 10KOhm 100pack	38	350000	2	Other
11021	Vice Mini	44	350000	2	Other
11022	Vice Bench	45	350000	2	Other
11023	Paper Cutter	46	350000	2	Other
11024	Cutter Blade 10 Pack	47	350000	2	Other
11025	LNA Ro-43508,0.8mm,35 micron, HASL, Normal 2-Layers	48	350000	2	Other
11026	Laptop repairing	49	350000	3	Equipment Repairs & Maintenance
1479	Low Light HD USB Camera	1	300000	7	Parts
1484	Electronic Speed Controller	6	300000	7	Parts
1485	Cast Acrylic Tube 4" (100m depth rated)	7	300000	7	Parts
11051	printer	1	300000	7	IT Equipment
11052	scanner	2	300000	7	IT Equipment
11053	keyboard	3	300000	7	IT Equipment
11027	HP Core i5 13GEN 16 GB RAM, 500 GB SSD Laptop	1	300000	7	IT Equipment
11028	HP color printer	2	300000	7	IT Equipment
11029	Desktop PC core i5 8GB RAM,512 GB HDD	3	300000	7	IT Equipment
11036	Biological Safety Cabinet Model: BSC-1500IIIX MFG: BioBase	1	350000	7	Machinery / Equipment
11049	HP Core i7 13 GEN, 16 GB RAM, 1TB SSD Laptop	1	300000	2	IT Equipment
11050	afdadsfads	2	300000	2	Chemical
11030	HP Core i7 13 GEN, 16 GB RAM, 1TB SSD Laptop	1	300000	7	IT Equipment
11031	HP Printer Black n White	2	300000	7	IT Equipment
11032	Scanner	3	300000	7	IT Equipment
1438	16.8 V 17 AH, Li-ion Battery pack for ROV	1	300000	2	Parts
1439	ROV pay load skid structure	2	300000	2	Parts
1440	Camera/ Mount Assembly for installation on PAP 104	3	300000	2	Parts
1441	DVR	4	300000	7	Machinery / Equipment
1445	Cat 6 Cable for tether development	5	300000	2	Parts
11009	Single Op-Amp CA3130	32	350000	2	Other
11010	Single Op-Amp TL081	33	350000	2	Other
11011	Quad Op-Amp TL084	34	350000	2	Other
11016	Scissor	39	350000	2	Other
11017	Soldering Iron 60W	40	350000	2	Other
11018	Soldering Iron Stand	41	350000	2	Other
11019	Safety Glasses	42	350000	2	Other
11020	KADA Soldering Station	43	350000	2	Other
11037	High Manual Precision Machine vise	1	350000	2	Other
11038	Rail guide	2	350000	2	Other
11039	Tungsten Rhenium Wire 3 0.25mm-0.5mm	3	350000	2	Raw Material
11040	Polyvinyl alcohol (PVA) liquid	4	350000	2	Chemical
11041	Polyvinyl alcohol (PVA) Powder	5	350000	2	Other
11042	Paraffin Wax soft	6	350000	2	Other
11043	Copper foil 0.2mm-0.2mm	7	350000	2	Raw Material
11044	Metal fastners	8	350000	2	Other
11045	Chemical storage container	9	350000	2	Other
11046	copper electrode	10	350000	2	Other
11047	Raw material for barrel	11	350000	2	Raw Material
11048	Coil outsource machining charges	12	350000	3	Other
8557	Fixture seals	2	350000	2	Parts
8558	Load transmission coupling	3	350000	2	Parts
7317	Ruggedized Data storage device	4	400000	2	Parts
7356	Table set steel	3	400000	2	Stationary
7354	paper tray steel	2	400000	2	Stationary
11058	O Ring Seal	1	350000	2	Other
11059	Prime 37 Resin	2	350000	2	Chemical
11060	Fuel charges	3	350000	2	POL
11061	EDM Machining	4	350000	2	Other
11065	Wire mesh 6mm	2	350000	2	Other
8194	Calculator Large	11	160000	2	Stationary
8195	Remote Bell	12	160000	2	Other
8197	Glint	14	160000	2	Other
8202	Rifle of Printer Cartage	19	160000	2	Other
8200	Pencil Cell (06p)	17	160000	2	Stationary
8201	Highlighter, Pencil ,Ball Point ,Gum stick (01 set)	18	160000	2	Stationary
8250	Oil paint	4	160000	2	Other
8198	Cabinet File	15	160000	2	Stationary
8199	File Holder	16	160000	2	Stationary
8251	Kerosene Oil	5	160000	2	Other
8252	Labor of Mason & labourer	6	160000	2	Other
7718	Paint & Wiring work	2	160000	2	Other
7754	Cricket Stumps	4	160000	2	Other
8253	Distemper	7	160000	2	Other
7762	Badminton Rackets	8	160000	2	Other
7765	Basketball	11	160000	2	Other
7752	Ball	2	160000	2	Other
8264	Silicon	4	160000	2	Other
8261	Sac of cement	1	160000	2	Other
8263	Glass	3	160000	2	Other
7736	A4 Paper Photostate Rim	1	160000	2	Stationary
7737	Attendance Register	2	160000	2	Stationary
7738	Box File (Imported)	3	160000	2	Stationary
7739	Ball Point (Packet)	4	160000	2	Stationary
8167	Nokia Mobile 105	1	160000	2	Other
8181	Sanitizer mini Bottle	1	160000	2	Other
8281	19 Core Wire (240 Mtr)	3	450000	2	Other
8288	HDMI Cable (20 Mtr)	10	450000	2	Other
8289	VGA Cable (20 Mtr)	11	450000	2	Other
8295	Hydraulic Valve	17	450000	2	Other
7630	tea Charges	1	350000	2	Other
7319	Interfacing Firmware	2	400000	2	Other
8196	Dust Bin	13	160000	2	Other
7288	Printing and Binding of operation and Maintenance Manual	8	400000	2	Stationary
6892	Vacuum Cleaner VC14301	1	350000	7	Other
7394	HDMI cable	16	250000	7	Parts
7378	Customized Lockers	5	250000	7	Furniture
7377	Office chairs	4	250000	7	Furniture
7376	CPU Trays and table seperator	3	250000	7	Furniture
7375	Printer Table	2	250000	7	Furniture
7374	Office Tables	1	250000	7	Furniture
7322	Provision of chairs	2	400000	7	Furniture
7323	Office Cabinets	3	400000	7	Furniture
7321	Provision of table with chest drawr	1	400000	7	Furniture
8182	Photo state paper 80Grm	2	160000	2	Stationary
8183	Uni Pointer (01 Packet)	3	160000	2	Stationary
8189	Whito Pen	6	160000	2	Stationary
8192	Mint Sheet	9	160000	2	Stationary
8191	Account Ledger Register	8	160000	2	Stationary
8190	Register	7	160000	2	Stationary
8224	Off White Drum(16 liter)	1	160000	2	Other
8227	Sandpaper, plaster of Paris, Glue (1kg)	4	160000	2	Other
8225	Quarter White color 250ml can	2	160000	2	Other
8226	Off white color gallon	3	160000	2	Other
8228	Kerosene oil 1 liter cans	5	160000	2	Other
7769	Kit bag	15	160000	2	Other
7753	Cricket Tape	3	160000	2	Other
7767	Squash ball	13	160000	2	Other
7766	Squash Rackets	12	160000	2	Other
7760	Tennis Balls	7	160000	2	Other
7763	Bedminton shuttlecocks	9	160000	2	Other
7764	Volleyball	10	160000	2	Other
7758	Foot ball	5	160000	2	Other
7759	Tennis Rackets	6	160000	2	Other
8298	Fresh Lemonade and Disposable Cups	1	160000	2	Other
8163	Files	1	160000	2	Stationary
8272	Door Threshold 9x3.5 Feet with Divider	1	160000	2	Other
8273	Door Threshold 7x2.5 Feet	2	160000	2	Other
8268	Kerosene Oil	8	160000	2	Other
8267	Distemper	7	160000	2	Other
8266	Oil Paint	6	160000	2	Other
8262	Sac of sand	2	160000	2	Other
8265	Silicon gun	5	160000	2	Other
7740	Cousioning of Chair back of Conference Room	5	160000	2	Other
8445	Bathroom Toiled requisites, Cleaning materials	1	160000	2	Cleaning Material
8212	Desining 2x3 Notice Board	1	160000	2	Other
8230	Hanging lamps	7	160000	2	Other
8213	Fabrication of 2x3 Notice Board	2	160000	2	Other
8222	Panaflex printing (5.5'x4").	8	160000	2	Other
8236	Flexible water pipe.	2	160000	7	Other
7728	Pedestal Fan	2	160000	7	Office Equipment
9122	Colour work on shed and its frame in tennis area	1	160000	2	Other
9123	Side cover/ frill of fibre glass 3 mm sheet of size 45x1 sq foot	2	160000	2	Raw Material
9150	Bank tax charges	1	160000	3	Transport
9176	Repair of floor of second entrance	1	160000	3	Building Construction
9174	Cement / Sand	12	160000	2	Building Material
9177	Provision of queue barrier	2	160000	7	Other
9169	Tough Tiles 60 mm	7	160000	2	Building Material
9170	Transportation cost	8	160000	3	Building Construction
9171	Khaka / basement	9	160000	2	Building Material
9172	Labor charges	10	160000	3	Building Construction
9173	Kerb Stone	11	160000	2	Building Material
9178	Cement	1	160000	2	Building Repairs & Maintenance
9179	Sand	2	160000	2	Building Repairs & Maintenance
9180	Transportation charges	3	160000	3	Building Repairs & Maintenance
9181	Labor charges for 750 sq ft area	4	160000	3	Building Repairs & Maintenance
9182	Parking poles	5	160000	2	Other
9184	Chain	6	160000	2	Other
9186	Pole fixing labor	8	160000	3	Building Repairs & Maintenance
9185	Welding Charges	7	160000	3	Fabrication
7382	Recolation of DB, rigging of industrial grade socket / 3-phase wiring and change over switch for 42U rack	7	250000	2	Other
8193	USB 32 GB	10	160000	2	Other
9086	Red oxide	18	350000	2	Chemical
9333	Coating	22	350000	3	Other
9075	Safety Gloves	7	350000	2	Other
9076	KN-95 Mask	8	350000	2	Other
9077	Stationary items (Printer Cartridge refill, Paper Rim (A4 Size), Ball Points, Note Pads, White Board Dusters and Markers)	9	350000	2	Stationary
9078	Fuel for Gharo trials	10	350000	2	POL
9079	Fuel for MLQ trials	11	350000	2	POL
9080	Generator Fuel for MC trials	12	350000	2	POL
9081	Jazz Internet Charges for NDT, INTELL & RF LAB	13	350000	3	Other
9082	Internet charges at MF	14	350000	3	Other
9083	Disposable cup	15	350000	2	Other
9084	Micro fiber cloth	16	350000	2	Other
9085	Oil Paint	17	350000	2	Chemical
9320	Buck Converter	10	350000	2	Other
9310	Momentory push button	1	350000	2	Other
9311	On/Off Selector Switch	2	350000	2	Other
9312	Indication LEDs	3	350000	2	Other
9314	37 Pin Mil Spec Connector	4	350000	2	Other
9315	GX 16 2 Pin Connector	5	350000	2	Other
9316	14 Pin 24V DC Relays	6	350000	2	Other
9317	Relay Base	7	350000	2	Other
9318	Power Supply	8	350000	2	Other
9319	Tallies	9	350000	2	Other
9321	Timer Circuit 30 Sec	11	350000	2	Other
9322	3 Pole Selector Switch	12	350000	2	Other
9323	DC Isolator	13	350000	7	Parts
9324	Solder Wire Roll	14	350000	2	Raw Material
9325	10 Pin Connector	15	350000	2	Other
9326	Arduino Nano	16	350000	2	Other
9327	Misc Wiring item	17	350000	2	Other
9328	Wires 0.75 Sq wire	18	350000	2	Other
9087	Wrapping sheet rolls	19	350000	2	Other
9088	Paper Tape, Duct Tape, Double Tape & Electric Tape	20	350000	2	Stationary
9089	Sanding Papers (Different Grades)	21	350000	2	Stationary
9090	WD-40 contact cleaner and Degreaser 01 pack each	22	350000	2	Cleaning Material
9091	Cutting Disc 4", Grinding Disc 4" & Emery Paper Disc 4"	23	350000	2	Other
9093	Screws	24	350000	2	Other
9094	Spray Bottle	25	350000	2	Other
9095	Wiper	26	350000	7	Parts
9096	Lubrication oil	27	350000	2	POL
9097	Grease	28	350000	2	Chemical
9099	Nut bolts	29	350000	2	Other
9100	High pressure clamps	30	350000	2	Other
9101	Welding rods	31	350000	2	Raw Material
9069	Ram upgradation for RF PC	1	350000	7	Parts
9070	Hard Drive for machine shop PC	2	350000	7	Parts
9071	Teflon block	3	350000	2	Raw Material
9072	AC repair work at NDT LAB	4	350000	3	Equipment Repairs & Maintenance
9073	3 quarters of paint	5	350000	2	Chemical
9074	Thinner (L)	6	350000	2	Chemical
5264	Mr. Tabish Hussain	1	350000	3	Travel/Boarding/Lodging
5265	Shaheryar Ahmed Baig	2	350000	3	Travel/Boarding/Lodging
5365	Material charges additional - distember off-white and white _ 10 - Neel, Emergy paper, Puttine , Plaster of paris, Malmal Cloth	3	350000	2	Other
5437	Waleed Bin Yousaf	2	350000	3	Travel/Boarding/Lodging
5435	Lt Cdr Hasnain Afzal Bhatti	1	350000	3	Travel/Boarding/Lodging
5438	Farhan Zafar Khan	3	350000	3	Travel/Boarding/Lodging
5439	Shehzada Wamiq Fahim Shiekh	4	350000	3	Travel/Boarding/Lodging
5440	Irshad Hussain Nadeem	5	350000	3	Travel/Boarding/Lodging
5441	Muhammad Iftikhar Ahmed	6	350000	3	Travel/Boarding/Lodging
5442	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
9297	12 V DC Motor 500W 1400 RPM	1	160000	7	Parts
9065	Purchased accessories for washroom commode flush	1	160000	3	Equipment Installation
9066	03 AC repair in system lab and gas refill	2	160000	3	Equipment Repairs & Maintenance
9064	Installation of public washroom door locks and repair maintenance work	1	160000	3	Equipment Installation
8218	Designing of 2x3 notice board (for addition of PN ships pictures).	4	160000	7	Other
8219	Fabrication of 2x3 notice board with 6mm acyclic sheet, with 04xspacers.	5	160000	7	Other
8220	Fabrication of 2x1" notice board with 6mm acyclic sheet, with 04xspacers (SINC Logo)	6	160000	7	Other
5444	Shehzada Wamiq Shiekh	3	350000	3	Travel/Boarding/Lodging
5443	Ahmad Ali	2	350000	3	Travel/Boarding/Lodging
5445	Hassan Mehmood	4	350000	3	Travel/Boarding/Lodging
5446	Janib Agha	5	350000	3	Travel/Boarding/Lodging
2308	Ball Pen	1	400000	2	Stationary
2309	Whitner	2	400000	2	Stationary
2310	Led Pencil	3	400000	2	Stationary
2311	Eraser	4	400000	2	Stationary
2312	Sticky Notes	5	400000	2	Stationary
2313	Flags	6	400000	2	Stationary
5462	Cable LMR-400, Connector N(M) _ N(M), Length 3m	1	350000	2	Parts
5464	Cable LMR-400, Connector N(M) _ N(M), Length 1ft	2	350000	2	Parts
5465	Cable RG-142, Connector SMA(M) _ N(M), Length 3m	3	350000	2	Parts
7399	False ceiling (02 x labs)	21	250000	7	Other
7379	Tiles(02 x Labs)	6	250000	7	Other
5447	Muhammad Asif	6	350000	3	Travel/Boarding/Lodging
5448	Ahmed Ali	7	350000	3	Travel/Boarding/Lodging
5450	Muhammad Haseeb	1	350000	3	Travel/Boarding/Lodging
5451	Hassan Mehmood	2	350000	3	Travel/Boarding/Lodging
5452	Irshad Hussain Nadeem	3	350000	3	Travel/Boarding/Lodging
5453	Janib Agha	4	350000	3	Travel/Boarding/Lodging
5454	Muhammad Ahmed	5	350000	3	Travel/Boarding/Lodging
5455	Muhammad Iftikhar Ahmed	6	350000	3	Travel/Boarding/Lodging
5466	TA/DA charges (Officer category) 01 Individual	1	350000	3	Travel/Boarding/Lodging
5467	TA/DA charges (Officer category) 01 Individual	2	350000	3	Travel/Boarding/Lodging
5468	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
8610	Lunch on 17 Oct 22	1	200000	3	Meals/Refreshments
8611	Lunch on 18 Oct 22	2	200000	3	Meals/Refreshments
8612	Lunch on 19 Oct 22	3	200000	3	Meals/Refreshments
8613	Lunch on 1 Nov 22	4	200000	3	Meals/Refreshments
8614	Lunch on 10 Nov 22	5	200000	3	Meals/Refreshments
8615	Lunch on 29 Nov 22	6	200000	3	Meals/Refreshments
8657	Lunch on 27 Oct 22	8	200000	3	Meals/Refreshments
8775	TA/DA for Waleed Bin Yousaf, ID: 14-20-08-3623, Grade: PRO\r\nMeezan Account: 01010104120257 (0101)	1	350000	3	Travel/Boarding/Lodging
8577	Cat-6 UTP cable (1000 no)	5	250000	7	Other
8574	Tinted papers and Roll up blinds	2	250000	7	Other
8576	Cat-6 Cable Roll (01 no)	4	250000	7	Other
8578	RJ-45 I/O Face plate back box 6U wall mounted rack	6	250000	7	Other
8594	Pigtails	15	250000	7	Other
8595	Splicing	16	250000	7	Other
8598	Power supply cable type 3,22" for camera switches and UPS supply	19	250000	7	Other
8599	Water Proof boxes for housing camera adopters	20	250000	7	Other
8282	 Camera	4	450000	7	Other
8283	Camera Tripod	5	450000	7	Other
8284	Caera Coaxial Cable	6	450000	7	Other
8285	Camera Mount Setup	7	450000	7	Other
8293	Solar Plates Frame	15	450000	7	Other
8296	Hydraulic Pipe (50 Mtr)	18	450000	7	Other
8633	Official meeting Dec-20	1	160000	3	Other
8634	DG room	1	160000	3	Building Construction
8635	DG Room	1	160000	3	Building Construction
8636	DG Room	1	160000	3	Building Construction
8637	Construction of MD secretariat Cubicle	1	160000	3	Building Construction
8638	Construction of MD Secretariat	1	160000	3	Building Construction
8678	Misc	1	160000	3	Other
8719	misc items	1	160000	3	Other
8722	misc items	1	160000	3	Other
8256	Blocks	1	160000	7	Other
7359	Dust bin steel	4	400000	2	Other
5449	VRx to Antenna Extension Cable (RG-8 MIL SPEC 8m)	1	350000	2	Other
7324	Vinyl Flooring	1	400000	2	Other
7325	Paint Work (Labour and Material)	2	400000	2	Other
7303	Repair & Wood work (Labour and Material. 06 x tables, 03 x Partition screen, 01 x Wooden grill	3	400000	2	Other
7302	Renovation of walls cracks/ Holes & Termite proofing	2	400000	2	Other
7301	Electrical Data (Internet, Intranet, Networking, Labour and Material)	1	400000	2	Other
7304	Glass Partition with door (8' x 10')	4	400000	2	Other
7768	Chess board	14	160000	2	Other
7351	white board	1	400000	7	Office Equipment
7316	Software Package	3	400000	7	Software
8556	Spare motor control unit	1	350000	7	Parts
8505	Air Condition Repairing	1	450000	3	Equipment Repairs & Maintenance
8513	Toyota Corolla GLI/XLI and BRV	1	200000	3	Transport
8591	Cat-VI cable brand 3M	13	250000	7	Other
8572	 Toyota Corolla GLI/XLI	1	200000	3	Transport
8600	water proof boxes for switches and media converter	21	250000	7	Other
8601	Water proof panel for UPS supply distribution	22	250000	7	Other
8773	Toyota Corolla GLI/XLI	1	200000	3	Transport
8616	Lunch on 1 Dec 22	7	200000	3	Meals/Refreshments
1138	Hiace Van	1	200000	3	Transport
8806	Salary for the month of Jan 23.	1	350000	3	Other
8776	TA/DA for Muhammad Asif , ID: 14-20-08-6239, Grade: SRT\r\nMeezan Account: 01010104120656 (0101)	2	350000	3	Travel/Boarding/Lodging
8777	TA/DA for Muhammad Haseeb, ID: 14-22-01-3217, Grade: RO\r\nMeezan Account: 0099290104904038 (9929)	3	350000	3	Travel/Boarding/Lodging
1049	Lunch	1	200000	2	Meals/Refreshments
1477	Lunch on 1 Feb	3	200000	2	Meals/Refreshments
1502	Lunch on 16 Feb	4	200000	2	Meals/Refreshments
1496	Lunch on 9 Feb	1	200000	2	Meals/Refreshments
1635	Lunch on 16 Mar 22	1	200000	2	Meals/Refreshments
1931	Lunch on 22 Mar 22	1	200000	2	Meals/Refreshments
2186	Lunch on 24 May 22	3	200000	2	Meals/Refreshments
2189	Lunch on 2 Jun 22	6	200000	2	Meals/Refreshments
2400	Lunch on 19 July 22	2	200000	2	Meals/Refreshments
2402	Lunch on 21 July 22	4	200000	2	Meals/Refreshments
5355	Lunch on 29 Aug 22	1	200000	2	Meals/Refreshments
8765	Facility repair work (exhaust fan replacement, cement & gravel for levelling)	24	350000	3	Other
8767	Software license renewal	25	350000	3	Other
8745	Drill bits pack	5	350000	7	Fabrication Machinery
8743	Ball link 8mm	3	350000	7	Parts
8747	L Channels (25x25x3mm) (L=1224mm)	7	350000	7	Parts
8748	8mm Sheet (1000mm x 1400mm)	8	350000	7	Raw Material
8749	8mm wire rope	9	350000	7	Raw Material
8750	Turn buckle	10	350000	7	Parts
8751	Load cell (1000kg capacity)	11	350000	7	Parts
8752	Anchoring mounts	12	350000	7	Parts
8753	U-Clamps	13	350000	7	Parts
8754	D Shackle (8mm)	14	350000	7	Parts
8755	Welding rods	15	350000	2	Fabrication Machinery
8756	Red oxide	16	350000	2	Fabrication Machinery
8757	Donek knife	17	350000	7	Tools
8758	Ifixit kit	18	350000	7	Tools
8759	Rivnuts box	19	350000	7	Tools
8760	Safety Gear (Respirator masks & Face shields)	20	350000	7	Other
8761	RO plant filters	21	350000	7	Parts
8762	Paint	22	350000	2	Fabrication Machinery
8764	Thinner	23	350000	2	Fabrication Machinery
8768	Stationary items (paper rim, minute sheets, white board markers, envelopes etc)	26	350000	2	Stationary
8769	8GB RAM for PC	27	350000	7	Office Equipment
117	Door, door handle and door closer.	8	350000	7	Furniture
116	Worktable for VFD area with drawers and cabinets	7	350000	7	Furniture
114	workstation for 10 persons	6	350000	7	Furniture
112	Window, Door and cargo Door on VFD area	4	350000	7	Furniture
107	Lab wall demolishing and reconstruction for lab extension	1	350000	3	Building Modification
8258	Sac of cement	2	160000	7	Other
8249	Sand trolley	3	160000	7	Other
8512	Total Bank Charges	1	160000	3	Other
8507	Purchase of Daily Dawn News Paper for the Month of Nov, Dec 19 and Jan 20.	1	160000	3	Other
8724	misc items	1	160000	3	Other
8727	misc items	1	160000	3	Other
8728	misc items	1	160000	3	Other
8729	misc items	1	160000	3	Other
8478	Repair & Maintenance of computers LCD Flickering, RAM 4GB, Keyboard & Mouse Replacement	1	450000	2	Other
242	Control wiring cable 3m	7	350000	2	Other
8248	Sac of Cement	2	160000	2	Other
241	Indication light	6	350000	2	Other
243	Power wiring	8	350000	2	Other
109	False sealing and insulation under fiber roof of lab extension	2	350000	3	Building Modification
111	Brick missinonary work, plaster and paint work on new VFD work area	3	350000	3	Building Modification
113	Electrical works, AC reinstallation and Associated activities	5	350000	3	Other
236	Transformer rewinding (02 HT & 02 LT sides)	1	350000	3	Equipment Repairs & Maintenance
237	Transformer oil replacement	2	350000	3	Equipment Repairs & Maintenance
238	63A 3pole	3	350000	3	Equipment Repairs & Maintenance
239	20A single pole	4	350000	3	Equipment Repairs & Maintenance
244	Distribution box with dimension 2', 7"x 1', 10" x5'	9	350000	3	Equipment Repairs & Maintenance
2040	Muhammad Attayab Shahid	1	350000	3	Travel/Boarding/Lodging
259	Transformer rewinding (02 HT & 02 LT sides)	1	350000	3	Equipment Repairs & Maintenance
260	63A 3pole	2	350000	3	Equipment Repairs & Maintenance
262	20A single pole	3	350000	3	Equipment Repairs & Maintenance
271	Indication light	5	350000	2	Parts
269	Lugs 35mm cable	4	350000	2	Parts
400	Transformer rewinding (02 HT & 02 LT sides)	1	350000	3	Equipment Repairs & Maintenance
401	Transformer Oil Replacement	2	350000	3	Equipment Repairs & Maintenance
407	Indication Light	6	350000	7	Other
8590	Optic-fiber single mode 06 core	12	250000	7	Other
408	Control Wiring Cable 3m	7	350000	7	Other
1426	Shipment	2	200000	7	Other
668	Lunch for X	1	200000	3	Travel
1043	Hiace	1	200000	3	Transport
1045	Vigo	1	200000	3	Transport
1047	Corolla GLI/XLI	1	200000	3	Transport
1048	Hiace	1	200000	3	Transport
1124	Hiace van	1	200000	3	Transport
1123	Hiace van	1	200000	3	Transport
1122	Hiace van	1	200000	3	Transport
1050	Toyota Corolla GLI/XLI	1	200000	3	Transport
1332	Toyota Corolla XLI or GLI	1	200000	3	Transport
1137	Toyota Corolla GLI or XLI	1	200000	3	Transport
1336	PLA MATERIAL for 3D PRINTER	4	300000	2	Parts
1422	Toyota Corolla XLI or GLI	1	200000	3	Transport
1423	Hiace	2	200000	3	Transport
1471	Toyota Corolla XLI/GLI	1	200000	3	Transport
1472	Hiace	2	200000	3	Transport
1478	Lunch on 2 Feb	4	200000	2	Meals/Refreshments
1494	Toyota Corolla XLI/GLI	1	200000	3	Transport
1495	Hiace	2	200000	3	Transport
1474	Lunch on 1 Feb	1	200000	2	Meals/Refreshments
1476	Lunch on 2 Feb	2	200000	2	Meals/Refreshments
1503	Lunch on 17 Feb	5	200000	2	Meals/Refreshments
1508	Lunch on 1 Mar	12	200000	2	Meals/Refreshments
1507	Lunch on 28 Feb	11	200000	2	Meals/Refreshments
1506	Lunch on 25 feb	8	200000	2	Meals/Refreshments
1505	Lunch on 20 Feb	7	200000	2	Meals/Refreshments
1500	Lunch on 10 Feb	2	200000	2	Meals/Refreshments
1501	Lunch on 15 Feb	3	200000	2	Meals/Refreshments
1504	Lunch on 18 Feb	6	200000	2	Meals/Refreshments
1636	Lunch on 17 Mar 22	2	200000	2	Meals/Refreshments
1692	adfasbnbf,	1	200000	7	Tools / Test Equipment
1877	Rashad Reyaz	3	350000	3	Travel/Boarding/Lodging
1858	Muhammad Atayyab Shahid (Senior Research Officer)	1	350000	3	Travel/Boarding/Lodging
1928	Toyota Corolla GLI/XLI	1	200000	3	Transport
1929	Hiace	2	200000	3	Transport
1875	Muhammad Atayyab Shahid	1	350000	3	Travel/Boarding/Lodging
1876	Muhammad Furqan Siddiqui	2	350000	3	Travel/Boarding/Lodging
1872	Muhammad Atayyab Shahid (SRO)	1	350000	3	Travel/Boarding/Lodging
1873	Farhan Zafar Khan (SRO)	2	350000	3	Travel/Boarding/Lodging
1874	Muhammad Ahmed Khan (RO)	3	350000	3	Travel/Boarding/Lodging
1926	Toyota Corolla GLI/XLI	1	200000	3	Transport
1809	Labor	2	160000	3	Fabrication
240	Lugs 35mm cable	5	350000	2	Other
272	Control wiring cable 3m	6	350000	2	Other
273	power wiring	7	350000	2	Other
406	Lugs 35mm Cable	5	350000	2	Other
618	Slip Resistant Rubber Mat for Test Tank Area	3	300000	2	Other
621	Casting Material for Buoyancy Chamber	5	300000	2	Other
703	Refreshments for FATs team	32	350000	2	Other
1927	Hiace	2	200000	3	Transport
1932	Lunch on 24 Mar 22	2	200000	2	Meals/Refreshments
2041	Farhan Zafar Khan	2	350000	3	Travel/Boarding/Lodging
2042	Muhammad Ahmed Khan	3	350000	3	Travel/Boarding/Lodging
2020	Muhammad Attayab Shahid	1	350000	3	Travel/Boarding/Lodging
2022	Muhammad Iftikhar	2	350000	3	Travel/Boarding/Lodging
2082	Toyota Corolla GLI/XLI	1	200000	3	Transport
2137	Hiace	1	200000	3	Transport
2138	Toyota Corolla GLI/XLI	1	200000	3	Transport
2091	Ticket Expense (Commercial Flight)	2	350000	3	Travel/Boarding/Lodging
2043	Muhammad Iftikhar Ahmed	4	350000	3	Travel/Boarding/Lodging
2188	Lunch on 26 May 22	5	200000	2	Meals/Refreshments
1626	Toyota Corolla GLI/XLI	1	200000	3	Transport
2194	Toyota Corolla GLI/XLI	1	200000	3	Transport
2185	Lunch on 23 May 22	2	200000	2	Meals/Refreshments
2187	Lunch on 25 May 22	4	200000	2	Meals/Refreshments
2193	Lunch on 10 Jun 22	10	200000	2	Meals/Refreshments
2192	Lunch on 9 Jun 22	9	200000	2	Meals/Refreshments
2191	Lunch on 8 Jun 22	8	200000	2	Meals/Refreshments
2184	Lunch on 19 May 22	1	200000	2	Meals/Refreshments
2190	Lunch on 7 Jun 22	7	200000	2	Meals/Refreshments
2212	Toyota Corolla GLI/XLI	1	200000	3	Transport
2253	Muhammad Attayab Shahid	1	350000	3	Travel/Boarding/Lodging
2254	Syed Wahab Zarin	2	350000	3	Travel/Boarding/Lodging
2255	Tabish Hussain	3	350000	3	Travel/Boarding/Lodging
2256	Muhammad Asif	4	350000	3	Travel/Boarding/Lodging
2257	Muhammad Iftikhar Ahmed	5	350000	3	Travel/Boarding/Lodging
2157	Waleed Bin Yousuf	1	350000	3	Travel/Boarding/Lodging
2158	Shehzada Wamiq	2	350000	3	Travel/Boarding/Lodging
2159	Muhammad Haseeb	3	350000	3	Travel/Boarding/Lodging
2160	Muhammad Asif	4	350000	3	Travel/Boarding/Lodging
2161	Muhammad Iftikhar Ahmed	5	350000	3	Travel/Boarding/Lodging
2162	Ahmed Ali	6	350000	3	Travel/Boarding/Lodging
2398	Hiace	2	200000	3	Transport
903	Stands Fabrication, Wooden Race floor, Grey Carpet, LCD stands inclusive of labour and transport	1	350000	3	Other
2397	BRV	1	200000	3	Transport
2399	Lunch on 18 July 22	1	200000	2	Meals/Refreshments
2401	Lunch on 20 July 22	3	200000	2	Meals/Refreshments
2092	Wahab Zarin	1	350000	3	Travel/Boarding/Lodging
2090	Muhammad Atayyab Shahid	1	350000	3	Travel/Boarding/Lodging
11508	Connection strip 25A	15	200000	2	Other
2593	Pneumatic Actuator Upgradation to operate at 80 bar pressure	1	350000	3	Equipment Modification
2594	Crane and Truck Rent for Launcher transportation to/from Port Qasim	2	350000	3	Other
2595	New concrete floor 20ft x 12 ft complete with labour	3	350000	3	Building Modification
2596	Wood polish, paint and wooden floor	4	350000	3	Building Modification
2680	Muhammad Atayyab Shahid	5	350000	3	Travel/Boarding/Lodging
2677	Muhammad Furqan Siddiqui	2	350000	3	Travel/Boarding/Lodging
2681	Muhammad Iftikhar Ahmed	6	350000	3	Travel/Boarding/Lodging
2682	Ahmed Ali	7	350000	3	Travel/Boarding/Lodging
2678	Muhammad Ahmed Khan	3	350000	3	Travel/Boarding/Lodging
2675	Waleed Bin Yousuf	1	350000	3	Travel/Boarding/Lodging
2679	Muhammad Haseeb	4	350000	3	Travel/Boarding/Lodging
2699	Toyota Corolla GLI/XLI	1	200000	3	Transport
2842	Muhammad Atayyab Shahid	1	350000	3	Travel/Boarding/Lodging
2845	Muhammad Iftikhar Ahmed	3	350000	3	Travel/Boarding/Lodging
2843	Shehzada Wamiq	2	350000	3	Travel/Boarding/Lodging
2892	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
2894	Muhammad Haseeb	2	350000	3	Travel/Boarding/Lodging
2895	Ahmed Ali	3	350000	3	Travel/Boarding/Lodging
3049	TA/DA for Muhammad Haseeb, ID: 14-22-01-3217, Grade: RO\r\nMeezan Account: 0099290104904038 (9929)	1	350000	3	Travel/Boarding/Lodging
3050	TA/DA for Irshad Hussain Nadeem, ID: 14-20-06-2173, Grade: SRT\r\nMeezan Account: 0129-0104503403 (0129)	2	350000	3	Travel/Boarding/Lodging
3051	TA/DA for Muhammad Iftikhar Ahmed, ID: 14-20-08-6977, Grade: RT\r\nMeezan Account: 01010104120469 (0101)	3	350000	3	Travel/Boarding/Lodging
2021	Flow meter	1	160000	7	Machinery / Equipment
3043	TA/DA for Waleed Bin Yousaf, ID: 14-20-08-3623, Grade: PRO\r\nMeezan Account: 01010104120257 (0101)	1	350000	3	Travel/Boarding/Lodging
3045	TA/DA for Muhammad Haseeb, ID: 14-22-01-3217, Grade: RO\r\nMeezan Account: 0099290104904038 (9929)	2	350000	3	Travel/Boarding/Lodging
3047	TA/DA for Irshad Hussain Nadeem, ID: 14-20-06-2173, Grade: SRT\r\nMeezan Account: 0129-0104503403 (0129)	3	350000	3	Travel/Boarding/Lodging
3048	TA/DA for Muhammad Iftikhar Ahmed, ID: 14-20-08-6977, Grade: RT\r\nMeezan Account: 01010104120469 (0101)	4	350000	3	Travel/Boarding/Lodging
5268	Syed Muhammad Mehdi Haider	3	350000	3	Travel/Boarding/Lodging
5269	Muhammad Asif	4	350000	3	Travel/Boarding/Lodging
5263	Air ticket for Muhammad Atayyab Shahid, ID: 14-20-08-1035, Grade: SRO\r\nMeezan Account: 01010104120755 (0101)	3	350000	3	Travel/Boarding/Lodging
5260	TA/DA for Muhammad Atayyab Shahid, ID: 14-20-08-1035, Grade: SRO\r\nMeezan Account: 01010104120755 (0101)	1	350000	3	Travel/Boarding/Lodging
5261	TA/DA for Shaheryar Ahmed Baig, ID: 14-21-09-9933, Grade: RO\r\nMeezan Account: 0145-0105925531 (0145)	2	350000	3	Travel/Boarding/Lodging
5496	Wajeeh ul haq	5	350000	3	Travel/Boarding/Lodging
5364	Labor charges (Lump Sum) for additional work (back quarters) - White wash with minor scrapping of 02 x back quarters with outer wall	2	350000	3	Building Repairs & Maintenance
5408	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
5409	Shehzada Wamiq Shiekh	2	350000	3	Travel/Boarding/Lodging
5410	Muhammad Asif	3	350000	3	Travel/Boarding/Lodging
5411	Muhammad Iftikhar Ahmed	4	350000	3	Travel/Boarding/Lodging
5412	Ahmed Ali	5	350000	3	Travel/Boarding/Lodging
5413	Muhammad Haseeb	1	350000	3	Travel/Boarding/Lodging
5414	Irshad Hussain Nadeem	2	350000	3	Travel/Boarding/Lodging
5415	Muhammad Rafay Shahzad	3	350000	3	Travel/Boarding/Lodging
5416	Wajeeh ul haq	4	350000	3	Travel/Boarding/Lodging
5417	Waleed Bin Yousaf	5	350000	3	Travel/Boarding/Lodging
5418	Janib Agha	6	350000	3	Travel/Boarding/Lodging
5363	Labor charges (Lump sum) for additional work - Major scrapping of walls main hall - white wash of walls above 10ft of main hall - Extra coat of distemper for roof	1	350000	3	Building Repairs & Maintenance
5434	TA/DA charges (officer category) 01 individual	1	350000	3	Travel/Boarding/Lodging
5433	TA/DA charges (Officer category) 01 Individual	1	350000	3	Travel/Boarding/Lodging
5507	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
5510	TA/DA for Shehzada Wamiq\r\nID: 14-20-08-8409, Grade: RO, Meezan Account: 01960104147943 (0196)	8	350000	3	Travel/Boarding/Lodging
5511	Farhan Zafar Khan	2	350000	3	Travel/Boarding/Lodging
5513	Rashad Reyaz	3	350000	3	Travel/Boarding/Lodging
5475	Return Airfare Charges	5	350000	3	Travel/Boarding/Lodging
5477	Lt Cdr Husnain Bhatti	6	350000	3	Travel/Boarding/Lodging
5514	Muhammad Asif	4	350000	3	Travel/Boarding/Lodging
5470	Farhan Zafar Khan	2	350000	3	Travel/Boarding/Lodging
5472	Rashad Riyaz	3	350000	3	Travel/Boarding/Lodging
5474	Muhammad Asif	4	350000	3	Travel/Boarding/Lodging
5491	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
5492	Shehzada Wamiq Fahim Shiekh	2	350000	3	Travel/Boarding/Lodging
5493	Rashad Reyaz	3	350000	3	Travel/Boarding/Lodging
5495	Tabish Hussain	4	350000	3	Travel/Boarding/Lodging
5358	Lunch on 10 Oct 22	4	200000	2	Meals/Refreshments
5359	Lunch on 11 Oct 22	5	200000	2	Meals/Refreshments
5360	Lunch on 12 Oct 22	6	200000	2	Meals/Refreshments
5357	Lunch on 6 Oct 22	3	200000	2	Meals/Refreshments
5361	Lunch on 13 Oct 22	7	200000	2	Meals/Refreshments
5356	Lunch on 1 Sep 22	2	200000	2	Meals/Refreshments
5498	Iftikhar Ahmed	6	350000	3	Travel/Boarding/Lodging
5499	Shahzaib Malik	7	350000	3	Travel/Boarding/Lodging
5500	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
5501	Muhammad Asif	2	350000	3	Travel/Boarding/Lodging
5502	Tabish Hussain	1	350000	3	Travel/Boarding/Lodging
5504	Tabish Hussain	1	350000	3	Travel/Boarding/Lodging
5505	Airfare charges	2	350000	3	Travel/Boarding/Lodging
5506	Tabish Hussain	1	350000	3	Travel/Boarding/Lodging
5518	Repair & Testing ZHL-100W-272+	1	350000	3	Equipment Repairs & Maintenance
5575	Capt (Rtd) Farooque Azam TA/DA	1	200000	3	Travel/Boarding/Lodging
5579	TA/DA to Muhammad Tayyab Janjua	1	200000	3	Travel/Boarding/Lodging
5580	Lunch for project teams at ELINT project site	1	200000	3	Travel/Boarding/Lodging
7746	Tea Charges	1	160000	3	Other
5581	TA/DA and reimbursements of courier charges	1	200000	3	Travel/Boarding/Lodging
7327	Repair Workstation	1	400000	3	Other
7328	Labour Charges	2	400000	3	Other
7420	Ahmed Ali	8	350000	3	Travel/Boarding/Lodging
7384	Gas filling and repairing of 4-ton AC with service charges on room 108	10	250000	3	Equipment Repairs & Maintenance
7385	Recolation / reinstallation of 4-ton AC with wiring and service charges in room 109	11	250000	3	Equipment Installation
7392	Fumigation	14	250000	3	Other
7401	Wall breaking and Finishing	23	250000	3	Building Repairs & Maintenance
7402	Separation wall	24	250000	3	Building Modification
7404	Waleed bin Yousaf	1	350000	3	Travel/Boarding/Lodging
7406	Hassan Mehmood	3	350000	3	Travel/Boarding/Lodging
7405	Shehzada Wamiq Sheikh	2	350000	3	Travel/Boarding/Lodging
7407	Janib Agha	4	350000	3	Travel/Boarding/Lodging
7409	Muhammad Asif	5	350000	3	Travel/Boarding/Lodging
7410	Ahmed Ali	6	350000	3	Travel/Boarding/Lodging
7411	Muhammad Iftikhar Ahmed	7	350000	3	Travel/Boarding/Lodging
7412	Adnan Shah	8	350000	3	Travel/Boarding/Lodging
7414	Hassan Mehmood	2	350000	3	Travel/Boarding/Lodging
7415	Muhammad Asif	3	350000	3	Travel/Boarding/Lodging
7413	Waleed Bin Yousaf	1	350000	3	Travel/Boarding/Lodging
7416	Farhan ul Haq	4	350000	3	Travel/Boarding/Lodging
7417	Farhan Zafar Khan	5	350000	3	Travel/Boarding/Lodging
7418	Shehzada Wamiq Sheikh	6	350000	3	Travel/Boarding/Lodging
7419	Muhammad Iftikhar Ahmed	7	350000	3	Travel/Boarding/Lodging
7373	Toyota Corolla GLI/XLI	1	200000	3	Transport
5515	Canopy for GRP Fabrication Area (37' x 12' x 10')\r\nPipe support structure and 2mm fiber glass sheet	1	350000	3	Fabrication
7596	TY Duty	1	250000	3	Travel/Boarding/Lodging
7597	Boarding, lodging and travel of industrial collaborators visiting from North	1	200000	3	Travel/Boarding/Lodging
7672	Transportation charges	1	250000	3	Other
118	Miscellaneous Activities	9	350000	3	Building Modification
7747	Tea Charges	1	160000	3	Other
7719	Aluminium Door Repairing	3	160000	3	Equipment Repairs & Maintenance
7722	Service & Labour Charge for Repair of  AC	3	160000	3	Equipment Repairs & Maintenance
7729	Serving Wooden Tray	3	160000	3	Other
7741	Floor burnishing and polish in RDW block (Both ground and first floor including stairs 2504 sq. ft)	1	160000	3	Building Repairs & Maintenance
7770	Repair of AC Dir Fin office	1	160000	3	Equipment Repairs & Maintenance
7771	Refill of HP Laser Jet Printer Cartage	2	160000	3	Equipment Repairs & Maintenance
8164	Wooden Foundation for Porta Cabin	1	160000	3	Other
8165	Fresh Lemonade and Disposable Cups (50 Person)	1	160000	3	Other
8166	Tea Charges (Meeting)	1	160000	3	Other
8170	Pantry Items	1	160000	3	Other
8172	Thermos	3	160000	3	Other
8173	Fork ,Spoon,Fruit Knife	4	160000	3	Other
8174	Dishes sets	5	160000	3	Other
8171	Jug, Glass	2	160000	3	Other
8175	Tea Bar Extra Charges	1	160000	3	Other
8215	Instalation Charges	3	160000	3	Other
8223	Installation charges/labor	9	160000	3	Other
8229	Labour charges of painter & helper	6	160000	3	Other
8237	R & D Meeting With DG NRDI	1	160000	3	Other
8239	MD/HD Meeting	3	160000	3	Other
8240	MD/HD Meeting	4	160000	3	Other
8241	DG/HD Meeting	5	160000	3	Other
8242	DG MTC Meeting	6	160000	3	Other
8243	MISL Meeting with R & D	7	160000	3	Other
8244	NRDI COMPLEX with DG PD NRDI and M/S Principle	8	160000	3	Other
8238	Meeting with MTSS team MD R & D	2	160000	3	Other
8245	Fresh Lemonade and Disposable cups	1	160000	3	Other
8246	Aloo Samosa with Disposable plate	2	160000	3	Other
8255	Labor of Painter	9	160000	3	Other
8270	Labor of Carpenter	10	160000	3	Other
7400	Paint plus specialized lights absorbing coating (02 x labs)	22	250000	3	Other
8254	Labor of Corpenter	8	160000	3	Other
7625	Air Ticket for Shaheryar Ahmed Baig, ID: 14-21-09-9933, Grade: RO\r\nMeezan Account: 0145-0105925531 (0145)	4	350000	3	Travel/Boarding/Lodging
8504	Admin arrangments for visit of Chinese team to NRDI	1	350000	3	Other
8506	Hydraulic Chair Repairing	2	450000	3	Other
8602	Piging work	23	250000	3	Other
8603	Wood work at C&CC and walk Through gate updation	24	250000	3	Other
8643	TA/DA to 02 x staff	1	450000	3	Travel/Boarding/Lodging
8644	Systems Lab renovation	1	450000	3	Building Modification
8609	Toyota Corolla GLI/XLI	1	200000	3	Transport
8559	Freight / Handling	4	350000	3	Other
8645	Systems Lab renovation	1	450000	3	Building Modification
8744	Raw bolts	4	350000	7	Parts
8739	3K Carbon (toray) Twill, FAW 204, 1000mm, 100m/roll	1	350000	7	Raw Material
8746	Hollow pipe 30mm	6	350000	7	Raw Material
8740	M8 SS Screws	1	350000	7	Parts
8742	Bowden cable	2	350000	7	Parts
8807	Salary of Mr Jackson Ishaq (SW) for the month of Jan 23	1	350000	3	Other
8981	TA/DA for Muhammad Asif , ID: 14-20-08-6239, Grade: SRT\r\nMeezan Account: 01010104120656 (0101)	1	350000	3	Travel/Boarding/Lodging
8996	Toyota Corolla GLI/XLI	1	200000	3	Transport
9329	Wire Tagging Service	19	350000	3	Other
9330	MS Sheet for Panel	20	350000	2	Raw Material
9332	Relay 24V	21	350000	2	Other
9334	1 TB Hard drive with lock	23	350000	7	IT Equipment
9444	Lunch on 15 Feb	2	200000	3	Meals/Refreshments
9431	Toyota Corolla GLI/XLI	1	200000	3	Transport
9443	Lunch on 14 Feb	1	200000	3	Meals/Refreshments
9307	Lubrication	10	160000	7	Parts
9507	Carpentar work	6	160000	3	Building Construction
9508	Dismantling of old damaged wooden threshold (choukhat) by termite	7	160000	3	Building Modification
11622	Helicoil M8 X 1.25 mm	6	350000	2	Other
9509	Fixing of steel door threshold (choukhat) with material gauge 18	8	160000	3	Building Repairs & Maintenance
9510	Fixing of bidding around thresholds	9	160000	3	Building Repairs & Maintenance
9511	Provision of flush tank and fixing charges	10	160000	3	Building Repairs & Maintenance
9512	Provision of Sink tap (plastic) with charges of fitting and removing of old one	11	160000	7	Other
9514	Provision of light frame capsuled shape with bulb and fixing charges	12	160000	2	Other
9515	LED bulb for washroom roof holders	13	160000	2	Other
9516	Sink Mirror	14	160000	7	Other
8269	Labor of Mason and laborer	9	160000	3	Other
8271	Labor of Painter	11	160000	3	Other
8278	Rozee.pk Domain Account For 1 Year	1	160000	3	Other
8419	Repair work water supply line PG Block	1	160000	3	Other
8432	Repair of Acs	1	160000	3	Other
8439	Pantry Bill & Misc Expenditure	1	160000	3	Other
8476	Electrical work & paint work in MD R&D Office	1	160000	3	Other
8477	Repair and polish of 08 x chairs and 01 x table, 01 x White board fitting in R&D wing conference room	2	160000	3	Other
8501	Pantry Bill Charges	1	160000	3	Other
8502	Pantry Bill	1	160000	3	Other
8639	Data center construction	1	160000	3	Building Construction
8640	Stand Easy annual round DMT Staff	1	160000	3	Other
8641	Refreshment for Table Tennis Championship	1	160000	3	Other
8642	Telephone wiring in PG Block	1	160000	3	Building Repairs & Maintenance
9187	Color (red and green)	9	160000	2	Building Material
9188	Kerosene oil	10	160000	2	Cleaning Material
9189	Labor charges for paint work	11	160000	3	Building Repairs & Maintenance
9299	12 V Battery	2	160000	7	Parts
9300	Electric Panel	3	160000	7	Parts
9301	Electric Wire	4	160000	7	Parts
9302	Speed Controller for DC Motor	5	160000	7	Parts
9303	Motor Protection Cover	6	160000	7	Parts
9304	Motor Flange	7	160000	7	Parts
9305	SS Rods with Rings	8	160000	7	Parts
9306	Bottom Cover for Structure	9	160000	7	Parts
9308	Motor O Ring & Seal	11	160000	7	Parts
9309	Mechanical Fabrication	12	160000	7	Parts
9505	Glass fitting with partition in glass	4	160000	3	Building Construction
9506	Polish of complete wooden partition	5	160000	3	Building Repairs & Maintenance
9585	TA/DA for Muhammad Iftikhar Ahmed, ID: 14-20-08-6977, Grade: RT\r\nMeezan Account: 01010104120469 (0101)	1	350000	3	Travel/Boarding/Lodging
9608	Lunch on 31 May	1	200000	3	Meals/Refreshments
9612	Lunch on 2 June	2	200000	3	Meals/Refreshments
9613	Lunch on 7 June	3	200000	3	Meals/Refreshments
9614	Lunch on 8 June	4	200000	3	Meals/Refreshments
9615	Lunch on 13 June	5	200000	3	Meals/Refreshments
9616	Lunch on 14 June	6	200000	3	Meals/Refreshments
9607	Toyota Corola GLI/XLI	1	200000	3	Transport
9875	TA/DA for Muhammad Iftikhar Ahmed, ID: 14-20-08-6977, Grade: RT\r\nMeezan Account: 01010104120469 (0101)	1	350000	3	Travel/Boarding/Lodging
9876	TA/DA for Irfan Muqim, ID: 14-22-10-8599, Grade: RO\r\nMeezan Account: 0116-0107346292 (0116)	2	350000	3	Travel/Boarding/Lodging
9885	Toyota Corola GLI/XLI	1	200000	3	Transport
9947	TA/DA for Irshad Hussain Nadeem, ID: 14-20-06-2173, Grade: SRT\r\nMeezan Account: 0129-0104503403 (0129)	1	350000	3	Travel/Boarding/Lodging
9948	TA/DA for Muhammad Asif , ID: 14-20-08-6239, Grade: SRT\r\nMeezan Account: 01010104120656 (0101)	2	350000	3	Travel/Boarding/Lodging
9921	TA/DA for Waleed Bin Yousaf, ID: 14-20-08-3623, Grade: PRO\r\nMeezan Account: 01010104120257 (0101)	1	350000	3	Travel/Boarding/Lodging
9922	TA/DA for Tabish Hussain, ID: 14-20-10-6921, Grade: SRO\r\nMeezan Account: 0122-0105094054 (0122)	2	350000	3	Travel/Boarding/Lodging
9924	TA/DA for Ahmed Ali, ID: 14-20-06-9295, Grade: RT\r\nMeezan Account: 9915-0104535304 (9915)	3	350000	3	Travel/Boarding/Lodging
9925	TA/DA for Jawad Mehdi, ID: 14-22-09-6559, Grade: RT\r\nMeezan Account: 0117-0107259487 (0117)	4	350000	3	Travel/Boarding/Lodging
10242	TA/DA for Tabish Hussain, ID: 14-20-10-6921, Grade: SRO\r\nMeezan Account: 0122-0105094054 (0122)	1	350000	3	Travel/Boarding/Lodging
10243	TA/DA for Muhammad Iftikhar Ahmed, ID: 14-20-08-6977, Grade: RT\r\nMeezan Account: 01010104120469 (0101)	2	350000	3	Travel/Boarding/Lodging
9535	Sink waist pipe exchange	31	160000	2	Other
9536	Provision and installation of muslim showers with metallic chain pipe	32	160000	2	Other
9500	AC repair and service charges (circuit board repair, complete service indoor and outdoor unit)	1	160000	3	Equipment Repairs & Maintenance
9543	Ceiling lights purchased for the office of Dir propulsion	3	160000	2	Other
9519	Provision and installation of Muslim showers with metallic chain pipe	16	160000	2	Other
9520	Provision of light frame capsuled shape with bulb and installation charges	17	160000	2	Other
9521	Provision of sink tap (plastic) with charges of fitting and removing of old one	18	160000	2	Other
9523	Provision of exhaust fan with fitting charges	20	160000	7	Appliance
9525	Provision of flush tank and fixing charges	21	160000	2	Other
9526	Provision and installation of Muslim showers with metallic chain pipe	22	160000	2	Other
9527	Provision of light frame capsuled shape with bulb and installation charges	23	160000	2	Other
9528	Provision of sink tap with charges of fitting and removing of old one	24	160000	2	Other
9529	Provision of exhaust fan with fitting charges	25	160000	7	Appliance
9530	Commode fitting complete	26	160000	2	Other
9531	Commode seat covers	27	160000	2	Other
9532	Door lock with fitting charges	28	160000	2	Other
9533	Cemented repair work in wall with material charges	29	160000	3	Building Repairs & Maintenance
9534	Oil paint of all washroom doors	30	160000	3	Building Repairs & Maintenance
9538	Dustbin for washroom	34	160000	7	Furniture
9539	Tissue roll packets	35	160000	2	Cleaning Material
9540	Provision and installation of new commode set	36	160000	7	Other
9541	Tissue dispensing box with tissue for three sinks in officer's washroom	37	160000	2	Other
9542	Floor gridding work to stop floor seepage	38	160000	3	Building Repairs & Maintenance
11709	Safety Helmet	6	300000	7	Other
9537	Tissue roll holders for officer's washroom	33	160000	2	Other
9729	Labour Charges	2	160000	3	Equipment Modification
9726	Purchase of tough tiles for parking of service vehicles area 310 sq ft	1	160000	2	Building Material
9727	Labour Charges	2	160000	3	Building Modification
9715	Fabrication of fiber glass (01 mm) shade for parking size 140 sqft	1	160000	3	Fabrication
9725	Labour Charges	2	160000	3	Equipment Installation
9714	Installation of shade by welding and fixing with labour charges	1	160000	3	Equipment Installation
1637	Thumb Seccgen Hamster Plus	1	160000	7	Office Equipment
1638	Cannon Camera	2	160000	7	Office Equipment
10154	Repair and parts replacement of damaged office revolving chairs	2	160000	3	Equipment Repairs & Maintenance
9522	LED bulb for wasroom roof holders	19	160000	2	Other
10324	Stock size (400mm x 80mm)	1	350000	3	Travel/Boarding/Lodging
10376	Toyota Corola GLI/XLI	1	200000	3	Transport
10445	Bearing (63002-A-2RSR)	4	350000	2	Other
10442	Coaxial Cables RG142	2	350000	2	Other
10444	UPS Repair Charges	3	350000	3	Equipment Repairs & Maintenance
10446	Pneumatic Pipe	5	350000	2	Other
10447	Nylon Strap Repairing	6	350000	3	Equipment Repairs & Maintenance
10448	Fuel	7	350000	2	POL
10449	Generator Oil	8	350000	2	POL
10450	SS Wire 3mm	9	350000	2	Other
10451	Clear Epoxy	10	350000	2	Chemical
10452	Butterfly Wire Lock	11	350000	2	Other
10453	Paint Hardener And Thinner	12	350000	2	Chemical
10454	Acetone	13	350000	2	Chemical
10455	White Board With Duster (3ft x 4ft)	14	350000	7	Furniture
10456	MS Material (190 x 110 x 40)	15	350000	2	Raw Material
10457	Aluminum Material (70mm x 1000mm)	16	350000	2	Raw Material
10458	MS Material (30mm x 1000mm)	17	350000	2	Raw Material
10459	Machining	18	350000	3	Equipment Modification
10460	MS stock	19	350000	2	Raw Material
10461	Copper stock	20	350000	2	Raw Material
10462	EDM charges	21	350000	3	Equipment Modification
10463	Fixed SMD Inductor 82nH 100mA 2.5 Ohm	22	350000	2	Other
10464	Fixed SMD Inductor 5.6nH 300mA 270 mOhm	23	350000	2	Other
10465	Fixed SMD Inductor 15nH 250mA 600mOhm	24	350000	2	Other
10466	Capacitor Cer 1000pF 50V X7R 0402	25	350000	2	Other
10467	Capacitor Cer 56pF 50V C0G/Np0 0402	26	350000	2	Other
10468	Capacitor Cer 0.1uF 16V X7R 0402	27	350000	2	Other
10469	Resistor 1K Ohm 5% 1/10W 0402	28	350000	2	Other
10470	Capacitor Cer 82pF 16V Np0 0402	29	350000	2	Other
10471	Capacitor Cer 68pF 50V C0G/Np0 0402	30	350000	2	Other
10472	SMT LNA, 700 - 3000 Mhz, 50	31	350000	2	Other
10473	Filter Saw 1.575Ghz 5 SMD	32	350000	2	Other
10474	SMA connector Jack Str 50Ohm Edge Mount	33	350000	2	Other
10475	SMA connector Jack Str 50 Ohm Solder	34	350000	2	Other
10441	14 SS Turnbuckle	1	350000	2	Other
10485	Rubber dampper hard stop	44	350000	2	Other
10486	Launcher paint hardner thinner	45	350000	2	Chemical
10487	PU foam	46	350000	2	Raw Material
10488	Loctite 243	47	350000	2	Chemical
10514	PCB fabrication	4	350000	3	Fabrication
10476	SMA connector N Plug To SMA Jack	35	350000	2	Other
10477	SMA connector Plug To N Plug	36	350000	2	Other
10478	SMA connector Jack To N Jack	37	350000	2	Other
10479	Connector N Plug Straight Crimp	38	350000	2	Other
10480	Connector N Jack STR 50 Ohm Crimp	39	350000	2	Other
10481	Steel wire 8 mm	40	350000	2	Raw Material
10482	Braking strap 2"	41	350000	2	Other
10483	Braking strap stitching	42	350000	2	Other
10497	Repair charges of COFDM tracking board\r\nBRAND/OEM: KIMPOK	1	350000	3	Equipment Repairs & Maintenance
10484	Rubber dampper actuator	43	350000	2	Other
10489	Wood plank	48	350000	2	Other
10510	2 KVA UPS 2kW with complete accessories	1	350000	7	Appliance
10511	SNP device	2	350000	7	Machinery / Equipment
10513	UPS wiring	3	350000	2	Other
10490	D shackles SS 316	49	350000	2	Other
10491	Paint brush box	50	350000	2	Other
10492	Hand tap 4 mm	51	350000	2	Other
10493	Steel wire brush (hard and soft)	52	350000	2	Other
10494	Heat gun	53	350000	7	Machinery/Equipment
10495	SS threaded rod with nut	54	350000	2	Other
10496	UPS Battery 100AH	55	350000	7	Machinery/Equipment
10588	Mini truck of soft soil (bhalu matti) for R&D Wing garden grass. Cost includes transportation and unloading charges	2	160000	2	Other
10648	TA/DA for Muhammad Rafay, ID: 14-21-12-7103, Grade: RO\r\nMeezan Account: 01380106342757 (0138)	1	350000	3	Travel/Boarding/Lodging
10650	TA/DA for Muhammad Asif , ID: 14-20-08-6239, Grade: SRT\r\nMeezan Account: 01010104120656 (0101)	5	350000	3	Travel/Boarding/Lodging
10651	Air ticket for Muhammad Asif , ID: 14-20-08-6239, Grade: SRT\r\nMeezan Account: 01010104120656 (0101)	6	350000	3	Travel/Boarding/Lodging
10649	Air ticket for Muhammad Rafay, ID: 14-21-12-7103, Grade: RO\r\nMeezan Account: 01380106342757 (0138)	2	350000	3	Travel/Boarding/Lodging
11157	MS Sheet (8ft*4ft*1.5mm)	1	350000	2	Raw Material
10647	Air ticket for Shehzada Wamiq, ID: 14-20-08-8409, Grade: RO\r\nMeezan Account: 01960104147943 (0196)	3	350000	3	Travel/Boarding/Lodging
10637	TA/DA for Shehzada Wamiq, ID: 14-20-08-8409, Grade: RO\r\nMeezan Account: 01960104147943 (0196)	4	350000	3	Travel/Boarding/Lodging
10723	Toyota Corola GLI/XLI	1	200000	3	Transport
10803	Honda-BRV	1	200000	3	Transport
10338	Part Number: 14850011\r\nPA-control 2, programming tool\r\nPart Number: PN-14850006\r\nAdapter MTA100-DE9, for actuators without connector\r\nMake: Pegasus Actuators GmbH	1	350000	7	Tools
11064	Solder Iron 120W	1	350000	7	Machinery / Equipment
11119	Al Stock 500*200*85	1	350000	2	Raw Material
9728	Purchase of tough tiles for parking of service vehicles area 490 sq ft	1	160000	2	Building Material
11056	Air ticket for Cdr (Rtd) Omer Zia Siddiqui PN, ID: 14-23-01-2587, Grade: SRO\r\nMeezan Account: 01330107377634 (0133)	2	350000	3	Travel/Boarding/Lodging
11101	Servo connector bracket mount	37	350000	2	Other
11054	TA/DA for Cdr (Rtd) Omer Zia Siddiqui PN, ID: 14-23-01-2587, Grade: SRO\r\nMeezan Account: 01330107377634 (0133)	1	350000	3	Travel/Boarding/Lodging
11138	Load cell	5	350000	2	Other
11120	MS Stock	2	350000	2	Raw Material
11135	Synthetic oil	2	350000	2	Chemical
11110	CXCH18T4Z1P1 Male cable end 4pin connector	1	200000	2	Other
11111	CXCH18F7K1P1 Female chassis 7pin connector	2	200000	2	Other
11112	CXCH18T7Z1P1 Male cable end 7pin connector	3	200000	2	Other
11121	Fasteners	3	350000	2	Other
11122	Wire cut	4	350000	3	Other
11123	P20 material	5	350000	2	Raw Material
11125	Tappered Bushes	6	350000	2	Other
11126	LED Monitor 24inch	7	350000	7	IT Equipment
11127	RAM	8	350000	7	IT Equipment
11128	WD40 440ml	9	350000	2	Chemical
11129	Grub Screw	10	350000	2	Other
11133	Rent a Car (Toyota Corola GLI/XLI) for 15 days	1	200000	3	Transport
10586	Complete color work (oil paint) of 02 x pota cabins with triple coat. Cost includes provision of items, labor and transportation	1	160000	3	Building Repairs & Maintenance
9724	Purchase of fiber glass sheet (1 mm) for parking shed of service vehicles size 290 sq ft.	1	160000	2	Raw Material
10589	DAP for R&D Wing garden	3	160000	2	Chemical
10602	Replacement of broken window glass in public washroom of R&D Wing at ground floor	1	160000	7	Building Material
10590	Prayer mats for Female Common Room (FCR)	1	160000	2	Other
10592	AC repair and complete service in FCR	2	160000	3	Building Repairs & Maintenance
10601	Paint work in public washrooms of R&D Wing and on one wall of office of DD EW	1	160000	3	Building Repairs & Maintenance
10615	Lugs for cable joint	4	160000	7	Parts
10598	Portraits of CNS and DG NRDI	2	160000	7	Office Equipment
10596	Installation of windows blinds in R&D Wing pantry	2	160000	2	Other
10610	Repair of R&D Wing secretariat shredder machine	3	160000	3	Equipment Repairs & Maintenance
10593	AC repair (04 Ton standing AC) in R&D Wing secretariat with complete service of indoor and outdoor units	1	160000	3	Building Repairs & Maintenance
10594	AC repair (01 Ton) in R&D Wing TpT porta cabin	2	160000	3	Building Repairs & Maintenance
11033	50 MBPS (Shared Connection)	1	160000	3	Other
11034	Router 5G (03) 10000/ per	2	160000	7	IT Equipment
11035	Installation with GPON Fiber	3	160000	3	Equipment Installation
11066	Wire mesh 10mm	3	350000	2	Other
11067	Heat shrink 2mm	4	350000	2	Other
11068	Heat shrink 3mm	5	350000	2	Other
11069	Heat shrink 4mm	6	350000	2	Other
11062	Drill EDM	5	350000	2	Other
11063	Vacuum Bagging 100m	6	350000	2	Other
11070	Heat shrink 5mm	7	350000	2	Other
11071	Heat shrink 10mm	8	350000	2	Other
11072	Heat shrink 15mm	9	350000	2	Other
11073	Heat shrink 20mm	10	350000	2	Other
11154	Jazz internet NDT lab, RF lab, INTELL lab	21	350000	3	Other
11153	PLA-F spools	20	350000	2	Raw Material
11152	Connector soldering Jig	19	350000	2	Other
11151	JR crimping tool	18	350000	7	Tools
11150	Slider pots	17	350000	2	Other
11149	Engine starter panel	16	350000	7	Parts
11142	LM3900n Amplifier IC	9	350000	2	Other
11131	PCB (FR4, Single Sided, Size: 12" x 12")	12	350000	2	Other
11130	Nitrile gloves	11	350000	2	Other
11156	Internet SPF	23	350000	3	Other
11155	Printer cartridge	22	350000	2	Other
11158	Powder Coating	2	350000	3	Other
11159	DLE 170M	1	350000	7	Parts
11160	DLE 170 Spare Ignition Module	2	350000	2	Other
11161	Spark Plugs	3	350000	2	Other
11162	DLE 170 Fuel Filter	4	350000	2	Other
11141	LCD Display	8	350000	2	Other
11140	Buck Converter XL-4016	7	350000	2	Other
11139	IBT2 motor drive	6	350000	7	Parts
11137	Wooden propeller	4	350000	7	Parts
11074	Storage boxes	11	350000	7	Other
11075	8dBi Antenna SMA male female connector	12	350000	2	Other
11076	AC-DC Single output enclosed power supply	13	350000	7	IT Equipment
11077	Motor GARTT 5210	14	350000	7	Parts
11147	Airspeed sensor	14	350000	2	Other
11148	JR connectors	15	350000	2	Other
11146	STM32 Discovery Board	13	350000	2	Other
11145	UART to USB Converter	12	350000	2	Other
11144	RS232 to UART Converter	11	350000	2	Other
11143	AM26LS32 Differential Receiver IC	10	350000	2	Other
11136	Shaft balancing	3	350000	3	Other
11134	Loctite 243	1	350000	2	Chemical
11132	PCB (FR4, Double Sided, Size: 12" x 12")	13	350000	2	Other
11078	Hobby Wing ESC	15	350000	2	Other
11079	1655 CF props	16	350000	2	Other
11080	CF tubes	17	350000	2	Other
11081	Aluminum Alloy Control Servo Arm Horn	18	350000	2	Other
11082	Tungsten Carbide Rotary Point Burr tool set	19	350000	7	Tools
11083	Peel ply roll 100 meters	20	350000	2	Other
11084	8-19mm Wire Tube Machinery Cleaning Brush Rust Cleaner	21	350000	2	Other
11085	Damaged Nut Bolt Remover set	22	350000	7	Tools
11086	20mm Long Nib Head Markers For Metal Perforating	23	350000	2	Other
11087	SS grub screws, counter sunk, bolt and washers M1.6xM8	24	350000	2	Other
11088	Multi-Spline Screw Extractor Set Hex Head Bolt Remover	25	350000	7	Tools
11089	Carbon Fiber Solid control Rod	26	350000	2	Other
11090	Automatic Centre Punch	27	350000	7	Tools
11092	Metal Iron Clevis Pull Rod Connector Clamp Horn M2 - M3	28	350000	2	Other
11093	M3-M12 2D Fastening Thread repair	29	350000	2	Other
11094	M2 M2.5 M3 Rod End RC Ball Joint Link With Screw Set	30	350000	2	Other
11095	High Quality Ball Link Plier	31	350000	7	Tools
11096	Metal Servo Arm Linkage Joint	32	350000	2	Other
11097	2 Flute Tungsten solid carbide Tapered Ball Nose End Mills	33	350000	2	Other
11098	Carbon fiber hollow rod 12mm	34	350000	2	Other
11099	1 Pair Plastic RC Servo Covering Protective Cover	35	350000	2	Other
11100	Stainless Steel Solid Ball Bearing	36	350000	2	Other
11106	Crimping tool with aluminum sleeves	42	350000	7	Tools
11107	Mega phone	43	350000	7	Other
11108	Screws, locks and grubs	44	350000	2	Other
11109	rubber seal	45	350000	2	Other
11195	Mini Soft Light	2	250000	2	Other
11196	Battery for DSLR Camera	3	250000	2	Other
11198	Gorilla Tripod Stand	4	250000	7	Other
11201	Monopod/ Tripod Stand	5	250000	7	Other
11202	USB-C to Mini USB Cable	6	250000	2	Other
11203	Remote Shutter Control Cable for DSLR Camera	7	250000	2	Other
11215	8.7 HP Plug and Play Spindle / VFD System	1	350000	7	Parts
11216	ER32 Collet and Nut Set, Imperial	2	350000	7	Tools
11210	Spray Paint	6	250000	2	Other
11213	Fence Welding & Grinding	7	250000	2	Other
11214	Mobile Rack 21 x Boxes	8	250000	7	Furniture
11194	Gimbal Stabilizer for DSLR Camera	1	250000	7	Other
11205	PC Casing (19 x 7 Inches)	1	250000	7	Other
11206	PC Casing (19 x 8 Inches)	2	250000	7	Other
11207	Storage Server Rack (20 x 23 Inches)	3	250000	7	Other
11208	Pad Lock Long Hook	4	250000	7	Other
11209	Pad Lock Short Hook	5	250000	7	Other
11163	HDMI female connector	1	350000	2	Other
11164	HDMI right angle connector	2	350000	2	Other
11165	Fuel	3	350000	2	POL
11166	Powder coating	4	350000	3	Other
11167	RS232 connector	5	350000	2	Other
11242	Fabrication and installation of canopy including base frame structure,reinforcement members,glass fibre top and guide rail mechanism.	1	300000	7	Parts
11220	Adaptor Tee Bnc F/M/F	1	350000	2	Other
11221	Conn Terminator Adapt Bnc 600 Ohm	2	350000	2	Other
11222	Conn Terminator Plug Bnc 75 Ohm	3	350000	2	Other
11223	Conn Adapt Plug-Plug Bnc 50 Ohm	4	350000	2	Other
11224	Conn Adapt Jack-Jack Bnc 75 Ohm	5	350000	2	Other
11225	Conn Adapt Plug to Jack Bnc	6	350000	2	Other
11226	Rf Filter High Pass 1.7Ghz Inline	7	350000	2	Other
11227	Filter Saw 1.575Ghz Smd	8	350000	2	Other
11228	Filter Saw 1.575Ghz 5Smd	9	350000	2	Other
11229	Connectors	10	350000	2	Other
11230	GPS Ublox Neo M8N	11	350000	2	Other
11231	DC Wire	12	350000	2	Other
11232	UART To USB	13	350000	2	Other
11233	Powder Coating	14	350000	3	Fabrication
11234	PVC Pipe 1-1/2 " 20 ft	15	350000	2	Other
11235	Teflon Solid Rod 40mm 12000mm	16	350000	2	Other
11236	PVC T 1-1/2 "	17	350000	2	Other
11237	Nylon H Stock 1000mm	18	350000	2	Other
11238	End Section Cap 2000mm	19	350000	2	Other
11239	PVC Solution	20	350000	2	Chemical
11168	Microwave communication polished Zro2 Zirconium Oxide Substrate Zirconia Ceramic sheet	6	350000	2	Raw Material
11171	Wire tagging	7	350000	2	Other
11174	Mouse	8	350000	7	IT Equipment
11175	Tungsten wire 0.02mm*950m	9	350000	2	Other
11176	Soldering wire 400gram 70/30	10	350000	2	Other
11178	Mash 5mm, 6mm	11	350000	2	Other
11179	Heat shrink 8mm, 12mm	12	350000	2	Other
11180	Double side tape 1"	13	350000	2	Other
11241	Fabrication and installation of customized MS Canopy (including base frame structure, Reinforcement members, Glass Fiber top and guide rail mechanism)	1	300000	3	Fabrication
11181	wire lugs 2.5mm	14	350000	2	Other
11182	wire lugs 1.5mm	15	350000	2	Other
11184	wire lugs 1mm	16	350000	2	Other
11186	wire lugs 0.5mm	17	350000	2	Other
11187	silicon wire 14awg	18	350000	2	Other
11217	CNC Router Bit Set	3	350000	7	Tools
11218	ER32 Collet and Nut Set, Metric	4	350000	7	Tools
11219	8.7 HP Spindle Tramming Mount	5	350000	7	Parts
11245	Procurement of Lab chairs (Interwood)	1	160000	7	Other
11246	Procurment/Replacement of Store room Door and lock	2	160000	7	Other
11240	Brush Cutter	1	160000	7	Tools
11188	silicon wire 18awg	19	350000	2	Other
11189	silicon wire 20awg	20	350000	2	Other
11190	silicon wire 24awg	21	350000	2	Other
2604	MS Structure Fabrication and erection of 2 ton overhead crane. Overhead structure height 5 mtr, length 34m, and complete installation in all aspect (Material & Labour)	1	300000	7	Machinery / Equipment
2837	MS Monkey Ladder For PEB Shed	3	300000	7	Other
3052	Installation of Glass Pannels on PEB Walls	4	300000	7	Other
2086	Pressure Sensor 30 bar	1	300000	7	Parts
2087	ALTI meter ECO Sounder	2	300000	7	Parts
2088	Under Water Light (1500 Lumens)	3	300000	7	Parts
2071	PVC Pipe 6'' dia	1	300000	7	Parts
2073	PVC Elbow 6'' dia	2	300000	7	Parts
2074	PVC Socket 6'' dia	3	300000	7	Parts
2076	Joint Solution	4	300000	2	Chemicals
2077	Ring 24'' dia	5	300000	7	Parts
2078	Ring 36'' dia	6	300000	7	Parts
2079	Ring cover 24''	7	300000	7	Parts
2055	16.8v 17 AH Li-ion Battery pack for ROV	1	300000	2	Parts
2056	Camera Module for underwater inspection (Driver Aide)	2	300000	7	Parts
2057	IP rated Transportation container for portable ROV sytem	3	300000	7	Parts
2058	Gazeebo Tent (Size 10x10)	4	300000	2	Other
1860	Design and construction of Pre Engineering Building (34m x 20m)	1	300000	7	Other
1861	ROV Assembly Area Building & Pump Room	2	300000	7	Other
1528	3500 PSI Cylindrical Readymix	1	300000	2	Other
1529	1:4:8 Cylindrical Ready mix for Lean	2	300000	2	Other
11257	PYB15-Q24-S12-H-T DC/DC CONVERTER 12V 15W	1	200000	2	Other
11258	01609.0-01 Enclosure Heating Element, 10W, 120°C, 12 ? 36 V ac/dc	2	200000	2	Other
11259	SKT011409NC-CP Norm. Closed Thermostat Fahrenheit	3	200000	2	Other
11260	MiniPlex-BUF MiniPlex-BUF is an advanced NMEA buffer/splitter	4	200000	2	Other
11261	CXCH18F4K1P1 Female chassis 4pin connector	5	200000	2	Other
11256	Overall Design & Manufacturing of Mechanical Enclosure	1	200000	7	Other
11281	Toyota Corola GLI/XLI	1	200000	3	Transport
11273	Pressure Sensor (300m depth)	1	300000	7	Test / Measuring Equipment
11274	Underwater Lights	2	300000	7	Machinery / Equipment
11275	Control Integration Board	3	300000	7	Parts
11276	Lead Acid Battery pack	4	300000	2	Other
11277	Controller Joystick	5	300000	7	Parts
11278	Electronics Tray	6	300000	7	Parts
11250	88 GSM carbon fibre	21	350000	2	Raw Material
11279	Ballast Weight	7	300000	7	Parts
11280	Battery Charger	8	300000	7	Parts
11251	Rohacell	22	350000	2	Raw Material
11283	C1608X5R1A226M080AC    Cap: 22 µF Total Derated Cap: 7.2 µF VDC: 10 V ESR: 3.71 m? Package: 0603  TDK	2	200000	2	Other
9502	Ceiling lights purchased for the office of Dir SoSE	2	160000	2	Other
9517	Charges for installation of commode from officer's washroom and installation in staff washroom	15	160000	3	Building Repairs & Maintenance
11262	826646-2    PCB HEADER                  3.3V  GND	1	200000	2	Other
11263	826646-5    PCB HEADER TCK TMS TDI TDO RST, 5V RX TX GND RST	2	200000	2	Other
11264	1915233     TERM BLOCK HDR 4POS VERT 5.08MM	3	200000	2	Other
11265	1805327     TERM BLOCK PLUG 4POS STR 5.08MM	4	200000	2	Other
11266	Connction Cable roll	5	200000	2	Other
11252	GPU Repairing	1	250000	3	Equipment Repairs & Maintenance
11253	GPU & Mother board Repairing	2	250000	3	Equipment Repairs & Maintenance
11503	DC/DC module	10	200000	2	Other
11504	Solder cusion wire	11	200000	2	Other
11505	Connection Pins 1000	12	200000	2	Other
11506	Connection strip 100A	13	200000	2	Other
11255	GPU & Power Supply Repairing	3	250000	3	Equipment Repairs & Maintenance
11267	Connection Pins	6	200000	2	Other
11268	PCB Fibrication	7	200000	2	Other
11269	Data cable 10 core	8	200000	2	Other
11271	Power supply  cable	9	200000	2	Other
11272	Accessories	10	200000	2	Other
11325	Fasteners	22	350000	2	Other
11326	Arduino uno	23	350000	2	Other
11327	DS18B20 Temperature Sensor	24	350000	2	Other
11328	LCD 20X4	25	350000	2	Other
11329	LCD I2C	26	350000	2	Other
11330	10AWG wire (5 meters)	27	350000	2	Other
11331	Bullet connector Male and Female (6mm)	28	350000	2	Other
11332	JST-HX Male and Female (3Pin)	29	350000	2	Other
11336	Male header	30	350000	2	Other
11337	Female header	31	350000	2	Other
11300	Overall Design & manufactring of Customized onboard Cable Ducting Solution (Junction Box Included)	1	200000	7	Other
11282	 06033C104KAT2A   Cap: 100 nF Total Derated Cap: 100 nF VDC: 25 V ESR: 50 m? Package: 0603  AVX	1	200000	2	Other
11284	RC0201FR-0710KL       Resistance: 10 k? Tolerance: 1.0% Power: 50 mW Package: 0603  Yageo	3	200000	2	Other
11285	 XAL4030-332MEB        L: 3.3 µH DCR: 26 m? IDC: 5.5 A   Coilcraft	4	200000	2	Other
11286	 TPS563208DDCR         Regulator IC  Texas Instruments	5	200000	2	Other
11287	C2012X5R1V106K085AC  Cap: 10 µF Total Derated Cap: 2.5 µF VDC: 35 V ESR: 2.82 m? Package: 0805  TDK	6	200000	2	Other
11288	RC0201FR-7D68K1L       Resistance: 68.1 k? Tolerance: 1.0% Power: 50 mW Package: 0603  Yageo	7	200000	2	Other
11339	Vero board	33	350000	2	Other
11292	Core i5 8th Generation, 16 GB RAM, 1TB SSD, Graphic Card 2GB	1	160000	7	IT Equipment
11299	Monitor HP 23"	2	160000	7	IT Equipment
11289	Control Cable 24 core shield  1100meter	1	200000	2	Other
11290	Control Cable 6 core shield  2000meter	2	200000	2	Other
11291	Connection Pins PKT	3	200000	2	Other
11338	GX16 three pin connector	32	350000	2	Other
11302	Nylon Sheet 50 mm	1	350000	2	Raw Material
11303	Magic Depoxy	2	350000	2	Chemical
11304	End Cap	3	350000	2	Other
11305	24AWG Blue Wire Roll	4	350000	2	Raw Material
11307	4mm Banana Plug male	5	350000	2	Other
11308	DB9 male	6	350000	2	Other
11309	DB9 female	7	350000	2	Other
11310	DB9 connector case	8	350000	2	Other
11311	DB15 male	9	350000	2	Other
11312	DB15 female	10	350000	2	Other
11314	DB15 connector case	11	350000	2	Other
11315	RCA male	12	350000	2	Other
11316	RJ45 male	13	350000	2	Other
11317	JR futaba female	14	350000	2	Other
11318	JR futaba male	15	350000	2	Other
11319	400g soldring wire	16	350000	2	Other
11320	4mm cable sleeve black 10m	17	350000	2	Other
11340	die for 1.2mm gap	34	350000	2	Other
11341	die for 0.8mm gap	35	350000	2	Other
11342	coil	36	350000	2	Other
11343	Electric brake pneumatic wheel	37	350000	7	Parts
11344	Gas struts 1100N 400mm	38	350000	2	Other
11345	Gas struts 1100N 500mm	39	350000	2	Other
11346	Gas struts 1100N 600mm	40	350000	2	Other
11347	1 Pair Rubber wheel tire w/ Aluminum Hub 4.5"	41	350000	7	Parts
11366	RES 1K OHM 5% 1/10W 0402	13	350000	2	Other
11348	1 Pair Rubber wheel tire w/ Aluminum Hub 5.0"	42	350000	7	Parts
11321	USB to RS485/422 module	18	350000	2	Other
11322	Component Box XL	19	350000	7	Other
11323	Component Box L	20	350000	7	Office Equipment
11324	Wire mesh 2mm	21	350000	2	Raw Material
11349	Cross recess flush head screw	1	350000	2	Other
11350	Retaining washers	2	350000	2	Other
11351	Receptacle	3	350000	2	Other
11352	Application tools	4	350000	2	Other
11353	Cleco fasterner premium kit	5	350000	2	Other
11354	Al 1000 (285mmx 285mmx 50mm)	1	350000	2	Raw Material
11355	Al 1000 (305mmx 305mmx 50mm)	2	350000	2	Raw Material
11356	Al 1000 (326mmx 326mmx 50mm)	3	350000	2	Raw Material
11357	Cutting charges	4	350000	3	Equipment Modification
11362	SMT LNA, 700 - 3000 MHZ, 50	9	350000	2	Other
11363	FIXED IND 5.6NH 300MA 270MOHM SM	10	350000	2	Other
11364	FIXED IND 15NH 250MA 600MOHM SMD	11	350000	2	Other
11365	FIXED IND 82NH 100MA 2.5 OHM SMD	12	350000	2	Other
11400	471-060 Analog Discovery 3 Pro Bundle	1	200000	7	Other
11397	HP Laser Printer (Refurbish)	1	200000	7	IT Equipment
11414	Portable DVD RW	2	200000	7	Parts
11386	AD630 Balanced Modulator/Demodulator	1	200000	2	Parts
11415	HDD IDE	3	200000	2	Parts
11401	Aluminum Sheet 5mm	1	350000	2	Raw Material
11402	Aluminum Sheet 10mm	2	350000	2	Raw Material
11403	Laser Cutting	3	350000	3	Equipment Modification
10374	Labor Charges for Installation of Piping and Valves (Including all accessories)	1	200000	3	Equipment Installation
10375	Material Charges	2	200000	3	Equipment Installation
11358	Mode converter	5	350000	2	Raw Material
11359	PCB	6	350000	2	Other
11387	False Ceiling Lights (~ 40 watt)	1	160000	7	Parts
11388	Lab Chairs (Interwood)	2	160000	7	Furniture
11406	Repair/ extension of partition of walls in alleyways at ground floor of R&D Wing near secretariat and power research lab. Cost includes provision of material, labor charges and transportation	1	160000	3	Furniture
11418	PCB Fibrication for FCC	5	200000	2	Parts
11410	Repair/ maintenance of windows blinds in R&D Wing conference room, secretariat, offices of Dir Propulsion, SME NRDI, Dir SoSE, ET and power research labs	2	160000	3	Equipment Repairs & Maintenance
11360	Laptop charger	7	350000	7	IT Equipment
11361	CONN ADAPT JACK-JACK N 50 OHM	8	350000	2	Other
11367	CAP CER 82PF 16V C0G/NP0 0402	14	350000	2	Other
11368	CAP CER 0.1UF 16V X7R 0402	15	350000	2	Other
11369	CAP CER 1000PF 50V X7R 0402	16	350000	2	Other
11370	CAP CER 56PF 50V C0G/NP0 0402	17	350000	2	Other
11371	CAP CER 68PF 50V C0G/NP0 0402	18	350000	2	Other
11372	N type female terminal connector	19	350000	2	Other
11417	EMI Filters	4	200000	2	Parts
11373	Phosphor Bronze rod	20	350000	2	Raw Material
11374	EDM charges	21	350000	3	Equipment Modification
11375	Chrome coating	22	350000	3	Other
11376	USB	23	350000	7	IT Equipment
11377	Fuel	24	350000	2	POL
11378	Jazz Internet charges RF and INTELL lab	25	350000	3	Other
11379	Internet & cable charges SPF	26	350000	3	Other
11411	Repair of seepage on walls at ground floor alleyways of R&D Wing and color work on repaired walls with provision of material	3	160000	3	Building Repairs & Maintenance
11392	CXCH18F4K1P1 Female chassis 4pin connector	1	200000	2	Other
11412	Repair of shredder machine of R&D Wing seceretariat, SoSE, ET divisions and SVDC	4	160000	3	Equipment Repairs & Maintenance
11421	54642D Oscilloscope with Calibration (Refurbished)	1	200000	7	Test / Measuring Equipment
11413	Repair of microwave oven of R&D Wing pantry	5	160000	3	Equipment Repairs & Maintenance
11393	CXCH18T4Z1P1 Male cable end 4pin connector	2	200000	2	Other
11394	CXCH18F7K1P1 Female chassis 7pin connector	3	200000	2	Other
11395	CXCH18T7Z1P1 Male cable end 7pin connector	4	200000	2	Other
11396	GEN4-ULCD-70DT MOD LCD 7" 800X420 4D SYSTEM	5	200000	2	Other
11389	False Ceiling Fan	3	160000	7	Appliance
11391	Dulux easy care Interior Paint 16 Liter (for lab with accessories)	4	160000	2	POL
11424	Aluminum windows repair of conference room, power research lab, seceretariat, porta cabins and office of SME NRDI	3	160000	3	Other
11425	Hiring of labor for bush cutting at the back side of R&D Wing block, porta cabins and SVDC	4	160000	3	Other
11404	Roller Bending	4	350000	3	Equipment Modification
11405	Laser Welding	5	350000	3	Fabrication
11420	CIMR-JT2A Power Supply 400Hz	6	200000	2	Parts
11434	Provision of soft soil trucks for associted gardens of R&D Wing	5	160000	2	Raw Material
11435	Provision of 50 Kg bags of SONA DAP for associaed gardens of R&D Wing	6	160000	2	Raw Material
11437	Repair of  aluminum doors of main entrance of R&D Wing and R&D Wing secretariat	7	160000	3	Other
11433	Replacement of defective energy savers(18W bulb) in alleways of ground and first floor	4	160000	7	Parts
11438	Replacement of commode tank fitting in washroom of FCR and washrooms in the offices of MD R&D, Dir ET, and Dir SoSE	8	160000	7	Other
11439	Repair of R&D Wing conference room chairs	9	160000	3	Other
11441	Polish of R&D Wing conference room table	10	160000	3	Other
11442	Provision of washroom accessories (complete set) for the washroom in the office of Director Propulsion	11	160000	7	Other
11449	JST HX connector 6 pin	5	350000	2	Other
11450	JST HX connector 3 pin	6	350000	2	Other
11451	Push button	7	350000	2	Other
11452	Resistor 1 k ohm	8	350000	2	Other
11443	Replacement of different current rating circuit breakers (32A, 16A, 8A) for power distribution board in associated transformer room of R&D Wing and second DB at the roof	12	160000	7	Parts
11422	Polish work on cabinets in the pantry	1	160000	3	Other
11423	Repair of wooden door and choukhat of power research lab, SINC lab, offic of Dir Systems and pantry	2	160000	3	Other
11426	Seepage repair and broken tiles replacemet in the fountain of R&D Wing garden with material and labor charges	5	160000	3	Building Repairs & Maintenance
11427	Paint work on R&D Wing porta cabins with material, labor and transportation charges	6	160000	3	Building Repairs & Maintenance
11428	Paint work in the officers washroom at first floor and staff washroom at ground floor of R&D Wing	7	160000	3	Building Repairs & Maintenance
11429	Seepage removal, floor cementing with plastir of paris, broken or dilipedated tiles replacement, replacement of faulty taps, repaining of commode drain pipe in the washrooms in offices of MD (R&D), SME NRDI, Director SoSE, Director ET, FCR and public washroom at Ground floor of R&D Wing	8	160000	3	Building Repairs & Maintenance
11430	Repair of 04 x Ton standing AC in R&D Wing conference room	1	160000	3	Equipment Repairs & Maintenance
11431	Repair of 01 x Ton ACs in R&D Wing Admin porta cabin and 03 x Acs in power research lab	2	160000	3	Equipment Repairs & Maintenance
11432	Replacement of defevtice LED panel (square 2'x2') in R&D Wing conference room	3	160000	7	Parts
11444	SD card module	1	350000	2	Other
11446	SD card 4 GB	2	350000	2	Other
11447	SD card reader	3	350000	2	Other
11448	IC base 28 pin ATmega328p	4	350000	2	Other
11453	Resistor 10 k ohm	9	350000	2	Other
11454	Transistor 2N2222	10	350000	2	Other
11455	Capacitor 0.1 uF	11	350000	2	Other
11456	GX12 connector 3 Pin	12	350000	2	Other
11457	Charging module TP4056	13	350000	2	Other
11458	XT60	14	350000	2	Other
11510	TM4C123GH6PMI	1	200000	2	Parts
11507	Connection strip 50A	14	200000	2	Other
11511	MAX232ACSE+ IC RS-232 DRVR/RCVR 16-SOIC	2	200000	2	Parts
11494	Volum with Knobs	1	200000	2	Other
11495	PCB board large	2	200000	2	Parts
11496	DC connection Pins	3	200000	2	Other
11497	Digital meters volts/ampares	4	200000	2	Other
11498	12VDC Adapter 3A	5	200000	2	Other
11499	Box for pressure sensors	6	200000	2	Other
11500	Cables 6pin wire 8m	7	200000	2	Other
11501	Conectors male/femal	8	200000	2	Other
11509	Heating Sleaves	16	200000	2	Other
11502	Connectors 8pin metal	9	200000	2	Other
11459	PSU DC adaptor 5V 6A	15	350000	2	Other
11460	JST HX connector 2 pin	16	350000	2	Other
11461	Al Stock  (345mm x 345mm x 50mm)	17	350000	2	Raw Material
11462	Al Stock (370mm x 370mm x 50mm)	18	350000	2	Raw Material
11463	Al Stock (390mm x 390mm x 50mm)	19	350000	2	Raw Material
11512	LM2596T-5.0/LF03 IC REG BUCK 5V 3A	3	200000	2	Parts
11480	Distemper (Drum)	1	350000	2	Chemical
11513	LM2596T-3.3/NOPB IC REG BUCK 3.3V 3A	4	200000	2	Parts
11514	LFXTAL003240Bulk  CRYSTAL 16.0000MHZ 30PF TH	5	200000	2	Parts
11515	GRM31CR60J227ME11L  Multilayer Ceramic Capacitors MLCC - SMD/SMT 220UF 6.3V 20% 1206	6	200000	2	Parts
11516	12063C104JAT2A  Multilayer Ceramic Capacitors MLCC - SMD/SMT 25V 0.1uF X7R 1206 5%	7	200000	2	Parts
11481	Distemper (Bucket)	2	350000	2	Chemical
11482	Oil Paint	3	350000	2	Chemical
11483	Paint putty	4	350000	2	Chemical
11484	Thinner	5	350000	2	Chemical
11485	Kerosene	6	350000	2	Chemical
11486	Paint Rollers	7	350000	2	Other
11487	Paint brush	8	350000	2	Other
11536	TA/DA for Muhammad Ahmed Khan, ID: 14-21-04-0903, Grade: RO\r\nMeezan Account: 0105416165 (9991)	1	350000	3	Travel/Boarding/Lodging
11537	TA/DA for Irshad Hussain Nadeem, ID: 14-20-06-2173, Grade: SRT\r\nMeezan Account: 0129-0104503403 (0129)	1	350000	3	Travel/Boarding/Lodging
11538	TA/DA for Muhammad Iftikhar Ahmed, ID: 14-20-08-6977, Grade: RT\r\nMeezan Account: 01010104120469 (0101)	2	350000	3	Travel/Boarding/Lodging
11464	Al Stock (410mm x 410mm x 50mm)	20	350000	2	Raw Material
11465	Cutting Charges	21	350000	3	Equipment Modification
11578	3 Phase AVR Servo Motor Control	1	350000	7	Machinery / Equipment
11579	Water Pipe	2	350000	2	Other
11539	Filter Element H70E	1	350000	2	Other
11540	Filter Element P70E	2	350000	2	Other
11541	Suction filter	3	350000	2	Other
11542	Oil filter	4	350000	2	Other
11543	Air condition filter	5	350000	2	Other
11466	Servo 25 kg standard	22	350000	2	Other
11467	Propeller	23	350000	2	Other
11468	Filament PLA	24	350000	2	Other
11469	Filament ABS	25	350000	2	Other
11470	Asus Prime B550M-A Wifi ll	26	350000	2	Other
11471	Acrylic frame 30x20x3	27	350000	2	Other
11472	Aluminum Bronze copper TIG welding wire	28	350000	2	Other
11473	Mouse	29	350000	7	IT Equipment
11474	NBR Oring Seal	30	350000	2	Other
11475	Mega 2560 Pro controller	31	350000	2	Other
11580	Green Board	3	350000	7	Other
11581	White Board	4	350000	7	Other
11582	Printers Refill	5	350000	3	Other
11583	Jazz Internet NDT & RF Lab	6	350000	3	Other
11476	Mini 360 DC supply	32	350000	2	Other
11477	Single side copper PCB board	33	350000	2	Other
11569	Underwater Camera	1	300000	7	Machinery / Equipment
11544	Ni plating setup with chemicals	6	350000	7	Other
11545	IRCBM hydrogen firing charges	7	350000	3	Other
11546	Graphite/Copper Stock for EDM electrode	8	350000	2	Raw Material
11570	Control Integration Board	2	300000	7	Parts
11517	Toyota Corola GLI/XLI	1	200000	3	Transport
11518	Toyota Vigo	2	200000	3	Transport
11520	Toyota Hiace	3	200000	3	Transport
11556	Lunch on 14 Nov 23	1	200000	3	Meals/Refreshments
11669	Elfy	21	350000	2	Other
11558	Lunch on 16 Nov 23	2	200000	3	Meals/Refreshments
11559	Lunch on 29 Nov 23	3	200000	3	Meals/Refreshments
11560	Lunch on 13 Dec 23	4	200000	3	Meals/Refreshments
11670	Nitrile Gloves	22	350000	2	Other
11561	Lunch on 01 Jan 24	5	200000	3	Meals/Refreshments
11562	Lunch on 11 Jan 24	6	200000	3	Meals/Refreshments
11671	Cotton Rage	23	350000	2	Other
11672	Cement block 4"	24	350000	2	Other
11564	Lunch on 17 Jan 24	7	200000	3	Meals/Refreshments
11673	Bajri (Gravel)	25	350000	2	Other
11674	Cement	26	350000	2	Other
11572	Ballast Weight	3	300000	7	Parts
11573	Underwater Lights	4	300000	7	Machinery / Equipment
11574	Battery Charger	5	300000	7	Parts
11575	Electronics Tray	6	300000	7	Parts
11576	Controller Joystick	7	300000	7	Parts
11577	Pressure Sensor (300m depth)	8	300000	7	Test / Measuring Equipment
11547	EDM Charges	9	350000	3	Other
11548	Copper stock for collector 4.5 kgs	10	350000	2	Raw Material
11675	Plastic sheet	27	350000	2	Other
11591	D-Link Networking Switch 24 ports Managable	1	200000	7	Parts
11592	D-Link Networking Switch 16 ports Managable	2	200000	7	Parts
11593	D-Link Networking Switch 8 ports Managable	3	200000	7	Parts
11594	Patch Pannel 24 port   3M / Corning CAT6 Cable	4	200000	7	Parts
11585	MC1496, MC1496B Balanced Modulators/ Demodulators	2	200000	2	Parts
11595	Patch Pannel 16 port 3M / Corning	5	200000	7	Parts
11596	Cable Manager 3M / Corning	6	200000	2	Parts
11597	6U Wall Mount  Network Rack Cabinet	7	200000	7	Parts
11598	Patch Cord 3M / Corning/ Corn Tech 1m	8	200000	2	Parts
11586	MCP4725 Data Conversion IC Development Tools	3	200000	2	Parts
11587	AD9850  DDS Signal Generator Module	4	200000	2	Parts
11589	EVAL-AD3552R-FMC2Z DAC Kit	5	200000	2	Parts
11590	EVAL-SDP-CH1Z Evaluation Board	6	200000	2	Parts
11599	Connectors RJ45	10	200000	2	Parts
11600	I/Os 3M / Corning/ Corn Tech	11	200000	2	Parts
11611	Zybo Z7 20  410-351-20	1	200000	7	Parts
11565	Lunch on 02 Feb 24	8	200000	3	Meals/Refreshments
11566	Lunch on 03 Feb 24	9	200000	3	Meals/Refreshments
11567	Lunch on 21 Feb 24	10	200000	3	Meals/Refreshments
11568	Lunch on 22 Feb 24	11	200000	3	Meals/Refreshments
11623	Allen Bolt M10 X 90mm	7	350000	2	Other
11624	Round Head Allen Boot M4 X 15mm	8	350000	2	Other
11625	Cut screw 1 1/2 "	9	350000	2	Other
11626	Cut screw 1 "	10	350000	2	Other
11627	T Roll	11	350000	2	Other
11628	Flexible pipe 4"	12	350000	2	Other
11629	Jubliee clamp 4"	13	350000	2	Other
11630	4 core 16mm Power Cable	14	350000	2	Other
11631	32 A TP 4 Pole Breaker	15	350000	2	Other
11632	Panel box 15" X 18"	16	350000	2	Other
11601	PDUs	12	200000	2	Parts
11602	3M Face Plate 01 Hole . 3M / Corning/ Corn Tech	13	200000	2	Parts
11612	Analog discovery 3 Pro Bundle 471-060	2	200000	7	Parts
11584	Internet & TV Cable Charges at SPF	7	350000	3	Other
11621	Round Head Allen Bolt M8 X 25mm	5	350000	2	Other
11613	Pmod DA3 One 16-bit D/A Output 410-241	3	200000	2	Parts
11614	Zybo Academic Pmod Pack  410-011	4	200000	2	Parts
11615	LFTX daughter Board	5	200000	7	Parts
11603	3M Face Plate 02 Hole. 3M / Corning/ Corn Tech	14	200000	2	Parts
11604	Back Box	15	200000	2	Parts
11605	Router TP Link	16	200000	7	Parts
11606	Cable Roll CAT6/FTP   3M / Corning CAT6 Cable	17	200000	2	Parts
11616	LFRX daughterBoard	6	200000	7	Parts
11607	Cable Roll CAT6/UTP   3M / Corning CAT6 Cable	18	200000	2	Parts
11608	Power Cable Roll	19	200000	2	Parts
11617	LMR-400-75-LSZH Coax	1	350000	2	Other
11618	CONN ADAPT PLUG-PLUG N 50 Ohm	2	350000	2	Other
11619	CONN N PLUG STRAIGHT CRIMP	3	350000	2	Other
11620	N-MALE (PLUG) RIGHT ANGLE CRIMP	4	350000	2	Other
11633	Channel (60"x60") and fittings	17	350000	2	Other
11634	Aluminium oil & fuel quick release connectors	18	350000	2	Other
11635	Fuel	19	350000	2	POL
11636	laser cutting and bending charges	20	350000	3	Equipment Modification
11637	Aluminum sheet 750mm x 300mm x 10mm	21	350000	2	Raw Material
11646	Allen key 14mm	2	350000	2	Other
10176	Micro controller based logic Analyzer 24M 8CH with USB interface	1	160000	2	Other
11647	24AWG Blue Wire Roll	3	350000	2	Other
11648	VGA 15 Pin connector (Female)	4	350000	2	Other
11649	1.4mm yellow tags (A-Z)	5	350000	2	Other
11549	Laser welding charges	11	350000	3	Other
11550	RG11 Cable 750 ohm	12	350000	2	Other
11551	N Type connectors	13	350000	2	Other
11552	Fuel	14	350000	2	POL
11478	Double male and female header	34	350000	2	Other
11479	Wire EDM machining	35	350000	3	Equipment Modification
11638	TA/DA for Tabish Hussain, ID: 14-20-10-6921, Grade: SRO\r\nMeezan Account: 0122-0105094054 (0122)	1	350000	3	Travel/Boarding/Lodging
7734	UNI-T UT203 Clamp Meter	1	160000	2	Other
7735	Network Cable Tester & Network Cable Crimp RJ-45	2	160000	2	Other
7723	Rear extra seat with installation	1	160000	2	Other
8176	Fridge Medium Size	1	160000	2	Other
7730	Power extension board	4	160000	2	Other
7632	Replaycement of Door Lock Room 117	2	160000	2	Other
7648	Impact Drill Machine With Bits	14	160000	7	Tools / Test Equipment
8231	Fancy light with four LED bulbs & helper	8	160000	2	Other
8232	LED bulbs	9	160000	2	Other
7651	Adjustable Wrench 12"	17	160000	7	Tools / Test Equipment
7636	Cutter Plier	3	160000	7	Tools / Test Equipment
7639	Hexa Blade	6	160000	7	Tools / Test Equipment
8277	HP Laser Jet Pro (MFP M227SDN)	1	160000	7	Other
8235	Fabrication & Instalation of steel board(30"x96'') with stainless words.	1	160000	7	Other
8168	Miscellaneous toolkit for PAP-104	1	160000	2	Other
7725	Haire Split AC Use Condition	1	160000	7	Office Equipment
9190	Maintenance free battery, 12 V, 7 amps	1	160000	7	Office Equipment
7643	Screw Driver L+	10	160000	7	Tools / Test Equipment
7724	Acrylic Notice Board (2'x3'), 6mm	1	160000	7	Other
7634	Nose Plier 6"	2	160000	7	Tools / Test Equipment
8510	Lighting Arrangements in Lab & Work Station Area Installation Charges	1	160000	7	Other
357	DB panel 1	1	160000	7	Machinery / Equipment
7631	AC Repair Room 115	1	160000	3	Other
8203	Bracket Fan 24	1	160000	3	Other
9194	Motor GARTT ML5008 400 KV	1	160000	7	Parts
10153	Provision of new non revolving office chairs	1	160000	7	Furniture
10220	P1503B multimter test lead kit	5	160000	2	Other
10609	Provision of office table with side rack	1	160000	7	Furniture
10597	Replacement of defective LED lights (size 2x2 ft) in R&D Wing conference room	1	160000	7	Appliance
7772	Making Rubber Stamp Account Officer R&D Wing	3	160000	2	Other
11652	1.4mm Yellow tags (0-9)	6	350000	2	Other
11653	10mm Yellow tags (A-Z)	7	350000	2	Other
11655	Tagging Strip (Black)	8	350000	2	Other
10180	P1503B multimeter test leads kit	5	160000	2	Other
11656	Adjustable circular crimping tool 0.75-6.0mm	9	350000	7	Tools
11657	Cable ties (Small)	10	350000	2	Other
10611	Fabrication of 02 x fiber sheds with specifications with cost includes: (a) M.S pipe 3/3" 18G. (b) M.S pipe 1/2" 18G. (c) Fiber sheets 1.5mm. (d) Rivets. (e) Paint Job (red oxide). (f) wheels 8". (g) welding rods. (h) cutting disks. (j) transportation. (k) labor charges	1	160000	7	Other
10778	Replacement of 01 x old exhaust fan, 02 x power boards, 05 x washroom lights and damaged wires	5	160000	7	Other
10774	Color work at all walls, doors and roof	1	160000	3	Building Modification
9191	One time grant to HQ NRDI Tea Bar	1	160000	3	Grant
1808	PVC Pipe (04 x inch diameter and 08 mm guage)	1	160000	7	Other
2035	Fkangs and valves	2	160000	7	Machinery / Equipment
7671	Instalation Charges	4	160000	3	Equipment Installation
11743	CNC Diamond tools	13	350000	2	Tools
11744	0.3 micron &1 micron Aluminum oxide	14	350000	2	Chemical
10155	Extension of parking shed space at back side of porta cabin of R&D Wing and development of new sitting space adjacent to said paring space. Following is included in qoutation: (a) Extension of shed frame includes installation/ welding of steel pipes, metallic bars for 40x20 ft space. (b) Installatino fixing of green net fiber for shed. (c ) Leveling of the ground. (d) Paving of the floor with dammer (40 x 20ft). (e ) Floor finishing with colored cement. (f) Transportation of men and material. (g) Labor Charges	1	160000	3	Building Modification
11639	TF + Data Board Sheets	1	300000	7	Parts
11640	Electric switch Boards	2	300000	7	Parts
11641	construction of test tank (sand & mix)	3	300000	7	Other
10284	15-20T SOURIAU FR 8D5/ 23M54AA	3	350000	2	Other
10285	16/49 SOURIAU MPS 8D5-13J26SN	4	350000	2	Other
10286	1710 AMPHENOL 62GB-16E14-04SN (214) QSC	5	350000	2	Other
10288	16/24 SOURIAU MPS D38999/26MG35 AN	6	350000	2	Other
10290	(IPU) APH-1734 62GB-16E14-04PN-214	8	350000	2	Other
10291	APH 1722 77820 88465562 JD38999/26MD35BA	9	350000	2	Other
10293	1737 SOURIAU HEI JD38999/26MD35 BN	10	350000	2	Other
10294	15-30 SOU MPS J D38999/26MC35 BN	11	350000	2	Other
10295	Conn circular SKT 53 POS Crimp ST Fiange Mount 53 Terminal 1 Port	12	350000	2	Other
10296	16-42 T SOURIAU FR 8STA1-0609SN	13	350000	2	Other
10297	15/39 SOURIAU mps J D28999/26ME 99 BN	14	350000	2	Other
10298	1526 Amphenol 62GB-16E-14-04SN(044)(214) QSC	15	350000	2	Other
10299	16-19 Souriau MPS J D38999/26MC35AN	16	350000	2	Other
10300	15-30 Souriau MPS J D38999/26MB35BN	17	350000	2	Other
10301	17-10 T SOURIAU FR 8STA6-0406PN	18	350000	2	Other
10302	17-17 T SOURIAU FR 8STA6-0609PN	19	350000	2	Other
10303	17-23 SOURIAU NEI 85102R 16-26S50	20	350000	2	Other
6989	Hacksaw with frame (more than one blade) 	16	200000	2	Tools
6972	Connector N Type Male	16	200000	2	Parts
11643	Toyota Corola GLI/XLI	1	200000	3	Transport
11645	Allen key 12mm	1	350000	2	Other
11644	Internet Package 02 x months 50 MBPS Shared Jan & Feb 24	1	160000	3	Other
11642	TMDSEVM6678  Evaluation Modules  refurbish	1	200000	2	Parts
11690	Tether Wire (neutrally buoyant) 22 AWG\r\n04 Pair Communication	1	300000	7	Parts
11691	Tether Wire (neutrally buoyant) 22 AWG\r\n02 Pair Communication	2	300000	7	Parts
11698	260v/24v DC Battery Management System Module	1	300000	7	Parts
11700	260v/24v DC Control Board (Battery Power Integration Board)	2	300000	7	Parts
11687	Echo Sounder Module (300m depth range)	1	300000	7	Parts
11688	Underwater Lights (300m depth rated)	2	300000	7	Parts
11689	Single axis robotic gripper	3	300000	7	Parts
11701	UPS Batteries 12V 7AH	1	300000	2	Parts
11702	Raspberry Pi 4	2	300000	7	Parts
11703	Arduino UNO	3	300000	7	Parts
11704	Arduino Nano	4	300000	7	Parts
11708	Safety Gloves	5	300000	2	Other
11710	HT Tape	7	300000	2	Other
10557	25 x 100mm Rod PCSN 4000 (Silicon Nitride)	1	350000	2	Raw Material
10558	17-7 PH 300x300x0.2mm	2	350000	2	Raw Material
10559	17-7 PH 300x300x0.4mm	3	350000	2	Raw Material
10560	16004-2RS1, SKF, Single Row Deep Groove Ball Bearing, Metric, Bearing, Bearing steel, Open (No seal), Normal Clearance (Cn)	4	350000	7	Parts
10562	61904-2RS1, SKF, Single Row Deep Groove Ball Bearing, Metric, Bearing, Bearing steel, Rubber seal in both sides, Normal Clearance (Cn)	5	350000	7	Parts
10563	63005-2RS1, SKF, Single Row Deep Groove Ball Bearing, Metric, Bearing, Bearing steel, Rubber seal in both sides, Normal Clearance (Cn)	6	350000	7	Parts
10564	NKS 22 XL, INA, Single row needle roller bearing with machined rings, with flanges, without inner ring, Metric, Bearing, Steel cage	7	350000	7	Parts
10565	K 40X47X20, NKE, Needle Roller & Cage Assembly, Metric, Bearing, Steel, Normal Clearance (Cn)	8	350000	7	Parts
11663	Paper Tape 1/2", 1", 2"	15	350000	2	Other
11664	Fiber Tape 1", 2"	16	350000	2	Other
11665	Transparent Tape 1"	17	350000	2	Other
11666	Eletric tape	18	350000	2	Other
11667	Teflon tape	19	350000	2	Other
11668	Double Tape	20	350000	2	Other
11676	Damper	28	350000	2	Other
11699	Diamond Grinder Disks	7	350000	2	Other
11658	Cable ties (Medium)	11	350000	2	Other
11660	Arduino Nano	12	350000	2	Other
11661	XLR Connector Female	13	350000	2	Other
11662	Safety Gloves Pairs	14	350000	2	Other
11692	Extension MS Rod 40 mm dia	1	350000	2	Raw Material
11693	Balsa 8mm	2	350000	2	Raw Material
11694	Balsa 4mm	3	350000	2	Raw Material
11695	Balsa 2mm	4	350000	2	Raw Material
11696	Balsa Square Stick	5	350000	2	Raw Material
11677	CNC dust collector shoe	29	350000	2	Other
11678	UPS batteries for SPF	30	350000	7	Other
11679	Battery replacement of field trial laptop	31	350000	7	Other
11680	SPF AC repair	32	350000	3	Equipment Repairs & Maintenance
11681	AC repair Room no. 117	33	350000	3	Equipment Repairs & Maintenance
11682	NDT Lab 4 ton AC maintenance	34	350000	3	Equipment Repairs & Maintenance
11734	Copper sheet 2ftx2ft	4	350000	2	Raw Material
11735	Laser Cutting	5	350000	3	Other
11736	Machine Screws inner plate	6	350000	2	Other
11730	Tether wire interface circuit card	4	300000	7	Parts
11724	4 Leg SS Sling Wire	20	300000	7	Tools
11683	Project Vehicle Expense	35	350000	3	Other
11684	Panaflex	36	350000	2	Other
11685	Internet line repair	37	350000	3	Other
11686	Fuel	38	350000	2	POL
11731	SS Stock 50x200 mm	1	350000	2	Raw Material
11732	SS Stock 20x20x100 mm	2	350000	2	Raw Material
11733	Cutting Charges	3	350000	3	Other
11737	Machine Screws outer plate	7	350000	2	Other
11738	Connector screws	8	350000	2	Other
11739	Marine silicon	9	350000	2	Chemical
11725	Machining & Fabrication of buoyancy chamber fittings	1	300000	7	Parts
11740	Threaded rod M3	10	350000	2	Other
11741	Threaded rod M4	11	350000	2	Other
11742	O ring	12	350000	2	Other
11726	Fabrication and installation of payload hold & release assembly	2	300000	7	Parts
11745	Plastic Cane	15	350000	2	Other
11746	Connectors	16	350000	2	Other
11747	Tachy tape	17	350000	2	Other
11711	Insulation Tape	8	300000	2	Other
11751	Automatic high torsion thick wire coil winding machine	1	350000	7	Machinery / Equipment
11752	Freight and Handling charges	2	350000	3	Other
11727	Machining and fabrication of End Cap with seal for electronics enclosure	1	300000	7	Parts
11728	Watertight Connectors for electronics enclosure (300m depth rated)	2	300000	7	Parts
11729	Machining and fabrication of payload plate assembly	3	300000	7	Parts
11712	Eye Bolt (SS)	9	300000	7	Parts
11713	Shekels	10	300000	7	Parts
11714	Soldering Wire	11	300000	2	Other
11715	BTS Motor Driver	12	300000	7	Parts
11716	XT 60 Connector	13	300000	7	Parts
11717	Switch (05 Ports)	14	300000	7	Parts
11718	Petrol (Bush Cutter)	15	300000	2	POL
11719	Buck 300W 20A	16	300000	7	Parts
11721	Cable Tie 8"	17	300000	2	Raw Material
11722	Heat Shrink 5mm	18	300000	7	Parts
11723	4 Core Shield Cable	19	300000	2	Parts
11749	Ultrasonic Cleaner tank capacity 30L	1	350000	7	Machinery / Equipment
11796	Patch Cord 3M / Corning/ Corn Tech 3m	9	200000	2	Parts
11609	Power Multi Sockets	20	200000	2	Parts
11610	Channel Pati, Flexible Pipe, Gati, Keils all, Steel keil, Rawal Bolts	21	200000	2	Parts
11753	Cable 8 core	18	350000	2	Other
11754	Wire spool	19	350000	2	Other
11755	Mesh M6	20	350000	2	Other
11756	Glue gun sticks	21	350000	2	Other
11758	Load cell (200kg)	22	350000	7	Other
11759	Double side tape	23	350000	2	Other
11760	Scotch tape	24	350000	2	Other
11762	Zip tie (6 inch)	26	350000	2	Other
11763	Zip tie (4 inch)	27	350000	2	Other
11764	PSU 24Vdc 5A	28	350000	2	Other
11765	Solder Flux	29	350000	2	Other
11767	Keil Wood 16 ft* 0.9375 ft * 0.125 ft	1	350000	2	Raw Material
11768	MS Sheet (8 ft * 4 ft*3mm)	2	350000	2	Raw Material
11769	C-clamps 1200mm	3	350000	7	Tools
11770	German Glue 1 Kg Packet	4	350000	2	Chemical
11771	Nails packets	5	350000	2	Other
11772	Varnish (1/2 qtr)	6	350000	2	Chemical
11777	Soldering flux	11	350000	2	Chemical
11778	Argon cylinder filling	12	350000	2	Other
11779	Internet charges for INTELL & RF LAB	13	350000	3	Other
11773	Sand Papers multiple sizes	7	350000	2	Other
11782	"AMPHENOL - 38999 CONTACT M39029/56-348 \nModel Number: 15-30 SOURIAU MPS 8D5/ 17M20BN"	1	350000	2	Other
11783	"AMPHENOL - 38999 CONTACT M39029/56-353\nModel Number: 15-30 SOURIAU MPS 8D5/ 17M20BN"	2	350000	2	Other
11784	"AMPHENOL - 38999 CONTACT M39029/58-360\nModel Number: 15/19 SOURIAU MPS D38999/26MJ35AN"	3	350000	2	Other
11774	Buffing discs	8	350000	2	Other
11785	"AMPHENOL - 38999 CONTACT M39029/58-360 \nModel Number: 15-20T SOURIAU FR 8D5- 23M54AA"	4	350000	2	Other
11786	"AMPHENOL - 38999 CONTACT M39029/58-365 \nModel Number: 15-20T SOURIAU FR 8D5- 23M54AA"	5	350000	2	Other
11787	"AMPHENOL - 38999 CONTACT M39029/76-425\nModel Number: 15-20T SOURIAU FR 8D5- 23M54AA"	6	350000	2	Other
11788	"AMPHENOL - 38999 CONTACT M39029/58-360\nModel Number: 16/24 SOURIAU MPS D38999/26MG35 AN"	7	350000	2	Other
11789	"AMPHENOL - 38999 CONTACT M39029/56-348\nModel Number: APH 1722 77820 88465562 JD38999/26MD 35BA"	8	350000	2	Other
11790	"AMPHENOL - 38999 CONTACT M39029/56-348 \nModel Number: 1737 SOURIAU HEI JD38999/26MD35 BN"	9	350000	2	Other
11791	"AMPHENOL - 38999 CONTACT M39029/56-348 \nModel Number: 15-30 SOU MPS J D38999/26MC35 BN"	10	350000	2	Other
11792	"AMPHENOL - 38999 CONTACT M39029/56-348 \nModel Number: SOURIAU 1723 5634591 8DO23M54BN"	11	350000	2	Other
11793	"AMPHENOL - 38999 CONTACT M39029/56-353\nModel Number: SOURIAU 1723 5634591 8DO23M54BN"	12	350000	2	Other
11766	Aluminum soldering wire	30	350000	2	Other
11775	Metal handles	9	350000	7	Other
11776	No clean flux	10	350000	2	Chemical
11780	Printer refill	14	350000	3	Other
11781	AC Magnetic coil repair at SPF	15	350000	3	Equipment Repairs & Maintenance
11798	Toyota Corola GLI/XLI	1	200000	3	Transport
11794	"AMPHENOL - 38999 CONTACT M39029/58-360\nModel Number: 16-19 Souriau MPS J D38999/26MC35AN"	13	350000	2	Other
11795	"AMPHENOL - 38999 CONTACT M39029/56-348\nModel Number: 15-30 Souriau MPS J D38999/26MB35BN"	14	350000	2	Other
11799	Toyota Hiace	2	200000	3	Transport
11834	AMAX-657-E2CW00A AMAX-5570, 4GRAM/64G, W10CDS RTE & Vis W/O Retain	1	200000	7	Parts
11835	AMAX-5001-A 4-CH Power Input with 4-ch DI EtherCAT Module	2	200000	7	Parts
11836	AMAX-5017H-A 4-CH High Speed Analog Input EtherCAT Module	3	200000	7	Parts
11816	Grinder repair	7	350000	3	Equipment Repairs & Maintenance
11837	AMAX-5017C-A 6-CH Current Input EtherCAT Module	4	200000	7	Parts
11838	96PSD-A60W24-MM A/D 100-240V 60W 24V MDR Din-Rail Power Supply	5	200000	7	Parts
11797	Colour Laser Printer	1	250000	7	IT Equipment
11839	Excavation on drain storm adjacent to SVDC	1	160000	3	Building Modification
11840	Fixing of paver work at SVDC	2	160000	3	Building Repairs & Maintenance
11841	Plantation and development of Nursery	3	160000	7	Other
11832	Repair/ Patch work of delievery from parade ground to SVDC	1	160000	3	Building Repairs & Maintenance
11817	MS Sheet & allen bolts	8	350000	2	Raw Material
11833	Provision of paint material and paint work on SVDC curb blocks, road and outer side wall	2	160000	2	Building Material
11813	Spary paint (Matte black)	4	350000	2	Chemical
11818	MS shaft & anchoring pins	9	350000	2	Other
11819	Diamond Cutting Disk 1mm	10	350000	2	Other
11800	Tether Interface Module	1	300000	7	Parts
11801	HD Underwater Camera Module (400m depth rated)	2	300000	7	Parts
11802	Water Tight connectors for Electronics enclosure (300m depth rated)	3	300000	2	Parts
11820	Diamond Buffing Disk	11	350000	2	Other
11821	Cutting charges	12	350000	3	Other
11822	Pink foam	13	350000	2	Other
11823	German Glue	14	350000	2	Chemical
11824	Nichrome wire 20 ft	15	350000	2	Other
11825	Long tool holder	16	350000	7	Other
11826	Exrta long ball nose 12*200	17	350000	2	Other
11803	Electronic Tray	1	300000	7	Parts
11804	Battery Charger	2	300000	7	Parts
11805	HD Underwater Camera Module	3	300000	7	Parts
11806	Ballast Weight	4	300000	2	Other
11807	Tether interface module	1	300000	7	Parts
11808	Intergration electronics board	2	300000	7	Parts
11814	FFP2 Mask	5	350000	2	Other
11815	Square tube (2in x 2in x 1.5mm)	6	350000	2	Other
11809	HD underwater camera module (400m depth rated)	3	300000	7	Parts
11827	Planks	18	350000	2	Other
11847	Acrylic Sheet	28	350000	2	Other
11811	Octane fuel	2	350000	2	POL
11812	Rain proof roof cover for Machine shop	3	350000	2	Other
11828	Shielded cable	19	350000	2	Other
11829	RS422 Converter	20	350000	2	Other
11830	Heater Coil	21	350000	2	Other
11831	Weight machining (2.5 x 2.5 ft)	22	350000	7	Test / Measuring Equipment
11842	Anchoring pins	23	350000	2	Other
11843	Long drill bits	24	350000	2	Other
11844	Long Bolts	25	350000	2	Other
11845	Drill machine	26	350000	3	Equipment / Tools rental
11846	Poster printing	27	350000	3	Other
11872	PA119A 900V/µs, 4A Power Amplifier with Thermal Shutoff	1	200000	2	Parts
11873	OPA549 High-Voltage, High-Current Operational Amplifier	2	200000	2	Parts
11874	5-1393224-2 5 Volt 8A PCB Relay (RY212005)	3	200000	2	Parts
11871	FHD 51MP USB Digital Microscope Camera 120x C Mount Lens US plug	1	200000	7	Test / Measuring Equipment
10179	Uniross original 1.5 v AA size cell for fluke multimter	4	160000	2	Other
11899	AC double breaker 20 A	10	350000	7	Other
11867	Gazebo Canopy Tent, size 13 x 13 feet	1	160000	7	Other
11868	Modified mount design, fabrication and installation on existing Ops Console	1	300000	7	Parts
11863	12V 9 Ah rechargeable battery for UPS	1	160000	7	Parts
11860	14.8V 18 AH High Endurance Li-ion battery pack	1	300000	2	Parts
11861	H6 Pro Li-ion High power AC/ DC balance charger	2	300000	7	Parts
11869	Launch deck to Ops Console digital data communication wire (for test purposes)	2	300000	7	Parts
11870	24” Sunbright (300 nits) low-blue light, anti-glare, HD video display (with 01x year warranty)	3	300000	7	IT Equipment
11848	Laptop Core i5 12 Gen 8gb RAM 256gb SSD (with warranty)	1	300000	7	IT Equipment
11849	HP 107a Printer	2	300000	7	IT Equipment
11862	Water tight battery enclosure	3	300000	7	Other
11859	High Performance Computing Laptop (8GB RAM, 256GB SSD, 10-CORE GPU) with IPS technology (500 nits brightness)	1	300000	7	IT Equipment
11850	HP M27F 27” IPS display (with warranty)	3	300000	7	IT Equipment
11851	UPS 1200 W	4	300000	7	IT Equipment
11855	Control integration board	1	300000	7	Parts
11856	Pressure sensor	2	300000	7	Parts
11857	Underwater light	3	300000	7	Parts
11858	Battery charger	4	300000	7	Parts
11810	Deep Cleaning Gasoline	1	350000	2	Chemical
11886	P/N: G-2012-C10-O\r\nCoreless direct drive micro gear pump, viton seals, fuel/light oil configuration	1	350000	3	Travel/Boarding/Lodging
11878	Fibrex PA6+GF30	1	350000	2	Raw Material
11875	XC9536    VQ44AMM0333   IC Controller	4	200000	2	Parts
11876	XC95144   IC Controller	5	200000	2	Parts
11877	SN74AHC245DW 20SOIC IC	6	200000	2	Parts
11879	Machining Tap	2	350000	7	Tools
11880	Loctite SI 5900	3	350000	2	Other
11884	Custom O ring	7	350000	2	Other
11881	M3 SS round head screw	4	350000	2	Other
11889	Core i7-8th Generation, 8GB RAM, 256GB SSD, 14" screen with charger	1	160000	7	IT Equipment
11882	M3 Helicoil	5	350000	2	Other
11883	Brass Nozzle	6	350000	2	Other
11885	Locking SS spring	8	350000	2	Other
11890	Propeller APC 15x8	1	350000	2	Other
11891	Propeller EMAX 14x7	2	350000	2	Other
11892	Brass rod 1 ft	3	350000	2	Raw Material
11893	SS Bolts	4	350000	2	Other
11894	Tap & counter sunk drill	5	350000	2	Other
11895	Power supply	6	350000	7	Parts
11896	Push connector 6 Nos	7	350000	2	Other
11897	Allen bolts 10x55 mm	8	350000	2	Other
11898	Silver plating 150 ml	9	350000	3	Other
11900	DC double breaker 20A	11	350000	7	Other
11901	Shrouded connector Blue (Male and Female)	12	350000	2	Other
11902	Shrouded connector Green (Male and Female)	13	350000	2	Other
11903	Shrouded connector Yellow (Male and Female)	14	350000	2	Other
11904	Shrouded connector Red (Male and Female)	15	350000	2	Other
11905	Shrouded connector Black (Male and Female)	16	350000	2	Other
11906	Din rail terminal block	17	350000	2	Other
11907	On/Off button 15A	18	350000	2	Other
11908	Potentiometer	19	350000	2	Other
11909	Ethernet panel mount female	20	350000	2	Other
11910	On/Off switch toggle	21	350000	2	Other
11911	Alligator connector	22	350000	2	Other
11912	Panel wire duct (25X25)	23	350000	2	Other
11913	C-Clamp	24	350000	7	Tools
11914	Allen bolts 12x230mm	25	350000	2	Other
11915	Rubber Damper	26	350000	2	Other
11917	V belt B-64	27	350000	2	Other
11918	Ni plating setup with chemicals	28	350000	7	Other
11919	Bolts 12.9 grade	29	350000	2	Other
11920	OCD parts Machining	30	350000	3	Other
11921	Planks	31	350000	2	Other
11922	High vacuum grease	32	350000	2	Chemical
11924	Motor V3115 KV900	33	350000	7	Parts
11925	T-Motor C80A 12S ESC	34	350000	7	Parts
11950	Echosounder depth detector (100m range) 300m depth rated	5	300000	7	Parts
11952	Leak detection sensor with safety switch	6	300000	7	Parts
11961	Times AAA grade tiles for flooring in alleyways of R&D Wing	1	160000	2	Building Material
11973	Rough pad	7	350000	2	Stationary
11994	Shell AX3 20W-50 Oil	2	350000	2	Chemical
11962	Pavers (02 x colors) for flooring at outer area of R&D Wing main entrance	2	160000	2	Building Material
11963	Tropical marble for main stairs and reception area of R&D Wing outside main entrance door	3	160000	2	Building Material
11964	Labor charges for pavers, tilling and marble	4	160000	3	Building Modification
11958	High power DC-DC converter module	2	300000	7	Parts
11965	Transportation charges for pavers delivery	5	160000	3	Transport
11960	Petrol for BTC-541 Hire Corolla Car for Month of June 2024	1	200000	3	Transport
11939	Aluminum Stock	1	160000	2	Raw Material
11935	TA/DA for Syed Awais Ahmed, ID: 11-22-04-8353, Grade: RO\r\nMeezan Account: 01840106931590 (0184)	1	200000	3	Travel/Boarding/Lodging
11940	MS Stock	2	160000	2	Raw Material
11938	asfls	1	160000	2	Chemical
11941	Kiel Wood	3	160000	2	Other
11942	Aluminum Sheet (8ft x 4ft x 2mm)	4	160000	2	Raw Material
11926	Propeller C10.5	35	350000	2	Other
11927	CF Rod 30x26 mm	36	350000	2	Other
11928	CF Rod 20x18 mm	37	350000	2	Other
11953	Temperature and humidity sensor	7	300000	7	Parts
11943	Dowel Pins & Fasteners	5	160000	2	Other
11966	Paper Rim A4	1	350000	2	Stationary
11929	Powder coating	38	350000	3	Other
11930	DIN7-16 connectors with cable ties	39	350000	2	Other
11967	Double side tape	2	350000	2	Stationary
11931	Shielded cable	40	350000	2	Other
11968	Envelope small	3	350000	2	Stationary
11969	Envelope medium	4	350000	2	Stationary
11970	Envelope large	5	350000	2	Stationary
11959	Supply of 2-TON Air Conditioner (Wall mounted)	1	160000	7	Appliance
11954	Fabrication/manufacture of buoyancy chamber for ROV skeleton hull	1	300000	7	Parts
11955	Fabrication of mounting plate for payload	2	300000	7	Parts
11932	Hydraulic Vise	41	350000	7	Tools
11933	Toyota Corola GLI/XLI	1	200000	3	Transport
11972	File clip	6	350000	2	Stationary
11956	Watertight connectors for battery pack enclosure	3	300000	7	Parts
11944	Tether interface board for underwater communication	1	300000	7	Parts
11945	Power sensing system module	2	300000	7	Parts
11948	Integrated flight controller board	3	300000	7	Parts
11949	Under Water 300m depth rated high intensity Lights	4	300000	7	Parts
11957	Fabrication/manufacture of frontal GRP cover of ROV hull	1	300000	7	Parts
11992	Shell HX3 20W-50 Oil	1	350000	2	Chemical
11995	Internet charges RF & NDT Lab	3	350000	3	Other
11996	Cable & Internet charges at SPF	4	350000	3	Other
11997	PND 3460 vehicle repair work	5	350000	3	Other
11998	Panaflex 38ft x 12 ft	6	350000	2	Other
11999	Paint with thinner	7	350000	2	Chemical
12007	Marine Operational Console:\r\nConsole specifications:\r\nDetachable console SS Metal\r\nConsole sheet thickness 2-3 mm\r\nMetal keyboard (black)wit track Ball IP67\r\n2X TWO Axis joysticks with controller\r\n2x power buttons for remote On/Off\r\n1x emergency power off switch(battery)\r\nExternal 2x USB 2.0 ports\r\nExternal 1x HDMI port\r\nExternal 1x ethernet port\r\n\r\nHardware Box specifications:\r\nDepth:<495mm\r\nAluminum hardware box 8-10mm\r\n3 Partition; top:5U (fixed tray), middle:3U (fixed tray), bottom:3u L bracket)1X ups and battery bank with backup time 10-15 min\r\nIncludes 4 port KVM switch (VGA & USB port)\r\nIncludes all necessary cables for connection with console\r\nIncludes 6x dampers\r\nProtocol converter/wiring/installation\r\nBottom sheet:8mm\r\nInstallation, commissioning and electrostatic powder paint included.	1	300000	7	Parts
11977	Permanent marker	11	350000	2	Stationary
12011	Cable ties	1	350000	2	Other
12008	Hardford cutting oil	1	350000	2	Chemical
12012	Silicon washers	2	350000	2	Other
12013	RTV silicon	3	350000	2	Chemical
12014	Brass rod	4	350000	2	Raw Material
12015	Magic depoxy	5	350000	2	Chemical
12009	Rubber Component	2	350000	2	Other
12016	Aluminum clamps	6	350000	2	Other
12010	Apex seal	3	350000	2	Other
12000	Fuel	8	350000	2	POL
12001	Machine cover 4x3 meter	9	350000	2	Other
12005	Gazebo Canopy Tent, size 13 x 13 feet	1	450000	7	Other
12002	AC repair at SPF conference room	10	350000	3	Other
12003	Rose petal Tissue	11	350000	2	Other
12004	Tissue roll	12	350000	2	Other
12006	Laptop Core i7, 12th Generation HP, 16GB RAM, 512GB SSD, 15.6" screen (01 x year warranty)	1	160000	7	IT Equipment
12017	O rings	7	350000	2	Other
11978	Sticky flag	12	350000	2	Stationary
12018	Inch drill & tap	8	350000	2	Other
12019	CF rods 32x 30 mm	9	350000	2	Raw Material
12020	CF rods 30x 28 mm	10	350000	2	Raw Material
11980	Gel pen	13	350000	2	Stationary
12021	CF rods 22x 20 mm	11	350000	2	Raw Material
12022	CF rods 22x 18 mm	12	350000	2	Raw Material
12023	Aluminum sheet 6061 (8 ft x 4 ft)	13	350000	2	Raw Material
12024	Diamond tool 8mmx70mm	14	350000	2	Other
11981	Whito correction pen	14	350000	2	Stationary
11982	Highlighter pen	15	350000	2	Stationary
12027	Harness	16	350000	2	Other
11974	paper tape 2"	8	350000	2	Stationary
11975	Green page	9	350000	2	Stationary
11976	Stapler pin	10	350000	2	Stationary
11983	Yellow sticky notes	16	350000	2	Stationary
11984	Alpha blade	17	350000	2	Stationary
11985	Stapler	18	350000	2	Stationary
11986	Rose petal Tissue	19	350000	2	Stationary
12028	Hartford lubrication oil	17	350000	2	Chemical
11987	Tissue roll	20	350000	2	Stationary
11988	Air freshener	21	350000	2	Stationary
11989	Room freshener	22	350000	2	Stationary
11990	Perfect glass cleaner	23	350000	2	Stationary
12056	AC repair of SPF conference room	19	350000	3	Equipment Repairs & Maintenance
12046	Nitrile gloves	9	350000	2	Other
12047	Cotton rage	10	350000	2	Other
12048	Circuit breaker	11	350000	2	Other
12049	Poster printing	12	350000	2	Other
12050	Acrylic frames	13	350000	2	Other
12025	Stainless steel pipe 2" x 1"	15	350000	2	Raw Material
11991	Kingston spray	24	350000	2	Stationary
12037	Safety gloves	1	350000	2	Other
12038	Paper tape 1/2", 1", 2"	2	350000	2	Other
12039	Fiber tape 1", 2"	3	350000	2	Other
12040	Transparent tape 1"	4	350000	2	Other
12041	Electric tape	5	350000	2	Other
12043	Teflon tape	6	350000	2	Other
12044	Double tape	7	350000	2	Other
12045	Elfy	8	350000	2	Other
12052	Laser welding	15	350000	3	Other
12030	AMPHENOL 15-30 SOU MPS J D38999/20MC35 BN	2	350000	2	Other
12029	SOURIAU FR 15-23T 8STA0-0609SN	1	350000	2	Other
12034	SOURIAU FR 15-37T 8STA6-0235SN	3	350000	2	Other
12035	AMPHENOL 1520 62GB-16E12-14PN(044)(214) QSC	4	350000	2	Other
12036	AMPHENOL 38999 CONTACT M39029/56-348	5	350000	2	Other
12051	Molybdenum stock	14	350000	2	Raw Material
12053	Jazz internet charges for NDT Lab	16	350000	3	Other
12054	SPF internet and cable charges	17	350000	3	Other
12055	Printer cartridges NDT Lab	18	350000	2	Other
11887	P/N: G-2012-C10-O\r\nCoreless direct drive micro gear pump, viton seals, fuel/light oil configuration	1	350000	7	Parts
11934	BIOVIN Tube Printer S900E Cable Label Printer  With Accessories Catridge R-200B/RS-S128 black (10), Shrink Tube 2mm 400m, Shrink Tube 4mm 400m, Shrink Tube 8mm 100m, Shrink Tube 10mm 100m	1	200000	2	Parts
\.


--
-- TOC entry 5302 (class 0 OID 373619)
-- Dependencies: 397
-- Data for Name: rfq; Type: TABLE DATA; Schema: purnew; Owner: postgres
--

COPY purnew.rfq (rfq_id, pcs_date, pcs_remarks, pcs_unt_id, pcs_hed_id, pcs_effhed_id, pcs_effunt_id) FROM stdin;
86	2021-02-04	\N	350000	350004	350004	350000
87	2020-04-24	VA. Paid by cheque 10265580	250000	250001	160001	160000
88	2020-06-18	General Admin. Paid by cheque 10265596	160000	\N	160001	160000
89	2020-06-29	General Admin. Paid by cheque 10265597	160000	\N	160001	160000
90	2020-07-06	General Admin. Paid by cheque 10265602	160000	\N	160001	160000
91	2020-07-18	UUV. Paid by cheque 10265607	300000	300002	160001	160000
92	2020-12-07	SOSE Lab. Paid by cheque 10265637	400000	400001	160001	160000
93	2020-12-24	SOSE Lab. Paid by cheque 10265646	400000	400001	160001	160000
94	2020-12-28	SOSE Lab. Paid by cheque 10265647	400000	400001	160001	160000
95	2020-12-30	SOSE Lab. Paid by cheque 10265648	400000	400001	160001	160000
96	2019-11-11	\N	160000	\N	200003	200000
97	2019-11-11	\N	200000	200003	200003	200000
98	2019-02-28	This purchase case includes 3 cases: Min 97 - 157,037. Min 109 - 25,262. Min 110 - 26,152.	350000	350002	350002	350000
99	2019-02-28	\N	350000	350002	350002	350000
100	2019-11-30	\N	350000	350002	350002	350000
101	2020-09-01	\N	450000	450001	200004	200000
102	2020-09-01	\N	160000	\N	200004	200000
103	2020-09-01	\N	160000	\N	200004	200000
104	2020-08-01	\N	300000	300001	300001	300000
105	2020-08-01	\N	300000	300001	300001	300000
106	2019-02-28	\N	350000	350002	350002	350000
107	2019-02-28	\N	350000	350002	350002	350000
108	2019-02-28	\N	350000	350002	350002	350000
109	2019-02-28	\N	350000	350002	350002	350000
110	2019-02-28	\N	350000	350002	350002	350000
111	2019-11-30	\N	350000	350002	350002	350000
112	2019-11-30	\N	350000	350002	350002	350000
113	2019-11-30	\N	350000	350002	350002	350000
114	2019-11-30	\N	350000	350002	350002	350000
115	2019-12-11	\N	350000	350002	350002	350000
116	2019-12-12	\N	350000	350002	350002	350000
117	2019-12-13	\N	350000	350002	350002	350000
118	2020-02-19	\N	350000	350002	350002	350000
119	2020-02-19	\N	350000	350002	350002	350000
120	2020-02-19	\N	350000	350002	350002	350000
121	2020-02-19	\N	350000	350002	350002	350000
122	2020-02-19	\N	350000	350002	350002	350000
123	2020-02-07	\N	300000	300002	350002	350000
124	2020-02-19	\N	350000	350002	350002	350000
125	2020-02-19	\N	350000	350002	350002	350000
126	2020-02-19	\N	350000	350002	350002	350000
127	2020-02-19	\N	350000	350002	350002	350000
128	2020-02-19	\N	350000	350002	350002	350000
129	2020-02-19	\N	350000	350002	350002	350000
130	2020-02-12	\N	300000	300002	350002	350000
131	2020-02-10	\N	300000	300002	350002	350000
132	2020-02-08	\N	300000	300002	350002	350000
133	2020-02-09	\N	300000	300002	350002	350000
134	2020-03-19	\N	350000	350002	350002	350000
135	2020-03-19	\N	350000	350002	350002	350000
136	2020-03-19	\N	350000	350002	350002	350000
137	2020-03-19	\N	350000	350002	350002	350000
138	2020-05-19	\N	350000	350002	350002	350000
139	2020-05-19	\N	350000	350002	350002	350000
140	2020-07-20	\N	350000	350002	350002	350000
141	2021-02-02	\N	300000	300001	300001	300000
142	2020-08-20	GPS. Paid by cheque 10265616	350000	350002	160001	160000
143	2020-09-21	General Admin. Paid by cheque 10265619	160000	\N	160001	160000
144	2020-10-12	General Admin. Paid by cheque 10265625	160000	\N	160001	160000
145	2020-11-25	Power Lab. Paid by cheque 10265635	450000	450002	160001	160000
146	2020-12-01	General Admin. Paid by cheque 10265636	160000	\N	160001	160000
147	2020-12-21	General Admin. Paid by cheque 10265642	160000	\N	160001	160000
149	2021-01-18	Dir System. Paid by cheque 10265650	450000	450002	160001	160000
150	2021-03-04	So Admin. Paid by cheque 10310338	160000	\N	160001	160000
151	2021-03-15	So Admin. Paid by cheque 10310340	160000	\N	160001	160000
152	2020-12-01	\N	300000	300001	300001	300000
153	2020-10-01	\N	350000	350005	350005	350000
154	2020-12-20	\N	350000	350005	350005	350000
155	2021-02-01	\N	350000	350005	350005	350000
156	2021-03-04	\N	350000	350005	350005	350000
157	2020-12-20	\N	350000	350004	350004	350000
158	2021-02-03	\N	350000	350004	350004	350000
159	2020-11-20	\N	350000	350003	350003	350000
160	2020-12-01	\N	350000	350003	350003	350000
161	2021-01-01	\N	350000	350003	350003	350000
162	2021-03-11	\N	350000	350003	350003	350000
166	2020-11-20	\N	350000	350002	350002	350000
167	2020-11-20	\N	350000	350002	350002	350000
168	2020-12-03	\N	350000	350002	350002	350000
169	2020-12-04	\N	350000	350002	350002	350000
170	2021-02-21	\N	350000	350002	350002	350000
171	2019-12-02	General Admin. Paid by cheque 10265554	160000	\N	160001	160000
172	2020-03-16	Power Lab. Paid by cheque 1026561	450000	450002	160001	160000
173	2020-04-20	Power Lab. Paid by cheque 10265577	450000	450002	160001	160000
175	2020-06-11	GPS. Paid by cheque 10265594	350000	350002	160001	160000
176	2020-07-06	General Admin. Paid by cheque 10265603	160000	\N	160001	160000
177	2020-07-07	Power Lab. Paid by cheque 10265604	450000	450002	160001	160000
178	2019-06-13	GPS. Paid by cheque 10257846	350000	350002	160001	160000
1322	2023-11-22	1. Mobilization - 45% (Rs 985005), 2. Removal of accessories and piping	200000	200014	200014	200000
179	2019-06-13	GPS. Paid by cheque 10257847	350000	350002	160001	160000
180	2019-06-13	GPS. Paid by cheque 10257848	350000	350002	160001	160000
181	2019-06-13	GPS. Paid by cheque 10257849	350000	350002	160001	160000
182	2019-06-13	GPS. Paid by cheque 10257850	350000	350002	160001	160000
183	2019-06-24	GPS. Paid by cheque 10257856	350000	350002	160001	160000
184	2019-06-24	GPS. Paid by cheque 10257857	350000	350002	160001	160000
185	2019-07-01	GPS. Paid by cheque 10257891	350000	350002	160001	160000
186	2019-07-02	GPS. Paid by cheque 10257892	350000	350002	160001	160000
187	2019-07-08	GPS. Paid by cheque 10257893	350000	350002	160001	160000
188	2020-07-07	VA. Paid by cheque 10265605	250000	250001	160001	160000
189	2021-03-03	\N	200000	200003	200003	200000
190	2021-03-22	\N	200000	200004	200004	200000
191	2019-11-14	\N	200000	200002	200002	200000
192	2020-02-14	\N	200000	200002	200002	200000
193	2020-04-14	\N	200000	200002	200002	200000
194	2020-06-29	General Admin. Paid by cheque 10265600	160000	\N	160001	160000
195	2020-07-18	General Admin. Paid by cheque 10265608	160000	\N	160001	160000
196	2021-03-03	\N	350000	350005	350005	350000
197	2020-03-17	\N	200000	200003	200003	200000
198	2020-04-15	\N	200000	200003	200003	200000
199	2020-03-27	\N	200000	200003	200003	200000
200	2020-11-20	\N	200000	200003	200003	200000
201	2020-03-17	\N	200000	200003	200003	200000
202	2021-01-25	\N	200000	200004	200004	200000
203	2020-11-20	\N	350000	350005	350005	350000
204	2020-04-24	VA. Paid by cheque 10265581	250000	250001	160001	160000
206	2020-06-09	\N	200000	200003	200003	200000
207	2020-12-21	\N	200000	200003	200003	200000
208	2021-01-24	\N	200000	200003	200003	200000
209	2020-12-21	\N	200000	200003	200003	200000
210	2021-03-01	\N	200000	200003	200003	200000
211	2021-03-02	\N	200000	200003	200003	200000
212	2020-01-17	GPS. Paid by cheque 1026558	350000	350002	160001	160000
213	2020-01-17	GPS. Paid by cheque 1026559	350000	350002	160001	160000
214	2020-03-16	GPS. Paid by cheque 1026563	350000	350002	160001	160000
340	2020-01-15	\N	350000	350002	350002	350000
215	2020-03-16	GPS. Paid by cheque 1026562	350000	350002	160001	160000
216	2020-03-25	Dir SoSE. Paid by cheque 1026573	400000	400001	160001	160000
217	2020-03-25	Dir SoSE. Paid by cheque 1026574	400000	400001	160001	160000
219	2020-04-30	General Admin. 	160000	\N	160001	160000
220	2020-05-29	General Admin. Paid by cheque 10265582	160000	\N	160001	160000
221	2020-06-02	General Admin. Paid by cheque 10265589	160000	\N	160001	160000
222	2020-06-02	General Admin. Paid by cheque 10265590	160000	\N	160001	160000
223	2020-06-02	General Admin. Paid by cheque 10265591	160000	\N	160001	160000
224	2020-06-03	GPS. Paid by cheque 10265592	350000	350002	160001	160000
225	2020-06-22	General Admin. Paid by cheque 10265595	160000	\N	160001	160000
226	2020-07-18	GPS. Paid by cheque 10265606	350000	350002	160001	160000
227	2020-09-02	General Admin. Paid by cheque 10265617	160000	\N	160001	160000
228	2020-09-21	General Admin. Paid by cheque 10265620	160000	\N	160001	160000
229	2020-10-02	General Admin. Paid by cheque 10265622	160000	\N	160001	160000
230	2020-10-15	General Admin. Paid by cheque 10265626	160000	\N	160001	160000
231	2020-11-02	General Admin. Paid by cheque 10265629	160000	\N	160001	160000
232	2020-11-16	General Admin. Paid by cheque 10265631	160000	\N	160001	160000
233	2020-11-16	General Admin. Paid by cheque 10265630	160000	\N	160001	160000
234	2020-11-16	General Admin. Paid by cheque 10265640	160000	\N	160001	160000
235	2020-11-20	General Admin. Paid by cheque 10265632	160000	\N	160001	160000
236	2020-11-24	General Admin. Paid by cheque 10265634	160000	\N	160001	160000
237	2020-11-20	General Admin. Paid by cheque 10265633	160000	\N	160001	160000
238	2020-12-21	General Admin. Paid by cheque 10265643	160000	\N	160001	160000
239	2020-12-21	General Admin. Paid by cheque 10265644	160000	\N	160001	160000
240	2021-01-04	Paid by cheque 10265649	160000	\N	160001	160000
241	2021-02-02	Paid by cheque 10310334	160000	\N	160001	160000
242	2021-02-08	Dir System. Paid by cheque 10310335	160000	\N	160001	160000
243	2021-02-10	So Admin. Paid by cheque 10310336	160000	\N	160001	160000
244	2021-02-18	So Admin. Paid by cheque 10310337	160000	\N	160001	160000
245	2021-03-22	SO Sport. Paid by cheque 10310341	160000	\N	160001	160000
246	2019-06-30	General Admin. 	160000	\N	160001	160000
247	2019-07-26	General Admin. 	160000	\N	160001	160000
248	2019-08-31	General Admin. 	160000	\N	160001	160000
249	2019-09-30	General Admin. 	160000	\N	160001	160000
250	2019-10-03	General Admin. 	160000	\N	160001	160000
251	2019-11-30	General Admin. 	160000	\N	160001	160000
254	2021-03-20	\N	200000	200004	200004	200000
255	2020-08-26	\N	200000	200003	200003	200000
256	2021-01-24	\N	200000	200004	200004	200000
257	2021-03-03	\N	300000	300001	300001	300000
258	2020-10-20	\N	350000	350002	350002	350000
259	2020-10-20	\N	350000	350002	350002	350000
260	2021-02-01	\N	350000	350004	350004	350000
261	2020-07-28	UUV. Paid by cheque 10265612	300000	300002	160001	160000
262	2020-08-19	Dir SoSE. Paid by cheque 10265615	400000	400001	160001	160000
263	2020-11-02	General Admin. Paid by cheque 10265628	160000	\N	160001	160000
264	2020-12-21	General Admin. Paid by cheque 10265639	160000	\N	160001	160000
265	2020-12-21	General Admin. Paid by cheque 10265641	160000	\N	160001	160000
266	2021-01-25	So Comm. Paid by cheque 10310333	160000	\N	160001	160000
267	2019-12-04	\N	160000	\N	200003	200000
268	2019-12-09	\N	200000	200003	200003	200000
269	2020-02-14	\N	200000	200003	200003	200000
270	2019-12-24	\N	200000	200003	200003	200000
271	2019-12-01	\N	200000	200003	200003	200000
272	2020-06-02	\N	160000	\N	200003	200000
273	2020-04-22	\N	200000	200003	200003	200000
274	2020-03-20	\N	160000	\N	200003	200000
275	2020-07-20	\N	160000	\N	200003	200000
276	2020-09-02	\N	200000	200003	200003	200000
277	2020-12-30	\N	200000	200003	200003	200000
278	2020-12-30	\N	200000	200003	200003	200000
279	2020-09-01	\N	400000	400001	200004	200000
280	2020-10-01	\N	160000	\N	200004	200000
281	2020-12-01	\N	160000	\N	200004	200000
282	2020-12-01	\N	450000	450001	200004	200000
283	2020-12-01	\N	200000	200004	200004	200000
284	2020-12-01	\N	200000	200004	200004	200000
285	2021-01-22	\N	200000	200004	200004	200000
286	2020-08-01	\N	300000	300001	300001	300000
287	2020-08-01	\N	300000	300001	300001	300000
288	2020-11-01	\N	300000	300001	300001	300000
289	2020-11-02	\N	300000	300001	300001	300000
290	2020-11-03	\N	300000	300001	300001	300000
291	2021-01-21	\N	300000	300001	300001	300000
292	2021-02-01	\N	300000	300001	300001	300000
293	2021-03-05	\N	350000	350005	350005	350000
294	2021-03-16	\N	350000	350003	350003	350000
295	2021-03-31	\N	400000	400002	400002	400000
296	2021-03-31	\N	400000	400002	400002	400000
297	2020-02-08	\N	300000	300002	350002	350000
298	2020-02-08	\N	300000	300002	350002	350000
299	2020-02-11	\N	300000	300002	350002	350000
300	2020-06-26	\N	300000	300002	350002	350000
301	2020-02-09	\N	300000	300002	350002	350000
302	2021-03-02	\N	350000	350002	350002	350000
303	2020-04-15	UUV. Paid by cheque 10265575	300000	300002	160001	160000
304	2020-04-15	General Admin. Paid by cheque 10265576	160000	\N	160001	160000
305	2020-04-20	UUV. Paid by cheque 10265578	300000	300002	160001	160000
306	2020-04-24	UUV. Paid by cheque 10265579	300000	300002	160001	160000
307	2020-06-23	General Admin. Paid by cheque 10265598	160000	\N	160001	160000
308	2019-06-24	GPS. Paid by cheque 10257855	350000	350002	160001	160000
309	2020-03-04	VA. Paid by cheque 1026555	250000	250001	160001	160000
310	2020-03-04	VA. Paid by cheque 1026556	250000	250001	160001	160000
311	2020-05-07	UUV. Paid by cheque 10265583	300000	300002	160001	160000
312	2020-07-28	General Admin. Paid by cheque 10265611	160000	\N	160001	160000
313	2020-07-29	UUV. Paid by cheque 10265613	300000	300002	160001	160000
314	2020-08-18	General Admin. Paid by cheque 10265614	160000	\N	160001	160000
315	2020-10-06	General Admin. Paid by cheque 10265623	160000	\N	160001	160000
316	2020-10-08	VA. Paid by cheque 10265624	250000	250001	160001	160000
318	2019-02-28	\N	350000	350002	350002	350000
319	2019-02-28	\N	350000	350002	350002	350000
321	2019-02-28	\N	350000	350002	350002	350000
324	2021-02-02	\N	350000	350005	350005	350000
325	2020-03-16	General Admin. Paid by cheque 1026560	160000	\N	160001	160000
326	2021-03-05	\N	300000	300001	300001	300000
327	2019-11-30	\N	250000	250001	350002	350000
328	2019-11-11	\N	200000	200003	200003	200000
329	2020-10-19	VA. Paid by cheque 10265627	250000	250001	160001	160000
330	2020-12-09	VA. Paid by cheque 10265638	250000	250001	160001	160000
331	2021-01-19	ET Division. Paid by cheque 10310332	250000	250002	160001	160000
332	2020-02-12	\N	160000	\N	200003	200000
333	2021-02-02	\N	350000	350004	350004	350000
335	2020-10-01	\N	350000	350003	350003	350000
336	2020-12-01	\N	350000	350003	350003	350000
337	2021-01-01	\N	350000	350003	350003	350000
338	2021-03-31	\N	350000	350010	350010	350000
339	2019-02-28	\N	350000	350002	350002	350000
341	2020-01-16	\N	350000	350002	350002	350000
342	2020-02-19	\N	350000	350002	350002	350000
343	2020-02-19	\N	350000	350002	350002	350000
344	2020-02-19	\N	350000	350002	350002	350000
345	2020-02-19	\N	350000	350002	350002	350000
346	2020-02-19	\N	350000	350002	350002	350000
347	2020-02-19	\N	350000	350002	350002	350000
348	2020-03-19	\N	350000	350002	350002	350000
349	2020-05-19	\N	350000	350002	350002	350000
350	2020-06-19	\N	350000	350002	350002	350000
351	2020-07-20	\N	350000	350002	350002	350000
352	2020-08-20	\N	350000	350002	350002	350000
353	2021-01-01	\N	350000	350003	350003	350000
354	2019-10-03	GPS. Paid by cheque 10265551	350000	350002	160001	160000
355	2021-03-13	\N	350000	350003	350003	350000
356	2020-12-02	\N	300000	300001	300001	300000
357	2021-03-01	\N	300000	300001	300001	300000
358	2020-05-19	\N	350000	350002	350002	350000
359	2021-01-19	So Admin. Paid by cheque 10310331	160000	\N	160001	160000
360	2020-11-01	\N	200000	200004	200004	200000
361	2020-09-09	GPS. Paid by cheque 10265618	350000	350002	160001	160000
362	2019-10-03	VA. Paid by cheque 10265553	250000	250001	160001	160000
363	2020-03-16	GPS. Paid by cheque 1026565	350000	350002	160001	160000
364	2020-03-16	GPS. Paid by cheque 1026566	350000	350002	160001	160000
365	2020-03-16	GPS. Paid by cheque 1026567	350000	350002	160001	160000
366	2020-03-16	GPS. Paid by cheque 1026568	350000	350002	160001	160000
367	2020-03-16	GPS. Paid by cheque 1026569	350000	350002	160001	160000
368	2020-03-16	GPS. Paid by cheque 1026570	350000	350002	160001	160000
369	2020-03-16	GPS. Paid by cheque 1026571	350000	350002	160001	160000
370	2020-03-16	GPS. Paid by cheque 1026572	350000	350002	160001	160000
371	2020-05-08	GPS. Paid by cheque 10265584	350000	350002	160001	160000
372	2020-05-08	GPS. Paid by cheque 10265585	350000	350002	160001	160000
373	2020-05-08	GPS. Paid by cheque 10265586	350000	350002	160001	160000
374	2020-05-08	GPS. Paid by cheque 10265587	350000	350002	160001	160000
375	2020-05-08	GPS. Paid by cheque 10265588	350000	350002	160001	160000
376	2020-10-20	\N	200000	200003	200003	200000
377	2020-09-01	\N	200000	200004	200004	200000
378	2020-10-01	\N	200000	200004	200004	200000
379	2021-01-23	\N	200000	200004	200004	200000
380	2021-03-21	\N	200000	200004	200004	200000
381	2019-11-11	\N	200000	200003	200003	200000
382	2020-10-01	\N	350000	350005	350005	350000
383	2021-03-01	\N	350000	350005	350005	350000
385	2020-10-20	\N	350000	350004	350004	350000
386	2020-12-20	\N	350000	350004	350004	350000
388	2020-11-20	\N	350000	350003	350003	350000
389	2020-12-01	\N	350000	350003	350003	350000
390	2021-03-12	\N	350000	350003	350003	350000
392	2021-03-15	\N	350000	350003	350003	350000
393	2019-02-28	\N	350000	350002	350002	350000
394	2019-02-28	\N	350000	350002	350002	350000
395	2019-02-28	\N	350000	350002	350002	350000
396	2019-02-28	\N	350000	350002	350002	350000
397	2019-02-28	\N	350000	350002	350002	350000
398	2019-02-28	\N	350000	350002	350002	350000
399	2019-11-30	\N	350000	350002	350002	350000
400	2020-01-17	\N	350000	350002	350002	350000
401	2020-01-18	\N	350000	350002	350002	350000
402	2020-01-19	\N	350000	350002	350002	350000
404	2020-05-19	\N	350000	350002	350002	350000
405	2020-05-19	\N	350000	350002	350002	350000
406	2020-10-20	\N	350000	350002	350002	350000
407	2020-12-01	\N	350000	350002	350002	350000
408	2020-12-02	\N	350000	350002	350002	350000
409	2021-03-01	\N	350000	350002	350002	350000
410	2020-07-02	General Admin. Paid by cheque 10265599	160000	\N	160001	160000
411	2021-03-04	\N	300000	300001	300001	300000
412	2020-03-16	VA. Paid by cheque 1026564	250000	250001	160001	160000
413	2021-03-10	\N	350000	350003	350003	350000
414	2019-10-03	VA. Paid by cheque 10265552	250000	250001	160001	160000
415	2020-01-09	Power Lab. Paid by cheque 1026557	450000	450002	160001	160000
417	2021-03-15	\N	350000	350003	350003	350000
418	2021-03-15	\N	350000	350003	350003	350000
419	2021-03-15	\N	350000	350003	350003	350000
420	2021-03-15	\N	350000	350003	350003	350000
421	2021-03-15	\N	350000	350003	350003	350000
422	2021-03-15	\N	350000	350003	350003	350000
423	2021-03-15	\N	350000	350005	350005	350000
425	2021-03-15	\N	350000	350005	350005	350000
426	2021-03-15	\N	350000	350005	350005	350000
427	2021-03-15	\N	350000	350005	350005	350000
428	2021-03-15	\N	350000	350005	350005	350000
429	2021-03-15	\N	350000	350004	350004	350000
430	2021-03-15	\N	350000	350004	350004	350000
431	2021-03-15	\N	350000	350004	350004	350000
432	2021-03-15	\N	350000	350004	350004	350000
433	2021-03-15	\N	350000	350004	350004	350000
434	2021-03-15	\N	350000	350004	350004	350000
440	2019-02-28	Items at serial 59 to 63 have a combined price of 140,000 in the approval minute.	350000	350002	160001	160000
443	2019-02-28	\N	350000	350007	160001	160000
444	2019-02-28	\N	200000	200002	160001	160000
445	2019-02-28	\N	200000	200001	160001	160000
447	2021-04-15	\N	450000	450001	450001	450000
449	2021-04-15	\N	200000	200004	200004	200000
451	2021-04-15	\N	350000	350010	350010	350000
452	2019-02-28	\N	250000	250001	160001	160000
453	2019-02-28	\N	300000	300002	160001	160000
456	2021-04-20	\N	350000	350011	350011	350000
457	2021-04-20	\N	350000	350011	350011	350000
458	2021-04-20	\N	350000	350011	350011	350000
459	2021-05-05	\N	350000	350011	350011	350000
461	2021-05-05	\N	350000	350011	350011	350000
462	2021-05-05	\N	300000	300001	300001	300000
463	2021-05-19	\N	350000	350002	350002	350000
464	2021-05-05	\N	350000	350002	350002	350000
465	2021-05-05	\N	350000	350011	350011	350000
469	2021-05-05	\N	350000	350011	350011	350000
471	2021-05-21	\N	350000	350005	350005	350000
472	2021-06-07	\N	350000	350005	350005	350000
473	2021-06-07	\N	350000	350005	350005	350000
475	2021-05-27	\N	350000	350003	350003	350000
476	2021-05-27	\N	350000	350003	350003	350000
477	2021-05-27	\N	350000	350003	350003	350000
478	2021-06-22	\N	350000	350010	350010	350000
479	2021-06-15	\N	200000	200004	200004	200000
480	2021-04-09	\N	200000	200004	200004	200000
481	2021-10-06	\N	300000	300001	300001	300000
482	2021-06-08	\N	350000	350004	350004	350000
483	2021-06-08	\N	350000	350004	350004	350000
484	2021-05-15	\N	350000	350005	350005	350000
485	2021-07-28	\N	350000	350005	350005	350000
486	2021-07-28	\N	350000	350005	350005	350000
487	2021-08-27	\N	350000	350005	350005	350000
488	2021-07-30	\N	350000	350004	350004	350000
489	2021-07-30	\N	350000	350004	350004	350000
490	2021-07-28	\N	350000	350003	350003	350000
491	2021-07-28	\N	350000	350003	350003	350000
492	2021-08-09	\N	200000	200004	200004	200000
493	2021-06-29	\N	350000	350005	350005	350000
494	2021-06-29	\N	350000	350005	350005	350000
495	2021-06-29	\N	400000	400002	400002	400000
496	2021-06-08	\N	350000	350004	350004	350000
499	2021-06-29	\N	350000	350003	350003	350000
500	2021-06-29	\N	350000	350003	350003	350000
501	2021-08-30	\N	350000	350005	350005	350000
502	2021-09-07	\N	200000	200004	200004	200000
503	2021-09-01	\N	350000	350010	350010	350000
504	2021-09-07	\N	350000	350012	350012	350000
505	2021-06-21	\N	350000	350002	350002	350000
506	2021-07-06	\N	350000	350004	350004	350000
508	2021-09-10	\N	200000	200002	200002	200000
509	2021-09-09	\N	350000	350005	350005	350000
511	2021-09-09	\N	350000	350014	350014	350000
512	2021-09-09	\N	350000	350014	350014	350000
513	2021-04-01	\N	160000	\N	160001	160000
514	2021-04-01	\N	400000	400002	160001	160000
515	2021-04-01	\N	400000	400002	160001	160000
516	2021-04-01	Payment to OI/C Teabar	160000	\N	160001	160000
517	2021-04-02	\N	160000	\N	160001	160000
518	2021-04-05	\N	160000	\N	160001	160000
519	2021-04-08	Payment to Comm Department	160000	\N	160001	160000
520	2021-04-06	\N	160000	\N	160001	160000
521	2021-04-13	Payment to SO Admin	160000	\N	160001	160000
522	2021-04-14	\N	160000	\N	160001	160000
523	2021-09-30	60% payment will be release on successful testing of prototype and 40% will be released on successful testing of power supplies in final form.	200000	200004	200004	200000
253	2021-02-21	\N	200000	200004	200004	200000
532	2021-09-09	\N	350000	350002	350002	350000
533	2021-07-14	\N	200000	200002	200002	200000
534	2021-07-14	\N	200000	200002	200002	200000
535	2021-09-08	\N	350000	350004	350004	350000
536	2021-09-08	\N	350000	350004	350004	350000
537	2021-09-08	\N	350000	350004	350004	350000
538	2021-09-08	\N	350000	350004	350004	350000
540	2021-10-08	\N	200000	200004	200004	200000
541	2021-10-21	100% on delivery	350000	350010	350010	350000
542	2021-10-21	50% advance & 50 % on delivery	350000	350010	350010	350000
543	2021-10-21	100% on delivery	350000	350014	350014	350000
544	2021-10-21	50% advance & 50 % on delivery	350000	350014	350014	350000
545	2021-10-21	50% advance & 50 % on delivery	350000	350014	350014	350000
549	2021-10-22	100 % advance on delivery	350000	350014	350014	350000
550	2021-10-25	\N	300000	300001	300001	300000
551	2021-09-22	\N	350000	350010	350010	350000
552	2021-10-27	\N	200000	200004	200004	200000
553	2021-10-29	100% payment on delivery.	350000	350014	350014	350000
555	2021-11-05	\N	300000	300001	300001	300000
556	2021-11-05	50% Advance 50% on delivery	300000	300001	300001	300000
563	2021-11-09	70% advance  with PO and 30% on delivery	350000	350004	350004	350000
564	2021-11-09	50% advance and 50% after delivery	350000	350004	350004	350000
567	2021-11-10	100% upon delivery	350000	350004	350004	350000
568	2021-11-15	\N	200000	200004	200004	200000
570	2021-11-12	\N	160000	\N	350012	350000
571	2021-11-12	\N	200000	200004	200004	200000
572	2021-11-23	100% payment on delivery	350000	350014	350014	350000
573	2022-02-01	100% payment on delivery	350000	350014	350014	350000
574	2021-11-26	70% payment with PO (advance), 30% after delivery/acceptance	350000	350004	350004	350000
575	2021-11-26	100% payment upon delivery	350000	350004	350004	350000
576	2021-11-26	50% Advance , 50% after delivery	350000	350004	350004	350000
577	2021-11-29	50% advance and 50% after delivery	350000	350004	350004	350000
578	2021-11-29	\N	350000	350004	350004	350000
579	2021-11-29	50% advance and 50% after delivery	350000	350004	350004	350000
580	2021-12-02	\N	200000	200004	200004	200000
581	2021-12-10	\N	200000	200004	200004	200000
584	2021-12-08	100% payment upon delivery	350000	350003	350003	350000
585	2021-12-08	100% payment upon delivery	350000	350012	350012	350000
656	2021-11-01	Hiace van for 1st Nov	200000	200004	200004	200000
586	2021-12-09	100% payment upon delivery	350000	350012	350012	350000
587	2021-12-13	\N	300000	300001	300001	300000
588	2021-12-23	100% payment upon delivery	350000	350002	350002	350000
589	2021-12-13	\N	300000	300001	300001	300000
590	2021-12-13	\N	200000	200008	200008	200000
591	2021-12-21	100% upon delivery	350000	350005	350005	350000
592	2021-12-21	100% upon delivery	350000	350005	350005	350000
593	2021-12-21	50% Advance & 50% at delivery	350000	350006	350006	350000
594	2021-12-22	\N	200000	200004	200004	200000
596	2021-12-23	100% payment upon delivery	350000	350010	350010	350000
597	2021-12-23	100% payment upon delivery	350000	350010	350010	350000
598	2021-12-23	50% payment in advance 50% after delivery	350000	350002	350002	350000
599	2021-12-24	\N	200000	200004	200004	200000
600	2021-12-24	100% on Delivery	350000	350004	350004	350000
601	2021-12-24	50% advance 50% after completion of order	350000	350004	350004	350000
602	2021-12-24	70% Advance 30% after completion of order	350000	350004	350004	350000
607	2021-12-24	100% payment upon delivery	350000	350004	350004	350000
637	2021-12-27	100% paymeny upon delivery	350000	350010	350010	350000
638	2021-12-23	100% payment on delivery	350000	350010	350010	350000
639	2021-12-23	100% payment upon delivery	350000	350010	350010	350000
640	2021-12-28	\N	200000	200004	200004	200000
641	2021-12-29	100% Payment upon delivery	350000	350003	350003	350000
642	2022-01-03	\N	200000	200004	200004	200000
643	2021-12-16	Required on 16th	200000	200004	200004	200000
644	2021-12-16	Required on 16,20,21,22,23	200000	200004	200004	200000
1065	2022-12-09	hhj	200000	200004	200004	200000
645	2021-12-24	Required on 24th, 27th, 28th, 29th, 30th and 31st	200000	200004	200004	200000
646	2021-12-28	Required for 28th and 29th	200000	200004	200004	200000
647	2021-12-29	Taken on 11th and 16th Dec 21	200000	200004	200004	200000
648	2022-01-06	Required on 3,4,5,6, 7,10,11,12 and 13 Jan 22	200000	200004	200004	200000
649	2022-01-11	100% payment after delivery	350000	350004	350004	350000
651	2022-01-12	100% payment upon delivery	350000	350003	350003	350000
652	2022-01-12	75% payment in advance 25% payment after delivery	350000	350003	350003	350000
655	2022-01-12	100% payment after delivery	350000	350003	350003	350000
657	2021-11-17	Hiace van for 17th Nov	200000	200004	200004	200000
658	2021-10-21	Hiace van for 21st Oct	200000	200004	200004	200000
659	2022-02-23	\N	300000	300003	300003	300000
662	2022-01-18	Required for 18, 19,20, 21, 24,25,26,27,28 and 31 Jan 22	200000	200004	200004	200000
663	2022-01-19	Required for 20, 26 and 27 Jan 22	200000	200004	200004	200000
664	2022-01-21	Total payment required after complition of project with project in charge satisfaction	350000	350004	350004	350000
665	2022-01-21	Total payment required after the complition of project with project in charge satisfaction	350000	350004	350004	350000
666	2022-02-01	total payment required after the completion of project with project in charge satisfaction	350000	350004	350004	350000
667	2022-01-24	\N	300000	300001	300001	300000
668	2022-01-31	50% Advance, 50% After completion of order	350000	350014	350014	350000
669	2022-01-31	Total payment required after the complition of project with project in charge satisfaction	350000	350004	350004	350000
671	2022-01-31	100%	350000	350014	350014	350000
672	2022-02-01	100% payment after delivery	350000	350004	350004	350000
673	2022-02-01	100% payment after delivery	350000	350014	350014	350000
674	2022-02-01	70% advance & 30% after completion of work	350000	350014	350014	350000
675	2022-02-02	\N	200000	200006	200006	200000
678	2022-02-02	\N	200000	200006	200006	200000
679	2022-02-02	\N	200000	200004	200004	200000
680	2022-02-02	\N	200000	200006	200006	200000
681	2022-02-02	Required on 2 and 3 Feb 22	200000	200004	200004	200000
682	2022-02-03	\N	300000	300001	300001	300000
683	2022-02-03	\N	300000	300001	300001	300000
684	2022-02-03	\N	300000	300001	300001	300000
685	2022-02-03	50% Advance payment, 25% on delivery and 25% on completion of work	200000	200004	200004	200000
686	2022-02-03	ghj	350000	350005	350005	350000
687	2022-02-07	FOR (DDP) Karachi/Islamabad basis\r\n100% payment on receipt of stores	200000	200006	200006	200000
688	2022-02-07	100% Payment upon delivery	350000	350002	350002	350000
689	2022-02-08	\N	300000	300001	300001	300000
690	2022-02-21	\N	300000	300003	300003	300000
691	2022-02-22	50% advance and 50% after delivery	350000	350010	350010	350000
692	2022-02-22	Toyota Corolla required on 7,8,9,10,11,14,15,16,17,18,20,21 Feb 22 Hiace required on 8,9,15,16 Feb 22	200000	200004	200004	200000
693	2022-02-23	100% payment on delivery	200000	200004	200004	200000
694	2022-02-23	Payment on delivery	200000	200004	200004	200000
695	2022-02-23	50% advance payment and 50% on delivery	200000	200004	200004	200000
696	2022-02-24	\N	300000	300003	300003	300000
698	2022-02-25	100% payment upon delivery	350000	350010	350010	350000
699	2022-02-28	Toyota Corolla XLI/GLI required on 22, 23, 24, 25 and 28 Feb and Hiac required on 17, 18, 22, 24, 25 and 28 Feb and lunch on 1 and 2 Feb	200000	200004	200004	200000
700	2022-02-28	\N	200000	200004	200004	200000
704	2022-03-09	\N	300000	300003	300003	300000
705	2022-03-18	70% payment with PO (advance) and the balance 30% on delivery	350000	350014	350014	350000
706	2022-03-25	100% after completion of Work	350000	350014	350014	350000
707	2022-03-11	Toyota Corolla XLI/GLI required on 1,2,3 and 4 Mar 22 and Hiace required on 1 and 2 Mar	200000	200004	200004	200000
708	2022-03-11	\N	200000	200004	200004	200000
710	2022-03-15	50% Advance Payment and 50% on delivery of stores	200000	200006	200006	200000
711	2022-03-15	\N	200000	200006	200006	200000
712	2022-03-15	Payment on delivery	200000	200006	200006	200000
714	2022-03-16	70% Advance and 30% after delivery of store	350000	350014	350014	350000
718	2022-03-16	\N	300000	300001	300001	300000
719	2022-03-17	100% payment upon delivery of items.	350000	350014	350014	350000
720	2022-03-18	100% payment after delivery of items	350000	350014	350014	350000
721	2022-03-18	Toyota Corolla GLI/XLI required on 16 Mar 22	200000	200004	200004	200000
723	2022-03-18	70% Payment with PO (Advance) and the Balance 30% on delivery	350000	350014	350014	350000
724	2022-03-18	\N	200000	200004	200004	200000
725	2022-01-11	\N	160000	\N	350010	350000
726	2022-03-22	\N	300000	300003	300003	300000
727	2022-03-28	70% Payment with PO (Advance) and the balance 30% on delivery	350000	350014	350014	350000
729	2022-03-28	\N	200000	200007	200007	200000
731	2022-03-28	\N	200000	200006	200006	200000
732	2022-03-28	dfsdfsf	200000	200006	200006	200000
733	2022-03-30	100% Payment against delivery of stores	350000	350005	350003	350000
734	2022-03-31	100% Payment upon delivery	350000	350005	350003	350000
736	2022-04-01	100% Payment upon delivery	350000	350003	350003	350000
737	2022-04-04	50% advance and 50% after delivery.	350000	350004	350004	350000
738	2022-04-06	100% Payment after delivery	350000	350004	350004	350000
872	2020-06-22	\N	350000	350013	350013	350000
739	2022-04-07	Payment on delivery	200000	200009	200009	200000
740	2022-04-08	Payment on delivery	200000	200009	200009	200000
741	2022-04-08	Payment on delivery	200000	200009	200009	200000
742	2022-04-12	80% Advance & 20% Before Delivery	350000	350014	350014	350000
743	2022-04-13	70% Payment With PO Advance and the balance 30% on delivery	350000	350014	350014	350000
745	2022-04-13	70% Payment with PO Advance and the balance 30% on delivery	350000	350014	350014	350000
746	2022-04-13	\N	160000	\N	200008	200000
747	2022-04-13	\N	160000	\N	200007	200000
750	2022-04-14	100% Payment after delivery	350000	350014	350014	350000
774	2022-04-18	Payment on delivery	200000	200009	200009	200000
777	2022-04-21	100% payment.	350000	350004	350004	350000
778	2022-04-21	100% payment.\r\nRemarks:\r\nEx-post fecto approval for TA/DA of above mentioned individuals is requested.	350000	350004	350004	350000
779	2022-05-11	Payment on delivery	200000	200009	200009	200000
780	2022-04-25	100% Payment upon delivery	350000	350018	350018	350000
781	2022-04-25	Toyota Corolla GLI/XLI on 17,18,21,22,24,25,28,30, 31 Mar and 1 Apr and Hiace on 16,17,22 and 24 Mar	200000	200004	200004	200000
782	2022-05-11	Toyota Corolla GLI/XLI for 4,5,6, 12,13 and 14 Apr and Hiace for 25 Apr, 2022	200000	200004	200004	200000
783	2022-04-26	Payment on Delivery	200000	200009	200009	200000
784	2022-05-11	\N	200000	200004	200004	200000
785	2022-04-27	\N	300000	300003	300003	300000
786	2022-04-27	\N	300000	300003	300003	300000
787	2022-04-28	100% Payment upon delivery	350000	350014	350014	350000
788	2022-04-28	100% Payment upon delivery	350000	350003	350003	350000
789	2022-04-28	75% in Advance and remaing 25% payment against delivery of Stores	350000	350003	350003	350000
790	2022-04-28	75% in Advance and remaining 25% payment against delivery of Stores	350000	350003	350003	350000
792	2022-05-10	100% payment	350000	350004	350004	350000
793	2022-05-11	100% payment\r\nRemarks:\r\nEx-post facto approval for TA/DA of above mentioned individuals is requested.	350000	350004	350004	350000
795	2022-05-12	100% Payment upon delivery	350000	350018	350018	350000
797	2022-05-12	100% Payment upon delivery	350000	350018	350018	350000
799	2022-05-13	75% in advance and balance 25% on delivery	350000	350006	350006	350000
874	2020-09-02	\N	350000	350013	350013	350000
800	2022-05-13	70% Payment with PO advance and the balance 30% on delivery	350000	350006	350006	350000
804	2022-05-13	100% payment on delivery	350000	350005	350005	350000
811	2022-05-24	\N	200000	200004	200004	200000
812	2022-06-09	Approval for TA/DA for 02 days (from 30 May 22 - 31 May 22 ) of above mentioned individual is requested.	350000	350010	350010	350000
813	2022-05-25	Toyota Corolla GLI/XLI for 19,20,23,24,25 and 26 May	200000	200004	200004	200000
814	2022-05-27	\N	300000	300003	300003	300000
816	2022-05-27	\N	300000	300003	300003	300000
819	2022-06-09	Ex-post facto approval for TA/DA for 04 days (from 26 May 22 - 29 May 22 ) of above mentioned individual is requested.	350000	350010	350010	350000
820	2022-06-06	100% at completion of work	350000	350014	350014	350000
821	2022-11-15	\N	300000	300003	300003	300000
822	2022-06-07	100% payment upon delivery	350000	350014	350014	350000
823	2022-06-09	Hiace required on 23, 24, 25 and 26 May 22	200000	200004	200004	200000
824	2022-06-09	02 Toyota Corolla GLI/XLI required 2, 7 , 8 and 9 June 22 and 01 Toyota Corolla GLI/XLI required for 3 June 22	200000	200004	200004	200000
825	2022-06-09	\N	200000	200007	200007	200000
826	2022-06-09	\N	200000	200007	200007	200000
827	2022-06-09	75% advance 25% on delivery	350000	350014	350014	350000
828	2022-06-17	Approval for TA/DA for 02 days (from 13 Jun - 14 Jun 22) of above mentioned individuals is requested.	350000	350010	350010	350000
829	2022-06-13	Payment on delivery	200000	200008	200008	200000
830	2022-06-13	Payment on delivery	200000	200004	200004	200000
807	2022-05-24	\N	300000	300003	300003	300000
831	2022-06-13	Payment on delivery	200000	200004	200004	200000
832	2022-06-16	Lunch on 19, 23-26 May and 2, 7-10 Jun 22	200000	200004	200004	200000
833	2022-06-16	02 Toyota Corolla GLI/XLI required on 10 Jun 22 and 01 Toyota Corolla GLI/XLI required on 14,15,16 and 17 Jun 22	200000	200004	200004	200000
834	2022-06-22	100% Payment after delivery	350000	350018	350018	350000
835	2022-06-27	Toyota Corolla GLI/XLI required on 21, 22, 23, 28 Jun, 22	200000	200004	200004	200000
836	2022-06-28	100% payment after delivery	350000	350003	350003	350000
837	2022-06-28	100% Payment after delivery	350000	350014	350014	350000
838	2022-06-28	100% payment on delivery	350000	350003	350003	350000
873	2020-07-03	\N	350000	350013	350013	350000
839	2022-07-05	Approval of TA/DA for 2 days(from 25 Jun - 26 Jun 22) of above mentioned individual is requested. If approved then endorsement of signature on TY duty forms enclosed at Flag AQ is also requested, please.	350000	350010	350010	350000
840	2022-06-30	100% Payment after delivery	350000	350005	350005	350000
841	2022-07-01	100% against delivery	350000	350005	350005	350000
842	2022-07-04	Payment on delivery of stoes	200000	200009	200009	200000
843	2022-07-05	\N	300000	300003	300003	300000
844	2022-07-05	\N	300000	300003	300003	300000
845	2022-09-19	\N	300000	300003	300003	300000
847	2022-07-05	100% payment upon delivery of stores	400000	400002	400002	400000
848	2022-07-21	100 % Payment upon delivery of stores	400000	400002	400002	400000
849	2022-07-06	70% Payment with PO (Advance) and the balance 30% on delivery	350000	350010	350010	350000
850	2022-07-06	100% payment upon delivery	350000	350010	350010	350000
852	2022-07-18	70% Payment with Po Advance and the balance 30% on delivery	350000	350015	350015	350000
853	2022-07-20	100% Payment after delivery	350000	350015	350015	350000
854	2022-07-22	100% Payment after delivery	350000	350006	350006	350000
855	2022-07-28	01 BRV required on 18,19,20, and 21 July 22 and 01 Hiace required on  18,19,20, and 21 July 22	200000	200004	200004	200000
856	2022-07-28	Lunch on 18,19,20 and 21 July 22	200000	200004	200004	200000
857	2022-08-05	\N	300000	300001	300001	300000
858	2022-08-05	\N	300000	300001	300001	300000
859	2022-08-10	Payment on delivery	200000	200009	200009	200000
860	2022-08-10	Payment on delivery	200000	200009	200009	200000
861	2022-08-10	Payment on delivery	200000	200008	200008	200000
862	2022-08-10	50% Payment advanced and 50% on delivery of stores	200000	200009	200009	200000
863	2022-08-10	100% payment upon delivery	350000	350014	350014	350000
864	2022-08-18	100% Payment upon delivery	350000	350006	350006	350000
869	2022-08-22	\N	200000	200004	200004	200000
870	2022-08-22	100% after delivery	350000	350015	350015	350000
871	2022-08-22	100% upon delivery	350000	350015	350015	350000
875	2020-07-20	\N	350000	350013	350013	350000
876	2020-08-24	\N	350000	350013	350013	350000
877	2020-10-05	\N	350000	350013	350013	350000
878	2020-07-03	\N	350000	350013	350013	350000
879	2020-11-03	\N	350000	350009	350009	350000
880	2021-02-17	\N	350000	350009	350009	350000
881	2020-07-20	\N	350000	350013	350013	350000
882	2021-02-17	\N	350000	350013	350013	350000
883	2021-03-24	\N	350000	350009	350009	350000
884	2021-05-25	\N	350000	350013	350013	350000
885	2020-08-31	\N	350000	350009	350009	350000
886	2020-06-02	\N	350000	350013	350013	350000
887	2020-06-02	\N	350000	350013	350013	350000
888	2020-06-02	\N	350000	350013	350013	350000
889	2021-02-01	\N	350000	350013	350013	350000
890	2021-02-01	\N	350000	350013	350013	350000
891	2020-06-02	\N	350000	350013	350013	350000
892	2020-06-02	\N	350000	350013	350013	350000
893	2021-02-01	\N	350000	350013	350013	350000
894	2021-02-01	\N	350000	350013	350013	350000
895	2020-06-19	\N	350000	350013	350013	350000
896	2022-03-02	\N	350000	350013	350013	350000
897	2022-03-02	\N	350000	350013	350013	350000
898	2020-09-09	\N	350000	350009	350009	350000
899	2020-10-14	\N	350000	350009	350009	350000
900	2021-02-08	\N	350000	350009	350009	350000
901	2021-03-08	\N	350000	350009	350009	350000
902	2021-06-07	\N	350000	350009	350009	350000
903	2021-09-30	\N	350000	350009	350009	350000
904	2021-10-18	\N	350000	350009	350009	350000
905	2021-12-17	\N	350000	350009	350009	350000
906	2021-02-10	\N	350000	350013	350013	350000
907	2020-08-24	\N	350000	350013	350013	350000
908	2020-09-09	\N	350000	350013	350013	350000
909	2020-10-22	\N	350000	350013	350013	350000
910	2021-09-28	\N	350000	350013	350013	350000
911	2021-09-28	\N	350000	350013	350013	350000
912	2022-02-11	\N	350000	350013	350013	350000
913	2022-02-10	\N	350000	350013	350013	350000
914	2022-03-06	\N	350000	350013	350013	350000
915	2022-03-14	\N	350000	350013	350013	350000
916	2020-10-05	\N	350000	350009	350009	350000
917	2021-06-24	\N	350000	350013	350013	350000
918	2020-07-23	\N	350000	350013	350013	350000
919	2020-09-10	\N	350000	350013	350013	350000
920	2020-10-09	\N	350000	350013	350013	350000
921	2021-01-28	\N	350000	350013	350013	350000
922	2020-07-08	\N	350000	350009	350009	350000
923	2020-07-08	\N	350000	350009	350009	350000
924	2020-07-08	\N	350000	350009	350009	350000
925	2020-06-29	\N	350000	350009	350009	350000
926	2020-07-01	\N	350000	350009	350009	350000
927	2020-10-02	\N	350000	350009	350009	350000
928	2020-11-17	\N	350000	350009	350009	350000
929	2020-12-17	\N	350000	350009	350009	350000
930	2021-01-04	\N	350000	350009	350009	350000
931	2021-01-26	\N	350000	350009	350009	350000
932	2021-02-01	\N	350000	350009	350009	350000
933	2021-10-22	\N	350000	350009	350009	350000
934	2022-04-25	\N	350000	350009	350009	350000
935	2022-05-26	\N	350000	350009	350009	350000
936	2020-08-31	\N	350000	350009	350009	350000
937	2020-11-18	\N	350000	350009	350009	350000
938	2022-04-25	\N	350000	350009	350009	350000
939	2022-05-26	\N	350000	350009	350009	350000
1054	2022-11-25	100%	350000	350013	350013	350000
940	2020-06-05	\N	350000	350013	350013	350000
941	2022-02-17	\N	350000	350013	350013	350000
942	2020-06-25	\N	350000	350009	350009	350000
943	2020-08-10	\N	350000	350009	350009	350000
944	2020-08-18	\N	350000	350009	350009	350000
945	2020-10-13	\N	350000	350009	350009	350000
946	2020-11-18	\N	350000	350009	350009	350000
947	2020-12-29	\N	350000	350009	350009	350000
948	2021-05-25	\N	350000	350009	350009	350000
949	2021-10-22	\N	350000	350009	350009	350000
950	2021-12-24	\N	350000	350009	350009	350000
951	2020-09-02	\N	350000	350009	350009	350000
952	2022-04-25	\N	350000	350009	350009	350000
953	2022-05-26	\N	350000	350009	350009	350000
954	2021-09-06	\N	350000	350009	350009	350000
955	2020-08-17	\N	350000	350013	350013	350000
956	2020-09-07	\N	350000	350013	350013	350000
957	2021-03-24	\N	350000	350013	350013	350000
958	2022-02-10	\N	350000	350013	350013	350000
959	2022-02-17	\N	350000	350013	350013	350000
960	2022-03-02	\N	350000	350013	350013	350000
961	2021-06-24	\N	350000	350013	350013	350000
962	2020-10-19	\N	350000	350013	350013	350000
963	2020-06-25	\N	350000	350013	350013	350000
964	2020-07-01	\N	350000	350013	350013	350000
965	2020-09-09	\N	350000	350013	350013	350000
966	2020-11-18	\N	350000	350013	350013	350000
967	2021-01-25	\N	350000	350013	350013	350000
968	2022-02-17	\N	350000	350013	350013	350000
969	2021-06-24	\N	350000	350013	350013	350000
970	2022-02-17	\N	350000	350013	350013	350000
971	2021-03-15	\N	350000	350013	350013	350000
972	2022-02-10	\N	350000	350013	350013	350000
973	2022-02-17	\N	350000	350013	350013	350000
974	2022-08-30	\N	300000	300003	300003	300000
975	2022-09-05	70% payment with PO (advance) and balance 30% on delivery	350000	350015	350015	350000
976	2022-09-06	100% payment upon delivery	350000	350015	350015	350000
977	2022-09-07	\N	300000	300003	300003	300000
1055	2022-11-25	\N	300000	300003	300003	300000
164	2020-09-20	\N	350000	350002	350002	350000
165	2020-09-20	\N	350000	350002	350002	350000
978	2022-09-08	100% Payment upon delivery	350000	350014	350014	350000
979	2022-09-08	85% in  advance and remaing 15% payment against delivery of stores	350000	350014	350014	350000
980	2022-09-08	85% in advance and remain	350000	350014	350014	350000
981	2022-09-09	100% payment upon delivery	350000	350005	350005	350000
983	2022-10-07	100% at completion of work	350000	350010	350010	350000
984	2022-09-12	\N	300000	300003	300003	300000
985	2022-09-12	\N	300000	300003	300003	300000
987	2022-09-12	\N	300000	300003	300003	300000
988	2022-11-17	80% Advance\r\n20% On Delivery	300000	300003	300003	300000
989	2022-11-17	80% Advance\r\n20% On delivery	300000	300003	300003	300000
990	2022-09-15	\N	300000	300003	300003	300000
991	2022-09-16	100% payment upon delivery	350000	350018	350018	350000
992	2022-10-05	Apropos, TA/DA charges amounting Rs. 82,500/- (Rupees Eighty Two Thousand Five Hundred Only) in total may please be approved for respective individuals from project funds at M/s MTSS.	350000	350013	350013	350000
993	2022-09-22	100% payment upon delivery	350000	350015	350015	350000
994	2022-09-23	75% advance & 25% upon completion	350000	350014	350014	350000
995	2022-09-26	Toyota Corolla GLI/XLI required on 4,5,29,30,31 Aug and 1,3,6,7,8,12,13,14 and 15 Sep 22	200000	200004	200004	200000
996	2022-09-26	Toyota Corolla GLI/XLI required on 4,5,29,30,31 Aug and 1,3,6,7,8,12,13,14 and 15 Sep 22. 02 x vehicles were used on the 29th Aug.	200000	200004	200004	200000
997	2022-09-26	\N	200000	200009	200009	200000
998	2022-10-04	payment on delivery	200000	200009	200009	200000
999	2022-10-04	payment on delivery	200000	200009	200009	200000
1001	2022-10-05	70% advance payment and 30% after shipment through crossed cheque in favor of DTS	350000	350014	350014	350000
1004	2022-10-06	100% payment upon delivery	350000	350006	350006	350000
1005	2022-10-07	100% payment upon delivery	350000	350018	350018	350000
205	2020-06-09	\N	200000	200003	200003	200000
470	2021-05-21	\N	350000	350005	350005	350000
474	2021-06-07	\N	350000	350005	350005	350000
1006	2022-10-07	100% payment upon delivery	350000	350015	350015	350000
1007	2022-10-10	100% payment upon delivery	350000	350002	350002	350000
1008	2022-10-10	75% advance and 25% after completion of Work	350000	350013	350013	350000
986	2022-11-17	70% Advance \r\n15% Work completion \r\n15% Completion/ Finishing of Project	300000	300003	300003	300000
1009	2022-10-11	100% payment upon delivery	350000	350004	350004	350000
1010	2022-10-11	100% payment upon delivery	350000	350004	350004	350000
1011	2022-10-12	100% on delivery	350000	350002	350002	350000
1012	2022-11-17	70% Advance \r\n15% Work completion \r\n15% Completion/ Finishing of Project	300000	300003	300003	300000
1013	2022-11-04	Ex-post facto approval for TA/DA of above mentioned individual is requested.	350000	350010	350010	350000
1014	2022-10-19	100% payment upon delivery	350000	350014	350014	350000
1016	2022-11-04	Ex-post facto approval for TA/DA of above mentioned individual is requested.	350000	350010	350010	350000
1017	2022-10-26	\N	300000	300003	300003	300000
1020	2022-10-27	100% payment upon delivery	350000	350014	350014	350000
1021	2022-10-28	100% payment upon delivery	350000	350014	350014	350000
1022	2022-11-03	Payment on delivery	200000	200009	200009	200000
1023	2022-11-03	Payment on delivery	200000	200006	200006	200000
1024	2022-11-08	Payment on delivery	200000	200007	200007	200000
1025	2022-11-15	\N	300000	300003	300003	300000
1026	2022-11-16	100% payment upon delivery	350000	350014	350014	350000
1027	2022-11-16	100% payment upon delivery	350000	350014	350014	350000
1028	2022-11-16	70% advance payment and 30% after shipment	350000	350014	350014	350000
1032	2022-12-20	Ex-post facto approval for TA/DA of above mentioned individuals deployed in ORMARA vide letter no. A.  PN/R AND D WING/ GENERAL/ 1110643 (SECRET), is requested.	350000	350014	350014	350000
1033	2022-12-20	Ex-post facto approval for TA/DA of above mentioned individuals deployed in TURBAT vide letter no. PN/HQ COMPAK/ GENERAL/ 1118491 (SECRET), is requested.	350000	350014	350014	350000
174	2020-06-11	Power Lab. Paid by cheque 10265593	450000	450002	160001	160000
1034	2022-11-18	Payment on delivery	200000	200009	200009	200000
1036	2022-12-06	Approval for TA/DA of above mentioned individual is requested.	350000	350010	350010	350000
1038	2022-12-01	Payment on delivery	200000	200011	200011	200000
1039	2022-12-01	Payment on delivery	200000	200011	200011	200000
1040	2022-12-01	Paymnt on delivery	200000	200011	200011	200000
1041	2022-11-21	\N	200000	200004	200004	200000
1042	2022-11-22	67% Advance\r\n33% After completion of work	300000	300003	300003	300000
1043	2022-11-22	\N	300000	300003	300003	300000
1045	2022-11-22	\N	300000	300003	300003	300000
1046	2022-11-22	\N	300000	300003	300003	300000
1047	2022-11-21	100% payment upon delivery	350000	350006	350006	350000
1048	2022-11-21	100% payment upon delivery	350000	350006	350006	350000
1049	2022-12-02	Toyota Corolla GLI/XLI required on 27,28,29,30 Sep 22 & 14  Oct 22 and 02x vehicles were used on the 10,11,12,13 Oct 22	200000	200004	200004	200000
1050	2022-12-02	Lunch on 29 Aug, 1 Sep, and 6,10,11,12 and 13 Oct	200000	200004	200004	200000
1053	2022-12-23	Payment on delivery	200000	200004	200004	200000
1056	2022-12-26	payment on delivery	200000	200011	200011	200000
1057	2022-12-01	50% advance payment	200000	200007	200007	200000
1059	2022-12-01	Payment on delivery	200000	200011	200011	200000
1063	2022-12-07	100% payment upon delivery	350000	350014	350014	350000
1064	2022-12-09	100% payment upon delivery	350000	350014	350014	350000
1066	2022-12-13	Toyota Corolla GLI/XLI required on 3-7 Oct 22, 17-21 Oct 22 and 25-28 Oct 22, and BRV required on 6 and 18 Oct 22	200000	200004	200004	200000
1067	2022-12-15	100% Payment upon delivery	350000	350015	350015	350000
1068	2022-12-15	75% Advance and 25% on completion	350000	350013	350013	350000
1069	2022-12-16	100% Payment upon delivery	350000	350014	350014	350000
1070	2022-12-21	01 x Toyota Corolla GLI/XLI required 31 Oct, 2,3,4,7,8,10,11,14,17,21-24 Nov, and 02 x  Toyota Corolla GLI/XLI required on 01 Nov,22	200000	200004	200004	200000
1071	2022-12-22	Payment on delivery	200000	200004	200004	200000
1072	2022-12-26	Payment on delivery	200000	200008	200008	200000
1073	2022-12-27	Payment on delivery/utilization	200000	200004	200004	200000
1074	2022-12-27	Lunch on 17,18,19 and 27 Oct and 1,10 and 29 Nov and 1 Dec 2022	200000	200004	200004	200000
1075	2022-12-28	\N	300000	300003	300003	300000
1077	2022-12-28	Payment on Delivery	200000	200004	200004	200000
1078	2022-12-28	Advance 80%\r\n20% After Completion of work	300000	300003	300003	300000
1052	2022-11-23	\N	160000	\N	160001	160000
1060	2022-12-01	\N	160000	\N	160001	160000
1079	2022-12-29	25% on issuance of PO signing of agreement, 25% for each of the 03 x milestones	200000	200012	200012	200000
1080	2023-01-23	payment on delivery	200000	200008	200008	200000
1083	2023-01-06	70% Advance Payment and 30% on delivery	350000	350015	350015	350000
1084	2023-01-06	100% Payment After delivery	350000	350015	350015	350000
1085	2023-01-12	Payment on delivery	200000	200007	200007	200000
1086	2023-01-12	Payment on delivery	200000	200004	200004	200000
1087	2023-01-23	Payment on delivery	200000	200004	200004	200000
1088	2023-01-12	100% Upon delivery	350000	350013	350013	350000
1089	2023-01-13	100% upon delivery	350000	350014	350014	350000
1090	2023-01-17	70% Advance\r\n30% After Completion	300000	300003	300003	300000
1091	2023-01-23	Payment on delivery	200000	200004	200004	200000
1092	2023-01-23	Payment on delivery/utlization	200000	200004	200004	200000
1093	2023-01-24	Payment on Delivery	200000	200012	200012	200000
1094	2023-02-03	100% Payement                                                                                                                                                             Remarks: Ex-post facto approval for TADA of above mentioned individuals is requested.	350000	350014	350014	350000
1096	2023-01-24	\N	350000	350013	350013	350000
1097	2023-01-24	100% Payment Upon delivery	350000	350015	350015	350000
1098	2023-03-16	Payment on delivery	200000	200009	200009	200000
1099	2023-01-31	NIL	350000	350014	350014	350000
1100	2023-01-31	NIL	350000	350014	350014	350000
1101	2023-02-10	100% payment upon delivery	350000	350015	350015	350000
1102	2023-02-10	100% Payment upon delivery	350000	350014	350014	350000
1103	2023-02-14	100%  payment after delivery	300000	300003	300003	300000
1104	2023-02-20	Payment on delivery	200000	200011	200011	200000
1105	2023-02-21	50% advance payment	200000	200011	200011	200000
1106	2023-03-01	100% payment after delivery	300000	300003	300003	300000
1108	2023-03-06	\N	350000	350002	350002	350000
1109	2023-03-06	100% Payment upon delivery	350000	350014	350014	350000
1112	2023-03-30	Ex-post facto approval for TA/DA of above mentioned individual deployed in TURBAT for operationalization of GTRS Station at NAS TURBAT	350000	350010	350010	350000
1113	2023-03-14	100% payment after delivery	300000	300003	300003	300000
1114	2023-03-16	100% payment after delivery	300000	300003	300003	300000
1115	2023-03-20	100% payment on delivery	300000	300003	300003	300000
1116	2023-03-21	Payment on delivery	200000	200008	200008	200000
1117	2023-03-21	Payment on dilevery	200000	200004	200004	200000
1118	2023-03-21	Payment on delivery/utilization	200000	200008	200008	200000
1119	2023-03-31	100% Payment upon delivery	350000	350006	350006	350000
1121	2023-03-27	100% Payment upon delivery	350000	350022	350022	350000
1122	2023-03-28	Payment on delivery	200000	200008	200008	200000
1123	2023-03-28	Payment on delivery	200000	200004	200004	200000
1124	2023-03-30	100% payment after delivery	300000	300003	300003	300000
1125	2023-03-31	100% payment after delivery	300000	300003	300003	300000
1127	2023-04-10	100% payment after completion of work	160000	\N	200009	200000
1155	2023-05-16	100% payment after completion of work	300000	300003	300003	300000
1128	2023-04-10	100% payment after completion of work	160000	\N	350018	350000
1129	2023-04-05	100% Payment upon delivery	350000	350010	350010	350000
1130	2023-04-07	100% Payment upon delivery	350000	350006	350006	350000
1131	2023-04-10	100% payment after completion of work	160000	\N	350010	350000
1132	2023-04-11	100% Payment upon delivery	350000	350020	350020	350000
163	2020-08-20	\N	350000	350002	350002	350000
252	2021-01-21	\N	200000	200004	200004	200000
507	2021-08-06	\N	200000	200004	200004	200000
1133	2021-12-02	\N	160000	\N	160001	160000
1134	2022-01-15	\N	160000	\N	160001	160000
1135	2022-02-11	\N	160000	\N	160001	160000
1136	2022-06-06	\N	450000	450002	160001	160000
1137	2022-10-18	\N	160000	\N	160001	160000
1138	2022-11-22	\N	160000	\N	160001	160000
1139	2022-10-18	\N	160000	\N	160001	160000
1140	2022-11-14	\N	160000	\N	160001	160000
1141	2023-01-09	\N	160000	\N	160001	160000
1142	2023-04-12	100% Payment upon delivery	350000	350019	350019	350000
1143	2023-04-13	100% Payment on delivery	350000	350002	350002	350000
1144	2023-04-13	100% payment after completion of work	300000	300003	300003	300000
1145	2023-04-13	50% Advance & 50% after delivery	350000	350019	350019	350000
1146	2023-04-17	100% payment after completion of work	300000	300003	300003	300000
1147	2023-04-19	100% payment after delivery & completion of work	300000	300003	300003	300000
1148	2023-04-26	100% Payment upon delivery	350000	350022	350022	350000
1149	2023-05-04	100% Payment upon delivery	350000	350010	350010	350000
1150	2023-05-11	100% after delivery	350000	350013	350013	350000
1151	2023-05-11	Payment Terms 100% on completion	350000	350013	350013	350000
1152	2023-05-09	100% payment after delivery.	350000	350023	350023	350000
1153	2023-05-12	100% payment after delivery	350000	350020	350020	350000
1154	2023-05-15	100% payment on completion of work	300000	300003	300003	300000
1156	2023-05-16	100% Payment after delivery	350000	350014	350014	350000
1157	2023-05-18	100% payment after delivery	300000	300003	300003	300000
1158	2023-05-16	100% payment after delivery	300000	300003	300003	300000
1159	2023-05-18	100% Payment after delivery	350000	350024	350024	350000
1160	2023-05-18	70% Advance & 30% after delivery	350000	350019	350019	350000
1161	2023-05-19	Payment on delivery	200000	200008	200008	200000
1162	2023-05-19	Payment on delivery/utilization	200000	200008	200008	200000
1163	2023-05-19	100% Payment after delivery	350000	350022	350022	350000
1164	2023-05-19	Lunch on 14 and 15 feb 2023	200000	200008	200008	200000
1165	2023-05-25	100% on completion of work	350000	350014	350014	350000
1166	2023-05-29	100% payment after delivery	300000	300003	300003	300000
1167	2023-05-30	100% Payment after delivery	350000	350020	350020	350000
1168	2023-06-01	100% Payment after delivery	350000	350021	350021	350000
1171	2023-06-16	100% Payment after delivery	350000	350020	350020	350000
1172	2023-06-16	100% payment after delivery	300000	300005	300005	300000
1173	2023-06-16	100% payment after completion of work	300000	300003	300003	300000
1174	2023-06-19	Ex-post facto approval for TA/DA of mentioned individual deployed in Gwadar for operationalization of GTRS Station at PNS Akram	350000	350013	350013	350000
1175	2023-06-19	50% advance (without frieght) with PO and balance 50% (plus frieght) on delivery.	350000	350026	350026	350000
1176	2023-06-19	100% Payment after delivery	350000	350022	350022	350000
1177	2023-06-20	Payment on dilevery	200000	200008	200008	200000
1178	2023-06-20	50% advance & 50% against delivery of Stores	350000	350025	350025	350000
1179	2023-06-21	50% advance, 50% on completion.	350000	350013	350013	350000
1181	2023-06-21	50% down payment for Production. 50% shall be paid on delivery	350000	350025	350025	350000
1183	2023-06-21	Lunch on 31 May, 2,7,8,13 and 14 June 2023	200000	200004	200004	200000
1184	2023-06-27	100% payment after delivery	350000	350023	350023	350000
1185	2023-07-04	100% payment after delivery	350000	350020	350020	350000
1186	2023-07-07	100% payment after completion of work	160000	\N	200003	200000
1338	2023-12-05	Payment on dilivery	200000	200007	200007	200000
1187	2023-07-07	100% payment after completion of work	160000	\N	200001	200000
1188	2023-07-10	100% payment after completion of work	160000	\N	350005	350000
441	2019-02-28	\N	350000	350002	160001	160000
510	2021-09-09	\N	350000	350005	350005	350000
1189	2023-07-10	\N	300000	300005	300005	300000
1190	2023-07-10	100% payment after delivery and completion of work	300000	300005	300005	300000
1192	2023-07-10	100% payment after delivery and completion of work	160000	\N	350009	350000
1193	2023-07-10	100% payment after delivery and completion of work	160000	\N	350015	350000
1194	2023-07-11	100% payment after delivery	350000	350028	350028	350000
1195	2023-07-11	50% advance with PO, 50% on completion of project	350000	350028	350028	350000
1196	2023-07-12	Payment on dilivery	200000	200009	200009	200000
1197	2023-07-12	Payment on dilivery	200000	200009	200009	200000
1198	2023-07-13	Payment on dilivery	200000	200004	200004	200000
1199	2023-07-13	100% payment after delivery	300000	300005	300005	300000
1200	2023-07-13	Payment on dilivery	200000	200004	200004	200000
1202	2023-07-14	Payment on dilivery	200000	200004	200004	200000
1203	2023-07-14	100% payment after delivery	350000	350021	350021	350000
1204	2023-07-14	100% advavce payment	200000	200004	200004	200000
1205	2023-07-14	Payment on dilivery	200000	200004	200004	200000
1206	2023-07-18	100% payment after delivery	350000	350014	350014	350000
1208	2023-08-18	Ex-post facto approval for TA/DA of above mentioned individuals deployed in MAKRAN for acceptance trials	350000	350023	350023	350000
1209	2023-07-27	70% advance and 30% on delivery	350000	350025	350025	350000
1210	2023-08-08	Payment to be made 100% after delivery of items	450000	450002	450002	450000
1212	2023-08-01	\N	350000	350022	350022	350000
1213	2023-06-06	\N	450000	450002	450002	450000
1214	2023-08-02	Payment on delivery/utiliztion	200000	200004	200004	200000
1216	2023-08-02	70% advance and 30% after delivery.	350000	350010	350010	350000
1217	2023-08-02	50% advance with PO, 50% on completion of project.	350000	350010	350010	350000
1218	2023-08-02	100% payment after delivery.	350000	350010	350010	350000
1219	2023-10-26	Ex-post facto approval for TA/DA of mentioned individual deployed in  TURBAT for Acceptance Test Procedues (ATPs) at PNS SIDDIQ	350000	350013	350013	350000
1220	2023-08-04	100% payment after delivery	350000	350029	350029	350000
1221	2023-11-20	100% payment after delivery & completion of work	300000	300005	300005	300000
1225	2023-10-26	Ex-post facto approval for TA/DA of mentioned individuals deployed in ORMARA/ PNS AKRAM for Acceptance Test Procedures (ATPs) at Mianwali/ Gwadar	350000	350013	350013	350000
1226	2023-08-15	Payment on dilevery	200000	200004	200004	200000
1227	2023-08-10	100% payment after delivery	350000	350027	350027	350000
1228	2023-08-25	100% payment after delivery	350000	350014	350014	350000
1230	2023-08-28	100% payment after delivery	350000	350028	350028	350000
1231	2023-08-28	\N	450000	450002	450002	450000
1232	2023-08-28	100% payment after delivery	300000	300005	300005	300000
1233	2023-08-29	\N	450000	450002	450002	450000
1235	2023-08-30	100% payment after delivery	350000	350030	350030	350000
1236	2023-08-30	100% payment after delivery & completion of work	300000	300005	300005	300000
1237	2023-08-31	100% payment after delivery	350000	350020	350020	350000
1238	2023-09-07	35% advance, 35% after 3 months upon submission of technical documentation and 30% on delivery stores.	350000	350031	350031	350000
1239	2023-09-05	payment on dilivery	200000	200004	200004	200000
1240	2023-09-05	Payment on dilivery	200000	200008	200008	200000
1241	2023-09-05	Payment on dilivery	200000	200008	200008	200000
1243	2023-09-07	100% Payment after provision of new chairs and after complete repairing of old damaged chairs	160000	\N	450002	450000
1244	2023-09-07	100% payment after completion of works	160000	\N	350020	350000
1245	2023-09-08	100% payment after delivery	350000	350029	350029	350000
1247	2023-09-12	100 % payment after delivery	350000	350030	350030	350000
1248	2023-09-11	100 % payment after delivery	350000	350022	350022	350000
1252	2023-08-28	100% payment after delivery	160000	\N	450002	450000
1253	2023-09-13	100% payment after delivery	350000	350014	350014	350000
1254	2023-09-13	100% payment after delivery	350000	350025	350025	350000
1255	2023-09-19	Ex-post facto approval for TA/DA of above mentioned individuals deployed in PASNI for field acceptance trials of Dish Antenna.	350000	350022	350022	350000
1256	2023-09-18	100% pyment after delivery	350000	350028	350028	350000
1257	2023-09-18	100% payment after delivery	350000	350023	350023	350000
1258	2023-09-22	Payment on Dilivery	200000	200008	200008	200000
1259	2023-09-22	Payment on dilivery	200000	200011	200011	200000
1260	2023-09-22	Payment on dilivery	200000	200011	200011	200000
1261	2023-09-18	Payment on dilivery	200000	200011	200011	200000
1262	2023-09-22	Payment on dilivery	200000	200004	200004	200000
1263	2023-09-19	100% payment after delivery	300000	300005	300005	300000
1265	2023-09-20	100% Payment after Delivery	450000	450002	450002	450000
1266	2023-09-25	\N	350000	350014	350014	350000
1267	2023-09-25	100% payment after delivery	350000	350014	350014	350000
1269	2023-09-27	Payment on dilivery	200000	200011	200011	200000
1264	2023-09-19	70% advance & 30% against delivery of Stores	350000	350020	350020	350000
1246	2023-09-08	Payment after delivery	160000	\N	450002	450000
1270	2023-09-27	Payment on dilivery	200000	200008	200008	200000
1271	2023-10-02	100% payment after delivery	350000	350029	350029	350000
1272	2023-10-04	\N	300000	300005	300005	300000
1274	2023-10-03	Payment on delivery/utiliztion	200000	200004	200004	200000
1276	2023-10-04	100% payment after delivery	350000	350030	350030	350000
1277	2023-10-04	50% down payment for production and 50% shall be paid on delivery	350000	350030	350030	350000
1278	2023-10-05	50% Advance\r\n50% After Delivery	300000	300006	300006	300000
1279	2023-10-10	100% payment after delivery	350000	350024	350024	350000
384	2021-02-19	\N	350000	350005	350005	350000
775	2022-04-01	\N	350000	350004	350004	350000
1280	2023-10-09	100% payment after delivery	300000	300005	300005	300000
1281	2023-10-10	100% payment after delivery	350000	350023	350023	350000
1282	2023-10-11	50% Advance\r\n50% After Delivery	300000	300005	300005	300000
1283	2023-10-11	100% payment after delivery	350000	350029	350029	350000
1284	2023-10-17	100% payment after delivery	350000	350014	350014	350000
1285	2023-10-17	100% on completion of work	350000	350014	350014	350000
1286	2023-10-18	100% payment after delivery	350000	350030	350030	350000
1287	2023-10-19	100% payment after delivery	350000	350028	350028	350000
1289	2023-10-25	100 % payment after delivery	350000	350019	350019	350000
1291	2023-10-25	\N	450000	450002	450002	450000
1292	2023-10-25	100% payment after delivery	300000	300006	300006	300000
1293	2023-10-25	100% payment after delivery	300000	300005	300005	300000
1294	2023-10-24	100% payment after completion of work	160000	\N	350015	350000
1295	2023-10-25	100% payment after completion of work and delivery of items	160000	\N	350005	350000
1296	2023-10-25	100% payment after completion of work	160000	\N	350009	350000
1290	2023-10-25	70% advance payment and 30% on delivery	350000	350019	350019	350000
1275	2023-10-04	\N	450000	450002	450002	450000
1297	2023-10-25	100% payment after provision of item and completion of work	160000	\N	350010	350000
1298	2023-10-26	100% payment after provision of goods	160000	\N	350018	350000
1299	2023-10-26	100% payment after completion of works	160000	\N	350012	350000
1300	2023-10-26	100% payment after completion of works and provision of items	160000	\N	350024	350000
1301	2023-10-26	100% payment after provision of furniture items	160000	\N	350022	350000
1302	2023-10-27	100% payment after fabrication of sheds and delievery of electrical items	160000	\N	450002	450000
1303	2023-10-30	Payment on dilivery	200000	200011	200011	200000
403	2020-02-19	\N	350000	350002	350002	350000
1304	2023-10-30	Payment on dilivery	200000	200011	200011	200000
1305	2023-10-30	Payment on dilivery	200000	200011	200011	200000
1306	2023-10-30	Payment on dilivery	200000	200008	200008	200000
1307	2023-11-03	Approval of TA/DA	350000	350014	350014	350000
1309	2023-11-08	Payment on dilivery	200000	200011	200011	200000
1310	2023-10-31	100% payment after delivery	300000	300005	300005	300000
1339	2023-12-05	100% payment after delivery	350000	350021	350021	350000
1311	2023-11-06	100 % payment after delivery	350000	350025	350025	350000
1312	2023-11-06	100% payment after delivery/ completion of work	300000	300006	300006	300000
1313	2023-11-06	100% payment after delivery	300000	300005	300005	300000
1315	2023-11-10	\N	200000	200014	200014	200000
1316	2023-11-10	100% Advance Payment	300000	300005	300005	300000
1317	2023-11-15	100% payment after delivery.	350000	350019	350019	350000
1318	2023-11-16	\N	200000	200014	200014	200000
1319	2023-11-16	Payment on delivery	200000	200014	200014	200000
1320	2023-11-22	dfsa	300000	300005	300005	300000
1321	2023-11-21	\N	300000	300005	300005	300000
1323	2023-11-22	100% Payment after delivery/ completion of work	300000	300006	300006	300000
1324	2023-11-22	100% Payment after delivery	300000	300005	300005	300000
1325	2023-11-22	100 % payment after completin of work	160000	\N	200014	200000
1326	2023-11-24	\N	300000	300005	300005	300000
1327	2023-11-22	100% payment after completion of work	160000	\N	300005	300000
1328	2023-11-22	100% payment after provision of furniture items	160000	\N	300006	300000
1329	2023-11-22	100% payment after completion of work	160000	\N	200007	200000
1330	2023-11-23	100% payment after completion of work	160000	\N	200011	200000
1331	2023-11-24	100% payment after completion of work	160000	\N	200014	200000
1333	2023-11-22	100% payment after provision of items	160000	\N	250003	250000
1334	2023-11-29	100% payment after delivery	350000	350029	350029	350000
1314	2023-11-08	Payment on delivery/utiliztion	200000	200014	200014	200000
1349	2023-12-20	75% advance & 25% against delievry of stores	350000	350025	350025	350000
531	2021-10-07	\N	300000	300001	300001	300000
539	2021-10-08	\N	200000	200004	200004	200000
546	2021-10-21	70% Advance & 30% on delivery	350000	350004	350004	350000
547	2021-12-21	50 % Advance & 50% at delivery	350000	350005	350005	350000
554	2021-11-02	\N	300000	300001	300001	300000
1335	2023-11-29	Payment on delivery/utiliztion	200000	200004	200004	200000
1336	2023-11-29	100% payment after delivery	350000	350027	350027	350000
1337	2023-11-29	100% payment after delivery	350000	350030	350030	350000
1381	2024-01-31	\N	350000	350021	350021	350000
1340	2023-12-06	70% advance & 30% against delivery of stores	350000	350014	350014	350000
1341	2023-12-07	70% advance & 30% against the delivery of stores	350000	350013	350013	350000
1342	2023-12-08	100% payment after delivery/ completion of work	300000	300005	300005	300000
1343	2023-12-12	100% payment after delivery/ completion of work	300000	300005	300005	300000
1344	2023-12-13	100 % payment after delivery	350000	350027	350027	350000
1345	2023-12-14	100% payment after delivery	300000	300006	300006	300000
1347	2023-12-14	100% payment after delivery	300000	300007	300007	300000
1351	2023-12-22	100% payment after delivery	300000	300005	300005	300000
1352	2023-12-22	100% payment after delivery	300000	300006	300006	300000
1348	2023-12-18	100% payment after delivery.	160000	\N	350002	350000
1350	2023-12-21	100% Payment after delivery	350000	350025	350025	350000
148	2020-12-24	Power Lab. Paid by cheque 10265645	450000	450002	160001	160000
218	2020-03-31	General Admin. 	160000	\N	160001	160000
1182	2023-06-21	Payment on delivery/utiliztion	200000	200004	200004	200000
1229	2023-08-28	\N	160000	\N	160001	160000
851	2022-07-13	\N	300000	300003	300003	300000
317	2019-02-28	\N	350000	350002	350002	350000
320	2019-02-28	\N	350000	350002	350002	350000
322	2020-02-19	\N	350000	350002	350002	350000
323	2020-02-19	\N	350000	350002	350002	350000
697	2022-02-24	\N	300000	300001	300001	300000
1000	2022-10-27	\N	300000	300001	300001	300000
1249	2023-08-28	\N	160000	\N	450002	450000
1360	2024-01-05	100% payment after delivery	350000	350030	350030	350000
1362	2024-01-10	100% payment after delivery	350000	350031	350031	350000
1355	2023-12-30	100% payment after delivery	350000	350019	350019	350000
1359	2024-01-03	100% payment after delivery	350000	350028	350028	350000
1268	2023-09-25	70% in advance and 30% against delivery of stores.	350000	350014	350014	350000
1353	2024-01-02	Ex-post facto approval for TA/DA of above mentioned individual for visit to KRL & HQ SPD	350000	350023	350023	350000
1357	2024-01-01	Payment on dilivery	200000	200011	200011	200000
1356	2023-12-30	100% payment after delivery	350000	350021	350021	350000
1364	2024-01-16	100% payment after delivery	350000	350021	350021	350000
1037	2024-06-26	Approval for TA/DA of above mentioned individual is requested vide letter PN/R AND D WING/GENERAL/1523926 Dated 19 Nov 22 (SECRET)	350000	350010	350010	350000
1361	2024-01-08	\N	300000	300005	300005	300000
1366	2024-01-18	100% payment after delivery.	250000	250003	250003	250000
1367	2024-02-20	100% Payment after delivery	250000	250003	250003	250000
559	2021-09-09	\N	200000	200004	200004	200000
1354	2023-12-13	\N	160000	\N	300001	300000
1368	2024-01-29	100 % payment after delivery	350000	350021	350021	350000
1358	2024-01-02	Payment on delivery/utiliztion	200000	200014	200014	200000
1363	2024-01-11	50% advance and 50% after the delivery	350000	350030	350030	350000
560	2021-10-01	\N	200000	200004	200004	200000
1288	2023-10-24	\N	450000	450002	450002	450000
1377	2024-01-29	Payment on dilivery	200000	200017	200017	200000
1376	2024-01-29	Payment on dilivery	200000	200017	200017	200000
1385	2024-02-26	Payment advance 30%	200000	200014	200014	200000
818	2022-05-27	\N	300000	300003	300003	300000
806	2022-05-24	\N	300000	300003	300003	300000
798	2022-05-24	\N	300000	300003	300003	300000
776	2022-04-20	\N	300000	300003	300003	300000
1373	2024-01-25	100% payment after delivery/completion of work	300000	300005	300005	300000
717	2022-03-16	\N	300000	300003	300003	300000
1384	2024-02-01	100% payment after delivery.	160000	\N	450002	450000
1380	2024-01-30	Payment on delivery/utiliztion	200000	200014	200014	200000
1388	2024-02-07	100% after delivery	350000	350021	350021	350000
1379	2024-01-30	100% payment after delivery	300000	300007	300007	300000
1391	2024-03-22	Payment on delivery	200000	200018	200018	200000
1374	2024-01-26	100% payment after delivery/ completion of work	300000	300005	300005	300000
1170	2023-06-08	50% advance payment and 50% payment after completion work	160000	\N	350018	350000
1386	2024-02-06	\N	350000	350021	350021	350000
1365	2024-01-18	100% payment after delivery of equipment	250000	250003	250003	250000
1371	2024-01-24	100% payment after delievery of brush cutter	160000	\N	300005	300000
1375	2024-01-26	\N	160000	\N	450002	450000
1378	2024-01-29	Payment on dilivery	200000	200017	200017	200000
1387	2024-02-06	100% payment after delivery	350000	350019	350019	350000
1382	2024-02-01	Payment on dilivery	200000	200004	200004	200000
1369	2024-01-23	100% payment after delivery	350000	350019	350019	350000
1389	2024-02-16	100 % payment after delivery	350000	350020	350020	350000
1383	2024-02-26	Payment on delivery	200000	200014	200014	200000
1390	2024-02-29	Payment on dilivery	200000	200014	200014	200000
1399	2024-02-29	Payment on delivery	200000	200004	200004	200000
1400	2024-02-22	Complete payment after completion of works	160000	\N	350002	350000
1273	2023-10-03	Payment on dilivery	200000	200014	200014	200000
1414	2024-03-12	Payment on delivery	200000	200018	200018	200000
1412	2024-03-07	100% payment after delivery	350000	350030	350030	350000
1408	2024-03-11	Ex-post facto approval for TA/DA of above mentioned individuals deployed in RDS Mianwali for Live Weapon Firing	350000	350013	350013	350000
1405	2024-02-29	Payment on dlivery	200000	200017	200017	200000
1395	2024-02-28	Payment on Dilivery	200000	200004	200004	200000
1406	2024-03-01	Payment on delivery/utiliztion	200000	200004	200004	200000
1404	2024-02-28	100% payment after delivery	350000	350019	350019	350000
1393	2024-02-26	100% Payment after delivery	160000	\N	450002	450000
1407	2024-03-11	Ex-post facto approval for TA/DA of above mentioned individual deployed in Shah Bandar for operator training for MDB flying	350000	350022	350022	350000
1410	2024-03-06	Lunch on 14,16 and 29 Nov, 13 Dec 2023. 1, 11 and 17 Jan and 2,3,21 and 22 Feb 2024	200000	200004	200004	200000
1411	2024-03-06	100% payment after delivery	300000	300007	300007	300000
1401	2024-02-26	100% payment after completion of works	160000	\N	350031	350000
1409	2024-03-05	100% payment after delivery	350000	350025	350025	350000
1398	2024-02-23	Complete payment after completion of works	160000	\N	350002	350000
1416	2024-03-21	Ex-post facto approval for TA/DA of above mentioned individual deployed at Ormara for Noise related issues of APM VFDs onboard MCMV	350000	350013	350013	350000
1402	2024-02-28	100% payment after delivery	350000	350019	350019	350000
1396	2024-02-26	Payment on dilivery	200000	200018	200018	200000
1392	2024-02-27	100% payment after delivery	350000	350028	350028	350000
1418	2024-03-25	Payment on delivery	200000	200018	200018	200000
1417	2024-03-21	100% payment after completion of job	300000	300005	300005	300000
1415	2024-03-12	100% payment after delivery	350000	350021	350021	350000
1413	2024-03-11	Payment on delivery	200000	200014	200014	200000
1419	2024-03-25	Payment on delivery/utiliztion	200000	200014	200014	200000
1425	2024-04-05	100% Payment after delivery	300000	300007	300007	300000
1427	2024-04-05	100% Payment after delivery	300000	300005	300005	300000
1421	2024-02-28	\N	300000	300005	300005	300000
1424	2024-04-05	100% Payment after Delivery	300000	300006	300006	300000
1423	2024-03-06	\N	200000	200004	200004	200000
1438	2024-04-30	\N	350000	350014	350014	350000
1431	2024-04-15	100% payment after delivery	350000	350020	350020	350000
1430	2024-04-08	100% payment after delivery	300000	300006	300006	300000
1429	2024-04-08	100% payment after delivery	300000	300005	300005	300000
1428	2024-04-08	100% Payment after delivery	300000	300007	300007	300000
1420	2024-03-27	Complete monthly payment.	160000	\N	450002	450000
1394	2024-02-26	Payment on Dilivery	200000	200017	200017	200000
1442	2024-05-06	100% payment after delivery	300000	300005	300005	300000
1439	2024-04-30	Payment on delivery/utiliztion	200000	200014	200014	200000
1436	2024-04-22	70% advance and 30% after delivery of stores	350000	350020	350020	350000
1426	2024-04-05	100% payment after delivery	350000	350013	350013	350000
1422	2024-03-28	100% payment after delivery	350000	350026	350026	350000
1432	2024-04-15	70% payment advance and 30% on delivery	350000	350014	350014	350000
1447	2024-05-09	100% payment after delivery	300000	300007	300007	300000
1443	2024-05-07	100% payment after delivery	350000	350031	350031	350000
1435	2024-04-19	100% payment after delivery	350000	350021	350021	350000
1446	2024-05-08	Complete payment after completion of works	160000	\N	300007	300000
1433	2024-04-15	70% advance & 30% against delivery of stores	350000	350014	350014	350000
1441	2024-05-06	100% payment after delivery	300000	300007	300007	300000
1440	2024-05-06	100% payment after delivery	300000	300006	300006	300000
1437	2024-04-24	100% payment after delivery	250000	250003	250003	250000
1444	2024-05-08	Complete paytment after completion of works	160000	\N	300007	300000
1456	2024-05-17	Payment on delivery	200000	200018	200018	200000
1449	2024-05-09	100% payment after delivery	300000	300005	300005	300000
1455	2024-05-17	Payment on Delivery	200000	200018	200018	200000
1448	2024-05-09	100% payment after delivery	300000	300007	300007	300000
1473	2024-06-12	100% Delivery before payment	160000	\N	350003	350000
1477	2024-06-10	100% payment after delivery	350000	350014	350014	350000
1465	2024-06-04	\N	350000	350014	350014	350000
1445	2024-05-08	50% advance payment	200000	200014	200014	200000
1451	2024-05-10	\N	160000	\N	200008	200000
1454	2024-05-15	100% payment after delivery/ completion	300000	300007	300007	300000
1450	2024-05-09	100% payment after delivery	300000	300006	300006	300000
1460	2024-05-20	70% advance payment & 30% on delivery	350000	350032	350032	350000
1466	2024-06-07	Nil	200000	200014	200014	200000
1468	2024-06-05	\N	350000	350014	350014	350000
1458	2024-05-20	\N	350000	350032	350032	350000
1459	2024-05-20	\N	350000	350032	350032	350000
1461	2024-05-27	Complete payment after delivery of required item	160000	\N	350023	350000
1457	2024-05-17	100% payment after delivery	350000	350032	350032	350000
1467	2024-06-05	\N	160000	\N	350014	350000
1474	2024-06-07	Payment on delivery	200000	200014	200014	200000
1469	2024-06-05	100% payment after delivery	160000	\N	350014	350000
1476	2024-06-10	100% payment after delivery	350000	350010	350010	350000
1472	2024-06-06	100% payment after delivery	300000	300005	300005	300000
1452	2024-06-10	100% delivery before payment	160000	\N	450002	450000
1475	2024-06-07	Complete payment after completion of works	160000	\N	350003	350000
1463	2024-05-29	Payment on delivery/utiliztion	200000	200014	200014	200000
1471	2024-06-06	100% payment after delivery	300000	300006	300006	300000
1462	2024-05-29	100% payment after delivery	350000	350020	350020	350000
1485	2024-06-27	100% payment after delivery	350000	350025	350025	350000
1484	2024-06-27	70% Advance 30% after delivery of stores.	350000	350020	350020	350000
1482	2024-06-13	100% payment after delivery	350000	350032	350032	350000
1479	2024-06-11	Complete payment after deleivery of equipment/ item	160000	\N	350003	350000
1470	2024-06-06	100% payment after delivery	300000	300007	300007	300000
1478	2024-06-11	100% delivery before payment	450000	450002	450002	450000
1481	2024-06-12	50% advance 50% after delivery	300000	300007	300007	300000
1483	2024-06-13	100% payment after delivery	350000	350031	350031	350000
1464	2024-06-04	Payment on delivery	200000	200014	200014	200000
\.


--
-- TOC entry 5304 (class 0 OID 373628)
-- Dependencies: 399
-- Data for Name: rfq_items; Type: TABLE DATA; Schema: purnew; Owner: postgres
--

COPY purnew.rfq_items (rfq_item_id, rfq_id, item_id, est_price, price) FROM stdin;
1	112	5751	\N	400
2	1004	2756	\N	338
3	596	701	\N	2300
4	1000	2718	\N	10424
5	111	5734	\N	120
6	174	8346	\N	1
7	726	1640	\N	1200000
8	974	2515	\N	28000
9	1071	8585	\N	5600
10	596	741	\N	4025
11	940	7959	\N	180813.33
12	974	2514	\N	25000
13	974	2513	\N	120000
14	947	7274	\N	420
15	988	2609	\N	150000
16	974	2512	\N	225000
17	440	6493	\N	2800
18	440	6494	\N	250
19	440	6495	\N	2000
20	185	6351	\N	800
21	477	4790	\N	48750
22	977	2536	\N	227000
23	977	2537	\N	80000
24	459	4687	\N	9450
25	234	7751	\N	3500
26	543	153	\N	1150
27	551	5198	\N	605
28	573	414	\N	8640
29	672	1219	\N	690
30	720	1600	\N	575
31	977	2538	\N	50000
32	988	2624	\N	30000
33	988	2629	\N	150000
34	988	2634	\N	150000
35	1185	9706	\N	4375
36	988	2618	\N	28000
37	988	2615	\N	25000
38	988	2617	\N	20000
39	988	2619	\N	20000
40	988	2620	\N	25000
41	988	2621	\N	21000
42	988	2623	\N	30000
43	988	2616	\N	25000
44	989	2637	\N	235000
45	1194	9764	\N	26875
46	989	2645	\N	12500
47	989	2649	\N	98000
48	989	2638	\N	515000
49	989	2648	\N	6500
50	1228	10047	\N	225
51	1247	10186	\N	35000
52	1247	10187	\N	43007
53	1247	10188	\N	875
54	990	2664	\N	180000
55	990	2665	\N	218500
56	1012	2829	\N	4065
57	1012	2832	\N	350000
58	1267	10326	\N	562.5
59	1012	2831	\N	1937550
60	1012	2830	\N	680
61	1017	2896	\N	389500
62	1025	2989	\N	120000
63	968	7822	\N	5750
64	991	2666	\N	325000
65	1025	2988	\N	126500
66	1025	2987	\N	142500
67	543	145	\N	5750
68	1025	2990	\N	8500
69	1045	5331	\N	340000
70	1045	5329	\N	510000
71	1282	10420	\N	1425000
72	1282	10421	\N	1135000
73	477	4787	\N	360098
74	440	6584	\N	3960
75	159	4251	\N	13200
76	159	4250	\N	11000
77	159	4249	\N	6600
78	1046	5334	\N	150000
79	1055	5566	\N	133500
80	1055	5565	\N	76000
81	979	2569	\N	9204299.59
82	1026	3036	\N	6600
83	1027	3038	\N	150000
84	1027	3039	\N	150000
85	1027	3037	\N	300600
86	1063	8401	\N	3950
87	1063	8400	\N	14500
88	1063	8370	\N	1375
89	1063	8365	\N	14000
90	1063	8360	\N	5250
91	1063	8357	\N	3375
92	1063	8349	\N	11500
93	1009	2814	\N	23350
94	1009	2815	\N	15870
95	726	1641	\N	1800
96	814	2084	\N	10426
97	816	2085	\N	830
98	91	7615	\N	12000
99	91	7616	\N	8000
100	799	2059	\N	475668
101	1067	8527	\N	2100
102	1067	8521	\N	1250
103	1067	8522	\N	20250
104	1067	8523	\N	17525
105	1067	8524	\N	11500
106	98	5648	\N	1375
107	98	5649	\N	850
108	98	5650	\N	590
109	98	5652	\N	115
110	98	5655	\N	7200
111	98	5656	\N	200
112	99	5658	\N	50
113	99	5659	\N	150
114	99	5660	\N	650
115	99	5661	\N	415
116	99	5662	\N	150
117	99	5663	\N	45
118	99	5664	\N	150
119	99	5665	\N	200
120	99	5666	\N	300
121	99	5667	\N	75
122	99	5668	\N	50
123	99	5669	\N	50
124	99	5670	\N	1640
125	159	4244	\N	14000
126	159	4243	\N	9900
127	159	4242	\N	11000
128	426	4496	\N	150
129	159	4241	\N	5700
130	159	4240	\N	15000
131	159	4239	\N	7000
132	159	4238	\N	19000
133	159	4237	\N	42500
134	158	4233	\N	11400
135	158	4225	\N	4140
136	158	4224	\N	8855
137	158	4223	\N	13225
138	158	4222	\N	60000
139	158	4221	\N	11500
140	158	4220	\N	17250
141	158	4219	\N	40500
142	158	4218	\N	805
143	99	5671	\N	6500
144	1026	3021	\N	8400
145	1026	3023	\N	1900
146	1026	3024	\N	300
147	1026	3026	\N	3000
148	1026	3028	\N	7900
149	1026	3029	\N	335
150	1026	3030	\N	400
151	1026	3034	\N	15000
152	1026	3019	\N	3600
153	1026	3032	\N	11400
154	1026	3018	\N	18000
155	1026	3013	\N	10800
156	1026	3017	\N	50
157	1026	3016	\N	1320
158	1026	3011	\N	960
159	1026	3012	\N	8400
160	1026	3014	\N	10800
161	1028	3042	\N	5500
162	1028	3040	\N	42500
163	100	5673	\N	1830
164	100	5680	\N	6950
165	100	5681	\N	550
166	106	5688	\N	1680
167	108	5697	\N	350
168	108	5698	\N	250
169	108	5699	\N	100
170	108	5700	\N	80
171	108	5702	\N	2100
172	108	5703	\N	120
173	108	5704	\N	720
174	108	5707	\N	550
175	108	5708	\N	50
176	109	5709	\N	110
177	109	5710	\N	220
178	109	5711	\N	450
179	109	5712	\N	350
180	109	5713	\N	900
181	109	5716	\N	50
182	109	5719	\N	200
183	110	5720	\N	850
184	110	5721	\N	2500
185	110	5722	\N	375
186	1028	3041	\N	42500
187	1063	8317	\N	60
188	1063	8179	\N	7770
189	1063	8307	\N	645
190	1063	8312	\N	8125
191	1063	8313	\N	1125
192	1063	8315	\N	875
193	1063	8409	\N	646
194	1063	8384	\N	1375
195	1063	8319	\N	538
196	1063	8390	\N	875
197	1063	8393	\N	1125
198	1063	8395	\N	1750
199	1063	8396	\N	1125
200	1063	8398	\N	1750
201	1063	8402	\N	5825
202	1063	8403	\N	1042
203	1063	8404	\N	406
204	1063	8383	\N	563
205	1063	8407	\N	406
206	111	5729	\N	550
207	111	5730	\N	60
208	111	5731	\N	45
209	111	5732	\N	100
210	1063	8385	\N	375
211	1063	8410	\N	646
212	111	5733	\N	95
213	111	5737	\N	150
214	1063	8406	\N	406
215	121	5822	\N	20000
216	1063	8345	\N	1250
217	1063	8323	\N	1063
218	1063	8326	\N	3000
219	1063	8328	\N	613
220	1063	8332	\N	93
221	1063	8335	\N	1938
222	1063	8336	\N	1813
223	1063	8339	\N	1438
224	1063	8341	\N	2157
225	1063	8342	\N	3375
226	1063	8392	\N	2600
227	1063	8322	\N	4875
228	1063	8325	\N	2250
229	1063	8343	\N	2250
230	1063	8374	\N	1563
231	1063	8381	\N	333
232	1063	8380	\N	8297
233	1063	8379	\N	1125
234	1063	8378	\N	438
235	117	5796	\N	70
236	117	5797	\N	55
237	117	5798	\N	25
238	117	5799	\N	90
239	117	5801	\N	350
240	117	5803	\N	150
241	117	5804	\N	100
242	117	5805	\N	15
243	117	5806	\N	150
244	117	5807	\N	3500
245	117	5808	\N	150
246	118	5810	\N	650
247	1063	8377	\N	2250
248	1063	8382	\N	563
249	1063	8375	\N	2375
250	1063	8373	\N	1250
251	126	5858	\N	2300
252	126	5859	\N	500
253	126	5860	\N	180
254	126	5861	\N	280
255	126	5862	\N	100
256	126	5863	\N	180
257	126	5865	\N	200
258	126	5869	\N	117
259	126	5870	\N	550
260	126	5872	\N	300
261	126	5873	\N	300
262	126	5874	\N	2500
263	128	5883	\N	420
264	128	5884	\N	350
265	128	5885	\N	45
266	128	5886	\N	80
267	128	5887	\N	2850
268	128	5888	\N	10
269	128	5889	\N	20
270	128	5890	\N	550
271	1063	8355	\N	2000
272	1063	8353	\N	7125
273	1063	8351	\N	375
274	1063	8347	\N	688
275	135	5917	\N	1200
276	135	5918	\N	900
277	1063	8376	\N	2250
278	135	5919	\N	240
279	135	5920	\N	75
280	135	5921	\N	85
281	135	5923	\N	5832
282	135	5924	\N	1556
283	136	5927	\N	9275
284	138	5930	\N	280
285	138	5931	\N	1150
286	138	5932	\N	1900
287	138	5933	\N	70
288	138	5934	\N	500
289	138	5935	\N	450
290	138	5936	\N	80
291	139	5937	\N	2745
292	139	5938	\N	1890
293	139	5940	\N	150
294	139	5941	\N	60
295	139	5942	\N	270
296	127	5879	\N	1800
297	140	5992	\N	14250
298	224	7235	\N	600
299	224	7236	\N	150
300	224	7237	\N	55
301	224	7238	\N	280
302	224	7239	\N	200
303	224	7240	\N	100
304	224	7241	\N	60
305	224	7242	\N	60
306	492	6982	\N	300
307	169	6236	\N	180
308	149	8280	\N	16500
309	492	6984	\N	50
310	492	6985	\N	500
311	492	6986	\N	300
312	492	6987	\N	10
313	149	8290	\N	2400
314	149	8292	\N	10000
315	153	4098	\N	20910
316	153	4099	\N	10275
317	277	6927	\N	15500
318	154	4128	\N	16500
319	153	4100	\N	12285
320	153	4101	\N	7087.5
321	153	4102	\N	8268
322	153	4103	\N	17062
323	153	4104	\N	19372
324	153	4105	\N	30712
325	153	4106	\N	7560
326	153	4107	\N	37800
327	153	4108	\N	30712
328	1040	5317	\N	65000
329	277	6928	\N	13500
330	155	4140	\N	575
331	155	4141	\N	103500
332	155	4142	\N	23000
333	155	4143	\N	690
334	155	4147	\N	575
335	155	4148	\N	345
336	155	4149	\N	805
337	158	4214	\N	18400
338	158	4213	\N	16675
339	158	4212	\N	24150
340	158	4211	\N	17825
341	157	4210	\N	207000
342	157	4209	\N	6900
343	157	4208	\N	26450
344	157	4207	\N	21300
345	157	4206	\N	17000
346	157	4205	\N	11500
347	1291	10572	\N	25000
348	157	4204	\N	25300
349	157	4203	\N	3000
350	157	4202	\N	48300
351	157	4200	\N	92000
352	157	4199	\N	8000
353	144	7744	\N	5000
354	147	7713	\N	800
355	147	7712	\N	650
356	144	7745	\N	1000
357	146	7662	\N	700
358	146	7659	\N	28
359	146	7657	\N	200
360	147	7711	\N	600
361	144	7742	\N	6000
362	144	7743	\N	2500
363	151	8247	\N	10800
364	492	6994	\N	500
365	492	6991	\N	1000
366	492	6993	\N	15000
367	492	6990	\N	2000
368	492	6992	\N	1000
369	157	4198	\N	15500
370	157	4197	\N	20700
371	157	4196	\N	40500
372	157	4195	\N	72500
373	156	4179	\N	17250
374	492	6976	\N	25000
375	158	4227	\N	2300
376	158	4231	\N	300000
377	158	4232	\N	4600
378	158	4236	\N	1900
379	159	4256	\N	2400
380	159	4257	\N	6000
381	160	4263	\N	7200
382	160	4265	\N	6400
383	160	4266	\N	60000
384	160	4272	\N	4000
385	160	4273	\N	4000
386	163	6084	\N	9660
387	163	6083	\N	1500
388	163	6092	\N	550
389	164	6107	\N	3840
390	166	6132	\N	2628
391	492	6974	\N	100
392	492	6975	\N	60
393	492	6977	\N	100
394	492	6978	\N	130
395	492	6979	\N	40
396	492	6981	\N	120
397	492	6983	\N	1650
398	163	6094	\N	4560
399	164	6105	\N	900
400	164	6106	\N	825
401	164	6108	\N	900
402	166	6110	\N	6513
403	166	6111	\N	2098
404	166	6112	\N	30
405	166	6113	\N	602
406	166	6114	\N	525
407	166	6115	\N	445
408	166	6116	\N	806
409	166	6117	\N	218
410	166	6118	\N	34
411	166	6119	\N	280
412	166	6120	\N	540
413	166	6121	\N	320
414	166	6122	\N	191
415	166	6142	\N	10848
416	166	6133	\N	12048
417	166	6131	\N	3648
418	165	6109	\N	19200
419	164	6103	\N	4620
420	1289	10535	\N	345
421	160	4314	\N	3600
422	161	4320	\N	3000
423	158	4216	\N	6900
424	166	6123	\N	160
425	166	6124	\N	45
426	166	6167	\N	3000
427	166	6168	\N	1800
428	166	6169	\N	660
429	167	6174	\N	60
430	167	6175	\N	60
431	167	6182	\N	900
432	167	6187	\N	1020
433	167	6189	\N	300
434	167	6190	\N	300
435	492	6988	\N	15000
436	167	6191	\N	300
437	167	6192	\N	30
438	167	6197	\N	77110
439	167	6198	\N	2640
440	169	6233	\N	840
441	169	6234	\N	540
442	169	6235	\N	420
443	169	6237	\N	5400
444	169	6238	\N	1440
445	169	6239	\N	300
446	169	6241	\N	900
447	169	6242	\N	1200
448	169	6246	\N	365
449	169	6249	\N	1700
450	169	6250	\N	4500
451	169	6258	\N	11280
452	174	8299	\N	50
453	174	8344	\N	80
454	174	8348	\N	2
455	174	8350	\N	6.5
456	174	8352	\N	45
457	174	8354	\N	40
458	180	6324	\N	4800
459	184	6333	\N	55000
460	178	6285	\N	8200
461	178	6290	\N	1500
462	178	6287	\N	3500
463	170	6267	\N	5500
464	170	6268	\N	2500
465	169	6254	\N	4250
466	169	6253	\N	15000
467	169	6255	\N	600
468	184	6334	\N	15000
469	184	6335	\N	28000
470	185	6336	\N	200
471	185	6337	\N	400
472	185	6338	\N	250
473	185	6339	\N	60
474	185	6340	\N	200
475	185	6341	\N	450
476	185	6342	\N	30
477	185	6343	\N	10
478	185	6345	\N	500
479	185	6346	\N	400
480	185	6347	\N	80
481	185	6348	\N	800
482	1132	9129	\N	5895
483	185	6353	\N	100
484	185	6354	\N	230
485	185	6355	\N	44
486	187	6358	\N	1450
487	187	6359	\N	20
488	187	6360	\N	10
489	187	6361	\N	250
490	187	6362	\N	200
491	187	6363	\N	400
492	187	6364	\N	50
493	187	6365	\N	25
494	187	6366	\N	15
495	187	6371	\N	350
496	187	6372	\N	250
497	186	6357	\N	20600
498	224	7243	\N	40
499	224	7244	\N	85
500	294	4382	\N	1200
501	295	7309	\N	310000
502	295	7310	\N	65000
503	212	7218	\N	1310
504	212	7225	\N	490
505	213	7231	\N	2035
506	296	7318	\N	265000
507	302	6383	\N	35
508	196	4335	\N	112500
509	187	6377	\N	600
510	185	6344	\N	1000
511	178	6292	\N	2000
512	178	6293	\N	2000
513	179	6314	\N	2000
514	179	6315	\N	850
515	302	6384	\N	550
516	302	6385	\N	80
517	302	6386	\N	350
518	302	6387	\N	100
519	302	6388	\N	250
520	302	6389	\N	40
521	302	6390	\N	80
522	302	6391	\N	40
523	302	6394	\N	220
524	214	7233	\N	6490
525	214	7234	\N	8240
526	256	6936	\N	4000
527	268	7456	\N	25000
528	268	7455	\N	70000
529	273	7475	\N	55000
530	269	7457	\N	220
531	269	7458	\N	160
532	269	7461	\N	450
533	269	7466	\N	14500
534	273	7480	\N	26000
535	302	6395	\N	40
536	302	6396	\N	180
537	302	6397	\N	55
538	302	6398	\N	270
539	302	6399	\N	400
540	302	6400	\N	35
541	440	6740	\N	80
542	302	6401	\N	3000
543	302	6402	\N	220
544	302	6403	\N	60
545	392	4449	\N	75
546	430	4516	\N	115
547	430	4518	\N	575
548	430	4519	\N	322
549	430	4520	\N	610
550	430	4521	\N	3350
551	430	4522	\N	1150
552	430	4523	\N	290
553	430	4524	\N	20
554	434	4581	\N	288
555	434	4583	\N	575
556	434	4584	\N	115
557	440	6496	\N	300
558	440	6497	\N	80
559	440	6728	\N	60
560	440	6730	\N	450
561	440	6731	\N	750
562	440	6732	\N	350
563	276	7487	\N	30000
564	284	6941	\N	1500
565	284	6950	\N	15000
566	285	6951	\N	1000
567	285	6952	\N	200
568	285	6953	\N	200
569	285	6954	\N	100
570	285	6955	\N	1500
571	285	6956	\N	30000
572	265	7716	\N	1500
573	293	4358	\N	377535
574	293	4359	\N	801965
575	293	4360	\N	417677
576	293	4361	\N	643370
577	293	4362	\N	29428
578	440	6733	\N	450
579	440	6734	\N	30
580	440	6738	\N	250
581	473	4755	\N	6325
582	258	6379	\N	8948693.93
583	270	7471	\N	94000
584	270	7470	\N	10000
585	270	7469	\N	92000
586	440	6739	\N	250
587	440	6741	\N	10
588	440	6744	\N	450
589	440	6745	\N	325
590	440	6747	\N	25
591	440	6751	\N	1000
592	440	6770	\N	750
593	440	6771	\N	400
594	440	6772	\N	500
595	440	6773	\N	700
596	440	6775	\N	20
597	440	6776	\N	200
598	440	6777	\N	550
599	440	6778	\N	1000
600	440	6779	\N	515
601	440	6780	\N	260
602	440	6781	\N	110
603	284	6942	\N	500
604	284	6940	\N	30000
605	293	4364	\N	121535
606	293	4365	\N	4715
607	293	4366	\N	45070
608	294	4367	\N	6000
609	294	4368	\N	2400
610	294	4369	\N	36400
611	294	4370	\N	1200
612	294	4371	\N	2400
613	294	4372	\N	54079
614	294	4375	\N	72100
615	294	4378	\N	12020
616	294	4379	\N	1200
617	294	4380	\N	1200
618	294	4381	\N	6000
619	294	4384	\N	6000
620	302	6393	\N	350
621	302	6392	\N	220
622	294	4385	\N	16900
623	294	4376	\N	36050
624	294	4374	\N	9615
625	311	7613	\N	9375
626	440	6784	\N	360
627	88	8177	\N	14000
628	89	8169	\N	7170
629	147	7715	\N	3030
630	147	7714	\N	2400
631	312	7733	\N	150
632	312	7731	\N	100
633	440	6785	\N	550
634	441	8461	\N	115000
635	458	4671	\N	41350
636	458	4675	\N	3660
637	458	4676	\N	48760
638	459	4681	\N	6420
639	472	4743	\N	70275
640	472	4744	\N	127725
641	472	4745	\N	5700
642	473	4748	\N	3600
643	473	4749	\N	5100
644	473	4750	\N	3700
645	473	4751	\N	4500
646	473	4752	\N	450
647	473	4753	\N	2300
648	473	4756	\N	12075
649	473	4757	\N	10000
650	474	4758	\N	300000
651	474	4759	\N	250000
652	474	4760	\N	402500
653	474	4761	\N	200
654	474	4764	\N	10000
655	474	4765	\N	5750
656	477	4769	\N	202
657	477	4770	\N	100
658	477	4771	\N	1485
659	477	4772	\N	1435
660	477	4773	\N	745
661	477	4774	\N	1325
662	477	4775	\N	75
663	477	4776	\N	185
664	477	4777	\N	175
665	477	4778	\N	216
666	477	4779	\N	300
667	477	4780	\N	815
668	477	4781	\N	150
669	477	4782	\N	75
670	477	4784	\N	9225
671	333	4389	\N	25000
672	352	6432	\N	1900
673	477	4785	\N	10180
674	477	4786	\N	23370
675	477	4788	\N	58210
676	478	4791	\N	635
677	478	4792	\N	175
678	478	4793	\N	920
679	1026	3003	\N	480
680	478	4801	\N	1150
681	478	4802	\N	3450
682	478	4803	\N	18400
683	478	4804	\N	1150
684	478	4805	\N	16120
685	478	4806	\N	2300
686	478	4807	\N	2300
687	478	4808	\N	400
688	478	4809	\N	8625
689	480	6957	\N	875
690	480	6959	\N	250
691	480	6966	\N	1500
692	480	6967	\N	320
693	480	6968	\N	150
694	480	6969	\N	540
695	480	6970	\N	360
696	482	4810	\N	1725
697	482	4811	\N	2645
698	480	6973	\N	3900
699	480	6971	\N	500
700	482	4812	\N	1150
701	482	4813	\N	46
702	482	4814	\N	115
703	482	4815	\N	1150
704	482	4816	\N	690
705	482	4817	\N	460
706	482	4818	\N	5175
707	482	4819	\N	35
708	482	4820	\N	550
709	482	4821	\N	80
710	482	4822	\N	350
711	383	4408	\N	101370
712	383	4409	\N	188360
713	383	4410	\N	13045
714	383	4411	\N	54325
715	383	4416	\N	116885
716	383	4417	\N	10065
717	390	4431	\N	61000
718	390	4432	\N	17500
719	390	4433	\N	272000
720	392	4434	\N	36080
721	392	4435	\N	570
722	392	4436	\N	870
723	392	4437	\N	950
724	392	4438	\N	980
725	392	4439	\N	450
726	392	4440	\N	860
727	392	4441	\N	145
728	392	4442	\N	65
729	392	4443	\N	140
730	392	4444	\N	210
731	392	4445	\N	635
732	392	4446	\N	40
733	392	4447	\N	114
734	392	4448	\N	125
735	389	4430	\N	23275
736	389	4429	\N	78700
737	386	4420	\N	294000
738	351	6429	\N	303475
739	347	6425	\N	165000
740	341	6419	\N	49875
741	336	4399	\N	90000
742	673	1280	\N	9050
743	482	4823	\N	100
744	482	4824	\N	250
745	482	4825	\N	40
746	482	4826	\N	80
747	482	4827	\N	40
748	482	4828	\N	220
749	482	4829	\N	350
750	482	4830	\N	220
751	482	4831	\N	40
752	482	4832	\N	180
753	482	4833	\N	55
754	482	4834	\N	270
755	482	4835	\N	400
756	482	4836	\N	35
757	482	4837	\N	3000
758	482	4838	\N	220
759	223	8185	\N	200
760	223	8186	\N	650
761	410	7721	\N	3000
762	410	7720	\N	4000
763	482	4839	\N	60
764	482	4845	\N	3175
765	482	4846	\N	2875
766	485	4853	\N	104900
767	486	4854	\N	274926
768	486	4855	\N	231610
769	487	4856	\N	19500
770	487	4857	\N	16900
771	487	4859	\N	13000
772	487	4860	\N	7020
773	487	4861	\N	108920
774	487	4862	\N	4810
775	487	4863	\N	12090
776	487	4864	\N	11500
777	487	4868	\N	3450
778	487	4869	\N	253
779	487	4870	\N	253
780	487	4871	\N	60
781	487	4872	\N	1725
782	487	4873	\N	1897.5
783	487	4874	\N	14950
784	487	4875	\N	32200
785	487	4876	\N	977.5
786	487	4877	\N	2932.5
787	487	4881	\N	2930
788	487	4882	\N	2930
789	487	4883	\N	2932.5
790	487	4885	\N	4025
791	487	4886	\N	56
792	487	4887	\N	5750
793	487	4888	\N	2875
794	487	4889	\N	4025
795	487	4890	\N	30300
796	487	4891	\N	31450
797	422	4459	\N	46
798	417	4452	\N	348600
799	422	4462	\N	34500
800	422	4466	\N	1150
801	408	6484	\N	68752.34
802	408	6479	\N	775259.63
803	426	4479	\N	1700
804	426	4480	\N	950
805	426	4481	\N	750
806	426	4482	\N	950
807	426	4484	\N	150
808	426	4489	\N	320
809	426	4490	\N	140
810	426	4493	\N	1200
811	426	4494	\N	545
812	426	4495	\N	100
813	426	4497	\N	2800
814	426	4500	\N	2100
815	426	4501	\N	2100
816	426	4483	\N	1200
817	426	4502	\N	40
818	427	4504	\N	34500
819	430	4514	\N	345
820	430	4515	\N	690
821	430	4534	\N	8620
822	434	4561	\N	1725
823	434	4562	\N	2875
824	434	4563	\N	23
825	434	4566	\N	989
826	434	4568	\N	575
827	434	4571	\N	863
828	434	4572	\N	3048
829	434	4574	\N	575
830	434	4575	\N	2684
831	434	4576	\N	150
832	434	4577	\N	3450
833	434	4578	\N	3450
834	434	4579	\N	3450
835	434	4580	\N	1150
836	434	4582	\N	17
837	440	6546	\N	1000
838	440	6547	\N	1750
839	440	6548	\N	500
840	440	6551	\N	10000
841	434	4570	\N	1817
842	431	4536	\N	15600
843	440	6552	\N	5000
844	434	4564	\N	5405
845	493	5006	\N	53040
846	440	6554	\N	120
847	440	6555	\N	12750
848	440	6556	\N	5400
849	440	6561	\N	540
850	440	6562	\N	360
851	487	4892	\N	90500
852	487	4893	\N	2300
853	487	4894	\N	1725
854	487	4895	\N	2300
855	487	4896	\N	1150
856	487	4901	\N	920
857	487	4902	\N	2300
858	487	4903	\N	1725
859	489	4958	\N	1500
860	489	4960	\N	8050
861	490	4964	\N	141050
862	493	4984	\N	920
863	493	4985	\N	460
864	493	4986	\N	230
865	493	4987	\N	920
866	493	4988	\N	2530
867	493	4989	\N	23000
868	493	4990	\N	115
869	493	4991	\N	28810
870	493	4994	\N	4600
871	493	4995	\N	4600
872	493	5004	\N	44200
873	493	5005	\N	36400
874	493	5007	\N	92000
875	493	5011	\N	41032
876	493	5013	\N	368
877	493	5014	\N	368
878	493	5015	\N	368
879	493	5016	\N	368
880	493	5017	\N	1840
881	493	5019	\N	3680
882	493	5020	\N	3680
883	493	5021	\N	4600
884	440	6502	\N	8200
885	440	6557	\N	4500
886	440	6506	\N	1200
887	440	6563	\N	1800
888	440	6567	\N	7110
889	440	6571	\N	200
890	440	6572	\N	3500
891	440	6573	\N	2100
892	440	6576	\N	500
893	440	6587	\N	90
894	440	6590	\N	270
895	440	6591	\N	540
896	440	6592	\N	270
897	440	6593	\N	150
898	440	6595	\N	1200
899	440	6596	\N	2700
900	440	6598	\N	250
901	440	6599	\N	150
902	440	6600	\N	540
903	720	1613	\N	1000
904	723	1634	\N	\N
905	183	6332	\N	149980
906	440	6508	\N	1024229.7
907	440	6602	\N	540
908	440	6603	\N	585
909	440	6604	\N	162
910	493	5022	\N	736
911	493	5023	\N	1840
912	493	5024	\N	36800
913	493	5025	\N	9200
914	493	5026	\N	9200
915	495	7281	\N	75000
916	495	7282	\N	45000
917	495	7283	\N	5000
918	495	7284	\N	5000
919	495	7285	\N	35000
920	495	7286	\N	22000
921	501	5067	\N	26000
922	501	5068	\N	20000
923	440	6607	\N	2500
924	440	6605	\N	9900
925	440	6606	\N	8000
926	440	6586	\N	1620
927	440	6627	\N	1600
928	440	6628	\N	3865
929	440	6629	\N	2000
930	440	6639	\N	1170
931	440	6640	\N	360
932	440	6642	\N	450
933	440	6650	\N	680
934	440	6651	\N	2700
935	440	6654	\N	325
936	440	6655	\N	90
937	440	6657	\N	50
938	440	6659	\N	145
939	440	6662	\N	680
940	440	6663	\N	50
941	440	6664	\N	100
942	440	6666	\N	220
943	440	6667	\N	350
944	440	6669	\N	685
945	440	6671	\N	450
946	440	6672	\N	150
947	440	6673	\N	125
948	440	6675	\N	135
949	440	6685	\N	9100
950	440	6688	\N	6300
951	440	6689	\N	8400
952	501	5069	\N	2500
953	501	5070	\N	5000
954	501	5071	\N	150000
955	501	5072	\N	23000
956	501	5073	\N	29900
957	501	5075	\N	46000
958	501	5076	\N	2800
959	1039	5277	\N	125
960	1039	5278	\N	125
961	586	571	\N	1150
962	586	581	\N	2875
963	1038	5274	\N	13500
964	440	6686	\N	29860
965	440	6634	\N	9500
966	1039	5279	\N	125
967	440	6737	\N	500
968	440	6690	\N	14000
969	440	6691	\N	9400
970	440	6692	\N	10000
971	440	6693	\N	1400
972	440	6694	\N	21000
973	440	6695	\N	14360
974	440	6696	\N	14000
975	440	6698	\N	2500
976	440	6707	\N	1040
977	440	6708	\N	155
978	440	6709	\N	260
979	440	6710	\N	1650
980	440	6712	\N	10
981	1039	5283	\N	672
982	440	6713	\N	25
983	1039	5292	\N	430
984	1039	5285	\N	379
985	440	6714	\N	450
986	440	6715	\N	140
987	1039	5287	\N	50
988	1039	5289	\N	85
989	440	6716	\N	750
990	1039	5286	\N	95
991	1039	5281	\N	125
992	440	6720	\N	250
993	440	6727	\N	540
994	1039	5282	\N	125
995	1039	5290	\N	135
996	440	6754	\N	74950
997	440	6759	\N	550
998	440	6760	\N	1500
999	440	6762	\N	100
1000	440	6763	\N	30
1001	440	6764	\N	350
1002	1039	5307	\N	2400
1003	1039	5303	\N	1670
1004	440	6765	\N	110
1005	440	6767	\N	1300
1006	440	6769	\N	120
1007	1039	5300	\N	1450
1008	1039	5306	\N	1051
1009	1039	5309	\N	890
1010	1039	5312	\N	845
1011	1039	5294	\N	665
1012	1039	5296	\N	350
1013	1039	5298	\N	350
1014	1039	5310	\N	315
1015	457	4630	\N	3500
1016	1039	5308	\N	295
1017	1039	5304	\N	250
1018	1039	5305	\N	250
1019	1039	5302	\N	226
1020	1039	5295	\N	170
1021	451	4585	\N	540
1022	451	4586	\N	465
1023	451	4587	\N	810
1024	451	4588	\N	930
1025	451	4589	\N	735
1026	451	4590	\N	1225
1027	451	4591	\N	318
1028	451	4592	\N	318
1029	451	4593	\N	9145
1030	451	4594	\N	810
1031	451	4595	\N	75
1032	451	4596	\N	125
1033	451	4597	\N	710
1034	451	4598	\N	1830
1035	451	4599	\N	9755
1036	451	4600	\N	2149
1037	456	4611	\N	5700
1038	456	4612	\N	2175
1039	456	4613	\N	70275
1040	456	4614	\N	127700
1041	501	5077	\N	5750
1042	503	5079	\N	10050
1043	503	5080	\N	262
1044	503	5081	\N	1840
1045	503	5082	\N	690
1046	504	5107	\N	200
1047	504	5108	\N	2000
1048	504	5109	\N	520
1049	504	5111	\N	2500
1050	504	5112	\N	500
1051	504	5113	\N	2000
1052	508	7000	\N	240
1053	508	7002	\N	155
1054	508	7004	\N	22003
1055	508	7009	\N	550
1056	512	5179	\N	630
1057	512	5181	\N	2000
1058	1053	5459	\N	17500
1059	1059	6798	\N	890
1060	1059	6795	\N	220
1061	489	4932	\N	230
1062	1059	6796	\N	80
1063	457	4626	\N	3625
1064	457	4627	\N	1000
1065	457	4628	\N	1000
1066	457	4631	\N	3500
1067	457	4632	\N	457
1068	457	4633	\N	4935
1069	457	4634	\N	3275
1070	457	4635	\N	3680
1071	457	4638	\N	3450
1072	457	4639	\N	46
1073	457	4640	\N	690
1074	457	4641	\N	633
1075	457	4642	\N	23
1076	457	4643	\N	311
1077	457	4644	\N	1150
1078	457	4645	\N	288
1079	457	4646	\N	1955
1080	457	4647	\N	403
1081	457	4648	\N	920
1082	457	4649	\N	460
1083	457	4650	\N	230
1084	1059	6817	\N	13500
1085	457	4651	\N	920
1086	457	4652	\N	345
1087	457	4655	\N	58
1088	457	4656	\N	1150
1089	457	4657	\N	460
1090	457	4658	\N	173
1091	457	4660	\N	288
1092	1059	6816	\N	13500
1093	1059	6815	\N	13500
1094	1059	6814	\N	5200
1095	1059	6813	\N	2880
1096	1059	6802	\N	2640
1097	1059	6801	\N	2640
1098	1059	6799	\N	1230
1099	1059	6803	\N	1180
1100	1059	6800	\N	480
1101	1059	6804	\N	425
1102	1059	6805	\N	340
1103	1059	6812	\N	215
1104	1059	6808	\N	130
1105	1059	6811	\N	160
1106	1059	6809	\N	170
1107	1059	6810	\N	180
1108	1059	6806	\N	190
1109	1059	6807	\N	215
1110	459	4688	\N	1139
1111	459	4689	\N	1140
1112	459	4690	\N	189750
1113	459	4691	\N	950
1114	459	4692	\N	1150
1115	459	4693	\N	950
1116	459	4694	\N	925
1117	459	4695	\N	940
1118	459	4696	\N	950
1119	459	4697	\N	950
1120	459	4698	\N	950
1121	459	4700	\N	950
1122	459	4701	\N	3450
1123	459	4702	\N	46000
1124	459	4703	\N	21263
1125	459	4704	\N	22500
1126	461	4708	\N	1105
1127	461	4709	\N	4420
1128	461	4710	\N	6630
1129	461	4715	\N	1032
1130	459	4684	\N	16500
1131	461	4717	\N	36135
1132	471	4729	\N	307500
1133	471	4731	\N	3050
1134	543	165	\N	345
1135	551	5209	\N	200
1136	135	5916	\N	1200
1137	1059	6823	\N	24500
1138	1059	6822	\N	16500
1139	1059	6819	\N	16500
1140	1059	6820	\N	16500
1141	1059	6818	\N	13500
1142	1059	6824	\N	13400
1143	140	6062	\N	58200
1144	140	6058	\N	42600
1145	471	4736	\N	950
1146	140	6056	\N	1150
1147	471	4737	\N	1250
1148	471	4738	\N	49400
1149	471	4739	\N	16445
1150	471	4740	\N	29500
1151	471	4741	\N	150
1152	471	4742	\N	175
1153	480	6958	\N	260
1154	478	4794	\N	1725
1155	478	4795	\N	2300
1156	478	4796	\N	920
1157	478	4797	\N	1220
1158	478	4798	\N	230
1159	478	4799	\N	5750
1160	478	4800	\N	575
1161	493	4979	\N	575
1162	88	8178	\N	3000
1163	146	7647	\N	120
1164	496	5036	\N	5792
1165	140	6055	\N	3000
1166	140	6061	\N	14400
1167	140	6077	\N	940
1168	140	6076	\N	2040
1169	482	4841	\N	2645
1170	140	6074	\N	8500
1171	482	4842	\N	1700
1172	487	4904	\N	2185
1173	487	4905	\N	414
1174	487	4906	\N	253
1175	487	4908	\N	170
1176	487	4909	\N	2530
1177	487	4910	\N	520
1178	487	4911	\N	3680
1179	487	4912	\N	2415
1180	487	4913	\N	1150
1181	123	7705	\N	179500
1182	482	4847	\N	15000
1183	487	4899	\N	1955
1184	487	4898	\N	1955
1185	140	6069	\N	8400
1186	140	6068	\N	3000
1187	140	6053	\N	1000
1188	140	6073	\N	12920
1189	298	7700	\N	190000
1190	131	7688	\N	397500
1191	132	7689	\N	389500
1192	487	4914	\N	1000.5
1193	487	4915	\N	400
1194	487	4916	\N	6900
1195	487	4917	\N	1035
1196	489	4933	\N	230
1197	489	4934	\N	345
1198	489	4935	\N	635
1199	489	4937	\N	1150
1200	489	4940	\N	34.5
1201	489	4941	\N	575
1202	489	4942	\N	345
1203	489	4943	\N	1150
1204	482	4843	\N	1610
1205	489	4944	\N	20950
1206	489	4953	\N	2760
1207	489	4945	\N	1150
1208	489	4946	\N	3450
1209	489	4947	\N	14000
1210	489	4948	\N	6624
1211	489	4949	\N	7176
1212	491	4967	\N	390000
1213	495	7290	\N	2800
1214	491	4968	\N	122600
1215	491	4971	\N	1380
1216	493	4972	\N	4500
1217	493	4973	\N	460
1218	493	4974	\N	3450
1219	493	4975	\N	35
1220	493	4976	\N	690
1221	493	4977	\N	633
1222	493	4978	\N	23
1223	493	4980	\N	450
1224	493	4981	\N	1380
1225	493	4982	\N	345
1226	493	4983	\N	311
1227	495	7287	\N	5000
1228	496	5038	\N	10500
1229	500	5041	\N	130000
1230	500	5043	\N	68000
1231	500	5044	\N	5750
1232	500	5045	\N	10580
1233	500	5046	\N	11500
1234	500	5047	\N	47000
1235	501	5049	\N	11500
1236	501	5050	\N	1265
1237	501	5051	\N	1840
1238	501	5052	\N	20000
1239	501	5053	\N	5865
1240	501	5054	\N	1100
1241	501	5055	\N	24160
1242	501	5056	\N	12305
1243	501	5057	\N	9200
1244	501	5059	\N	5750
1245	501	5062	\N	23000
1246	501	5063	\N	1000
1247	501	5066	\N	15000
1248	503	5083	\N	460
1249	489	4930	\N	175
1250	503	5084	\N	460
1251	503	5086	\N	345
1252	503	5087	\N	200
1253	503	5088	\N	100
1254	503	5090	\N	100
1255	503	5091	\N	600
1256	503	5092	\N	2300
1257	504	5098	\N	1500
1258	504	5099	\N	6500
1259	504	5100	\N	400
1260	504	5101	\N	260
1261	504	5102	\N	260
1262	504	5103	\N	195
1263	504	5104	\N	195
1264	504	5105	\N	5000
1265	504	5106	\N	325
1266	152	7085	\N	45000
1267	512	5147	\N	2430
1268	512	5148	\N	2430
1269	512	5149	\N	9706
1270	512	5150	\N	6000
1271	512	5151	\N	11000
1272	512	5152	\N	6500
1273	512	5153	\N	6500
1274	512	5154	\N	11000
1275	512	5155	\N	9700
1276	512	5156	\N	6000
1277	512	5157	\N	8500
1278	512	5158	\N	7000
1279	512	5159	\N	2500
1280	512	5160	\N	2500
1281	512	5161	\N	2500
1282	512	5162	\N	7000
1283	512	5163	\N	6500
1284	512	5164	\N	1200
1285	330	7383	\N	3800
1286	512	5165	\N	3700
1287	512	5171	\N	3000
1288	512	5173	\N	1000
1289	512	5175	\N	425
1290	512	5176	\N	1230
1291	512	5177	\N	1230
1292	331	7499	\N	10000
1293	512	5178	\N	365
1294	512	5180	\N	300
1295	536	5186	\N	200000
1296	536	5188	\N	1100
1297	519	8207	\N	480
1298	519	8210	\N	50
1299	519	8211	\N	250
1300	536	5189	\N	650
1301	536	5190	\N	2500
1302	543	142	\N	70000
1303	543	143	\N	35000
1304	543	148	\N	2300
1305	543	149	\N	2875
1306	543	150	\N	2875
1307	543	151	\N	4025
1308	543	152	\N	173
1309	543	154	\N	345
1310	543	155	\N	1725
1311	440	6705	\N	63800
1312	543	156	\N	1438
1313	543	157	\N	1150
1314	543	158	\N	3450
1315	543	159	\N	1150
1316	440	6766	\N	3500
1317	543	162	\N	173
1318	543	163	\N	863
1319	456	4610	\N	205000
1320	461	4711	\N	7514
1321	543	180	\N	805
1322	543	181	\N	7475
1323	543	183	\N	4600
1324	543	184	\N	3450
1325	750	1848	\N	450
1326	543	187	\N	805
1327	543	188	\N	23
1328	543	189	\N	34500
1329	543	194	\N	2875
1330	551	5210	\N	195
1331	551	5211	\N	85
1332	551	5212	\N	50
1333	551	5213	\N	73
1334	551	5214	\N	50
1335	551	5215	\N	12
1336	551	5216	\N	18
1337	551	5217	\N	2425
1338	551	5218	\N	2425
1339	551	5219	\N	145
1340	551	5220	\N	180
1341	551	5221	\N	36
1342	551	5222	\N	180
1343	551	5223	\N	6670
1344	551	5224	\N	790
1345	551	5225	\N	1210
1346	551	5226	\N	610
1347	551	5227	\N	610
1348	551	5228	\N	180
1349	551	5229	\N	3640
1350	551	5230	\N	240
1351	551	5231	\N	3035
1352	551	5232	\N	4853
1353	551	5233	\N	605
1354	551	5234	\N	180
1355	563	294	\N	15616
1356	563	295	\N	2050
1357	563	296	\N	39900
1358	563	297	\N	18.75
1359	563	299	\N	11210
1360	563	301	\N	147000
1361	586	564	\N	15
1362	586	565	\N	25
1363	586	566	\N	25
1364	586	567	\N	20
1365	550	252	\N	9800
1366	551	5199	\N	425
1367	551	5200	\N	1210
1368	551	5201	\N	850
1369	551	5202	\N	2425
1370	551	5203	\N	3035
1371	551	5204	\N	727
1372	551	5205	\N	727
1373	551	5206	\N	6065
1374	551	5207	\N	3035
1375	551	5208	\N	1210
1376	564	306	\N	4920
1377	564	312	\N	78045
1378	564	313	\N	110885
1379	564	315	\N	4010
1380	564	319	\N	6925
1381	564	320	\N	3150
1382	567	323	\N	5280
1383	567	324	\N	2880
1384	567	325	\N	672
1385	567	326	\N	7200
1386	567	327	\N	840
1387	567	328	\N	20000
1388	567	333	\N	6435
1389	567	335	\N	4155
1390	567	339	\N	49680
1391	567	340	\N	1840
1392	567	342	\N	100395
1393	567	344	\N	360
1394	567	345	\N	910
1395	822	2110	\N	9750
1396	567	346	\N	3120
1397	567	347	\N	2400
1398	567	348	\N	600
1399	567	349	\N	2080
1400	567	350	\N	600
1401	567	351	\N	25
1402	567	353	\N	9360
1403	586	569	\N	115
1404	586	570	\N	1150
1405	586	572	\N	50
1406	586	573	\N	140
1407	586	574	\N	575
1408	586	576	\N	1725
1409	586	577	\N	4830
1410	586	578	\N	635
1411	586	579	\N	185
1412	586	580	\N	4725
1413	586	588	\N	1315
1414	571	378	\N	50000
1415	571	379	\N	9500
1416	571	381	\N	2500
1417	571	382	\N	325
1418	571	383	\N	10000
1419	571	384	\N	5400
1420	571	385	\N	11500
1421	571	388	\N	780
1422	571	394	\N	18
1423	573	415	\N	5400
1424	573	416	\N	270
1425	573	417	\N	1080
1426	573	418	\N	1800
1427	573	419	\N	21600
1428	575	426	\N	5750
1429	575	428	\N	25
1430	575	430	\N	23
1431	575	431	\N	230
1432	584	471	\N	45500
1433	586	545	\N	520
1434	586	546	\N	610
1435	586	547	\N	175
1436	519	8205	\N	6000
1437	519	8206	\N	19500
1438	586	548	\N	600
1439	586	549	\N	1150
1440	586	550	\N	110
1441	586	551	\N	520
1442	576	437	\N	44000
1443	586	589	\N	1260
1444	586	592	\N	740
1445	586	593	\N	75
1446	586	594	\N	275
1447	586	595	\N	75
1448	586	597	\N	255
1449	586	598	\N	5
1450	586	599	\N	420
1451	586	552	\N	20
1452	586	553	\N	575
1453	586	554	\N	70
1454	586	555	\N	3220
1455	586	556	\N	17250
1456	586	557	\N	495
1457	586	559	\N	60
1458	586	560	\N	15
1459	586	561	\N	5
1460	586	562	\N	5
1461	586	563	\N	5
1462	590	644	\N	87000
1463	586	600	\N	45
1464	586	601	\N	100
1465	586	602	\N	75
1466	586	603	\N	30
1467	586	604	\N	155
1468	586	605	\N	220
1469	586	606	\N	35
1470	586	607	\N	475
1471	586	608	\N	190
1472	586	609	\N	120
1473	586	610	\N	65
1474	586	611	\N	95
1475	586	613	\N	265
1476	588	625	\N	750
1477	589	633	\N	109500
1478	589	634	\N	11000
1479	589	632	\N	160
1480	588	626	\N	26450
1481	588	636	\N	48300
1482	588	637	\N	160
1483	588	638	\N	1840
1484	588	639	\N	266
1485	590	640	\N	4650
1486	590	642	\N	4180
1487	590	643	\N	11000
1488	590	645	\N	350
1489	590	646	\N	1450
1490	590	647	\N	3600
1491	590	648	\N	250
1492	590	649	\N	23500
1493	592	661	\N	8530
1494	592	663	\N	360
1495	592	664	\N	960
1496	592	665	\N	600
1497	598	756	\N	155000
1498	598	758	\N	139500
1499	599	759	\N	1500
1500	599	769	\N	1700
1501	599	770	\N	980
1502	599	772	\N	1200
1503	599	773	\N	1500
1504	599	776	\N	5350
1505	599	778	\N	5350
1506	599	781	\N	2600
1507	599	782	\N	2600
1508	599	784	\N	2600
1509	599	785	\N	2600
1510	599	788	\N	3100
1511	599	790	\N	3100
1512	599	792	\N	5000
1513	599	795	\N	5000
1514	599	796	\N	375
1515	599	798	\N	400
1516	599	799	\N	475
1517	599	800	\N	190
1518	599	801	\N	90
1519	599	803	\N	740
1520	599	806	\N	740
1521	599	809	\N	490
1522	602	816	\N	38520
1523	607	822	\N	2300
1524	607	823	\N	115
1525	607	824	\N	490
1526	607	825	\N	775
1527	607	826	\N	1575
1528	607	827	\N	460
1529	607	828	\N	405
1530	607	829	\N	460
1531	607	830	\N	1985
1532	607	831	\N	6825
1533	607	832	\N	1260
1534	607	834	\N	775
1535	607	835	\N	290
1536	607	837	\N	1150
1537	607	838	\N	885
1538	607	839	\N	690
1539	607	840	\N	17825
1540	607	841	\N	1380
1541	607	842	\N	1725
1542	607	843	\N	230
1543	607	844	\N	115
1544	607	845	\N	16200
1545	607	847	\N	2645
1546	607	848	\N	3755
1547	607	849	\N	3035
1548	607	850	\N	2035
1549	607	852	\N	1440
1550	607	853	\N	3910
1551	607	854	\N	14465
1552	607	855	\N	19090
1553	607	856	\N	102092
1554	607	857	\N	158000
1555	607	858	\N	750
1556	607	859	\N	2430
1557	607	860	\N	590
1558	607	861	\N	2875
1559	607	862	\N	45
1560	607	863	\N	45
1561	607	864	\N	45
1562	607	865	\N	230
1563	607	866	\N	1380
1564	607	867	\N	12
1565	607	868	\N	46
1566	607	870	\N	58
1567	607	871	\N	58
1568	607	872	\N	115
1569	607	873	\N	115
1570	607	874	\N	1725
1571	607	875	\N	345
1572	607	876	\N	345
1573	607	877	\N	40
1574	607	881	\N	5200
1575	607	882	\N	9270
1576	607	883	\N	51500
1577	639	927	\N	5750
1578	639	928	\N	9200
1579	639	929	\N	2235
1580	639	930	\N	6670
1581	639	931	\N	2875
1582	639	933	\N	230
1583	639	934	\N	46
1584	639	935	\N	69
1585	639	936	\N	1840
1586	639	937	\N	230
1587	639	938	\N	172
1588	639	939	\N	57
1589	639	940	\N	3450
1590	639	941	\N	230
1591	639	942	\N	1150
1592	639	943	\N	460
1593	639	944	\N	310
1594	639	945	\N	115
1595	640	1018	\N	450
1596	639	946	\N	3660
1597	639	948	\N	575
1598	639	949	\N	4600
1599	639	950	\N	60
1600	639	951	\N	12
1601	639	952	\N	12
1602	639	953	\N	2300
1603	639	954	\N	190
1604	639	955	\N	3105
1605	639	956	\N	290
1606	639	957	\N	920
1607	639	958	\N	2300
1608	639	959	\N	1150
1609	639	960	\N	7590
1610	639	965	\N	3450
1611	639	973	\N	520
1612	652	1110	\N	1534020.81
1613	652	1111	\N	1534020.81
1614	870	2504	\N	45500
1615	639	966	\N	575
1616	639	967	\N	1440
1617	639	968	\N	1150
1618	639	969	\N	575
1619	639	970	\N	230
1620	639	971	\N	1150
1621	639	972	\N	2070
1622	639	985	\N	6890
1623	639	1002	\N	28750
1624	639	1005	\N	2875
1625	639	1007	\N	18685
1626	640	1009	\N	5000
1627	640	1010	\N	3050
1628	640	1011	\N	7500
1629	640	1015	\N	750
1630	640	1016	\N	700
1631	640	1019	\N	8400
1632	640	1023	\N	90
1633	640	1025	\N	475
1634	642	1038	\N	1460
1635	642	1039	\N	1095
1636	642	1040	\N	195
1637	642	1042	\N	29700
1638	649	1053	\N	7400
1639	649	1055	\N	25300
1640	649	1056	\N	14375
1641	649	1057	\N	28000
1642	649	1058	\N	3300
1643	649	1060	\N	29850
1644	649	1061	\N	2590
1645	649	1062	\N	3050
1646	649	1063	\N	3050
1647	649	1064	\N	1440
1648	649	1065	\N	750
1649	649	1066	\N	520
1650	649	1067	\N	95
1651	649	1068	\N	95
1652	649	1069	\N	70
1653	649	1071	\N	70
1654	649	1072	\N	129600
1655	649	1073	\N	62900
1656	649	1074	\N	3450
1657	649	1075	\N	9660
1658	649	1076	\N	10350
1659	649	1077	\N	2820
1660	649	1078	\N	1410
1661	649	1079	\N	1725
1662	649	1080	\N	1840
1663	649	1081	\N	46000
1664	649	1082	\N	10350
1665	649	1083	\N	460
1666	649	1084	\N	1080
1667	649	1085	\N	2980
1668	649	1086	\N	875
1669	649	1087	\N	1035
1670	649	1088	\N	1380
1671	649	1089	\N	2760
1672	649	1090	\N	5980
1673	649	1091	\N	2300
1674	649	1093	\N	2300
1675	649	1095	\N	290
1676	649	1096	\N	420
1677	649	1097	\N	1800
1678	649	1098	\N	6050
1679	649	1100	\N	4460
1680	649	1101	\N	13860
1681	652	1112	\N	172911.24
1682	655	1116	\N	28750
1683	655	1118	\N	3165
1684	655	1119	\N	23000
1685	666	1149	\N	430
1686	666	1150	\N	450
1687	666	1153	\N	450
1688	666	1154	\N	450
1689	666	1155	\N	450
1690	666	1156	\N	4500
1691	666	1157	\N	400
1692	666	1158	\N	3000
1693	672	1176	\N	17980
1694	672	1177	\N	19630
1695	1026	3005	\N	2100
1696	672	1178	\N	19630
1697	672	1179	\N	29350
1698	672	1180	\N	29350
1699	672	1181	\N	46500
1700	672	1182	\N	46500
1701	672	1183	\N	9450
1702	672	1185	\N	7150
1703	672	1186	\N	8680
1704	672	1187	\N	7050
1705	672	1188	\N	9200
1706	672	1189	\N	52250
1707	672	1190	\N	52250
1708	672	1191	\N	34800
1709	672	1192	\N	34800
1710	672	1195	\N	7550
1711	672	1196	\N	8100
1712	672	1197	\N	12750
1713	672	1198	\N	4230
1714	672	1199	\N	5980
1715	672	1200	\N	690
1716	672	1201	\N	4550
1717	672	1202	\N	1800
1718	672	1203	\N	175
1719	672	1204	\N	30
1720	672	1205	\N	1725
1721	672	1206	\N	4350
1722	672	1207	\N	5
1723	672	1208	\N	2050
1724	672	1209	\N	345
1725	672	1210	\N	15
1726	672	1211	\N	70
1727	672	1212	\N	80
1728	672	1213	\N	127
1729	672	1214	\N	80
1730	672	1215	\N	2
1731	672	1216	\N	2760
1732	672	1217	\N	17205
1733	672	1218	\N	375
1734	672	1220	\N	325
1735	672	1221	\N	690
1736	672	1223	\N	4200
1737	673	1231	\N	150
1738	673	1232	\N	35
1739	673	1233	\N	35
1740	673	1234	\N	35
1741	673	1235	\N	250
1742	673	1236	\N	150
1743	673	1237	\N	35
1744	673	1238	\N	39700
1745	734	1711	\N	9200
1746	652	1113	\N	681628.03
1747	673	1239	\N	3250
1748	673	1240	\N	4450
1749	673	1241	\N	5750
1750	673	1268	\N	615
1751	673	1269	\N	840
1752	673	1270	\N	14970
1753	673	1271	\N	2420
1754	673	1272	\N	275
1755	673	1273	\N	210
1756	673	1274	\N	155
1757	673	1277	\N	13100
1758	673	1279	\N	2225
1759	673	1281	\N	2800
1760	673	1282	\N	2225
1761	673	1284	\N	155
1762	673	1285	\N	41250
1763	673	1287	\N	2150
1764	673	1288	\N	920
1765	673	1289	\N	8750
1766	673	1290	\N	8750
1767	673	1291	\N	575
1768	673	1292	\N	2725
1769	673	1293	\N	9050
1770	673	1297	\N	8400
1771	673	1298	\N	4100
1772	673	1299	\N	5650
1773	673	1300	\N	7730
1774	673	1301	\N	2810
1775	679	1308	\N	1940
1776	679	1314	\N	810
1777	679	1315	\N	840
1778	679	1316	\N	90
1779	679	1317	\N	195
1780	679	1320	\N	40
1781	679	1322	\N	120
1782	679	1323	\N	210
1783	679	1324	\N	25
1784	679	1325	\N	1620
1785	679	1326	\N	4840
1786	684	1360	\N	12000
1787	684	1359	\N	9500
1788	679	1327	\N	1810
1789	679	1328	\N	240
1790	688	1366	\N	38000
1791	688	1367	\N	4600
1792	688	1368	\N	1800
1793	688	1369	\N	1150
1794	688	1371	\N	18228
1795	688	1372	\N	8100
1796	688	1373	\N	8250
1797	688	1374	\N	1150
1798	688	1375	\N	600
1799	837	2247	\N	4500
1800	837	2248	\N	3500
1801	688	1376	\N	600
1802	688	1377	\N	1150
1803	688	1378	\N	300
1804	688	1379	\N	1150
1805	688	1380	\N	1150
1806	688	1381	\N	600
1807	688	1383	\N	350
1808	688	1384	\N	600
1809	688	1385	\N	600
1810	688	1386	\N	2300
1811	688	1387	\N	370
1812	688	1390	\N	2300
1813	688	1391	\N	1200
1814	688	1392	\N	300
1815	688	1393	\N	1050
1816	688	1394	\N	2900
1817	688	1395	\N	60
1818	691	1403	\N	182631.04
1819	691	1405	\N	101523.84
1820	691	1406	\N	170325.12
1821	691	1407	\N	64885.76
1822	691	1408	\N	72996.48
1823	691	1409	\N	40480
1824	691	1412	\N	223184.64
1825	691	1413	\N	101523.84
1826	691	1414	\N	170325.12
1827	691	1415	\N	72996.48
1828	691	1416	\N	40480
1829	691	1417	\N	243321.6
1830	691	1418	\N	101523.84
1831	691	1419	\N	170325.12
1832	691	1420	\N	72996.48
1833	691	1421	\N	40480
1834	673	1248	\N	12500
1835	698	1447	\N	19483
1836	698	1448	\N	2875
1837	698	1462	\N	1906
1838	698	1464	\N	12521
1839	453	8552	\N	210000
1840	698	1465	\N	5725
1841	698	1467	\N	245
1842	698	1468	\N	375
1843	698	1469	\N	375
1844	698	1470	\N	9900
1845	714	1522	\N	54395
1846	714	1523	\N	44391
1847	714	1524	\N	13757
1848	714	1525	\N	24384
1849	673	1244	\N	12900
1850	673	1265	\N	9910
1851	673	1264	\N	12500
1852	673	1283	\N	2070
1853	714	1526	\N	13755
1854	714	1527	\N	9378
1855	720	1560	\N	30000
1856	673	1261	\N	12950
1857	720	1573	\N	800
1858	720	1574	\N	3050
1859	720	1575	\N	1120
1860	673	1253	\N	1152
1861	1004	2754	\N	8268
1862	1004	2759	\N	10063
1863	1004	2755	\N	2080
1864	1004	2757	\N	1083
1865	1004	2758	\N	6875
1866	720	1576	\N	1200
1867	720	1577	\N	1300
1868	720	1578	\N	1360
1869	720	1581	\N	759
1870	720	1584	\N	6300
1871	720	1589	\N	4600
1872	720	1594	\N	672
1873	720	1566	\N	1700
1874	918	7937	\N	6250
1875	919	7944	\N	71500
1876	673	1256	\N	28350
1877	673	1258	\N	16100
1878	720	1595	\N	370
1879	720	1596	\N	4235
1880	720	1597	\N	575
1881	720	1598	\N	1725
1882	720	1606	\N	1425
1883	673	1260	\N	26875
1884	673	1259	\N	11050
1885	720	1607	\N	3500
1886	720	1608	\N	2610
1887	720	1609	\N	7000
1888	720	1614	\N	250
1889	720	1615	\N	575
1890	720	1617	\N	1200
1891	720	1618	\N	1200
1892	720	1619	\N	2500
1893	720	1620	\N	1012
1894	720	1621	\N	1380
1895	720	1622	\N	357
1896	720	1623	\N	115
1897	720	1625	\N	3220
1898	673	1252	\N	9450
1899	673	1257	\N	108850
1900	673	1245	\N	10600
1901	673	1246	\N	30000
1902	673	1247	\N	26750
1903	146	7661	\N	150
1904	696	1437	\N	187000
1905	696	1436	\N	189000
1906	696	1435	\N	189000
1907	731	1660	\N	8400
1908	731	1663	\N	1680
1909	731	1666	\N	840
1910	731	1667	\N	22200
1911	731	1668	\N	14500
1912	963	7780	\N	4200
1913	963	7782	\N	40800
1914	963	7781	\N	10800
1915	1014	2854	\N	4077
1916	1014	2862	\N	3687
1917	731	1669	\N	1340
1918	731	1670	\N	550
1919	731	1671	\N	450
1920	1014	2864	\N	1050
1921	1014	2865	\N	23791
1922	938	6886	\N	27149
1923	1014	2863	\N	5288
1924	1014	2858	\N	1050
1925	105	8480	\N	265000
1926	105	8482	\N	95000
1927	105	8481	\N	105000
1928	720	1604	\N	682
1929	720	1605	\N	345
1930	731	1672	\N	3140
1931	731	1673	\N	125
1932	731	1674	\N	950
1933	731	1675	\N	650
1934	731	1683	\N	180
1935	734	1702	\N	43870
1936	734	1703	\N	710
1937	734	1704	\N	485
1938	734	1705	\N	405
1939	734	1706	\N	828
1940	734	1708	\N	3050
1941	734	1710	\N	155
1942	734	1712	\N	3105
1943	734	1713	\N	600
1944	734	1714	\N	135
1945	734	1715	\N	600
1946	734	1716	\N	795
1947	734	1719	\N	158
1948	734	1720	\N	1850
1949	105	8485	\N	135000
1950	105	8483	\N	65000
1951	750	1830	\N	25
1952	146	7654	\N	50
1953	146	7652	\N	2000
1954	146	7650	\N	700
1955	731	1676	\N	2140
1956	731	1677	\N	570
1957	731	1678	\N	1150
1958	731	1679	\N	1150
1959	731	1680	\N	450
1960	731	1681	\N	380
1961	731	1682	\N	75
1962	731	1684	\N	21600
1963	731	1685	\N	192
1964	731	1686	\N	19500
1965	731	1687	\N	3670
1966	731	1688	\N	850
1967	731	1689	\N	550
1968	731	1690	\N	30000
1969	130	8495	\N	45000
1970	734	1721	\N	1135
1971	734	1722	\N	1955
1972	734	1723	\N	495
1973	734	1724	\N	85
1974	734	1725	\N	3795
1975	734	1727	\N	166
1976	734	1729	\N	230
1977	734	1730	\N	8
1978	734	1732	\N	230
1979	734	1733	\N	1095
1980	734	1734	\N	150
1981	734	1735	\N	18630
1982	736	1744	\N	11385
1983	736	1745	\N	22655
1984	736	1746	\N	28290
1985	736	1747	\N	37950
1986	461	4718	\N	2692000
1987	736	1749	\N	1207
1988	736	1750	\N	4025
1989	736	1751	\N	6
1990	736	1752	\N	6
1991	736	1753	\N	6
1992	736	1754	\N	6
1993	736	1755	\N	6
1994	736	1756	\N	6
1995	736	1757	\N	6
1996	736	1758	\N	6
1997	736	1759	\N	29
1998	736	1760	\N	2015
1999	736	1761	\N	920
2000	736	1762	\N	345
2001	736	1763	\N	1150
2002	736	1764	\N	1150
2003	736	1765	\N	52
2004	944	7046	\N	2592
2005	737	1771	\N	575000
2006	738	1774	\N	520
2007	738	1775	\N	345
2008	738	1776	\N	575
2009	738	1777	\N	865
2010	738	1779	\N	34500
2011	738	1780	\N	6900
2012	738	1781	\N	2645
2013	745	1802	\N	698880
2014	745	1803	\N	490
2015	750	1817	\N	7500
2016	750	1819	\N	10950
2017	750	1820	\N	1200
2018	750	1821	\N	150
2019	750	1822	\N	1050
2020	750	1823	\N	250
2021	750	1825	\N	150
2022	750	1826	\N	150
2023	750	1827	\N	3000
2024	750	1828	\N	1850
2025	750	1829	\N	60
2026	750	1831	\N	40
2027	750	1832	\N	750
2028	750	1833	\N	600
2029	750	1834	\N	70
2030	750	1835	\N	870
2031	146	7633	\N	650
2032	146	7637	\N	1500
2033	146	7640	\N	200
2034	146	7638	\N	350
2035	146	7641	\N	400
2036	146	7655	\N	350
2037	750	1836	\N	300
2038	750	1837	\N	880
2039	750	1838	\N	150
2040	750	1839	\N	850
2041	750	1840	\N	120
2042	750	1841	\N	25
2043	750	1842	\N	520
2044	750	1843	\N	200
2045	750	1844	\N	5500
2046	750	1845	\N	600
2047	750	1846	\N	150
2048	750	1847	\N	1200
2049	750	1849	\N	12000
2050	750	1850	\N	92500
2051	750	1851	\N	60
2052	750	1852	\N	175
2053	750	1853	\N	2300
2054	750	1854	\N	575
2055	750	1855	\N	30
2056	750	1856	\N	345
2057	780	1890	\N	1050
2058	780	1891	\N	240
2059	780	1892	\N	6400
2060	780	1894	\N	760
2061	1059	6821	\N	14500
2062	780	1895	\N	1200
2063	780	1896	\N	1125
2064	780	1897	\N	3544
2065	780	1898	\N	65
2066	780	1899	\N	25
2067	780	1900	\N	200
2068	780	1901	\N	2625
2069	780	1902	\N	12500
2070	780	1903	\N	7000
2071	154	4120	\N	17600
2072	154	4121	\N	2800
2073	780	1904	\N	1875
2074	780	1905	\N	3750
2075	780	1906	\N	690
2076	780	1908	\N	815
2077	780	1909	\N	1065
2078	780	1910	\N	1190
2079	780	1911	\N	1565
2080	780	1912	\N	3250
2081	780	1913	\N	3565
2082	780	1915	\N	4750
2083	780	1917	\N	565
2084	780	1918	\N	8750
2085	780	1925	\N	30000
2086	217	7362	\N	24800
2087	787	1938	\N	8500
2088	787	1939	\N	2750
2089	787	1940	\N	10500
2090	787	1941	\N	5500
2091	787	1942	\N	5000
2092	787	1943	\N	4000
2093	787	1944	\N	3800
2094	787	1947	\N	5000
2095	787	1948	\N	3500
2096	787	1950	\N	7200
2097	787	1951	\N	1100
2098	787	1952	\N	1725
2099	787	1953	\N	14500
2100	787	1954	\N	5500
2101	787	1955	\N	9300
2102	787	1958	\N	1250
2103	787	1959	\N	470
2104	787	1960	\N	95
2105	278	6937	\N	203870
2106	284	6938	\N	190000
2107	273	7477	\N	125000
2108	787	1961	\N	1900
2109	787	1962	\N	500
2110	268	7451	\N	85000
2111	268	7453	\N	50000
2112	268	7452	\N	20000
2113	284	6944	\N	1000
2114	787	1963	\N	1900
2115	787	1964	\N	900
2116	787	1965	\N	2500
2117	787	1966	\N	650
2118	787	1967	\N	1500
2119	284	6949	\N	1500
2120	284	6946	\N	3000
2121	284	6945	\N	3000
2122	284	6943	\N	10000
2123	787	1969	\N	3800
2124	787	1974	\N	18750
2125	787	1981	\N	250
2126	787	1983	\N	190
2127	787	1985	\N	59500
2128	964	7786	\N	57750
2129	86	4095	\N	36089
2130	1014	2857	\N	61837
2131	787	1986	\N	35950
2132	787	1987	\N	120500
2133	787	1988	\N	161493
2134	787	1989	\N	160315
2135	787	1990	\N	132375
2136	787	1991	\N	10940
2137	787	1992	\N	12500
2138	787	1993	\N	3750
2139	787	1994	\N	4065
2140	787	1995	\N	4690
2141	787	1996	\N	6250
2142	787	1997	\N	3750
2143	787	1998	\N	2500
2144	787	1999	\N	190
2145	787	2000	\N	1500
2146	787	2004	\N	15000
2147	788	1968	\N	3900
2148	788	1970	\N	2875
2149	788	1971	\N	16310
2150	788	1972	\N	6773
2151	310	7568	\N	9800
2152	788	1973	\N	198
2153	788	1975	\N	11230
2154	788	2016	\N	938
2155	797	2050	\N	315
2156	797	2052	\N	845
2157	797	2053	\N	9173
2158	797	2054	\N	317
2159	804	2063	\N	15635
2160	804	2065	\N	12535
2161	804	2066	\N	750
2162	804	2069	\N	438
2163	822	2122	\N	1885.42
2164	822	2123	\N	2500
2165	263	7670	\N	90000
2166	263	7669	\N	30000
2167	263	7668	\N	230.77
2168	822	2124	\N	1875
2169	298	7703	\N	40000
2170	298	7701	\N	50000
2171	298	7702	\N	90000
2172	298	7704	\N	25000
2173	299	7686	\N	85000
2174	822	2125	\N	6000
2175	822	2132	\N	6215
2176	822	2133	\N	1315
2177	837	2245	\N	6250
2178	834	2197	\N	20000
2179	299	7687	\N	22500
2180	834	2198	\N	875
2181	834	2199	\N	3010
2182	834	2200	\N	100
2183	834	2201	\N	15
2184	834	2202	\N	250
2185	834	2203	\N	189
2186	834	2204	\N	5000
2187	834	2205	\N	5000
2188	834	2206	\N	625
2189	834	2207	\N	759
2190	834	2208	\N	2688
2191	834	2209	\N	50
2192	834	2211	\N	50000
2193	1006	2774	\N	2625
2194	309	7567	\N	43000
2195	836	2214	\N	25000
2196	310	7570	\N	6000
2197	836	2215	\N	18750
2198	837	2241	\N	11250
2199	837	2233	\N	138000
2200	836	2213	\N	9375
2201	837	2232	\N	23500
2202	837	2234	\N	23375
2203	1014	2871	\N	362
2204	1014	2870	\N	487
2205	1014	2872	\N	139
2206	1014	2873	\N	114
2207	1014	2866	\N	313
2208	1014	2867	\N	338
2209	310	7569	\N	18000
2210	330	7397	\N	8700
2211	329	7592	\N	18800
2212	1014	2859	\N	15108
2213	1014	2860	\N	2172
2214	836	2216	\N	7500
2215	836	2218	\N	1257
2216	836	2219	\N	5295
2217	836	2223	\N	46160
2218	837	2225	\N	101780
2219	837	2227	\N	63300
2220	837	2228	\N	63300
2221	837	2244	\N	146340
2222	837	2250	\N	9455
2223	837	2251	\N	245.7
2224	841	2261	\N	20000
2225	837	2235	\N	11500
2226	841	2263	\N	20000
2227	841	2265	\N	30000
2228	841	2268	\N	30000
2229	841	2270	\N	20000
2230	842	2278	\N	4750
2231	841	2271	\N	50000
2232	842	2272	\N	429
2233	842	2273	\N	3090
2234	837	2242	\N	3325
2235	330	7403	\N	5200
2236	356	8479	\N	4987500
2237	327	7506	\N	85585.5
2238	98	5647	\N	910
2239	329	7584	\N	4347.5
2240	329	7585	\N	7637.5
2241	1014	2874	\N	9250
2242	1020	2936	\N	18871
2243	1014	2881	\N	20500
2244	1014	2868	\N	414
2245	1014	2869	\N	330
2246	1020	2933	\N	65000
2247	329	7586	\N	6815
2248	329	7587	\N	175075
2249	329	7589	\N	413600
2250	329	7590	\N	23500
2251	329	7591	\N	3525
2252	329	7583	\N	403613
2253	330	7393	\N	1500
2254	330	7398	\N	25300
2255	330	7396	\N	25700
2256	330	7395	\N	10400
2257	330	7390	\N	26375
2258	331	7496	\N	140000
2259	331	7497	\N	20000
2260	331	7498	\N	20000
2261	327	7502	\N	489060
2262	495	7291	\N	45000
2263	488	4925	\N	141000
2264	488	4923	\N	111390
2265	488	4926	\N	8808
2266	489	4955	\N	65000
2267	495	7289	\N	8900
2268	495	7292	\N	150000
2269	515	7320	\N	75000
2270	543	166	\N	69000
2271	543	168	\N	46000
2272	543	169	\N	25300
2273	543	170	\N	86250
2274	543	171	\N	17250
2275	543	173	\N	17250
2276	543	174	\N	23000
2277	543	172	\N	11500
2278	655	1120	\N	57500
2279	655	1121	\N	126500
2280	673	1226	\N	4875
2281	673	1228	\N	68550
2282	640	1026	\N	7600
2283	640	1028	\N	8500
2284	640	1017	\N	950
2285	673	1295	\N	2300
2286	673	1242	\N	54550
2287	673	1249	\N	2000
2288	673	1255	\N	38100
2289	729	1652	\N	5200
2290	729	1654	\N	6500
2291	720	1565	\N	300
2292	720	1570	\N	3100
2293	742	1795	\N	127775
2294	1020	2932	\N	6500
2295	1020	2935	\N	1125
2296	745	1801	\N	2453850
2297	745	1800	\N	1136500
2298	750	1816	\N	39500
2299	738	1772	\N	25700
2300	820	2093	\N	8450
2301	820	2094	\N	174200
2302	788	2017	\N	156000
2303	750	1818	\N	25000
2304	822	2120	\N	779000
2305	822	2117	\N	60679
2306	822	2104	\N	281910
2307	822	2105	\N	72250
2308	788	2012	\N	1875
2309	788	2013	\N	59375
2310	788	2014	\N	10250
2311	788	2015	\N	6250
2312	750	1814	\N	130475
2313	750	1815	\N	16400
2314	750	1813	\N	7500
2315	850	2335	\N	3000
2316	837	2229	\N	61946
2317	854	2394	\N	49863
2318	837	2236	\N	23750
2319	837	2237	\N	15375
2320	837	2240	\N	1225
2321	837	2243	\N	40750
2322	837	2239	\N	2350
2323	837	2224	\N	91500
2324	837	2231	\N	40125
2325	837	2230	\N	57500
2326	863	2466	\N	10625
2327	870	2497	\N	288762
2328	863	2436	\N	3000
2329	863	2438	\N	3500
2330	863	2437	\N	2500
2331	863	2455	\N	15000
2332	978	2565	\N	1325
2333	978	2563	\N	169611
2334	978	2564	\N	168500
2335	976	2527	\N	7625
2336	976	2526	\N	12875
2337	1021	2939	\N	73931
2338	94	7306	\N	80000
2339	94	7305	\N	110000
2340	840	2258	\N	133684
2341	1026	3007	\N	600
2342	837	2226	\N	8315
2343	822	2119	\N	738500
2344	639	1004	\N	28000
2345	639	1003	\N	13800
2346	543	146	\N	205500
2347	593	666	\N	1100000
2348	388	4425	\N	180500
2349	493	4992	\N	56250
2350	472	4746	\N	1160832
2351	471	4734	\N	81250
2352	141	7096	\N	149075
2353	742	1794	\N	712500
2354	443	8426	\N	775000
2355	1007	2802	\N	12306827
2356	1020	2934	\N	625
2357	506	5131	\N	58000
2358	155	4153	\N	3450
2359	178	6294	\N	200
2360	918	7929	\N	12000
2361	918	7931	\N	115000
2362	1119	8997	\N	6250
2363	1119	8998	\N	8750
2364	1119	8999	\N	625
2365	1119	9000	\N	688
2366	1119	9002	\N	2750
2367	1119	9003	\N	2688
2368	1119	9004	\N	17824
2369	1119	9005	\N	2750
2370	1119	9006	\N	5125
2371	1119	9008	\N	3750
2372	1119	9009	\N	6500
2373	1119	9010	\N	4375
2374	1119	9011	\N	350
2375	1119	9012	\N	7250
2376	1119	9013	\N	18750
2377	1119	9015	\N	2000
2378	1119	9016	\N	2500
2379	1119	9017	\N	1875
2380	1119	9018	\N	3125
2381	1119	9019	\N	4375
2382	1119	9020	\N	1250
2383	1119	9021	\N	16805
2384	1146	9238	\N	247.75
2385	1146	9239	\N	35500
2386	1146	9240	\N	105000
2387	1146	9241	\N	1000
2388	1147	9242	\N	120
2389	1147	9243	\N	35000
2390	1147	9244	\N	195000
2391	1147	9245	\N	77500
2392	1132	9126	\N	24000
2393	1132	9127	\N	29200
2394	1132	9128	\N	1500
2395	1132	9124	\N	15800
2396	1132	9125	\N	12550
2397	1132	9130	\N	3200
2398	1132	9131	\N	50000
2399	1132	9132	\N	159375
2400	1132	9133	\N	7813
2401	1132	9134	\N	10000
2402	1132	9135	\N	9000
2403	1132	9140	\N	14560
2404	1121	9023	\N	20000
2405	1121	9024	\N	12500
2406	1121	9025	\N	33750
2407	1121	9026	\N	10750
2408	1121	9027	\N	1882
2409	1121	9028	\N	538
2410	1121	9029	\N	9375
2411	1121	9030	\N	641
2412	1121	9031	\N	2750
2413	1121	9032	\N	5000
2414	1121	9033	\N	2813
2415	1121	9034	\N	3314
2416	1136	9160	\N	35000
2417	1136	9161	\N	85000
2418	1136	9162	\N	10000
2419	1144	9233	\N	325000
2420	1144	9234	\N	29500
2421	1144	9235	\N	8000
2422	1144	9236	\N	34500
2423	1132	9136	\N	10000
2424	1132	9137	\N	70000
2425	1132	9138	\N	225000
2426	1132	9139	\N	115700
2427	1132	9142	\N	125
2428	1132	9143	\N	125
2429	1132	9144	\N	5000
2430	1142	9203	\N	6250
2431	1142	9204	\N	5625
2432	1142	9205	\N	10625
2433	1142	9206	\N	12500
2434	1142	9207	\N	11875
2435	1142	9208	\N	1229
2436	1142	9209	\N	23360
2437	1142	9210	\N	11500
2438	1142	9211	\N	7820
2439	1142	9213	\N	68000
2440	1142	9214	\N	79000
2441	1142	9215	\N	3000
2442	1142	9216	\N	2900
2443	1142	9217	\N	5830
2444	1122	9035	\N	74600
2445	1122	9036	\N	240
2446	1122	9037	\N	230
2447	1122	9038	\N	190
2448	1122	9039	\N	145
2449	1122	9040	\N	110
2450	1122	9041	\N	350
2451	1136	9164	\N	40000
2452	1135	9151	\N	40000
2453	1135	9152	\N	6000
2454	1135	9154	\N	4500
2455	1135	9155	\N	8000
2456	1135	9156	\N	2000
2457	1135	9157	\N	800
2458	1145	9237	\N	8894000
2459	1122	9042	\N	450
2460	1142	9218	\N	1875
2461	1142	9219	\N	2250
2462	1142	9220	\N	2625
2463	1142	9221	\N	3125
2464	1142	9222	\N	3500
2465	1142	9223	\N	4375
2466	1142	9224	\N	3125
2467	1142	9225	\N	4375
2468	1142	9226	\N	17875
2469	1142	9228	\N	2250
2470	1132	9145	\N	3750
2471	1142	9229	\N	2625
2472	1142	9230	\N	3125
2473	1142	9231	\N	3500
2474	1132	9146	\N	313
2475	1132	9147	\N	13
2476	122	5833	\N	2500
2477	1132	9148	\N	63
2478	1148	9248	\N	663
2479	1148	9249	\N	1063
2480	1123	9043	\N	25600
2481	1123	9044	\N	23400
2482	1123	9045	\N	19900
2483	1123	9046	\N	380
2484	1123	9047	\N	180
2485	1123	9049	\N	180
2486	1123	9050	\N	160
2487	1123	9051	\N	110
2488	1123	9052	\N	340
2489	1123	9053	\N	80
2490	1123	9054	\N	35
2491	1148	9250	\N	563
2492	1148	9251	\N	4400
2493	1148	9252	\N	5725
2494	1148	9253	\N	6897
2495	1148	9254	\N	3125
2496	1148	9255	\N	82
2497	1148	9286	\N	25000
2498	1148	9287	\N	57600
2499	1148	9288	\N	87500
2500	1148	9289	\N	11188
2501	1148	9290	\N	9375
2502	1148	9291	\N	2813
2503	1148	9292	\N	625
2504	1148	9293	\N	4375
2505	1148	9294	\N	313
2506	1148	9295	\N	250
2507	1148	9296	\N	313
2508	1148	9246	\N	663
2509	1148	9247	\N	663
2510	477	4783	\N	945
2511	1152	9341	\N	41000
2512	1152	9342	\N	9887
2513	1152	9343	\N	9887
2514	1152	9344	\N	12000
2515	1152	9345	\N	5000
2516	1152	9355	\N	10300
2517	1152	9356	\N	5000
2518	1152	9357	\N	2645
2519	1152	9358	\N	5460
2520	1124	9055	\N	325000
2521	1124	9056	\N	29500
2522	1124	9057	\N	8000
2523	1135	9158	\N	1000
2524	1135	9159	\N	3000
2525	1124	9058	\N	34500
2526	440	6523	\N	800000
2527	487	4858	\N	93075
2528	1119	9059	\N	1500
2529	1119	9060	\N	147500
2530	440	6648	\N	11000
2531	1125	9061	\N	128000
2532	1125	9062	\N	52500
2533	1125	9063	\N	108250
2534	456	4616	\N	75275
2535	1098	8804	\N	14461
2536	1098	8805	\N	12500
2537	503	5089	\N	1725
2538	508	6998	\N	430
2539	1151	9336	\N	4824820
2540	1151	9337	\N	1665590
2541	1151	9338	\N	171600
2542	1151	9339	\N	457600
2543	1150	9335	\N	375000
2544	551	5235	\N	145
2545	551	5236	\N	60
2546	551	5237	\N	120
2547	551	5239	\N	2425
2548	551	5240	\N	180
2549	551	5241	\N	6675
2550	551	5242	\N	18200
2551	551	5244	\N	11160
2552	551	5245	\N	5460
2553	1130	9109	\N	115
2554	1130	9110	\N	11250
2555	1130	9111	\N	9375
2556	95	7307	\N	14000
2557	92	7326	\N	57000
2558	551	5238	\N	12130
2559	159	4262	\N	66000
2560	673	1243	\N	48000
2561	1130	9102	\N	57777
2562	1130	9104	\N	27942
2563	1130	9105	\N	5625
2564	1130	9106	\N	6700
2565	1130	9107	\N	8563
2566	1130	9108	\N	71625
2567	551	5243	\N	7280
2568	305	7609	\N	6750
2569	684	1356	\N	48000
2570	135	5922	\N	2530
2571	575	425	\N	70300
2572	1130	9112	\N	500
2573	1130	9113	\N	1875
2574	1130	9114	\N	2313
2575	1130	9115	\N	1875
2576	1130	9116	\N	2063
2577	1130	9117	\N	5640
2578	1130	9118	\N	3535
2579	1130	9119	\N	282
2580	1130	9120	\N	10125
2581	1130	9121	\N	14375
2582	948	6847	\N	2415
2583	158	4230	\N	4000
2584	639	989	\N	3450
2585	1040	5316	\N	3570
2586	420	4456	\N	292000
2587	863	2442	\N	4375
2588	736	1743	\N	188164
2589	584	467	\N	97900
2590	673	1266	\N	46280
2591	639	983	\N	1610
2592	639	974	\N	2875
2593	86	4093	\N	7013216
2594	602	814	\N	704404
2595	948	6856	\N	40250
2596	954	6857	\N	1380
2597	954	6861	\N	520
2598	673	1227	\N	4300
2599	1053	5458	\N	17500
2600	1039	5313	\N	750
2601	1039	5311	\N	155
2602	1039	5299	\N	205
2603	879	5461	\N	262200
2604	822	2116	\N	9750
2605	733	1694	\N	81260.25
2606	576	433	\N	1753000
2607	286	8487	\N	269450
2608	97	7422	\N	19800
2609	933	7158	\N	32487
2610	453	8548	\N	897500
2611	453	8555	\N	445750
2612	291	7101	\N	125000
2613	122	5834	\N	3000
2614	291	7104	\N	28500
2615	589	630	\N	135000
2616	303	7607	\N	720
2617	291	7103	\N	148000
2618	290	7658	\N	130000
2619	1054	5559	\N	4000000
2620	938	6880	\N	19085
2621	290	7656	\N	156000
2622	290	7653	\N	110500
2623	289	7088	\N	115000
2624	86	4094	\N	35850
2625	86	4096	\N	295165
2626	110	5723	\N	185
2627	110	5724	\N	105
2628	110	5725	\N	150
2629	110	5726	\N	130
2630	110	5727	\N	180
2631	110	5728	\N	75
2632	111	5735	\N	150
2633	111	5736	\N	50
2634	111	5739	\N	300
2635	111	5740	\N	550
2636	111	5741	\N	200
2637	111	5742	\N	250
2638	111	5743	\N	400
2639	112	5745	\N	115
2640	112	5746	\N	3000
2641	112	5747	\N	250
2642	112	5752	\N	2100
2643	113	5754	\N	1650
2644	113	5756	\N	9500
2645	113	5757	\N	19850
2646	115	5759	\N	675
2647	115	5760	\N	425
2648	115	5761	\N	350
2649	115	5762	\N	180
2650	115	5763	\N	500
2651	115	5764	\N	350
2652	115	5765	\N	150
2653	115	5766	\N	100
2654	115	5767	\N	100
2655	115	5768	\N	100
2656	115	5769	\N	150
2657	115	5770	\N	250
2658	115	5771	\N	65
2659	115	5772	\N	300
2660	115	5773	\N	50
2661	115	5774	\N	150
2662	115	5775	\N	250
2663	115	5776	\N	850
2664	115	5778	\N	250
2665	115	5779	\N	50
2666	115	5781	\N	50
2667	115	5782	\N	2800
2668	115	5783	\N	3500
2669	115	5784	\N	1500
2670	116	5787	\N	120
2671	116	5788	\N	100
2672	116	5789	\N	150
2673	116	5791	\N	420
2674	117	5792	\N	450
2675	117	5793	\N	250
2676	117	5794	\N	60
2677	117	5795	\N	85
2678	118	5812	\N	200
2679	118	5813	\N	30
2680	118	5815	\N	120
2681	120	5817	\N	6920
2682	120	5818	\N	6920
2683	121	5823	\N	5000
2684	122	5828	\N	9000
2685	122	5829	\N	990
2686	122	5830	\N	750
2687	122	5831	\N	1150
2688	122	5832	\N	850
2689	122	5835	\N	5500
2690	122	5836	\N	6000
2691	122	5837	\N	550
2692	122	5838	\N	650
2693	122	5839	\N	600
2694	122	5840	\N	1000
2695	122	5841	\N	1000
2696	122	5842	\N	257.5
2697	122	5843	\N	6500
2698	122	5844	\N	750
2699	122	5845	\N	3500
2700	128	5891	\N	780
2701	129	5893	\N	11750
2702	134	5895	\N	23105
2703	134	5896	\N	5960
2704	134	5897	\N	4132
2705	134	5898	\N	1500
2706	134	5899	\N	1705
2707	134	5900	\N	22620
2708	134	5901	\N	22375
2709	134	5902	\N	14110
2710	134	5905	\N	56
2711	134	5906	\N	420
2712	134	5907	\N	255
2713	134	5908	\N	1050
2714	86	4097	\N	351808
2715	135	5909	\N	720
2716	135	5910	\N	1920
2717	135	5912	\N	2160
2718	135	5913	\N	4800
2719	135	5914	\N	10800
2720	135	5915	\N	35
2721	139	5943	\N	15
2722	139	5947	\N	85
2723	139	5948	\N	150
2724	139	5949	\N	250
2725	139	5950	\N	150
2726	139	5951	\N	90
2727	139	5952	\N	330
2728	139	5956	\N	2500
2729	139	5959	\N	250
2730	139	5961	\N	675
2731	139	5965	\N	6800
2732	139	5966	\N	1200
2733	140	5968	\N	180
2734	140	5976	\N	120
2735	140	5980	\N	800
2736	140	5981	\N	1200
2737	140	5985	\N	400
2738	140	5987	\N	3500
2739	140	5991	\N	510
2740	140	5994	\N	720
2741	140	5995	\N	515
2742	140	5996	\N	105
2743	140	5997	\N	920
2744	140	5998	\N	105
2745	140	5999	\N	205
2746	140	6000	\N	920
2747	140	6001	\N	1130
2748	140	6002	\N	920
2749	140	6003	\N	515
2750	140	6004	\N	215
2751	140	6005	\N	215
2752	140	6006	\N	115
2753	140	6007	\N	240
2754	140	6008	\N	150
2755	140	6009	\N	115
2756	140	6010	\N	115
2757	140	6011	\N	115
2758	140	6012	\N	215
2759	140	6013	\N	215
2760	140	6014	\N	215
2761	140	6015	\N	215
2762	140	6016	\N	215
2763	140	6017	\N	215
2764	140	6018	\N	215
2765	140	6019	\N	115
2766	140	6020	\N	115
2767	140	6021	\N	215
2768	140	6022	\N	115
2769	140	6023	\N	215
2770	140	6024	\N	240
2771	140	6025	\N	240
2772	140	6026	\N	240
2773	140	6027	\N	240
2774	140	6028	\N	240
2775	140	6029	\N	240
2776	140	6030	\N	205
2777	140	6031	\N	205
2778	140	6032	\N	240
2779	140	6033	\N	240
2780	140	6034	\N	240
2781	140	6035	\N	240
2782	140	6036	\N	240
2783	140	6037	\N	215
2784	140	6038	\N	105
2785	140	6039	\N	1025
2786	140	6040	\N	105
2787	140	6041	\N	105
2788	140	6042	\N	115
2789	140	6043	\N	115
2790	140	6044	\N	70725
2791	140	6045	\N	2970
2792	140	6047	\N	2800
2793	140	6048	\N	2250
2794	140	6051	\N	1800
2795	140	6052	\N	5400
2796	153	4109	\N	47250
2797	153	4110	\N	54337
2798	153	4111	\N	4252
2799	153	4112	\N	1800
2800	153	4113	\N	3600
2801	153	4114	\N	900
2802	153	4115	\N	5760
2803	155	4132	\N	13260
2804	155	4136	\N	39100
2805	155	4137	\N	9775
2806	155	4139	\N	575
2807	155	4150	\N	6325
2808	155	4151	\N	1495
2809	155	4152	\N	1840
2810	155	4154	\N	5750
2811	155	4160	\N	780
2812	155	4161	\N	400
2813	155	4164	\N	175
2814	156	4166	\N	3100
2815	156	4167	\N	460
2816	156	4168	\N	5600
2817	161	4319	\N	1200
2818	156	4169	\N	3450
2819	156	4170	\N	3680
2820	156	4171	\N	123500
2821	156	4172	\N	216250
2822	156	4173	\N	140
2823	156	4174	\N	3450
2824	156	4175	\N	45
2825	156	4176	\N	700
2826	156	4177	\N	625
2827	156	4178	\N	25
2828	156	4180	\N	1000
2829	156	4181	\N	90
2830	156	4182	\N	2200
2831	156	4183	\N	290
2832	156	4184	\N	2300
2833	156	4185	\N	150
2834	156	4186	\N	920
2835	156	4187	\N	460
2836	174	8367	\N	10
2837	156	4188	\N	230
2838	156	4189	\N	920
2839	156	4190	\N	340
2840	156	4192	\N	73790
2841	156	4193	\N	3400
2842	160	4274	\N	5600
2843	160	4275	\N	3200
2844	160	4276	\N	4000
2845	160	4277	\N	5600
2846	160	4278	\N	5600
2847	160	4279	\N	9600
2848	160	4280	\N	7200
2849	160	4281	\N	5600
2850	160	4282	\N	10
2851	160	4283	\N	10
2852	160	4284	\N	10
2853	160	4285	\N	10
2854	160	4286	\N	10
2855	160	4287	\N	10
2856	160	4288	\N	10
2857	160	4289	\N	10
2858	160	4290	\N	10
2859	160	4291	\N	10
2860	160	4292	\N	15
2861	160	4293	\N	10
2862	160	4294	\N	10
2863	160	4295	\N	10
2864	160	4296	\N	10
2865	160	4297	\N	10
2866	160	4298	\N	20
2867	166	6141	\N	4248
2868	160	4299	\N	10
2869	160	4300	\N	20
2870	160	4301	\N	20
2871	160	4302	\N	360
2872	160	4303	\N	25
2873	160	4304	\N	60
2874	160	4305	\N	3200
2875	160	4306	\N	25
2876	160	4307	\N	25
2877	160	4308	\N	2880
2878	160	4310	\N	100
2879	160	4311	\N	4480
2880	160	4312	\N	12000
2881	160	4315	\N	275
2882	160	4316	\N	320
2883	160	4317	\N	400
2884	160	4318	\N	9600
2885	161	4321	\N	9600
2886	161	4322	\N	12000
2887	162	4327	\N	35000
2888	163	6085	\N	1500
2889	163	6086	\N	780
2890	163	6087	\N	180
2891	163	6088	\N	850
2892	163	6089	\N	2250
2893	163	6090	\N	1000
2894	166	6125	\N	455
2895	166	6126	\N	390
2896	166	6127	\N	390
2897	166	6128	\N	390
2898	166	6129	\N	28
2899	166	6130	\N	48
2900	166	6137	\N	2508
2901	166	6138	\N	11928
2902	166	6139	\N	1608
2903	166	6143	\N	1200
2904	166	6145	\N	108
2905	166	6146	\N	390
2906	166	6147	\N	300
2907	166	6148	\N	324
2908	166	6149	\N	954
2909	166	6150	\N	240
2910	166	6151	\N	72
2911	166	6154	\N	480
2912	166	6155	\N	720
2913	167	6199	\N	100
2914	167	6200	\N	120
2915	167	6201	\N	60
2916	167	6202	\N	220
2917	167	6203	\N	220
2918	167	6204	\N	160
2919	167	6205	\N	160
2920	959	8069	\N	288
2921	167	6206	\N	360
2922	167	6207	\N	360
2923	167	6208	\N	160
2924	167	6209	\N	40
2925	167	6210	\N	40
2926	167	6211	\N	300
2927	167	6212	\N	230
2928	167	6213	\N	9200
2929	167	6214	\N	2450
2930	167	6215	\N	50
2931	167	6218	\N	750
2932	168	6223	\N	2800
2933	168	6227	\N	1250
2934	169	6230	\N	300
2935	169	6231	\N	2200
2936	169	6232	\N	420
2937	169	6266	\N	1250
2938	170	6275	\N	1350
2939	170	6276	\N	7500
2940	170	6282	\N	2700
2941	173	8500	\N	49257
2942	97	7421	\N	39000
2943	174	8300	\N	22
2944	174	8301	\N	28
2945	174	8302	\N	1250
2946	174	8303	\N	4449
2947	174	8304	\N	18
2948	174	8305	\N	385
2949	174	8306	\N	2
2950	174	8309	\N	25
2951	174	8314	\N	85
2952	174	8318	\N	3
2953	174	8320	\N	35
2954	174	8327	\N	3
2955	174	8329	\N	35
2956	174	8333	\N	8
2957	174	8337	\N	2
2958	174	8356	\N	80
2959	174	8358	\N	80
2960	174	8361	\N	5
2961	174	8362	\N	2
2962	174	8366	\N	15
2963	174	8369	\N	190
2964	178	6286	\N	150
2965	178	6288	\N	100
2966	178	6289	\N	300
2967	178	6291	\N	400
2968	178	6295	\N	8000
2969	178	6296	\N	250
2970	178	6297	\N	250
2971	178	6298	\N	150
2972	178	6299	\N	350
2973	178	6300	\N	300
2974	178	6301	\N	50
2975	178	6302	\N	200
2976	179	6305	\N	575
2977	179	6306	\N	800
2978	179	6307	\N	1500
2979	179	6308	\N	850
2980	179	6309	\N	450
2981	179	6310	\N	350
2982	179	6311	\N	50
2983	179	6312	\N	700
2984	179	6313	\N	30
2985	179	6316	\N	2500
2986	179	6317	\N	1690
2987	180	6318	\N	6300
2988	180	6319	\N	1500
2989	180	6321	\N	1000
2990	180	6322	\N	500
2991	181	6328	\N	500
2992	181	6329	\N	2500
2993	182	6331	\N	1500
2994	187	6373	\N	750
2995	187	6375	\N	450
2996	187	6378	\N	115
2997	842	2274	\N	5780
2998	842	2275	\N	5780
2999	842	2276	\N	14600
3000	842	2277	\N	14600
3001	174	8331	\N	4.499999
3002	842	2279	\N	4750
3003	842	2280	\N	3450
3004	842	2281	\N	3450
3005	842	2282	\N	1770
3006	849	2314	\N	5000
3007	849	2315	\N	5000
3008	849	2316	\N	10000
3009	849	2317	\N	20000
3010	849	2318	\N	20000
3011	849	2319	\N	20000
3012	849	2320	\N	2500
3013	849	2321	\N	17500
3014	850	2322	\N	2900
3015	850	2323	\N	560
3016	850	2324	\N	450
3017	850	2325	\N	300
3018	850	2326	\N	155
3019	850	2327	\N	362.5
3020	850	2328	\N	450
3021	850	2331	\N	2733
3022	850	2332	\N	3250
3023	850	2333	\N	6500
3024	850	2334	\N	1750
3025	850	2336	\N	224
3026	852	2344	\N	3055
3027	852	2345	\N	3666
3028	852	2346	\N	2075
3029	852	2350	\N	2112
3030	853	2352	\N	560
3031	853	2353	\N	5565
3032	853	2354	\N	12550
3033	853	2355	\N	25860
3034	853	2356	\N	25860
3035	853	2357	\N	5780
3036	853	2358	\N	9700
3037	853	2359	\N	3900
3038	853	2361	\N	1820
3039	853	2362	\N	338
3040	853	2363	\N	25750
3041	853	2366	\N	3250
3042	853	2367	\N	3315
3043	854	2369	\N	315
3044	854	2370	\N	315
3045	854	2371	\N	1800
3046	854	2372	\N	2800
3047	854	2373	\N	70
3048	854	2374	\N	15
3049	854	2375	\N	63
3050	854	2376	\N	63
3051	854	2377	\N	25
3052	854	2378	\N	25
3053	854	2379	\N	63
3054	854	2380	\N	63
3055	854	2381	\N	2800
3056	854	2382	\N	2700
3057	854	2383	\N	1500
3058	854	2384	\N	3550
3059	854	2385	\N	1500
3060	854	2386	\N	2000
3061	854	2387	\N	2000
3062	854	2389	\N	2000
3063	854	2392	\N	17000
3064	854	2393	\N	67500
3065	860	2411	\N	560
3066	860	2412	\N	3400
3067	860	2413	\N	10500
3068	860	2415	\N	3450
3069	860	2417	\N	2650
3070	860	2418	\N	650
3071	860	2419	\N	550
3072	860	2421	\N	570
3073	860	2424	\N	1780
3074	860	2425	\N	26000
3075	862	2429	\N	515600
3076	862	2430	\N	640700
3077	863	2439	\N	500
3078	863	2441	\N	315
3079	863	2443	\N	37.5
3080	863	2444	\N	625
3081	863	2445	\N	1250
3082	863	2434	\N	565
3083	863	2446	\N	440
3084	863	2447	\N	190
3085	863	2448	\N	250
3086	863	2449	\N	100
3087	863	2451	\N	125
3088	863	2456	\N	32500
3089	863	2457	\N	75000
3090	863	2460	\N	1000
3091	863	2463	\N	1375
3092	863	2464	\N	500
3093	863	2465	\N	1250
3094	863	2467	\N	7500
3095	863	2469	\N	22250
3096	863	2470	\N	5265
3097	863	2472	\N	4375
3098	863	2473	\N	6915
3099	863	2475	\N	34175
3100	863	2478	\N	5625
3101	863	2479	\N	1940
3102	863	2480	\N	36250
3103	863	2481	\N	135000
3104	864	2482	\N	309050
3105	864	2483	\N	7125
3106	864	2484	\N	4375
3107	864	2485	\N	725
3108	864	2486	\N	1000
3109	864	2487	\N	1000
3110	864	2488	\N	1500
3111	864	2489	\N	4000
3112	864	2490	\N	500
3113	864	2491	\N	12
3114	864	2492	\N	1200
3115	864	2493	\N	12500
3116	870	2496	\N	2192
3117	863	2433	\N	475
3118	870	2498	\N	5000
3119	870	2499	\N	7500
3120	870	2500	\N	7500
3121	870	2501	\N	110080
3122	870	2502	\N	43
3123	870	2503	\N	44460
3124	883	6837	\N	7765
3125	884	7847	\N	7475
3126	884	7848	\N	575
3127	884	7849	\N	1150
3128	884	7850	\N	1840
3129	884	7851	\N	2990
3130	884	7852	\N	690
3131	884	7853	\N	920
3132	884	7854	\N	143.75
3133	884	7855	\N	575
3134	884	7856	\N	1150
3135	884	7857	\N	1610
3136	884	7858	\N	1150
3137	884	7859	\N	403
3138	884	7860	\N	1150
3139	198	7433	\N	14500
3140	884	7861	\N	575
3141	884	7862	\N	345
3142	884	7863	\N	575
3143	884	7864	\N	288
3144	884	7865	\N	253
3145	884	7866	\N	276
3146	884	7867	\N	345
3147	884	7868	\N	575
3148	887	5428	\N	59500
3149	906	7902	\N	390
3150	906	7903	\N	2616
3151	906	7904	\N	345
3152	906	7905	\N	805
3153	906	7906	\N	460
3154	906	7907	\N	400
3155	906	7908	\N	690
3156	906	7909	\N	290
3157	906	7910	\N	115
3158	906	7911	\N	3450
3159	906	7912	\N	368
3160	906	7913	\N	9200
3161	906	7914	\N	575
3162	906	7915	\N	3450
3163	906	7918	\N	1725
3164	906	7921	\N	1150
3165	920	7946	\N	1150
3166	920	7947	\N	890
3167	928	5644	\N	10735.9
3168	928	5645	\N	6780
3169	928	6825	\N	4960
3170	928	6826	\N	26298.39
3171	928	6827	\N	3230.53
3172	928	6828	\N	2342.7
3173	928	6829	\N	13349.48
3174	928	6830	\N	10688.31
3175	928	6831	\N	19829.88
3176	930	5598	\N	73150
3177	930	5599	\N	156.28
3178	930	5600	\N	211.2
3179	930	5601	\N	141
3180	930	5602	\N	141
3181	930	5603	\N	168.46
3182	930	5605	\N	12.8
3183	930	5606	\N	7.43
3184	930	5608	\N	7.43
3185	930	5609	\N	7.43
3186	930	5610	\N	16.52
3187	930	5611	\N	16.52
3188	930	5612	\N	633.79
3189	930	5613	\N	179.61
3190	930	5614	\N	2747.81
3191	930	5615	\N	1684.61
3192	930	5616	\N	3224.71
3193	932	5589	\N	4960
3194	932	5590	\N	6452
3195	932	5591	\N	6452
3196	932	5592	\N	19829.88
3197	937	5641	\N	6120
3198	937	5642	\N	3060
3199	937	5643	\N	5100
3200	942	7119	\N	110
3201	942	7120	\N	1055
3202	942	7121	\N	695
3203	942	7122	\N	395
3204	942	7123	\N	300
3205	942	7124	\N	330
3206	942	7125	\N	970
3207	942	7126	\N	245
3208	942	7127	\N	245
3209	942	7128	\N	75
3210	942	7129	\N	1220
3211	942	7130	\N	610
3212	942	7131	\N	490
3213	942	7132	\N	735
3214	942	7136	\N	1220
3215	942	7137	\N	490
3216	942	7138	\N	245
3217	942	7139	\N	300
3218	942	7140	\N	245
3219	942	7141	\N	245
3220	942	7142	\N	245
3221	943	5530	\N	21600
3222	943	5531	\N	2160
3223	943	5532	\N	1040
3224	943	5534	\N	780
3225	943	5535	\N	1560
3226	943	5540	\N	1800
3227	943	5541	\N	10800
3228	943	5542	\N	1950
3229	943	5543	\N	4800
3230	943	5545	\N	1800
3231	943	5546	\N	65
3232	943	5547	\N	4800
3233	943	5548	\N	3600
3234	943	5550	\N	2400
3235	943	5551	\N	650
3236	944	7030	\N	2723
3237	944	7031	\N	2972
3238	944	7042	\N	360
3239	944	7043	\N	648
3240	944	7044	\N	60
3241	944	7045	\N	186
3242	944	7047	\N	72
3243	944	7048	\N	360
3244	944	7049	\N	60
3245	944	7080	\N	30
3246	944	7081	\N	30
3247	945	7014	\N	220
3248	945	7015	\N	1980
3249	945	7016	\N	330
3250	945	7017	\N	440
3251	945	7018	\N	385
3252	945	7019	\N	4950
3253	945	7020	\N	1320
3254	945	7021	\N	275
3255	945	7022	\N	880
3256	945	7023	\N	110
3257	945	7024	\N	825
3258	945	7025	\N	1100
3259	945	7026	\N	220
3260	945	7027	\N	770
3261	945	7028	\N	165
3262	945	7029	\N	330
3263	946	5619	\N	3000
3264	946	5620	\N	4800
3265	946	5621	\N	300
3266	946	5622	\N	3000
3267	946	5623	\N	600
3268	946	5624	\N	600
3269	946	5625	\N	600
3270	946	5626	\N	1200
3271	946	5627	\N	1020
3272	946	5628	\N	1020
3273	946	5629	\N	420
3274	946	5630	\N	420
3275	946	5631	\N	240
3276	946	5632	\N	420
3277	946	5633	\N	420
3278	946	5634	\N	420
3279	946	5635	\N	540
3280	946	5636	\N	300
3281	946	5637	\N	480
3282	946	5638	\N	360
3283	946	5639	\N	36
3284	946	5640	\N	2400
3285	947	7248	\N	190
3286	947	7265	\N	720
3287	947	7266	\N	600
3288	947	7329	\N	1200
3289	947	7330	\N	1200
3290	947	7331	\N	144
3291	947	7332	\N	144
3292	947	7333	\N	480
3293	947	7334	\N	600
3294	198	7434	\N	16200
3295	947	7335	\N	600
3296	947	7336	\N	360
3297	947	7337	\N	420
3298	947	7338	\N	20
3299	947	7339	\N	215
3300	947	7340	\N	50
3301	947	7341	\N	25
3302	947	7342	\N	20
3303	947	7345	\N	220
3304	947	7346	\N	1980
3305	947	7347	\N	330
3306	947	7348	\N	770
3307	947	7349	\N	495
3308	947	7350	\N	385
3309	947	7353	\N	550
3310	947	7355	\N	275
3311	947	7357	\N	110
3312	948	6843	\N	5175
3313	948	6844	\N	1160
3314	948	6845	\N	1825
3315	948	6846	\N	1725
3316	948	6848	\N	4025
3317	948	6849	\N	3000
3318	948	6851	\N	3450
3319	948	6854	\N	8950
3320	949	7185	\N	345
3321	949	7193	\N	115
3322	949	7206	\N	690
3323	949	7208	\N	575
3324	949	7232	\N	11500
3325	950	6875	\N	21450
3326	950	6876	\N	40480
3327	951	5484	\N	30880
3328	951	5485	\N	5355
3329	951	5489	\N	9515
3330	952	6893	\N	110
3331	952	6894	\N	140
3332	952	6895	\N	6000
3333	952	6896	\N	16000
3334	952	6897	\N	300
3335	952	6898	\N	860
3336	952	6900	\N	260
3337	952	6902	\N	15800
3338	952	6903	\N	2885
3339	952	6904	\N	1950
3340	952	6905	\N	86
3341	952	6906	\N	25650
3342	952	6907	\N	5850
3343	952	6908	\N	3150
3344	953	5520	\N	50000
3345	954	6858	\N	18000
3346	954	6864	\N	1380
3347	954	6867	\N	3600
3348	954	6868	\N	920
3349	955	7967	\N	35
3350	955	7968	\N	45
3351	955	7969	\N	170
3352	955	7970	\N	20
3353	955	7971	\N	325
3354	955	7972	\N	20
3355	955	7973	\N	15
3356	955	7974	\N	195
3357	955	7975	\N	25
3358	955	7976	\N	10
3359	955	7977	\N	30
3360	955	7978	\N	30
3361	955	7979	\N	10
3362	955	7980	\N	55
3363	955	7981	\N	25
3364	955	7982	\N	25
3365	955	7983	\N	25
3366	955	7984	\N	150
3367	955	7985	\N	115
3368	955	7986	\N	1550
3369	955	7987	\N	35
3370	956	7988	\N	148
3371	956	7989	\N	240
3372	956	7990	\N	330
3373	956	7991	\N	1210
3374	956	7992	\N	2090
3375	956	7993	\N	110
3376	956	7994	\N	33
3377	956	7995	\N	330
3378	956	7996	\N	1650
3379	956	7997	\N	330
3380	956	7998	\N	385
3381	956	7999	\N	385
3382	956	8000	\N	165
3383	956	8001	\N	275
3384	956	8002	\N	605
3385	956	8003	\N	2805
3386	956	8004	\N	385
3387	956	8017	\N	180
3388	956	8018	\N	18
3389	956	8019	\N	12
3390	956	8020	\N	12
3391	956	8021	\N	12
3392	956	8022	\N	24
3393	956	8023	\N	12
3394	956	8024	\N	12
3395	956	8025	\N	18
3396	956	8026	\N	348
3397	956	8027	\N	24
3398	957	8033	\N	620
3399	957	8034	\N	2300
3400	957	8035	\N	1150
3401	957	8036	\N	920
3402	957	8037	\N	632.5
3403	957	8038	\N	400
3404	957	8039	\N	920
3405	957	8040	\N	290
3406	957	8041	\N	230
3407	957	8042	\N	4025
3408	957	8043	\N	575
3409	957	8044	\N	620
3410	957	8045	\N	9200
3411	957	8046	\N	575
3412	958	8049	\N	3000
3413	958	8050	\N	3453
3414	958	8054	\N	9200
3415	958	8056	\N	4600
3416	958	8057	\N	4025
3417	958	8058	\N	4690
3418	958	8060	\N	7425
3419	958	8061	\N	4340
3420	958	8062	\N	3250
3421	958	8063	\N	313
3422	959	8067	\N	253
3423	959	8068	\N	173
3424	959	8070	\N	345
3425	959	8071	\N	58
3426	959	8072	\N	345
3427	959	8073	\N	518
3428	959	8074	\N	403
3429	959	8075	\N	29
3430	959	8076	\N	2300
3431	959	8077	\N	288
3432	959	8078	\N	230
3433	959	8079	\N	253
3434	959	8080	\N	57700
3435	959	8081	\N	1649
3436	959	8082	\N	5175
3437	960	8088	\N	6300
3438	960	8089	\N	30000
3439	961	8090	\N	6900
3440	961	8091	\N	2070
3441	961	8095	\N	50
3442	961	8096	\N	100
3443	961	8104	\N	1840
3444	961	8105	\N	4100
3445	961	8106	\N	575
3446	961	8108	\N	862.5
3447	961	8109	\N	1380
3448	961	8110	\N	2300
3449	961	8111	\N	460
3450	962	8115	\N	8160
3451	962	8116	\N	5100
3452	962	8117	\N	4320
3453	962	8121	\N	1020
3454	962	8122	\N	1440
3455	962	8123	\N	1440
3456	962	8124	\N	900
3457	962	8125	\N	2220
3458	962	8126	\N	2220
3459	962	8127	\N	2400
3460	962	8128	\N	2160
3461	962	8129	\N	2160
3462	962	8130	\N	2930
3463	965	7789	\N	2640
3464	965	7790	\N	180
3465	965	7791	\N	11400
3466	965	7792	\N	2160
3467	965	7793	\N	120
3468	966	7797	\N	38400
3469	966	7800	\N	3720
3470	966	7804	\N	19800
3471	966	7805	\N	420
3472	966	7807	\N	7200
3473	966	7810	\N	300
3474	967	7811	\N	700
3475	967	7812	\N	650
3476	967	7813	\N	7528
3477	967	7814	\N	120
3478	967	7815	\N	1440
3479	967	7816	\N	2400
3480	967	7819	\N	960
3481	967	7820	\N	960
3482	967	7821	\N	1680
3483	968	7823	\N	267
3484	968	7824	\N	345
3485	968	7825	\N	1725
3486	968	7826	\N	1725
3487	968	7827	\N	290
3488	968	7828	\N	690
3489	968	7829	\N	1150
3490	968	7830	\N	1150
3491	972	8148	\N	49400
3492	972	8150	\N	19500
3493	973	8152	\N	300
3494	973	8153	\N	80
3495	973	8154	\N	30
3496	973	8155	\N	35
3497	973	8156	\N	1000
3498	973	8157	\N	10
3499	973	8158	\N	130
3500	973	8159	\N	400
3501	973	8160	\N	30
3502	973	8161	\N	35
3503	976	2518	\N	10064
3504	976	2520	\N	3250
3505	976	2523	\N	5417
3506	976	2531	\N	7500
3507	976	2532	\N	5000
3508	976	2533	\N	71688
3509	978	2541	\N	1750
3510	978	2542	\N	1563
3511	978	2543	\N	1813
3512	978	2544	\N	2000
3513	978	2545	\N	4250
3514	978	2546	\N	315
3515	978	2547	\N	315
3516	978	2548	\N	250
3517	978	2549	\N	565
3518	978	2552	\N	33250
3519	978	2553	\N	24250
3520	492	6980	\N	150
3521	978	2554	\N	70625
3522	978	2555	\N	14500
3523	978	2556	\N	13000
3524	978	2557	\N	875
3525	978	2558	\N	1500
3526	978	2559	\N	2125
3527	978	2560	\N	750
3528	978	2561	\N	5500
3529	978	2562	\N	3738
3530	978	2567	\N	15181
3531	981	2575	\N	2788
3532	981	2576	\N	193
3533	981	2577	\N	1031
3534	981	2578	\N	345
3535	981	2579	\N	12500
3536	981	2581	\N	3275
3537	981	2582	\N	1125
3538	981	2583	\N	854
3539	981	2584	\N	710
3540	981	2585	\N	3500
3541	981	2586	\N	513
3542	981	2587	\N	275
3543	981	2588	\N	45
3544	981	2589	\N	10975
3545	981	2590	\N	63102
3546	981	2591	\N	12404
3547	978	2551	\N	47027
3548	978	2550	\N	97000
3549	991	2667	\N	192513
3550	991	2669	\N	22500
3551	991	2671	\N	5625
3552	991	2672	\N	1250
3553	993	2685	\N	20782
3554	993	2686	\N	29282
3555	1005	2762	\N	100509
3556	1005	2763	\N	22051
3557	1006	2769	\N	980
3558	1006	2775	\N	1613
3559	1006	2776	\N	1763
3560	1006	2777	\N	1196
3561	1006	2778	\N	4125
3562	1006	2779	\N	1333
3563	1006	2780	\N	18656
3564	1006	2781	\N	16938
3565	1006	2782	\N	1750
3566	1006	2783	\N	245
3567	1006	2784	\N	438
3568	1006	2786	\N	6950
3569	1006	2787	\N	219
3570	1006	2788	\N	1000
3571	1006	2789	\N	8197
3572	1006	2790	\N	2040
3573	1010	2816	\N	12891
3574	1010	2824	\N	389
3575	1014	2846	\N	178830
3576	1014	2848	\N	13194
3577	1014	2875	\N	16375
3578	1014	2877	\N	6000
3579	1014	2878	\N	4250
3580	1014	2882	\N	20313
3581	1014	2883	\N	1230
3582	1014	2884	\N	2712
3583	1014	2885	\N	3095
3584	1014	2886	\N	120625
3585	1014	2887	\N	21907
3586	1020	2897	\N	66750
3587	1020	2898	\N	79952
3588	1020	2900	\N	251
3589	1020	2901	\N	151
3590	1020	2902	\N	714
3591	1020	2903	\N	763
3592	976	2529	\N	4490
3593	1020	2904	\N	263
3594	1020	2905	\N	2075
3595	1020	2906	\N	2310
3596	1020	2907	\N	4610
3597	1020	2908	\N	9875
3598	1020	2909	\N	158
3599	1020	2910	\N	167
3600	1020	2911	\N	4875
3601	1020	2912	\N	7
3602	1020	2913	\N	7
3603	1020	2914	\N	13
3604	1020	2918	\N	5500
3605	1020	2919	\N	25
3606	1020	2920	\N	15500
3607	1020	2921	\N	20000
3608	1020	2923	\N	12500
3609	1020	2924	\N	5100
3610	1020	2925	\N	6350
3611	1020	2926	\N	270
3612	1020	2927	\N	8625
3613	1020	2928	\N	1500
3614	1020	2929	\N	4438
3615	1020	2930	\N	5000
3616	1020	2937	\N	9375
3617	1022	2942	\N	26680
3618	1022	2943	\N	600
3619	1022	2947	\N	10000
3620	1022	2952	\N	10000
3621	1022	2959	\N	10000
3622	1023	2974	\N	890
3623	1023	2976	\N	1250
3624	1023	2982	\N	25000
3625	1024	2986	\N	25000
3626	1034	5246	\N	17500
3627	1034	5247	\N	8500
3628	1034	5248	\N	17500
3629	1034	5249	\N	27500
3630	1034	5250	\N	28500
3631	1022	2961	\N	8500
3632	1022	2958	\N	640
3633	1024	2984	\N	7500
3634	140	6078	\N	3400
3635	639	962	\N	4600
3636	98	5654	\N	2000
3637	487	4880	\N	391
3638	652	1109	\N	379212.23
3639	543	179	\N	34500
3640	543	195	\N	2300
3641	545	228	\N	112930
3642	652	1115	\N	555555.56
3643	501	5074	\N	11500
3644	413	4451	\N	292000
3645	576	438	\N	44000
3646	535	5183	\N	516150
3647	158	4226	\N	2500
3648	422	4469	\N	2300
3649	491	4970	\N	1150
3650	673	1224	\N	6750
3651	720	1571	\N	2000
3652	978	2566	\N	6813
3653	1014	2861	\N	1061
3654	166	6134	\N	12048
3655	108	5706	\N	1500
3656	112	5748	\N	2000
3657	457	4659	\N	1725
3658	163	6082	\N	6060
3659	465	4720	\N	205400
3660	440	6643	\N	270
3661	440	6611	\N	1200
3662	673	1276	\N	16350
3663	863	2432	\N	350
3664	440	6500	\N	6900
3665	952	6899	\N	370
3666	441	8458	\N	149900
3667	456	4603	\N	19670
3668	1020	2931	\N	8250
3669	440	6558	\N	3000
3670	440	6706	\N	87800
3671	140	6059	\N	8400
3672	140	6075	\N	2465
3673	418	4454	\N	6198960
3674	1026	3004	\N	4630
3675	1026	3001	\N	150
3676	863	2431	\N	250
3677	1026	2991	\N	9000
3678	863	2435	\N	3125
3679	1026	2992	\N	260
3680	1026	2993	\N	25
3681	1026	2994	\N	400
3682	1026	2996	\N	3600
3683	1026	3006	\N	2540
3684	1026	2998	\N	4600
3685	1026	3002	\N	1020
3686	1026	3008	\N	3700
3687	1026	3009	\N	540
3688	1026	3010	\N	3600
3689	1026	2997	\N	240
3690	1026	2999	\N	12000
3691	483	4850	\N	448500
3692	430	4526	\N	290
3693	127	5880	\N	5400
3694	456	4609	\N	610000
3695	456	4608	\N	604925
3696	965	7788	\N	2880
3697	1047	5346	\N	20062
3698	1047	5345	\N	44075
3699	1047	5343	\N	19
3700	1047	5344	\N	360
3701	1047	5339	\N	5495
3702	1047	5338	\N	11800
3703	1047	5337	\N	3044
3704	1047	5336	\N	24043
3705	1047	5348	\N	8600
3706	1047	5349	\N	29995
3707	1047	5340	\N	7920
3708	1047	5347	\N	7200
3709	1048	5351	\N	1325
3710	1026	3020	\N	38059
3711	1026	3033	\N	187200
3712	1026	3035	\N	34200
3713	1026	3031	\N	30000
3714	1026	3025	\N	139500
3715	1026	3022	\N	8400
3716	1067	8540	\N	500
3717	1067	8529	\N	1250
3718	1067	8531	\N	2750
3719	1067	8532	\N	3750
3720	1067	8533	\N	5625
3721	1067	8535	\N	3750
3722	1067	8536	\N	575
3723	1067	8539	\N	4375
3724	1067	8526	\N	4425
3725	1067	8541	\N	157080
3726	1067	8538	\N	938
3727	199	6909	\N	9500
3728	199	6913	\N	12500
3729	124	5846	\N	76960
3730	1067	8520	\N	30428
3731	1067	8515	\N	228620
3732	1067	8516	\N	11570
3733	1067	8517	\N	8000
3734	1067	8528	\N	5775
3735	1067	8519	\N	425
3736	1067	8525	\N	1725
3737	1067	8537	\N	6670
3738	1067	8530	\N	750
3739	1064	8423	\N	79300
3740	1064	8424	\N	12688
3741	329	7594	\N	749650
3742	353	4405	\N	3300000
3743	482	4840	\N	320000
3744	787	1957	\N	21000
3745	870	2505	\N	45500
3746	870	2506	\N	1250
3747	870	2510	\N	3125
3748	975	2517	\N	111200
3749	837	2249	\N	21500
3750	1004	2760	\N	2625
3751	1004	2761	\N	5540
3752	1084	8668	\N	4520
3753	1084	8672	\N	6595
3754	1084	8704	\N	6858
3755	1084	8715	\N	10950
3756	1097	8794	\N	2625
3757	1097	8796	\N	20662
3758	459	4682	\N	165160
3759	508	7010	\N	560
3760	509	5133	\N	130000
3761	556	285	\N	83000
3762	639	987	\N	1505
3763	639	980	\N	1150
3764	639	996	\N	1090
3765	688	1388	\N	130000
3766	688	1389	\N	32500
3767	684	1357	\N	95500
3768	712	1519	\N	17500
3769	779	1889	\N	66990
3770	1080	8655	\N	200824
3771	288	7087	\N	284750
3772	287	7666	\N	78500
3773	287	7665	\N	165000
3774	287	7664	\N	135000
3775	287	7663	\N	225000
3776	1023	2981	\N	8500
3777	1023	2980	\N	870
3778	98	5653	\N	2650
3779	286	8490	\N	16200
3780	286	8489	\N	60000
3781	289	7089	\N	284750
3782	288	7086	\N	111000
3783	780	1916	\N	21885
3784	780	1920	\N	10000
3785	326	7098	\N	237471
3786	440	6566	\N	360
3787	140	5993	\N	1700
3788	1023	2966	\N	265000
3789	997	2700	\N	165350
3790	741	1788	\N	1049675
3791	687	1364	\N	929600
3792	1011	2828	\N	4352995
3793	984	2601	\N	130
3794	976	2528	\N	1625
3795	95	7308	\N	11000
3796	1008	2803	\N	90090
3797	1006	2796	\N	144056
3798	711	1513	\N	375000
3799	453	8550	\N	897800
3800	453	8551	\N	160000
3801	710	1509	\N	1736040
3802	1038	5270	\N	15000
3803	453	8549	\N	890000
3804	411	7097	\N	398619
3805	287	7667	\N	130000
3806	1023	2973	\N	19000
3807	1023	2972	\N	4650
3808	1023	2970	\N	19750
3809	1023	2968	\N	5400
3810	1022	2953	\N	9500
3811	130	8493	\N	24990
3812	462	8316	\N	1449754.14
3813	783	1930	\N	159719.75
3814	1022	2949	\N	550
3815	1021	2940	\N	51183
3816	400	6450	\N	5010152.4
3817	203	4342	\N	1050000
3818	203	4336	\N	1300000
3819	257	7082	\N	339452
3820	684	1362	\N	43000
3821	300	7682	\N	85000
3822	300	7683	\N	16500
3823	297	7699	\N	18100
3824	297	7697	\N	60000
3825	536	5191	\N	70000
3826	462	8311	\N	1222553.58
3827	1057	5588	\N	125000
3828	1020	2922	\N	93450
3829	1020	2899	\N	109500
3830	1014	2888	\N	1875
3831	1023	2967	\N	7500
3832	1014	2847	\N	20750
3833	1010	2822	\N	14300
3834	1010	2819	\N	9100
3835	1010	2818	\N	19500
3836	1010	2817	\N	53050
3837	1006	2797	\N	10563
3838	1022	2950	\N	90000
3839	1006	2785	\N	43000
3840	1006	2768	\N	95409
3841	1006	2767	\N	120000
3842	1005	2766	\N	8450
3843	204	7579	\N	6500
3844	550	248	\N	42
3845	1005	2765	\N	2340
3846	1005	2764	\N	22100
3847	999	2717	\N	19900
3848	997	2711	\N	3600
3849	1024	2983	\N	265000
3850	999	2716	\N	23400
3851	999	2715	\N	25600
3852	998	2714	\N	14500
3853	550	250	\N	8000
3854	306	7610	\N	9500
3855	313	7623	\N	4800
3856	305	7608	\N	42500
3857	998	2713	\N	5200
3858	997	2712	\N	460
3859	997	2710	\N	4680
3860	313	7624	\N	21000
3861	123	7706	\N	125000
3862	303	7606	\N	24200
3863	462	8324	\N	2626068.06
3864	357	7605	\N	4955000
3865	845	2291	\N	85500
3866	689	1398	\N	380250
3867	291	7100	\N	98000
3868	689	1401	\N	17000
3869	453	8554	\N	449500
3870	286	8492	\N	85000
3871	306	7612	\N	16000
3872	1004	2752	\N	1188
3873	1063	8363	\N	7750
3874	1063	8368	\N	2500
3875	133	7690	\N	390000
3876	299	7685	\N	155000
3877	556	283	\N	260000
3878	326	7099	\N	161831
3879	292	7105	\N	56750
3880	292	7106	\N	46000
3881	462	8338	\N	41046.25
3882	152	7084	\N	38500
3883	306	7611	\N	14500
3884	311	7614	\N	4750
3885	997	2709	\N	1460
3886	997	2708	\N	360
3887	997	2707	\N	4400
3888	997	2705	\N	5600
3889	997	2704	\N	1850
3890	997	2703	\N	4775
3891	976	2524	\N	18000
3892	993	2691	\N	106164
3893	993	2690	\N	109625
3894	993	2689	\N	299125
3895	261	7622	\N	15500
3896	962	8112	\N	57600
3897	741	1791	\N	74372
3898	639	981	\N	1785
3899	780	1924	\N	15000
3900	962	8119	\N	6960
3901	962	8120	\N	4560
3902	962	8131	\N	1440
3903	851	2342	\N	20000
3904	993	2688	\N	213125
3905	286	8488	\N	145000
3906	1038	5272	\N	14400
3907	510	5136	\N	3250
3908	727	1647	\N	362880
3909	481	7110	\N	6900000
3910	1038	5271	\N	7000
3911	851	2339	\N	91000
3912	993	2687	\N	58375
3913	825	2196	\N	151153
3914	568	372	\N	92000
3915	640	1029	\N	10700
3916	825	2140	\N	36081
3917	1038	5273	\N	7500
3918	1026	3027	\N	10700
3919	299	7684	\N	135000
3920	642	1034	\N	67800
3921	729	1655	\N	210000
3922	571	386	\N	4500
3923	495	7294	\N	135000
3924	976	2525	\N	85000
3925	543	144	\N	20881
3926	154	4127	\N	12650
3927	738	1773	\N	63951
3928	261	7619	\N	2400
3929	976	2535	\N	3314
3930	976	2519	\N	8750
3931	975	2516	\N	567910
3932	1038	5275	\N	122500
3933	1038	5276	\N	12000
3934	972	8147	\N	16900
3935	208	6919	\N	35885
3936	211	8571	\N	113500
3937	277	6920	\N	145
3938	277	6921	\N	950
3939	277	6922	\N	4500
3940	277	6923	\N	6700
3941	277	6924	\N	2000
3942	277	6925	\N	30000
3943	277	6926	\N	6500
3944	972	8144	\N	31200
3945	972	8143	\N	103480
3946	972	8142	\N	98150
3947	204	7580	\N	16000
3948	972	8141	\N	68900
3949	971	7836	\N	940500
3950	971	7833	\N	550000
3951	970	7832	\N	1054620
3952	969	7831	\N	739015
3953	966	7806	\N	8400
3954	966	7798	\N	4200
3955	965	7795	\N	21600
3956	962	8132	\N	6600
3957	962	8118	\N	3000
3958	277	6929	\N	4500
3959	277	6930	\N	2450
3960	277	7488	\N	23000
3961	962	8114	\N	4320
3962	962	8113	\N	3360
3963	449	8564	\N	825532
3964	961	8107	\N	2805
3965	596	720	\N	575
3966	988	2628	\N	150000
3967	271	8560	\N	100500
3968	961	8094	\N	6900
3969	210	6865	\N	113500
3970	1008	2812	\N	78650
3971	961	8093	\N	6095
3972	961	8092	\N	64975
3973	960	8087	\N	2500
3974	960	8086	\N	3000
3975	960	8085	\N	28850
3976	452	8575	\N	11590
3977	1008	2805	\N	227655
3978	1057	5584	\N	325000
3979	1008	2810	\N	188760
3980	960	8084	\N	35600
3981	958	8066	\N	11500
3982	452	8573	\N	320168.666
3983	1008	2809	\N	35750
3984	1008	2806	\N	32000
3985	1008	2804	\N	185900
3986	452	8580	\N	45000
3987	958	8065	\N	3450
3988	958	8064	\N	31000
3989	958	8055	\N	1600
3990	958	8053	\N	7475
3991	958	8052	\N	20700
3992	958	8051	\N	47040
3993	956	8032	\N	4500
3994	956	8031	\N	1440
3995	956	8030	\N	1440
3996	956	8029	\N	180
3997	956	8028	\N	28800
3998	956	8016	\N	132
3999	956	8015	\N	720
4000	956	8012	\N	6060
4001	956	8011	\N	26400
4002	956	8010	\N	6930
4003	956	8009	\N	4730
4004	956	8008	\N	6270
4005	956	8007	\N	20130
4006	863	2454	\N	260
4007	120	5820	\N	75000
4008	956	8006	\N	30250
4009	956	8005	\N	3300
4010	955	7964	\N	32500
4011	97	7428	\N	10500
4012	452	8587	\N	22000
4013	452	8588	\N	20000
4014	271	8561	\N	9800
4015	98	5646	\N	49990
4016	98	5651	\N	1400
4017	99	5657	\N	1350
4018	100	5672	\N	18135
4019	100	5674	\N	13190
4020	100	5675	\N	1700
4021	100	5676	\N	5090
4022	100	5677	\N	3825
4023	100	5678	\N	4000
4024	452	8596	\N	4000
4025	950	6874	\N	3573
4026	950	6873	\N	8030
4027	949	7230	\N	2875
4028	949	7229	\N	576
4029	949	7226	\N	17250
4030	949	7224	\N	13800
4031	949	7221	\N	11500
4032	949	7217	\N	27025
4033	949	7213	\N	51750
4034	949	7204	\N	3450
4035	949	7203	\N	1495
4036	100	5679	\N	7500
4037	100	5682	\N	5700
4038	107	5693	\N	9000
4039	107	5694	\N	9000
4040	107	5695	\N	6500
4041	108	5696	\N	3000
4042	108	5705	\N	1250
4043	111	5738	\N	2200
4044	112	5750	\N	3200
4045	113	5753	\N	107850
4046	113	5755	\N	1500
4047	115	5777	\N	800
4048	116	5785	\N	6200
4049	117	5800	\N	1500
4050	120	5819	\N	57090
4051	121	5821	\N	85000
4052	121	5824	\N	11500
4053	121	5825	\N	3000
4054	121	5826	\N	12900
4055	126	5866	\N	2250
4056	126	5867	\N	1050
4057	126	5868	\N	850
4058	126	5876	\N	300
4059	126	5877	\N	900
4060	123	7707	\N	94000
4061	97	7426	\N	11500
4062	105	8486	\N	88750
4063	97	7423	\N	20500
4064	97	7424	\N	11500
4065	97	7425	\N	27800
4066	97	7427	\N	11500
4067	116	5790	\N	2500
4068	134	5903	\N	15565
4069	134	5904	\N	22135
4070	146	7646	\N	450
4071	146	7644	\N	150
4072	146	7642	\N	150
4073	146	7649	\N	200
4074	135	5911	\N	28800
4075	139	5945	\N	4500
4076	139	5946	\N	2700
4077	139	5955	\N	950
4078	140	5967	\N	2800
4079	140	5969	\N	1850
4080	140	5970	\N	1500
4081	140	5971	\N	650
4082	140	5972	\N	540
4083	962	8133	\N	18420
4084	140	5973	\N	540
4085	140	5974	\N	1610
4086	140	5975	\N	400
4087	140	5977	\N	550
4088	140	5978	\N	1600
4089	140	5979	\N	800
4090	140	5982	\N	2000
4091	140	5983	\N	2500
4092	140	5984	\N	4500
4093	140	5986	\N	38250
4094	140	5988	\N	2550
4095	140	5989	\N	3145
4096	140	5990	\N	2125
4097	140	6070	\N	600
4098	140	6071	\N	765
4099	140	6072	\N	18700
4100	130	8494	\N	155760
4101	130	8496	\N	172500
4102	149	8286	\N	15000
4103	149	8287	\N	2800
4104	149	8291	\N	10500
4105	149	8294	\N	140000
4106	155	4129	\N	11050
4107	155	4130	\N	66300
4108	155	4131	\N	5525
4109	155	4133	\N	13800
4110	155	4134	\N	7735
4111	155	4135	\N	5865
4112	155	4138	\N	1150
4113	155	4144	\N	32200
4114	155	4146	\N	575
4115	155	4155	\N	920
4116	155	4156	\N	2070
4117	155	4157	\N	6900
4118	155	4158	\N	2070
4119	155	4159	\N	135
4120	155	4162	\N	575
4121	155	4163	\N	135
4122	155	4165	\N	17250
4123	158	4229	\N	64400
4124	158	4234	\N	31600
4125	158	4235	\N	86250
4126	159	4259	\N	24000
4127	159	4260	\N	1200
4128	159	4261	\N	5000
4129	160	4267	\N	6000
4130	160	4268	\N	1800
4131	160	4269	\N	7200
4132	164	6097	\N	60600
4133	164	6098	\N	165
4134	164	6099	\N	280
4135	164	6101	\N	138600
4136	164	6102	\N	6060
4137	166	6135	\N	93648
4138	166	6136	\N	148260
4139	166	6140	\N	16068
4140	166	6144	\N	2928
4141	166	6152	\N	2448
4142	166	6153	\N	600
4143	166	6156	\N	6000
4144	166	6157	\N	1200
4145	166	6158	\N	480
4146	166	6159	\N	240
4147	166	6160	\N	300
4148	152	7083	\N	315650
4149	166	6161	\N	240
4150	166	6162	\N	240
4151	166	6163	\N	22848
4152	166	6164	\N	7968
4153	166	6165	\N	2520
4154	440	6665	\N	250
4155	166	6166	\N	900
4156	166	6170	\N	15360
4157	166	6171	\N	3000
4158	166	6172	\N	3145
4159	167	6181	\N	3000
4160	167	6183	\N	12600
4161	167	6184	\N	1560
4162	167	6195	\N	360
4163	167	6216	\N	240
4164	167	6217	\N	240
4165	169	6243	\N	240
4166	169	6244	\N	840
4167	169	6245	\N	200
4168	169	6247	\N	3000
4169	169	6248	\N	2700
4170	169	6256	\N	2300
4171	169	6257	\N	180
4172	169	6259	\N	2400
4173	169	6262	\N	5160
4174	169	6263	\N	3000
4175	169	6264	\N	4500
4176	169	6265	\N	4500
4177	170	6269	\N	2000
4178	170	6277	\N	21610
4179	178	6303	\N	10500
4180	179	6304	\N	5000
4181	180	6320	\N	3500
4182	180	6323	\N	3000
4183	182	6330	\N	11625
4184	185	6349	\N	1500
4185	185	6350	\N	500
4186	187	6367	\N	2500
4187	187	6368	\N	5000
4188	187	6369	\N	3000
4189	187	6370	\N	200
4190	191	5397	\N	74500
4191	192	5528	\N	78640.5
4192	193	5399	\N	74500
4193	198	7429	\N	13200
4194	198	7430	\N	16200
4195	198	7431	\N	17500
4196	198	7432	\N	22200
4197	199	6910	\N	8500
4198	199	6911	\N	15500
4199	199	6912	\N	8800
4200	199	6914	\N	11000
4201	177	8503	\N	48000
4202	188	7680	\N	601700
4203	199	6915	\N	17500
4204	202	6916	\N	14500
4205	202	6917	\N	14500
4206	202	6918	\N	5500
4207	203	4337	\N	70000
4208	203	4339	\N	36000
4209	203	4345	\N	1815000
4210	203	4347	\N	1150000
4211	204	7575	\N	48200
4212	205	7435	\N	27825
4213	205	7436	\N	38620
4214	205	7437	\N	25665
4215	205	7438	\N	29260
4216	206	7439	\N	29740
4217	206	7440	\N	37885
4218	206	7441	\N	21588
4219	207	7442	\N	28500
4220	207	7443	\N	6610
4221	207	7444	\N	11850
4222	209	6931	\N	84900
4223	255	7445	\N	20280
4224	255	7446	\N	42000
4225	203	4338	\N	380000
4226	203	4340	\N	875000
4227	203	4341	\N	2700000
4228	203	4343	\N	380000
4229	203	4344	\N	215000
4230	203	4346	\N	485000
4231	255	7447	\N	45000
4232	255	7448	\N	20000
4233	255	7449	\N	37
4234	204	7578	\N	20000
4235	204	7577	\N	59000
4236	204	7576	\N	1095
4237	204	7581	\N	12000
4238	204	7582	\N	7000
4239	255	7450	\N	1003
4240	256	6932	\N	23551
4241	256	6933	\N	23551
4242	256	6934	\N	19442
4243	256	6935	\N	25631
4244	269	7459	\N	3500
4245	269	7462	\N	3600
4246	269	7463	\N	5000
4247	269	7464	\N	4700
4248	269	7465	\N	10000
4249	273	7476	\N	14000
4250	273	7478	\N	15000
4251	273	7479	\N	7000
4252	276	7481	\N	35000
4253	276	7482	\N	30000
4254	276	7483	\N	870
4255	174	8372	\N	1
4256	276	7484	\N	400
4257	276	7485	\N	240
4258	276	7486	\N	170
4259	284	6947	\N	1050
4260	284	6948	\N	15000
4261	269	7467	\N	155000
4262	273	7474	\N	2500
4263	286	8491	\N	95000
4264	261	7620	\N	21000
4265	269	7460	\N	11500
4266	443	8428	\N	800000
4267	273	7473	\N	175000
4268	284	6939	\N	65000
4269	293	4363	\N	501935
4270	318	6408	\N	150000
4271	318	6409	\N	275000
4272	319	6411	\N	35000
4273	319	6412	\N	272000
4274	320	6413	\N	7449910
4275	317	6406	\N	577500
4276	324	4386	\N	1037000
4277	297	7698	\N	21000
4278	333	4387	\N	750000
4279	333	4388	\N	290000
4280	337	4400	\N	339000
4281	338	4401	\N	833350
4282	338	4402	\N	1078000
4283	338	4403	\N	1327300
4284	338	4404	\N	104530
4285	339	6417	\N	415000
4286	348	6426	\N	329000
4287	354	6435	\N	164970
4288	358	6436	\N	1510185
4289	440	6505	\N	7200
4290	358	6437	\N	341130
4291	360	5576	\N	205829
4292	327	7507	\N	48642.7
4293	327	7503	\N	75804.3
4294	327	7501	\N	244530
4295	327	7500	\N	295881.3
4296	327	7504	\N	116151.7
4297	433	4547	\N	64600
4298	383	4412	\N	190510
4299	383	4413	\N	66860
4300	383	4414	\N	123080
4301	383	4415	\N	21840
4302	384	4418	\N	231610
4303	388	4421	\N	22000
4304	388	4422	\N	105000
4305	388	4423	\N	206000
4306	388	4424	\N	106000
4307	389	4427	\N	670000
4308	389	4428	\N	14000
4309	393	6439	\N	160293
4310	394	6440	\N	28000
4311	394	6441	\N	18500
4312	395	6442	\N	169149.6
4313	396	6443	\N	335000
4314	397	6445	\N	239549.4
4315	398	6446	\N	1445170
4316	422	4461	\N	12000
4317	596	676	\N	230
4318	398	6447	\N	586990
4319	399	6448	\N	32290.02
4320	399	6449	\N	24871.3
4321	441	8453	\N	147500
4322	1063	8308	\N	27710
4323	388	4426	\N	171500
4324	382	4407	\N	5989690
4325	1071	8583	\N	19800
4326	1071	8584	\N	9800
4327	323	6416	\N	35000
4328	452	8581	\N	43000
4329	415	8508	\N	45000
4330	452	8608	\N	633000
4331	452	8607	\N	263700
4332	452	8606	\N	795000
4333	452	8605	\N	268000
4334	452	8604	\N	35000
4335	400	6451	\N	62553.6
4336	400	6452	\N	215028
4337	400	6453	\N	142700.4
4338	401	6455	\N	220586.11
4339	440	6514	\N	750000
4340	440	6515	\N	800000
4341	401	6454	\N	6155743.16
4342	402	6456	\N	797408.23
4343	402	6459	\N	168756.89
4344	402	6461	\N	63019.49
4345	402	6462	\N	79056
4346	404	6466	\N	70079.16
4347	404	6467	\N	27384.42
4348	405	6468	\N	1674561.6
4349	405	6469	\N	42055.2
4350	405	6470	\N	672883.2
4351	405	6471	\N	3439304
4352	405	6472	\N	375196.8
4353	405	6473	\N	4396.68
4354	405	6474	\N	3612.92
4355	406	6475	\N	142500
4356	407	6476	\N	905360
4357	408	6477	\N	61600
4358	271	8562	\N	19000
4359	1071	8582	\N	28500
4360	408	6478	\N	12880
4361	408	6485	\N	6268.63
4362	408	6486	\N	14772.21
4363	408	6487	\N	36600
4364	408	6488	\N	29716
4365	408	6489	\N	15525
4366	408	6490	\N	79000
4367	408	6491	\N	79000
4368	409	6492	\N	22499870
4369	419	4455	\N	23800
4370	421	4457	\N	575500
4371	422	4458	\N	2875
4372	422	4460	\N	44830
4373	423	4470	\N	2050000
4374	426	4473	\N	487600
4375	426	4487	\N	250
4376	426	4488	\N	75
4377	426	4491	\N	4700
4378	426	4492	\N	8000
4379	426	4498	\N	2400
4380	426	4499	\N	1460
4381	426	4503	\N	36570
4382	427	4505	\N	247450
4383	427	4507	\N	3450
4384	428	4508	\N	1097775
4385	428	4509	\N	77910
4386	428	4510	\N	47880
4387	428	4511	\N	114912
4388	428	4513	\N	185000
4389	433	4540	\N	9776000
4390	433	4542	\N	18975
4391	433	4543	\N	123760
4392	433	4544	\N	1636800
4393	433	4545	\N	765600
4394	433	4546	\N	169000
4395	422	4465	\N	11500
4396	433	4548	\N	60800
4397	434	4559	\N	377050
4398	440	6498	\N	4953
4399	440	6499	\N	49970
4400	440	6503	\N	17815
4401	440	6504	\N	10500
4402	440	6516	\N	800000
4403	440	6517	\N	800000
4404	440	6518	\N	700000
4405	414	7676	\N	2000
4406	414	7677	\N	2300
4407	440	6519	\N	800000
4408	440	6520	\N	750000
4409	440	6521	\N	800000
4410	440	6522	\N	750000
4411	440	6524	\N	250000
4412	433	4541	\N	47435
4413	1059	6794	\N	220
4414	1059	6797	\N	120
4415	440	6525	\N	600000
4416	440	6526	\N	800000
4417	440	6527	\N	800000
4418	440	6528	\N	700000
4419	440	6529	\N	800000
4420	440	6530	\N	800000
4421	440	6531	\N	800000
4422	440	6532	\N	700000
4423	440	6533	\N	200000
4424	440	6534	\N	600000
4425	440	6535	\N	200000
4426	440	6536	\N	400000
4427	440	6537	\N	400000
4428	440	6538	\N	100000
4429	440	6539	\N	200000
4430	440	6540	\N	300000
4431	440	6541	\N	400000
4432	440	6542	\N	200000
4433	440	6543	\N	2500
4434	440	6544	\N	3750
4435	440	6545	\N	5000
4436	440	6549	\N	1000
4437	440	6550	\N	5500
4438	440	6553	\N	250
4439	440	6559	\N	270
4440	440	6564	\N	495
4441	440	6565	\N	810
4442	440	6568	\N	19500
4443	440	6569	\N	4500
4444	440	6574	\N	1600
4445	440	6575	\N	2800
4446	440	6577	\N	700
4447	440	6578	\N	1500
4448	440	6579	\N	14000
4449	440	6585	\N	810
4450	440	6588	\N	21960
4451	440	6589	\N	810
4452	440	6594	\N	21000
4453	440	6597	\N	980
4454	440	6601	\N	50
4455	440	6610	\N	150
4456	440	6612	\N	49850
4457	440	6613	\N	27430
4458	440	6614	\N	6200
4459	440	6615	\N	6200
4460	440	6616	\N	4650
4461	440	6617	\N	3870
4462	440	6618	\N	1550
4463	440	6619	\N	4160
4464	440	6620	\N	6550
4465	440	6621	\N	2775
4466	440	6622	\N	4590
4467	440	6623	\N	1710
4468	440	6624	\N	6120
4469	440	6625	\N	9500
4470	440	6626	\N	905
4471	440	6630	\N	49518
4472	440	6631	\N	28225
4473	440	6632	\N	2790
4474	440	6633	\N	1170
4475	440	6635	\N	3024
4476	440	6636	\N	29160
4477	440	6637	\N	9955
4478	440	6638	\N	49950
4479	440	6641	\N	2340
4480	440	6645	\N	2160
4481	440	6646	\N	5535
4482	440	6647	\N	2645
4483	1090	8770	\N	3000000
4484	1086	8732	\N	26500
4485	1086	8731	\N	28500
4486	1086	8733	\N	19800
4487	1086	8734	\N	3500
4488	1086	8735	\N	3200
4489	1085	8730	\N	72500
4490	1072	8586	\N	240500
4491	1084	8696	\N	37030
4492	1084	8685	\N	2310
4493	440	6649	\N	2700
4494	440	6652	\N	1350
4495	440	6653	\N	25200
4496	440	6656	\N	1800
4497	440	6658	\N	990
4498	440	6660	\N	49850
4499	440	6661	\N	15000
4500	440	6676	\N	10220
4501	440	6677	\N	17640
4502	440	6678	\N	27720
4503	440	6679	\N	20980
4504	440	6680	\N	236500
4505	440	6681	\N	532000
4506	440	6682	\N	355000
4507	440	6687	\N	6300
4508	440	6699	\N	4325
4509	440	6700	\N	30580
4510	440	6701	\N	4700
4511	440	6702	\N	2380
4512	440	6703	\N	3500
4513	440	6711	\N	1200
4514	440	6717	\N	2500
4515	440	6718	\N	500
4516	440	6721	\N	2550
4517	440	6723	\N	6800
4518	440	6724	\N	49900
4519	440	6725	\N	1530
4520	440	6726	\N	1890
4521	440	6729	\N	260
4522	440	6735	\N	10
4523	825	2139	\N	1975960
4524	440	6736	\N	10
4525	440	6742	\N	250
4526	825	2195	\N	336736
4527	1097	8779	\N	6000
4528	1084	8711	\N	685
4529	1097	8791	\N	3050
4530	1084	8714	\N	53870
4531	1102	8856	\N	2500
4532	1097	8780	\N	5500
4533	1097	8783	\N	506
4534	1097	8784	\N	2550
4535	1097	8785	\N	1725
4536	1084	8660	\N	2806
4537	1084	8665	\N	2806
4538	1084	8680	\N	6595
4539	1097	8788	\N	601
4540	1097	8786	\N	2404
4541	1097	8790	\N	417
4542	1097	8789	\N	601
4543	440	6748	\N	300
4544	440	6749	\N	1000
4545	440	6752	\N	2000
4546	440	6753	\N	1500
4547	440	6755	\N	825
4548	440	6756	\N	2000
4549	440	6761	\N	5000
4550	440	6768	\N	800
4551	440	6774	\N	14000
4552	440	6786	\N	2500
4553	440	6787	\N	17500
4554	443	8394	\N	800000
4555	443	8405	\N	40000
4556	443	8416	\N	800000
4557	443	8417	\N	700000
4558	443	8418	\N	700000
4559	443	8420	\N	900000
4560	443	8421	\N	800000
4561	443	8427	\N	800000
4562	443	8429	\N	800000
4563	443	8430	\N	600000
4564	443	8431	\N	800000
4565	443	8433	\N	700000
4566	443	8434	\N	700000
4567	443	8435	\N	600000
4568	1104	8894	\N	16500
4569	1104	8895	\N	9800
4570	727	1649	\N	809828
4571	1102	8883	\N	17600
4572	1102	8885	\N	10200
4573	1103	8887	\N	900
4574	1103	8888	\N	800
4575	1103	8889	\N	800
4576	1103	8890	\N	800
4577	1103	8892	\N	60000
4578	1101	8825	\N	200
4579	1101	8824	\N	375
4580	1101	8823	\N	150
4581	1101	8822	\N	800
4582	1101	8821	\N	1120
4583	1102	8881	\N	2150
4584	1102	8886	\N	11475
4585	1102	8884	\N	2150
4586	1102	8882	\N	5300
4587	727	1643	\N	91776
4588	727	1648	\N	437760
4589	443	8436	\N	400000
4590	443	8438	\N	600000
4591	456	4601	\N	180730
4592	456	4602	\N	54810
4593	456	4604	\N	1105
4594	456	4605	\N	4420
4595	456	4606	\N	6630
4596	456	4615	\N	78375
4597	456	4617	\N	84320
4598	1104	8896	\N	6200
4599	1104	8897	\N	11700
4600	1104	8902	\N	9400
4601	1104	8903	\N	10400
4602	456	4618	\N	51560
4603	456	4619	\N	25780
4604	456	4620	\N	3095
4605	456	4621	\N	1030
4606	456	4622	\N	21285
4607	456	4623	\N	830
4608	456	4624	\N	123760
4609	457	4629	\N	1000
4610	458	4661	\N	10600
4611	458	4662	\N	110250
4612	458	4663	\N	4250
4613	458	4664	\N	9330
4614	458	4665	\N	14315
4615	458	4666	\N	51410
4616	458	4667	\N	81620
4617	458	4668	\N	2335
4618	458	4672	\N	58515
4619	458	4673	\N	14385
4620	458	4674	\N	73150
4621	459	4677	\N	38610
4622	596	739	\N	1090
4623	459	4678	\N	25000
4624	459	4679	\N	25000
4625	459	4680	\N	25000
4626	459	4683	\N	2005
4627	459	4685	\N	87900
4628	459	4686	\N	37950
4629	459	4699	\N	950
4630	461	4705	\N	169456
4631	461	4706	\N	54808
4632	461	4707	\N	19669
4633	461	4712	\N	51563
4634	461	4713	\N	25782
4635	461	4714	\N	3094
4636	461	4716	\N	415800
4637	458	4670	\N	27880
4638	456	4607	\N	7515
4639	464	6790	\N	689300
4640	464	6791	\N	285870
4641	443	8422	\N	900000
4642	443	8387	\N	850000
4643	443	8414	\N	900000
4644	443	8408	\N	825000
4645	443	8399	\N	700000
4646	443	8386	\N	600000
4647	443	8391	\N	800000
4648	443	8388	\N	990000
4649	443	8437	\N	100000
4650	1104	8893	\N	15500
4651	1104	8904	\N	9200
4652	1104	8906	\N	8500
4653	1104	8907	\N	8500
4654	1104	8908	\N	21830
4655	1104	8909	\N	3540
4656	1104	8910	\N	3540
4657	1104	8911	\N	1180
4658	1104	8912	\N	1240
4659	1102	8880	\N	5775
4660	1102	8879	\N	14000
4661	1102	8878	\N	32750
4662	1106	8918	\N	112.5
4663	1106	8919	\N	28000
4664	1106	8920	\N	46000
4665	1106	8921	\N	900
4666	1106	8922	\N	4000
4667	1106	8923	\N	616
4668	1106	8917	\N	6000
4669	1106	8924	\N	232
4670	471	4730	\N	54275
4671	1109	8934	\N	2000
4672	1109	8933	\N	150
4673	469	4723	\N	169160
4674	471	4727	\N	30000
4675	471	4728	\N	9775
4676	471	4732	\N	5175
4677	473	4754	\N	21735
4678	474	4762	\N	6500
4679	474	4763	\N	7000
4680	476	4768	\N	1492500
4681	487	4867	\N	17250
4682	487	4884	\N	9200
4683	487	4900	\N	1000
4684	487	4907	\N	285
4685	487	4918	\N	24000
4686	487	4919	\N	45000
4687	488	4920	\N	1347080
4688	488	4921	\N	1564000
4689	462	8330	\N	70214.18
4690	462	8334	\N	51753.89
4691	462	8340	\N	420614
4692	480	6962	\N	750
4693	480	6961	\N	1400
4694	480	6964	\N	1500
4695	212	7228	0	2660
4696	488	4922	\N	1249492
4697	1109	8940	\N	4500
4698	1109	8939	\N	3000
4699	1109	8938	\N	2700
4700	453	8553	0	125000
4701	141	7091	0	62400
4702	167	6173	0	2400
4703	169	6261	0	7800
4704	883	6838	0	9143
4705	1109	8937	\N	2700
4706	1109	8936	\N	600
4707	1109	8932	\N	600
4708	1109	8930	\N	1000
4709	1109	8929	\N	4500
4710	1109	8928	\N	1500
4711	1109	8927	\N	500
4712	1109	8926	\N	11500
4713	1109	8931	\N	9000
4714	1109	8963	\N	5500
4715	1109	8950	\N	500
4716	1109	8949	\N	2500
4717	1109	8948	\N	1800
4718	1109	8947	\N	5500
4719	1109	8942	\N	2700
4720	1109	8943	\N	5000
4721	1109	8944	\N	350
4722	1109	8945	\N	1500
4723	1109	8946	\N	55000
4724	1109	8941	\N	2700
4725	480	6960	\N	2700
4726	480	6963	\N	250
4727	480	6965	\N	500
4728	488	4924	\N	126900
4729	488	4927	\N	80000
4730	488	4928	\N	9112
4731	489	4929	\N	175
4732	489	4931	\N	175
4733	489	4936	\N	3450
4734	489	4938	\N	21000
4735	489	4939	\N	345
4736	489	4951	\N	13500
4737	489	4952	\N	2760
4738	489	4957	\N	2530
4739	489	4961	\N	25300
4740	491	4966	\N	59800
4741	1067	8514	\N	5000
4742	493	4993	\N	13800
4743	493	4996	\N	16640
4744	493	4997	\N	32240
4745	493	4998	\N	6864
4746	493	4999	\N	5824
4747	596	738	\N	1680
4748	493	5000	\N	6032
4749	493	5001	\N	4264
4750	493	5002	\N	7862
4751	493	5003	\N	13308
4752	493	5008	\N	372
4753	493	5009	\N	20
4754	493	5010	\N	810
4755	493	5012	\N	18400
4756	493	5018	\N	920
4757	494	5027	\N	80000
4758	496	5028	\N	201810
4759	496	5029	\N	16615
4760	496	5030	\N	262524
4761	496	5031	\N	18047
4762	496	5032	\N	30087
4763	496	5033	\N	92335
4764	496	5034	\N	41739
4765	496	5035	\N	91310
4766	496	5037	\N	386154
4767	499	5039	\N	71400
4768	501	5048	\N	5750
4769	501	5058	\N	3100
4770	501	5060	\N	5750
4771	501	5061	\N	11500
4772	501	5064	\N	20000
4773	501	5065	\N	15000
4774	502	6995	\N	12870
4775	502	6996	\N	14546
4776	502	6997	\N	3637
4777	504	5093	\N	13500
4778	504	5094	\N	1000
4779	504	5095	\N	500
4780	504	5096	\N	550
4781	504	5097	\N	1200
4782	504	5110	\N	40965
4783	504	5114	\N	195
4784	505	6792	\N	215800
4785	508	6999	\N	405
4786	508	7001	\N	125
4787	508	7003	\N	4500
4788	508	7005	\N	1980
4789	508	7006	\N	2260
4790	508	7007	\N	1730
4791	508	7008	\N	1850
4792	509	5134	\N	97000
4793	510	5135	\N	113647
4794	511	5141	\N	30450
4795	511	5142	\N	26250
4796	511	5143	\N	85600
4797	511	5144	\N	70580
4798	511	5145	\N	64040
4799	512	5166	\N	2500
4800	512	5167	\N	600
4801	512	5168	\N	2500
4802	512	5169	\N	3700
4803	512	5170	\N	1230
4804	512	5172	\N	2000
4805	512	5174	\N	18000
4806	523	95	\N	550000
4807	523	98	\N	550000
4808	532	6793	\N	739015
4809	533	7011	\N	80000
4810	534	5396	\N	450000
4811	536	5185	\N	66666.67
4812	536	5192	\N	20000
4813	537	5193	\N	650000
4814	543	147	\N	25300
4815	543	160	\N	1150
4816	543	164	\N	58
4817	1012	2838	\N	1550
4818	1012	2839	\N	1500000
4819	1043	5328	\N	250000
4820	1043	5327	\N	400000
4821	1046	5332	\N	300000
4822	1046	5333	\N	200000
4823	1055	5568	\N	14500
4824	1055	5567	\N	55000
4825	543	176	\N	6900
4826	510	5137	\N	5750
4827	543	177	\N	5750
4828	543	175	\N	11500
4829	543	182	\N	69000
4830	543	191	\N	15525
4831	543	198	\N	1725
4832	544	199	\N	2591628
4833	551	5197	\N	5460
4834	563	286	\N	25826
4835	563	289	\N	4204
4836	563	291	\N	2402
4837	563	293	\N	15315
4838	564	302	\N	34665
4839	564	303	\N	49085
4840	564	304	\N	58070
4841	550	247	\N	1000
4842	550	249	\N	490
4843	545	227	\N	88500
4844	545	224	\N	49435
4845	545	223	\N	48655
4846	545	220	\N	41960
4847	556	284	\N	550
4848	550	246	\N	210
4849	550	251	\N	25620
4850	545	219	\N	39605
4851	545	210	\N	23140
4852	545	208	\N	10315
4853	545	215	\N	6065
4854	545	214	\N	6065
4855	545	212	\N	5725
4856	545	221	\N	4215
4857	545	216	\N	1685
4858	545	222	\N	2385
4859	545	225	\N	1940
4860	564	305	\N	23805
4861	564	307	\N	94390
4862	564	308	\N	18225
4863	564	309	\N	11730
4864	564	310	\N	11730
4865	564	311	\N	19375
4866	564	314	\N	221475
4867	567	321	\N	3500
4868	567	322	\N	3000
4869	567	330	\N	20625
4870	567	334	\N	21340
4871	567	336	\N	48000
4872	567	337	\N	33000
4873	545	200	\N	6125
4874	531	101	\N	\N
4875	546	230	\N	27280
4876	539	102	\N	229500
4877	1109	8969	\N	1200
4878	1109	8970	\N	1500
4879	1109	8971	\N	1000
4880	1109	8973	\N	365
4881	1109	8974	\N	185
4882	668	1162	\N	2129500
4883	545	226	\N	87890
4884	545	218	\N	35585
4885	545	217	\N	35440
4886	545	206	\N	11730
4887	545	201	\N	850
4888	545	205	\N	10470
4889	545	202	\N	8655
4890	545	207	\N	20020
4891	543	178	\N	3450
4892	546	231	\N	39300
4893	585	519	\N	\N
4894	989	2661	\N	2500
4895	567	338	\N	102850
4896	567	341	\N	5750
4897	989	2662	\N	20000
4898	989	2650	\N	15000
4899	567	343	\N	5200
4900	989	2660	\N	210000
4901	989	2652	\N	200000
4902	989	2653	\N	426500
4903	545	209	\N	20900
4904	545	211	\N	24025
4905	545	213	\N	30110
4906	545	204	\N	9735
4907	552	256	\N	1453358
4908	540	254	\N	505500
4909	540	253	\N	185279
4910	552	258	\N	446500
4911	540	104	\N	440000
4912	541	127	\N	69
4913	541	128	\N	46
4914	541	129	\N	12
4915	541	130	\N	12
4916	541	131	\N	2300
4917	568	359	\N	12900
4918	568	360	\N	13000
4919	568	361	\N	2500
4920	568	363	\N	14500
4921	568	365	\N	12000
4922	568	367	\N	14500
4923	571	395	\N	7000
4924	568	368	\N	12500
4925	568	369	\N	12500
4926	596	745	\N	28750
4927	546	229	\N	637824
4928	541	124	\N	2875
4929	541	121	\N	2300
4930	541	119	\N	6291
4931	546	232	\N	38520
4932	541	139	\N	3450
4933	541	122	\N	2875
4934	541	123	\N	9200
4935	541	125	\N	230
4936	541	140	\N	230
4937	541	126	\N	46
4938	541	132	\N	173
4939	541	133	\N	1840
4940	541	134	\N	288
4941	541	135	\N	920
4942	541	136	\N	230
4943	541	137	\N	173
4944	541	138	\N	58
4945	541	141	\N	1150
4946	546	234	\N	191725
4947	546	233	\N	52360
4948	543	190	\N	26450
4949	567	329	\N	6900
4950	554	276	\N	\N
4951	553	274	\N	\N
4952	585	520	\N	\N
4953	571	398	\N	78500
4954	571	387	\N	36000
4955	568	370	\N	17200
4956	568	371	\N	2500
4957	568	373	\N	9500
4958	568	374	\N	9500
4959	568	377	\N	25000
4960	571	393	\N	1680
4961	571	396	\N	1000
4962	573	420	\N	16700
4963	574	421	\N	87600
4964	949	7202	\N	575
4965	574	423	\N	478610
4966	575	427	\N	345
4967	575	429	\N	175
4968	575	432	\N	5750
4969	571	391	\N	7800
4970	571	397	\N	20000
4971	580	460	\N	150000
4972	581	461	\N	480000
4973	584	469	\N	40250
4974	584	472	\N	23400
4975	586	596	\N	265
4976	586	614	\N	1320
4977	588	627	\N	16100
4978	588	628	\N	50306
4979	590	650	\N	19500
4980	555	282	\N	83000
4981	555	281	\N	550
4982	555	279	\N	260000
4983	554	275	\N	\N
4984	554	278	\N	\N
4985	554	277	\N	\N
4986	571	389	\N	1580
4987	571	390	\N	1450
4988	571	392	\N	26000
4989	584	462	\N	195000
4990	584	463	\N	34500
4991	576	439	\N	236500
4992	576	434	\N	331500
4993	576	435	\N	89000
4994	572	403	\N	\N
4995	572	402	\N	\N
4996	1153	9375	\N	9500
4997	577	446	\N	\N
4998	590	651	\N	27500
4999	590	653	\N	7550
5000	592	662	\N	6800
5001	601	813	\N	1453900
5002	639	984	\N	1725
5003	639	986	\N	400
5004	639	992	\N	182835
5005	589	631	\N	40000
5006	639	991	\N	144200
5007	602	818	\N	72000
5008	602	817	\N	52360
5009	639	993	\N	50000
5010	639	999	\N	11155
5011	639	961	\N	9200
5012	639	982	\N	7000
5013	639	988	\N	5060
5014	949	7201	\N	690
5015	949	7200	\N	1440
5016	949	7197	\N	920
5017	639	998	\N	4025
5018	949	7196	\N	2070
5019	639	995	\N	1680
5020	639	990	\N	1090
5021	639	975	\N	1080
5022	639	994	\N	865
5023	639	979	\N	275
5024	586	612	\N	80
5025	639	978	\N	230
5026	639	977	\N	575
5027	639	997	\N	690
5028	639	976	\N	860
5029	639	1008	\N	250000
5030	640	1014	\N	30000
5031	642	1035	\N	7700
5032	642	1036	\N	5680
5033	642	1041	\N	13
5034	649	1051	\N	70581
5035	649	1092	\N	2185
5036	652	1114	\N	1754594
5037	666	1148	\N	162500
5038	666	1159	\N	320000
5039	577	448	\N	\N
5040	577	440	\N	\N
5041	577	442	\N	\N
5042	586	615	\N	850
5043	586	582	\N	18400
5044	639	1000	\N	391
5045	639	1001	\N	3450
5046	639	963	\N	6900
5047	639	964	\N	8625
5048	574	424	\N	124400
5049	585	501	\N	\N
5050	577	444	\N	\N
5051	673	1286	\N	207500
5052	679	1309	\N	1230
5053	679	1310	\N	595
5054	679	1311	\N	490
5055	679	1312	\N	380
5056	679	1313	\N	165
5057	679	1318	\N	7610
5058	679	1319	\N	5620
5059	640	1020	\N	48230
5060	640	1027	\N	2950
5061	640	1012	\N	25500
5062	640	1021	\N	4950
5063	640	1013	\N	4700
5064	640	1024	\N	1450
5065	640	1022	\N	1350
5066	649	1099	\N	1150
5067	679	1329	\N	1130
5068	679	1330	\N	1910
5069	685	1363	\N	175000
5070	688	1382	\N	6900
5071	693	1429	\N	53670
5072	693	1430	\N	4515
5073	693	1431	\N	16805
5074	694	1432	\N	67375
5075	596	744	\N	3450
5076	579	449	\N	16200
5077	579	450	\N	16200
5078	585	494	\N	\N
5079	579	451	\N	16200
5080	579	458	\N	16200
5081	577	443	\N	\N
5082	579	456	\N	16200
5083	579	459	\N	16200
5084	585	473	\N	\N
5085	596	672	\N	9200
5086	596	675	\N	2875
5087	585	528	\N	\N
5088	585	474	\N	\N
5089	585	475	\N	\N
5090	585	476	\N	\N
5091	585	477	\N	\N
5092	585	478	\N	\N
5093	585	479	\N	\N
5094	585	480	\N	\N
5095	585	481	\N	\N
5096	585	482	\N	\N
5097	585	483	\N	\N
5098	652	1108	\N	5095992.32
5099	673	1225	\N	29700
5100	639	1006	\N	2875
5101	584	464	\N	46000
5102	584	465	\N	2886
5103	584	466	\N	3270
5104	584	468	\N	690
5105	585	510	\N	\N
5106	585	511	\N	\N
5107	585	512	\N	\N
5108	585	513	\N	\N
5109	585	507	\N	\N
5110	585	517	\N	\N
5111	585	518	\N	\N
5112	695	1433	\N	488400
5113	695	1434	\N	399600
5114	698	1446	\N	160000
5115	698	1449	\N	3250
5116	698	1450	\N	4035
5117	698	1451	\N	2115
5118	585	486	\N	\N
5119	585	488	\N	\N
5120	585	489	\N	\N
5121	585	514	\N	\N
5122	585	508	\N	\N
5123	585	487	\N	\N
5124	585	484	\N	\N
5125	585	485	\N	\N
5126	585	502	\N	\N
5127	585	490	\N	\N
5128	585	491	\N	\N
5129	585	492	\N	\N
5130	585	493	\N	\N
5131	585	495	\N	\N
5132	585	496	\N	\N
5133	585	497	\N	\N
5134	585	498	\N	\N
5135	585	499	\N	\N
5136	585	500	\N	\N
5137	585	503	\N	\N
5138	585	504	\N	\N
5139	585	505	\N	\N
5140	585	506	\N	\N
5141	585	515	\N	\N
5142	585	516	\N	\N
5143	596	686	\N	460
5144	596	687	\N	310
5145	596	688	\N	115
5146	596	689	\N	3660
5147	596	690	\N	575
5148	673	1278	\N	29750
5149	673	1230	\N	52250
5150	673	1229	\N	136700
5151	673	1296	\N	43400
5152	673	1254	\N	82950
5153	673	1251	\N	47500
5154	673	1250	\N	1150
5155	673	1262	\N	2010
5156	673	1263	\N	1150
5157	673	1294	\N	1725
5158	596	706	\N	6900
5159	596	702	\N	1150
5160	585	523	\N	\N
5161	591	655	\N	6800
5162	585	521	\N	\N
5163	585	522	\N	\N
5164	596	752	\N	2875
5165	602	820	\N	316800
5166	602	821	\N	191725
5167	698	1453	\N	13550
5168	585	524	\N	\N
5169	698	1454	\N	19795
5170	698	1455	\N	3010
5171	698	1456	\N	12085
5172	698	1457	\N	16540
5173	698	1459	\N	4117
5174	698	1460	\N	4136
5175	698	1461	\N	3335
5176	698	1463	\N	20000
5177	698	1466	\N	1760
5178	710	1510	\N	31700
5179	710	1511	\N	295850
5180	710	1691	\N	132800
5181	712	1514	\N	11898
5182	712	1515	\N	10682
5183	712	1516	\N	11254
5184	712	1517	\N	12612
5185	585	525	\N	\N
5186	585	526	\N	\N
5187	585	527	\N	\N
5188	585	529	\N	\N
5189	585	530	\N	\N
5190	585	531	\N	\N
5191	585	532	\N	\N
5192	585	533	\N	\N
5193	585	534	\N	\N
5194	585	535	\N	\N
5195	585	536	\N	\N
5196	585	538	\N	\N
5197	585	540	\N	\N
5198	587	620	\N	109500
5199	596	753	\N	18685
5200	585	537	\N	\N
5201	596	670	\N	5750
5202	638	907	\N	\N
5203	596	673	\N	2235
5204	638	908	\N	\N
5205	638	910	\N	\N
5206	638	918	\N	\N
5207	638	904	\N	\N
5208	638	905	\N	\N
5209	638	919	\N	\N
5210	638	920	\N	\N
5211	638	921	\N	\N
5212	638	922	\N	\N
5213	638	923	\N	\N
5214	638	906	\N	\N
5215	638	924	\N	\N
5216	591	654	\N	8530
5217	591	657	\N	360
5218	591	658	\N	960
5219	591	660	\N	600
5220	586	575	\N	4890
5221	586	583	\N	8400
5222	586	584	\N	2875
5223	586	585	\N	53950
5224	586	586	\N	750
5225	596	746	\N	13800
5226	596	717	\N	2875
5227	596	715	\N	2070
5228	684	1358	\N	55800
5229	679	1321	\N	700
5230	779	1888	\N	17000
5231	787	1977	\N	12800
5232	452	8589	\N	11500
5233	452	8597	\N	8333.333
5234	452	8592	\N	7500
5235	596	714	\N	1150
5236	596	708	\N	3450
5237	596	707	\N	8625
5238	596	712	\N	575
5239	596	713	\N	230
5240	949	7191	\N	21415
5241	693	1428	\N	475000
5242	712	1520	\N	17800
5243	712	1521	\N	13622
5244	720	1593	\N	7000
5245	720	1599	\N	2875
5246	720	1611	\N	1400
5247	731	1657	\N	12500
5248	731	1658	\N	45000
5249	731	1662	\N	2450
5250	112	5744	\N	1150
5251	731	1664	\N	1760
5252	733	1695	\N	67038.84
5253	733	1696	\N	54900.12
5254	733	1698	\N	43864.92
5255	733	1699	\N	25380.96
5256	733	1700	\N	43354.96
5257	596	716	\N	520
5258	596	751	\N	2875
5259	596	694	\N	12
5260	788	1978	\N	30000
5261	596	725	\N	7000
5262	596	724	\N	1785
5263	547	235	\N	1100000
5264	596	718	\N	1080
5265	596	705	\N	4600
5266	596	704	\N	9200
5267	596	747	\N	28000
5268	596	709	\N	575
5269	596	711	\N	1150
5270	586	590	\N	2100
5271	586	591	\N	3675
5272	779	1879	\N	140000
5273	597	755	\N	\N
5274	596	729	\N	400
5275	596	727	\N	1725
5276	596	726	\N	1610
5277	596	680	\N	230
5278	596	684	\N	230
5279	596	683	\N	3450
5280	596	682	\N	57
5281	734	1737	\N	6900
5282	734	1742	\N	11500
5283	949	7187	\N	1840
5284	949	7186	\N	4600
5285	949	7184	\N	4025
5286	949	7178	\N	4600
5287	949	7177	\N	8500
5288	949	7176	\N	9350
5289	949	7174	\N	1683
5290	949	7172	\N	1645
5291	949	7166	\N	5500
5292	949	7164	\N	5000
5293	949	7163	\N	26755
5294	738	1778	\N	23000
5295	738	1782	\N	30475
5296	740	1787	\N	134500
5297	779	1881	\N	20500
5298	731	1661	\N	90000
5299	1052	5457	\N	\N
5300	731	1659	\N	6500
5301	734	1718	\N	1600
5302	720	1567	\N	1500
5303	734	1709	\N	1012
5304	734	1717	\N	875
5305	750	1824	\N	3800
5306	596	730	\N	1505
5307	596	734	\N	144200
5308	596	733	\N	1090
5309	596	732	\N	3450
5310	596	731	\N	5060
5311	596	685	\N	1150
5312	596	681	\N	172
5313	596	679	\N	1840
5314	596	678	\N	69
5315	596	677	\N	46
5316	596	674	\N	6670
5317	596	728	\N	6890
5318	706	1642	\N	52910
5319	720	1579	\N	2100
5320	720	1588	\N	68250
5321	720	1602	\N	10000
5322	720	1603	\N	6855
5323	720	1559	\N	15475
5324	720	1572	\N	2800
5325	729	1651	\N	206940
5326	729	1656	\N	4900
5327	729	1653	\N	19900
5328	600	766	\N	\N
5329	600	764	\N	\N
5330	600	762	\N	\N
5331	600	761	\N	\N
5332	600	786	\N	\N
5333	600	783	\N	\N
5334	600	779	\N	\N
5335	600	777	\N	\N
5336	600	765	\N	\N
5337	600	793	\N	\N
5338	596	710	\N	1440
5339	596	691	\N	4600
5340	596	693	\N	12
5341	596	696	\N	190
5342	596	697	\N	3105
5343	596	698	\N	290
5344	596	699	\N	920
5345	600	804	\N	\N
5346	774	1857	\N	9500
5347	779	1880	\N	62000
5348	779	1882	\N	77000
5349	779	1883	\N	13000
5350	779	1884	\N	19500
5351	779	1885	\N	225000
5352	600	812	\N	\N
5353	600	811	\N	\N
5354	600	810	\N	\N
5355	600	808	\N	\N
5356	600	807	\N	\N
5357	600	802	\N	\N
5358	659	1130	\N	650000
5359	651	1102	\N	\N
5360	651	1106	\N	\N
5361	600	797	\N	\N
5362	600	767	\N	\N
5363	600	774	\N	\N
5364	600	775	\N	\N
5365	600	791	\N	\N
5366	600	789	\N	\N
5367	600	787	\N	\N
5368	600	771	\N	\N
5369	600	768	\N	\N
5370	600	763	\N	\N
5371	600	780	\N	\N
5372	607	833	\N	1150
5373	596	723	\N	1150
5374	596	722	\N	275
5375	596	721	\N	230
5376	641	1030	\N	650000
5377	822	2114	\N	1000
5378	779	1886	\N	28500
5379	1404	11488	\N	200
5380	779	1887	\N	14500
5381	787	2001	\N	31250
5382	780	1921	\N	17500
5383	790	2019	\N	134720
5384	804	2070	\N	15000
5385	780	1919	\N	26250
5386	826	2141	\N	11898
5387	826	2142	\N	10682
5388	826	2143	\N	11254
5389	822	2109	\N	14125
5390	826	2144	\N	14500
5391	826	2145	\N	17500
5392	826	2146	\N	13622
5393	826	2147	\N	12300
5394	826	2148	\N	12500
5395	826	2149	\N	12500
5396	826	2150	\N	17500
5397	826	2151	\N	4200
5398	826	2152	\N	4200
5399	826	2153	\N	4200
5400	826	2154	\N	4200
5401	826	2155	\N	8000
5402	827	2156	\N	2970000
5403	949	7162	\N	3465
5404	949	7161	\N	4460
5405	829	2163	\N	101166.83
5406	596	735	\N	182835
5407	822	2106	\N	33250
5408	788	1976	\N	84375
5409	736	1748	\N	2875
5410	720	1561	\N	17100
5411	720	1562	\N	300
5412	655	1117	\N	2903
5413	552	255	\N	1026950
5414	669	1174	\N	40000
5415	669	1165	\N	450
5416	669	1168	\N	450
5417	669	1166	\N	450
5418	822	2107	\N	22875
5419	822	2108	\N	97910
5420	822	2115	\N	11000
5421	822	2113	\N	1750
5422	788	2011	\N	5875
5423	788	1979	\N	13375
5424	788	1980	\N	8750
5425	788	1982	\N	40625
5426	788	1984	\N	21250
5427	669	1173	\N	320000
5428	669	1169	\N	450
5429	664	1140	\N	\N
5430	669	1163	\N	162500
5431	665	1144	\N	\N
5432	596	719	\N	860
5433	596	743	\N	391
5434	596	742	\N	11155
5435	596	740	\N	690
5436	596	737	\N	865
5437	596	736	\N	50000
5438	596	692	\N	60
5439	596	695	\N	2300
5440	665	1145	\N	\N
5441	669	1164	\N	430
5442	822	2111	\N	27800
5443	822	2112	\N	12375
5444	829	2164	\N	208751.5
5445	830	2165	\N	7800
5446	830	2169	\N	11500
5447	830	2170	\N	1850
5448	830	2171	\N	22800
5449	830	2172	\N	28400
5450	831	2173	\N	2688
5451	831	2175	\N	2880
5452	831	2176	\N	6912
5453	831	2178	\N	6528
5454	831	2179	\N	6528
5455	831	2181	\N	12291.84
5456	831	2182	\N	11873.28
5457	831	2183	\N	11174.4
5458	822	2121	\N	6350
5459	838	2252	\N	899000
5460	840	2260	\N	12500
5461	852	2347	\N	84000
5462	852	2348	\N	7000
5463	853	2360	\N	13000
5464	853	2364	\N	18423
5465	853	2365	\N	1300
5466	854	2390	\N	23460
5467	860	2409	\N	4500
5468	860	2410	\N	3500
5469	860	2414	\N	9500
5470	860	2420	\N	19900
5471	861	2426	\N	84983.85
5472	861	2427	\N	220512.6
5473	861	2428	\N	141487.5
5474	863	2461	\N	1560
5475	863	2468	\N	84375
5476	863	2471	\N	25000
5477	869	2494	\N	300000
5478	875	7842	\N	35000
5479	876	5388	\N	475000
5480	876	5389	\N	125800
5481	876	5391	\N	26266.67
5482	876	5392	\N	1400000
5483	669	1170	\N	4500
5484	669	1171	\N	400
5485	669	1172	\N	3000
5486	788	2005	\N	18750
5487	876	5393	\N	195000
5488	876	5394	\N	102375
5489	877	5385	\N	543353
5490	878	5386	\N	185093.11
5491	881	5400	\N	280000
5492	881	5401	\N	70000
5493	883	6832	\N	22700
5494	883	6840	\N	80
5495	869	2495	\N	55000
5496	845	2289	\N	188300
5497	860	2422	\N	12500
5498	860	2408	\N	39000
5499	860	2423	\N	17800
5500	863	2462	\N	7875
5501	883	6841	\N	318
5502	883	6842	\N	1018
5503	884	7880	\N	50000
5504	884	7883	\N	110500
5505	888	5430	\N	573750
5506	889	7885	\N	11183000
5507	890	7886	\N	59500
5508	890	7887	\N	0
5509	891	5432	\N	5270000
5510	893	7890	\N	5486666.67
5511	893	7891	\N	0
5512	893	7892	\N	0
5513	893	7893	\N	0
5514	894	7894	\N	103300
5515	895	7895	\N	561000
5516	896	7896	\N	2166000
5517	896	7897	\N	0
5518	897	7899	\N	1003200
5519	897	7900	\N	0
5520	916	5483	\N	294875
5521	922	7148	\N	1242111.9
5522	942	7143	\N	12500
5523	922	7149	\N	280575.21
5524	923	7151	\N	1242111.97
5525	942	7134	\N	11590
5526	923	7152	\N	280575.21
5527	924	7154	\N	1242111.97
5528	924	7155	\N	280575.21
5529	925	5479	\N	659585
5530	925	5480	\N	1449126
5531	925	5481	\N	14794
5532	925	5482	\N	28750
5533	945	5564	\N	7370
5534	926	5478	\N	355000
5535	927	5552	\N	659585
5536	927	5553	\N	1449126
5537	927	5555	\N	31875
5538	863	2458	\N	130173
5539	672	1194	\N	6900
5540	552	257	\N	1339500
5541	673	1275	\N	2650
5542	682	1335	\N	\N
5543	682	1334	\N	\N
5544	682	1333	\N	\N
5545	682	1337	\N	\N
5546	683	1347	\N	\N
5547	683	1351	\N	\N
5548	927	5556	\N	28750
5549	927	5557	\N	355000
5550	929	6871	\N	258552
5551	930	5617	\N	20871.83
5552	930	5618	\N	75330.58
5553	931	5593	\N	646393.3
5554	931	5594	\N	1420143.48
5555	931	5595	\N	31237.5
5556	931	5596	\N	28175
5557	931	5597	\N	347900
5558	933	7159	\N	34465
5559	934	6877	\N	75000
5560	935	5517	\N	480000
5561	936	5490	\N	21840
5562	947	7273	\N	420
5563	940	7957	\N	761415.81
5564	940	7958	\N	180813.33
5565	940	7960	\N	180813.33
5566	940	7961	\N	224721.76
5567	940	7962	\N	67524.72
5568	941	7963	\N	42631.62
5569	942	7107	\N	13080
5570	942	7108	\N	7188
5571	942	7109	\N	36600
5572	942	7111	\N	14400
5573	942	7112	\N	76800
5574	942	7113	\N	18000
5575	942	7114	\N	1800
5576	942	7115	\N	9600
5577	942	7116	\N	1220
5578	942	7117	\N	1710
5579	942	7118	\N	15715
5580	942	7133	\N	18265
5581	942	7135	\N	4880
5582	942	7144	\N	10600
5583	942	7145	\N	12500
5584	942	7146	\N	10600
5585	690	1402	\N	500
5586	683	1345	\N	\N
5587	683	1344	\N	\N
5588	683	1352	\N	\N
5589	683	1346	\N	\N
5590	942	7147	\N	12750
5591	943	5536	\N	1200
5592	943	5537	\N	1820
5593	943	5538	\N	1920
5594	943	5539	\N	12000
5595	943	5544	\N	3600
5596	943	5549	\N	30000
5597	945	5560	\N	42350
5598	863	2453	\N	315
5599	945	5561	\N	18700
5600	945	5562	\N	6050
5601	945	5563	\N	9350
5602	945	7012	\N	16500
5603	945	7013	\N	20350
5604	947	7245	\N	1800
5605	947	7246	\N	3000
5606	947	7247	\N	1200
5607	947	7249	\N	23400
5608	947	7250	\N	2160
5609	947	7251	\N	1200
5610	947	7252	\N	9600
5611	947	7253	\N	5280
5612	947	7254	\N	2400
5613	947	7255	\N	1920
5614	947	7256	\N	240
5615	947	7257	\N	60
5616	947	7258	\N	1080
5617	947	7259	\N	960
5618	947	7260	\N	1500
5619	947	7261	\N	720
5620	947	7262	\N	1560
5621	947	7263	\N	3600
5622	947	7264	\N	300
5623	947	7267	\N	1920
5624	947	7268	\N	265
5625	947	7269	\N	600
5626	947	7270	\N	1500
5627	947	7271	\N	9600
5628	947	7272	\N	2400
5629	947	7275	\N	240
5630	947	7276	\N	285
5631	947	7277	\N	215
5632	947	7278	\N	385
5633	947	7279	\N	480
5634	947	7280	\N	600
5635	947	7343	\N	18000
5636	947	7344	\N	26400
5637	947	7352	\N	4950
5638	947	7358	\N	3300
5639	947	7360	\N	40150
5640	947	7361	\N	18150
5641	947	7363	\N	6930
5642	947	7364	\N	7150
5643	947	7365	\N	7700
5644	948	6850	\N	400
5645	948	6855	\N	23825
5646	949	7160	\N	7395
5647	149	8279	\N	128000
5648	1024	2985	\N	19000
5649	705	1489	\N	140544
5650	698	1458	\N	1335
5651	596	754	\N	250000
5652	587	617	\N	40000
5653	587	616	\N	135000
5654	705	1486	\N	362880
5655	705	1488	\N	437760
5656	719	1549	\N	\N
5657	719	1539	\N	\N
5658	719	1551	\N	\N
5659	118	5811	\N	850
5660	720	1580	\N	1600
5661	586	587	\N	2964
5662	981	2574	\N	87694
5663	164	6100	\N	36400
5664	719	1554	\N	\N
5665	719	1540	\N	\N
5666	719	1537	\N	\N
5667	719	1535	\N	\N
5668	719	1531	\N	\N
5669	718	1530	\N	5200
5670	719	1548	\N	\N
5671	719	1547	\N	\N
5672	719	1543	\N	\N
5673	719	1541	\N	\N
5674	719	1534	\N	\N
5675	977	2540	\N	40000
5676	704	1480	\N	33060
5677	704	1481	\N	951080.13
5678	704	1482	\N	93089.3
5679	704	1483	\N	14345.1
5680	1404	11489	\N	470
5681	988	2611	\N	140000
5682	988	2614	\N	22000
5683	988	2613	\N	20000
5684	988	2612	\N	35000
5685	979	2570	\N	128620.31
5686	979	2571	\N	111628.11
5687	979	2572	\N	93384.72
5688	979	2573	\N	80503.84
5689	567	354	\N	27000
5690	573	412	\N	43200
5691	573	413	\N	43200
5692	588	622	\N	800
5693	588	623	\N	800
5694	720	1586	\N	13800
5695	720	1590	\N	2730
5696	720	1591	\N	5460
5697	105	8484	\N	145000
5698	723	1633	\N	\N
5699	261	7621	\N	450
5700	91	7617	\N	7450
5701	91	7618	\N	21550
5702	550	245	\N	490
5703	300	7681	\N	96750
5704	104	7602	\N	420911
5705	386	4419	\N	711000
5706	316	7371	\N	73000
5707	308	6404	\N	285000
5708	1014	2889	\N	43313
5709	1014	2856	\N	240890
5710	1006	2772	\N	11625
5711	1006	2770	\N	33500
5712	981	2580	\N	3625
5713	967	7818	\N	11600
5714	967	7817	\N	6000
5715	966	7809	\N	4200
5716	966	7802	\N	3600
5717	723	1631	\N	\N
5718	723	1630	\N	\N
5719	723	1629	\N	\N
5720	723	1627	\N	\N
5721	585	542	\N	\N
5722	585	539	\N	\N
5723	585	541	\N	\N
5724	963	8135	\N	11500
5725	963	8137	\N	550
5726	963	8136	\N	1500
5727	965	7794	\N	660
5728	964	7787	\N	5775
5729	1006	2773	\N	1438
5730	991	2673	\N	7500
5731	1014	2855	\N	709
5732	1014	2853	\N	2652
5733	1014	2852	\N	1165
5734	1014	2851	\N	4769
5735	1014	2850	\N	6050
5736	1014	2849	\N	26122
5737	720	1582	\N	11200
5738	706	1493	\N	117250
5739	727	1650	\N	711622
5740	918	7936	\N	125000
5741	966	7796	\N	52800
5742	964	7785	\N	14420
5743	964	7784	\N	61490
5744	964	7783	\N	84150
5745	961	8103	\N	2300
5746	961	8100	\N	6325
5747	961	8099	\N	17575
5748	960	8083	\N	194537
5749	951	5488	\N	43350
5750	951	5486	\N	31875
5751	953	5519	\N	83930
5752	949	7210	\N	14950
5753	948	6852	\N	7360
5754	944	7041	\N	2860
5755	944	7040	\N	13750
5756	944	7039	\N	12650
5757	944	7038	\N	9240
5758	944	7037	\N	14850
5759	944	7036	\N	63800
5760	944	7035	\N	11660
5761	944	7034	\N	10230
5762	944	7033	\N	25850
5763	944	7032	\N	73150
5764	938	6890	\N	53222
5765	938	6889	\N	47040
5766	938	6888	\N	25536
5767	1010	2823	\N	14375
5768	938	6884	\N	40723
5769	938	6883	\N	34944
5770	938	6882	\N	48787
5771	938	6879	\N	63034
5772	921	7956	\N	55556
5773	921	7955	\N	15385
5774	921	7954	\N	20513
5775	920	7953	\N	38000
5776	920	7952	\N	42000
5777	920	7951	\N	35000
5778	920	7949	\N	1150
5779	919	7943	\N	8500
5780	919	7942	\N	3900
5781	919	7941	\N	35000
5782	918	7938	\N	28000
5783	918	7935	\N	90000
5784	918	7934	\N	69000
5785	918	7933	\N	40000
5786	918	7932	\N	65000
5787	919	7945	\N	245000
5788	938	6887	\N	3226
5789	961	8101	\N	575
5790	961	8102	\N	10350
5791	738	1783	\N	2875
5792	738	1784	\N	6325
5793	736	1767	\N	17250
5794	736	1768	\N	17250
5795	734	1736	\N	13800
5796	734	1738	\N	6900
5797	734	1739	\N	12650
5798	734	1740	\N	2881
5799	739	1786	\N	71583
5800	741	1789	\N	418500
5801	741	1790	\N	375000
5802	158	4217	\N	2025
5803	535	5182	\N	1523770
5804	918	7927	\N	20000
5805	918	7926	\N	45000
5806	918	7925	\N	17500
5807	918	7924	\N	32000
5808	886	5402	\N	10928450
5809	878	5387	\N	2276278.43
5810	873	5366	\N	99684
5811	538	5195	\N	913325
5812	538	5194	\N	4254721
5813	510	5138	\N	13800
5814	510	5140	\N	4600
5815	510	5139	\N	12650
5816	504	5115	\N	75400
5817	506	5132	\N	136000
5818	506	5130	\N	1086500
5819	506	5129	\N	231500
5820	506	5128	\N	185500
5821	506	5127	\N	1064500
5822	283	5577	\N	150000
5823	270	7468	\N	148000
5824	268	7454	\N	20000
5825	259	6382	\N	454784.98
5826	259	6381	\N	419359.94
5827	170	6283	\N	9000
5828	170	6278	\N	3300
5829	170	6274	\N	156750
5830	170	6273	\N	75240
5831	170	6272	\N	58000
5832	170	6271	\N	26000
5833	170	6270	\N	4000
5834	879	5460	\N	87400
5835	535	5184	\N	626080
5836	742	1796	\N	91675
5837	745	1804	\N	476100
5838	126	5871	\N	325
5839	168	6221	\N	3600
5840	167	6196	\N	7800
5841	168	6222	\N	6500
5842	168	6229	\N	4800
5843	168	6225	\N	4000
5844	168	6224	\N	1800
5845	168	6228	\N	1800
5846	168	6220	\N	160500
5847	168	6219	\N	198000
5848	168	6226	\N	1800
5849	167	6194	\N	6060
5850	167	6193	\N	14080
5851	167	6188	\N	2280
5852	163	6093	\N	12500
5853	163	6091	\N	2400
5854	163	6081	\N	12500
5855	163	6080	\N	15000
5856	162	4333	\N	6000
5857	162	4328	\N	17400
5858	158	4215	\N	2875
5859	154	4126	\N	11000
5860	154	4125	\N	28600
5861	154	4124	\N	800
5862	154	4123	\N	1400
5863	154	4122	\N	6600
5864	154	4119	\N	16000
5865	154	4118	\N	17000
5866	154	4117	\N	23000
5867	154	4116	\N	41800
5868	352	6430	\N	155000
5869	157	4201	\N	81700
5870	140	6079	\N	2290
5871	140	6049	\N	283560
5872	140	6060	\N	86400
5873	140	6057	\N	3000
5874	140	6050	\N	68700
5875	139	5954	\N	450
5876	139	5953	\N	900
5877	127	5881	\N	1000
5878	140	6067	\N	15600
5879	140	6066	\N	900
5880	140	6065	\N	6000
5881	140	6064	\N	3600
5882	140	6063	\N	14400
5883	159	4253	\N	600
5884	159	4252	\N	1300
5885	440	6501	\N	8000
5886	137	5929	\N	17360
5887	126	5864	\N	450
5888	127	5878	\N	68700
5889	125	5857	\N	750
5890	117	5802	\N	600
5891	121	5827	\N	45000
5892	125	5856	\N	1440
5893	125	5855	\N	25000
5894	125	5854	\N	16800
5895	125	5853	\N	13500
5896	125	5852	\N	100980
5897	125	5851	\N	11040
5898	125	5850	\N	22440
5899	125	5849	\N	32160
5900	125	5848	\N	42400
5901	125	5847	\N	62000
5902	118	5814	\N	3000
5903	434	4573	\N	1150
5904	440	6560	\N	49135
5905	418	4453	\N	219500
5906	441	8474	\N	60000
5907	441	8473	\N	89000
5908	441	8472	\N	149800
5909	441	8467	\N	75000
5910	506	5126	\N	19778500
5911	441	8466	\N	149700
5912	441	8465	\N	49500
5913	441	8462	\N	97500
5914	355	4406	\N	83000
5915	352	6434	\N	36000
5916	352	6433	\N	18500
5917	116	5786	\N	2500
5918	115	5780	\N	650
5919	109	5714	\N	6000
5920	109	5718	\N	2000
5921	109	5717	\N	2500
5922	109	5715	\N	5600
5923	270	7472	\N	11500
5924	786	1937	\N	\N
5925	785	1933	\N	100
5926	787	2002	\N	3125
5927	800	2062	\N	520550
5928	483	4852	\N	1773070
5929	489	4954	\N	2300
5930	159	4255	\N	115500
5931	159	4254	\N	1900
5932	159	4247	\N	15000
5933	159	4246	\N	2800
5934	106	5692	\N	4350
5935	106	5691	\N	1400
5936	106	5690	\N	23500
5937	106	5689	\N	15580
5938	106	5687	\N	13500
5939	106	5686	\N	6800
5940	106	5685	\N	9500
5941	106	5684	\N	20300
5942	106	5683	\N	48750
5943	159	4245	\N	3000
5944	504	5125	\N	33150
5945	504	5124	\N	1040
5946	504	5123	\N	1625
5947	504	5122	\N	10790
5948	504	5121	\N	24050
5949	504	5120	\N	8970
5950	504	5119	\N	13650
5951	504	5118	\N	70070
5952	504	5117	\N	34580
5953	504	5116	\N	33800
5954	503	5078	\N	205500
5955	489	4962	\N	34500
5956	489	4959	\N	10350
5957	489	4956	\N	40000
5958	489	4950	\N	12500
5959	487	4879	\N	5865
5960	487	4878	\N	980
5961	487	4866	\N	575
5962	487	4865	\N	1725
5963	483	4851	\N	28600
5964	483	4849	\N	207600
5965	483	4848	\N	357600
5966	482	4844	\N	15000
5967	795	2045	\N	\N
5968	795	2046	\N	\N
5969	795	2047	\N	\N
5970	795	2048	\N	\N
5971	795	2049	\N	\N
5972	800	2060	\N	397125
5973	821	2101	\N	5000
5974	820	2095	\N	26000
5975	800	2061	\N	93825
5976	820	2096	\N	5200
5977	820	2098	\N	52000
5978	820	2099	\N	42250
5979	852	2351	\N	198500
5980	822	2135	\N	16875
5981	475	4766	\N	3514734
5982	475	4767	\N	810815
5983	471	4735	\N	346500
5984	471	4733	\N	104500
5985	463	6788	\N	160045
5986	458	4669	\N	59900
5987	963	8134	\N	3000
5988	822	2134	\N	5625
5989	836	2217	\N	2500
5990	457	4654	\N	57500
5991	440	6782	\N	3000
5992	440	6758	\N	1000
5993	440	6757	\N	650
5994	441	8457	\N	148500
5995	441	8456	\N	149700
5996	441	8455	\N	146500
5997	441	8452	\N	149700
5998	441	8451	\N	133000
5999	441	8450	\N	148500
6000	807	2080	\N	2282820
6001	441	8449	\N	149500
6002	441	8448	\N	75500
6003	441	8446	\N	149700
6004	441	8444	\N	147850
6005	441	8441	\N	149580
6006	440	6719	\N	4950
6007	440	6746	\N	2500
6008	440	6743	\N	650
6009	440	6722	\N	650
6010	440	6697	\N	147420
6011	440	6750	\N	8500
6012	440	6704	\N	98500
6013	440	6683	\N	6150
6014	440	6684	\N	4850
6015	440	6668	\N	1080
6016	440	6570	\N	5000
6017	440	6583	\N	4860
6018	440	6580	\N	4000
6019	440	6581	\N	117515
6020	440	6582	\N	87750
6021	440	6509	\N	140000
6022	440	6507	\N	49970
6023	431	4537	\N	188500
6024	434	4569	\N	11500
6025	434	4560	\N	103500
6026	1004	2738	\N	445
6027	426	4475	\N	48750
6028	440	6609	\N	600
6029	440	6608	\N	450
6030	440	6644	\N	450
6031	840	2259	\N	18750
6032	849	2338	\N	97500
6033	845	2290	\N	61400
6034	851	2343	\N	18000
6035	851	2340	\N	98000
6036	434	4557	\N	307935
6037	434	4556	\N	330820
6038	434	4555	\N	75000
6039	434	4554	\N	32200
6040	434	4553	\N	11500
6041	434	4552	\N	11500
6042	434	4551	\N	20700
6043	434	4550	\N	46000
6044	431	4538	\N	240000
6045	431	4535	\N	936000
6046	430	4533	\N	8560
6047	430	4532	\N	149500
6048	430	4530	\N	8050
6049	430	4525	\N	2875
6050	430	4517	\N	1150
6051	430	4529	\N	6670
6052	430	4527	\N	8855
6053	427	4506	\N	46000
6054	426	4486	\N	116600
6055	426	4485	\N	30500
6056	426	4478	\N	20100
6057	426	4477	\N	24500
6058	426	4476	\N	30500
6059	426	4474	\N	18280
6060	843	2285	\N	215000
6061	843	2284	\N	60
6062	843	2283	\N	60
6063	851	2341	\N	55000
6064	736	1766	\N	17250
6065	854	2391	\N	16875
6066	844	2296	\N	225000
6067	854	2396	\N	6250
6068	666	1161	\N	40000
6069	607	878	\N	8820
6070	850	2337	\N	4300
6071	859	2405	\N	265000
6072	850	2330	\N	5600
6073	857	2403	\N	5000
6074	859	2406	\N	25000
6075	859	2407	\N	515000
6076	883	6835	\N	1431
6077	854	2395	\N	32000
6078	863	2476	\N	3125
6079	863	2477	\N	6500
6080	871	2511	\N	156750
6081	674	1302	\N	100100
6082	674	1303	\N	125840
6083	863	2440	\N	4750
6084	706	1490	\N	39039
6085	844	2301	\N	28000
6086	844	2300	\N	25000
6087	844	2298	\N	120000
6088	858	2404	\N	10424
6089	863	2452	\N	1250
6090	976	2534	\N	12500
6091	981	2592	\N	7980
6092	978	2568	\N	3250
6093	991	2668	\N	575
6094	984	2599	\N	249
6095	984	2598	\N	125
6096	987	2608	\N	125000
6097	987	2607	\N	3700
6098	985	2603	\N	3550
6099	985	2602	\N	1435
6100	984	2600	\N	430
6101	837	2246	\N	3125
6102	706	1491	\N	79643
6103	993	2692	\N	65625
6104	1004	2736	\N	815
6105	1006	2794	\N	6500
6106	1006	2795	\N	2600
6107	1006	2798	\N	315
6108	1006	2799	\N	2625
6109	1006	2800	\N	5250
6110	712	1518	\N	14500
6111	1004	2742	\N	1500
6112	1004	2741	\N	419
6113	1004	2734	\N	92583
6114	1004	2733	\N	8250
6115	1004	2732	\N	164167
6116	1004	2745	\N	1613
6117	1004	2746	\N	1080
6118	1004	2747	\N	3125
6119	1004	2748	\N	231
6120	1004	2749	\N	2083
6121	1004	2735	\N	1719
6122	1004	2737	\N	1875
6123	1004	2739	\N	1833
6124	1004	2743	\N	456
6125	1004	2744	\N	1576
6126	1004	2753	\N	505
6127	1004	2740	\N	2950
6128	1004	2750	\N	33000
6129	1004	2751	\N	11860
6130	1010	2825	\N	6300
6131	1006	2791	\N	3850
6132	1006	2792	\N	26250
6133	1006	2793	\N	1950
6134	1006	2801	\N	4700
6135	155	4145	\N	1150
6136	156	4191	\N	60500
6137	156	4194	\N	51250
6138	158	4228	\N	2875
6139	1014	2879	\N	708
6140	1014	2880	\N	8500
6141	1014	2890	\N	25418
6142	1014	2891	\N	2415
6143	159	4248	\N	8800
6144	159	4258	\N	48000
6145	160	4264	\N	18000
6146	160	4270	\N	21600
6147	160	4271	\N	60000
6148	1021	2941	\N	75200
6149	1020	2938	\N	5460
6150	160	4309	\N	1200
6151	160	4313	\N	36000
6152	161	4323	\N	7200
6153	161	4324	\N	42000
6154	161	4325	\N	48000
6155	161	4326	\N	18000
6156	162	4329	\N	18000
6157	863	2474	\N	6875
6158	162	4330	\N	14800
6159	162	4331	\N	14400
6160	162	4332	\N	2400
6161	162	4334	\N	8400
6162	203	4348	\N	1300000
6163	260	4349	\N	13500
6164	260	4350	\N	20000
6165	260	4351	\N	315000
6166	260	4352	\N	500000
6167	260	4353	\N	215
6168	260	4354	\N	115
6169	260	4355	\N	200000
6170	260	4356	\N	215
6171	260	4357	\N	69000
6172	294	4373	\N	6000
6173	986	2836	\N	1098000
6174	1060	7173	\N	200
6175	1060	7171	\N	100
6176	294	4377	\N	30050
6177	294	4383	\N	12020
6178	335	4390	\N	85000
6179	335	4391	\N	950
6180	335	4392	\N	39000
6181	335	4393	\N	3500
6182	335	4394	\N	750
6183	335	4395	\N	9000
6184	335	4396	\N	3500
6185	335	4397	\N	39000
6186	335	4398	\N	125000
6187	422	4463	\N	60000
6188	422	4464	\N	4600
6189	422	4467	\N	17000
6190	422	4468	\N	2875
6191	425	4472	\N	35400
6192	428	4512	\N	115374
6193	430	4528	\N	2875
6194	430	4531	\N	1150
6195	432	4539	\N	2500
6196	433	4549	\N	219170
6197	434	4558	\N	2500
6198	434	4565	\N	5095
6199	434	4567	\N	1725
6200	456	4625	\N	411450
6201	457	4636	\N	115000
6202	457	4637	\N	172500
6203	457	4653	\N	57500
6204	461	4719	\N	189000
6205	465	4721	\N	760045
6206	465	4722	\N	69784
6207	470	4726	\N	483912
6208	472	4747	\N	122000
6209	470	4725	\N	205400
6210	477	4789	\N	115000
6211	487	4897	\N	1725
6212	521	8221	\N	800
6213	489	4963	\N	3450
6214	490	4965	\N	42500
6215	491	4969	\N	2875
6216	499	5040	\N	92400
6217	500	5042	\N	300000
6218	503	5085	\N	10100
6219	511	5146	\N	229000
6220	536	5187	\N	17965
6221	538	5196	\N	418710
6222	883	6834	\N	4876
6223	883	6833	\N	1445
6224	883	6836	\N	3048
6225	328	5320	\N	79100
6226	381	5321	\N	26100
6227	883	6839	\N	9752
6228	948	6853	\N	2300
6229	872	5368	\N	130000
6230	872	5367	\N	120000
6231	872	5369	\N	12000
6232	954	6859	\N	2875
6233	954	6860	\N	2875
6234	954	6870	\N	4600
6235	1048	5352	\N	39000
6236	876	5395	\N	389500
6237	876	5390	\N	34000
6238	953	5524	\N	6250
6239	954	6869	\N	172.5
6240	887	5429	\N	612000
6241	954	6866	\N	3680
6242	953	5525	\N	3750
6243	953	5527	\N	3125
6244	953	5523	\N	12500
6245	953	5521	\N	6750
6246	253	5529	\N	13126
6247	479	5533	\N	11165
6248	953	5522	\N	3285
6249	938	6881	\N	13440
6250	938	6885	\N	4838
6251	952	6901	\N	22798
6252	254	5571	\N	58025
6253	507	5573	\N	24200
6254	252	5574	\N	39600
6255	190	5578	\N	215000
6256	1056	5583	\N	3914
6257	1056	5582	\N	1896
6258	108	5701	\N	1200
6259	112	5749	\N	650
6260	118	5809	\N	350
6261	119	5816	\N	9700
6262	126	5875	\N	20
6263	127	5882	\N	5500
6264	128	5892	\N	1500
6265	129	5894	\N	12600
6266	135	5925	\N	56500
6267	135	5926	\N	18000
6268	136	5928	\N	6000
6269	139	5939	\N	840
6270	139	5944	\N	650
6271	139	5957	\N	12
6272	139	5958	\N	25
6273	139	5960	\N	900
6274	139	5962	\N	150
6275	139	5963	\N	850
6276	139	5964	\N	1660
6277	140	6046	\N	17500
6278	140	6054	\N	5500
6279	163	6095	\N	1250
6280	163	6096	\N	1200
6281	164	6104	\N	4200
6282	167	6176	\N	7200
6283	167	6177	\N	4500
6284	167	6178	\N	6300
6285	167	6179	\N	9000
6286	167	6180	\N	4104
6287	167	6185	\N	1320
6288	167	6186	\N	8070
6289	169	6251	\N	8600
6290	169	6252	\N	11400
6291	169	6240	\N	120
6292	169	6260	\N	5400
6293	170	6279	\N	3300
6294	170	6280	\N	1250
6295	170	6281	\N	2700
6296	170	6284	\N	6000
6297	180	6325	\N	1500
6298	180	6326	\N	14500
6299	181	6327	\N	14500
6300	185	6352	\N	60
6301	185	6356	\N	30
6302	187	6374	\N	300
6303	187	6376	\N	550
6304	259	6380	\N	947181.06
6305	318	6410	\N	180000
6306	340	6418	\N	125000
6307	342	6420	\N	155500
6308	343	6421	\N	190000
6309	344	6422	\N	45000
6310	345	6423	\N	215000
6311	346	6424	\N	149000
6312	349	6427	\N	293000
6313	350	6428	\N	240000
6314	352	6431	\N	82620
6315	358	6438	\N	120000
6316	402	6457	\N	168756.89
6317	402	6458	\N	168756.89
6318	402	6460	\N	209148.58
6319	402	6463	\N	58896.72
6320	402	6464	\N	147834.72
6321	408	6480	\N	184100.75
6322	408	6481	\N	184100.75
6323	408	6482	\N	184100.75
6324	408	6483	\N	228807.51
6325	440	6670	\N	450
6326	440	6674	\N	1400
6327	463	6789	\N	30000
6328	440	6783	\N	800
6329	470	4724	\N	706368
6330	141	7095	0	56160
6331	141	7094	0	3510
6332	922	7150	\N	155172.41
6333	923	7153	\N	172413.79
6334	924	7157	\N	172413.79
6335	1084	8687	\N	2954
6336	175	7165	\N	9225
6337	175	7170	\N	1335
6338	175	7167	\N	1025
6339	361	7175	\N	1800
6340	949	7183	\N	5980
6341	363	7181	\N	2800
6342	364	7182	\N	2800
6343	365	7188	\N	2800
6344	366	7189	\N	2800
6345	367	7190	\N	2800
6346	368	7192	\N	2800
6347	369	7194	\N	1800
6348	370	7195	\N	1800
6349	371	7198	\N	2800
6350	372	7199	\N	2800
6351	373	7205	\N	2800
6352	374	7207	\N	1800
6353	375	7209	\N	1800
6354	142	7211	\N	15600
6355	949	7179	\N	23000
6356	212	7212	0	2000
6357	212	7227	0	3790
6358	212	7223	0	3630
6359	212	7220	0	4850
6360	212	7216	0	2750
6361	212	7215	0	1900
6362	330	7391	\N	22200
6363	484	7495	\N	22600
6364	484	7489	\N	24600
6365	327	7505	\N	38703.6
6366	87	7571	\N	105000
6367	87	7572	\N	177000
6368	87	7573	\N	20000
6369	87	7574	\N	10000
6370	329	7588	\N	5875
6371	200	7593	\N	105560
6372	197	7595	\N	184726
6373	897	7901	\N	114000
6374	414	7673	\N	2000
6375	414	7674	\N	3000
6376	414	7675	\N	5000
6377	412	7678	\N	5000
6378	412	7679	\N	8000
6379	949	7180	\N	17250
6380	950	6872	\N	2875
6381	425	4471	\N	489600
6382	1102	8869	\N	200
6383	875	7773	\N	45000
6384	875	7774	\N	6500
6385	875	7775	\N	8000
6386	875	7776	\N	32000
6387	875	7777	\N	18000
6388	875	7778	\N	3000
6389	875	7779	\N	40000
6390	966	7799	\N	3480
6391	1084	8686	\N	600
6392	966	7801	\N	8640
6393	966	7803	\N	1200
6394	966	7808	\N	4800
6395	971	7834	\N	583000
6396	971	7835	\N	198000
6397	875	7837	\N	22500
6398	875	7838	\N	62500
6399	875	7839	\N	22000
6400	875	7840	\N	15000
6401	875	7841	\N	45000
6402	875	7843	\N	7000
6403	875	7844	\N	32000
6404	882	7845	\N	360600
6405	906	7916	\N	7475
6406	882	7846	\N	637920
6407	147	7708	\N	280
6408	147	7709	\N	6000
6409	147	7710	\N	750
6410	312	7726	\N	11000
6411	233	7750	\N	1250
6412	233	7749	\N	7850
6413	233	7748	\N	7250
6414	884	7869	\N	45000
6415	884	7870	\N	6500
6416	884	7871	\N	11000
6417	884	7872	\N	32000
6418	884	7873	\N	18000
6419	884	7874	\N	40000
6420	884	7875	\N	37500
6421	884	7876	\N	62500
6422	884	7877	\N	37000
6423	884	7878	\N	15000
6424	884	7879	\N	45000
6425	884	7881	\N	7000
6426	884	7882	\N	32000
6427	890	7888	\N	925000
6428	896	7898	\N	125400
6429	906	7917	\N	20700
6430	906	7919	\N	2875
6431	906	7920	\N	4000
6432	906	7922	\N	2080
6433	918	7928	\N	15000
6434	918	7930	\N	13000
6435	918	7939	\N	40000
6436	918	7940	\N	2500
6437	920	7948	\N	55000
6438	920	7950	\N	22000
6439	955	7965	\N	780
6440	955	7966	\N	3250
6441	956	8013	\N	1800
6442	956	8014	\N	1560
6443	957	8047	\N	3450
6444	957	8048	\N	4788
6445	958	8059	\N	1690
6446	961	8097	\N	9200
6447	961	8098	\N	2875
6448	971	8138	\N	1270500
6449	971	8139	\N	132000
6450	971	8140	\N	198000
6451	972	8145	\N	23400
6452	972	8146	\N	45500
6453	972	8149	\N	9750
6454	972	8151	\N	32500
6455	789	2018	\N	\N
6456	786	1934	\N	\N
6457	441	8440	\N	148500
6458	1063	8412	\N	26125
6459	1063	8413	\N	20425
6460	1063	8415	\N	2460
6461	1064	8425	\N	72000
6462	441	8442	\N	145760
6463	441	8443	\N	112310
6464	441	8454	\N	75000
6465	441	8459	\N	115000
6466	520	8204	\N	1000
6467	519	8208	\N	100
6468	518	8234	\N	1000
6469	242	8297	\N	50000
6470	441	8460	\N	95500
6471	441	8463	\N	110000
6472	441	8464	\N	125000
6473	441	8468	\N	149700
6474	148	8469	\N	12125
6475	441	8471	\N	149700
6476	441	8475	\N	142000
6477	1102	8870	\N	75
6478	1063	8321	\N	60000
6479	1065	8447	\N	0
6480	449	8563	\N	1651065
6481	449	8565	\N	412766
6482	449	8566	\N	412766
6483	449	8567	\N	825532
6484	201	8568	\N	175000
6485	1079	8654	\N	450000
6486	189	8569	\N	340000
6487	1075	8618	\N	320000
6488	917	7923	\N	359000
6489	1083	8658	\N	545000
6490	1041	5318	\N	188100
6491	1077	8623	\N	35000
6492	1077	8621	\N	35000
6493	1077	8647	\N	95000
6494	1077	8625	\N	35000
6495	1077	8624	\N	35000
6496	1077	8649	\N	63000
6497	1077	8648	\N	65000
6498	1084	8695	\N	12000
6499	1084	8694	\N	2900
6500	1084	8693	\N	1707
6501	1084	8692	\N	230
6502	1084	8691	\N	230
6503	1084	8690	\N	195
6504	1084	8689	\N	5050
6505	1084	8688	\N	17022
6506	1084	8684	\N	330
6507	1084	8683	\N	4616
6508	1084	8682	\N	4220
6509	1084	8679	\N	4220
6510	1084	8676	\N	1424
6511	1084	8675	\N	1107
6512	1084	8674	\N	3197
6513	1084	8671	\N	2806
6514	1084	8670	\N	2806
6515	1084	8669	\N	26
6516	1084	8667	\N	4520
6517	1084	8666	\N	10550
6518	1084	8664	\N	2806
6519	1084	8663	\N	2806
6520	1084	8662	\N	2806
6521	1168	9484	\N	13092
6522	1084	8677	\N	1160
6523	1104	8913	\N	1460
6524	1098	8798	\N	11600
6525	1098	8799	\N	12340
6526	1098	8800	\N	12540
6527	1098	8801	\N	11700
6528	1098	8802	\N	4937
6529	1079	8653	0	450000
6530	1084	8697	\N	2005
6531	1084	8713	\N	617
6532	1084	8712	\N	545
6533	1084	8708	\N	8176
6534	1084	8705	\N	11
6535	1084	8707	\N	8809
6536	1084	8703	\N	1055
6537	1084	8706	\N	15
6538	1084	8702	\N	16880
6539	1084	8701	\N	16880
6540	1084	8700	\N	36398
6541	1084	8699	\N	9495
6542	1084	8698	\N	4748
6543	1087	8736	\N	7500
6544	1087	8737	\N	9800
6545	1087	8738	\N	950
6546	1091	8771	\N	9800
6547	1093	8774	\N	741880
6548	1097	8797	\N	237
6549	1097	8792	\N	8226
6550	1097	8793	\N	20330
6551	1101	8837	\N	40
6552	1101	8835	\N	54375
6553	1101	8832	\N	1875
6554	1101	8819	\N	880
6555	1101	8818	\N	150
6556	1101	8831	\N	125
6557	1101	8817	\N	200
6558	1101	8816	\N	250
6559	1101	8820	\N	500
6560	1101	8808	\N	3500
6561	1101	8809	\N	330
6562	1101	8810	\N	330
6563	1101	8811	\N	3500
6564	1101	8812	\N	300
6565	1101	8813	\N	50
6566	1101	8814	\N	375
6567	1101	8815	\N	700
6568	1101	8830	\N	125
6569	1101	8829	\N	125
6570	1101	8828	\N	1250
6571	1101	8827	\N	190
6572	1101	8826	\N	350
6573	1098	8803	\N	13375
6574	1008	2808	\N	85800
6575	1008	2811	\N	10725
6576	1008	2813	\N	35750
6577	1008	2826	\N	33000
6578	1008	2827	\N	128700
6579	1079	8650	\N	450000
6580	1101	8844	\N	10
6581	1079	8651	0	450000
6582	1091	8772	\N	950
6583	1102	8876	\N	765
6584	1102	8871	\N	85
6585	1102	8872	\N	1200
6586	1102	8873	\N	670
6587	1102	8874	\N	750
6588	1102	8875	\N	760
6589	1102	8877	\N	1000
6590	1102	8857	\N	2500
6591	1102	8858	\N	2000
6592	1102	8859	\N	4500
6593	1102	8860	\N	8000
6594	1102	8861	\N	10160
6595	1102	8862	\N	250
6596	1102	8863	\N	250
6597	1102	8864	\N	150
6598	1102	8865	\N	400
6599	1102	8866	\N	200
6600	1102	8867	\N	75
6601	1102	8868	\N	375
6602	1109	8956	\N	565
6603	1109	8951	\N	500
6604	1109	8952	\N	700
6605	1109	8953	\N	250
6606	1109	8954	\N	300
6607	1109	8955	\N	500
6608	1109	8957	\N	565
6609	1109	8958	\N	500
6610	1109	8959	\N	1500
6611	1109	8960	\N	1800
6612	1109	8961	\N	8500
6613	1109	8962	\N	50565
6614	1067	8518	\N	1218
6615	1109	8964	\N	12000
6616	1104	8914	\N	3200
6617	1104	8915	\N	850
6618	1105	8916	\N	100000
6619	1067	8542	\N	6300
6620	1067	8543	\N	5250
6621	1026	2995	\N	400
6622	1047	5341	\N	12000
6623	1047	5342	\N	408
6624	1047	5350	\N	2563
6625	1109	8966	\N	1850
6626	1109	8967	\N	500
6627	1109	8968	\N	1500
6628	1109	8965	\N	500
6629	994	2693	\N	2195050
6630	994	2694	\N	4494490
6631	1153	9374	\N	7200
6632	994	2695	\N	766480
6633	1075	8619	\N	33000
6634	1075	8620	\N	45600
6635	1109	8977	\N	2625
6636	1109	8978	\N	5460
6637	1109	8979	\N	1700
6638	1101	8833	\N	10000
6639	1101	8834	\N	9375
6640	1101	8836	\N	96285
6641	1101	8838	\N	40
6642	1101	8839	\N	5
6643	1101	8840	\N	5
6644	1101	8841	\N	5
6645	1101	8842	\N	5
6646	1101	8843	\N	2000
6647	1101	8852	\N	1250
6648	1101	8845	\N	10
6649	1101	8846	\N	45
6650	1101	8847	\N	40
6651	1101	8851	\N	25
6652	1101	8850	\N	65
6653	1101	8849	\N	40
6654	1101	8848	\N	40
6655	1101	8853	\N	65
6656	1109	8975	\N	10150
6657	1109	8976	\N	9000
6658	1109	8980	\N	19000
6659	1113	8982	\N	1023.333333
6660	1113	8983	\N	245000
6661	1114	8985	\N	25000
6662	1114	8987	\N	200000
6663	1114	8986	\N	25000
6664	1114	8984	\N	25000
6665	1115	8988	\N	150000
6666	1115	8989	\N	248500
6667	1063	8411	\N	7750
6668	392	4450	\N	10820
6669	1063	8180	\N	5164
6670	397	6444	\N	1059441.12
6671	114	5758	\N	225000
6672	1063	8389	\N	875
6673	1117	8993	\N	67950
6674	1117	8994	\N	54800
6675	1117	8995	\N	6750
6676	1116	8990	\N	74600
6677	1116	8991	\N	27700
6678	1153	9361	\N	7000
6679	1143	9232	\N	17261315
6680	1154	9383	\N	325000
6681	1154	9384	\N	29500
6682	1154	9385	\N	8000
6683	1154	9386	\N	34500
6684	1153	9362	\N	17000
6685	1153	9363	\N	3800
6686	1153	9364	\N	403
6687	1153	9365	\N	750
6688	1153	9366	\N	4500
6689	1153	9367	\N	230
6690	1153	9368	\N	120
6691	1153	9369	\N	308
6692	1153	9370	\N	7000
6693	1153	9373	\N	1300
6694	1153	9376	\N	37000
6695	1153	9377	\N	5000
6696	1153	9378	\N	500
6697	1153	9379	\N	2313
6698	1153	9380	\N	4000
6699	1156	9395	\N	625
6700	1156	9396	\N	1625
6701	1156	9397	\N	938
6702	1159	9427	\N	220467
6703	1159	9428	\N	41968
6704	1153	9381	\N	3000
6705	1153	9382	\N	19000
6706	1153	9387	\N	3500
6707	1155	9392	\N	100
6708	1155	9394	\N	200
6709	1157	9418	\N	1442307.69
6710	1157	9419	\N	201923.08
6711	1157	9420	\N	95726.5
6712	1157	9421	\N	168589.74
6713	1157	9422	\N	25427.35
6714	1136	9165	\N	15000
6715	1136	9166	\N	1500
6716	1136	9167	\N	5000
6717	1136	9168	\N	5000
6718	1156	9398	\N	938
6719	1156	9399	\N	94
6720	1156	9400	\N	1575
6721	1158	9424	\N	148000
6722	1158	9425	\N	90000
6723	1158	9426	\N	975
6724	1153	9389	\N	1400
6725	1153	9390	\N	1100
6726	1153	9391	\N	9375
6727	1001	2730	\N	253450
6728	1001	2719	\N	49190
6729	1166	9449	\N	325000
6730	1166	9450	\N	29500
6731	1166	9451	\N	8000
6732	1166	9452	\N	34500
6733	1165	9448	\N	63000
6734	1167	9454	\N	1650
6735	1161	9430	\N	240500
6736	1068	8546	\N	3371940
6737	1068	8547	\N	457600
6738	1001	2720	\N	1810
6739	1001	2721	\N	45365
6740	1001	2722	\N	55865
6741	1001	2723	\N	2225
6742	1001	2724	\N	2225
6743	1001	2725	\N	570
6744	1001	2726	\N	465
6745	1001	2727	\N	2740
6746	1001	2728	\N	1500
6747	1001	2729	\N	1520
6748	1156	9401	\N	1250
6749	1156	9402	\N	625
6750	1156	9403	\N	20000
6751	1156	9404	\N	4375
6752	1156	9405	\N	5471
6753	1156	9406	\N	1875
6754	1156	9407	\N	25000
6755	1156	9408	\N	4375
6756	1156	9409	\N	625
6757	1156	9410	\N	3750
6758	1156	9411	\N	4375
6759	1156	9412	\N	4375
6760	1156	9413	\N	21250
6761	1001	2731	\N	49860
6762	1156	9414	\N	375
6763	1156	9416	\N	1750
6764	1156	9417	\N	5000
6765	1163	9432	\N	2020
6766	1163	9433	\N	2020
6767	1163	9434	\N	26000
6768	1163	9435	\N	28500
6769	1141	9195	\N	45500
6770	1141	9196	\N	7895
6771	1141	9197	\N	8535
6772	1141	9198	\N	26586
6773	1141	9199	\N	390000
6774	1141	9200	\N	97500
6775	1141	9201	\N	13000
6776	1141	9202	\N	19500
6777	1163	9436	\N	22500
6778	1163	9437	\N	3059
6779	1163	9438	\N	3059
6780	1163	9439	\N	3059
6781	1163	9440	\N	7438
6782	1163	9441	\N	2675
6783	1163	9442	\N	4900
6784	1167	9458	\N	22552
6785	1167	9459	\N	4752
6786	1167	9460	\N	885
6787	1167	9461	\N	44
6788	1167	9462	\N	5500
6789	1167	9463	\N	2515
6790	1167	9464	\N	4750
6791	1167	9465	\N	1250
6792	1167	9466	\N	1350
6793	1163	9445	\N	11250
6794	1167	9467	\N	20850
6795	1167	9468	\N	2500
6796	1167	9469	\N	2989
6797	1167	9470	\N	725
6798	1167	9471	\N	625
6799	1167	9472	\N	550
6800	1167	9473	\N	600
6801	1167	9474	\N	16000
6802	1167	9475	\N	2625
6803	1167	9477	\N	5775
6804	1167	9478	\N	4000
6805	1167	9453	\N	3063
6806	1167	9455	\N	1250
6807	1167	9456	\N	10000
6808	1167	9457	\N	8500
6809	1168	9479	\N	120000
6810	1168	9480	\N	1095
6811	1168	9481	\N	1000
6812	1168	9482	\N	195
6813	1168	9483	\N	1500
6814	1168	9499	\N	733
6815	1168	9485	\N	2691
6816	1168	9486	\N	823
6817	1168	9487	\N	1188
6818	1168	9488	\N	3322
6819	1168	9489	\N	12500
6820	1168	9490	\N	500
6821	1168	9491	\N	101563
6822	1168	9492	\N	210
6823	1168	9493	\N	20
6824	1168	9495	\N	287
6825	1168	9496	\N	10000
6826	1168	9497	\N	217
6827	1426	11697	\N	5463
6828	1168	9498	\N	12500
6829	1173	9582	\N	18266.666
6830	1171	9559	\N	600
6831	1171	9560	\N	9950
6832	1171	9561	\N	8000
6833	1171	9562	\N	650
6834	1176	9597	\N	122
6835	1171	9544	\N	750
6836	1171	9545	\N	6500
6837	1171	9546	\N	3500
6838	1171	9547	\N	4250
6839	1171	9549	\N	3530
6840	1177	9598	\N	95800
6841	1176	9588	\N	475
6842	1176	9590	\N	9000
6843	1176	9591	\N	11740
6844	1176	9592	\N	2219
6845	1176	9594	\N	2800
6846	1176	9595	\N	17850
6847	1177	9599	\N	75500
6848	1177	9600	\N	35600
6849	1172	9563	\N	313930
6850	1172	9566	\N	1069455
6851	1171	9550	\N	3915
6852	1171	9551	\N	3600
6853	1171	9552	\N	8000
6854	1171	9553	\N	16600
6855	1171	9554	\N	11500
6856	1171	9555	\N	4500
6857	1171	9556	\N	8000
6858	1171	9557	\N	745
6859	1171	9558	\N	625
6860	1172	9569	\N	182490
6861	1171	9564	\N	10350
6862	1173	9578	\N	28750
6863	1176	9596	\N	4115
6864	1185	9648	\N	5000
6865	1185	9649	\N	5000
6866	1181	9606	\N	1360800
6867	1178	9602	\N	3250000
6868	1184	9622	\N	4000
6869	1171	9565	\N	6600
6870	1171	9567	\N	9350
6871	1171	9568	\N	8400
6872	1171	9570	\N	8400
6873	1171	9571	\N	1693
6874	1171	9572	\N	5473
6875	1171	9573	\N	6163
6876	1171	9574	\N	2716
6877	1171	9575	\N	2630
6878	1171	9576	\N	3600
6879	1171	9577	\N	5500
6880	1184	9623	\N	1500
6881	1184	9624	\N	7000
6882	1184	9626	\N	3500
6883	1184	9627	\N	1100
6884	1184	9628	\N	645
6885	1184	9630	\N	7500
6886	1184	9631	\N	27147
6887	1184	9632	\N	400
6888	1431	11761	\N	173
6889	1184	9633	\N	4500
6890	1184	9617	\N	7500
6891	1184	9619	\N	3950
6892	1184	9620	\N	4000
6893	1184	9621	\N	6000
6894	1184	9635	\N	6000
6895	1184	9637	\N	6000
6896	1184	9638	\N	6000
6897	1184	9639	\N	3300
6898	1184	9640	\N	3500
6899	1184	9641	\N	15000
6900	1184	9642	\N	5000
6901	1185	9650	\N	7500
6902	1185	9651	\N	42500
6903	1185	9652	\N	8000
6904	1185	9653	\N	4890
6905	1194	9731	\N	2000
6906	1185	9654	\N	3459
6907	1185	9702	\N	500
6908	1185	9655	\N	2500
6909	1184	9643	\N	34000
6910	1184	9645	\N	34400
6911	1190	9723	\N	105000
6912	1096	9713	\N	\N
6913	1190	9716	\N	60714.286
6914	1185	9656	\N	3125
6915	1185	9657	\N	1250
6916	1190	9717	\N	36111.111
6917	1190	9718	\N	95000
6918	1190	9719	\N	14444.444
6919	1190	9720	\N	40000
6920	1190	9721	\N	35000
6921	1190	9722	\N	209500
6922	1185	9646	\N	18500
6923	1185	9658	\N	37500
6924	1185	9659	\N	2500
6925	1185	9660	\N	43750
6926	1185	9661	\N	5000
6927	1185	9663	\N	11250
6928	1185	9696	\N	2500
6929	1185	9697	\N	3750
6930	1185	9698	\N	625
6931	1185	9699	\N	1875
6932	1203	9816	\N	4970
6933	1203	9818	\N	210
6934	1203	9822	\N	210
6935	1203	9823	\N	210
6936	1203	9829	\N	210
6937	1203	9830	\N	80419
6938	1203	9831	\N	62000
6939	1203	9832	\N	3000
6940	1203	9833	\N	6000
6941	1203	9834	\N	12000
6942	1203	9836	\N	277
6943	1203	9837	\N	2500
6944	1203	9838	\N	9375
6945	1203	9840	\N	2500
6946	1203	9841	\N	5486
6947	1203	9842	\N	1875
6948	1206	9852	\N	9375
6949	1206	9853	\N	70000
6950	1194	9730	\N	990
6951	1194	9732	\N	750
6952	1194	9733	\N	13650
6953	1194	9734	\N	3266
6954	1194	9736	\N	7
6955	1206	9854	\N	7375
6956	1206	9855	\N	5625
6957	1206	9856	\N	5625
6958	1206	9857	\N	34325
6959	1206	9858	\N	187500
6960	1206	9859	\N	813
6961	1206	9845	\N	65000
6962	1194	9737	\N	7
6963	1194	9738	\N	7
6964	1194	9739	\N	7
6965	1194	9740	\N	7
6966	1194	9741	\N	125
6967	1194	9742	\N	32
6968	1194	9743	\N	2975
6969	1206	9849	\N	4125
6970	1206	9850	\N	438
6971	1206	9851	\N	438
6972	1194	9744	\N	3000
6973	1197	9783	\N	16500
6974	1203	9825	\N	400
6975	1197	9784	\N	15700
6976	1197	9785	\N	16300
6977	1197	9786	\N	17900
6978	1197	9787	\N	17500
6979	1197	9788	\N	19500
6980	1197	9789	\N	3600
6981	1194	9754	\N	75000
6982	1185	9700	\N	1250
6983	1185	9701	\N	250
6984	1185	9703	\N	37500
6985	1185	9704	\N	7570
6986	1196	9782	\N	145500
6987	1185	9705	\N	18285
6988	1204	9843	\N	256500
6989	1203	9824	\N	375
6990	1203	9826	\N	300
6991	1203	9828	\N	300
6992	1200	9803	\N	35600
6993	1200	9804	\N	57800
6994	1200	9805	\N	13500
6995	1185	9707	\N	18750
6996	1185	9708	\N	328
6997	1185	9709	\N	6820
6998	1185	9710	\N	2125
6999	1185	9711	\N	688
7000	1185	9712	\N	7980
7001	1202	9807	\N	28100
7002	1196	9781	\N	232250
7003	1199	9802	\N	115000
7004	1206	9848	\N	17742
7005	1205	9844	\N	350000
7006	1202	9806	\N	185600
7007	1199	9798	\N	21000
7008	1199	9799	\N	9500
7009	1202	9811	\N	28100
7010	1202	9812	\N	108300
7011	1202	9813	\N	3200
7012	1202	9814	\N	45
7013	1202	9815	\N	150
7014	1199	9800	\N	105000
7015	1199	9801	\N	20500
7016	1198	9790	\N	6840
7017	1198	9792	\N	6900
7018	1198	9791	\N	7200
7019	1198	9793	\N	6200
7020	1198	9794	\N	8500
7021	1198	9795	\N	8500
7022	1198	9796	\N	5700
7023	1198	9797	\N	6900
7024	1218	9903	\N	3695
7025	1218	9904	\N	8125
7026	1206	9860	\N	9300
7027	1194	9745	\N	38
7028	1194	9746	\N	38
7029	1194	9748	\N	50
7030	1194	9749	\N	50
7031	1194	9750	\N	2500
7032	1194	9751	\N	125
7033	1194	9752	\N	4375
7034	1194	9753	\N	4550
7035	1194	9755	\N	13650
7036	1194	9757	\N	12350
7037	1194	9758	\N	11050
7038	1194	9759	\N	9750
7039	1194	9760	\N	4375
7040	1194	9761	\N	8125
7041	1209	9877	\N	2229960
7042	1195	9774	\N	55400
7043	1194	9762	\N	12500
7044	1194	9763	\N	29875
7045	1194	9765	\N	5813
7046	1194	9767	\N	27488
7047	1194	9768	\N	15625
7048	1194	9769	\N	11750
7049	1194	9770	\N	227375
7050	1194	9771	\N	27375
7051	1194	9772	\N	90473
7052	1194	9773	\N	24750
7053	1212	9884	\N	100754
7054	1206	9861	\N	50455
7055	1206	9862	\N	5000
7056	1206	9864	\N	38700
7057	1206	9865	\N	1875
7058	1206	9866	\N	1875
7059	1206	9867	\N	1875
7060	1206	9868	\N	1250
7061	1206	9869	\N	2250
7062	1206	9870	\N	1250
7063	1206	9871	\N	2500
7064	1206	9872	\N	1875
7065	1218	9902	\N	4084
7066	1218	9905	\N	625
7067	1227	9955	\N	189
7068	1218	9906	\N	4560
7069	1218	9907	\N	5925
7070	1226	9950	\N	298000
7071	1218	9908	\N	9700
7072	1218	9909	\N	51750
7073	1218	9910	\N	45500
7074	1218	9911	\N	33375
7075	1218	9912	\N	101750
7076	1218	9913	\N	36225
7077	1218	9914	\N	98
7078	1218	9915	\N	2250
7079	1218	9917	\N	1230
7080	1206	9873	\N	690
7081	1206	9874	\N	5000
7082	1220	9937	\N	72
7083	1220	9938	\N	72
7084	1220	9939	\N	72
7085	1220	9926	\N	20250
7086	1217	9888	\N	541.137
7087	1217	9889	\N	396.075
7088	1217	9890	\N	3035
7089	1217	9891	\N	770
7090	1217	9892	\N	32350
7091	1217	9893	\N	11900
7092	1217	9894	\N	12900
7093	1217	9895	\N	1780
7094	1217	9898	\N	620.76
7095	1220	9927	\N	2065
7096	1220	9929	\N	3300
7097	1220	9930	\N	5300
7098	1220	9931	\N	32900
7099	1220	9932	\N	8244
7100	1220	9933	\N	5875
7101	1220	9934	\N	315
7102	1220	9935	\N	806
7103	1220	9936	\N	72
7104	1220	9940	\N	29
7105	1227	9958	\N	138
7106	1227	9959	\N	88
7107	1227	9960	\N	9505
7108	1227	9961	\N	314
7109	1227	9962	\N	275
7110	1227	9963	\N	45943
7111	1227	9964	\N	15000
7112	1227	9965	\N	3130
7113	1227	9966	\N	3130
7114	1227	9967	\N	1500
7115	1227	9969	\N	4130
7116	1227	9970	\N	4130
7117	1227	9971	\N	375
7118	1227	9972	\N	64884
7119	1227	9973	\N	72944
7120	1227	9974	\N	88
7121	1227	9975	\N	625
7122	1227	9976	\N	125
7123	1227	9977	\N	188
7124	1227	9978	\N	250
7125	1227	9979	\N	63
7126	1227	9980	\N	88
7127	1227	9981	\N	188
7128	1227	9982	\N	188
7129	1227	9983	\N	625
7130	1227	9984	\N	13
7131	1227	9985	\N	250
7132	1227	9986	\N	313
7133	1227	9987	\N	875
7134	1227	9988	\N	625
7135	1227	9989	\N	875
7136	1210	9920	\N	38332
7137	1227	9951	\N	1875
7138	1227	9954	\N	5100
7139	1227	9956	\N	189
7140	1227	9957	\N	189
7141	1228	9998	\N	287
7142	1231	10071	\N	0
7143	1228	10000	\N	13000
7144	1228	10001	\N	2000
7145	1228	10002	\N	15000
7146	1228	10009	\N	700
7147	1228	10023	\N	275
7148	1226	9990	\N	100150
7149	1230	10056	\N	48242
7150	1230	10064	\N	14488
7151	1228	9991	\N	2800
7152	1228	9992	\N	2500
7153	1228	9993	\N	3500
7154	1228	9994	\N	2500
7155	1228	9995	\N	500
7156	1228	9997	\N	400
7157	1231	10070	\N	0
7158	1231	10072	\N	0
7159	1231	10073	\N	0
7160	1231	10074	\N	0
7161	1231	10075	\N	0
7162	1232	10076	\N	242500
7163	1232	10077	\N	155000
7164	1230	10069	\N	12500
7165	1233	10078	\N	430392
7166	1233	10079	\N	95000
7167	1233	10080	\N	60000
7168	1233	10081	\N	1000
7169	1233	10082	\N	2500
7170	1233	10084	\N	6000
7171	1233	10085	\N	48450
7172	1233	10086	\N	32500
7173	1228	10012	\N	52500
7174	1228	10013	\N	21067
7175	1228	10014	\N	33700
7176	1228	10015	\N	1625
7177	1228	10016	\N	475
7178	1228	10019	\N	200
7179	1228	10020	\N	88
7180	1228	10021	\N	13
7181	1228	10022	\N	82
7182	1228	10024	\N	100
7183	1228	10025	\N	150
7184	1210	9878	\N	75325
7185	1210	9880	\N	2500
7186	1210	9881	\N	35325
7187	1210	9882	\N	19325
7188	1210	9883	\N	22000
7189	1233	10087	\N	31350
7190	1233	10088	\N	10450
7191	1228	10003	\N	33000
7192	1230	10057	\N	8500
7193	1228	10004	\N	8000
7194	1228	10005	\N	20000
7195	1228	10006	\N	5000
7196	1228	10007	\N	5500
7197	1230	10058	\N	6545
7198	1230	10059	\N	5688
7199	1230	10060	\N	72504
7200	1230	10061	\N	1250
7201	1230	10062	\N	12500
7202	1230	10063	\N	1563
7203	1230	10065	\N	10313
7204	1230	10067	\N	8594
7205	1230	10068	\N	21875
7206	1228	10008	\N	3600
7207	1228	10010	\N	9500
7208	1228	10011	\N	700
7209	1228	10026	\N	125
7210	1228	10027	\N	188
7211	1228	10028	\N	163
7212	1228	10029	\N	3300
7213	1228	10030	\N	813
7214	1235	10089	\N	20167
7215	1235	10090	\N	9800
7216	1235	10091	\N	28100
7217	1235	10092	\N	6321
7218	1235	10093	\N	16250
7219	1235	10094	\N	1950
7220	1235	10095	\N	1350
7221	1235	10096	\N	841
7222	1235	10097	\N	22669
7223	1235	10098	\N	5625
7224	1235	10099	\N	3125
7225	1235	10100	\N	1313
7226	1235	10101	\N	10625
7227	1235	10102	\N	9475
7228	1235	10103	\N	4063
7229	1235	10104	\N	1500
7230	1235	10105	\N	2813
7231	1235	10106	\N	4500
7232	1235	10107	\N	438
7233	1235	10108	\N	438
7234	1235	10109	\N	5000
7235	1235	10110	\N	15625
7236	1235	10111	\N	18750
7237	1228	10031	\N	438
7238	1228	10032	\N	425
7239	1228	10033	\N	125
7240	1228	10034	\N	750
7241	1228	10035	\N	50
7242	1228	10036	\N	125
7243	1228	10037	\N	2000
7244	1228	10038	\N	2000
7245	1228	10039	\N	125
7246	1228	10040	\N	313
7247	1228	10041	\N	438
7248	1228	10042	\N	688
7249	1228	10043	\N	163
7250	1228	10044	\N	413
7251	1228	10045	\N	63
7252	1228	10046	\N	500
7253	1228	10048	\N	250
7254	1228	10049	\N	550
7255	1235	10112	\N	5250
7256	1235	10113	\N	4375
7257	1228	10050	\N	2000
7258	1228	10051	\N	375
7259	1228	10052	\N	7500
7260	1228	10053	\N	3125
7261	1228	10054	\N	3375
7262	1228	10055	\N	3125
7263	1235	10114	\N	4500
7264	1235	10120	\N	20688
7265	1235	10121	\N	1063
7266	1236	10124	\N	165
7267	1236	10125	\N	95000
7268	1236	10126	\N	35000
7269	1236	10127	\N	79250
7270	1236	10128	\N	5450
7271	1238	10147	\N	237023
7272	1237	10129	\N	625
7273	1237	10130	\N	625
7274	1237	10131	\N	875
7275	1235	10122	\N	2000
7276	1235	10123	\N	4375
7277	1237	10132	\N	875
7278	1237	10133	\N	875
7279	1237	10134	\N	875
7280	1237	10135	\N	30050
7281	1245	10157	\N	1175
7282	1245	10158	\N	1300
7283	1237	10136	\N	150
7284	1237	10137	\N	563
7285	1237	10138	\N	12898
7286	1237	10139	\N	4750
7287	1237	10140	\N	1032
7288	1237	10141	\N	75
7289	1237	10142	\N	50000
7290	1237	10143	\N	20000
7291	1238	10144	\N	2170401
7292	1238	10145	\N	2789173
7293	1238	10146	\N	3486467
7294	1235	10115	\N	4500
7295	1160	9429	\N	4212000
7296	1235	10116	\N	6875
7297	1235	10117	\N	5000
7298	1235	10118	\N	1988
7299	1235	10119	\N	6000
7300	1241	10152	\N	28500
7301	1240	10151	\N	2500
7302	1245	10160	\N	1000
7303	1245	10161	\N	5000
7304	1245	10162	\N	6875
7305	1239	10148	\N	19700
7306	1239	10149	\N	13500
7307	1245	10163	\N	1000
7308	1239	10150	\N	13500
7309	1245	10156	\N	2800
7310	1247	10181	\N	77500
7311	1247	10182	\N	35000
7312	1247	10183	\N	35000
7313	1247	10184	\N	8250
7314	1247	10185	\N	35000
7315	1247	10189	\N	25000
7316	1247	10190	\N	25000
7317	1247	10191	\N	6250
7318	1248	10199	\N	14299
7319	1252	10212	\N	1764
7320	1252	10213	\N	2186
7321	1252	10214	\N	1300
7322	1252	10219	\N	250
7323	1253	10221	\N	57716
7324	1253	10222	\N	1067
7325	1253	10224	\N	11000
7326	1253	10225	\N	2500
7327	1253	10226	\N	800
7328	1253	10227	\N	3000
7329	1253	10228	\N	193000
7330	1253	10229	\N	5460
7331	1253	10230	\N	7530
7332	1253	10231	\N	1500
7333	1253	10232	\N	9000
7334	1248	10200	\N	15000
7335	1248	10201	\N	3125
7336	1248	10202	\N	3750
7337	1248	10203	\N	1500
7338	1248	10204	\N	2125
7339	1248	10205	\N	4750
7340	1248	10206	\N	10000
7341	1248	10207	\N	5625
7342	1248	10208	\N	36875
7343	1248	10209	\N	8125
7344	1245	10164	\N	538
7345	1245	10165	\N	538
7346	1245	10166	\N	538
7347	1245	10167	\N	5625
7348	1245	10168	\N	719
7349	1245	10169	\N	1063
7350	1245	10170	\N	1500
7351	1245	10171	\N	6250
7352	1245	10172	\N	6630
7353	1245	10173	\N	969
7354	1245	10174	\N	4750
7355	1247	10192	\N	18750
7356	1247	10193	\N	56875
7357	1261	10267	\N	5500
7358	1261	10268	\N	4200
7359	1261	10269	\N	4200
7360	1261	10270	\N	1650
7361	1261	10271	\N	1550
7362	1261	10272	\N	4350
7363	1261	10276	\N	7800
7364	1262	10273	\N	49500
7365	1262	10274	\N	58500
7366	1262	10275	\N	57500
7367	1263	10278	\N	60000
7368	1256	10246	\N	3234
7369	1257	10248	\N	3750
7370	1257	10249	\N	2750
7371	1254	10234	\N	8750
7372	1254	10235	\N	63370
7373	1254	10236	\N	3750
7374	1254	10237	\N	2750
7375	1254	10238	\N	2363
7376	1254	10239	\N	638
7377	1254	10240	\N	5983
7378	1254	10241	\N	5000
7379	1257	10250	\N	3565
7380	1260	10264	\N	10603
7381	1260	10265	\N	15915
7382	1257	10251	\N	625
7383	1253	10233	\N	2500
7384	1247	10194	\N	112500
7385	1246	10177	\N	0
7386	1264	10289	\N	116839
7387	1246	10178	\N	0
7388	1247	10198	\N	10625
7389	1257	10252	\N	5850
7390	1257	10253	\N	5000
7391	1259	10263	\N	51000
7392	1259	10277	\N	11500
7393	1256	10244	\N	64586
7394	1247	10195	\N	43750
7395	1257	10254	\N	1875
7396	1257	10255	\N	1625
7397	1257	10256	\N	63
7398	1256	10245	\N	938
7399	1257	10247	\N	8750
7400	1247	10196	\N	62500
7401	1247	10197	\N	2812.5
7402	1257	10257	\N	63
7403	1257	10258	\N	15000
7404	1257	10259	\N	1250
7405	1263	10279	\N	78000
7406	1263	10280	\N	110000
7407	1263	10281	\N	150000
7408	1269	10339	\N	500
7409	1269	10341	\N	500
7410	1269	10342	\N	2200
7411	1269	10343	\N	2200
7412	1269	10344	\N	1140
7413	1269	10345	\N	3650
7414	1269	10346	\N	3650
7415	1283	10432	\N	44500
7416	1267	10325	\N	858
7417	1267	10327	\N	812.5
7418	1269	10347	\N	1460
7419	1267	10328	\N	9375
7420	1267	10329	\N	750
7421	1269	10348	\N	230
7422	1269	10349	\N	230
7423	1267	10330	\N	1250
7424	1267	10331	\N	2557
7425	1267	10332	\N	1625
7426	1267	10333	\N	625
7427	1267	10334	\N	2500
7428	1267	10335	\N	625
7429	1267	10336	\N	48250
7430	1267	10337	\N	16875
7431	1264	10282	\N	47656
7432	1264	10283	\N	40715
7433	1271	10368	\N	34500
7434	1270	10353	\N	2350
7435	1276	10386	\N	1900
7436	1276	10389	\N	820
7437	1276	10377	\N	12500
7438	1269	10350	\N	1180
7439	1292	10584	\N	74200
7440	1269	10351	\N	1240
7441	1276	10378	\N	4500
7442	1276	10379	\N	650
7443	1270	10354	\N	120
7444	1270	10355	\N	55
7445	1270	10356	\N	20
7446	1271	10369	\N	4500
7447	1271	10370	\N	660
7448	1271	10371	\N	4600
7449	1270	10357	\N	80
7450	1270	10358	\N	140
7451	1270	10359	\N	120
7452	1270	10360	\N	130
7453	1270	10361	\N	340
7454	1271	10372	\N	4220
7455	1276	10380	\N	2000
7456	1270	10362	\N	1450
7457	1270	10363	\N	350
7458	1270	10364	\N	1250
7459	1270	10365	\N	220
7460	1271	10373	\N	442409
7461	1270	10366	\N	450
7462	1276	10381	\N	1800
7463	1276	10382	\N	283086
7464	1276	10383	\N	1070
7465	1276	10384	\N	1575
7466	1276	10390	\N	2000
7467	1276	10391	\N	5700
7468	1276	10392	\N	650
7469	1276	10393	\N	108000
7470	1276	10394	\N	1900
7471	1270	10367	\N	450
7472	1276	10395	\N	1900
7473	1276	10396	\N	700
7474	1276	10397	\N	188
7475	1276	10398	\N	8
7476	1276	10399	\N	650
7477	1276	10400	\N	188
7478	1276	10401	\N	2625
7479	1283	10428	\N	11540
7480	1275	10385	\N	400000
7481	1283	10429	\N	5651
7482	1277	10403	\N	526605
7483	1277	10404	\N	45105
7484	1277	10405	\N	12025
7485	1277	10406	\N	15105
7486	1282	10422	\N	625000
7487	1282	10423	\N	1795000
7488	1282	10424	\N	2985500
7489	1276	10402	\N	9800
7490	1179	9603	\N	2349455
7491	1179	9604	\N	5135330
7492	1179	9605	\N	3802713
7493	1278	10407	\N	150000
7494	1278	10409	\N	1462500
7495	1278	10410	\N	380250
7496	1278	10411	\N	220000
7497	1278	10412	\N	1050000
7498	1278	10413	\N	260000
7499	1279	10414	\N	760
7500	1279	10415	\N	24261
7501	1280	10416	\N	105000
7502	1280	10417	\N	239350
7503	1280	10418	\N	53500
7504	1042	5322	\N	2000000
7505	1042	5324	\N	1500000
7506	150	8259	\N	250
7507	1042	5325	\N	1000000
7508	1078	8626	\N	25
7509	1078	8627	\N	400
7510	1078	8628	\N	490
7511	1078	8630	\N	500
7512	1078	8631	\N	240
7513	1078	8632	\N	210
7514	1265	10304	\N	62000
7515	1265	10305	\N	161500
7516	1265	10315	\N	63000
7517	1265	10317	\N	20050
7518	1265	10321	\N	72500
7519	1265	10322	\N	53500
7520	1265	10323	\N	70800
7521	1216	9886	\N	140355
7522	1216	9887	\N	140355
7523	1283	10426	\N	420
7524	1283	10427	\N	17500
7525	1283	10434	\N	4750
7526	1283	10435	\N	23125
7527	1283	10436	\N	38
7528	1283	10437	\N	44
7529	1283	10438	\N	32
7530	1283	10439	\N	2500
7531	1283	10440	\N	938
7532	1281	10419	\N	747.5
7533	1283	10425	\N	35500
7534	1291	10569	\N	645328
7535	1289	10516	\N	1750
7536	1289	10518	\N	2750
7537	1289	10556	\N	5460
7538	1311	10682	\N	1100
7539	1286	10499	\N	18625
7540	1286	10501	\N	46750
7541	1286	10502	\N	5625
7542	1286	10503	\N	2063
7543	1286	10504	\N	1813
7544	1286	10505	\N	17750
7545	1286	10498	\N	116400
7546	1286	10506	\N	13125
7547	1286	10507	\N	25626
7548	1286	10508	\N	26312.5
7549	1286	10509	\N	53125
7550	1311	10676	\N	1014
7551	1311	10677	\N	113
7552	1311	10678	\N	1200
7553	1311	10680	\N	1200
7554	1311	10681	\N	1613
7555	1291	10567	\N	400000
7556	1291	10571	\N	323872
7557	1291	10573	\N	45000
7558	1291	10574	\N	5000
7559	1291	10581	\N	15000
7560	1289	10515	\N	1750
7561	1289	10519	\N	2750
7562	1289	10520	\N	2250
7563	1292	10582	\N	245750
7564	1289	10521	\N	2250
7565	1289	10522	\N	3000
7566	1289	10523	\N	2473
7567	1289	10524	\N	9125
7568	1289	10525	\N	738
7569	1289	10526	\N	5746
7570	1289	10527	\N	725
7571	1293	10585	\N	2200
7572	1292	10583	\N	78500
7573	150	8260	\N	2500
7574	1289	10528	\N	4548
7575	1289	10529	\N	5746
7576	1283	10430	\N	12250
7577	1283	10431	\N	472500
7578	1283	10433	\N	62500
7579	1310	10653	\N	295000
7580	1310	10654	\N	75000
7581	1305	10626	\N	11500
7582	1306	10630	\N	35800
7583	1306	10631	\N	3500
7584	1303	10616	\N	1346
7585	1304	10624	\N	113000
7586	1291	10575	\N	150000
7587	1303	10617	\N	179
7588	1291	10576	\N	15000
7589	1291	10577	\N	325000
7590	1291	10578	\N	45000
7591	1303	10619	\N	1584
7592	1303	10620	\N	191
7593	1309	10652	\N	24255
7594	1291	10579	\N	45000
7595	1291	10580	\N	20000
7596	1305	10627	\N	125
7597	1306	10632	\N	3780
7598	1306	10633	\N	550
7599	1310	10655	\N	7200
7600	1305	10628	\N	125
7601	1305	10629	\N	95
7602	1306	10634	\N	4500
7603	1306	10635	\N	750
7604	1300	10606	\N	740
7605	1297	10595	\N	29520
7606	1302	10612	\N	743.45
7607	1298	10600	\N	1245
7608	1302	10613	\N	11355
7609	1300	10656	\N	3327
7610	1300	10658	\N	2426
7611	1290	10566	\N	206500
7612	1311	10672	\N	1750
7613	1311	10673	\N	2000
7614	1311	10674	\N	1050
7615	1311	10675	\N	3275
7616	1311	10701	\N	30
7617	1311	10702	\N	5
7618	1311	10703	\N	5
7619	1311	10704	\N	950
7620	1311	10705	\N	24
7621	1311	10706	\N	24
7622	1311	10707	\N	43
7623	1311	10708	\N	55
7624	1311	10659	\N	31782
7625	1311	10709	\N	448
7626	1311	10660	\N	66750
7627	1311	10661	\N	20075
7628	1311	10662	\N	10750
7629	1311	10663	\N	12463
7630	1311	10664	\N	125
7631	1311	10665	\N	2500
7632	1311	10667	\N	100
7633	1311	10668	\N	5
7634	1311	10669	\N	5
7635	1311	10670	\N	3125
7636	1311	10671	\N	850
7637	1312	10710	\N	215750
7638	1289	10530	\N	875
7639	1289	10531	\N	875
7640	1289	10532	\N	684
7641	1289	10533	\N	4467
7642	1289	10534	\N	4250
7643	1306	10636	\N	15000
7644	1313	10713	\N	215750
7645	1312	10711	\N	148500
7646	1312	10712	\N	33600
7647	1313	10715	\N	75000
7648	1313	10716	\N	1800
7649	1289	10536	\N	47000
7650	1289	10537	\N	5375
7651	1289	10538	\N	675
7652	1289	10539	\N	9875
7653	1289	10540	\N	3000
7654	1289	10541	\N	3000
7655	1289	10542	\N	3349
7656	1289	10543	\N	9875
7657	1289	10544	\N	8313
7658	1289	10545	\N	1500
7659	1289	10546	\N	6750
7660	1289	10547	\N	92262
7661	1289	10548	\N	191637
7662	1289	10550	\N	61137
7663	1289	10551	\N	78637
7664	1289	10552	\N	88637
7665	1311	10683	\N	238
7666	1311	10684	\N	1238
7667	1311	10685	\N	250
7668	1311	10686	\N	488
7669	1289	10553	\N	88637
7670	1289	10554	\N	107387
7671	1289	10555	\N	2522
7672	1221	9941	\N	2000
7673	1221	9944	\N	4500
7674	1221	9945	\N	6000
7675	1221	9946	\N	4250
7676	1313	10717	\N	97500
7677	1311	10687	\N	2688
7678	1311	10688	\N	525
7679	1311	10689	\N	488
7680	1311	10690	\N	250
7681	1315	10724	\N	167
7682	1315	10725	\N	541
7683	1315	10726	\N	575000
7684	1315	10727	\N	90000
7685	1315	10728	\N	100000
7686	1315	10729	\N	150000
7687	1315	10730	\N	80000
7688	1315	10731	\N	70000
7689	1311	10691	\N	225
7690	1221	9942	\N	1000
7691	1221	9943	\N	2000
7692	1334	10795	\N	5460
7693	1334	10796	\N	49300
7694	1334	10797	\N	60000
7695	1334	10800	\N	863
7696	1334	10801	\N	230
7697	1334	10802	\N	2625
7698	1336	10831	\N	10
7699	1322	10764	\N	649250
7700	1311	10692	\N	238
7701	1311	10693	\N	260
7702	1311	10694	\N	425
7703	1311	10695	\N	35
7704	1311	10696	\N	37
7705	1311	10697	\N	70
7706	1311	10698	\N	18
7707	1311	10699	\N	43
7708	1311	10700	\N	5
7709	1316	10732	\N	15934
7710	1324	10760	\N	389250
7711	1317	10733	\N	748
7712	1317	10735	\N	3450
7713	1319	10751	\N	469200
7714	1317	10736	\N	2645
7715	1331	10780	\N	1100
7716	1331	10777	\N	2200
7717	1333	10781	\N	14000
7718	1333	10783	\N	55000
7719	1333	10784	\N	3000
7720	1333	10785	\N	9000
7721	1333	10786	\N	38000
7722	1333	10787	\N	13000
7723	1333	10788	\N	7000
7724	1333	10789	\N	3300
7725	1333	10790	\N	1800
7726	1333	10791	\N	4500
7727	1333	10792	\N	2000
7728	1333	10793	\N	20000
7729	1329	10772	\N	104.82
7730	1330	10773	\N	222.22
7731	1327	10766	\N	850
7732	1327	10767	\N	127938
7733	1325	10761	\N	398950
7734	1319	10752	\N	65000
7735	1303	10621	\N	1856
7736	1303	10622	\N	290
7737	1323	10758	\N	235500
7738	1323	10759	\N	163250
7739	1336	10806	\N	1840
7740	1317	10737	\N	5750
7741	1334	10794	\N	9890
7742	1336	10808	\N	230
7743	1336	10809	\N	213
7744	1317	10738	\N	2875
7745	1336	10811	\N	50100
7746	1336	10824	\N	5267
7747	1336	10825	\N	2415
7748	1336	10826	\N	5980
7749	1336	10833	\N	1380
7750	1336	10834	\N	403
7751	1336	10835	\N	1380
7752	1336	10836	\N	633
7753	1336	10837	\N	21
7754	1336	10838	\N	21
7755	1336	10839	\N	29
7756	1336	10840	\N	58
7757	1336	10841	\N	575
7758	1336	10842	\N	518
7759	1322	10765	\N	371000
7760	1322	10763	\N	834750
7761	1336	10804	\N	45500
7762	1336	10812	\N	1150
7763	1336	10813	\N	69
7764	1336	10814	\N	805
7765	1336	10815	\N	2875
7766	1336	10816	\N	4600
7767	1336	10817	\N	58
7768	1336	10818	\N	18
7769	1336	10819	\N	403
7770	1336	10820	\N	633
7771	1336	10821	\N	460
7772	1336	10822	\N	9200
7773	1336	10823	\N	863
7774	1336	10827	\N	5175
7775	1336	10828	\N	748
7776	1336	10829	\N	575
7777	1336	10830	\N	1357
7778	1336	10843	\N	288
7779	1336	10844	\N	1150
7780	1336	10845	\N	58
7781	1336	10847	\N	1610
7782	1336	10848	\N	3680
7783	1336	10849	\N	677
7784	1336	10850	\N	190
7785	1336	10851	\N	345
7786	1336	10852	\N	598
7787	1336	10853	\N	167
7788	1336	10854	\N	87
7789	1336	10855	\N	288
7790	1336	10859	\N	92
7791	1336	10860	\N	138
7792	1336	10861	\N	127
7793	1331	10775	\N	66000
7794	1331	10776	\N	13500
7795	1328	10768	\N	36000
7796	1333	10782	\N	110000
7797	1328	10769	\N	8000
7798	1328	10771	\N	8000
7799	1336	10864	\N	288
7800	1337	10897	\N	5675
7801	1337	10898	\N	5330
7802	1337	10899	\N	9388
7803	1337	10900	\N	31551
7804	1337	10901	\N	2800
7805	1337	10902	\N	10275
7806	1337	10903	\N	5675
7807	1337	10904	\N	5675
7808	1337	10905	\N	575
7809	1337	10906	\N	5675
7810	1337	10907	\N	3778
7811	1337	10908	\N	56
7812	1337	10909	\N	1680
7813	1337	10910	\N	1231
7814	1337	10911	\N	79
7815	1337	10912	\N	6422
7816	1337	10913	\N	10580
7817	1337	10914	\N	1380
7818	1337	10915	\N	26289
7819	1337	10916	\N	12088
7820	1337	10917	\N	2256
7821	1337	10918	\N	3090
7822	1337	10920	\N	19717
7823	1337	10921	\N	17642
7824	1337	10922	\N	117692
7825	1337	10924	\N	273700
7826	1319	10753	\N	21900
7827	1339	10936	\N	2025
7828	1338	10927	\N	54950
7829	1338	10929	\N	51875
7830	1338	10930	\N	2200
7831	1336	10885	\N	6900
7832	1336	10886	\N	518
7833	1336	10888	\N	863
7834	1338	10931	\N	1996
7835	1336	10846	\N	2990
7836	1336	10856	\N	242
7837	1336	10857	\N	104
7838	1336	10858	\N	83
7839	1336	10862	\N	92
7840	1336	10863	\N	115
7841	1336	10865	\N	12
7842	1336	10866	\N	12
7843	1336	10867	\N	12
7844	1336	10868	\N	12
7845	1336	10869	\N	12
7846	1336	10870	\N	29
7847	1336	10871	\N	58
7848	1336	10872	\N	58
7849	1336	10873	\N	12
7850	1336	10874	\N	12
7851	1336	10875	\N	58
7852	1336	10876	\N	23
7853	1336	10877	\N	625
7854	1336	10878	\N	620
7855	1336	10879	\N	740
7856	1336	10880	\N	625
7857	1336	10881	\N	863
7858	1336	10882	\N	54050
7859	1336	10883	\N	1380
7860	1336	10884	\N	1840
7861	1336	10889	\N	1150
7862	1336	10890	\N	385
7863	1336	10891	\N	38
7864	1336	10892	\N	29
7865	1336	10893	\N	1840
7866	1336	10894	\N	1380
7867	1336	10895	\N	4408
7868	1337	10896	\N	25000
7869	1337	10925	\N	6670
7870	1337	10926	\N	17135
7871	1339	10933	\N	3720
7872	1339	10934	\N	3274
7873	1339	10935	\N	5432
7874	1342	10971	\N	200000
7875	1342	10972	\N	290
7876	1217	9899	\N	560
7877	1217	9900	\N	7260
7878	1217	9901	\N	198000
7879	1195	9775	\N	21800
7880	1195	9776	\N	10550
7881	1195	9777	\N	31700
7882	1317	10739	\N	748
7883	1195	9778	\N	34300
7884	1195	9779	\N	450000
7885	1195	9780	\N	330000
7886	1175	9586	\N	5491840
7887	1175	9587	\N	407800
7888	403	6465	\N	4524.7
7889	317	6405	\N	3762000
7890	317	6407	\N	165000
7891	1340	10969	\N	1399000
7892	321	6414	\N	4032000
7893	1317	10740	\N	5175
7894	322	6415	\N	35000
7895	1341	10970	\N	6155000
7896	1258	10260	\N	85000
7897	1258	10262	\N	140000
7898	1339	10932	\N	11200
7899	1317	10741	\N	3220
7900	1317	10742	\N	34500
7901	1317	10743	\N	115
7902	1317	10744	\N	7475
7903	1317	10745	\N	20897
7904	1317	10746	\N	24924
7905	1317	10747	\N	5923
7906	1317	10748	\N	294
7907	1317	10749	\N	6900
7908	1317	10750	\N	9200
7909	1339	10937	\N	1120
7910	1339	10938	\N	3030
7911	1339	10939	\N	20250
7912	1339	10940	\N	12350
7913	1339	10941	\N	2
7914	1339	10942	\N	2
7915	1339	10943	\N	2
7916	1339	10944	\N	12
7917	1339	10945	\N	58
7918	1339	10946	\N	288
7919	1339	10947	\N	6250
7920	1339	10948	\N	1880
7921	1339	10949	\N	3375
7922	1339	10950	\N	2225
7923	1339	10951	\N	1538
7924	1339	10952	\N	1480
7925	1339	10953	\N	1250
7926	1339	10954	\N	7325
7927	1339	10955	\N	59700
7928	1339	10956	\N	2795
7929	1339	10957	\N	1105
7930	1339	10958	\N	59550
7931	1339	10959	\N	35486
7932	1339	10960	\N	274550
7933	1339	10961	\N	8670
7934	1339	10963	\N	24435
7935	1339	10964	\N	5145
7936	1339	10965	\N	5460
7937	1339	10966	\N	2625
7938	1339	10967	\N	4700
7939	1339	10968	\N	30350
7940	1343	10973	\N	180000
7941	1343	10974	\N	20000
7942	1344	10991	\N	7600
7943	1344	10976	\N	4180
7944	1344	10978	\N	280
7945	1344	10980	\N	560
7946	1344	10981	\N	41
7947	1344	10982	\N	6
7948	1344	10983	\N	33955
7949	1344	10984	\N	20104
7950	1344	10985	\N	36541
7951	1344	10986	\N	19525
7952	1344	10987	\N	44091
7953	1344	10988	\N	28000
7954	1344	10989	\N	13500
7955	1344	10990	\N	5065
7956	1344	10992	\N	2025
7957	1344	10993	\N	1035
7958	1344	10994	\N	345
7959	1344	10995	\N	23
7960	1344	10996	\N	144
7961	1344	10997	\N	69
7962	1344	10998	\N	52
7963	1344	10999	\N	46
7964	1344	11000	\N	18
7965	1344	11001	\N	23
7966	1344	11002	\N	18
7967	1344	11003	\N	58
7968	1344	11004	\N	87
7969	1344	11005	\N	10
7970	1344	11006	\N	173
7971	1344	11007	\N	173
7972	1344	11008	\N	173
7973	1344	11012	\N	12
7974	1344	11013	\N	12
7975	1344	11014	\N	12
7976	1344	11015	\N	230
7977	1344	11021	\N	2875
7978	1344	11022	\N	5175
7979	1344	11023	\N	575
7980	1344	11024	\N	345
7981	1344	11025	\N	36639
7982	1344	11026	\N	26450
7983	704	1479	\N	54503.06
7984	704	1484	\N	21405.38
7985	704	1485	\N	101838.25
7986	1352	11051	\N	75000
7987	1352	11052	\N	35000
7988	1352	11053	\N	1500
7989	1345	11027	\N	200000
7990	1345	11028	\N	70000
7991	1345	11029	\N	95000
7992	1349	11036	\N	2100000
7993	1351	11049	\N	0
7994	1351	11050	\N	0
7995	1347	11030	\N	270000
7996	1347	11031	\N	35000
7997	1347	11032	\N	45000
7998	697	1438	\N	115000
7999	697	1439	\N	55000
8000	697	1440	\N	25750
8001	697	1441	\N	15000
8002	697	1445	\N	48000
8003	1344	11009	\N	230
8004	1344	11010	\N	69
8005	1344	11011	\N	69
8006	1344	11016	\N	345
8007	1344	11017	\N	1150
8008	1344	11018	\N	575
8009	1344	11019	\N	690
8010	1344	11020	\N	15525
8011	1350	11037	\N	62012
8012	1350	11038	\N	22381
8013	1350	11039	\N	8004
8014	1350	11040	\N	2875
8015	1350	11041	\N	2875
8016	1350	11042	\N	4025
8017	1350	11043	\N	2645
8018	1350	11044	\N	29
8019	1350	11045	\N	2645
8020	1350	11046	\N	5750
8021	1350	11047	\N	4439
8022	1350	11048	\N	17250
8023	1069	8557	\N	150000
8024	1069	8558	\N	150000
8025	295	7317	\N	35000
8026	216	7356	\N	2650
8027	216	7354	\N	2200
8028	1355	11058	\N	1726
8029	1355	11059	\N	373054
8030	1355	11060	\N	325
8031	1355	11061	\N	9775
8032	1356	11065	\N	104
8033	223	8194	\N	650
8034	223	8195	\N	900
8035	223	8197	\N	230
8036	223	8202	\N	1000
8037	223	8200	\N	25
8038	223	8201	\N	320
8039	151	8250	\N	1000
8040	223	8198	\N	40
8041	223	8199	\N	65
8042	151	8251	\N	300
8043	151	8252	\N	6000
8044	265	7718	\N	10000
8045	234	7754	\N	1200
8046	151	8253	\N	900
8047	234	7762	\N	1200
8048	234	7765	\N	2500
8049	234	7752	\N	110
8050	244	8264	\N	450
8051	244	8261	\N	1100
8052	244	8263	\N	200
8053	314	7736	\N	600
8054	314	7737	\N	350
8055	314	7738	\N	200
8056	314	7739	\N	90
8057	194	8167	\N	2950
8058	223	8181	\N	125
8059	149	8281	\N	380
8060	149	8288	\N	4500
8061	149	8289	\N	2
8062	149	8295	\N	16000
8063	226	7630	\N	400
8064	296	7319	\N	190000
8065	223	8196	\N	250
8066	495	7288	\N	2500
8067	952	6892	\N	27000
8068	330	7394	\N	7500
8069	330	7378	\N	119600
8070	330	7377	\N	15500
8071	330	7376	\N	5847.5
8072	330	7375	\N	8000
8073	330	7374	\N	30214.285714
8074	514	7322	\N	20000
8075	514	7323	\N	45000
8076	514	7321	\N	35000
8077	223	8182	\N	720
8078	223	8183	\N	100
8079	223	8189	\N	60
8080	223	8192	\N	200
8081	223	8191	\N	350
8082	223	8190	\N	150
8083	518	8224	\N	8500
8084	518	8227	\N	400
8085	518	8225	\N	500
8086	518	8226	\N	1700
8087	518	8228	\N	250
8088	234	7769	\N	5000
8089	234	7753	\N	25
8090	234	7767	\N	200
8091	234	7766	\N	4500
8092	234	7760	\N	110
8093	234	7763	\N	125
8094	234	7764	\N	1200
8095	234	7758	\N	3000
8096	234	7759	\N	8500
8097	240	8298	\N	26.83
8098	236	8163	\N	30
8099	243	8272	\N	10800
8100	243	8273	\N	7800
8101	244	8268	\N	300
8102	244	8267	\N	500
8103	244	8266	\N	1000
8104	244	8262	\N	500
8105	244	8265	\N	350
8106	314	7740	\N	800
8107	304	8445	\N	29778
8108	521	8212	\N	1800
8109	518	8230	\N	2550
8110	521	8213	\N	6000
8111	521	8222	\N	2800
8112	517	8236	\N	80
8113	312	7728	\N	8300
8114	1131	9122	\N	28140
8115	1131	9123	\N	20000
8116	1134	9150	\N	3955
8117	1137	9176	\N	9500
8118	1137	9174	\N	11000
8119	1137	9177	\N	5500
8120	1137	9169	\N	100
8121	1137	9170	\N	20000
8122	1137	9171	\N	24000
8123	1137	9172	\N	40000
8124	1137	9173	\N	205
8125	1138	9178	\N	1050
8126	1138	9179	\N	7500
8127	1138	9180	\N	4000
8128	1138	9181	\N	6500
8129	1138	9182	\N	1350
8130	1138	9184	\N	4837
8131	1138	9186	\N	1500
8132	1138	9185	\N	4350
8133	330	7382	\N	173000
8134	223	8193	\N	1000
8135	1129	9086	\N	3125
8136	1149	9333	\N	6250
8137	1129	9075	\N	180
8138	1129	9076	\N	38
8139	1129	9077	\N	25000
8140	1129	9078	\N	286
8141	1129	9079	\N	286
8142	1129	9080	\N	308
8143	1129	9081	\N	2625
8144	1129	9082	\N	5460
8145	1129	9083	\N	15
8146	1129	9084	\N	500
8147	1129	9085	\N	6500
8148	1149	9320	\N	625
8149	1149	9310	\N	1250
8150	1149	9311	\N	2500
8151	1149	9312	\N	625
8152	1149	9314	\N	5000
8153	1149	9315	\N	625
8154	1149	9316	\N	2500
8155	1149	9317	\N	1250
8156	1149	9318	\N	3125
8157	1149	9319	\N	6250
8158	1149	9321	\N	1000
8159	1149	9322	\N	16252
8160	1149	9323	\N	35500
8161	1149	9324	\N	5000
8162	1149	9325	\N	625
8163	1149	9326	\N	2500
8164	1149	9327	\N	8750
8165	1149	9328	\N	5000
8166	1129	9087	\N	1250
8167	1129	9088	\N	3750
8168	1129	9089	\N	6250
8169	1129	9090	\N	1125
8170	1129	9091	\N	125
8171	1129	9093	\N	313
8172	1129	9094	\N	188
8173	1129	9095	\N	250
8174	1129	9096	\N	1250
8175	1129	9097	\N	1250
8176	1129	9099	\N	2800
8177	1129	9100	\N	3000
8178	1129	9101	\N	2500
8179	1129	9069	\N	19500
8180	1129	9070	\N	21500
8181	1129	9071	\N	9034
8182	1129	9072	\N	22500
8183	1129	9073	\N	21000
8184	1129	9074	\N	850
8185	1037	5264	\N	3500
8186	1037	5265	\N	3500
8187	874	5365	\N	58500
8188	909	5437	\N	2800
8189	909	5435	\N	2800
8190	909	5438	\N	2800
8191	909	5439	\N	2800
8192	909	5440	\N	1800
8193	909	5441	\N	1800
8194	910	5442	\N	2800
8195	1133	9297	\N	45000
8196	1128	9065	\N	7250
8197	1128	9066	\N	4000
8198	1127	9064	\N	6025
8199	521	8218	\N	500
8200	521	8219	\N	6000
8201	521	8220	\N	2200
8202	910	5444	\N	2800
8203	910	5443	\N	2800
8204	910	5445	\N	2800
8205	910	5446	\N	2800
8206	848	2308	\N	10
8207	848	2309	\N	40
8208	848	2310	\N	5
8209	848	2311	\N	4
8210	848	2312	\N	40
8211	848	2313	\N	20
8212	885	5462	\N	42350
8213	885	5464	\N	15750
8214	885	5465	\N	9625
8215	330	7399	\N	154238
8216	330	7379	\N	635100
8217	910	5447	\N	2800
8218	910	5448	\N	1800
8219	913	5450	\N	3500
8220	913	5451	\N	3500
8221	913	5452	\N	3500
8222	913	5453	\N	3500
8223	913	5454	\N	3500
8224	913	5455	\N	3500
8225	898	5466	\N	2800
8226	898	5467	\N	2800
8227	899	5468	\N	2800
8228	1074	8610	\N	1680
8229	1074	8611	\N	6135
8230	1074	8612	\N	920
8231	1074	8613	\N	2130
8232	1074	8614	\N	1200
8233	1074	8615	\N	1770
8234	1074	8657	\N	3890
8235	1094	8775	\N	5000
8236	452	8577	\N	18000
8237	452	8574	\N	26400
8238	452	8576	\N	13000
8239	452	8578	\N	24000
8240	452	8594	\N	6000
8241	452	8595	\N	8000
8242	452	8598	\N	17000
8243	452	8599	\N	2250
8244	149	8282	\N	6000
8245	149	8283	\N	7000
8246	149	8284	\N	13000
8247	149	8285	\N	50000
8248	149	8293	\N	9000
8249	149	8296	\N	373
8250	241	8633	\N	7910
8251	102	8634	\N	402785
8252	281	8635	\N	190003
8253	280	8636	\N	285005
8254	274	8637	\N	369451
8255	275	8638	\N	263894
8256	218	8678	\N	1137
8257	251	8719	\N	40
8258	219	8722	\N	4748
8259	150	8256	\N	32
8260	216	7359	\N	1600
8261	892	5449	\N	103300
8262	92	7324	\N	84
8263	92	7325	\N	15000
8264	93	7303	\N	35000
8265	93	7302	\N	25000
8266	93	7301	\N	3500
8267	93	7304	\N	65000
8268	234	7768	\N	1500
8269	216	7351	\N	4550
8270	295	7316	\N	70000
8271	1069	8556	\N	300600
8272	172	8505	\N	4166.6
8273	1066	8513	\N	5400
8274	452	8591	\N	9000
8275	1070	8572	\N	5400
8276	452	8600	\N	5500
8277	452	8601	\N	4000
8278	1092	8773	\N	5500
8279	1074	8616	\N	620
8280	663	1138	\N	10000
8281	1099	8806	\N	\N
8282	1094	8776	\N	5000
8283	1094	8777	\N	3500
8284	647	1049	0	4715
8285	699	1477	0	11920
8286	708	1502	0	3690
8287	708	1496	0	13060
8288	724	1635	0	1974
8289	784	1931	0	5770
8290	832	2186	0	3842
8291	832	2189	0	2850
8292	856	2400	0	6470
8293	856	2402	0	5435
8294	1050	5355	0	2130
8295	1089	8765	\N	9000
8296	1089	8767	\N	16625
8297	1089	8745	\N	1750
8298	1089	8743	\N	875
8299	1089	8747	\N	1104
8300	1089	8748	\N	850
8301	1089	8749	\N	500
8302	1089	8750	\N	438
8303	1089	8751	\N	25000
8304	1089	8752	\N	663
8305	1089	8753	\N	413
8306	1089	8754	\N	938
8307	1089	8755	\N	3188
8308	1089	8756	\N	2375
8309	1089	8757	\N	113750
8310	1089	8758	\N	77250
8311	1089	8759	\N	5000
8312	1089	8760	\N	2000
8313	1089	8761	\N	15625
8314	1089	8762	\N	4875
8315	1089	8764	\N	600
8316	1089	8768	\N	33350
8317	1089	8769	\N	8125
8318	542	117	\N	68117
8319	542	116	\N	65200
8320	542	114	\N	164460
8321	542	112	\N	220000
8322	542	107	\N	260200
8323	150	8258	\N	800
8324	151	8249	\N	350
8325	247	8512	\N	9871
8326	325	8507	\N	620
8327	246	8724	\N	10691
8328	248	8727	\N	2415
8329	249	8728	\N	5759
8330	250	8729	\N	1327
8331	145	8478	\N	49000
8332	549	242	\N	2000
8333	151	8248	\N	1100
8334	549	241	\N	1215
8335	549	243	\N	24265
8336	542	109	\N	95000
8337	542	111	\N	178240
8338	542	113	\N	92300
8339	549	236	\N	48750
8340	549	237	\N	48530
8341	549	238	\N	9705
8342	549	239	\N	6040
8343	549	244	\N	18120
8344	553	259	\N	\N
8345	553	260	\N	\N
8346	553	262	\N	\N
8347	553	271	\N	\N
8348	553	269	\N	\N
8349	572	400	\N	\N
8350	572	401	\N	\N
8351	572	407	\N	\N
8352	452	8590	\N	12000
8353	572	408	\N	\N
8354	687	1426	0	359305
8355	594	668	\N	10000
8356	643	1043	\N	10500
8357	644	1045	\N	10500
8358	645	1047	\N	5000
8359	646	1048	\N	10000
8360	658	1124	\N	10372
8361	657	1123	\N	9554
8362	656	1122	\N	10110
8363	648	1050	\N	5000
8364	681	1332	\N	5000
8365	662	1137	\N	5000
8366	682	1336	\N	\N
8367	692	1422	\N	5000
8368	692	1423	\N	10000
8369	699	1471	\N	5000
8370	699	1472	\N	10000
8371	699	1478	0	1950
8372	707	1494	\N	5000
8373	707	1495	\N	10000
8374	700	1474	\N	11920
8375	700	1476	\N	1950
8376	708	1503	0	5046
8377	708	1508	0	4020
8378	708	1507	0	2580
8379	708	1506	0	1030
8380	708	1505	0	660
8381	708	1500	0	2890
8382	708	1501	0	2170
8383	708	1504	0	5195
8384	724	1636	0	1380
8385	732	1692	\N	\N
8386	778	1877	\N	5000
8387	775	1858	\N	5000
8388	782	1928	\N	5400
8389	782	1929	\N	10000
8390	778	1875	\N	5000
8391	778	1876	\N	5000
8392	777	1872	\N	5000
8393	777	1873	\N	5000
8394	777	1874	\N	3500
8395	781	1926	\N	5000
8396	746	1809	\N	0
8397	549	240	\N	305
8398	553	272	\N	\N
8399	553	273	\N	\N
8400	572	406	\N	\N
8401	587	618	\N	160
8402	587	621	\N	11000
8403	596	703	\N	7590
8404	781	1927	\N	10000
8405	784	1932	0	6080
8406	793	2041	\N	5000
8407	793	2042	\N	3500
8408	792	2020	\N	5000
8409	792	2022	\N	3500
8410	813	2082	\N	5400
8411	823	2137	\N	10000
8412	824	2138	\N	5400
8413	812	2091	\N	49390
8414	793	2043	\N	3500
8415	832	2188	0	3930
8416	721	1626	\N	5400
8417	833	2194	\N	5400
8418	832	2185	0	3714
8419	832	2187	0	3640
8420	832	2193	0	4950
8421	832	2192	0	6570
8422	832	2191	0	2240
8423	832	2184	0	870
8424	832	2190	0	1120
8425	835	2212	\N	5400
8426	839	2253	\N	5000
8427	839	2254	\N	3500
8428	839	2255	\N	3500
8429	839	2256	\N	5000
8430	839	2257	\N	3500
8431	828	2157	\N	5000
8432	828	2158	\N	3500
8433	828	2159	\N	3500
8434	828	2160	\N	5000
8435	828	2161	\N	3500
8436	828	2162	\N	2000
8437	855	2398	\N	10000
8438	637	903	\N	70000
8439	855	2397	\N	5400
8440	856	2399	0	5426
8441	856	2401	0	3670
8442	819	2092	\N	5000
8443	812	2090	\N	5000
8444	793	2040	\N	5000
8445	983	2593	\N	158400
8446	983	2594	\N	111600
8447	983	2595	\N	112200
8448	983	2596	\N	244200
8449	992	2680	\N	5000
8450	992	2677	\N	5000
8451	992	2681	\N	3500
8452	992	2682	\N	2000
8453	992	2678	\N	3500
8454	992	2675	\N	5000
8455	992	2679	\N	3500
8456	996	2699	\N	5400
8457	1013	2842	\N	5000
8458	1013	2845	\N	3500
8459	1013	2843	\N	5000
8460	1016	2892	\N	5000
8461	1016	2894	\N	3500
8462	1016	2895	\N	2000
8463	1033	3049	\N	3500
8464	1033	3050	\N	3500
8465	1033	3051	\N	3500
8466	747	2021	\N	7000
8467	1032	3043	\N	5000
8468	1032	3045	\N	3500
8469	1032	3047	\N	3500
8470	1032	3048	\N	3500
8471	1037	5268	\N	3500
8472	1037	5269	\N	5000
8473	1036	5263	\N	35180
8474	1036	5260	\N	5000
8475	1036	5261	\N	3500
8476	902	5496	\N	2800
8477	874	5364	\N	21000
8478	915	5408	\N	5000
8479	915	5409	\N	5000
8480	915	5410	\N	5000
8481	915	5411	\N	3500
8482	915	5412	\N	2000
8483	914	5413	\N	3500
8484	914	5414	\N	3500
8485	914	5415	\N	3500
8486	914	5416	\N	3500
8487	914	5417	\N	5000
8488	914	5418	\N	3500
8489	874	5363	\N	20000
8490	908	5434	\N	2800
8491	907	5433	\N	2800
8492	900	5507	\N	2800
8493	1037	5510	\N	3500
8494	900	5511	\N	2800
8495	900	5513	\N	2800
8496	899	5475	\N	26250
8497	899	5477	\N	2800
8498	900	5514	\N	2800
8499	899	5470	\N	2800
8500	899	5472	\N	2800
8501	899	5474	\N	2800
8502	902	5491	\N	2800
8503	902	5492	\N	2800
8504	902	5493	\N	2800
8505	902	5495	\N	2800
8506	1050	5358	0	2010
8507	1050	5359	0	1020
8508	1050	5360	0	1160
8509	1050	5357	0	7455
8510	1050	5361	0	5790
8511	1050	5356	0	2310
8512	902	5498	\N	1800
8513	902	5499	\N	1800
8514	901	5500	\N	2800
8515	901	5501	\N	2800
8516	903	5502	\N	2800
8517	904	5504	\N	2800
8518	904	5505	\N	14500
8519	905	5506	\N	2800
8520	939	5518	\N	97570
8521	377	5575	\N	36233
8522	378	5579	\N	42501
8523	379	5580	\N	20401
8524	230	7746	\N	2765
8525	380	5581	\N	113684
8526	262	7327	\N	1000
8527	262	7328	\N	4000
8528	912	7420	\N	2000
8529	330	7384	\N	11750
8530	330	7385	\N	30000
8531	330	7392	\N	21725
8532	330	7401	\N	21150
8533	330	7402	\N	165000
8534	911	7404	\N	2800
8535	911	7406	\N	2800
8536	911	7405	\N	2800
8537	911	7407	\N	2800
8538	911	7409	\N	2800
8539	911	7410	\N	1800
8540	911	7411	\N	1800
8541	911	7412	\N	1800
8542	912	7414	\N	3500
8543	912	7415	\N	5000
8544	912	7413	\N	5000
8545	912	7416	\N	3500
8546	912	7417	\N	5000
8547	912	7418	\N	5000
8548	912	7419	\N	3500
8549	1049	7373	\N	5400
8550	880	5515	\N	341160
8551	362	7596	\N	42792
8552	376	7597	\N	52000
8553	414	7672	\N	3000
8554	542	118	\N	\N
8555	232	7747	\N	4060
8556	265	7719	\N	4000
8557	410	7722	\N	1000
8558	312	7729	\N	1400
8559	315	7741	\N	46500
8560	235	7770	\N	2000
8561	235	7771	\N	350
8562	237	8164	\N	1100
8563	238	8165	\N	31.2
8564	239	8166	\N	5070
8565	307	8170	\N	33500
8566	307	8172	\N	2500
8567	307	8173	\N	5500
8568	307	8174	\N	1250
8569	307	8171	\N	3000
8570	225	8175	\N	16363
8571	521	8215	\N	500
8572	521	8223	\N	500
8573	518	8229	\N	8
8574	516	8237	\N	20
8575	516	8239	\N	31.666
8576	516	8240	\N	85
8577	516	8241	\N	96.25
8578	516	8242	\N	265
8579	516	8243	\N	60
8580	516	8244	\N	96.25
8581	516	8238	\N	177.78
8582	245	8245	\N	23.125
8583	245	8246	\N	21.5
8584	151	8255	\N	1500
8585	244	8270	\N	5000
8586	330	7400	\N	184475
8587	151	8254	\N	2500
8588	1036	7625	\N	35180
8589	215	8504	\N	21957
8590	172	8506	\N	1500
8591	452	8602	\N	10000
8592	452	8603	\N	36000
8593	447	8643	\N	5600
8594	282	8644	\N	749459
8595	1073	8609	\N	5400
8596	1069	8559	\N	274100
8597	101	8645	\N	962162
8598	1089	8744	\N	206
8599	1088	8739	\N	5674
8600	1089	8746	\N	2250
8601	1089	8740	\N	63
8602	1089	8742	\N	763
8603	1100	8807	\N	21000
8604	1112	8981	\N	5000
8605	1118	8996	\N	5500
8606	1149	9329	\N	6250
8607	1149	9330	\N	21750
8608	1149	9332	\N	625
8609	1149	9334	\N	122694
8610	1164	9444	\N	7000
8611	1162	9431	\N	5500
8612	1164	9443	\N	2150
8613	1133	9307	\N	5000
8614	1170	9507	\N	6000
8615	1170	9508	\N	1700
8616	1170	9509	\N	10000
8617	1170	9510	\N	1300
8618	1170	9511	\N	2500
8619	1170	9512	\N	1800
8620	1170	9514	\N	1000
8621	1170	9515	\N	400
8622	1170	9516	\N	1200
8623	244	8269	\N	12000
8624	244	8271	\N	2500
8625	359	8278	\N	79100
8626	222	8419	\N	2000
8627	221	8432	\N	2000
8628	220	8439	\N	5025
8629	264	8476	\N	8000
8630	264	8477	\N	16496
8631	229	8501	\N	5325
8632	228	8502	\N	2765
8633	332	8639	\N	315605
8634	227	8640	\N	1750
8635	513	8641	\N	12080
8636	522	8642	\N	49500
8637	1138	9187	\N	2700
8638	1138	9188	\N	650
8639	1138	9189	\N	1500
8640	1133	9299	\N	20000
8641	1133	9300	\N	5000
8642	1133	9301	\N	10000
8643	1133	9302	\N	15000
8644	1133	9303	\N	30000
8645	1133	9304	\N	15000
8646	1133	9305	\N	30000
8647	1133	9306	\N	30000
8648	1133	9308	\N	25000
8649	1133	9309	\N	70000
8650	1170	9505	\N	8000
8651	1170	9506	\N	3000
8652	1174	9585	\N	3500
8653	1183	9608	\N	1590
8654	1183	9612	\N	3070
8655	1183	9613	\N	5370
8656	1183	9614	\N	1990
8657	1183	9615	\N	2270
8658	1183	9616	\N	1750
8659	1182	9607	\N	5500
8660	1208	9875	\N	3500
8661	1208	9876	\N	3500
8662	1214	9885	\N	5500
8663	1225	9947	\N	3500
8664	1225	9948	\N	5000
8665	1219	9921	\N	5000
8666	1219	9922	\N	5000
8667	1219	9924	\N	3500
8668	1219	9925	\N	3500
8669	1255	10242	\N	5000
8670	1255	10243	\N	3500
8671	1170	9535	\N	175
8672	1170	9536	\N	3000
8673	1170	9500	\N	13000
8674	1170	9543	\N	1500
8675	1170	9519	\N	3000
8676	1170	9520	\N	1000
8677	1170	9521	\N	1800
8678	1170	9523	\N	4500
8679	1170	9525	\N	2500
8680	1170	9526	\N	3000
8681	1170	9527	\N	1000
8682	1170	9528	\N	3500
8683	1170	9529	\N	4500
8684	1170	9530	\N	4800
8685	1170	9531	\N	1500
8686	1170	9532	\N	2000
8687	1170	9533	\N	8000
8688	1170	9534	\N	600
8689	1170	9538	\N	800
8690	1170	9539	\N	650
8691	1170	9540	\N	25000
8692	1170	9541	\N	6500
8693	1170	9542	\N	4500
8694	1170	9537	\N	400
8695	1193	9729	\N	24308
8696	1192	9726	\N	90.9
8697	1192	9727	\N	14993
8698	1187	9715	\N	28012
8699	1188	9725	\N	3714
8700	1186	9714	\N	5151
8701	725	1637	\N	15300
8702	725	1638	\N	22040
8703	1243	10154	\N	4500
8704	1170	9522	\N	400
8705	1266	10324	\N	858
8706	1274	10376	\N	5500
8707	1284	10445	\N	1560
8708	1284	10442	\N	12250
8709	1284	10444	\N	20900
8710	1284	10446	\N	310
8711	1284	10447	\N	1600
8712	1284	10448	\N	358
8713	1284	10449	\N	995
8714	1284	10450	\N	158
8715	1284	10451	\N	2825
8716	1284	10452	\N	330
8717	1284	10453	\N	16750
8718	1284	10454	\N	1750
8719	1284	10455	\N	6750
8720	1284	10456	\N	8550
8721	1284	10457	\N	40319
8722	1284	10458	\N	6800
8723	1284	10459	\N	38000
8724	1284	10460	\N	4908
8725	1284	10461	\N	11500
8726	1284	10462	\N	5050
8727	1284	10463	\N	20
8728	1284	10464	\N	20
8729	1284	10465	\N	20
8730	1284	10466	\N	10
8731	1284	10467	\N	15
8732	1284	10468	\N	7
8733	1284	10469	\N	29
8734	1284	10470	\N	5
8735	1284	10471	\N	9
8736	1284	10472	\N	3529
8737	1284	10473	\N	300
8738	1284	10474	\N	1361
8739	1284	10475	\N	3076
8740	1284	10441	\N	52994
8741	1284	10485	\N	1113
8742	1284	10486	\N	17250
8743	1284	10487	\N	3625
8744	1284	10488	\N	11750
8745	1287	10514	\N	48237
8746	1284	10476	\N	3271
8747	1284	10477	\N	5619
8748	1284	10478	\N	6625
8749	1284	10479	\N	6481
8750	1284	10480	\N	2832
8751	1284	10481	\N	2438
8752	1284	10482	\N	333
8753	1284	10483	\N	4375
8754	1285	10497	\N	52500
8755	1284	10484	\N	6313
8756	1284	10489	\N	9000
8757	1287	10510	\N	408000
8758	1287	10511	\N	67306
8759	1287	10513	\N	45306
8760	1284	10490	\N	768
8761	1284	10491	\N	2700
8762	1284	10492	\N	2250
8763	1284	10493	\N	2588
8764	1284	10494	\N	10500
8765	1284	10495	\N	5500
8766	1284	10496	\N	48750
8767	1294	10588	\N	12383
8768	1307	10648	\N	3500
8769	1307	10650	\N	5000
8770	1307	10651	\N	58000
8771	1307	10649	\N	58000
8772	1362	11157	\N	31250
8773	1307	10647	\N	58000
8774	1307	10637	\N	3500
8775	1314	10723	\N	5500
8776	1335	10803	\N	78313
8777	1268	10338	\N	806949
8778	1356	11064	\N	1725
8779	1359	11119	\N	3048
8780	1193	9728	\N	90.9
8781	1353	11056	\N	69766
8782	1353	11054	\N	5000
8783	1360	11138	\N	303
8784	1359	11120	\N	748
8785	1360	11135	\N	10504
8786	1357	11110	\N	8700
8787	1357	11111	\N	5800
8788	1357	11112	\N	8700
8789	1359	11121	\N	50
8790	1359	11122	\N	13800
8791	1359	11123	\N	4028
8792	1359	11125	\N	575
8793	1359	11126	\N	58760
8794	1359	11127	\N	28787
8795	1359	11128	\N	1725
8796	1359	11129	\N	575
8797	1358	11133	\N	5500
8798	1294	10586	\N	32.5
8799	1188	9724	\N	50
8800	1294	10589	\N	5246
8801	1300	10602	\N	9200
8802	1295	10590	\N	3500
8803	1295	10592	\N	11214
8804	1299	10601	\N	11000
8805	1302	10615	\N	260
8806	1298	10598	\N	1726
8807	1297	10596	\N	18620
8808	1296	10610	\N	2299
8809	1296	10593	\N	28804
8810	1296	10594	\N	12069
8811	1348	11033	\N	8000
8812	1348	11034	\N	10000
8813	1348	11035	\N	10000
8814	1356	11066	\N	115
8815	1356	11067	\N	69
8816	1356	11068	\N	92
8817	1356	11069	\N	92
8818	1355	11062	\N	4600
8819	1355	11063	\N	97463
8820	1356	11070	\N	58
8821	1356	11071	\N	81
8822	1356	11072	\N	92
8823	1356	11073	\N	115
8824	1360	11154	\N	2625
8825	1360	11153	\N	5175
8826	1360	11152	\N	5750
8827	1360	11151	\N	9700
8828	1360	11150	\N	575
8829	1360	11149	\N	9700
8830	1360	11142	\N	223
8831	1359	11131	\N	1725
8832	1359	11130	\N	2875
8833	1360	11156	\N	5460
8834	1360	11155	\N	8510
8835	1362	11158	\N	15531
8836	1363	11159	\N	625500
8837	1363	11160	\N	58500
8838	1363	11161	\N	14700
8839	1363	11162	\N	9000
8840	1360	11141	\N	890
8841	1360	11140	\N	1580
8842	1360	11139	\N	1680
8843	1360	11137	\N	11453
8844	1356	11074	\N	518
8845	1356	11075	\N	625
8846	1356	11076	\N	21852
8847	1356	11077	\N	21854
8848	1360	11147	\N	20550
8849	1360	11148	\N	138
8850	1360	11146	\N	11125
8851	1360	11145	\N	890
8852	1360	11144	\N	280
8853	1360	11143	\N	338
8854	1360	11136	\N	49153
8855	1360	11134	\N	12528
8856	1359	11132	\N	2300
8857	1356	11078	\N	5175
8858	1356	11079	\N	4600
8859	1356	11080	\N	6900
8860	1356	11081	\N	2070
8861	1356	11082	\N	9488
8862	1356	11083	\N	69000
8863	1356	11084	\N	3243
8864	1356	11085	\N	9405
8865	1356	11086	\N	2300
8866	1356	11087	\N	10350
8867	1356	11088	\N	18975
8868	1356	11089	\N	2875
8869	1356	11090	\N	1725
8870	1356	11092	\N	748
8871	1356	11093	\N	30
8872	1356	11094	\N	325
8873	1356	11095	\N	2162
8874	1356	11096	\N	978
8875	1356	11097	\N	4658
8876	1356	11098	\N	4025
8877	1356	11099	\N	4025
8878	1356	11100	\N	5980
8879	1356	11101	\N	3680
8880	1356	11102	\N	8625
8881	1356	11103	\N	7475
8882	1356	11104	\N	5175
8883	1356	11105	\N	4600
8884	1356	11106	\N	16675
8885	1356	11107	\N	9200
8886	1356	11108	\N	4600
8887	1356	11109	\N	5405
8888	1365	11195	\N	8000
8889	1365	11196	\N	5500
8890	1365	11198	\N	6500
8891	1365	11201	\N	18000
8892	1365	11202	\N	4000
8893	1365	11203	\N	4500
8894	1369	11215	\N	1512000
8895	1369	11216	\N	74399
8896	1367	11210	\N	1200
8897	1367	11213	\N	9000
8898	1367	11214	\N	55000
8899	1365	11194	\N	260000
8900	1367	11205	\N	3000
8901	1367	11206	\N	3300
8902	1367	11207	\N	3800
8903	1367	11208	\N	600
8904	1367	11209	\N	400
8905	1364	11163	\N	775
8906	1364	11164	\N	1320
8907	1364	11165	\N	281
8908	1364	11166	\N	18000
8909	1364	11167	\N	2340
8910	1374	11242	\N	390000
8911	1368	11220	\N	5443
8912	1368	11221	\N	45555
8913	1368	11222	\N	1524
8914	1368	11223	\N	3172
8915	1368	11224	\N	2418
8916	1368	11225	\N	5233
8917	1368	11226	\N	30172
8918	1368	11227	\N	482
8919	1368	11228	\N	276
8920	1368	11229	\N	1938
8921	1368	11230	\N	2243
8922	1368	11231	\N	138
8923	1368	11232	\N	489
8924	1368	11233	\N	1380
8925	1368	11234	\N	2214
8926	1368	11235	\N	25563
8927	1368	11236	\N	173
8928	1368	11237	\N	2351
8929	1368	11238	\N	3706
8930	1368	11239	\N	958
8931	1364	11168	\N	62314
8932	1364	11171	\N	2800
8933	1364	11174	\N	2025
8934	1364	11175	\N	368409
8935	1364	11176	\N	3048
8936	1364	11178	\N	92
8937	1364	11179	\N	69
8938	1364	11180	\N	115
8939	1373	11241	\N	400000
8940	1364	11181	\N	690
8941	1364	11182	\N	575
8942	1364	11184	\N	460
8943	1364	11186	\N	345
8944	1364	11187	\N	690
8945	1369	11217	\N	93950
8946	1369	11218	\N	70500
8947	1369	11219	\N	59445
8948	1375	11245	\N	25000
8949	1375	11246	\N	54000
8950	1371	11240	\N	86017
8951	1364	11188	\N	575
8952	1364	11189	\N	460
8953	1364	11190	\N	345
8954	986	2604	\N	5378000
8955	986	2837	\N	95000
8956	986	3052	\N	600000
8957	818	2086	\N	66500
8958	818	2087	\N	225000
8959	818	2088	\N	72500
8960	806	2071	\N	10900
8961	806	2073	\N	1200
8962	806	2074	\N	1000
8963	806	2076	\N	2400
8964	806	2077	\N	450
8965	806	2078	\N	650
8966	806	2079	\N	1000
8967	798	2055	\N	115000
8968	798	2056	\N	20000
8969	798	2057	\N	79500
8970	798	2058	\N	65500
8971	776	1860	\N	16058100
8972	776	1861	\N	5652500
8973	717	1528	\N	11277
8974	717	1529	\N	6956
8975	1377	11257	\N	18500
8976	1377	11258	\N	14500
8977	1377	11259	\N	21800
8978	1377	11260	\N	138000
8979	1377	11261	\N	5800
8980	1376	11256	\N	175000
8981	1380	11281	\N	5500
8982	1379	11273	\N	225000
8983	1379	11274	\N	265000
8984	1379	11275	\N	1350000
8985	1379	11276	\N	154000
8986	1379	11277	\N	23500
8987	1379	11278	\N	12500
8988	1368	11250	\N	279671
8989	1379	11279	\N	1500
8990	1379	11280	\N	87500
8991	1368	11251	\N	45000
8992	1382	11283	\N	2750
8993	1170	9502	\N	1500
8994	1170	9517	\N	3000
8995	1378	11262	\N	1500
8996	1378	11263	\N	1900
8997	1378	11264	\N	3500
8998	1378	11265	\N	3500
8999	1378	11266	\N	1890
9000	1366	11252	\N	35000
9001	1366	11253	\N	50000
9002	1390	11503	\N	3500
9003	1390	11504	\N	350
9004	1390	11505	\N	7
9005	1390	11506	\N	450
9006	1366	11255	\N	37000
9007	1378	11267	\N	22
9008	1378	11268	\N	11800
9009	1378	11269	\N	620
9010	1378	11271	\N	380
9011	1378	11272	\N	35000
9012	1387	11325	\N	8625
9013	1387	11326	\N	2300
9014	1387	11327	\N	345
9015	1387	11328	\N	1438
9016	1387	11329	\N	345
9017	1387	11330	\N	2875
9018	1387	11331	\N	173
9019	1387	11332	\N	46
9020	1387	11336	\N	29
9021	1387	11337	\N	46
9022	1385	11300	\N	3500000
9023	1382	11282	\N	2680
9024	1382	11284	\N	950
9025	1382	11285	\N	9500
9026	1382	11286	\N	13900
9027	1382	11287	\N	2550
9028	1382	11288	\N	1300
9029	1387	11339	\N	518
9030	1384	11292	\N	99000
9031	1384	11299	\N	18000
9032	1383	11289	\N	850
9033	1383	11290	\N	750
9034	1383	11291	\N	8000
9035	1387	11338	\N	230
9036	1387	11302	\N	13225
9037	1387	11303	\N	2875
9038	1387	11304	\N	115
9039	1387	11305	\N	1150
9040	1387	11307	\N	35
9041	1387	11308	\N	35
9042	1387	11309	\N	35
9043	1387	11310	\N	29
9044	1387	11311	\N	46
9045	1387	11312	\N	46
9046	1387	11314	\N	46
9047	1387	11315	\N	46
9048	1387	11316	\N	23
9049	1387	11317	\N	29
9050	1387	11318	\N	29
9051	1387	11319	\N	2300
9052	1387	11320	\N	46
9053	1387	11340	\N	9200
9054	1387	11341	\N	9200
9055	1387	11342	\N	690
9056	1387	11343	\N	123152
9057	1387	11344	\N	16860
9058	1387	11345	\N	17665
9059	1387	11346	\N	18470
9060	1387	11347	\N	19735
9061	1389	11366	\N	31
9062	1387	11348	\N	23876
9063	1387	11321	\N	4025
9064	1387	11322	\N	575
9065	1387	11323	\N	460
9066	1387	11324	\N	12650
9067	1388	11349	\N	2795
9068	1388	11350	\N	2250
9069	1388	11351	\N	2650
9070	1388	11352	\N	235000
9071	1388	11353	\N	125000
9072	1389	11354	\N	34373
9073	1389	11355	\N	37889
9074	1389	11356	\N	41844
9075	1389	11357	\N	1650
9076	1389	11362	\N	3397
9077	1389	11363	\N	21
9078	1389	11364	\N	21
9079	1389	11365	\N	21
9080	1396	11400	\N	260000
9081	1395	11397	\N	24000
9082	1395	11414	\N	11850
9083	1391	11386	\N	17000
9084	1395	11415	\N	5600
9085	1392	11401	\N	2243
9086	1392	11402	\N	2482
9087	1392	11403	\N	7475
9088	1273	10374	\N	2265625
9089	1273	10375	\N	5115890
9090	1389	11358	\N	51651
9091	1389	11359	\N	100423
9092	1393	11387	\N	4500
9093	1393	11388	\N	25000
9094	1398	11406	\N	113600
9095	1395	11418	\N	11500
9096	1398	11410	\N	222
9097	1389	11360	\N	4525
9098	1389	11361	\N	7986
9099	1389	11367	\N	10
9100	1389	11368	\N	11
9101	1389	11369	\N	12
9102	1389	11370	\N	17
9103	1389	11371	\N	14
9104	1389	11372	\N	5774
9105	1395	11417	\N	5150
9106	1389	11373	\N	5750
9107	1389	11374	\N	3565
9108	1389	11375	\N	1035
9109	1389	11376	\N	3405
9110	1389	11377	\N	289
9111	1389	11378	\N	2730
9112	1389	11379	\N	5460
9113	1398	11411	\N	58
9114	1394	11392	\N	5800
9115	1398	11412	\N	4800
9116	1399	11421	\N	548000
9117	1398	11413	\N	5700
9118	1394	11393	\N	8700
9119	1394	11394	\N	5800
9120	1394	11395	\N	8700
9121	1394	11396	\N	90000
9122	1393	11389	\N	18000
9123	1393	11391	\N	30000
9124	1400	11424	\N	6500
9125	1400	11425	\N	47500
9126	1392	11404	\N	5196
9127	1392	11405	\N	3400
9128	1395	11420	\N	7800
9129	1401	11434	\N	19000
9130	1401	11435	\N	11500
9131	1401	11437	\N	14270
9132	1401	11433	\N	750
9133	1401	11438	\N	4750
9134	1401	11439	\N	4200
9135	1401	11441	\N	12000
9136	1401	11442	\N	7700
9137	1402	11449	\N	35
9138	1402	11450	\N	29
9139	1402	11451	\N	23
9140	1402	11452	\N	12
9141	1401	11443	\N	2200
9142	1400	11422	\N	2100
9143	1400	11423	\N	8350
9144	1400	11426	\N	34780
9145	1400	11427	\N	22000
9146	1400	11428	\N	17000
9147	1400	11429	\N	13200
9148	1401	11430	\N	26450
9149	1401	11431	\N	11000
9150	1401	11432	\N	5200
9151	1402	11444	\N	288
9152	1402	11446	\N	518
9153	1402	11447	\N	288
9154	1402	11448	\N	46
9155	1402	11453	\N	12
9156	1402	11454	\N	58
9157	1402	11455	\N	12
9158	1402	11456	\N	345
9159	1402	11457	\N	173
9160	1402	11458	\N	173
9161	1405	11510	\N	5500
9162	1390	11507	\N	350
9163	1390	11508	\N	230
9164	1405	11511	\N	4350
9165	1390	11494	\N	345
9166	1390	11495	\N	1580
9167	1390	11496	\N	250
9168	1390	11497	\N	3250
9169	1390	11498	\N	1550
9170	1390	11499	\N	8500
9171	1390	11500	\N	160
9172	1390	11501	\N	240
9173	1390	11509	\N	10000
9174	1390	11502	\N	5500
9175	1402	11459	\N	2225
9176	1402	11460	\N	23
9177	1402	11461	\N	48700
9178	1402	11462	\N	54030
9179	1402	11463	\N	63555
9180	1405	11512	\N	4200
9181	1404	11480	\N	24800
9182	1405	11513	\N	4200
9183	1405	11514	\N	1460
9184	1405	11515	\N	450
9185	1405	11516	\N	230
9186	1404	11481	\N	5400
9187	1404	11482	\N	9500
9188	1404	11483	\N	4300
9189	1404	11484	\N	600
9190	1404	11485	\N	430
9191	1404	11486	\N	2200
9192	1404	11487	\N	2500
9193	1407	11536	\N	3500
9194	1408	11537	\N	3500
9195	1408	11538	\N	3500
9196	1402	11464	\N	68342
9197	1402	11465	\N	1650
9198	1412	11578	\N	305620
9199	1412	11579	\N	12449
9200	1409	11539	\N	17250
9201	1409	11540	\N	17250
9202	1409	11541	\N	6900
9203	1409	11542	\N	5175
9204	1409	11543	\N	4888
9205	1402	11466	\N	4180
9206	1402	11467	\N	6825
9207	1402	11468	\N	6350
9208	1402	11469	\N	6520
9209	1402	11470	\N	72500
9210	1402	11471	\N	5700
9211	1402	11472	\N	70625
9212	1402	11473	\N	1495
9213	1402	11474	\N	2300
9214	1402	11475	\N	4025
9215	1412	11580	\N	19198
9216	1412	11581	\N	18853
9217	1412	11582	\N	10058
9218	1412	11583	\N	2730
9219	1402	11476	\N	460
9220	1402	11477	\N	345
9221	1411	11569	\N	295000
9222	1409	11544	\N	561492
9223	1409	11545	\N	74292
9224	1409	11546	\N	51042
9225	1411	11570	\N	1350000
9226	1406	11517	\N	5500
9227	1406	11518	\N	12000
9228	1406	11520	\N	13000
9229	1410	11556	\N	600
9230	1422	11669	\N	3450
9231	1410	11558	\N	600
9232	1410	11559	\N	600
9233	1410	11560	\N	600
9234	1422	11670	\N	22700
9235	1410	11561	\N	750
9236	1410	11562	\N	1400
9237	1422	11671	\N	207
9238	1422	11672	\N	58
9239	1410	11564	\N	840
9240	1422	11673	\N	3450
9241	1422	11674	\N	1380
9242	1411	11572	\N	1500
9243	1411	11573	\N	265000
9244	1411	11574	\N	87500
9245	1411	11575	\N	12500
9246	1411	11576	\N	23500
9247	1411	11577	\N	225000
9248	1409	11547	\N	74292
9249	1409	11548	\N	47062
9250	1422	11675	\N	1012
9251	1413	11591	\N	180000
9252	1413	11592	\N	110000
9253	1413	11593	\N	85000
9254	1413	11594	\N	26500
9255	1391	11585	\N	630
9256	1413	11595	\N	20500
9257	1413	11596	\N	3700
9258	1413	11597	\N	12000
9259	1413	11598	\N	850
9260	1391	11586	\N	1000
9261	1391	11587	\N	15000
9262	1391	11589	\N	77000
9263	1391	11590	\N	168000
9264	1413	11599	\N	12
9265	1413	11600	\N	800
9266	1414	11611	\N	192304
9267	1415	11622	\N	75
9268	1410	11565	\N	720
9269	1410	11566	\N	470
9270	1410	11567	\N	1260
9271	1410	11568	\N	950
9272	1415	11623	\N	72
9273	1415	11624	\N	519
9274	1415	11625	\N	127
9275	1415	11626	\N	690
9276	1415	11627	\N	112
9277	1415	11628	\N	748
9278	1415	11629	\N	345
9279	1415	11630	\N	772
9280	1415	11631	\N	2875
9281	1415	11632	\N	5175
9282	1413	11601	\N	3600
9283	1413	11602	\N	580
9284	1414	11612	\N	176998
9285	1412	11584	\N	9583
9286	1415	11621	\N	79
9287	1414	11613	\N	12870
9288	1414	11614	\N	20000
9289	1414	11615	\N	75000
9290	1413	11603	\N	680
9291	1413	11604	\N	55
9292	1413	11605	\N	12500
9293	1413	11606	\N	75500
9294	1414	11616	\N	75000
9295	1413	11607	\N	51200
9296	1413	11608	\N	44500
9297	1415	11617	\N	3238
9298	1415	11618	\N	5556
9299	1415	11619	\N	8045
9300	1415	11620	\N	9745
9301	1415	11633	\N	5750
9302	1415	11634	\N	8537
9303	1415	11635	\N	294
9304	1415	11636	\N	53340
9305	1415	11637	\N	24571
9306	1422	11646	\N	1035
9307	1246	10176	\N	0
9308	1422	11647	\N	2070
9309	1422	11648	\N	69
9310	1422	11649	\N	2300
9311	1409	11549	\N	62432
9312	1409	11550	\N	1726
9313	1409	11551	\N	690
9314	1409	11552	\N	315
9315	1402	11478	\N	58
9316	1402	11479	\N	18315
9317	1416	11638	\N	5000
9318	143	7734	\N	5500
9319	143	7735	\N	1500
9320	231	7723	\N	7500
9321	88	8176	\N	32000
9322	312	7730	\N	600
9323	176	7632	\N	1025
9324	146	7648	\N	6500
9325	518	8231	\N	5000
9326	518	8232	\N	700
9327	146	7651	\N	650
9328	146	7636	\N	500
9329	146	7639	\N	450
9330	266	8277	\N	49900
9331	517	8235	\N	42800
9332	89	8168	\N	26500
9333	195	7725	\N	24950
9334	1139	9190	\N	4500
9335	146	7643	\N	400
9336	90	7724	\N	6500
9337	146	7634	\N	500
9338	171	8510	\N	49700
9339	570	357	\N	\N
9340	176	7631	\N	11630
9341	520	8203	\N	7500
9342	1141	9194	\N	15520
9343	1243	10153	\N	7900
9344	1252	10220	\N	3675
9345	1301	10609	\N	44029
9346	1298	10597	\N	4851
9347	235	7772	\N	200
9348	1422	11652	\N	1150
9349	1422	11653	\N	3450
9350	1422	11655	\N	46
9351	1246	10180	\N	0
9352	1422	11656	\N	8050
9353	1422	11657	\N	1150
9354	1302	10611	\N	1351.72
9355	1331	10778	\N	65000
9356	1331	10774	\N	104.82
9357	1140	9191	\N	500000
9358	746	1808	\N	666.667
9359	747	2035	\N	3000
9360	263	7671	\N	20000
9361	1244	10155	\N	203.75
9362	1417	11639	\N	46250
9363	1417	11640	\N	55250
9364	1417	11641	\N	97000
9365	1264	10284	\N	87486
9366	1264	10285	\N	41952
9367	1264	10286	\N	30213
9368	1264	10288	\N	37484
9369	1264	10290	\N	24532
9370	1264	10291	\N	41932
9371	1264	10293	\N	29208
9372	1264	10294	\N	23197
9373	1264	10295	\N	67799
9374	1264	10296	\N	0
9375	1264	10297	\N	32770
9376	1264	10298	\N	30230
9377	1264	10299	\N	31720
9378	1264	10300	\N	19367
9379	1264	10301	\N	56060
9380	1264	10302	\N	84790
9381	1264	10303	\N	56050
9382	492	6989	\N	500
9383	480	6972	\N	400
9384	1419	11643	\N	5500
9385	1422	11645	\N	518
9386	1420	11644	\N	8335
9387	1418	11642	\N	175000
9388	1425	11690	\N	1650
9389	1425	11691	\N	1425
9390	1427	11698	\N	965000
9391	1427	11700	\N	895000
9392	1424	11687	\N	670500
9393	1424	11688	\N	270000
9394	1424	11689	\N	410000
9395	1428	11701	\N	4900
9396	1428	11702	\N	52000
9397	1428	11703	\N	2000
9398	1428	11704	\N	1800
9399	1428	11708	\N	580
9400	1428	11710	\N	500
9401	1290	10557	\N	199865
9402	1290	10558	\N	6200
9403	1290	10559	\N	7750
9404	1290	10560	\N	5500
9405	1290	10562	\N	10125
9406	1290	10563	\N	11500
9407	1290	10564	\N	34750
9408	1290	10565	\N	8750
9409	1422	11663	\N	2300
9410	1422	11664	\N	3450
9411	1422	11665	\N	2674
9412	1422	11666	\N	690
9413	1422	11667	\N	460
9414	1422	11668	\N	690
9415	1422	11676	\N	8970
9416	1426	11699	\N	690
9417	1422	11658	\N	2300
9418	1422	11660	\N	1380
9419	1422	11661	\N	920
9420	1422	11662	\N	345
9421	1426	11692	\N	17574
9422	1426	11693	\N	1438
9423	1426	11694	\N	1438
9424	1426	11695	\N	863
9425	1426	11696	\N	432
9426	1422	11677	\N	7475
9427	1422	11678	\N	78717
9428	1422	11679	\N	24700
9429	1422	11680	\N	14234
9430	1422	11681	\N	14234
9431	1422	11682	\N	23650
9432	1431	11734	\N	36599
9433	1431	11735	\N	4715
9434	1431	11736	\N	29
9435	1430	11730	\N	169750
9436	1428	11724	\N	15420
9437	1422	11683	\N	79279
9438	1422	11684	\N	5750
9439	1422	11685	\N	2625
9440	1422	11686	\N	294
9441	1431	11731	\N	6980
9442	1431	11732	\N	863
9443	1431	11733	\N	115
9444	1431	11737	\N	29
9445	1431	11738	\N	29
9446	1431	11739	\N	4375
9447	1429	11725	\N	225000
9448	1431	11740	\N	1725
9449	1431	11741	\N	2128
9450	1431	11742	\N	2875
9451	1429	11726	\N	172850
9452	1428	11709	\N	3500
9453	1431	11743	\N	97138
9454	1431	11744	\N	53524
9455	1431	11745	\N	653
9456	1431	11746	\N	690
9457	1431	11747	\N	2300
9458	1428	11711	\N	70
9459	1432	11751	\N	798000
9460	1432	11752	\N	197500
9461	1430	11727	\N	85000
9462	1430	11728	\N	8500
9463	1430	11729	\N	68000
9464	1428	11712	\N	1650
9465	1428	11713	\N	1033.333
9466	1428	11714	\N	12
9467	1428	11715	\N	3000
9468	1428	11716	\N	150
9469	1428	11717	\N	1800
9470	1428	11718	\N	189
9471	1428	11719	\N	1350
9472	1428	11721	\N	240
9473	1428	11722	\N	80
9474	1428	11723	\N	250
9475	1433	11749	\N	223711
9476	1413	11796	\N	1250
9477	1413	11609	\N	1250
9478	1413	11610	\N	135000
9479	1431	11753	\N	207
9480	1431	11754	\N	2300
9481	1431	11755	\N	41
9482	1431	11756	\N	46
9483	1431	11758	\N	32789
9484	1431	11759	\N	173
9485	1431	11760	\N	230
9486	1431	11762	\N	518
9487	1431	11763	\N	518
9488	1431	11764	\N	4025
9489	1431	11765	\N	403
9490	1435	11767	\N	8780
9491	1435	11768	\N	51361
9492	1435	11769	\N	9625
9493	1435	11770	\N	995
9494	1435	11771	\N	2875
9495	1435	11772	\N	575
9496	1435	11777	\N	403
9497	1435	11778	\N	10925
9498	1435	11779	\N	2678
9499	1435	11773	\N	5750
9500	1436	11782	\N	547.87
9501	1436	11783	\N	2911.85
9502	1436	11784	\N	157.92
9503	1435	11774	\N	345
9504	1436	11785	\N	232.32
9505	1436	11786	\N	1979.67
9506	1436	11787	\N	26370.14
9507	1436	11788	\N	179.25
9508	1436	11789	\N	386.49
9509	1436	11790	\N	386.49
9510	1436	11791	\N	474.57
9511	1436	11792	\N	377.77
9512	1436	11793	\N	2314.63
9513	1431	11766	\N	1725
9514	1435	11775	\N	1725
9515	1435	11776	\N	1500
9516	1435	11780	\N	1050
9517	1435	11781	\N	3150
9518	1439	11798	\N	5500
9519	1436	11794	\N	322.73
9520	1436	11795	\N	609.88
9521	1439	11799	\N	13000
9522	1445	11834	\N	954830
9523	1445	11835	\N	67375
9524	1445	11836	\N	176565
9525	1443	11816	\N	3450
9526	1445	11837	\N	112095
9527	1445	11838	\N	16265
9528	1437	11797	\N	198000
9529	1446	11839	\N	200000
9530	1446	11840	\N	100000
9531	1446	11841	\N	96000
9532	1444	11832	\N	310000
9533	1443	11817	\N	27000
9534	1444	11833	\N	85000
9535	1443	11813	\N	690
9536	1443	11818	\N	21000
9537	1443	11819	\N	600
9538	1440	11800	\N	82750
9539	1440	11801	\N	260000
9540	1440	11802	\N	9250
9541	1443	11820	\N	1438
9542	1443	11821	\N	5100
9543	1443	11822	\N	6000
9544	1443	11823	\N	1100
9545	1443	11824	\N	7000
9546	1443	11825	\N	22000
9547	1443	11826	\N	30260
9548	1441	11803	\N	12850
9549	1441	11804	\N	87500
9550	1441	11805	\N	260000
9551	1441	11806	\N	1500
9552	1442	11807	\N	82750
9553	1442	11808	\N	55000
9554	1443	11814	\N	230
9555	1443	11815	\N	4255
9556	1442	11809	\N	260000
9557	1443	11827	\N	205
9558	1443	11847	\N	15640
9559	1443	11811	\N	326
9560	1443	11812	\N	9200
9561	1443	11828	\N	69
9562	1443	11829	\N	1552.5
9563	1443	11830	\N	700
9564	1443	11831	\N	27150
9565	1443	11842	\N	173
9566	1443	11843	\N	949
9567	1443	11844	\N	460
9568	1443	11845	\N	4600
9569	1443	11846	\N	3680
9570	1456	11872	\N	14800
9571	1456	11873	\N	13028
9572	1456	11874	\N	2650
9573	1455	11871	\N	61500
9574	1246	10179	\N	0
9575	1462	11899	\N	2055
9576	1452	11867	\N	72034
9577	1454	11868	\N	68500
9578	1451	11863	\N	0
9579	1450	11860	\N	225200
9580	1450	11861	\N	115000
9581	1454	11869	\N	1793
9582	1454	11870	\N	149100
9583	1447	11848	\N	225000
9584	1447	11849	\N	39500
9585	1450	11862	\N	56650
9586	1449	11859	\N	392750
9587	1447	11850	\N	92500
9588	1447	11851	\N	40200
9589	1448	11855	\N	1350000
9590	1448	11856	\N	225000
9591	1448	11857	\N	260000
9592	1448	11858	\N	87500
9593	1443	11810	\N	4370
9594	1458	11886	\N	\N
9595	1457	11878	\N	29000
9596	1456	11875	\N	21500
9597	1456	11876	\N	28800
9598	1456	11877	\N	7800
9599	1457	11879	\N	115000
9600	1457	11880	\N	34000
9601	1457	11884	\N	357
9602	1457	11881	\N	63
9603	1461	11889	\N	84576
9604	1457	11882	\N	69
9605	1457	11883	\N	288
9606	1457	11885	\N	345
9607	1462	11890	\N	3578
9608	1462	11891	\N	798
9609	1462	11892	\N	1181
9610	1462	11893	\N	1078
9611	1462	11894	\N	2340
9612	1462	11895	\N	2055
9613	1462	11896	\N	1308
9614	1462	11897	\N	359
9615	1462	11898	\N	23912
9616	1462	11900	\N	2400
9617	1462	11901	\N	326
9618	1462	11902	\N	326
9619	1462	11903	\N	326
9620	1462	11904	\N	326
9621	1462	11905	\N	326
9622	1462	11906	\N	68
9623	1462	11907	\N	395
9624	1462	11908	\N	165
9625	1462	11909	\N	1250
9626	1462	11910	\N	200
9627	1462	11911	\N	79
9628	1462	11912	\N	840
9629	1462	11913	\N	1135
9630	1462	11914	\N	786
9631	1462	11915	\N	7405
9632	1462	11917	\N	654
9633	1462	11918	\N	147166
9634	1462	11919	\N	255
9635	1462	11920	\N	27075
9636	1462	11921	\N	21400
9637	1462	11922	\N	25075
9638	1462	11924	\N	25325
9639	1462	11925	\N	39875
9640	1470	11950	\N	670000
9641	1470	11952	\N	85000
9642	1475	11961	\N	1880
9643	1476	11973	\N	69
9644	1477	11994	\N	1127
9645	1475	11962	\N	155
9646	1475	11963	\N	290
9647	1475	11964	\N	40
9648	1472	11958	\N	67250
9649	1475	11965	\N	21500
9650	1474	11960	\N	308
9651	1469	11939	\N	2222
9652	1466	11935	\N	280
9653	1469	11940	\N	630
9654	1467	11938	\N	5
9655	1469	11941	\N	7608.5
9656	1469	11942	\N	1948
9657	1462	11926	\N	4468
9658	1462	11927	\N	59340
9659	1462	11928	\N	37440
9660	1470	11953	\N	40000
9661	1469	11943	\N	131.25
9662	1476	11966	\N	1290
9663	1462	11929	\N	3720
9664	1462	11930	\N	3944
9665	1476	11967	\N	92
9666	1462	11931	\N	18250
9667	1476	11968	\N	361
9668	1476	11969	\N	345
9669	1476	11970	\N	345
9670	1473	11959	\N	280000
9671	1471	11954	\N	239000
9672	1471	11955	\N	38800
9673	1462	11932	\N	154950
9674	1463	11933	\N	5500
9675	1476	11972	\N	345
9676	1471	11956	\N	8500
9677	1470	11944	\N	68000
9678	1470	11945	\N	82000
9679	1470	11948	\N	258000
9680	1470	11949	\N	285000
9681	1472	11957	\N	327500
9682	1477	11992	\N	1438
9683	1477	11995	\N	2888
9684	1477	11996	\N	5985
9685	1477	11997	\N	21392
9686	1477	11998	\N	6750
9687	1477	11999	\N	3300
9688	1481	12007	\N	1975000
9689	1476	11977	\N	115
9690	1483	12011	\N	3170
9691	1482	12008	\N	922
9692	1483	12012	\N	97
9693	1483	12013	\N	1285
9694	1483	12014	\N	1216
9695	1483	12015	\N	395
9696	1482	12009	\N	25522
9697	1483	12016	\N	338
9698	1482	12010	\N	41407
9699	1477	12000	\N	284
9700	1477	12001	\N	4793
9701	1478	12005	\N	72034
9702	1477	12002	\N	5775
9703	1477	12003	\N	299
9704	1477	12004	\N	115
9705	1479	12006	\N	208620
9706	1483	12017	\N	280
9707	1476	11978	\N	98
9708	1483	12018	\N	963
9709	1483	12019	\N	25104
9710	1483	12020	\N	20626
9711	1476	11980	\N	1093
9712	1483	12021	\N	15800
9713	1483	12022	\N	6980
9714	1483	12023	\N	56625
9715	1483	12024	\N	18963
9716	1476	11981	\N	207
9717	1476	11982	\N	115
9718	1483	12027	\N	52850
9719	1476	11974	\N	138
9720	1476	11975	\N	667
9721	1476	11976	\N	161
9722	1476	11983	\N	104
9723	1476	11984	\N	173
9724	1476	11985	\N	748
9725	1476	11986	\N	299
9726	1483	12028	\N	1898
9727	1476	11987	\N	115
9728	1476	11988	\N	575
9729	1476	11989	\N	69
9730	1476	11990	\N	460
9731	1485	12056	\N	5775
9732	1485	12046	\N	30705
9733	1485	12047	\N	207
9734	1485	12048	\N	1725
9735	1485	12049	\N	920
9736	1485	12050	\N	2608
9737	1483	12025	\N	9502.5
9738	1476	11991	\N	782
9739	1485	12037	\N	345
9740	1485	12038	\N	3550
9741	1485	12039	\N	4700
9742	1485	12040	\N	3924
9743	1485	12041	\N	690
9744	1485	12043	\N	460
9745	1485	12044	\N	690
9746	1485	12045	\N	3450
9747	1485	12052	\N	1380
9748	1484	12030	\N	31913.5
9749	1484	12029	\N	78467.83
9750	1484	12034	\N	63590.88
9751	1484	12035	\N	0
9752	1484	12036	\N	1532.28
9753	1485	12051	\N	230602
9754	1485	12053	\N	2730
9755	1485	12054	\N	5985
9756	1485	12055	\N	8925
9757	1460	11887	\N	767000
9758	1460	11888	\N	150000
9759	1464	11934	\N	245000
\.


--
-- TOC entry 5310 (class 0 OID 0)
-- Dependencies: 398
-- Name: rfq_items_rfq_item_id_seq; Type: SEQUENCE SET; Schema: purnew; Owner: postgres
--

SELECT pg_catalog.setval('purnew.rfq_items_rfq_item_id_seq', 9759, true);


--
-- TOC entry 5146 (class 2606 OID 373618)
-- Name: items items_pkey; Type: CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.items
    ADD CONSTRAINT items_pkey PRIMARY KEY (item_id);


--
-- TOC entry 5150 (class 2606 OID 373635)
-- Name: rfq_items rfq_items_pkey; Type: CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq_items
    ADD CONSTRAINT rfq_items_pkey PRIMARY KEY (rfq_item_id);


--
-- TOC entry 5148 (class 2606 OID 373626)
-- Name: rfq rfq_pkey; Type: CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq
    ADD CONSTRAINT rfq_pkey PRIMARY KEY (rfq_id);


--
-- TOC entry 5151 (class 2606 OID 373641)
-- Name: rfq_items rfq_items_item_id_fkey; Type: FK CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq_items
    ADD CONSTRAINT rfq_items_item_id_fkey FOREIGN KEY (item_id) REFERENCES purnew.items(item_id);


--
-- TOC entry 5152 (class 2606 OID 373636)
-- Name: rfq_items rfq_items_rfq_id_fkey; Type: FK CONSTRAINT; Schema: purnew; Owner: postgres
--

ALTER TABLE ONLY purnew.rfq_items
    ADD CONSTRAINT rfq_items_rfq_id_fkey FOREIGN KEY (rfq_id) REFERENCES purnew.rfq(rfq_id);


-- Completed on 2026-03-04 15:21:08

--
-- PostgreSQL database dump complete
--

\unrestrict VI7hyP2ePUOIuhkgnCoeyX5PsDw1PsCJ3k4ODbbe02npz7u6tzdsevX95aLpbJx

