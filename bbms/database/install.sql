-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - MASTER INSTALL SCRIPT
-- Run this file to set up the complete database
-- MySQL 8+ Compatible
-- ============================================================================

-- Display start message
SELECT '============================================================' AS '';
SELECT 'BBMS Database Installation Started' AS 'Status';
SELECT '============================================================' AS '';

-- Source the schema (tables)
SOURCE database/schema.sql;
SELECT 'Schema created successfully' AS 'Status';

-- Source the views
SOURCE database/views.sql;
SELECT 'Views created successfully' AS 'Status';

-- Source the triggers
SOURCE database/triggers.sql;
SELECT 'Triggers created successfully' AS 'Status';

-- Source the stored procedures
SOURCE database/stored_procedures.sql;
SELECT 'Stored procedures created successfully' AS 'Status';

-- Source the sample data
SOURCE database/sample_data.sql;
SELECT 'Sample data inserted successfully' AS 'Status';

-- Display completion message
SELECT '============================================================' AS '';
SELECT 'BBMS Database Installation Completed Successfully!' AS 'Status';
SELECT '============================================================' AS '';

-- Show summary
SELECT 'Database Summary:' AS '';
SELECT COUNT(*) AS 'Total Tables' FROM information_schema.tables WHERE table_schema = 'bbms' AND table_type = 'BASE TABLE';
SELECT COUNT(*) AS 'Total Views' FROM information_schema.views WHERE table_schema = 'bbms';
SELECT COUNT(*) AS 'Total Triggers' FROM information_schema.triggers WHERE trigger_schema = 'bbms';
SELECT COUNT(*) AS 'Total Procedures' FROM information_schema.routines WHERE routine_schema = 'bbms' AND routine_type = 'PROCEDURE';
