
-- Table: document (d)
CREATE OR REPLACE TABLE document (
  d_id VARCHAR2(128) NOT NULL,
  d_revision VARCHAR2(128) NOT NULL,
  d_document  CLOB NOT NULL,
  changed DATETIME DEFAULT SYSDATE,
  PRIMARY KEY (d_id, d_revision)
);

-- Table: document_update (du)
CREATE TABLE IF NOT EXISTS document_update (
  du_sequence INT NOT NULL,
  d_id VARCHAR2(128) NOT NULL,
  d_revision VARCHAR2(128) NOT NULL,
  changed DATETIME DEFAULT SYSDATE,
  PRIMARY KEY (du_sequence),
  KEY (d_id, d_revision)
);

-- Table: revision (r)
CREATE TABLE IF NOT EXISTS revision (
  r_id VARCHAR2(128) NOT NULL,
  r_revision  CLOB NOT NULL,
  changed TIMESTAMP DEFAULT SYSDATE,
  PRIMARY KEY (r_id)
);
