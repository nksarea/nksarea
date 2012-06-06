-- Fügt eine Übersetzung hinzu

-- @param key string der Schlüssel der Übersetzung
-- @param lang string Sprache der Zeichenkette
-- @param text string Übersetzung in diese Sprache
-- @author Cédric Neukom

INSERT INTO `translation` (`key`, `lang`, `text`)
VALUES ("%{key}%", "%{lang}%", "%{text}%")