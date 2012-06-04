-- Setzt neuen Wert für accept

-- @param accept integer der neue Wert für accept
-- @param uid integer die Benutzer ID des betroffenen Benutzers
-- @author Cédric Neukom

UPDATE `users`
SET `accept` = '%{accept}%'
WHERE `id` = '%{uid}%'
LIMIT 1