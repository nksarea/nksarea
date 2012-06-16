SELECT projects.name, projects.owner AS owner_id, users.name AS owner, projects.upload_time, projects.id, projects.color
FROM `projects`
JOIN `users` ON users.id = projects.owner
WHERE users.id = %{id}%
ORDER BY projects.upload_time DESC