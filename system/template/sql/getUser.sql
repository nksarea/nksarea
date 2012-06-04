-- Sucht Benutzer mit gegebenem Benutzernamen

-- @param name string der Benutzername, nach dem gesucht werden soll
-- @author Cédric Neukom

SELECT *
FROM `users`
WHERE `name` LIKE '%{user}%'