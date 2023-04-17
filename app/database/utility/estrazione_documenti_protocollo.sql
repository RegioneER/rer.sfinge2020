set @procedura = 15;

-- PRESENTAZIONE

select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Presentazione/') as 'Path output' 

from richieste_protocollo protocollo
join richieste_protocollo_documenti doc 
on doc.richiesta_protocollo_id = protocollo.id
join richieste r 
on r.id = protocollo.richiesta_id
where r.procedura_id = @procedura 
and r.data_cancellazione is null 
and protocollo.data_cancellazione is null 
and doc.data_cancellazione is null 
and protocollo.tipo = 'FINANZIAMENTO'
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- PAGAMENTI
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Pagamento/') as `Path output`
		
from richieste_protocollo as protocollo
join pagamenti as p
on p.id = protocollo.`pagamento_id`
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)


UNION
-- VARIAZIONI
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Variazione/') as `Path output`
		
from richieste_protocollo as protocollo

join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id

join variazioni_richieste as v
on v.id = protocollo.`variazione_id`
join attuazione_controllo_richieste as atc
on atc.id = v.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`

where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- PROROGHE
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Proroga/') as `Path output`
		
from richieste_protocollo as protocollo

join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id

join proroghe as p
on p.id = protocollo.`proroga_id`
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`

where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION

-- INTEGRAZIONE ISTRUTTORIA
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Integrazione_istruttoria/') as `Path output`
		
from richieste_protocollo as protocollo

join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id

join integrazioni_istruttorie as ii
on ii.id = protocollo.`integrazione_id`
join istruttorie_richieste as i
on i.id = ii.`istruttoria_id`
join richieste as r
on r.id = i.`richiesta_id`

where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Risposta integrazione istruttoria

select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Risposta_integrazione_istruttoria/') as `Path output`
		
from richieste_protocollo as protocollo

join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id

join risposte_integrazioni as ri
on ri.id = protocollo.`risposta_integrazione_id`
join integrazioni_istruttorie as ii
on ii.id = ri.`integrazione_id`
join istruttorie_richieste as i
on i.id = ii.`istruttoria_id`
join richieste as r
on r.id = i.`richiesta_id`

where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Integrazione pagamento

select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Integrazione_pagamento/') as `Path output`
		
from richieste_protocollo as protocollo
join integrazioni_pagamenti as ip
on ip.id = protocollo.integrazione_pagamento_id
join pagamenti as p
on p.id = ip.`pagamento_id`
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Risposta integrazione pagamento

select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Risposta_integrazione_pagamento/') as `Path output`
		
from richieste_protocollo as protocollo
join  risposte_integrazioni_pagamenti as rip
on rip.id = protocollo.risposta_integrazione_pagamento_id
join integrazioni_pagamenti as ip
on ip.id = rip.integrazione_id
join pagamenti as p
on p.id = ip.`pagamento_id`
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION

-- Comunicazione esito
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Comunicazione_esito_istruttoria/') as `Path output`
		
from richieste_protocollo as protocollo
join comunicazioni_esiti_istruttorie as cei
on cei.id = protocollo.`comunicazione_esito_id`
join istruttorie_richieste as i
on i.id = cei.`istruttoria_id`
join richieste as r
on r.id = i.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)


UNION 
-- Risposta comunicazione esito
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Risposta_comunicazione_esito_istruttoria/') as `Path output`
		
from richieste_protocollo as protocollo
join risposte_comunicazioni_esiti_istruttorie rcei
on rcei.id = protocollo.`risposta_comunicazione_id`
join comunicazioni_esiti_istruttorie as cei
on cei.id = rcei.comunicazione_id
join istruttorie_richieste as i
on i.id = cei.`istruttoria_id`
join richieste as r
on r.id = i.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Esito istruttoria pagamento
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Esito_istruttoria_pagamento/') as `Path output`
		
from richieste_protocollo as protocollo
join esiti_istruttoria_pagamento as eip
on eip.id = protocollo.`esito_istruttoria_pagamento_id`
join pagamenti as p
on p.id = eip.pagamento_id
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION

-- Richiesta chiarimenti
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Richiesta_chiarimenti/') as `Path output`
		
from richieste_protocollo as protocollo
join richieste_chiarimenti as rc
on rc.id = protocollo.`richiesta_chiarimenti_id`
join pagamenti as p
on p.id = rc.pagamento_id
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Risposta richiesta chiarimenti
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Risposta_richiesta_chiarimenti/') as `Path output`
		
from richieste_protocollo as protocollo
join risposte_richieste_chiarimenti as rrc
on rrc.id = protocollo.`risposta_richiesta_chiarimenti_id`
join richieste_chiarimenti as rc
on rc.id = rrc.richieste_chiarimenti_id
join pagamenti as p
on p.id = rc.pagamento_id
join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)
UNION
-- Comunicazione_progetto
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Comunicazione_progetto/') as `Path output`
		
from richieste_protocollo as protocollo
join comunicazioni_progetto as cp
on cp.id = protocollo.`comunicazione_progetto_id` and cp.tipo_oggetto = 'RICHIESTA'
join richieste as r
on r.id = cp.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Comunicazione_progetto variazione
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Comunicazione_progetto_variazione/') as `Path output`
		
from richieste_protocollo as protocollo
join comunicazioni_progetto as cp
on cp.id = protocollo.`comunicazione_progetto_id` and cp.tipo_oggetto = 'VARIAZIONE'
join variazioni_richieste as vr
on vr.id = cp.variazione_id
join attuazione_controllo_richieste as atc
on atc.id = vr.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)



UNION
-- Risposta comunicazione_progetto
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Risposta_comunicazione_progetto/') as `Path output`
		
from richieste_protocollo as protocollo
join risposte_comunicazioni_progetto as rcp
on rcp.id = protocollo.`risposta_comunicazione_progetto_id` 
join comunicazioni_progetto as cp
on cp.id = rcp.comunicazione_id and cp.tipo_oggetto = 'RICHIESTA'
join richieste as r
on r.id = cp.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Risposta omunicazione_progetto variazione
select replace(doc.path,'/var/www/' ,'/mnt/fesr/') as 'Path', 
		concat(r.id, '/', 'Risposta_comunicazione_progetto_variazione/') as `Path output`
		
from richieste_protocollo as protocollo
join risposte_comunicazioni_progetto as rcp
on rcp.id = protocollo.`risposta_comunicazione_progetto_id` 
join comunicazioni_progetto as cp
on cp.id = rcp.`comunicazione_id` and cp.tipo_oggetto = 'VARIAZIONE'
join variazioni_richieste as vr
on vr.id = cp.variazione_id
join attuazione_controllo_richieste as atc
on atc.id = vr.`attuazione_controllo_richiesta_id`
join richieste as r
on r.id = atc.`richiesta_id`
join `richieste_protocollo_documenti` as doc
on doc.`richiesta_protocollo_id` = protocollo.id
where r.procedura_id = @procedura
and protocollo.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Documenti istruttoria

select concat(replace(doc.path,'/var/www/' ,'/mnt/fesr/'), doc.nome) as 'Path',
		concat(r.id, '/', 'Istruttoria/') as `Path output`
		
from documenti as doc
join documenti_istruttoria as di
on di.documento_file_id = doc.id 
join richieste as r
on r.id = di.documento_richiesta_id

where r.procedura_id = @procedura
and doc.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION

-- Revoche
select concat(replace(doc.path,'/var/www/' ,'/mnt/fesr/'), doc.nome) as 'Path',
		concat(r.id, '/', 'Revoca/') as `Path output`
		
from documenti as doc
join atti_revoche as ar
on ar.documento_id = doc.id
join revoche as re
on re.atto_revoca_id = ar.id
join attuazione_controllo_richieste as atc
on atc.id = re.attuazione_controllo_richiesta_id
join richieste as r
on r.id = atc.richiesta_id

where r.procedura_id = @procedura
and doc.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION

-- Documenti amissibilità
select concat(replace(doc.path,'/var/www/' ,'/mnt/fesr/'), doc.nome) as 'Path',
		concat(r.id, '/', 'Atti ammissibilita/') as `Path output`
		
from documenti as doc
join atti as a on a.documento_id = doc.id
join istruttorie_richieste as di on a.id = di.atto_ammissibilita_atc_id
join richieste as r on r.id = di.richiesta_id
where r.procedura_id = @procedura
and doc.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

UNION
-- Documenti concenssione
select concat(replace(doc.path,'/var/www/' ,'/mnt/fesr/'), doc.nome) as 'Path',
		concat(r.id, '/', 'Atti concessione/') as `Path output`
		
from documenti as doc
join atti as a on a.documento_id = doc.id
join istruttorie_richieste as di on a.id = di.atto_concessione_atc_id
join richieste as r on r.id = di.richiesta_id
where r.procedura_id = @procedura
and doc.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

-- Documenti attuazione
UNION

select concat(replace(doc.path,'/var/www/' ,'/mnt/fesr/'), doc.nome) as 'Path',
		concat(r.id, '/', 'Istruttoria attuazione/') as `Path output`
		
from documenti as doc
join documenti_attuazione as di
on di.documentofile_id = doc.id 
join attuazione_controllo_richieste as atc
on di.attuazionecontrollorichiesta_id = atc.id 
join richieste as r
on r.id = atc.richiesta_id

where r.procedura_id = @procedura
and doc.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

-- Documenti istruttoria pagamenti
UNION

select concat(replace(doc.path,'/var/www/' ,'/mnt/fesr/'), doc.nome) as 'Path',
		concat(r.id, '/', 'Istruttoria pagamenti/') as `Path output`
		
from documenti as doc
join documenti_istruttoria_pagamenti as di
on di.documentofile_id = doc.id 
join pagamenti as p
on di.pagamento_id = p.id 
join attuazione_controllo_richieste as atc
on p.attuazione_controllo_richiesta_id = atc.id 
join richieste as r
on r.id = atc.richiesta_id

where r.procedura_id = @procedura
and doc.data_cancellazione is null
and r.id in
(2144,2142,2553,1910,1915,1866,2007,1938,2247,2060,2199,2163,2572,1886,2134,1980,
1930,2687,2022,2020,2172,2384,2222,2563,2510,2164,2556,1934,1799,2115,2285,1872,1829,1822,
1964,2073,2240,2061,2494,2086,2025,2078,2436,1877,1950,1820,2371,2695,1807,1811,1819,2539,
1785,1985,2357,1853,1942,1845,1805,2176,1912,2528,2310,2309,1958,1951,2006,2349,2746,2065,
2187,2182,1818,2631,2723,2004,2579,2752,2670,2671,1789,2582,2753,1802,1852,2043,2046,2708,
1788,2332,2603,2527,1936,2132,1921,2053,2052,2038,2149,2114,2171,2141,2110,1999,1911,2345,
2459,2647,2151,2532,2587,2616,2348,1892,2191,2347,2356,2408,2416,2162,2335,2555,2509,2350,
1833,2136,2041,2649)

