-- Liest einzuloggenden Benutzer aus

-- @param user string der Benutzername
-- @author Cédric Neukom

SELECT *
FROM `users`
WHERE `name` = '%{user}%'
	AND `registrated` IS NOT NULL
LIMIT 0, 1