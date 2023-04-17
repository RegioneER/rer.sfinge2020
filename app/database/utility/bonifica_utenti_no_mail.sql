SELECT p.`email_principale` FROM `utenti` u 
join `persone` p on u.`persona_id` = p.id 
WHERE `email_canonical` IS NULL 

update utenti u 
join `persone` p on u.`persona_id` = p.id 
set email = p.`email_principale` ,
email_canonical = p.`email_principale` 
WHERE `email_canonical` IS NULL

