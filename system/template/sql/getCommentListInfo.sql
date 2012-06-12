-- Liest Anzahl Kommentare und Root-Kommentare zu einem Objekt

-- @param type string der Typ des Objekts ( = Spaltenname)
-- @param id integer die ID des Objekts

SELECT (
	SELECT COUNT(*)
	FROM `comments`
	WHERE `%{type}%` = '%{id}%'
) AS `length`, (
	SELECT COUNT(*)
	FROM `comments`
	WHERE `%{type}%` = '%{id}%'
		AND `parent` = 0
) AS `threads`