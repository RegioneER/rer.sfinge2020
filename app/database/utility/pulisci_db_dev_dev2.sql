SET FOREIGN_KEY_CHECKS=1;

SELECT CONCAT('truncate table ',table_schema,'.',table_name,';') 
  FROM information_schema.tables 
 WHERE table_schema IN ('devfesr2020')
union
 SELECT CONCAT('truncate table ',table_schema,'.',table_name,';') 
  FROM information_schema.tables 
 WHERE table_schema IN ('sfingefesr2020dev');

SET FOREIGN_KEY_CHECKS = 0;