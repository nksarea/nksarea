-- ?

SELECT p.*, o.`name` AS owner_name, o.`realname` AS owner_realname
FROM `projects` p LEFT JOIN `users` o ON (o.id = p.owner)
WHERE p.`list` = %{list}%
ORDER BY %{orderby}% %{desc}%