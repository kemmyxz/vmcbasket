CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(50) NOT NULL,
    student_pass VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    student_fname VARCHAR(100) NOT NULL,
    student_lname VARCHAR(100) NOT NULL,
    student_mname VARCHAR(100) NOT NULL,
    email VARCHAR(250) NOT NULL,
    phone_number int(11) NOT NULL,
    year_level VARCHAR(150) ,
    birthday DATE,
    photo VARCHAR(255),
    otp VARCHAR(100),
    otp_expiry DATETIME,
    last_activity TIMESTAMP NULL DEFAULT NULL,
    active_status ENUM('Active', 'Disabled') DEFAULT 'Active'
);

    CREATE TABLE admin_login(
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_name  VARCHAR(100) NOT NULL,
        admin_pass VARCHAR(100) NOT NULL
    );

-- ORDERS TABLE
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    school_id VARCHAR(20) NOT NULL,
    email VARCHAR(255) AFTER school_id,
    phone VARCHAR(15) AFTER email,
    price DECIMAL(10,2) NOT NULL,
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status ENUM('Pending', 'ToPickUp','Complete', 'Cancelled', 'Refunded') NOT NULL DEFAULT 'Pending',
    user_id int NULL,
    product_id int NOT NULL,
    size VARCHAR(20) NULL,
    total_price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    receipt_no VARCHAR(50),
    payment_method ENUM('Cash (Pay at the Counter)', 'Send Online Receipt') NOT NULL DEFAULT 'Cash (Pay at the Counter)',
);

CREATE TABLE order_receipt (
    receipt_id VARCHAR(255) UNIQUE PRIMARY KEY,
    order_status ENUM('Pending', 'ToPickUp', 'Complete', 'Cancelled', 'Refunded') 
    NOT NULL DEFAULT 'Pending'
);

-- SALES TABLE (FOR BESTSELLERS)
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    quantity_sold INT NOT NULL DEFAULT 0,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INQUIRIES TABLE
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    sender ENUM('user', 'admin') DEFAULT 'user',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- REVIEWS TABLE
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    is_anonymous BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)

ALTER TABLE product_reviews
DROP FOREIGN KEY product_reviews_ibfk_1;

ALTER TABLE product_reviews
ADD CONSTRAINT product_reviews_ibfk_1
FOREIGN KEY (user_id) REFERENCES users(id)
ON DELETE CASCADE;

);

CREATE TABLE review_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    dr_number VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    type ENUM('Uniform', 'Supplies') NOT NULL,
    date_modified DATE NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    rating float,
    tags TEXT null,
    max_quantity int

);

CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Unisex') NULL,
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_variant (product_id, size, gender),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  favorite INT DEFAULT 0,  -- 1 for favorited, 0 for not
  FOREIGN KEY (user_id) REFERENCES users(id),  -- Assuming you have a 'users' table
  FOREIGN KEY (product_id) REFERENCES products(id)  -- Assuming you have a 'products' table


ALTER TABLE favorites
DROP FOREIGN KEY favorites_ibfk_1;

ALTER TABLE favorites
ADD CONSTRAINT favorites_ibfk_1
FOREIGN KEY (user_id) REFERENCES users(id)
ON DELETE CASCADE;
);



CREATE TABLE basket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    size VARCHAR(10) DEFAULT NULL,
    quantity INT DEFAULT 1
);

CREATE TABLE order_receipts_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id VARCHAR(255),
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receipt_id) REFERENCES order_receipt(receipt_id)
);

CREATE TABLE restock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    dr_number VARCHAR(50) NOT NULL,
    added_stock INT NOT NULL,
    updated_by VARCHAR(100) NOT NULL,
    restock_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    variant_details VARCHAR(255)

);

CREATE TABLE chat_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);