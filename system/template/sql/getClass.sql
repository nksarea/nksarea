-- Liesst die Klasse anhand ihrer ID aus

-- @param id integer Die Klassen ID
-- @author Cédric Neukom

SELECT *
FROM `classes`
WHERE `id` = '%{id}%'
LIMIT 0,1