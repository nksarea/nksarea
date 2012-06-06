-- Setzt neuen Benutzerlevel

-- @param accessLevel integer der neue Benutzerlevel
-- @param uid integer die Benutzer ID des betroffenen Benutzers
-- @author Cédric Neukom

UPDATE `users`
SET `access_level` = '%{accessLevel}%'
WHERE `id` = '%{uid}%'
LIMIT 1