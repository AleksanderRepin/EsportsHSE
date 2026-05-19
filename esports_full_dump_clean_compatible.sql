--
-- PostgreSQL database dump
--


-- Dumped from database version 17.9
-- Dumped by pg_dump version 17.9

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

ALTER TABLE IF EXISTS ONLY public.tournamentteam DROP CONSTRAINT IF EXISTS tournamentteam_tournament_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentteam DROP CONSTRAINT IF EXISTS tournamentteam_team_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentstage DROP CONSTRAINT IF EXISTS tournamentstage_tournament_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_user_team_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_tournament_team_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_tournament_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_team_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_role_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tournament DROP CONSTRAINT IF EXISTS tournament_game_type_id_fkey;
ALTER TABLE IF EXISTS ONLY public.teamplayer DROP CONSTRAINT IF EXISTS teamplayer_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.teamplayer DROP CONSTRAINT IF EXISTS teamplayer_team_id_fkey;
ALTER TABLE IF EXISTS ONLY public.teamplayer DROP CONSTRAINT IF EXISTS teamplayer_role_id_fkey;
ALTER TABLE IF EXISTS ONLY public.prizedistribution DROP CONSTRAINT IF EXISTS prizedistribution_tournament_id_fkey;
ALTER TABLE IF EXISTS ONLY public.playerstats DROP CONSTRAINT IF EXISTS playerstats_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.playerstats DROP CONSTRAINT IF EXISTS playerstats_match_game_id_fkey;
ALTER TABLE IF EXISTS ONLY public.matchgame DROP CONSTRAINT IF EXISTS matchgame_winner_team_id_fkey;
ALTER TABLE IF EXISTS ONLY public.matchgame DROP CONSTRAINT IF EXISTS matchgame_match_id_fkey;
ALTER TABLE IF EXISTS ONLY public.matchgame DROP CONSTRAINT IF EXISTS matchgame_map_id_fkey;
ALTER TABLE IF EXISTS ONLY public.match DROP CONSTRAINT IF EXISTS match_winner_team_id_fkey;
ALTER TABLE IF EXISTS ONLY public.match DROP CONSTRAINT IF EXISTS match_tournament_id_fkey;
ALTER TABLE IF EXISTS ONLY public.match DROP CONSTRAINT IF EXISTS match_team2_id_fkey;
ALTER TABLE IF EXISTS ONLY public.match DROP CONSTRAINT IF EXISTS match_team1_id_fkey;
ALTER TABLE IF EXISTS ONLY public.match DROP CONSTRAINT IF EXISTS match_stage_id_fkey;
ALTER TABLE IF EXISTS ONLY public.gamemap DROP CONSTRAINT IF EXISTS gamemap_game_type_id_fkey;
DROP INDEX IF EXISTS public.tournamentroster_user_idx;
DROP INDEX IF EXISTS public.tournamentroster_tournament_team_idx;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_nickname_key;
ALTER TABLE IF EXISTS ONLY public.tournamentteam DROP CONSTRAINT IF EXISTS tournamentteam_tournament_id_team_id_key;
ALTER TABLE IF EXISTS ONLY public.tournamentteam DROP CONSTRAINT IF EXISTS tournamentteam_pkey;
ALTER TABLE IF EXISTS ONLY public.tournamentstage DROP CONSTRAINT IF EXISTS tournamentstage_tournament_id_stage_order_key;
ALTER TABLE IF EXISTS ONLY public.tournamentstage DROP CONSTRAINT IF EXISTS tournamentstage_pkey;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_tournament_id_user_id_key;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_tournament_id_team_id_user_id_key;
ALTER TABLE IF EXISTS ONLY public.tournamentroster DROP CONSTRAINT IF EXISTS tournamentroster_pkey;
ALTER TABLE IF EXISTS ONLY public.tournament DROP CONSTRAINT IF EXISTS tournament_pkey;
ALTER TABLE IF EXISTS ONLY public.teamplayer DROP CONSTRAINT IF EXISTS teamplayer_user_id_team_id_key;
ALTER TABLE IF EXISTS ONLY public.teamplayer DROP CONSTRAINT IF EXISTS teamplayer_pkey;
ALTER TABLE IF EXISTS ONLY public.team DROP CONSTRAINT IF EXISTS team_pkey;
ALTER TABLE IF EXISTS ONLY public.team DROP CONSTRAINT IF EXISTS team_name_key;
ALTER TABLE IF EXISTS ONLY public.prizedistribution DROP CONSTRAINT IF EXISTS prizedistribution_tournament_id_place_key;
ALTER TABLE IF EXISTS ONLY public.prizedistribution DROP CONSTRAINT IF EXISTS prizedistribution_pkey;
ALTER TABLE IF EXISTS ONLY public.playerstats DROP CONSTRAINT IF EXISTS playerstats_pkey;
ALTER TABLE IF EXISTS ONLY public.playerstats DROP CONSTRAINT IF EXISTS playerstats_match_game_id_user_id_key;
ALTER TABLE IF EXISTS ONLY public.playerrole DROP CONSTRAINT IF EXISTS playerrole_pkey;
ALTER TABLE IF EXISTS ONLY public.playerrole DROP CONSTRAINT IF EXISTS playerrole_name_key;
ALTER TABLE IF EXISTS ONLY public.matchgame DROP CONSTRAINT IF EXISTS matchgame_pkey;
ALTER TABLE IF EXISTS ONLY public.matchgame DROP CONSTRAINT IF EXISTS matchgame_match_id_game_number_key;
ALTER TABLE IF EXISTS ONLY public.match DROP CONSTRAINT IF EXISTS match_pkey;
ALTER TABLE IF EXISTS ONLY public.gametype DROP CONSTRAINT IF EXISTS gametype_pkey;
ALTER TABLE IF EXISTS ONLY public.gametype DROP CONSTRAINT IF EXISTS gametype_name_key;
ALTER TABLE IF EXISTS ONLY public.gamemap DROP CONSTRAINT IF EXISTS gamemap_pkey;
ALTER TABLE IF EXISTS ONLY public.gamemap DROP CONSTRAINT IF EXISTS gamemap_game_type_id_name_key;
ALTER TABLE IF EXISTS public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tournamentteam ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tournamentstage ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tournamentroster ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tournament ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.teamplayer ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.team ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.prizedistribution ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.playerstats ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.playerrole ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.matchgame ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.match ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.gametype ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.gamemap ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.users_id_seq;
DROP TABLE IF EXISTS public.users;
DROP SEQUENCE IF EXISTS public.tournamentteam_id_seq;
DROP TABLE IF EXISTS public.tournamentteam;
DROP SEQUENCE IF EXISTS public.tournamentstage_id_seq;
DROP TABLE IF EXISTS public.tournamentstage;
DROP SEQUENCE IF EXISTS public.tournamentroster_id_seq;
DROP TABLE IF EXISTS public.tournamentroster;
DROP SEQUENCE IF EXISTS public.tournament_id_seq;
DROP TABLE IF EXISTS public.tournament;
DROP SEQUENCE IF EXISTS public.teamplayer_id_seq;
DROP TABLE IF EXISTS public.teamplayer;
DROP SEQUENCE IF EXISTS public.team_id_seq;
DROP TABLE IF EXISTS public.team;
DROP SEQUENCE IF EXISTS public.prizedistribution_id_seq;
DROP TABLE IF EXISTS public.prizedistribution;
DROP SEQUENCE IF EXISTS public.playerstats_id_seq;
DROP TABLE IF EXISTS public.playerstats;
DROP SEQUENCE IF EXISTS public.playerrole_id_seq;
DROP TABLE IF EXISTS public.playerrole;
DROP SEQUENCE IF EXISTS public.matchgame_id_seq;
DROP TABLE IF EXISTS public.matchgame;
DROP SEQUENCE IF EXISTS public.match_id_seq;
DROP TABLE IF EXISTS public.match;
DROP SEQUENCE IF EXISTS public.gametype_id_seq;
DROP TABLE IF EXISTS public.gametype;
DROP SEQUENCE IF EXISTS public.gamemap_id_seq;
DROP TABLE IF EXISTS public.gamemap;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: gamemap; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gamemap (
    id integer NOT NULL,
    game_type_id integer NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: gamemap_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gamemap_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gamemap_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gamemap_id_seq OWNED BY public.gamemap.id;


--
-- Name: gametype; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gametype (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    team_size integer NOT NULL,
    CONSTRAINT gametype_team_size_check CHECK ((team_size > 0))
);


--
-- Name: gametype_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gametype_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gametype_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gametype_id_seq OWNED BY public.gametype.id;


--
-- Name: match; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.match (
    id integer NOT NULL,
    tournament_id integer NOT NULL,
    stage_id integer NOT NULL,
    team1_id integer NOT NULL,
    team2_id integer NOT NULL,
    team1_score integer DEFAULT 0 NOT NULL,
    team2_score integer DEFAULT 0 NOT NULL,
    winner_team_id integer,
    match_date timestamp without time zone,
    end_time timestamp without time zone,
    is_finished boolean DEFAULT false NOT NULL,
    CONSTRAINT match_check CHECK ((team1_id <> team2_id)),
    CONSTRAINT match_team1_score_check CHECK ((team1_score >= 0)),
    CONSTRAINT match_team2_score_check CHECK ((team2_score >= 0))
);


--
-- Name: match_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.match_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: match_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.match_id_seq OWNED BY public.match.id;


--
-- Name: matchgame; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.matchgame (
    id integer NOT NULL,
    match_id integer NOT NULL,
    map_name character varying(255) NOT NULL,
    game_number integer NOT NULL,
    winner_team_id integer,
    map_id integer,
    CONSTRAINT matchgame_game_number_check CHECK ((game_number > 0))
);


--
-- Name: matchgame_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.matchgame_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: matchgame_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.matchgame_id_seq OWNED BY public.matchgame.id;


--
-- Name: playerrole; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.playerrole (
    id integer NOT NULL,
    name character varying(100) NOT NULL
);


--
-- Name: playerrole_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.playerrole_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: playerrole_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.playerrole_id_seq OWNED BY public.playerrole.id;


--
-- Name: playerstats; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.playerstats (
    id integer NOT NULL,
    match_game_id integer NOT NULL,
    user_id integer NOT NULL,
    kills integer DEFAULT 0 NOT NULL,
    deaths integer DEFAULT 0 NOT NULL,
    CONSTRAINT playerstats_deaths_check CHECK ((deaths >= 0)),
    CONSTRAINT playerstats_kills_check CHECK ((kills >= 0))
);


--
-- Name: playerstats_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.playerstats_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: playerstats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.playerstats_id_seq OWNED BY public.playerstats.id;


--
-- Name: prizedistribution; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.prizedistribution (
    id integer NOT NULL,
    tournament_id integer NOT NULL,
    place integer NOT NULL,
    prize_amount numeric(12,2) NOT NULL,
    CONSTRAINT prizedistribution_place_check CHECK ((place > 0)),
    CONSTRAINT prizedistribution_prize_amount_check CHECK ((prize_amount >= (0)::numeric))
);


--
-- Name: prizedistribution_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.prizedistribution_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: prizedistribution_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.prizedistribution_id_seq OWNED BY public.prizedistribution.id;


--
-- Name: team; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.team (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    country character varying(100) NOT NULL,
    rating integer DEFAULT 0 NOT NULL,
    CONSTRAINT team_rating_check CHECK ((rating >= 0))
);


--
-- Name: team_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.team_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: team_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.team_id_seq OWNED BY public.team.id;


--
-- Name: teamplayer; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.teamplayer (
    id integer NOT NULL,
    user_id integer NOT NULL,
    team_id integer NOT NULL,
    role character varying(100),
    joined_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    role_id integer
);


--
-- Name: teamplayer_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.teamplayer_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: teamplayer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.teamplayer_id_seq OWNED BY public.teamplayer.id;


--
-- Name: tournament; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournament (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    game_type_id integer NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    prize_pool numeric(12,2) DEFAULT 0,
    status character varying(30) DEFAULT 'planned'::character varying NOT NULL,
    CONSTRAINT tournament_check CHECK ((end_date >= start_date)),
    CONSTRAINT tournament_prize_pool_check CHECK ((prize_pool >= (0)::numeric)),
    CONSTRAINT tournament_status_check CHECK (((status)::text = ANY ((ARRAY['planned'::character varying, 'active'::character varying, 'finished'::character varying])::text[])))
);


--
-- Name: tournament_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tournament_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tournament_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tournament_id_seq OWNED BY public.tournament.id;


--
-- Name: tournamentroster; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournamentroster (
    id integer NOT NULL,
    tournament_id integer NOT NULL,
    team_id integer NOT NULL,
    user_id integer NOT NULL,
    role_id integer,
    joined_at timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: tournamentroster_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tournamentroster_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tournamentroster_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tournamentroster_id_seq OWNED BY public.tournamentroster.id;


--
-- Name: tournamentstage; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournamentstage (
    id integer NOT NULL,
    tournament_id integer NOT NULL,
    name character varying(255) NOT NULL,
    stage_order integer NOT NULL
);


--
-- Name: tournamentstage_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tournamentstage_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tournamentstage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tournamentstage_id_seq OWNED BY public.tournamentstage.id;


--
-- Name: tournamentteam; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournamentteam (
    id integer NOT NULL,
    tournament_id integer NOT NULL,
    team_id integer NOT NULL,
    seed integer,
    final_place integer
);


--
-- Name: tournamentteam_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tournamentteam_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tournamentteam_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tournamentteam_id_seq OWNED BY public.tournamentteam.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    nickname character varying(255) NOT NULL,
    country character varying(100) NOT NULL,
    rating integer DEFAULT 0 NOT NULL,
    role character varying(100) DEFAULT 'player'::character varying NOT NULL,
    password_hash character varying(255) NOT NULL,
    CONSTRAINT users_rating_check CHECK ((rating >= 0)),
    CONSTRAINT users_system_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'player'::character varying])::text[])))
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: gamemap id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gamemap ALTER COLUMN id SET DEFAULT nextval('public.gamemap_id_seq'::regclass);


--
-- Name: gametype id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gametype ALTER COLUMN id SET DEFAULT nextval('public.gametype_id_seq'::regclass);


--
-- Name: match id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match ALTER COLUMN id SET DEFAULT nextval('public.match_id_seq'::regclass);


--
-- Name: matchgame id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matchgame ALTER COLUMN id SET DEFAULT nextval('public.matchgame_id_seq'::regclass);


--
-- Name: playerrole id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerrole ALTER COLUMN id SET DEFAULT nextval('public.playerrole_id_seq'::regclass);


--
-- Name: playerstats id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerstats ALTER COLUMN id SET DEFAULT nextval('public.playerstats_id_seq'::regclass);


--
-- Name: prizedistribution id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prizedistribution ALTER COLUMN id SET DEFAULT nextval('public.prizedistribution_id_seq'::regclass);


--
-- Name: team id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.team ALTER COLUMN id SET DEFAULT nextval('public.team_id_seq'::regclass);


--
-- Name: teamplayer id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teamplayer ALTER COLUMN id SET DEFAULT nextval('public.teamplayer_id_seq'::regclass);


--
-- Name: tournament id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament ALTER COLUMN id SET DEFAULT nextval('public.tournament_id_seq'::regclass);


--
-- Name: tournamentroster id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster ALTER COLUMN id SET DEFAULT nextval('public.tournamentroster_id_seq'::regclass);


--
-- Name: tournamentstage id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentstage ALTER COLUMN id SET DEFAULT nextval('public.tournamentstage_id_seq'::regclass);


--
-- Name: tournamentteam id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentteam ALTER COLUMN id SET DEFAULT nextval('public.tournamentteam_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: gamemap; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.gamemap (id, game_type_id, name) FROM stdin;
1	1	Anubis
2	1	Ancient
3	1	Nuke
4	1	Inferno
5	1	Mirage
6	2	Radiant Draft
7	2	Dire Draft
8	2	Radiant vs Dire
9	3	Lotus
10	3	Split
11	3	Haven
12	3	Bind
13	3	Ascent
14	4	Summoner's Rift
\.


--
-- Data for Name: gametype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.gametype (id, name, team_size) FROM stdin;
1	Counter-Strike 2	5
2	Dota 2	5
3	Valorant	5
4	League of Legends	5
\.


--
-- Data for Name: match; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.match (id, tournament_id, stage_id, team1_id, team2_id, team1_score, team2_score, winner_team_id, match_date, end_time, is_finished) FROM stdin;
19	1	13	1	16	2	1	1	2026-05-12 23:35:32	2026-05-13 02:52:11.086137	t
20	1	13	13	15	2	1	13	2026-05-12 23:35:32	2026-05-13 02:56:28.997402	t
21	1	14	1	13	2	1	1	\N	2026-05-17 20:49:42.585484	t
3	2	3	1	2	4	1	1	2026-01-20 19:00:00	2026-01-20 22:40:00	t
4	2	4	2	3	2	1	2	2026-01-16 20:00:00	2026-01-16 22:05:00	t
5	2	4	1	4	2	0	1	2026-01-16 18:00:00	2026-01-16 19:25:00	t
6	2	5	4	5	2	1	4	2026-01-12 18:00:00	2026-01-12 19:45:00	t
7	2	5	3	6	2	0	3	2026-01-12 16:00:00	2026-01-12 17:15:00	t
8	2	5	2	13	2	1	2	2026-01-11 18:00:00	2026-01-11 19:50:00	t
9	2	5	1	14	2	0	1	2026-01-11 16:00:00	2026-01-11 17:20:00	t
10	3	8	4	5	2	1	4	2026-04-11 18:00:00	2026-04-11 20:00:00	t
11	3	8	3	6	2	1	3	2026-04-11 16:00:00	2026-04-11 18:00:00	t
12	3	8	2	11	2	0	2	2026-04-10 18:00:00	2026-04-10 20:00:00	t
13	3	8	1	12	2	1	1	2026-04-10 16:00:00	2026-04-10 18:00:00	t
29	3	7	4	3	1	2	3	2026-04-02 14:00:00	2026-04-02 16:00:00	t
30	3	7	2	1	1	2	1	2026-04-02 14:00:00	2026-04-02 16:00:00	t
31	3	6	3	1	1	2	1	2026-04-02 15:00:00	2026-04-02 17:00:00	t
24	4	17	7	10	2	1	7	2026-05-13 00:04:15	2026-05-13 02:04:15	t
25	4	17	8	9	2	1	8	2026-05-13 00:04:15	2026-05-13 02:04:15	t
32	4	18	7	8	2	1	7	2026-03-05 14:00:00	2026-03-05 16:00:00	t
26	5	19	5	8	3	0	5	2026-05-17 10:16:41	2026-05-17 13:22:18.968321	t
27	5	19	2	15	2	1	2	2026-05-17 10:16:41	2026-05-17 17:32:44.837062	t
28	5	20	5	2	1	2	2	\N	2026-05-17 20:49:42.585484	t
\.


--
-- Data for Name: matchgame; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.matchgame (id, match_id, map_name, game_number, winner_team_id, map_id) FROM stdin;
1	3	Nuke	3	1	3
2	3	Inferno	2	2	4
3	3	Mirage	1	1	5
4	8	Mirage	3	2	5
5	8	Ancient	2	13	2
6	8	Nuke	1	2	3
7	9	Inferno	2	1	4
8	9	Mirage	1	1	5
9	12	Dire Draft	2	2	7
10	12	Radiant vs Dire	1	2	8
16	19	Ancient	1	1	2
17	19	Ancient	2	16	2
19	19	Inferno	3	1	4
22	20	Ancient	1	13	2
23	20	Ancient	2	15	2
24	20	Inferno	3	13	4
28	26	Radiant vs Dire	1	5	8
29	26	Radiant vs Dire	2	5	8
30	26	Dire Draft	3	5	7
31	27	Radiant vs Dire	1	2	8
32	27	Radiant vs Dire	2	15	8
33	27	Radiant vs Dire	3	2	8
34	21	Anubis	1	13	1
35	21	Ancient	2	1	2
36	21	Nuke	3	1	3
37	3	Inferno	4	1	4
38	3	Mirage	5	1	5
39	4	Anubis	1	3	1
40	4	Ancient	2	2	2
41	4	Nuke	3	2	3
42	5	Anubis	1	1	1
43	5	Ancient	2	1	2
44	6	Anubis	1	5	1
45	6	Ancient	2	4	2
46	6	Nuke	3	4	3
47	7	Anubis	1	3	1
48	7	Ancient	2	3	2
49	10	Radiant Draft	1	5	6
50	10	Dire Draft	2	4	7
51	10	Radiant vs Dire	3	4	8
52	11	Radiant Draft	1	6	6
53	11	Dire Draft	2	3	7
54	11	Radiant vs Dire	3	3	8
55	13	Radiant Draft	1	12	6
56	13	Dire Draft	2	1	7
57	13	Radiant vs Dire	3	1	8
58	29	Radiant Draft	1	3	6
59	29	Dire Draft	2	3	7
60	29	Radiant vs Dire	3	4	8
61	30	Radiant Draft	1	1	6
62	30	Dire Draft	2	1	7
63	30	Radiant vs Dire	3	2	8
64	31	Radiant Draft	1	1	6
65	31	Dire Draft	2	1	7
66	31	Radiant vs Dire	3	3	8
67	24	Lotus	1	10	9
68	24	Split	2	7	10
69	24	Haven	3	7	11
70	25	Lotus	1	9	9
71	25	Split	2	8	10
72	25	Haven	3	8	11
73	32	Lotus	1	8	9
74	32	Split	2	7	10
75	32	Haven	3	7	11
76	28	Radiant Draft	1	2	6
77	28	Dire Draft	2	2	7
78	28	Radiant vs Dire	3	5	8
\.


--
-- Data for Name: playerrole; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.playerrole (id, name) FROM stdin;
1	Captain
2	Sniper
3	Support
4	Entry Fragger
5	Carry
6	Mid
7	Offlaner
8	Duelist
9	Controller
14	Lurker
15	AWPer
19	Hard Support
20	Soft Support
23	Initiator
24	Sentinel
25	Top
26	Jungler
27	ADC
\.


--
-- Data for Name: playerstats; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.playerstats (id, match_game_id, user_id, kills, deaths) FROM stdin;
1	2	19	25	16
2	2	18	18	19
3	3	3	21	20
4	3	2	28	16
5	5	30	20	14
6	5	19	17	18
7	6	15	22	17
8	6	3	19	15
9	7	32	13	18
10	7	18	20	11
11	8	16	15	19
12	8	2	24	12
13	9	28	7	11
14	9	3	10	6
15	10	12	8	9
16	10	19	14	4
164	23	42	16	4
165	23	43	17	5
28	16	33	17	3
27	16	34	15	2
30	16	2	12	5
26	16	18	10	3
32	17	33	12	5
33	17	34	17	4
34	17	2	16	4
35	17	18	10	2
36	19	2	11	4
37	19	18	18	2
38	19	33	5	6
39	19	34	4	10
40	22	15	10	5
41	22	30	14	4
42	22	17	4	7
43	22	31	5	10
44	23	15	4	8
45	23	30	5	6
46	23	17	7	5
47	23	31	12	3
48	24	15	10	1
49	24	30	13	2
50	24	17	12	1
51	24	31	3	4
52	28	9	12	4
53	28	25	20	5
54	28	146	16	3
55	28	147	6	10
56	28	148	5	12
57	28	6	30	4
58	28	90	20	24
59	28	91	15	9
60	28	92	18	4
61	28	93	12	27
62	29	6	17	10
63	29	90	26	15
64	29	91	6	4
65	29	92	9	3
66	29	93	10	5
67	29	9	19	7
68	29	25	15	5
69	29	146	25	20
70	29	147	10	13
71	29	148	16	8
72	30	6	14	8
73	30	90	18	2
74	30	91	19	7
75	30	92	6	21
76	30	93	8	15
77	30	9	17	1
78	30	25	15	2
79	30	146	7	15
80	30	147	22	4
81	30	148	18	4
82	31	3	11	6
83	31	19	16	6
84	31	98	8	7
85	31	99	10	4
86	31	100	8	9
87	32	3	10	4
88	32	19	12	5
89	32	98	12	5
90	32	99	13	3
91	32	100	15	3
92	32	17	16	4
93	32	31	14	2
94	32	42	13	3
95	32	43	13	4
96	32	44	11	2
97	31	17	10	3
98	31	31	14	2
99	31	42	14	4
100	31	43	15	4
101	31	44	14	2
102	33	3	0	0
103	33	19	15	0
104	33	98	0	0
105	33	99	0	0
106	33	100	0	0
107	33	17	8	4
108	33	31	13	6
109	33	42	6	8
110	33	43	5	4
111	33	44	12	5
119	16	50	16	7
120	16	51	17	4
121	16	52	12	5
124	16	82	7	11
125	16	83	8	8
126	16	84	9	9
129	17	50	12	8
130	17	51	7	9
131	17	52	8	10
134	17	82	13	4
135	17	83	14	5
136	17	84	15	6
139	19	50	12	5
140	19	51	13	6
141	19	52	14	7
144	19	82	9	9
145	19	83	10	10
146	19	84	11	11
149	22	66	15	7
150	22	67	16	4
151	22	68	17	5
154	22	42	10	11
155	22	43	11	8
156	22	44	12	9
159	23	66	11	8
160	23	67	12	9
161	23	68	7	10
166	23	44	12	6
169	24	66	17	5
170	24	67	12	6
171	24	68	13	7
174	24	42	12	9
175	24	43	7	10
176	24	44	8	11
177	34	2	7	11
178	34	18	11	11
179	34	50	7	11
180	34	51	8	8
181	34	52	9	9
182	34	15	13	4
183	34	30	16	7
184	34	66	16	7
185	34	67	17	4
186	34	68	12	5
187	35	2	13	4
188	35	18	17	4
189	35	50	13	4
190	35	51	14	5
191	35	52	15	6
192	35	15	9	9
193	35	30	12	8
194	35	66	12	8
195	35	67	7	9
196	35	68	8	10
197	36	2	14	5
198	36	18	12	5
199	36	50	14	5
200	36	51	15	6
201	36	52	16	7
202	36	15	10	10
203	36	30	7	9
204	36	66	7	9
205	36	67	8	10
206	36	68	9	11
208	3	18	16	7
209	3	52	14	5
210	3	53	15	6
211	3	54	16	7
213	3	19	12	8
214	3	98	7	11
215	3	99	8	8
216	3	104	7	9
217	2	2	8	8
219	2	52	10	10
220	2	53	11	11
221	2	54	12	8
222	2	3	14	5
224	2	98	13	4
225	2	99	14	5
226	2	104	13	6
227	1	2	14	5
228	1	18	12	5
229	1	52	16	7
230	1	53	17	4
231	1	54	12	5
232	1	3	10	10
233	1	19	8	10
234	1	98	9	9
235	1	99	10	10
236	1	104	9	11
237	37	2	15	6
238	37	18	13	6
239	37	52	17	4
240	37	53	12	5
241	37	54	13	6
242	37	3	11	11
243	37	19	9	11
244	37	98	10	10
245	37	99	11	11
246	37	104	10	8
247	38	2	16	7
248	38	18	14	7
249	38	52	12	5
250	38	53	13	6
251	38	54	14	7
252	38	3	12	8
253	38	19	10	8
254	38	98	11	11
255	38	99	12	8
256	38	104	11	9
257	39	3	9	8
258	39	19	7	8
259	39	98	8	11
260	39	99	9	8
261	39	104	8	9
262	39	4	15	5
263	39	20	13	5
264	39	58	15	7
265	39	59	16	4
266	39	60	17	5
267	40	3	15	5
268	40	19	13	5
269	40	98	14	4
270	40	99	15	5
271	40	104	14	6
272	40	4	11	10
273	40	20	9	10
274	40	58	11	8
275	40	59	12	9
276	40	60	7	10
277	41	3	16	6
278	41	19	14	6
279	41	98	15	5
280	41	99	16	6
281	41	104	15	7
282	41	4	12	11
283	41	20	10	11
284	41	58	12	9
285	41	59	7	10
286	41	60	8	11
287	42	2	14	7
288	42	18	12	7
289	42	52	16	5
290	42	53	17	6
291	42	54	12	7
292	42	5	12	10
293	42	21	10	10
294	42	106	11	11
295	42	107	12	8
296	42	108	7	9
297	43	2	15	4
298	43	18	13	4
299	43	52	17	6
300	43	53	12	7
301	43	54	13	4
302	43	5	7	11
303	43	21	11	11
304	43	106	12	8
305	43	107	7	9
306	43	108	8	10
307	44	5	7	10
308	44	21	11	10
309	44	106	12	11
310	44	107	7	8
311	44	108	8	9
312	44	6	13	7
313	44	22	17	7
314	44	90	13	7
315	44	91	14	4
316	44	92	15	5
317	45	5	13	7
318	45	21	17	7
319	45	106	12	4
320	45	107	13	5
321	45	108	14	6
322	45	6	9	8
323	45	22	7	8
324	45	90	9	8
325	45	91	10	9
326	45	92	11	10
327	46	5	14	4
328	46	21	12	4
329	46	106	13	5
330	46	107	14	6
331	46	108	15	7
332	46	6	10	9
333	46	22	8	9
334	46	90	10	9
335	46	91	11	10
336	46	92	12	11
337	47	4	12	5
338	47	20	16	5
339	47	58	12	7
340	47	59	13	4
341	47	60	14	5
342	47	7	10	8
343	47	23	8	8
344	47	162	9	11
345	47	163	10	8
346	47	164	11	9
347	48	4	13	6
348	48	20	17	6
349	48	58	13	4
350	48	59	14	5
351	48	60	15	6
352	48	7	11	9
353	48	23	9	9
354	48	162	10	8
355	48	163	11	9
356	48	164	12	10
358	6	19	16	4
359	6	98	17	7
360	6	99	12	4
361	6	104	17	5
363	6	30	10	11
364	6	66	10	11
365	6	67	11	8
366	6	68	12	9
367	5	3	8	9
369	5	98	7	8
370	5	99	8	9
371	5	104	7	10
372	5	15	13	5
374	5	66	16	4
375	5	67	17	5
376	5	68	12	6
377	4	3	14	6
378	4	19	12	6
379	4	98	13	5
380	4	99	14	6
381	4	104	13	7
382	4	15	9	10
383	4	30	12	9
384	4	66	12	9
385	4	67	7	10
386	4	68	8	11
388	8	18	16	7
389	8	52	14	5
390	8	53	15	6
391	8	54	16	7
393	8	32	7	9
394	8	114	11	11
395	8	115	12	8
396	8	116	7	9
397	7	2	13	4
399	7	52	15	6
400	7	53	16	7
401	7	54	17	4
402	7	16	10	10
404	7	114	12	8
405	7	115	7	9
406	7	116	8	10
407	49	5	11	10
408	49	21	9	10
409	49	106	10	11
410	49	107	11	8
411	49	108	12	9
412	49	6	17	7
413	49	22	15	7
414	49	90	17	7
415	49	91	12	4
416	49	92	13	5
417	50	5	17	7
418	50	21	15	7
419	50	106	16	4
420	50	107	17	5
421	50	108	12	6
422	50	6	7	8
423	50	22	11	8
424	50	90	7	8
425	50	91	8	9
426	50	92	9	10
427	51	5	12	4
428	51	21	16	4
429	51	106	17	5
430	51	107	12	6
431	51	108	13	7
432	51	6	8	9
433	51	22	12	9
434	51	90	8	9
435	51	91	9	10
436	51	92	10	11
437	52	4	11	9
438	52	20	9	9
439	52	58	11	11
440	52	59	12	8
441	52	60	7	9
442	52	7	13	4
443	52	23	17	4
444	52	162	12	7
445	52	163	13	4
446	52	164	14	5
447	53	4	17	6
448	53	20	15	6
449	53	58	17	4
450	53	59	12	5
451	53	60	13	6
452	53	7	9	9
453	53	23	7	9
454	53	162	8	8
455	53	163	9	9
456	53	164	10	10
457	54	4	12	7
458	54	20	16	7
459	54	58	12	5
460	54	59	13	6
461	54	60	14	7
462	54	7	10	10
463	54	23	8	10
464	54	162	9	9
465	54	163	10	10
466	54	164	11	11
467	10	3	16	4
469	10	98	15	7
470	10	99	16	4
471	10	100	17	5
473	10	28	12	9
474	10	154	12	11
475	10	155	7	8
476	10	156	8	9
478	9	19	15	5
479	9	98	16	4
480	9	99	17	5
481	9	100	12	6
482	9	12	9	10
484	9	154	7	8
485	9	155	8	9
486	9	156	9	10
487	55	2	11	11
488	55	18	9	11
489	55	50	11	11
490	55	51	12	8
491	55	52	7	9
492	55	13	15	6
493	55	29	13	6
494	55	122	16	7
495	55	123	17	4
496	55	124	12	5
497	56	2	17	4
498	56	18	15	4
499	56	50	17	4
500	56	51	12	5
501	56	52	13	6
502	56	13	11	11
503	56	29	9	11
504	56	122	12	8
505	56	123	7	9
506	56	124	8	10
507	57	2	12	5
508	57	18	16	5
509	57	50	12	5
510	57	51	13	6
511	57	52	14	7
512	57	13	12	8
513	57	29	10	8
514	57	122	7	9
515	57	123	8	10
516	57	124	9	11
517	58	5	12	10
518	58	21	10	10
519	58	106	11	11
520	58	107	12	8
521	58	108	7	9
522	58	4	16	5
523	58	20	14	5
524	58	58	16	7
525	58	59	17	4
526	58	60	12	5
527	59	5	7	11
528	59	21	11	11
529	59	106	12	8
530	59	107	7	9
531	59	108	8	10
532	59	4	17	6
533	59	20	15	6
534	59	58	17	4
535	59	59	12	5
536	59	60	13	6
537	60	5	13	4
538	60	21	17	4
539	60	106	12	5
540	60	107	13	6
541	60	108	14	7
542	60	4	7	11
543	60	20	11	11
544	60	58	7	9
545	60	59	8	10
546	60	60	9	11
547	61	3	11	8
548	61	19	9	8
549	61	98	10	11
550	61	99	11	8
551	61	100	12	9
552	61	2	15	7
553	61	18	13	7
554	61	50	15	7
555	61	51	16	4
556	61	52	17	5
557	62	3	12	9
558	62	19	10	9
559	62	98	11	8
560	62	99	12	9
561	62	100	7	10
562	62	2	16	4
563	62	18	14	4
564	62	50	16	4
565	62	51	17	5
566	62	52	12	6
567	63	3	12	6
568	63	19	16	6
569	63	98	17	5
570	63	99	12	6
571	63	100	13	7
572	63	2	12	9
573	63	18	10	9
574	63	50	12	9
575	63	51	7	10
576	63	52	8	11
577	64	4	7	9
578	64	20	11	9
579	64	58	7	11
580	64	59	8	8
581	64	60	9	9
582	64	2	16	7
583	64	18	14	7
584	64	50	16	7
585	64	51	17	4
586	64	52	12	5
587	65	4	8	10
588	65	20	12	10
589	65	58	8	8
590	65	59	9	9
591	65	60	10	10
592	65	2	17	4
593	65	18	15	4
594	65	50	17	4
595	65	51	12	5
596	65	52	13	6
597	66	4	14	7
598	66	20	12	7
599	66	58	14	5
600	66	59	15	6
601	66	60	16	7
602	66	2	7	9
603	66	18	11	9
604	66	50	7	9
605	66	51	8	10
606	66	52	9	11
607	67	8	10	9
608	67	130	12	11
609	67	131	7	8
610	67	132	8	9
611	67	133	9	10
612	67	11	12	4
613	67	27	16	4
614	67	74	15	7
615	67	75	16	4
616	67	76	17	5
617	68	8	16	6
618	68	130	12	4
619	68	131	13	5
620	68	132	14	6
621	68	133	15	7
622	68	11	8	9
623	68	27	12	9
624	68	74	11	8
625	68	75	12	9
626	68	76	7	10
627	69	8	17	7
628	69	130	13	5
629	69	131	14	6
630	69	132	15	7
631	69	133	16	4
632	69	11	9	10
633	69	27	7	10
634	69	74	12	9
635	69	75	7	10
636	69	76	8	11
637	70	9	12	10
638	70	25	10	10
639	70	146	11	11
640	70	147	12	8
641	70	148	7	9
642	70	10	12	7
643	70	26	16	7
644	70	138	14	7
645	70	139	15	4
646	70	140	16	5
647	71	9	12	7
648	71	25	16	7
649	71	146	17	4
650	71	147	12	5
651	71	148	13	6
652	71	10	8	8
653	71	26	12	8
654	71	138	10	8
655	71	139	11	9
656	71	140	12	10
657	72	9	13	4
658	72	25	17	4
659	72	146	12	5
660	72	147	13	6
661	72	148	14	7
662	72	10	9	9
663	72	26	7	9
664	72	138	11	9
665	72	139	12	10
666	72	140	7	11
667	73	8	12	9
668	73	130	8	11
669	73	131	9	8
670	73	132	10	9
671	73	133	11	10
672	73	9	12	6
673	73	25	16	6
674	73	146	17	7
675	73	147	12	4
676	73	148	13	5
677	74	8	12	6
678	74	130	14	4
679	74	131	15	5
680	74	132	16	6
681	74	133	17	7
682	74	9	8	11
683	74	25	12	11
684	74	146	7	8
685	74	147	8	9
686	74	148	9	10
687	75	8	13	7
688	75	130	15	5
689	75	131	16	6
690	75	132	17	7
691	75	133	12	4
692	75	9	9	8
693	75	25	7	8
694	75	146	8	9
695	75	147	9	10
696	75	148	10	11
757	76	6	12	11
758	76	90	12	11
759	76	91	7	8
760	76	92	8	9
761	76	93	9	10
762	76	3	14	4
763	76	19	12	4
764	76	98	13	7
765	76	99	14	4
766	76	100	15	5
767	77	6	7	8
768	77	90	7	8
769	77	91	8	9
770	77	92	9	10
771	77	93	10	11
772	77	3	15	5
773	77	19	13	5
774	77	98	14	4
775	77	99	15	5
776	77	100	16	6
777	78	6	13	5
778	78	90	13	5
779	78	91	14	6
780	78	92	15	7
781	78	93	16	4
782	78	3	11	10
783	78	19	9	10
784	78	98	10	9
785	78	99	11	10
786	78	100	12	11
\.


--
-- Data for Name: prizedistribution; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.prizedistribution (id, tournament_id, place, prize_amount) FROM stdin;
4	1	1	50000.00
3	1	2	30000.00
2	1	3	15000.00
1	1	4	5000.00
8	2	1	40000.00
7	2	2	24000.00
6	2	3	12000.00
5	2	4	4000.00
33	2	5	0.00
34	2	6	0.00
35	2	7	0.00
36	2	8	0.00
12	3	1	60000.00
11	3	2	36000.00
10	3	3	18000.00
9	3	4	6000.00
41	3	5	0.00
42	3	6	0.00
43	3	7	0.00
44	3	8	0.00
16	4	1	30000.00
15	4	2	18000.00
14	4	3	9000.00
13	4	4	3000.00
49	5	1	50000.00
50	5	2	30000.00
51	5	3	15000.00
52	5	4	5000.00
\.


--
-- Data for Name: team; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.team (id, name, country, rating) FROM stdin;
1	Aurora Five	Russia	1840
2	Nordic Wolves	Sweden	1765
3	Berlin Titans	Germany	1710
4	Paris Phoenix	France	1685
5	Madrid Raptors	Spain	1630
6	Warsaw Knights	Poland	1605
7	Seoul Sparks	South Korea	1900
8	Tokyo Ronin	Japan	1825
9	Shanghai Dragons	China	1795
10	Istanbul Storm	Turkey	1650
11	Toronto Lynx	Canada	1700
12	Sao Paulo Pulse	Brazil	1740
13	Helsinki Frost	Finland	1670
14	Prague Arrows	Czech Republic	1580
15	Astana Nomads	Kazakhstan	1545
16	London Orbit	United Kingdom	1615
\.


--
-- Data for Name: teamplayer; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.teamplayer (id, user_id, team_id, role, joined_at, role_id) FROM stdin;
1	2	1	AWPer	2025-01-10 10:00:00	15
2	3	2	Captain	2025-01-12 10:00:00	1
3	4	3	Entry Fragger	2025-01-14 10:00:00	4
4	5	4	Support	2025-01-16 10:00:00	3
5	6	5	Lurker	2025-01-18 10:00:00	14
6	7	6	Sniper	2025-01-20 10:00:00	2
7	8	7	Duelist	2025-01-22 10:00:00	8
8	9	8	Initiator	2025-01-24 10:00:00	23
9	10	9	Controller	2025-01-26 10:00:00	9
10	11	10	Sentinel	2025-01-28 10:00:00	24
11	12	11	Captain	2025-01-30 10:00:00	1
12	13	12	Entry Fragger	2025-02-01 10:00:00	4
13	15	13	AWPer	2025-02-03 10:00:00	15
14	16	14	Lurker	2025-02-05 10:00:00	14
15	17	15	Captain	2025-02-07 10:00:00	1
16	18	1	Entry Fragger	2025-01-11 10:00:00	4
17	19	2	Mid	2025-01-13 10:00:00	6
18	20	3	Offlaner	2025-01-15 10:00:00	7
19	21	4	Hard Support	2025-01-17 10:00:00	19
20	22	5	Soft Support	2025-01-19 10:00:00	20
21	23	6	Top	2025-01-21 10:00:00	25
23	25	8	ADC	2025-01-25 10:00:00	27
24	26	9	Sentinel	2025-01-27 10:00:00	24
25	27	10	Controller	2025-01-29 10:00:00	9
26	28	11	Initiator	2025-01-31 10:00:00	23
27	29	12	Duelist	2025-02-02 10:00:00	8
28	30	13	Support	2025-02-04 10:00:00	3
29	31	15	Entry Fragger	2025-02-08 10:00:00	4
30	32	14	Captain	2025-02-06 10:00:00	1
31	33	16	Support	2025-02-10 10:00:00	3
32	34	16	Mid	2025-02-09 10:00:00	6
33	42	15	Support	2026-05-13 15:44:03.979716	3
34	43	15	Entry Fragger	2026-05-13 15:44:03.979716	4
35	44	15	Lurker	2026-05-13 15:44:03.979716	14
36	45	15	AWPer	2026-05-13 15:44:03.979716	15
37	46	15	Hard Support	2026-05-13 15:44:03.979716	19
38	47	15	Soft Support	2026-05-13 15:44:03.979716	20
39	48	15	Initiator	2026-05-13 15:44:03.979716	23
40	49	15	Sentinel	2026-05-13 15:44:03.979716	24
41	50	1	Support	2026-05-13 15:44:03.979716	3
42	51	1	Entry Fragger	2026-05-13 15:44:03.979716	4
43	52	1	Lurker	2026-05-13 15:44:03.979716	14
44	53	1	AWPer	2026-05-13 15:44:03.979716	15
45	54	1	Hard Support	2026-05-13 15:44:03.979716	19
46	55	1	Soft Support	2026-05-13 15:44:03.979716	20
47	56	1	Initiator	2026-05-13 15:44:03.979716	23
48	57	1	Sentinel	2026-05-13 15:44:03.979716	24
49	58	3	Support	2026-05-13 15:44:03.979716	3
50	59	3	Entry Fragger	2026-05-13 15:44:03.979716	4
51	60	3	Lurker	2026-05-13 15:44:03.979716	14
52	61	3	AWPer	2026-05-13 15:44:03.979716	15
53	62	3	Hard Support	2026-05-13 15:44:03.979716	19
54	63	3	Soft Support	2026-05-13 15:44:03.979716	20
55	64	3	Initiator	2026-05-13 15:44:03.979716	23
56	65	3	Sentinel	2026-05-13 15:44:03.979716	24
57	66	13	Support	2026-05-13 15:44:03.979716	3
58	67	13	Entry Fragger	2026-05-13 15:44:03.979716	4
59	68	13	Lurker	2026-05-13 15:44:03.979716	14
60	69	13	AWPer	2026-05-13 15:44:03.979716	15
61	70	13	Hard Support	2026-05-13 15:44:03.979716	19
62	71	13	Soft Support	2026-05-13 15:44:03.979716	20
63	72	13	Initiator	2026-05-13 15:44:03.979716	23
64	73	13	Sentinel	2026-05-13 15:44:03.979716	24
65	74	10	Support	2026-05-13 15:44:03.979716	3
66	75	10	Entry Fragger	2026-05-13 15:44:03.979716	4
67	76	10	Lurker	2026-05-13 15:44:03.979716	14
68	77	10	AWPer	2026-05-13 15:44:03.979716	15
69	78	10	Hard Support	2026-05-13 15:44:03.979716	19
70	79	10	Soft Support	2026-05-13 15:44:03.979716	20
71	80	10	Initiator	2026-05-13 15:44:03.979716	23
72	81	10	Sentinel	2026-05-13 15:44:03.979716	24
73	82	16	Support	2026-05-13 15:44:03.979716	3
74	83	16	Entry Fragger	2026-05-13 15:44:03.979716	4
75	84	16	Lurker	2026-05-13 15:44:03.979716	14
76	85	16	AWPer	2026-05-13 15:44:03.979716	15
77	86	16	Hard Support	2026-05-13 15:44:03.979716	19
78	87	16	Soft Support	2026-05-13 15:44:03.979716	20
79	88	16	Initiator	2026-05-13 15:44:03.979716	23
80	89	16	Sentinel	2026-05-13 15:44:03.979716	24
81	90	5	Support	2026-05-13 15:44:03.979716	3
82	91	5	Entry Fragger	2026-05-13 15:44:03.979716	4
83	92	5	Lurker	2026-05-13 15:44:03.979716	14
84	93	5	AWPer	2026-05-13 15:44:03.979716	15
85	94	5	Hard Support	2026-05-13 15:44:03.979716	19
86	95	5	Soft Support	2026-05-13 15:44:03.979716	20
87	96	5	Initiator	2026-05-13 15:44:03.979716	23
88	97	5	Sentinel	2026-05-13 15:44:03.979716	24
89	98	2	Support	2026-05-13 15:44:03.979716	3
90	99	2	Entry Fragger	2026-05-13 15:44:03.979716	4
91	100	2	Lurker	2026-05-13 15:44:03.979716	14
92	101	2	AWPer	2026-05-13 15:44:03.979716	15
93	102	2	Hard Support	2026-05-13 15:44:03.979716	19
94	103	2	Soft Support	2026-05-13 15:44:03.979716	20
95	104	2	Initiator	2026-05-13 15:44:03.979716	23
96	105	2	Sentinel	2026-05-13 15:44:03.979716	24
97	106	4	Support	2026-05-13 15:44:03.979716	3
98	107	4	Entry Fragger	2026-05-13 15:44:03.979716	4
99	108	4	Lurker	2026-05-13 15:44:03.979716	14
100	109	4	AWPer	2026-05-13 15:44:03.979716	15
101	110	4	Hard Support	2026-05-13 15:44:03.979716	19
102	111	4	Soft Support	2026-05-13 15:44:03.979716	20
103	112	4	Initiator	2026-05-13 15:44:03.979716	23
104	113	4	Sentinel	2026-05-13 15:44:03.979716	24
105	114	14	Support	2026-05-13 15:44:03.979716	3
106	115	14	Entry Fragger	2026-05-13 15:44:03.979716	4
107	116	14	Lurker	2026-05-13 15:44:03.979716	14
108	117	14	AWPer	2026-05-13 15:44:03.979716	15
109	118	14	Hard Support	2026-05-13 15:44:03.979716	19
110	119	14	Soft Support	2026-05-13 15:44:03.979716	20
111	120	14	Initiator	2026-05-13 15:44:03.979716	23
112	121	14	Sentinel	2026-05-13 15:44:03.979716	24
113	122	12	Support	2026-05-13 15:44:03.979716	3
114	123	12	Entry Fragger	2026-05-13 15:44:03.979716	4
115	124	12	Lurker	2026-05-13 15:44:03.979716	14
116	125	12	AWPer	2026-05-13 15:44:03.979716	15
117	126	12	Hard Support	2026-05-13 15:44:03.979716	19
118	127	12	Soft Support	2026-05-13 15:44:03.979716	20
119	128	12	Initiator	2026-05-13 15:44:03.979716	23
120	129	12	Sentinel	2026-05-13 15:44:03.979716	24
121	130	7	Support	2026-05-13 15:44:03.979716	3
122	131	7	Entry Fragger	2026-05-13 15:44:03.979716	4
123	132	7	Lurker	2026-05-13 15:44:03.979716	14
124	133	7	AWPer	2026-05-13 15:44:03.979716	15
125	134	7	Hard Support	2026-05-13 15:44:03.979716	19
126	135	7	Soft Support	2026-05-13 15:44:03.979716	20
127	136	7	Initiator	2026-05-13 15:44:03.979716	23
128	137	7	Sentinel	2026-05-13 15:44:03.979716	24
129	138	9	Support	2026-05-13 15:44:03.979716	3
130	139	9	Entry Fragger	2026-05-13 15:44:03.979716	4
131	140	9	Lurker	2026-05-13 15:44:03.979716	14
132	141	9	AWPer	2026-05-13 15:44:03.979716	15
133	142	9	Hard Support	2026-05-13 15:44:03.979716	19
134	143	9	Soft Support	2026-05-13 15:44:03.979716	20
135	144	9	Initiator	2026-05-13 15:44:03.979716	23
136	145	9	Sentinel	2026-05-13 15:44:03.979716	24
137	146	8	Support	2026-05-13 15:44:03.979716	3
138	147	8	Entry Fragger	2026-05-13 15:44:03.979716	4
139	148	8	Lurker	2026-05-13 15:44:03.979716	14
140	149	8	AWPer	2026-05-13 15:44:03.979716	15
141	150	8	Hard Support	2026-05-13 15:44:03.979716	19
142	151	8	Soft Support	2026-05-13 15:44:03.979716	20
143	152	8	Initiator	2026-05-13 15:44:03.979716	23
144	153	8	Sentinel	2026-05-13 15:44:03.979716	24
145	154	11	Support	2026-05-13 15:44:03.979716	3
146	155	11	Entry Fragger	2026-05-13 15:44:03.979716	4
147	156	11	Lurker	2026-05-13 15:44:03.979716	14
148	157	11	AWPer	2026-05-13 15:44:03.979716	15
149	158	11	Hard Support	2026-05-13 15:44:03.979716	19
150	159	11	Soft Support	2026-05-13 15:44:03.979716	20
151	160	11	Initiator	2026-05-13 15:44:03.979716	23
152	161	11	Sentinel	2026-05-13 15:44:03.979716	24
153	162	6	Support	2026-05-13 15:44:03.979716	3
154	163	6	Entry Fragger	2026-05-13 15:44:03.979716	4
155	164	6	Lurker	2026-05-13 15:44:03.979716	14
156	165	6	AWPer	2026-05-13 15:44:03.979716	15
157	166	6	Hard Support	2026-05-13 15:44:03.979716	19
158	167	6	Soft Support	2026-05-13 15:44:03.979716	20
159	168	6	Initiator	2026-05-13 15:44:03.979716	23
160	169	6	Sentinel	2026-05-13 15:44:03.979716	24
\.


--
-- Data for Name: tournament; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tournament (id, name, game_type_id, start_date, end_date, prize_pool, status) FROM stdin;
1	Global Open 2026	1	2026-08-01	2026-08-12	100000.00	finished
2	Winter Clash 2026	1	2026-01-10	2026-01-20	80000.00	finished
3	Dota Masters 2026	2	2026-04-02	2026-04-16	120000.00	finished
4	Spring Arena 2026	3	2026-03-05	2026-03-15	60000.00	finished
5	DotaTournament2026	2	2026-05-15	2026-05-17	100000.00	finished
\.


--
-- Data for Name: tournamentroster; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tournamentroster (id, tournament_id, team_id, user_id, role_id, joined_at) FROM stdin;
1	1	16	34	6	2026-05-13 15:31:28.447217
2	1	16	33	3	2026-05-13 15:31:28.447217
3	1	15	31	4	2026-05-13 15:31:28.447217
4	1	15	17	1	2026-05-13 15:31:28.447217
5	1	13	30	3	2026-05-13 15:31:28.447217
6	1	13	15	15	2026-05-13 15:31:28.447217
7	1	1	18	4	2026-05-13 15:31:28.447217
8	1	1	2	15	2026-05-13 15:31:28.447217
9	2	14	32	1	2026-05-13 15:31:28.447217
10	2	14	16	14	2026-05-13 15:31:28.447217
11	2	13	30	3	2026-05-13 15:31:28.447217
12	2	13	15	15	2026-05-13 15:31:28.447217
13	2	6	23	25	2026-05-13 15:31:28.447217
14	2	6	7	2	2026-05-13 15:31:28.447217
15	2	5	22	20	2026-05-13 15:31:28.447217
16	2	5	6	14	2026-05-13 15:31:28.447217
17	2	4	21	19	2026-05-13 15:31:28.447217
18	2	4	5	3	2026-05-13 15:31:28.447217
19	2	3	20	7	2026-05-13 15:31:28.447217
20	2	3	4	4	2026-05-13 15:31:28.447217
21	2	2	19	6	2026-05-13 15:31:28.447217
22	2	2	3	1	2026-05-13 15:31:28.447217
23	2	1	18	4	2026-05-13 15:31:28.447217
24	2	1	2	15	2026-05-13 15:31:28.447217
25	3	12	29	8	2026-05-13 15:31:28.447217
26	3	12	13	4	2026-05-13 15:31:28.447217
27	3	11	28	23	2026-05-13 15:31:28.447217
28	3	11	12	1	2026-05-13 15:31:28.447217
29	3	6	23	25	2026-05-13 15:31:28.447217
30	3	6	7	2	2026-05-13 15:31:28.447217
31	3	5	22	20	2026-05-13 15:31:28.447217
32	3	5	6	14	2026-05-13 15:31:28.447217
33	3	4	21	19	2026-05-13 15:31:28.447217
34	3	4	5	3	2026-05-13 15:31:28.447217
35	3	3	20	7	2026-05-13 15:31:28.447217
36	3	3	4	4	2026-05-13 15:31:28.447217
37	3	2	19	6	2026-05-13 15:31:28.447217
38	3	2	3	1	2026-05-13 15:31:28.447217
39	3	1	18	4	2026-05-13 15:31:28.447217
40	3	1	2	15	2026-05-13 15:31:28.447217
41	4	10	27	9	2026-05-13 15:31:28.447217
42	4	10	11	24	2026-05-13 15:31:28.447217
43	4	9	26	24	2026-05-13 15:31:28.447217
44	4	9	10	9	2026-05-13 15:31:28.447217
45	4	8	25	27	2026-05-13 15:31:28.447217
46	4	8	9	23	2026-05-13 15:31:28.447217
48	4	7	8	8	2026-05-13 15:31:28.447217
51	2	2	104	2	2026-05-13 15:50:44.466902
53	2	2	99	3	2026-05-13 15:50:58.385977
56	2	1	52	27	2026-05-13 16:18:42.418928
57	2	3	58	27	2026-05-13 16:18:46.410994
58	2	3	59	27	2026-05-13 16:18:47.396608
59	2	3	60	27	2026-05-13 16:18:48.43695
60	2	4	106	27	2026-05-13 16:18:50.941019
61	2	4	107	27	2026-05-13 16:18:51.679355
62	2	4	108	27	2026-05-13 16:18:52.323061
63	2	5	90	27	2026-05-13 16:18:56.155259
64	2	5	91	27	2026-05-13 16:18:56.847093
65	2	5	92	27	2026-05-13 16:18:57.581008
66	2	6	162	27	2026-05-13 16:19:00.944813
67	2	6	163	27	2026-05-13 16:19:01.575779
68	2	6	164	27	2026-05-13 16:19:02.247957
69	2	13	66	27	2026-05-13 16:19:05.10847
70	2	13	67	27	2026-05-13 16:19:05.738791
71	2	13	68	27	2026-05-13 16:19:06.361299
72	2	14	114	27	2026-05-13 16:19:08.488009
73	2	14	115	27	2026-05-13 16:19:09.161463
74	2	14	116	27	2026-05-13 16:19:10.378258
75	2	1	54	27	2026-05-13 19:21:09.682536
76	2	1	53	27	2026-05-13 23:59:21.782413
77	2	2	98	27	2026-05-16 23:42:30.07275
78	5	5	6	27	2026-05-17 13:16:19.323224
79	5	5	90	27	2026-05-17 13:16:20.616866
80	5	5	91	27	2026-05-17 13:16:21.33845
81	5	5	92	27	2026-05-17 13:16:22.031279
82	5	5	93	27	2026-05-17 13:16:22.764345
83	5	2	19	27	2026-05-17 13:16:26.327959
84	5	2	3	27	2026-05-17 13:16:27.060578
85	5	2	98	27	2026-05-17 13:16:27.809987
86	5	2	99	27	2026-05-17 13:16:28.502456
87	5	2	100	27	2026-05-17 13:16:29.289561
88	5	15	31	27	2026-05-17 13:16:32.237053
89	5	15	17	27	2026-05-17 13:16:32.889024
90	5	15	42	27	2026-05-17 13:16:33.571098
91	5	15	43	27	2026-05-17 13:16:34.243527
92	5	15	44	27	2026-05-17 13:16:34.993892
93	5	8	25	27	2026-05-17 13:16:37.654739
94	5	8	9	27	2026-05-17 13:16:38.300784
95	5	8	146	27	2026-05-17 13:16:38.899714
96	5	8	147	27	2026-05-17 13:16:39.4804
97	5	8	148	27	2026-05-17 13:16:40.083477
98	1	1	50	3	2026-05-17 18:49:42.585484
99	1	1	51	4	2026-05-17 18:49:42.585484
100	1	1	52	14	2026-05-17 18:49:42.585484
101	1	13	66	3	2026-05-17 18:49:42.585484
102	1	13	67	4	2026-05-17 18:49:42.585484
103	1	13	68	14	2026-05-17 18:49:42.585484
104	1	15	42	3	2026-05-17 18:49:42.585484
105	1	15	43	4	2026-05-17 18:49:42.585484
106	1	15	44	14	2026-05-17 18:49:42.585484
107	1	16	82	3	2026-05-17 18:49:42.585484
108	1	16	83	4	2026-05-17 18:49:42.585484
109	1	16	84	14	2026-05-17 18:49:42.585484
110	3	1	50	3	2026-05-17 18:49:42.585484
111	3	1	51	4	2026-05-17 18:49:42.585484
112	3	1	52	14	2026-05-17 18:49:42.585484
113	3	2	98	3	2026-05-17 18:49:42.585484
114	3	2	99	4	2026-05-17 18:49:42.585484
115	3	2	100	14	2026-05-17 18:49:42.585484
116	3	3	58	3	2026-05-17 18:49:42.585484
117	3	3	59	4	2026-05-17 18:49:42.585484
118	3	3	60	14	2026-05-17 18:49:42.585484
119	3	4	106	3	2026-05-17 18:49:42.585484
120	3	4	107	4	2026-05-17 18:49:42.585484
121	3	4	108	14	2026-05-17 18:49:42.585484
122	3	5	90	3	2026-05-17 18:49:42.585484
123	3	5	91	4	2026-05-17 18:49:42.585484
124	3	5	92	14	2026-05-17 18:49:42.585484
125	3	6	162	3	2026-05-17 18:49:42.585484
126	3	6	163	4	2026-05-17 18:49:42.585484
127	3	6	164	14	2026-05-17 18:49:42.585484
128	3	11	154	3	2026-05-17 18:49:42.585484
129	3	11	155	4	2026-05-17 18:49:42.585484
130	3	11	156	14	2026-05-17 18:49:42.585484
131	3	12	122	3	2026-05-17 18:49:42.585484
132	3	12	123	4	2026-05-17 18:49:42.585484
133	3	12	124	14	2026-05-17 18:49:42.585484
134	4	7	130	3	2026-05-17 18:49:42.585484
135	4	7	131	4	2026-05-17 18:49:42.585484
136	4	7	132	14	2026-05-17 18:49:42.585484
137	4	7	133	15	2026-05-17 18:49:42.585484
138	4	8	146	3	2026-05-17 18:49:42.585484
139	4	8	147	4	2026-05-17 18:49:42.585484
140	4	8	148	14	2026-05-17 18:49:42.585484
141	4	9	138	3	2026-05-17 18:49:42.585484
142	4	9	139	4	2026-05-17 18:49:42.585484
143	4	9	140	14	2026-05-17 18:49:42.585484
144	4	10	74	3	2026-05-17 18:49:42.585484
145	4	10	75	4	2026-05-17 18:49:42.585484
146	4	10	76	14	2026-05-17 18:49:42.585484
\.


--
-- Data for Name: tournamentstage; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tournamentstage (id, tournament_id, name, stage_order) FROM stdin;
5	2	Р§РµС‚РІРµСЂС‚СЊС„РёРЅР°Р»	1
4	2	РџРѕР»СѓС„РёРЅР°Р»	2
3	2	Р¤РёРЅР°Р»	3
8	3	Р§РµС‚РІРµСЂС‚СЊС„РёРЅР°Р»	1
7	3	РџРѕР»СѓС„РёРЅР°Р»	2
6	3	Р¤РёРЅР°Р»	3
13	1	РџРѕР»СѓС„РёРЅР°Р»	1
14	1	Р¤РёРЅР°Р»	2
17	4	РџРѕР»СѓС„РёРЅР°Р»	1
18	4	Р¤РёРЅР°Р»	2
19	5	РџРѕР»СѓС„РёРЅР°Р»	1
20	5	Р¤РёРЅР°Р»	2
\.


--
-- Data for Name: tournamentteam; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tournamentteam (id, tournament_id, team_id, seed, final_place) FROM stdin;
4	1	1	1	1
3	1	13	2	2
2	1	15	3	3
1	1	16	4	3
12	2	1	1	1
11	2	2	2	2
10	2	3	3	3
9	2	4	4	3
6	2	13	7	5
8	2	5	5	5
5	2	14	8	5
7	2	6	6	5
20	3	1	1	1
18	3	3	3	2
19	3	2	2	3
17	3	4	4	3
16	3	5	5	5
13	3	12	8	5
14	3	11	7	5
15	3	6	6	5
24	4	7	1	1
23	4	8	2	2
21	4	10	4	3
22	4	9	3	3
28	5	2	2	1
25	5	5	1	2
29	5	15	3	3
30	5	8	4	3
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, nickname, country, rating, role, password_hash) FROM stdin;
2	s1lent	Russia	2190	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
3	northwind	Sweden	2110	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
4	kobalt	Germany	2060	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
5	phoenixx	France	2025	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
6	raptor	Spain	1980	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
7	rookie	Poland	1915	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
8	spark	South Korea	2260	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
9	ronin	Japan	2175	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
10	dragonfly	China	2140	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
11	stormy	Turkey	1995	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
12	lynx	Canada	2045	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
13	pulse	Brazil	2080	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
14	condor	Peru	1870	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
15	frost	Finland	2010	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
16	arrow	Czech Republic	1935	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
17	nomad	Kazakhstan	1890	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
18	carryon	Russia	2180	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
19	midnight	Sweden	2150	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
20	offbeat	Germany	2075	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
21	warder	France	2030	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
22	softie	Spain	1990	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
23	toplane	Poland	1975	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
24	jungle	South Korea	2225	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
25	marksman	Japan	2135	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
26	sentinelx	China	2105	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
27	binder	Turkey	1965	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
28	haven	Canada	2050	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
29	lotus	Brazil	2095	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
30	ancient	Finland	2005	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
31	inferno	Kazakhstan	1885	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
32	blitz	Denmark	1970	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
33	vector	USA	2035	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
34	orbit	UK	1998	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
35	zenith	Norway	2015	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
36	shadow	Ukraine	1940	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
37	ember	Serbia	1920	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
38	nova	Italy	1905	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
39	vortex	Netherlands	1988	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
40	falcon	UAE	1875	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
41	echo	Romania	1860	player	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
1	admin	Russia	0	admin	$2y$10$qKEYh1MhJ58eAcXHogqIGuwrlj8vRJcOIytbYQsFAH/4.J22U.wd2
42	nomad_reserve_03	Kazakhstan	1555	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
43	nomad_reserve_04	Kazakhstan	1565	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
44	nomad_reserve_05	Kazakhstan	1525	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
45	nomad_reserve_06	Kazakhstan	1535	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
46	nomad_reserve_07	Kazakhstan	1545	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
47	nomad_reserve_08	Kazakhstan	1555	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
48	nomad_reserve_09	Kazakhstan	1565	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
49	nomad_reserve_10	Kazakhstan	1525	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
50	aurora_reserve_03	Russia	1850	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
51	aurora_reserve_04	Russia	1860	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
52	aurora_reserve_05	Russia	1820	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
53	aurora_reserve_06	Russia	1830	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
54	aurora_reserve_07	Russia	1840	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
55	aurora_reserve_08	Russia	1850	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
56	aurora_reserve_09	Russia	1860	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
57	aurora_reserve_10	Russia	1820	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
58	titan_reserve_03	Germany	1720	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
59	titan_reserve_04	Germany	1730	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
60	titan_reserve_05	Germany	1690	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
61	titan_reserve_06	Germany	1700	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
62	titan_reserve_07	Germany	1710	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
63	titan_reserve_08	Germany	1720	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
64	titan_reserve_09	Germany	1730	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
65	titan_reserve_10	Germany	1690	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
66	frost_reserve_03	Finland	1680	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
67	frost_reserve_04	Finland	1690	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
68	frost_reserve_05	Finland	1650	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
69	frost_reserve_06	Finland	1660	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
70	frost_reserve_07	Finland	1670	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
71	frost_reserve_08	Finland	1680	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
72	frost_reserve_09	Finland	1690	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
73	frost_reserve_10	Finland	1650	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
74	storm_reserve_03	Turkey	1660	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
75	storm_reserve_04	Turkey	1670	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
76	storm_reserve_05	Turkey	1630	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
77	storm_reserve_06	Turkey	1640	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
78	storm_reserve_07	Turkey	1650	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
79	storm_reserve_08	Turkey	1660	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
80	storm_reserve_09	Turkey	1670	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
81	storm_reserve_10	Turkey	1630	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
82	orbit_reserve_03	United Kingdom	1625	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
83	orbit_reserve_04	United Kingdom	1635	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
84	orbit_reserve_05	United Kingdom	1595	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
85	orbit_reserve_06	United Kingdom	1605	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
86	orbit_reserve_07	United Kingdom	1615	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
87	orbit_reserve_08	United Kingdom	1625	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
88	orbit_reserve_09	United Kingdom	1635	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
89	orbit_reserve_10	United Kingdom	1595	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
90	raptor_reserve_03	Spain	1640	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
91	raptor_reserve_04	Spain	1650	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
92	raptor_reserve_05	Spain	1610	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
93	raptor_reserve_06	Spain	1620	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
94	raptor_reserve_07	Spain	1630	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
95	raptor_reserve_08	Spain	1640	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
96	raptor_reserve_09	Spain	1650	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
97	raptor_reserve_10	Spain	1610	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
98	wolf_reserve_03	Sweden	1775	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
99	wolf_reserve_04	Sweden	1785	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
100	wolf_reserve_05	Sweden	1745	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
101	wolf_reserve_06	Sweden	1755	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
102	wolf_reserve_07	Sweden	1765	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
103	wolf_reserve_08	Sweden	1775	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
104	wolf_reserve_09	Sweden	1785	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
105	wolf_reserve_10	Sweden	1745	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
106	phoenix_reserve_03	France	1695	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
107	phoenix_reserve_04	France	1705	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
108	phoenix_reserve_05	France	1665	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
109	phoenix_reserve_06	France	1675	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
110	phoenix_reserve_07	France	1685	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
111	phoenix_reserve_08	France	1695	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
112	phoenix_reserve_09	France	1705	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
113	phoenix_reserve_10	France	1665	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
114	arrow_reserve_03	Czech Republic	1590	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
115	arrow_reserve_04	Czech Republic	1600	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
116	arrow_reserve_05	Czech Republic	1560	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
117	arrow_reserve_06	Czech Republic	1570	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
118	arrow_reserve_07	Czech Republic	1580	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
119	arrow_reserve_08	Czech Republic	1590	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
120	arrow_reserve_09	Czech Republic	1600	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
121	arrow_reserve_10	Czech Republic	1560	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
122	pulse_reserve_03	Brazil	1750	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
123	pulse_reserve_04	Brazil	1760	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
124	pulse_reserve_05	Brazil	1720	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
125	pulse_reserve_06	Brazil	1730	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
126	pulse_reserve_07	Brazil	1740	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
127	pulse_reserve_08	Brazil	1750	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
128	pulse_reserve_09	Brazil	1760	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
129	pulse_reserve_10	Brazil	1720	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
130	spark_reserve_03	South Korea	1910	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
131	spark_reserve_04	South Korea	1920	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
132	spark_reserve_05	South Korea	1880	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
133	spark_reserve_06	South Korea	1890	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
134	spark_reserve_07	South Korea	1900	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
135	spark_reserve_08	South Korea	1910	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
136	spark_reserve_09	South Korea	1920	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
137	spark_reserve_10	South Korea	1880	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
138	dragon_reserve_03	China	1805	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
139	dragon_reserve_04	China	1815	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
140	dragon_reserve_05	China	1775	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
141	dragon_reserve_06	China	1785	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
142	dragon_reserve_07	China	1795	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
143	dragon_reserve_08	China	1805	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
144	dragon_reserve_09	China	1815	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
145	dragon_reserve_10	China	1775	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
146	ronin_reserve_03	Japan	1835	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
147	ronin_reserve_04	Japan	1845	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
148	ronin_reserve_05	Japan	1805	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
149	ronin_reserve_06	Japan	1815	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
150	ronin_reserve_07	Japan	1825	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
151	ronin_reserve_08	Japan	1835	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
152	ronin_reserve_09	Japan	1845	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
153	ronin_reserve_10	Japan	1805	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
154	lynx_reserve_03	Canada	1710	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
155	lynx_reserve_04	Canada	1720	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
156	lynx_reserve_05	Canada	1680	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
157	lynx_reserve_06	Canada	1690	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
158	lynx_reserve_07	Canada	1700	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
159	lynx_reserve_08	Canada	1710	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
160	lynx_reserve_09	Canada	1720	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
161	lynx_reserve_10	Canada	1680	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
162	knight_reserve_03	Poland	1615	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
163	knight_reserve_04	Poland	1625	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
164	knight_reserve_05	Poland	1585	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
165	knight_reserve_06	Poland	1595	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
166	knight_reserve_07	Poland	1605	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
167	knight_reserve_08	Poland	1615	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
168	knight_reserve_09	Poland	1625	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
169	knight_reserve_10	Poland	1585	player	$2y$10$Wvu6ZmsB8Gysw2oEa2J0fO9U8AGUspPxM30bCb9AuUVCnMDF8hI9W
170	Milerook	Russia	0	player	$2y$10$V627Pjc3fMB.oxpPbJa2/eAY9oDgHTe/9ejloO14SCVh9ygtkYg1W
\.


--
-- Name: gamemap_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.gamemap_id_seq', 14, true);


--
-- Name: gametype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.gametype_id_seq', 4, true);


--
-- Name: match_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.match_id_seq', 32, true);


--
-- Name: matchgame_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.matchgame_id_seq', 78, true);


--
-- Name: playerrole_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.playerrole_id_seq', 27, true);


--
-- Name: playerstats_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.playerstats_id_seq', 786, true);


--
-- Name: prizedistribution_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.prizedistribution_id_seq', 52, true);


--
-- Name: team_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.team_id_seq', 16, true);


--
-- Name: teamplayer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.teamplayer_id_seq', 160, true);


--
-- Name: tournament_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tournament_id_seq', 5, true);


--
-- Name: tournamentroster_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tournamentroster_id_seq', 146, true);


--
-- Name: tournamentstage_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tournamentstage_id_seq', 20, true);


--
-- Name: tournamentteam_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tournamentteam_id_seq', 30, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 171, true);


--
-- Name: gamemap gamemap_game_type_id_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gamemap
    ADD CONSTRAINT gamemap_game_type_id_name_key UNIQUE (game_type_id, name);


--
-- Name: gamemap gamemap_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gamemap
    ADD CONSTRAINT gamemap_pkey PRIMARY KEY (id);


--
-- Name: gametype gametype_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gametype
    ADD CONSTRAINT gametype_name_key UNIQUE (name);


--
-- Name: gametype gametype_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gametype
    ADD CONSTRAINT gametype_pkey PRIMARY KEY (id);


--
-- Name: match match_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match
    ADD CONSTRAINT match_pkey PRIMARY KEY (id);


--
-- Name: matchgame matchgame_match_id_game_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matchgame
    ADD CONSTRAINT matchgame_match_id_game_number_key UNIQUE (match_id, game_number);


--
-- Name: matchgame matchgame_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matchgame
    ADD CONSTRAINT matchgame_pkey PRIMARY KEY (id);


--
-- Name: playerrole playerrole_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerrole
    ADD CONSTRAINT playerrole_name_key UNIQUE (name);


--
-- Name: playerrole playerrole_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerrole
    ADD CONSTRAINT playerrole_pkey PRIMARY KEY (id);


--
-- Name: playerstats playerstats_match_game_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerstats
    ADD CONSTRAINT playerstats_match_game_id_user_id_key UNIQUE (match_game_id, user_id);


--
-- Name: playerstats playerstats_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerstats
    ADD CONSTRAINT playerstats_pkey PRIMARY KEY (id);


--
-- Name: prizedistribution prizedistribution_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prizedistribution
    ADD CONSTRAINT prizedistribution_pkey PRIMARY KEY (id);


--
-- Name: prizedistribution prizedistribution_tournament_id_place_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prizedistribution
    ADD CONSTRAINT prizedistribution_tournament_id_place_key UNIQUE (tournament_id, place);


--
-- Name: team team_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.team
    ADD CONSTRAINT team_name_key UNIQUE (name);


--
-- Name: team team_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.team
    ADD CONSTRAINT team_pkey PRIMARY KEY (id);


--
-- Name: teamplayer teamplayer_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teamplayer
    ADD CONSTRAINT teamplayer_pkey PRIMARY KEY (id);


--
-- Name: teamplayer teamplayer_user_id_team_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teamplayer
    ADD CONSTRAINT teamplayer_user_id_team_id_key UNIQUE (user_id, team_id);


--
-- Name: tournament tournament_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament
    ADD CONSTRAINT tournament_pkey PRIMARY KEY (id);


--
-- Name: tournamentroster tournamentroster_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_pkey PRIMARY KEY (id);


--
-- Name: tournamentroster tournamentroster_tournament_id_team_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_tournament_id_team_id_user_id_key UNIQUE (tournament_id, team_id, user_id);


--
-- Name: tournamentroster tournamentroster_tournament_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_tournament_id_user_id_key UNIQUE (tournament_id, user_id);


--
-- Name: tournamentstage tournamentstage_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentstage
    ADD CONSTRAINT tournamentstage_pkey PRIMARY KEY (id);


--
-- Name: tournamentstage tournamentstage_tournament_id_stage_order_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentstage
    ADD CONSTRAINT tournamentstage_tournament_id_stage_order_key UNIQUE (tournament_id, stage_order);


--
-- Name: tournamentteam tournamentteam_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentteam
    ADD CONSTRAINT tournamentteam_pkey PRIMARY KEY (id);


--
-- Name: tournamentteam tournamentteam_tournament_id_team_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentteam
    ADD CONSTRAINT tournamentteam_tournament_id_team_id_key UNIQUE (tournament_id, team_id);


--
-- Name: users users_nickname_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_nickname_key UNIQUE (nickname);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: tournamentroster_tournament_team_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tournamentroster_tournament_team_idx ON public.tournamentroster USING btree (tournament_id, team_id);


--
-- Name: tournamentroster_user_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tournamentroster_user_idx ON public.tournamentroster USING btree (user_id);


--
-- Name: gamemap gamemap_game_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gamemap
    ADD CONSTRAINT gamemap_game_type_id_fkey FOREIGN KEY (game_type_id) REFERENCES public.gametype(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: match match_stage_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match
    ADD CONSTRAINT match_stage_id_fkey FOREIGN KEY (stage_id) REFERENCES public.tournamentstage(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: match match_team1_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match
    ADD CONSTRAINT match_team1_id_fkey FOREIGN KEY (team1_id) REFERENCES public.team(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: match match_team2_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match
    ADD CONSTRAINT match_team2_id_fkey FOREIGN KEY (team2_id) REFERENCES public.team(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: match match_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match
    ADD CONSTRAINT match_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournament(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: match match_winner_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match
    ADD CONSTRAINT match_winner_team_id_fkey FOREIGN KEY (winner_team_id) REFERENCES public.team(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: matchgame matchgame_map_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matchgame
    ADD CONSTRAINT matchgame_map_id_fkey FOREIGN KEY (map_id) REFERENCES public.gamemap(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: matchgame matchgame_match_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matchgame
    ADD CONSTRAINT matchgame_match_id_fkey FOREIGN KEY (match_id) REFERENCES public.match(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: matchgame matchgame_winner_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matchgame
    ADD CONSTRAINT matchgame_winner_team_id_fkey FOREIGN KEY (winner_team_id) REFERENCES public.team(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: playerstats playerstats_match_game_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerstats
    ADD CONSTRAINT playerstats_match_game_id_fkey FOREIGN KEY (match_game_id) REFERENCES public.matchgame(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: playerstats playerstats_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.playerstats
    ADD CONSTRAINT playerstats_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: prizedistribution prizedistribution_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prizedistribution
    ADD CONSTRAINT prizedistribution_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournament(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: teamplayer teamplayer_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teamplayer
    ADD CONSTRAINT teamplayer_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.playerrole(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: teamplayer teamplayer_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teamplayer
    ADD CONSTRAINT teamplayer_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.team(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: teamplayer teamplayer_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teamplayer
    ADD CONSTRAINT teamplayer_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tournament tournament_game_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament
    ADD CONSTRAINT tournament_game_type_id_fkey FOREIGN KEY (game_type_id) REFERENCES public.gametype(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tournamentroster tournamentroster_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.playerrole(id) ON DELETE SET NULL;


--
-- Name: tournamentroster tournamentroster_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.team(id) ON DELETE CASCADE;


--
-- Name: tournamentroster tournamentroster_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournament(id) ON DELETE CASCADE;


--
-- Name: tournamentroster tournamentroster_tournament_team_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_tournament_team_fkey FOREIGN KEY (tournament_id, team_id) REFERENCES public.tournamentteam(tournament_id, team_id) ON DELETE CASCADE;


--
-- Name: tournamentroster tournamentroster_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: tournamentroster tournamentroster_user_team_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentroster
    ADD CONSTRAINT tournamentroster_user_team_fkey FOREIGN KEY (user_id, team_id) REFERENCES public.teamplayer(user_id, team_id) ON DELETE CASCADE;


--
-- Name: tournamentstage tournamentstage_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentstage
    ADD CONSTRAINT tournamentstage_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournament(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tournamentteam tournamentteam_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentteam
    ADD CONSTRAINT tournamentteam_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.team(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tournamentteam tournamentteam_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournamentteam
    ADD CONSTRAINT tournamentteam_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournament(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--


