-- Setzt den Zugangsschlüssel

-- @author Cédric Neukom

UPDATE `translation`
SET `text` = '%{token}%'
WHERE `id` = 0