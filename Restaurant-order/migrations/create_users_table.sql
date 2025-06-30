
-- Create sequence with proper name
CREATE SEQUENCE user_id_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

-- Create users table
CREATE TABLE users (
    id NUMBER DEFAULT user_id_seq.NEXTVAL PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    email VARCHAR2(255) NOT NULL UNIQUE,
    password VARCHAR2(255) NOT NULL,
    role VARCHAR2(50) DEFAULT 'user' NOT NULL,
    is_deleted NUMBER(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create trigger for updating the updated_at timestamp
CREATE OR REPLACE TRIGGER users_update_trigger
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    :NEW.updated_at := CURRENT_TIMESTAMP;
END;
/

-- Grant sequence permissions
GRANT SELECT, ALTER ON user_id_seq TO Restaurantdb;
-- Check and recreate email unique constraint
BEGIN
  EXECUTE IMMEDIATE 'ALTER TABLE users DROP CONSTRAINT users_email_unique';
EXCEPTION
  WHEN OTHERS THEN
    IF SQLCODE != -2443 THEN
      RAISE;
    END IF;
END;
/

ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email);

-- Reset sequence to max ID
DECLARE
  v_max_id NUMBER;
BEGIN
  SELECT NVL(MAX(id), 0) + 1 INTO v_max_id FROM users;
  EXECUTE IMMEDIATE 'ALTER SEQUENCE user_id_seq INCREMENT BY ' || v_max_id || ' MINVALUE 0';
  EXECUTE IMMEDIATE 'SELECT user_id_seq.NEXTVAL FROM dual';
  EXECUTE IMMEDIATE 'ALTER SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 0';
END;
/
ALTER TABLE users ADD remember_token VARCHAR2(64);
CREATE INDEX idx_remember_token ON users(remember_token);

-- The sequence user_id_seq is already created above; duplicate creation removed to prevent errors.