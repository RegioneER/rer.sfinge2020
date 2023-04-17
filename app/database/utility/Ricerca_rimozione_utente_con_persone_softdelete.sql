select p.* from `persone` p join utenti u on u.persona_id = p.id where p.data_cancellazione is not null 

select u.* from `persone` p join utenti u on u.persona_id = p.id where p.data_cancellazione is not null 

# poi va messo a null il persona_id nelle righe corrispondeti che risultano dalla seconda query
# ovviamente è una pezza in attesa di pote sviluppare qualcosa di funzionale lato codice