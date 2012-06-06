-- Liest den passenden Benutzer aus, dessen Registrierungsprozess noch nicht
-- abgeschlossen ist.

-- @param email string Die E-Mail Adresse des angehenden Benutzers
-- @param hash string Der einmalige Identifizierungshash
-- @author Cédric Neukom

SELECT `id`
FROM `users`
WHERE `registrated` IS NULL
	AND `name` IS NULL
	AND `email` = '%{email}%'
	AND `password` = '%{hash}%'
LIMIT 0,1