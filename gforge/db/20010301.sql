--
-- Selected TOC Entries:
--
\connect - www
--
-- TOC Entry ID 2 (OID 29407739)
--
-- Name: kernel_traffic Type: TABLE Owner: www
--

CREATE TABLE "kernel_traffic" (
	"kt_id" serial primary key,
	"kt_data" text,
	CONSTRAINT "kernel_traffic_pkey" PRIMARY KEY ("kt_id")
);

