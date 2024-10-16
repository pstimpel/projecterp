

CREATE TABLE public.fuzzer (
    fuzz_id integer NOT NULL,
    fuzzerin text NOT NULL,
    fuzzerout text NOT NULL,
    priority integer NOT NULL
);

CREATE SEQUENCE public.fuzzer_fuzz_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.fuzzer_fuzz_id_seq OWNED BY public.fuzzer.fuzz_id;

CREATE TABLE public.product (
    product_id integer NOT NULL,
    productname text NOT NULL
);

CREATE SEQUENCE public.product_product_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.product_product_id_seq OWNED BY public.product.product_id;

CREATE TABLE public.stock (
    stock_id integer NOT NULL,
    productid integer NOT NULL,
    storageid integer NOT NULL,
    amount double precision NOT NULL,
    ts timestamp without time zone NOT NULL
);

CREATE SEQUENCE public.stock_stock_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.stock_stock_id_seq OWNED BY public.stock.stock_id;

CREATE TABLE public.storage (
    storage_id integer NOT NULL,
    storagelocationid integer NOT NULL,
    storagename text NOT NULL
);

CREATE SEQUENCE public.storage_storage_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.storage_storage_id_seq OWNED BY public.storage.storage_id;

CREATE TABLE public.storagelocation (
    id integer NOT NULL,
    name text NOT NULL,
    description text NOT NULL
);

CREATE SEQUENCE public.storagelocation_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.storagelocation_id_seq OWNED BY public.storagelocation.id;

ALTER TABLE ONLY public.fuzzer ALTER COLUMN fuzz_id SET DEFAULT nextval('public.fuzzer_fuzz_id_seq'::regclass);

ALTER TABLE ONLY public.product ALTER COLUMN product_id SET DEFAULT nextval('public.product_product_id_seq'::regclass);

ALTER TABLE ONLY public.stock ALTER COLUMN stock_id SET DEFAULT nextval('public.stock_stock_id_seq'::regclass);

ALTER TABLE ONLY public.storage ALTER COLUMN storage_id SET DEFAULT nextval('public.storage_storage_id_seq'::regclass);

ALTER TABLE ONLY public.storagelocation ALTER COLUMN id SET DEFAULT nextval('public.storagelocation_id_seq'::regclass);

ALTER TABLE ONLY public.fuzzer
    ADD CONSTRAINT fuzzer_pkey PRIMARY KEY (fuzz_id);

ALTER TABLE ONLY public.product
    ADD CONSTRAINT product_pkey PRIMARY KEY (product_id);

ALTER TABLE ONLY public.stock
    ADD CONSTRAINT stock_pkey PRIMARY KEY (stock_id);

ALTER TABLE ONLY public.storage
    ADD CONSTRAINT storage_pkey PRIMARY KEY (storage_id);

ALTER TABLE ONLY public.storagelocation
    ADD CONSTRAINT storagelocation_pkey PRIMARY KEY (id);