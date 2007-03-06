--
-- Selected TOC Entries:
--
--
-- TOC Entry ID 2 (OID 29407739)
--
-- Name: kernel_traffic Type: TABLE Owner: www
--

CREATE TABLE "kernel_traffic" (
	"kt_id" serial unique,
	"kt_data" text,
	CONSTRAINT "kernel_traffic_pkey" PRIMARY KEY ("kt_id")
);

