-- Entfernt die Projekte von der Liste, die soeben gelöscht wurde

-- @param id integer Listen ID
-- @author Cédric Neukom

UPDATE `projects`
SET `list` = NULL
WHERE `list` = '%{id}%'