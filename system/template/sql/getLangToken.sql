-- Liest den Zugangsschlüssel oder NULL (falls abgelaufen) aus

-- @author Cédric Neukom

SELECT IF(`date` < NOW()-500, NULL, `text`) AS `token`
FROM `translation`
WHERE `id` = 0
LIMIT 1