-- Vollendet Registrierungsvorgang: Benutzernaame, Passwort und "Registriert am" setzen

-- @param name string Der Benutzername
-- @param pwd string Das Passwort - bereits gehasht
-- @param id integer Die ID des Benutzers
-- @author Cédric Neukom

UPDATE `users`
SET `password` = '%{pwd}%',
	`name` = '%{name}%',
	`registrated` = NOW(),
	`last_activity` = NOW(),
	`accept` = 1
 WHERE `id` = '%{id}%'
LIMIT 1