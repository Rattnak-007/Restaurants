
-- Create sequences with proper names
CREATE SEQUENCE order_id_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE order_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

-- Create orders table
CREATE TABLE orders (
    id NUMBER DEFAULT order_id_seq.NEXTVAL PRIMARY KEY,
    user_id NUMBER NOT NULL,
    total_amount NUMBER(10,2) NOT NULL,
    status VARCHAR2(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create order items table
CREATE TABLE order_items (
    id NUMBER DEFAULT order_item_id_seq.NEXTVAL PRIMARY KEY,
    order_id NUMBER NOT NULL,
    product_id NUMBER NOT NULL,
    quantity NUMBER DEFAULT 1,
    price NUMBER(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Grant sequence permissions
GRANT SELECT, ALTER ON order_id_seq TO Restaurantdb;
GRANT SELECT, ALTER ON order_item_id_seq TO Restaurantdb;
