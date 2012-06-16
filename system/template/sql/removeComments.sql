-- Markiert die gegebenen Kommentare als gelöscht

-- @param type string der Feldname des kommentierten Objektes
-- @param id integer die ID des kommentierten Objektes
-- @param remove string die IDs der zu löschenden Kommentare (kommagetrennt)
-- @author Cédric Neukom

UPDATE `comments`
SET `deleted` = NOW()
WHERE `%{type}%` = '%{id}%'
	AND `id` IN (%{remove}%)