-- Erstellt eine Liste

-- @param owner integer Eigentümer ID
-- @param name string Name der Liste
-- @param type integer Listentyp
-- @param deadline integer Abgabetermin (bei Prüfungslisten)
-- @param class ID der zugehörigen Klasse
-- @author Cédric Neukom

INSERT INTO `lists` (`owner`, `name`, `creation_time`, `type`, `deadline`, `class`)
VALUES ('%{owner}%', '%{name}%', NOW(), '%{type}%', %{deadline}%, '%{class}%')