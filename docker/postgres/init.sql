CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
SET timezone = 'America/Sao_Paulo';

-- Database para n8n
CREATE DATABASE n8n;

-- Schema para n8n (dentro do DB n8n)
\c n8n
CREATE SCHEMA IF NOT EXISTS n8n;
GRANT ALL PRIVILEGES ON SCHEMA n8n TO deliverypro;
