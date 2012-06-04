-- Liest einen Datensatz anhand tabelle und id aus

-- @param table string Die Tabelle
-- @param id integer Die ID des Datensatzes
-- @author Cédric Neukom

SELECT *
FROM `%{table}%`
WHERE `id` = '%{id}%'
LIMIT 1