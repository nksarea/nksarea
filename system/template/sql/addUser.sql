-- Trägt einen neuen Benutzer ein

-- @param hash string Zufällige Zeichenkette, die zum Aktivieren eines Kontos benötigt wird
-- @param accessLevel integer Zugriffslevel des neuen Benutzers
-- @param realName string Der (reale) Name des Benutzers
-- @param email string Die E-Mail Adresse des Benutzers
-- @param valid datetime gibt an, bis wann der Aktivierungslink gültig ist
-- @param class integer Die Klasse, zu der der neue Benutzer gehört
-- @author Cédric Neukom

INSERT INTO `users` (`password`, `access_level`, `realname`, `email`, `last_activity`, `class`)
VALUES ('%{hash}%', '%{accessLevel}%', '%{realName}%', '%{email}%', '%{valid}%', '%{class}%')