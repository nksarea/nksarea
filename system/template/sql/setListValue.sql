-- Setzt Feld einer Liste

-- @param key string Der Feldname des zu setzenden Wertes
-- @param value string Der neue Wert
-- @param id integer Listen ID
-- @author Cédric Neukom

UPDATE `lists`
SET `%{key}%` = '%{value}%'
WHERE `id` = '%{id}%'
LIMIT 1