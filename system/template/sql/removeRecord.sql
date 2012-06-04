-- Entfernt einen Datensatz anhand der Tabelle und der ID

-- @param table string Die Tabelle, von der gelöscht werden soll
-- @param id integer Die ID des zu löschenden Datensatzes
-- @author Cédric Neukom

DELETE FROM `%{table}%`
WHERE `id` = '%{id}%'
LIMIT 1