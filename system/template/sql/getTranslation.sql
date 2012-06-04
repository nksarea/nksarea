-- Liest eine Übersetzung aus dem lokalen Cache

-- @param key string der Schlüssel der Zeichenkette
-- @param lang string die vom Benutzer bevorzugte Sprache
-- @author Cédric Neukom

SELECT `text`
FROM `translation`
WHERE `key` = "%{key}%"
	AND `lang` = "%{lang}%"
	AND `id` != 0 -- Token verstecken
LIMIT 1