-- Setzt Zeitstempel des letzten Logins auf aktuellen Zeitspempel

-- @param uid integer ID des Benutzers
-- @author Cédric Neukom

UPDATE `users`
SET `last_activity` = NOW()
WHERE `id` = '%{uid}%'