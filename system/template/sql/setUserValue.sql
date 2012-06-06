-- Setzt einen Benutzerwert

-- @param key string Der Feldname, dessen Wert neu gesetzt werden soll
-- @param value string Der neue Wert
-- @param id integer Die Benutzer ID
-- @author Cédric Neukom

UPDATE `users`
SET `%{key}%` = '%{value}%'
WHERE `id` = '%{id}%'
LIMIT 1