-- Drop existing sequence if it exists
BEGIN
    EXECUTE IMMEDIATE 'DROP SEQUENCE product_id_seq';
EXCEPTION
    WHEN OTHERS THEN
        IF SQLCODE != -2289 THEN -- Ignore "sequence does not exist" error
            RAISE;
        END IF;
END;
/

-- Create sequence for products
CREATE SEQUENCE product_id_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

-- Create products table
CREATE TABLE products (
    id NUMBER DEFAULT product_id_seq.NEXTVAL PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    description CLOB,
    price NUMBER(10,2) NOT NULL,
    category VARCHAR2(100),
    image_url VARCHAR2(255),
    is_available NUMBER(1) DEFAULT 1,
    is_deleted NUMBER(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create trigger for updating the updated_at timestamp
CREATE OR REPLACE TRIGGER products_update_trigger
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    :NEW.updated_at := CURRENT_TIMESTAMP;
END;
/

-- Grant sequence permissions
GRANT SELECT, ALTER ON product_id_seq TO Restaurantdb;
