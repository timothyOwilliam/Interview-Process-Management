-- ============================================================
-- Interview Process Management - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS interview_management_db;
USE interview_management_db;

-- ============================================================
-- Table: applicants
-- ============================================================
CREATE TABLE IF NOT EXISTS applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    resume_link VARCHAR(255),
    position VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: sessions
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    zoom_link VARCHAR(255),
    scheduled_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE
);

-- ============================================================
-- Table: scores
-- ============================================================
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    applicant_name VARCHAR(100) NOT NULL,
    technical_skills INT DEFAULT 0,
    communication INT DEFAULT 0,
    confidence INT DEFAULT 0,
    work_ethic INT DEFAULT 0,
    team_fit INT DEFAULT 0,
    total INT DEFAULT 0,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- ============================================================
-- Table: employees
-- ============================================================
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(50),
    role VARCHAR(50),
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
