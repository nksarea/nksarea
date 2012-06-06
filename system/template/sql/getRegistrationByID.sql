-- Liest den passenden Benutzer aus, dessen Registrierungsprozess noch nicht
-- abgeschlossen ist.

-- @param id integer Die RegistrationsID
-- @author Cédric Neukom

SELECT *
FROM `users`
WHERE `registrated` IS NULL
	AND `name` IS NULL
	AND `id` = '%{id}%'
LIMIT 0,1