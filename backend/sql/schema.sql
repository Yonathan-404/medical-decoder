CREATE DATABASE IF NOT EXISTS medical_decoder
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE medical_decoder;

CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_profiles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL UNIQUE,
    age         INT,
    gender      ENUM('male', 'female', 'other'),
    height_cm   DECIMAL(5,2),
    weight_kg   DECIMAL(5,2),
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE test_categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    description TEXT,
    icon        VARCHAR(10) DEFAULT '📋'
) ENGINE=InnoDB;

CREATE TABLE lab_tests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    category_id     INT,
    name            VARCHAR(100) NOT NULL,
    unit            VARCHAR(20) NOT NULL,
    who_min         DECIMAL(8,3),       
    who_max         DECIMAL(8,3),       
    who_optimal     DECIMAL(8,3),       
    description     TEXT,               
    warning_low     DECIMAL(8,3),       
    warning_high    DECIMAL(8,3),       
    FOREIGN KEY (category_id) REFERENCES test_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE lab_results (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    test_id         INT NOT NULL,
    value           DECIMAL(8,3) NOT NULL,
    date_recorded   DATE NOT NULL,
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES lab_tests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE interpretations (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    result_id           INT NOT NULL UNIQUE,
    status              ENUM('normal', 'borderline', 'high', 'low', 'critical') NOT NULL,
    status_label        VARCHAR(20) NOT NULL,   
    message             TEXT NOT NULL,          
    recommendation      TEXT NOT NULL,          
    compared_to_who     BOOLEAN DEFAULT TRUE,   
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (result_id) REFERENCES lab_results(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_results_user ON lab_results(user_id);
CREATE INDEX idx_results_date ON lab_results(date_recorded);
CREATE INDEX idx_results_test ON lab_results(test_id);

INSERT INTO test_categories (name, description, icon) VALUES
('Metabolic', 'Tests related to blood sugar, metabolism, and energy regulation', '🩸'),
('Cardiovascular', 'Tests for heart health, blood pressure, and cholesterol', '❤️'),
('Anthropometric', 'Body measurements like height, weight, and BMI', '⚖️');

INSERT INTO lab_tests (category_id, name, unit, who_min, who_max, who_optimal, description, warning_low, warning_high) VALUES

(1, 'Fasting Blood Glucose', 'mmol/L', 3.9, 5.6, 4.5, 
 'Measures blood sugar after 8+ hours of fasting. Key indicator for diabetes risk.', 
 2.8, 7.0),

(1, 'HbA1c', '%', 4.0, 5.7, 5.0,
 'Reflects average blood glucose over 2-3 months. More stable than daily readings.',
 3.5, 6.5),

(2, 'Systolic Blood Pressure', 'mmHg', 90, 120, 115,
 'Top number in BP reading. Measures pressure when heart beats.',
 70, 140),

(2, 'Diastolic Blood Pressure', 'mmHg', 60, 80, 75,
 'Bottom number in BP reading. Measures pressure when heart rests between beats.',
 40, 90),

(2, 'Total Cholesterol', 'mmol/L', 0, 5.2, 4.0,
 'Overall cholesterol level. High levels increase heart disease and stroke risk.',
 0, 6.2),

(2, 'LDL Cholesterol', 'mmol/L', 0, 3.0, 2.0,
 '"Bad" cholesterol that builds up in artery walls. Lower is better.',
 0, 3.9),

(2, 'HDL Cholesterol', 'mmol/L', 1.0, 999, 1.5,
 '"Good" cholesterol that helps remove LDL. Higher is better (no upper WHO limit).',
 0.8, 999),

(3, 'Body Mass Index', 'kg/m²', 18.5, 24.9, 22.0,
 'Calculated from height and weight. Screens for weight categories.',
 16.0, 30.0);