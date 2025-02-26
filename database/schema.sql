CREATE TABLE jobs (
    id INT PRIMARY KEY IDENTITY(1,1),
    job_title NVARCHAR(255) NOT NULL,
    company_name NVARCHAR(255) NOT NULL,
    priority VARCHAR(50) NOT NULL,
    status VARCHAR(100) NOT NULL,
    source VARCHAR(100) NOT NULL,
    posting_url NVARCHAR(500) NULL,
    notes TEXT NULL
);