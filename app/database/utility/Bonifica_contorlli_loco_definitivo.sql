-- Camp. 1 ICT 2014-2020
INSERT INTO `controlli_progetti` (`esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (209607,209159,210510,194083,108329,121363,194264,206514,100048,225470,167091,224797,222210,88868,72576,72671,61222,149982,178268,221596,228502,124199,149975);

-- Camp.2 val.ris.artist.cult.amb.
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where rich.id in (48,614,500,521,592,593,594,553,566);


-- Camp.5 Exp. imp.non exp
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (616784,274804,633362,642314,403860,526228,453783,642326,394456,485088,642817,269173,634318,269201,269066,642217,269159,642241,421674,269251,279563,642280,634306,642152,446209,642155,380836,642150) and prot.anno_pg = '2016';


-- Camp.6 Ricerca Imprese
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join istruttorie_richieste prot on prot.richiesta_id = rich.id where prot.codice_cup in('E78C15000200007','E48C15000360007','E28C15000180007','E18C15000310007','E48C15000370007','E98C15000190007','E48I15000130007','E88C15000170007','E28C15000220007','E38C15000180007','E78C15000220007','E78C15000260007',
'E88I15000090007','E98C15000210007','E38C15000310007','E88C15000220007','E78I15000180007','E48C15000410007','E88C15000270007','E98C15000250007','E58C15000210007','E18C15000350007','E28C15000210007','E38C15000280007','E88C15000320007');


-- camp 8 Lab.Ricerca
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join istruttorie_richieste prot on prot.richiesta_id = rich.id where prot.codice_cup in('E38I16000140007', 'J32F16001330005');


-- Camp.9 Slitt.grad. 773
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (803589,800535,802065,802298,794599,803462,794616,803171,803220);


-- Camp. 10 Ser.Inn.Imp. 
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (663162,665158,667788,667884,664924,667842,667747,667786,667814,667806,667710,
667690,667535,660122,666071,665114,667637,661623,667503,667330,664484,667492,664323,665136,667746) and prot.anno_pg = '2016';


-- turismo di giuseppe
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (128165,129955,87780,122634,83286,109580,134091,127305,122998,117172,136014,131160,94167,134030,127420,123015,40450,94200,133858,
131677,113443,116916,127301,113539,127398,113356,15697,135087,123018,131772,123020,130762,108940,119007,129925) and prot.anno_pg = '2017';


-- collina bando 774 camp 13/2018 verbale slittamento
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (737636,731524,731520,731074,737624) and prot.anno_pg = '2015';


-- collina verbale 10 2017
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (660130,663162,665158,667788,667884,
667825,667842,667747,667786,667814,667806,667710,667690,667535,660122,666071,665114,667637,661623,667503,667330,664484,667492,664323,665136,
667627,663103,665119) and prot.anno_pg = '2016';


-- collina verbale 12 12 2017
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (351626,351683,351595,351520,351590,357753,406505,351690,
406652,406441,406656,351694,398289,402028,380449,351533,362010,351647,357763,351604,362000,351453,351482,406591,406445) and prot.anno_pg = '2017';


-- bando startup 2016
INSERT INTO `controlli_progetti` ( `esito`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (633380,271958,642226,642290,626483,642203,509696,642221,648475,376537,318906,269220)

-- bando DGR 2017-0300
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (406604,406621,406639) and prot.anno_pg = '2017';

-- bando DGR 1339/2017
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (694249,672050,688656,695070,692455,691198,695066,694707,694979,689136,694653,694378,694734,691525,
695064,686379,694666,694574,694999,694726,694236,691254,694965,691474,694296) and prot.anno_pg = '2017';


-- bando DGR 2018-1571
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in (607590,607619,607637,607649,609819,614226,638271,650248,650299,650405,653111,607612,
607631,607763,607777,608052,608623,610294,631112,650443,653125,653389,642001) and prot.anno_pg = '2017';


-- bando DGR 2015-0807
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in ('193938','519545','243990_1','243990_2','242987_1','242987_2','242987_3') and prot.anno_pg = '2016';

INSERT INTO `controlli_procedure` ( `procedura_id`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	( 64, NULL, now(), now(), NULL, NULL);

-- bando DGR 451-2017
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(361997,368376,368384,380576,380664,406567,408255,410233,424529,432920,433027,433041,437077,438704,439085,442321,
442327,442885,442917,453269,453271,453316,453333,453573,453644,453688,453706,453811,605095,606638,606656,614584,
618389,620356,622618,623431,638113,642809,650190,650275,650336,653094,654140,655800,656254,656264,656371,656380,656690,656693,656703,656715)
and prot.anno_pg = '2017';

-- bando 66 
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(435924,449815,431395,578925,443270,
386150,453556,409376,389230,579788,443497,577832,422131,402843,453705,437587,453482,397304,452876,574257,450619,413795,
444805,407961,386140,444911,574356,402832,454507,443222,576805,452189,444810,448454,374365,447758,393917,421479,427864)
and prot.anno_pg = '2018';

-- bando 61 
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(438484,452795,467372,500726,487207,445678,443306,456524,
446407,431023,492879,499540,449672,497538,452759,443235,483900,455815,505874,437580,435833,
473686,499544,412620,505891,443245,465248,463626,454538,469990,503978,412948,449053,436102,
482524,436080,482809,501298,500036,435841,423252,505915,412987,445845,479436,502985,486969)
and prot.anno_pg = '2018';


-- bando 452
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(442753,360245,368363,437002,398235,437005,368362,437283,443513,436961,453792)
and prot.anno_pg = '2017';

-- bando id 62
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(528201,518775,538148,529352,514715,531216)
and prot.anno_pg = '2018';


-- bando id 69
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(457685,482773,471934,472535,454643,519455,508933,501884,518805,
514058,514053,458365,518443,519179,519152,494550,516196,494546,455424,453477,496566,
518120,493252,506021,518732,452689,467433,475589,516314,485192,516794,491845,452638,
454545,452162,454466,508546,518684,518668,518313,470655,518658,518770,513760,456564)
and prot.anno_pg = '2018';


-- bando id 68
INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(719402,577607,699260,531210,579499,579728,574337)
and prot.anno_pg = '2018';

INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(461149,461121,355339,461230,355393,354841,460978,461243,461394,
354515,461466,461033,354829,461108,355313,461004,461353,354587,354655,354744,461022,461269,
461330,354813,461371,461064,355388,461026,461114,354567,354616,462352,460982,461437,354908)
and prot.anno_pg = '2019';


INSERT INTO `controlli_progetti` ( `esito_id`, `note`, `data_esito`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `data_inizio_controlli`, `richiesta_id`)
select  NULL, NULL, NULL, NULL, now(), NULL, NULL, NULL, NULL, rich.id from richieste rich
join richieste_protocollo prot on prot.richiesta_id = rich.id
where prot.num_pg in 
(473796,472907,600334,591003,590946,591852,590476,558227,473800,474357,517953,472426,592876,473805,590274,473824,589341,533684)
and prot.anno_pg = '2019';