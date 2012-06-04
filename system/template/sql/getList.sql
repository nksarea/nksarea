-- Liest die (Projekt-)Liste anhand der ID aus.

-- @param id integer die Liten ID
-- @author Cédric Neukom

SELECT *
FROM `lists`
WHERE `id` = '%{id}%'
LIMIT 0,1