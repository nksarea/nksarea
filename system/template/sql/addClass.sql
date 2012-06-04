-- Trägt eine neue Klasse ein

-- @param name string Name der Klasse
-- @param nickname string Spiztname der Klasse
-- @author Cédric Neukom

INSERT INTO `classes` (`name`, `nickname`)
VALUES ('%{name}%', '%{nickname}%')