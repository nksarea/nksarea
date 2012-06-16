-- Liest Anzahl Kommentare und Root-Kommentare zu einem Objekt

-- @param type string der Typ des Objekts ( = Spaltenname)
-- @param id integer die ID des Objekts

SELECT (
	SELECT COUNT(*)
	FROM `comments`
	WHERE `%{type}%` = '%{id}%'
		AND `deleted` IS NULL
) AS `length`, (
	SELECT COUNT(*)
	FROM `comments`
	WHERE `%{type}%` = '%{id}%'
		AND `parent` = 0
		AND `deleted` IS NULL
) AS `threads`, (
	SELECT `owner`
	FROM `%{type}%s`
	WHERE `id` = '%{id}%'
	LIMIT 1
) AS `owner`