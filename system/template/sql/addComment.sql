-- Fügt einen Kommentar hinzu

-- @param type string
-- @param objId integer
-- @param comment string
-- @param replyTo integer
-- @param author integer
-- @author Cédric Neukom

INSERT INTO `comments` (`parent`, `%{type}%`, `author`, `text`)
VALUES ('%{replyTo}%', '%{objId}%', '%{author}%', '%{comment}%')