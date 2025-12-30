CREATE TABLE judo_signups (
    id INT PRIMARY KEY AUTO_INCREMENT,

    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20),

    gender VARCHAR(10),
    age INT,
    height_cm REAL,
    weight_kg REAL,
    bmi_value REAL,
    bmi_category VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_email ON judo_signups (email);