-- Liest alle Kommentare zu einem Objekt aus

-- @param type string der Typ des Objekts
-- @param id integer die ID des Objekts
-- @param order ASC|DESC die Sortierreihenfolge

SELECT c.`id`, c.`text`, c.`date`, c.`author` AS `authorID`, c.`parent`, a.`name` AS `author`
FROM `comments` c
	JOIN `users` a
		ON c.`author` = a.`id`
WHERE `%{type}%` = '%{id}%'
	AND `deleted` IS NULL
ORDER BY `id` ASC, `date` %{order}%