SELECT lists.name, lists.type, lists.creation_time
FROM `lists`
JOIN `users` ON users.id = lists.owner
WHERE users.id = %{id}%
ORDER BY lists.creation_time DESC