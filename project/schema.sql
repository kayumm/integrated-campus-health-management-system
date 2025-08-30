CREATE DATABASE ichms;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','doctor','pharmacist','admin') NOT NULL
);

-- Students linked to user
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    dob DATE,
    gender ENUM('M','F','O'),
    department VARCHAR(100),
    program VARCHAR(100),
    year_level INT CHECK (year_level BETWEEN 1 AND 6),
    contact VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Doctors linked to user
CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    contact VARCHAR(100),
    availability TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Pharmacists linked to user
CREATE TABLE pharmacists (
    pharmacist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(100),
    shift VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Drugs
CREATE TABLE drugs (
    drug_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_name VARCHAR(100) NOT NULL,
    brand VARCHAR(100),
    dosage VARCHAR(100),
    stock_qty INT DEFAULT 0 CHECK (stock_qty >= 0),
    expiry_date DATE,
    INDEX idx_name (drug_name)
);

-- Appointments
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    doctor_id INT NOT NULL,
    datetime DATETIME NOT NULL,
    reason VARCHAR(255),
    status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
    INDEX idx_datetime (datetime),
    INDEX idx_status (status)
);

CREATE TABLE prescriptions_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    diagnosis TEXT NOT NULL,
    drug_id INT NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    duration VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (drug_id) REFERENCES drugs(drug_id),
    
    -- Indexes for performance
    INDEX idx_appointment (appointment_id),
    INDEX idx_created (created_at),
    INDEX idx_drug (drug_id)
);


-- sample drugs
INSERT INTO drugs (drug_name, brand, dosage, stock_qty, expiry_date)
VALUES
('Paracetamol', 'Tylenol', '500 mg tablet', 150, '2026-05-15'),
('Ibuprofen', 'Advil', '200 mg tablet', 120, '2026-07-10'),
('Amoxicillin', 'Amoxil', '500 mg capsule', 80, '2026-02-01'),
('Azithromycin', 'Zithromax', '250 mg tablet', 60, '2025-12-20'),
('Metformin', 'Glucophage', '850 mg tablet', 100, '2027-03-05'),
('Atorvastatin', 'Lipitor', '20 mg tablet', 70, '2027-06-12'),
('Losartan', 'Cozaar', '50 mg tablet', 90, '2026-09-18'),
('Omeprazole', 'Prilosec', '20 mg capsule', 110, '2026-11-30'),
('Cetirizine', 'Zyrtec', '10 mg tablet', 130, '2026-04-25'),
('Salbutamol', 'Ventolin', '100 mcg inhaler', 40, '2025-11-01');
